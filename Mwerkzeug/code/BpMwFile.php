<?php

use SilverStripe\Control\Session;
use SilverStripe\View\Requirements;
use SilverStripe\i18n\i18n;
use SilverStripe\Control\Cookie;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataObject;

//
// <% if Top.ChooserMode %>
// <div style='text-align:right'><a href='#' class='button uselink' dbid='$ID' dburl='$Url'>&raquo; auswaehlen</a></div>
// <% end_if %>
//
//   <div class="control">
//     <ul>
//       <!-- <li><a href='/BE/MwFile$ChooserMode/ajaxRemoveFile/$ID' class='edit button tinyicon' title='edit metadata'><span class='ui-icon-pencil'></span></a></li> -->
//       <% if Top.ChooserMode %>
//       <% else %>
//       <li><a href='/BE/MwFile$ChooserMode/ajaxRemoveFile/$ID' class='delete button tinyicon' title='<% _t('js__removeFile','Remove this file') %>'><span class='ui-icon-trash'></span></a></li>
//       <% end_if %>
//       <li><a href='$Link' class='preview button tinyicon' title='zoom' target='_blank'><span class='ui-icon-zoomin'></span></a></li>
//     </ul>
//   </div>
//

class BpMwFileController extends BackendPageController
{
  
  //upload
  
    public $CurrentDirectory;

    public $ChooserMode='';
  
    public $myClass='MwFile';

    private static $allowed_actions = [
        'iframeSave', 'iframeEdit', 'iframeReplaceFile', 'ajaxGetItemHTML', 'jsonGetInfo', 'index', 'uploadSingleFile', 'ajaxRemoveFile', 'ajaxMoveFile', 'fixParents', 'ajaxTreeRemove', 'ajaxTreeAdd', 'ajaxTreeRename', 'ajaxMoveFolder', 'receive', 'Files', 'upload', 'listing', 'folderTreeAsUL', 'UploadFile','receiveDropzoneFile'
        ];

    public function accessIsAllowed()
    {
        return true;
    }


    public function receiveDropzoneFile(SilverStripe\Control\HTTPRequest $request)
    {
        set_time_limit(500); // 5 minutes
        $mwFilePath=$request->getHeader('mwfile-path');

        $folder=MwFileManagement::getOrCreateFolder($mwFilePath);
        if (!$folder) {
            die("cannot create folder $mwFilePath");
        }
 
        $targetDir=$folder->getAbsoluteFilename();

        $fileName=$_FILES['file']['name'];
        
        $fileName=MwFileManagement::cleanupFilename($fileName);

        if (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
            unlink($targetDir . DIRECTORY_SEPARATOR . $fileName);
        }
        move_uploaded_file($_FILES['file']['tmp_name'], $targetDir . DIRECTORY_SEPARATOR . $fileName);
        $writtenFile=MwFileManagement::addPhysicalFile($targetDir . DIRECTORY_SEPARATOR . $fileName);

        return json_encode($writtenFile->toMap());
    }





    public function ListMode()
    {
        if (array_get($_GET, 'listmode')) {
            Mwerkzeug\MwSession::set('listmode', array_get($_GET, 'listmode'));
        }

        $mode=Mwerkzeug\MwSession::get('listmode');
        if ($mode =='list') {
            return 'list';
        } else {
            return 'icons';
        }
    }

    public static function includeRequirementsForMwFileItem()
    {
        Requirements::javascript('Mwerkzeug/javascript/MwFileItem_jqueryui_widget.js');

        if ($locale=i18n::get_locale()) {
            $lang=substr($locale, 0, 2);
            Requirements::javascript("Mwerkzeug/javascript/MwFileItem_jqueryui_widget-{$lang}.js");
        }

        Requirements::css('Mwerkzeug/css/MwFileItem.css');
        Requirements::themedCSS('MwFileItem');
     
        return "<!-- requirements added to header of document-->";
    }
  

