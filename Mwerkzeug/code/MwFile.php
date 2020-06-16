<?php

use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Director;
use SilverStripe\Control\Controller;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Storage\AssetContainer;
use SilverStripe\Assets\ImageManipulation;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class MwFile extends DataObject implements AssetContainer
{
    use ImageManipulation;



    public static $SyncMode = false;
    public static $CopyrightPrefix = '©';
    public static $conf;
    public $cache;

    private static $db = array(
        'Filename'    => 'Varchar(255)',
        'Tags'        => 'Varchar(255)',
        'Copyright'   => 'Varchar(255)',
        'Description' => 'Text',
        'Keywords'    => 'Varchar(255)',
        'Size'        => 'Int',
        'IsFolder'    => DBBoolean::class,
        'Deleted'     => DBBoolean::class,
        'OldID'       => 'Int',
        'LastParent'  => 'Varchar(255)',
        'OldFilename' => 'Varchar(255)',
        'UsedIn' => 'Varchar(255)',
        'Hidden' => DBBoolean::class,
        'Sort'=>'Int',
    );

    private static $has_one = array(
        'Parent' => 'MwFile',
    );

    public $ExtParent;
    public $ExtParentPrefix;

    public static function conf($key)
    {
        return self::$conf[$key];
    }

    public function getConfigManager()
    {
        static $cm;
        if (!isset($cm)) {
            if (class_exists('MysiteMwFileConfigManager')) {
                $cm = new MysiteMwFileConfigManager();
            } else {
                $cm = null;
            }
        }
        return $cm;
    }


    protected function getFileSystem()
    {
        static $filesystem;
        if (!$filesystem) {
            $adapter = new Local(__DIR__ . '/../../files');
            $filesystem = new Filesystem($adapter);
        }
        return $filesystem;
    }

    /**
     * Assign a set of data to the backend
     *
     * @param  string $data Raw binary/text content
     * @param  string $filename Name for the resulting file
     * @param  string $hash Hash of original file, if storing a variant.
     * @param  string $variant Name of variant, if storing a variant.
     * @param  array $config Write options. {@see AssetStore}
     * @return array Tuple associative array (Filename, Hash, Variant) Unless storing a variant, the hash
     * will be calculated from the given data.
     */
    public function setFromString($data, $filename, $hash = null, $variant = null, $config = array())
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }

    /**
     * Assign a local file to the backend.
     *
     * @param  string $path Absolute filesystem path to file
     * @param  string $filename Optional path to ask the backend to name as.
     * Will default to the filename of the $path, excluding directories.
     * @param  string $hash Hash of original file, if storing a variant.
     * @param  string $variant Name of variant, if storing a variant.
     * @param  array $config Write options. {@see AssetStore}
     * @return array Tuple associative array (Filename, Hash, Variant) Unless storing a variant, the hash
     * will be calculated from the local file content.
     */
    public function setFromLocalFile($path, $filename = null, $hash = null, $variant = null, $config = array())
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }

    /**
     * Assign a stream to the backend
     *
     * @param  resource $stream Streamable resource
     * @param  string $filename Name for the resulting file
     * @param  string $hash Hash of original file, if storing a variant.
     * @param  string $variant Name of variant, if storing a variant.
     * @param  array $config Write options. {@see AssetStore}
     * @return array Tuple associative array (Filename, Hash, Variant) Unless storing a variant, the hash
     * will be calculated from the raw stream.
     */
    public function setFromStream($stream, $filename, $hash = null, $variant = null, $config = array())
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }

    /**
     * @return string Data from the file in this container
     */
    public function getString()
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }

    /**
     * @return resource Data stream to the asset in this container
     */
    public function getStream()
    {
        if (!$this->exists()) {
            return null;
        }

        return $this->FileSystem->readStream($this->Filename);
    }

    /**
     * @param bool $grant Ensures that the url for any protected assets is granted for the current user.
     * If set to true, and the file is currently in protected mode, the asset store will ensure the
     * returned URL is accessible for the duration of the current session / user.
     * This will have no effect if the file is in published mode.
     * This will not grant access to users other than the owner of the current session.
     * @return string public url to the asset in this container
     */
    public function getUrl($grant = true)
    {
        return '/files' . $this->Filename;
    }

    /**
     * @return string The absolute URL to the asset in this container
     */
    public function getAbsoluteURL()
    {
        return Director::absoluteURL($this->getUrl());
    }

    /**
     * Get metadata for this file
     *
     * @return array|null File information
     */
    public function getMetaData()
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }

    /**
     * Get mime type
     *
     * @return string Mime type for this file
     */
    public function getMimeType()
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }

    /**
     * Return file size in bytes.
     *
     * @return int
     */
    public function getAbsoluteSize()
    {
        if (file_exists($this->getAbsoluteFilename())) {
            $size = filesize($this->getAbsoluteFilename());
            return $size;
        } else {
            return 0;
        }
    }

    /**
     * Determine if a valid non-empty image exists behind this asset
     *
     * @return bool
     */
    public function getIsImage()
    {
        return $this->isImage();
    }

    /**
     * Determine visibility of the given file
     *
     * @return string one of values defined by the constants VISIBILITY_PROTECTED or VISIBILITY_PUBLIC, or
     * null if the file does not exist
     */
    public function getVisibility()
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }

    /**
     * Determine if this container has a valid value
     *
     * @return bool Flag as to whether the file exists
     */
    public function exists()
    {
        return file_exists($this->getAbsoluteFilename());
    }

    public function getSortedChildren($opts=[])
    {
        $where = "ParentID={$this->ID} and Deleted=0";
        if (!$opts['show_hidden']) {
            $where.=" and Hidden=0";
        }
        return DataObject::get('MwFile', $where, "Sort asc");
    }


    /**
     * Get value of filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->record['Filename'];
    }

    /**
     * Get value of hash
     *
     * @return string
     */
    public function getHash()
    {
        if (!$this->IsFolder) {
            return md5($this->Filename);
        }
        return null;
    }

    /**
     * Get value of variant
     *
     * @return string
     */
    public function getVariant()
    {
        return null;
    }

    /**
     * Delete a file (and all variants).
     * {@see AssetStore::delete()}
     *
     * @return bool Flag if a file was deleted
     */
    public function deleteFile()
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }

    /**
     * Rename to new filename, and point to new file
     *
     * @param  string $newName
     * @return string Updated Filename
     */
    public function renameFile($newName)
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }

    /**
     * Copy to new filename.
     * This will not automatically point to the new file (as renameFile() does)
     *
     * @param  string $newName
     * @return string Updated filename
     */
    public function copyFile($newName)
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }

    /**
     * Publicly expose the file (and all variants) identified by the given filename and hash
     * {@see AssetStore::publish}
     */
    public function publishFile()
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }

    /**
     * Protect a file (and all variants) from public access, identified by the given filename and hash.
     * {@see AssetStore::protect()}
     */
    public function protectFile()
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }

    /**
     * Ensures that access to the specified protected file is granted for the current user.
     * If this file is currently in protected mode, the asset store will ensure the
     * returned asset for the duration of the current session / user.
     * This will have no effect if the file is in published mode.
     * This will not grant access to users other than the owner of the current session.
     * Does not require a member to be logged in.
     */
    public function grantFile()
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }

    /**
     * Revoke access to the given file for the current user.
     * Note: This will have no effect if the given file is public
     */
    public function revokeFile()
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }

    /**
     * Check if the current user can view the given file.
     *
     * @return bool True if the file is verified and grants access to the current session / user.
     */
    public function canViewFile()
    {
        die(__FUNCTION__ . ' called on MwFile but is not implemented yet');
    }




    public function getMwLink()
    {
        return "mwlink://MwFile-{$this->ID}";
    }

    // get local customization  ---------- BEGIN

    public function getMy()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__] = new MyMwFile($this);
        }
        return $this->cache[__FUNCTION__];
    }
    // get local customization  ---------- END

    public static function setConf($key, $value)
    {
        self::$conf[$key] = $value;
    }

    public function setExtParent($parentObject, $parentPrefix)
    {
        $this->ExtParent = $parentObject;
        $this->ExtParentPrefix = $parentPrefix;
    }

    public function getNameWithoutExtension($filename)
    {
        $name = basename($filename);

        // Split extension
        $extension = null;
        if (($pos = strpos($name, '.')) !== false) {
            $extension = substr($name, $pos);
            $name = substr($name, 0, $pos);
        }
        return $name;
    }
    public function removeCachedImages()
    {
        $folder = dirname($this->Filename) . '/';
        $folder = MwFileManagement::getAbsFilename($folder);
        $cmd = "rm -f {$folder}/_resampled/*/" . $this->getNameWithoutExtension($this->Filename).'__*.*';
        shell_exec($cmd);
        return 1;
    }

    public function touch()
    {
        $this->LastEdited = Datum::mysqlDate(time());
        $this->write();
    }


    public static function ShortenText($longString, $len = 18)
    {
        if (strlen($longString) <= $len + 1) {
            return $longString;
        }
        $separator = '...';
        $separatorlength = strlen($separator);
        $maxlength = $len - $separatorlength;
        $start = $maxlength / 2;
        $trunc =  strlen($longString) - $maxlength;
        return substr_replace($longString, $separator, $start, $trunc);
    }

    public function getShortTitle($len = 18)
    {
        return $this->ShortenText($this->Title, $len);
    }

    public function getShortCopyrightText($len = 18)
    {
        return $this->ShortenText($this->getCopyrightText(), $len);
    }

    public function getCopyright()
    {
        if ($this->ExtParent && $this->ExtParentPrefix) {
            $c = $this->ExtParent->{$this->ExtParentPrefix . 'Copyright'};
        }
        if (!$c) {
            $c = $this->getField('Copyright');
        }
        return $c;
    }

    public function getCopyrightText()
    {
        if ($c = $this->getCopyright()) {
            return self::$CopyrightPrefix . " " . $c;
        }
    }

    public function getDescription()
    {
        if ($this->ExtParent && $this->ExtParentPrefix) {
            $c = $this->ExtParent->{$this->ExtParentPrefix . 'Description'};
        }
        if (!$c) {
            $c = $this->getField('Description');
        }
        return $c;
    }

    public function getDescriptionText()
    {
        if ($c = $this->getDescription()) {
            return $c;
        }
    }


    // map important Image Functions for Templates ---------- BEGIN

    public function SetWidth($a, $mode = 'scale_up')
    {
        if ($img = $this->Image()) {
            if ($mode == 'scale_up') {
                return $img->ScaleWidth($a);
            }
            if ($img->getWidth() > $a) {
                return $img->ScaleMaxWidth($a);
            } else {
                return $img;
            }
        }
    }

    public function SetCustom($a, $b)
    {
        if ($img = $this->Image()) {
            return $img->SetCustom($a, $b);
        }
    }

    public function SetHeight($a, $mode = 'scale_up')
    {
        if ($img = $this->Image()) {
            if ($mode == 'scale_up') {
                return $img->ScaleHeight($a);
            }
            if ($img->getHeight() > $a) {
                return $img->ScaleMaxHeight($a);
            } else {
                return $img;
            }
        }
    }

    public function CroppedImage($a, $b)
    {
        if ($img = $this->Image()) {
            return $img->FillMax($a, $b);
        }
    }

    public function PaddedImage($a, $b, $backgroundColor = "FFFFFF", $transparencyPercent = 0)
    {
        if ($img = $this->Image()) {
            return $img->Pad($a, $b, $backgroundColor);
        }
    }



    public function SetRatioSize($a, $b, $c = false)
    {
        if ($img = $this->Image()) {
            return $img->Fit($a, $b, $c);
        }
    }

    public function SetFittedSize($target_w, $target_h, $c = false)
    {
        if ($img = $this->Image()) {
            $w = $img->getWidth();
            $h = $img->getHeight();

            // never upscale:
            if ($target_w > $w) {
                $target_w = $w;
            }

            if ($target_h > $h) {
                $target_h = $h;
            }


            //return plain image if no sizing is needed
            if ($target_w == $w && $h <= $target_h) {
                if ($GLOBALS['fetchRemoteImages']) {
                    return $img->SetWidth($w - 1, $c); //force fetch of remote-img
                } else {
                    return $img;
                }
            }


            if ($target_h == $h && $w <= $target_w) {
                if ($GLOBALS['fetchRemoteImages']) {
                    return $img->SetHeight($h - 1, $c); //force fetch of remote-img
                } else {
                    return $img;
                }
            }
            return $img->SetRatioSize($target_w, $target_h, $c);
        }
    }


    public function SetSize($a, $b)
    {
        if ($img = $this->Image()) {
            return $img->Fit($a, $b);
        }
    }
    // map important Image Functions for Templates ---------- END


    public function getTitle()
    {
        if ($this->Filename == '/') {
            return 'root';
        } else {
            return basename($this->Filename);
        }
    }


    private static $default_sort = "IsFolder desc,Filename asc";


    private static $indexes = [
        'FilenameIndex' => [
            'type'    => 'unique',
            'columns' => ['Filename'],
        ],
        'TagIndex'      => [
            'type'    => 'fulltext',
            'columns' => ['Tags'],
        ],
        'FolderIndex'   => ['IsFolder'],
    ];

    public static function getByFilename($filename, $params = array())
    {
        if ($filename != '/') {
            $filename = self::trimFileName($filename);
        }

        if (!$params['includeDeleted']) {
            $addon = " and Deleted=0 ";
        }

        $n = DataObject::get_one('MwFile', "Filename= BINARY '" . Convert::raw2sql($filename) . "' " . $addon);

        return $n;
    }

    public static function getByID($ID)
    {
        if ($ID) {
            $rec = DataObject::get_by_id('MwFile', $ID);
            if (!$rec->Deleted) {
                return $rec;
            }
        }
    }



    public static function getImageByID($ID)
    {
        $file = self::getByID($ID);
        if ($file) {
            $img = $file->Image();
            return $img;
        }
    }



    public static function getImageByFilename($filename)
    {
        $file = self::getByFilename($filename);
        if ($file) {
            return $file->Image();
        }
    }

    public function remove()
    {
        if (file_exists($this->getAbsoluteFilename())) {
            if (unlink($this->getAbsoluteFilename())) {
                $this->delete();
            }
        }
    }

    public function onBeforeWrite()
    {
        $parentid = $this->getCorrectParentID();
        $this->ParentID = $parentid;
        $this->setField('Size', $this->getAbsoluteSize());
        parent::onBeforeWrite();
    }

    public function getCorrectParentID()
    {
        $parentdir = dirname($this->Filename);
        $parentfolder = $this->getByFilename($parentdir);

        if ($parentfolder) {
            return $parentfolder->ID;
        } elseif ($this->Filename == '/') {
            return 0;
        } elseif (!preg_match('#^/__#', $parentdir)) { // do not create __ -rootfolders, keep them hidden
            //create parent-folder if it does not exist:

            $parentfolder = MwFileManagement::createFolderFromDirectory($parentdir);
            if (!$parentfolder) {
                throw new Exception("cannot create parentfolder for {$parentdir}");
            } else {
                return $parentfolder->ID;
            }
        }
    }

    public function Link($action = null)
    {
        return $this->BaseUrl . $this->Filename;
    }



    public function getFilenameFromAbsFilename($filename)
    {
        $f = str_replace(self::getBaseDir(), '', realpath($filename));
        if ($filename == $f) {
            throw new Exception("getFilenameFromAbsFilename failed for $filename basedir: (" . self::getBaseDir() . ")", 1);
        }

        return $f;
    }


    public static function trimFileName($str)
    {
        $str = "/" . trim(trim($str), "/");
        return $str;
    }

    public function createRootNode()
    {
        $rn = new MwFile();
        $rn->Filename = '/';
        $rn->IsFolder = 1;
        $rn->write();
        return $rn;
    }

    public static function getRootNode()
    {
        $rootnode = self::getByFilename('/');
        if (!$rootnode) {
            if (array_get($_GET, 'create')) {
                echo "<li>creating rootnode";
                $rootnode = self::createRootNode();
            }

            if (!$rootnode) {
                user_error("MwFile Root-Node cannot be found, use ?create=1 to force creation");
            }
        }
        return $rootnode;
    }


    public function getUserRootNode()
    {
        if ($this->hasMethod('EXTgetUserRootNode')) {
            return $this->EXTgetUserRootNode();
        }

        // if($m=Member::currentUser())
        // {
        //   if(Permission::check('ADMIN'))
        // return $this->getByFilename('/public');
        // }

        return self::getRootNode();
    }



    public function getAbsoluteFilename()
    {
        return MwFileManagement::getAbsFilename($this->Filename);
    }


    public function getAbsoluteLink()
    {
        return Director::absoluteURL($this->Link());
    }

    public function ListMode()
    {
        return Controller::curr()->ListMode();
    }

    /**
     * Returns the children of this DataObject as an XHTML UL. This will be called recursively on each child,
     * so if they have children they will be displayed as a UL inside a LI.
     *
     * @param  string $attributes Attributes to add to the UL.
     * @param  string $titleEval PHP code to evaluate to start each child - this should include '<li>'
     * @param  Array $params Extra arguments that will be passed on to children, for if they overload this function.
     * @param  boolean $rootCall Set to true for this first call, and then to false for calls inside the recursion. You should not change this.
     * @param  int $minNodeCount
     * @return string
     */


    public function getSubFoldersAsUL($attributes = "", $titleEval = '"<li>" . $child->Title', $params = array(), $level = 0)
    {

        // echo "<li>called getSubFoldersAsUL($level,{$this->Filename})";
        if ($level == 0 && $this->IsFolder) {
            $child = $this;
            if (MwFile::$SyncMode) {
                MwFileManagement::syncFolder($child);
            }
            $output = "<ul$attributes>\n";
            $output .= eval("return $titleEval;") . "\n";
        }

        $children = $this->getSubFolders($params);

        if ($children) {
            if ($attributes) {
                $attributes = " $attributes";
            }

            $output .= "<ul>\n";

            foreach ($children as $child) {
                $output .= eval("return $titleEval;") . "\n" .
                    $child->getSubFoldersAsUL("", $titleEval, $params, $level + 1) . "</li>\n";
            }

            $output .= "</ul>\n";
        }

        if ($level == 0) {
            $output .= "</ul>\n";
        }

        return $output;
    }


    public function getSubFolders()
    {
        if ($this->IsFolder) {
            $sql = "IsFolder=1 and Deleted=0 and ParentID={$this->ID} ";
            $folders = DataObject::get('MwFile', $sql);
            //      if(array_get($_GET,'d') || 1 ) { $x=$folders; $x=htmlspecialchars(print_r($x,1));echo "\n<li>ArrayList: $sql<pre>$x</pre>"; }


            if (MwFile::$SyncMode) {
                if ($folders) {
                    foreach ($folders as $folder) {
                        MwFileManagement::syncFolder($folder);
                    }
                }
            }

            $cm = $this->getConfigManager();
            if ($cm && $cm->hasMethod('filterFoldersForBackend')) {
                $folders = $cm->filterFoldersForBackend($folders);
            }

            return $folders;
        }
    }

    public function onBeforeDelete()
    {
        if ($this->IsFolder) {
            MwFileManagement::deleteFolder($this);
        }

        if (file_exists($this->getAbsoluteFilename())) {
            unlink($this->getAbsoluteFilename());
        }

        $this->removeCachedImages();


        parent::onBeforeDelete();
    }

    public function delete()
    {
        $this->onBeforeDelete();
        if (!file_exists($this->getAbsoluteFilename)) {
            parent::delete();
        } else {
            MwUtils::NiceDie("cannot physically delete file {$this->Filename}");
        }
    }


    public function renameFolder($newname)
    {
        return MwFileManagement::doRenameFolder($this, $newname);
    }

    public function moveToFolder($targetFolder)
    {
        return MwFileManagement::MoveFileToFolder($this, $targetFolder);
    }


    public function getFiles()
    {
        if ($this->IsFolder) {
            $where = "IsFolder=0 and ParentID={$this->ID} and Deleted=0 ";
            $files = DataObject::get('MwFile', $where, " Filename asc");
            return $files;
        }
    }


    public function getChildren()
    {
        $where = "ParentID={$this->ID} and Deleted=0 ";
        $files = DataObject::get('MwFile', $where);
        return $files;
    }


    public static function getBaseUrl()
    {
        return MwFileManagement::getBaseUrl();
    }
 

    public static function getBaseDir()
    {
        return MwFileManagement::getBaseDir();
    }

    //getBasename

    //getFiletype

    //getSize

    //getHumanSize

    //getDirname

    //getUrl

    public function getPath()
    {
        return $this->getBaseDir() . $this->Filename;
    }

    public function getSQLFilename()
    {
        $fn = $this->trimFileName($this->Filename);
        return Convert::raw2sql($fn);
    }

    public function fixFileParents()
    {
        return MwFileManagement::doFixFileParents($this);
    }

    public function getParentPath()
    {
        if ($this->Filename == '/') {
            return "";
        }
        return dirname($this->Filename);
    }


    public function HTML($tag_addon = "")
    {
        if ($img = $this->Image()) {
            return "<img src=\"{$img->Link()}\" width=\"{$img->getWidth()}\" height=\"{$img->getHeight()}\" $tag_addon>";
        }
    }

    // public function forTemplate()
    // {
    //     if ($img = $this->Image()) {
    //         die("\n\n<pre>mwuits-debug 2019-03-20_10:01 ".print_r($img,1));
    //         return $img->forTemplate();
    //     }
    // }


    public function getTag()
    {
        if (file_exists($this->AbsoluteFilename)) {
            $url = $this->getURL();
            $title = ($this->Title) ? $this->Title : $this->Filename;
            if ($this->Title) {
                $title = Convert::raw2att($this->Title);
            } else {
                if (preg_match("/([^\/]*)\.[a-zA-Z0-9]{1,6}$/", $title, $matches)) {
                    $title = Convert::raw2att($matches[1]);
                }
            }
            return "<img src=\"$url\" alt=\"$title\" width=\"{$this->Width}\" height=\"{$this->Height}\" />"; //mwuits
        }
    }
    
    /**
     * Return an XHTML img tag for this Image.
     *
     * @return string
     */
    public function forTemplate()
    {
        return $this->getTag();
    }
    




    public function Image()
    {
        return $this;
    }

    public function getFileExtension()
    {
        return strtolower(File::get_file_extension($this->Filename));
    }

    public function isImage()
    {
        return (preg_match('#(jpg|png|gif|jpeg)#i', $this->FileExtension));
    }

    public function getFileExtensionUppercase()
    {
        return strtoupper(File::get_file_extension($this->Filename));
    }

    public function getFileIcon()
    {
        $imgurl = "/mysite/img/fileicons16/{$this->FileExtension}.gif";
        return "<img src='$imgurl' style='vertical-align: middle'>";
    }


    public function getSize()
    {
        $size = $this->getField('Size');
        if (!$size) {
            $size = $this->getAbsoluteSize();
        }
        return ($size) ? File::format_size($size) : false;
    }


    public function isSvg()
    {
        if ($this->FileExtension == 'svg') {
            return true;
        } else {
            return false;
        }
    }

    public function SvgContent()
    {
        if ($this->isSvg()) {
            return file_get_contents($this->AbsoluteFilename);
        }
    }

    public function CMSThumbnail()
    {
        if ($this->isSvg()) {
            return $this;
        }
        $th = $this->getFormattedImage('SetSize', 100, 80);

        return $th;
    }

    //legacy function to be compatible with silverstripe3
    public function getFormattedImage($format, $arg1 = null, $arg2 = null)
    {


        // Generates the manipulation key
        $variant = $this->variantName($format, $arg1, $arg2);


        // Instruct the backend to search for an existing variant with this key,
        // and include a callback used to generate this image if it doesn't exist
        $obj = $this;
        return $this->manipulateImage($variant, function ($backend) use ($obj, $format, $arg1, $arg2) {
            switch ($format) {
                case "SetSize":
                    return $backend->paddedResize($arg1, $arg2);
                    break;
                case "SetWidth":
                    return $backend->resizeByWidth($arg1);
                    break;
                case "SetHeight":
                    return $backend->resizeByHeight($arg1);
                    break;
                case "CroppedImage":
                    return $backend->croppedResize($arg1, $arg2);
                    break;
                case "SetFittedSize":
                     return $backend->resizeRatio($arg1, $arg2);
                    break;
                default:
                    die("don't know how to generate image-format $format");
            }
        });
    }
}


