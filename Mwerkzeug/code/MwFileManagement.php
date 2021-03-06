<?php

use SilverStripe\Control\Director;

//this class holds static File-Managementfunctions used for MwFile, to keep MwFile neat and clean


class MwFileManagement extends MwFile
{
    public static function getBaseDir()
    {
        return Director::baseFolder().self::getBaseUrl();
    }

    public static function getBaseUrl()
    {
        return '/files';
    }


    public static function cleanupFilename($filename)
    {
        $filename=trim($filename);
        $trans=array('ö'=>'oe','ä'=>'ae','ü'=>'ue','Ö'=>'OE','Ä'=>'AE','Ü'=>'UE','ß'=>'ss');
        $f = strtr($filename, $trans);
        $f = str_replace('&', 'und', $f);
        
        return preg_replace('#[^A-Za-z0-9.-]#i', '_', $f);
    }

    public static function cleanupFoldername($filename)
    {
        return self::cleanupFilename($filename);
    }

    public static function cleanupPathname($filename)
    {
        $filename=str_replace('/', '__folderseparator__', $filename);
        $filename=self::cleanupFilename($filename);
        return str_replace('__folderseparator__', '/', $filename);
    }

    //TODO: sip upload extensionless files

    public static function isValidFilename($filename)
    {
        if ($filename[0]=='.') {
            return false;
        }

        if ($filename=='_resampled') {
            return false;
        }

        return true;
    }

    public static function getSubFilenames($folder)
    {
        $db=DBMS::getMdb();
        $sql="select Filename,1 from MwFile where ParentID=".$folder->ID;
        // if(array_get($_GET,'d') || 1 ) { $x=$sql; $x=htmlspecialchars(print_r($x,1));echo "\n<li>ArrayList: <pre>$x</pre>"; }

        $res=$db->getAssoc($sql);
        return $res;
    }



    public static function getAbsFilename($filename)
    {
        $filename=MwFile::trimFileName($filename);
  
        return self::getBaseDir().$filename;
    }

    public static function getOrCreateFolder($filename)
    {
        $filename=MwFile::trimFileName($filename);

        $folder=MwFile::getByFilename($filename);
        if ($folder && $folder->exists()) {
            return $folder;
        }
    
        $absolutefilename=self::getAbsFilename($filename);
    
        self::CreatePhysicalFolder($absolutefilename);

        return self::createFolderFromDirectory($filename);
    }

    public static function CreatePhysicalFolder($absolutefilename)
    {
        if (!file_exists($absolutefilename)) {
            shell_exec("mkdir -p ".escapeshellarg($absolutefilename));
        }
    }

    //
    //
    // static public function createFolder($filename)
    // {
    //   // is parent-folder existent ?
    //   $folder=self::trimFolderName($filename);
    //   // $parent_folder=
    //   // if(self::doesFolderExist())
    //
    // }
  
  
    //get directory based on current date
    public static function getCurrentUploadDirectory()
    {
        $filedate=new Datum(time()); //now
        $dirname='/uploads/'.$filedate->germanDate('Y/m');
        $folder=MwFileManagement::getOrCreateFolder($dirname);

        return $folder;
    }
  
  
  
  
    public static function createFolderUnderID($folderID, $newFolderData)
    {
        //load parentfolder
        if ($parentfolder=MwFile::getByID($folderID)) {
            $basepath=$parentfolder->Filename;

            $newFolderName=self::cleanupFoldername($newFolderData['Title']);

            $newfolder=str_replace('//', '/', $basepath.'/'.$newFolderName);
      
            if (!is_dir(self::getAbsFilename($newfolder))) {
                mkdir(self::getAbsFilename($newfolder));
            }
      
            return self::createFolderFromDirectory($newfolder);
        }
    }

    public static function createFolderFromDirectory($filename)
    {
        if (is_dir(self::getAbsFilename($filename))) {
            if (!($record=MwFile::getByFilename($filename, array('includeDeleted'=>1)))) {
                $record=new MwFile;
            }
            $record->IsFolder=1;
            $basename=basename($filename);

            $cleanedUpFilename=self::cleanupFilename($basename);
            if ($basename!=$cleanedUpFilename) {
                echo "<li>cannot create directory ($basename) its not following our naming-convention ";
            } else {
                $record->Filename=$filename;
                $record->Deleted=0;
                $record->write();
                return $record;
            }
        }
    }