    public function init()
    {
        parent::init();
    
        if (strstr(array_get($_SERVER, 'REQUEST_URI'), '/MwFileChooser/')) {
            $this->ChooserMode='Chooser';
            $this->summitSetTemplateFile('main', 'BackendPage_iframe');
            Requirements::javascript("Mwerkzeug/thirdparty/tinymce/jscripts/tiny_mce/tiny_mce_popup.js");
      
            Requirements::customCSS("
      body  { background:#F0F0EE ;margin:15px}
      .jstree-classic.jstree-focused {background:#F0F0EE !important }
      ");
      
            $js = <<<JAVASCRIPT
      
      var FileBrowserDialogue = {
          init : function () {
              // Here goes your code for setting your custom things onLoad.
          },
          mySubmit : function (URL,id) {
              //var URL = document.my_form.my_field.value;
              var win = tinyMCEPopup.getWindowArg("window");
              // insert information now
              win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

              // are we an image browser
              if (typeof(win.ImageDialog) != "undefined") {
                  // we are, so update image dimensions...
                  if (win.ImageDialog.getImageData)
                      win.ImageDialog.getImageData();

                  // ... and preview if necessary
                  if (win.ImageDialog.showPreviewImage)
                      win.ImageDialog.showPreviewImage(URL);
              }

              // close popup window
              tinyMCEPopup.close();
          }
      }

      tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);      
JAVASCRIPT;

            Requirements::customScript($js);
        }
    
        if (strstr(array_get($_SERVER, 'REQUEST_URI'), '/MwFileFieldChooser/')) {
            $this->ChooserMode='FieldChooser';
            $this->summitSetTemplateFile('main', 'BackendPage_iframe');

            Requirements::customCSS("
        body  { background:#F0F0EE ;margin:15px}
        .jstree-classic.jstree-focused {background:#F0F0EE !important }
        ");

            $js = <<<JAVASCRIPT
        var FileBrowserDialogue = {
            init : function () {
                // Here goes your code for setting your custom things onLoad.
            },
            mySubmit : function (URL,id) {
                if(window.opener)
                {
                  if(window.opener.LastUsedMwFileField)
                  {
                    window.opener.LastUsedMwFileField.updateIDFromPopupWindow(id);
                    window.close();
                  }
                  else
                    alert('error: cannot find file-chooser field in parent window.');
                }
                else
                { 
                  // boxy-version (old)
                  parent.FileBrowserReturnValue=id;
                  parent.popupWindow.hide();
                }
            }
        }
JAVASCRIPT;

            Requirements::customScript($js);
        }
    }
  
    public function Language()
    {
        if ($locale=i18n::get_locale()) {
            $lang=substr($locale, 0, 2);
        }
        
        return $lang;
    }
  
    public function iframeSave()
    {
        $this->loadRecord();
        if (array_get($_POST, 'fdata')) {
            $this->record->update(array_get($_POST, 'fdata'));
            $this->record->write();
        }
        return "<script>parent.MwFileItem_Current.options.editWindow.hide();</script>";
    }
  
  
    public function AllowUploadInFilechooser()
    {
        if (MwFile::conf('AllowUploadInFilechooser')===false) {
            return false;
        }
      
        return true;
    }



 
    
    public function iframeEdit()
    {
        $this->loadRecord();
        MwForm::preset($this->record);

        $p=array(); // ------- new field --------
        $p['label']="Copyright";
        $p['fieldname']="Copyright";
        $fields[$p['fieldname']]=MwForm::render_field($p);

        $p=array(); // ------- new field --------
        $p['label']="Description";
        $p['fieldname']="Description";
        $p['type']="textarea";
        $fields[$p['fieldname']]=MwForm::render_field($p);

        $fieldhtml=implode("\n", $fields);

        $html=<<<HTML
        <div style='padding-top:5px'>
          <a href='javascript:$("#dataform").submit();' class='button save'><span class='tinyicon ui-icon-disk'></span>Speichern</a>
          <a href='javascript:parent.MwFileItem_Current.options.editWindow.hide();' class='button cancel'><span class='tinyicon ui-icon-close'></span>Abbrechen</a>
        </div>
        <form id='dataform' method='POST' action='/BE/MwFile/iframeSave/{$this->record->ID}'>
          <div class='formsection'>
            <table class='ftable'>
              {$fieldhtml}
            </table>
          </div>
        </form>
    
        <form enctype="multipart/form-data" action="/BE/MwFile/iframeReplaceFile/{$this->record->ID}" method="POST" style="padding:10px 30px">
            Datei ersetzen:

<div style='padding:5px;background:#eee'>
          <input name="userfile" type="file" />
          <input type="submit" value="Absenden" />
        </div>
                </form>
HTML;

        $this->summitSetTemplateFile("main", "BackendPage_iframe");
        return array('Form'=>$html);
    }
    
  
    public function iframeReplaceFile()
    {
        $this->loadRecord();

        $filename=$_FILES['userfile']['name'];

        if (!$filename) {
            die("no file was uplaoded");
        }

        $oldFileExt = strtolower(pathinfo($this->record->Filename, PATHINFO_EXTENSION));
        $newFileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($oldFileExt && $oldFileExt != $newFileExt) {
            die("die neue Datei muss die Datei-Endung .$oldFileExt haben");
        }


        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $this->record->AbsoluteFilename)) {
            echo "Die Datei wurde ersetzt.";
            $this->record->touch();
            $this->record->removeCachedImages();
        } else {
            die("no file was uplaoded");
        }
    }

    public function ajaxGetItemHTML()
    {
        $this->loadRecord();
        if ($this->record) {
            return $this->customise($this->record)->renderWith('BpMwFileItem');
        }
    }
  
    public function jsonGetInfo()
    {
        $this->loadRecord();
        $ret=array();
    
        //$ret['requested_params']=$_POST;

        if ($this->record) {
            $ret['record']=$this->record->toMap();

            //thumbnail
            if ($img=$this->record->Image()) {
                if ($this->record->isSvg()) {
                    $ret['ThumbnailURL']=$this->record->Link();
                } elseif (is_array(array_get($_POST, 'Thumbnail')) && array_get($_POST, 'Thumbnail.format')) {
                    //custom thumbnail
                    $p=array_get($_POST, 'Thumbnail');
                    if ($tn=$img->getFormattedImage($p['format'], $p['arg1'], $p['arg2'])) {
                        $ret['ThumbnailURL']=$tn->Link();
                    }
                } else {
                    //default thumbnail
                    if ($tn=$img->CMSThumbnail()) {
                        $ret['ThumbnailURL']=$tn->Link();
                    }
                }
            }
            $ret['FilesizeStr']   = $this->record->getSize();
            $ret['FileExtension'] = $this->record->FileExtension;
            $ret['Filename']      = basename($this->record->Filename);
        }

        return json_encode($ret);
        exit();
    }

    public function index(SilverStripe\Control\HTTPRequest $request)
    {
        $id=Cookie::get('MwFileChooserDirectory');
        return $this->listing($id);
    }
  
  
    public function uploadSingleFile()
    {
        //show upload interface for MwFileField

        $this->summitSetTemplateFile('main', 'BackendPage_iframe');

        $this->summitSetTemplateFile('Layout', 'BpMwFile_uploadSingleFile');
    
        $c=array();
        return $c;
    }
  
    public function ajaxRemoveFile()
    {
        $this->record=Dataobject::get_by_id('MwFile', Controller::curr()->urlParams['ID']);

        $targetID=Controller::curr()->urlParams['OtherID'];
        if ($targetID) {
            $this->record->moveToFolder($targetID);
        } else {
            $this->record->delete();
        }
    
        echo "{ \"result\": \"record deleted\", \"error\": null, \"id\": ".Controller::curr()->urlParams['ID']." }";
    
        exit();
    }

    public function ajaxMoveFile()
    {
        $this->record = Dataobject::get_by_id('MwFile', Controller::curr()->urlParams['ID']);
        $targetFolder = Dataobject::get_by_id('MwFile', Controller::curr()->urlParams['OtherID']);
        $error=$this->record->moveToFolder($targetFolder);
    
        echo "{ \"result\": \"record moved\", \"error\": ".json_encode($error).", \"id\": ".Controller::curr()->urlParams['ID']." }";
    
        exit();
    }



    public function fixParents()
    {
        if ($match=array_get($_GET, 'match')) {
            $res=DataObject::get("MwFile", "Filename like '%$match%' and Filename<>'/'", "", "", "100");
            foreach ($res as $f) {
                $f->fixFileParents();
            }
            echo "<li>FIN";
        } else {
            echo "please add a match-parameter like ?match=/public/xyz";
        }

        die();
    }

    public function ajaxTreeRemove()
    {
        $id=(array_get($_REQUEST, 'id')*1);
        if ($id>1) {
            if ($folder=MwFile::getById($id)) {
                $ret['status']='OK';
                $ret['id']=$id;

                try {
                    $folder->delete();
                } catch (Exception $e) {
                    $ret['status']='ERR';
                    $ret['errormsg']=$e->getMessage();
                }
        
                header('content-type: application/json; charset=utf-8');
                echo json_encode($ret);
            }
        }

        exit();
    }

    public function ajaxTreeAdd()
    {
        header('content-type: application/json; charset=utf-8');
      
        if ($id=array_get($_REQUEST, 'id')*1) {
            $pageData['Title']=array_get($_REQUEST, 'title');
            $newpage=MwFileManagement::createFolderUnderID(array_get($_REQUEST, 'id'), $pageData);
            $ret['status']='OK';
            $ret['id']=$newpage->ID;
        }

        echo json_encode($ret);
        exit();
    }
   
   
   
    public function ajaxTreeRename()
    {
        $id=(array_get($_REQUEST, 'id')*1);
        if ($id>1) {
            if ($folder=MwFile::getById($id)) {
                $ret['status']='OK';
                $ret['id']=$id;


         
                try {
                    $res=$folder->renameFolder(array_get($_POST, 'newname'));
                    if (array_get($res, "type")=="error") {
                        $ret['status']='ERR';
                        $ret['errormsg']=array_get($res, "msg");
                    } else {
                        $ret=$res;
                    }
                } catch (Exception $e) {
                    $ret['status']='ERR';
                    $ret['errormsg']=$e->getMessage();
                }
        
                header('content-type: application/json; charset=utf-8');
                echo json_encode($ret);
            }
        }

        exit();
    }
   
   
   
   
  
    public function ajaxMoveFolder()
    {
        header('content-type: application/json; charset=utf-8');

        $ret=array();


        $nodeid=array_get($_POST, 'nodeid');
        $newparentid=array_get($_POST, 'newparentid');
        
        if (is_numeric($nodeid) && is_numeric($newparentid)) {
            //move nodeid to newparentid
            
            $NodeDir=Dataobject::get_by_id('MwFile', $nodeid);
            $NewParentDir=Dataobject::get_by_id('MwFile', $newparentid);
            
            if ($NewParentDir && $NodeDir && $NodeDir->ID && $NewParentDir->ID) {
                $error=$NodeDir->moveToFolder($NewParentDir);
    
                echo "{ \"result\": \"folder moved\", \"error\": ".json_encode($error).", \"id\": ".Controller::curr()->urlParams['ID']." }";
                exit();
            }
        }

        // $id=$this->parseID(array_get($_POST,'id'));
        //
        // if($id!=NULL)
        // {
        //
        //     if($id>0)
        //     {
        //         $ParentPage=Dataobject::get_by_id('SiteTree',$id);
        //     }
        //
        //   if($ParentPage->ID || $id==0)
        //   {
        //     $sortnum=0;
        //     if(is_array(array_get($_POST,'childrenids')))
        //     foreach (array_get($_POST,'childrenids') as $childrenid) {
        //       $sortnum+=10;
        //       if($page=Dataobject::get_by_id('SiteTree',$this->parseID($childrenid)))
        //       {
        //         //update sort
        //         $page->Sort=$sortnum;
        //         $page->setParent($id);
        //         $page->write();
        //         $ret['status']='OK';
        //         $ret['affected_nodes'][]='node_'.$page->ID;
        //       }else
        //         $ret['node_not_found'][]='node_'.$page->ID;
        //      }
        //   }
        // }
     

        echo json_encode($ret);
        exit();
    }

  
    public function receive($params=null)
    {
        if (Controller::curr()->urlParams['ID']) {
            $this->CurrentDirectory=MwFile::get_by_id('MwFile', Controller::curr()->urlParams['ID']);
        } else {
            $this->CurrentDirectory=MwFileManagement::getCurrentUploadDirectory();
        }


        /**
         *
         * Copyright 2009, Moxiecode Systems AB
         * Released under GPL License.
         *
         * License: http://www.plupload.com/license
         * Contributing: http://www.plupload.com/contributing
         */

        // HTTP headers for no cache etc
        header('Content-type: text/plain; charset=UTF-8');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // Settings
        //$targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
        
        $targetDir = MwFileManagement::getAbsFilename($this->CurrentDirectory->SQLFilename);
        
        $cleanupTargetDir = false; // Remove old files
        $maxFileAge = 60 * 60; // Temp file age in seconds

        // 5 minutes execution time
        @set_time_limit(5 * 60);
        // usleep(5000);

        // Get parameters
        $chunk = array_key_exists('chunk', $_REQUEST) ? array_get($_REQUEST, 'chunk') : 0;
        $chunks = array_key_exists('chunks', $_REQUEST) ? array_get($_REQUEST, 'chunks') : 0;
        $fileName = array_key_exists('name', $_REQUEST) ? array_get($_REQUEST, 'name') : '';

        // Clean the fileName for security reasons
        $fileName=MwFileManagement::cleanupFilename($fileName);
        // $fileName = preg_replace('/[^\w\._]+/', '', $fileName);

        // Make sure the fileName is unique

        $checkfilename=$this->CurrentDirectory->SQLFilename.'/'.$fileName;
        if (MwFile::getByFilename($checkfilename)) {
            // if (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName))
            $ext = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;
      
            while (MwFile::getByFilename($this->CurrentDirectory->SQLFilename.'/'. $fileName_a . '_' . $count . $fileName_b)) {
                $count++;
            }

            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        if (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
            unlink($targetDir . DIRECTORY_SEPARATOR . $fileName);
        }




        // Create target dir
        if (!file_exists($targetDir)) {
            @mkdir($targetDir);
        }



        // Remove old temp files
        if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
            while (($file = readdir($dir)) !== false) {
                $filePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // Remove temp files if they are older than the max age
                if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge)) {
                    @unlink($filePath);
                }
            }

            closedir($dir);
        } else {
            die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory '.$targetDir.'"}, "id" : "id"}');
        }

        // Look for the content type header
        if ((array_get($_SERVER, 'HTTP_CONTENT_TYPE'))) {
            $contentType = array_get($_SERVER, 'HTTP_CONTENT_TYPE');
        }

        if ((array_get($_SERVER, 'CONTENT_TYPE'))) {
            $contentType = array_get($_SERVER, 'CONTENT_TYPE');
        }



        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {


                // Open temp file
                $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
                if ($out) {

                    // Read binary input stream and append it to temp file
                    $in = fopen($_FILES['file']['tmp_name'], "rb");

                    if ($in) {
                        while ($buff = fread($in, 4096)) {
                            fwrite($out, $buff);
                        }
                    } else {
                        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                    }

                    fclose($out);

                    unlink($_FILES['file']['tmp_name']);
                } else {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                }
            } else {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "file" : "'.$_FILES['file']['tmp_name'].'" }');
            }
        } else {
            // Open temp file
            $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
            if ($out) {
                // Read binary input stream and append it to temp file
                $in = fopen("php://input", "rb");

                if ($in) {
                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                } else {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                }

                fclose($out);
            } else {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            }
        }