//class MwFile_Image extends Image
//{
//
//  var $Album;
//  var $Parent;
//  var $ID;
//
//  static public function createFromMwFile($MwFileObject) {
//    return new MwFile_Image($MwFileObject->record, false);
////    $this->Filename = str_replace(Director::baseFolder().'/','',$filename);
////
////    if(!preg_match('#\.(png|gif|jpg|jpeg|svg)$#i',$filename))
////    {
////      $this->Filename='Mwerkzeug/defaultfiles/icon_document.jpg';
////    }
////    elseif(!file_exists($filename) )
////    {
////      if(MwFile::conf('DefaultImage'))
////      {
////        $this->Filename=MwFile::conf('DefaultImage');
////      }
////    }
////
////    $this->ID=99;
//  }
//
//  // legacy:
////    public function getFormattedImage($format, $arg1 = null, $arg2 = null) {
////        if($GLOBALS['fetchRemoteImages']) { //mwuits begin
////            if(! Director::fileExists($this->Filename))
////            {
////                $imgbasedir=Director::getAbsFile(dirname($this->Filename));
////
////                if(!Director::fileExists($imgbasedir)) {
////                    Filesystem::makeFolder($imgbasedir);
////                    if(!Director::fileExists($imgbasedir)) {
////                        throw new Exception('cannot create dir '.$imgbasedir, 1);
////                    }
////                }
////
////                $remote=$GLOBALS['fetchRemoteImages'].$this->Filename;
////                $local=Director::getAbsFile($this->Filename);
////                @copy($remote, $local);
////
////            }
////
////        } //mwuits end
////
////        if($this->ID && $this->Filename && Director::fileExists($this->Filename)) {
////            $cacheFile = $this->cacheFilename($format, $arg1, $arg2);
////
////
////            if(!file_exists(Director::baseFolder()."/".$cacheFile) || isset(array_get($_GET,'flush'))  || array_get($_GET,'imgflush')==$arg1 ) {
////                $this->generateFormattedImage($format, $arg1, $arg2);
////            }
////
////            $cached = new Image_Cached($cacheFile);
////            // Pass through the title so the templates can use it
////            $cached->Title = $this->Title;
////            return $cached;
////        }
////    }
//
//
//    public function legacyGetFormattedImage($format, $arg1 = null, $arg2 = null) {
//
//
//        // Generates the manipulation key
//        $variant = $this->variantName($format, $arg1,$arg2);
//
////        // Instruct the backend to search for an existing variant with this key,
////        // and include a callback used to generate this image if it doesn't exist
//        $obj=$this;
//        return $this->manipulateImage($variant, function (Image_Backend $backend) use ($obj, $format, $arg1, $arg2) {
//
//            switch ($format) {
//                case "SetSize":
//                    die("\n\n<pre>mwuits-debug 2018-04-24_11:09 ".print_r(0,1));
//                    return $backend->resize(300, $arg1, $arg2);
//                    break;
//                default:
//                    die("don't know how to generate image-format $format");
//
//            }
//
//
//        });
//
//    }
//
//
//
//
//
//  public function VisibleOnPage()
//  {
//    static $min,$max;
//    if(!isset($min))
//      $min=array_get($_GET,'start')+1;
//    if(!isset($max))
//      $max= $min + Controller::curr()->getPageSize()-1;
//
//    $pos=$this->Pos();
//    if($pos>=$min && $pos <= $max)
//      return 1;
//    else
//      return 0;
//  }
//
//
//  // function cacheFilename($format, $arg1 = null, $arg2 = null) {
//  //   $folder = $this->Album->CacheDir;
//  //
//  //   $folder = str_replace(Director::baseFolder().'/','',$folder);
//  //
//  //   $format = $format.$arg1.$arg2;
//  //
//  //   return $folder . "/$format-" . $this->Name;
//  // }
//
//
//  // Prevent this from doing anything in the database
//  public function requireTable() {
//
//  }
//