    public static function addPhysicalFile($absfilename)
    {
        if (is_file($absfilename)) {
            $filename=self::getFilenameFromAbsFilename($absfilename);

            $rec=MwFile::getByFilename($filename);
            if (!$rec) {
                $rec=new MwFile;
                $rec->Filename=$filename;
            }
            $rec->write();
            $rec->removeCachedImages();
            return $rec;
        }
    }
  
  
    public static function importFile($filename)
    {
        if (file_exists(self::getAbsFilename($filename))) {
            if (is_dir(self::getAbsFilename($filename))) {
                return self::createFolderFromDirectory($filename);
            } else {
                return self::addPhysicalFile(self::getAbsFilename($filename));
            }
        }
    }

    public static function deleteFolder($folder)
    {
        if (!is_dir($folder->AbsoluteFilename)) {
            return 1;
        } //folder does not exist, we deleted it somehow so
        elseif ($folder->Children && $folder->Children->count >0) {
            throw new Exception(sprintf(_t('MwFile.js__NotEmpty', "cannot delete folder '%s', it is not empty"), $folder->SQLFilename));
        } else {
            $subfiles=scandir($folder->AbsoluteFilename);
            if (sizeof($subfiles)<=2) {
                //physically delete folder if its empty
                rmdir($folder->AbsoluteFilename);
            }
        }
    
    
        return 1;
    }

    
    public static function importTmpFileToFolder($absolutePath, $mwFileFolderPath)
    {

        //similar to receiveDropzoneFile
        $folder=MwFileManagement::getOrCreateFolder($mwFileFolderPath);
        if (!$folder) {
            die("cannot create folder $mwFileFolderPath");
        }
 
        $targetDir=$folder->getAbsoluteFilename();

        $fileName=basename($absolutePath);
        
        $fileName=MwFileManagement::cleanupFilename($fileName);

        if (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
            unlink($targetDir . DIRECTORY_SEPARATOR . $fileName);
        }
        rename($absolutePath, $targetDir . DIRECTORY_SEPARATOR . $fileName);
        $writtenFile=MwFileManagement::addPhysicalFile($targetDir . DIRECTORY_SEPARATOR . $fileName);

        return $writtenFile;
    }

    public function doRenameFolder($file, $newname)
    {
        // check if targetfile exists in database: ---------- BEGIN
    
        $targetfolder=$file->Parent();
        $targetfilename=$targetfolder->Filename.'/'.preg_replace('#[^a-z0-9_.-]#i', '', $newname);

        $targetfilename=str_replace('//', '/', $targetfilename);
    
        $existing_targetfile=MwFile::getByFilename($targetfilename);
        if ($existing_targetfile && $existing_targetfile->ID) {
            return array('type' => 'error','msg'=>"cannot rename, Target-File already exists !");
        }

        // check if targetfile exists in database: ---------- END

        // check if targetfile exists on disk ---------- BEGIN
        $targetfilenameAbsolute=MwFileManagement::getAbsFilename($targetfilename);
        if (file_exists($targetfilenameAbsolute)) {
            //file found, but not found in database (above), so we think the file is abandoned, and so we can delete it
            unlink($targetfilenameAbsolute);
        }
        // check if targetfile exists on disk ---------- END
    
        // move file ---------- BEGIN
        if (!file_exists($file->AbsoluteFilename)) {
            return array('type' => 'error','msg'=>"cannot rename, src does not exist: ({$file->AbsoluteFilename})");
        }

        if (file_exists($targetfilenameAbsolute)) {
            return array('type' => 'error','msg'=>"cannot rename, target exists: ({$targetfilenameAbsolute})");
        }
    
        if (@rename($file->AbsoluteFilename, $targetfilenameAbsolute)) {
            $oldfilename=$file->Filename;
            $file->Filename=$targetfilename;
            $file->write();
      
            if ($file->IsFolder) {
                //update filenames of underlying files:
          
                $oldfoldername=rtrim($oldfilename, '/').'/';
                $newfoldername=rtrim($file->Filename, '/').'/';
          
                $newfoldername=str_replace('//', '/', $newfoldername);
                $oldfoldername=str_replace('//', '/', $oldfoldername);

                $sql="update MwFile set Filename=replace(concat('^',Filename),'^{$oldfoldername}','{$newfoldername}') 
                    where Filename like '{$oldfoldername}%'";
        
                //          echo $sql;
                $db=DBMS::getMdb();
                $res=$db->query($sql);
            }
        } else {
            return array('type' => 'error','msg'=>"cannot move, rename failed ({$file->AbsoluteFilename},{$targetfilenameAbsolute})");
        }
    
        // move file ---------- END

        //all OK:
        return null;
    }