        //ok ?

      
        $writtenFile=MwFileManagement::addPhysicalFile($targetDir . DIRECTORY_SEPARATOR . $fileName);
      

        // Return JSON-RPC response
        if ($writtenFile && $writtenFile->ID) {
            if (array_get($_GET, 'Copyright')) {
                $writtenFile->Copyright=array_get($_GET, 'Copyright');
                $writtenFile->write();
            }
          
            echo('{"jsonrpc" : "2.0", "result" : null, "id" : '.$writtenFile->ID.',"res":"'.$targetDir . DIRECTORY_SEPARATOR . $fileName.'"}');
        } else {
            die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to write file '.$targetDir . DIRECTORY_SEPARATOR . $fileName.' "}, "id" : "id"}');
        }
        

        if ($params['ReturnTheFile']) {
            return $writtenFile;
        } else {
            exit();
        }
    }
  
  
    public function Files()
    {
        if ($this->CurrentDirectory) {
            $f=$this->CurrentDirectory->getFiles();
        }
    
        return $f;
    }
  
    public function upload()
    {
        $this->listing(Controller::curr()->urlParams['ID']);
        return array();
    }
   
    public function listing($id=0)
    {
        if (!is_numeric($id)) {
            $id=Controller::curr()->urlParams['ID'];
        }

        if ($id) {
            $this->CurrentDirectory=MwFile::getByID($id);
            if ($this->CurrentDirectory) {
                Cookie::set('MwFileChooserDirectory', $this->CurrentDirectory->ID);
            }
        }
        $this->summitSetTemplateFile('Layout', 'BpMwFile_index');
        return array();
    }
  
    public function folderTreeAsUL()
    {
        if (array_get($_GET, 'sync')) {
            MwFile::$SyncMode=true;
        }
   
        $rootNode=singleton('MwFile')->getUserRootNode();
   
        $tree=$rootNode->getSubFoldersAsUL('', '"<li id=\'" . $child->ID . "\'><a href=\'/BE/MwFile'.$this->ChooserMode.'/listing/" . $child->ID . "\'>" . $child->Title . "</a>" ');

        return "$tree";
    }
  
  
    public function UploadFile()
    {
        return array();
    }
}