//  function getFormattedImage($format, $arg1 = null, $arg2 = null)
//  {
//    if ($this->Parent->isSvg()) {
//      return $this;
//    }
//
//    switch (strtolower($format)) {
//      case 'croppedimage':
//      case 'setratiosize':
//      case 'setfittedsize':
//      if($arg1 == $this->getWidth() && $arg2==$this->getHeight())
//        return $this;
//      break;
//
//      case 'setwidth':
//      if($arg1 == $this->getWidth())
//        return $this;
//      break;
//
//      case 'setheight':
//      if($arg1==$this->getHeight())
//        return $this;
//      break;
//
//    }
//
//    return $this->legacyGetFormattedImage($format, $arg1 , $arg2);
//
//  }
//
//
//  public function generateSetCustom($gd,$arg1,$arg2)
//  {
//     if ($arg1) {
//      $followUpArgs=explode(',', $arg1);
//      $format=array_shift($followUpArgs);
//      $subArg1=array_shift($followUpArgs);
//      if ($followUpArgs) {
//        $subArg2=array_shift($followUpArgs);
//      } else {
//        $subArg2=NULL;
//      }
//      $generateFunc = "generate$format";
//      if($this->Parent->ExtParent && $this->Parent->ExtParent->hasMethod($generateFunc)){
//        $gd = $this->Parent->ExtParent->$generateFunc($gd, $subArg1, $subArg2);
//      }
//    }
//
//
//    if ($arg2) {
//      $followUpArgs=explode(',', $arg2);
//      $format=array_shift($followUpArgs);
//      $subArg1=array_shift($followUpArgs);
//      if ($followUpArgs) {
//        $subArg2=array_shift($followUpArgs);
//      } else {
//        $subArg2=NULL;
//      }
//      $generateFunc = "generate$format";
//      if($this->hasMethod($generateFunc)){
//        $gd = $this->$generateFunc($gd, $subArg1, $subArg2);
//      }
//    }
//
//    return $gd;
//
//  }
//
//
//  function Thumbnail($width=138,$height=121)
//  {
//    return $this->getFormattedImage('CroppedImage', $width,$height);
//  }
//
//
//  function CMSThumbnail()
//  {
//    if($this->Parent->isSvg()) {
//      return $this;
//    }
//    return $this->getFormattedImage('SetSize', 100,80);
//  }
//
//  function ZoomImage()
//  {
//    $limit=700;
//    if($this->getHeight() > $limit)
//      return $this->getFormattedImage('SetHeight', $limit);
//    else
//      return $this->getFormattedImage('SetWidth', $limit);
//  }
//
//  public function __get($fieldname)
//  {
//      //pass on to Mwfile Object
//      $ret=parent::__get($fieldname);
//      if(!$ret && $this->Parent)
//       return $this->Parent->__get($fieldname);
//      else
//       return $ret;
// }
//
//
//  /**
//   * Return the filename for the cached image, given it's format name and arguments.
//   * @param string $format The format name.
//   * @param string $arg1 The first argument passed to the generate function.
//   * @param string $arg2 The second argument passed to the generate function.
//   * @return string
//   */
//  function cacheFilename($format, $arg1 = null, $arg2 = null) {
//      $folder =dirname($this->Filename).'/';
//        if(strstr($folder,'Mwerkzeug'))
//        {
//            $folder=str_replace('Mwerkzeug','assets',$folder);
//        }
//      $format = $format.$arg1.$arg2;
//      return $folder . "_resampled/$format-" . $this->Name;
//  }
//
//
//
//}



class MwFileController extends Controller
{
}