    public function MoveFileToFolder($file, $targetfolder)
    {
    
    // check if targetfile exists in database: ---------- BEGIN
    
        $targetfilename=$targetfolder->Filename.'/'.basename($file->Filename);

        $targetfilename=str_replace('//', '/', $targetfilename);
    
        $existing_targetfile=MwFile::getByFilename($targetfilename);
        if ($existing_targetfile && $existing_targetfile->ID) {
            return array('type' => 'error','msg'=>"cannot move, Target-File already exists !");
        }

        // check if targetfile exists in database: ---------- END

        // check if targetfile exists on disk ---------- BEGIN
        $targetfilenameAbsolute=MwFileManagement::getAbsFilename($targetfilename);
        if (file_exists($targetfilenameAbsolute)) {
            //file found, but not found in database (above), so we think the file is abandoned, and so we can delete it
            unlink($targetfilenameAbsolute);
        }
        // check if targetfile exists on disk ---------- END
    
        // move file ---------- BEGIN
        if (!file_exists($file->AbsoluteFilename)) {
            return array('type' => 'error','msg'=>"cannot move, src does not exist: ({$file->AbsoluteFilename})");
        }

        if (file_exists($targetfilenameAbsolute)) {
            return array('type' => 'error','msg'=>"cannot move, target exists: ({$targetfilenameAbsolute})");
        }
    
        if (@rename($file->AbsoluteFilename, $targetfilenameAbsolute)) {
            $oldfilename=$file->Filename;
            $file->Filename=$targetfilename;
            $file->write();
      
            if ($file->IsFolder) {
                //update filenames of underlying files:
          
                $oldfoldername=rtrim($oldfilename, '/').'/';
                $newfoldername=rtrim($file->Filename, '/').'/';
          
                $newfoldername=str_replace('//', '/', $newfoldername);
                $oldfoldername=str_replace('//', '/', $oldfoldername);

                $sql="update MwFile set Filename=replace(concat('^',Filename),'^{$oldfoldername}','{$newfoldername}') 
                  where Filename like '{$oldfoldername}%'";
        
//          echo $sql;
                $db=DBMS::getMdb();
                $res=$db->query($sql);
            }
        } else {
            return array('type' => 'error','msg'=>"cannot move, rename failed ({$file->AbsoluteFilename},{$targetfilenameAbsolute})");
        }
    
        // move file ---------- END

        //all OK:
        return null;
    }

    public function doFixFileParents($file)
    {
        $parentfoldername=$file->getParentPath();
        if ($parentfolder=MwFile::getByFilename($parentfoldername)) {
            if ($parentfolder->ID!=$this->ParentID) {
                $existingParentFolder=MwFile::getById($this->ParentID);
                echo "<li>folder for {$this->Filename} false:  <b>{$existingParentFolder->Filename}|{$existingParentFolder->ParentID} </b> != <i>{$parentfolder->Filename}|{$parentfolder->ID}</i>";
                if (array_get($_GET, 'do')) {
                    $this->ParentID=$parentfolder->ID;
                    $this->write();
                    echo " ID fixed";
                }
            }
        }
    }

    public function exists()
    {
        return file_exists($this->AbsoluteFilename);
    }
  
    public static function syncFolder($folder)
    {
        $dirname=$folder->getPath();

        if (is_dir($dirname)) {
            $existingFilenames=self::getSubFilenames($folder);

            $filenames = scandir($dirname);

            foreach ($filenames as $filename) {
                $path=MwFile::trimFileName($folder->SQLFilename.'/'.$filename);
                if (self::isValidFilename($filename)) {
                    if (!$existingFilenames[$path]) {
                        self::importFile($path);
                    } else {
                        //skip file with wrong name
                    }
                }
                //echo "<li>unset".$path;
                unset($existingFilenames[$path]);
            }

            if ($existingFilenames) {
                echo "<li>some Files are present in DB and not on disk, check them...";
                if (array_get($_GET, 'd') || 1) {
                    $x=$existingFilenames;
                    $x=htmlspecialchars(print_r($x, 1));
                    echo "\n<li>ArrayList: <pre>$x</pre>";
                }
            }
        }
    }
}
