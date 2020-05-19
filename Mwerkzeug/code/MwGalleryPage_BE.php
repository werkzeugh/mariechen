<?php

use SilverStripe\Control\Controller;
use SilverStripe\Versioned\Versioned;
use SilverStripe\ORM\DataObject;


class MwGalleryPageBEController extends PageBEController
{

  var $myClass='MwGalleryPage';


  public function getRawTabItems($params=NULL)
  {
      if($params && $params['use_parent'])
          return parent::getRawTabItems();
      
    $items=Array(
      "10"=>"Title/Text",
      "20"=>"Settings",
      "25"=>"Images",
      );
    return $items;
  }

  //hidden step to import uploaded files
  public function step_90($value='')
  {
    $ret= "check for uplaoded files";
    
    $q=$this->record->getImportQueue();

    if(is_array($q))
    foreach($q as $f)
    {
      
       $filename=$this->record->UploadDir.'/'.$f;
       $ret.="<li>adding job for importing $filename";
       Job::addJob(get_class($this),'import2gallery',Array('ID'=>$this->record->ID,'file'=>$filename));
    }
    
    return $ret;
  }

  public function step_10()
  {

    $p=Array(); // ------- new field --------
    $p['label']="Titel";
    $p['fieldname']="Title";
    $this->formFields[$p['fieldname']]=$p;

    // $p=Array(); // ------- new field --------
    // $p['label']="Intro-Text";
    // $p['type']='textarea';
    // $p['fieldname']="Intro";
    // $p['styles']="height:150px";
    // $this->formFields[$p['fieldname']]=$p;

    $p=Array(); // ------- new field --------
    $p['label']="Copyright";
    $p['fieldname']="Copyright";
    $this->formFields[$p['fieldname']]=$p;


    BackendHelpers::includeTinyMCE();  //all textareas with class tinymce will be richtext-editors

    $p=Array(); // ------- new field --------
    $p['label']="Text";
    $p['type']='textarea';
    $p['fieldname']="Content";
    $p['addon_classes']="tinymce";
    $p['rendertype']='beneath';
    $this->formFields[$p['fieldname']]=$p;


  }



  public function step_25()
  {

    if (array_get($_GET,'receive')) 
    {
      return $this->receive();
    }

    if (array_get($_GET,'imagelist')) 
    {
      return $this->ajaxImageList();
    }

    if (array_get($_GET,'queueinfo')) 
    {
      return $this->ajaxQueueInfo();
    }

    if (array_get($_GET,'ajaxdelete')) 
    {
      return $this->ajaxDelete();
    }

    $p['FiltersJSON']=json_encode(Array('title' => 'Image-files','extensions' => 'jpg'));

    $p['UploadURL']=$this->CurrentURL().'?receive=1';

    $uploadcode=$this->customise($p)->renderWith("BpMwFile_UploadWidget");

    $h="

      <script type=\"text/javascript\" charset=\"utf-8\">
         var onPluploadComplete = function()
         {
           $(\"#uploader\").html('upload complete, please wait ...');
           document.location.href='{$this->CurrentURL()}';
         };
      </script>

      $uploadcode

      <style type='text/css' >

      .imagelist li {
        width:110px;
        border:1px solid #ccc;
        float:left;
        margin-right:5px;
        margin-bottom:5px;
      }

      .imagelist li.frontimage {
        background:yellow;
        border: 1px solid #900;
      }

      .imagelist div.thumbnail {
        margin:5px;
        position:relative;
      }

      .imagelist div.control {
        bottom:2px;
        right:5px;
        position:absolute;
        display:none;
      }

      .imagelist div.control ul li{
        text-align:right;
        margin:0;
        padding:0;
        float:none;
        border:none;
        margin:2px 0px;
      }


      .queueinfo {
        border-top:2px dotted #900;
        border-bottom:2px dotted #900;
        margin:10px 0px;
        min-height:20;
        padding:5px 5px 5px 45px;
      }

      .imagearea {
        width:800px;
      }

      .imagelist_header {
        border-bottom:1px solid #555;
        margin-bottom:10px;
      }

      </style>


      <div class='imagearea'>
      <div id='queueinfo' class='group'></div>
      <div id='imagelist' class='group'>
      <center><img src='/mysite/thirdparty/colorbox/example1/images/loading.gif'></center>
      </div>
      </div>


        <div class='space'>
        <a href='#' class='button imagelist_refresh'><span></span>refresh image-list</a>
        <a href='#' class='button delete alldelete confirm'><span></span>Delete all images</a>
      </div>
      
        <script type=\"text/javascript\" charset=\"utf-8\">

        var images_in_queue=0;

        var currenticon=0;

        $(document).ready(function() {

          $('.actions').hide();

          refresh_imagelist=function()
          {
            $('#imagelist').load('{$this->CurrentURL()}?imagelist=1');
          }

          refresh_queueinfo=function()
          {
            $('#queueinfo').load('{$this->CurrentURL()}?queueinfo=1');
          }


          $('a.alldelete').click(function(e)
          {
            e.preventDefault();
            $(\"input[name=NextAction]\").val('alldelete');
            $(\"#dataform\").submit();
          });

          
        
          $('a.imagelist_refresh').click(function(e)
          {
            e.preventDefault();
            refresh_imagelist();
          });

        
        
          $(document).live('mouseover','.imagelist div.thumbnail',function ()
            {
              if(currenticon!=0 && currenticon!=this)
              {
                $('.imagelist div.control').fadeOut();
              }
              $(this).find('div.control').fadeIn();
              currenticon=this;
            }
          );


          $(\".imagelist a.delete\").live('click',function(){
            url=$(this).attr('href');
            if(confirm('are you sure ?'))
            {
              $.ajax({
                type: \"GET\",
                url: url,
                success: function(msg){
                  refresh_imagelist();
                }
              });
            }
            return false;
          });


          $(\".imagelist a.setfrontimage\").live('click',function(){
            url=$(this).attr('href');
              $.ajax({
                type: \"GET\",
                url: url,
                success: function(msg){
                  refresh_imagelist();
                }
              });
            return false;
          });

          refresh_imagelist();
          refresh_queueinfo();

        });

        </script>
      
      
      ";

      return $h;

    }


    public function receive()
    {
      //taken from MwFile->receive()

      if(Controller::curr()->urlParams['ID'])
        $this->CurrentDirectory=MwFile::get_by_id('MwFile',Controller::curr()->urlParams['ID']);
      else
        $this->CurrentDirectory=MwFileManagement::getCurrentUploadDirectory();

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

      $targetDir = $this->record->getUploadDir();

      $cleanupTargetDir = false; // Remove old files
      $maxFileAge = 60 * 60; // Temp file age in seconds

      // 5 minutes execution time
      @set_time_limit(5 * 60);
      // usleep(5000);

      // Get parameters
      $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
      $chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
      $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

      // Clean the fileName for security reasons
      $fileName=MwFileManagement::cleanupFilename($fileName);
      // $fileName = preg_replace('/[^\w\._]+/', '', $fileName);

      // Create target dir
      if (!file_exists($targetDir))
        @mkdir($targetDir);

      // Remove old temp files
      if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
        while (($file = readdir($dir)) !== false) {
          $filePath = $targetDir . DIRECTORY_SEPARATOR . $file;

          // Remove temp files if they are older than the max age
          if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge))
            @unlink($filePath);
        }

        closedir($dir);
        } else
          die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory '.$targetDir.'"}, "id" : "id"}');

        // Look for the content type header
        if ((array_get($_SERVER,'HTTP_CONTENT_TYPE')))
          $contentType = array_get($_SERVER,'HTTP_CONTENT_TYPE');

        if ((array_get($_SERVER,'CONTENT_TYPE')))
          $contentType = array_get($_SERVER,'CONTENT_TYPE');

        if (strpos($contentType, "multipart") !== false) {
          if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) 
          {
            // Open temp file
            $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
            if ($out) {
              // Read binary input stream and append it to temp file
              $in = fopen($_FILES['file']['tmp_name'], "rb");

              if ($in) {
                while ($buff = fread($in, 4096))
                  fwrite($out, $buff);
              } 
              else
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

              fclose($out);
              unlink($_FILES['file']['tmp_name']);
            } 
            else
              die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
          }
          else
            die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
        } 
        else 
        {
          // Open temp file
          $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
          if ($out) 
          {
            // Read binary input stream and append it to temp file
            $in = fopen("php://input", "rb");

            if ($in) 
            {
              while ($buff = fread($in, 4096))
                fwrite($out, $buff);
            } 
            else
              die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

            fclose($out);
          } 
          else
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        //ok ?

//        $writtenFile=$this->import2gallery($targetDir . DIRECTORY_SEPARATOR . $fileName);


      $filename = $targetDir . DIRECTORY_SEPARATOR . $fileName;

      if(preg_match('#\.jpg$#i',$filename))
        {
          
        $size               = filesize($filename);
        $mb                 = ceil($size/1024/1024);
        $this->record->LastMessage = "the file <div><strong>{$fileobject[name]} ({$mb} MB)</strong></div> was uploaded successfully, and will now be converted.
        <script>
        refresh_queueinfo();
        </script>
        ";
        $newfilename        = $this->record->getCleanFilename(basename($filename));
        $target_file        = $this->record->UploadDir.'/'.$newfilename;

        rename($filename,$target_file);
        Job::addJob(get_class($this),'import2gallery',Array('ID'=>$this->record->ID,'file'=>$target_file));

        $writtenFile= $target_file;
        } 



        // Return JSON-RPC response
        if($writtenFile)
          die('{"jsonrpc" : "2.0", "result" : null, "res":"'.$targetDir . DIRECTORY_SEPARATOR . $fileName.'"}');
        else
          die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to write file '.$targetDir . DIRECTORY_SEPARATOR . $fileName.' "}, "id" : "id"}');


        exit();
      }
      
      public function import2gallery($p)
      {
      	Versioned::set_stage("Live");
        
        $gallery=DataObject::get_by_id('MwGalleryPage',$p['ID']*1);

        $file=$p['file'];
        
        if($gallery->ID && file_exists($file))
        {
          $max_w=MwGalleryPage::conf('resizeMaxLength'); //max w of resized gallery image
          
          $targetfile=$gallery->FileDir.'/'.basename($file);
          
          if($max_w)
          {

            $ii=getimagesize($file);
            list($width,$height)=$ii;


            if($height>$max_w || $width>$max_w)
            {
              echo "<li> resizing $file to $max_w ...";
              
              ini_set('memory_limit', '256M');
              
              $gd=new GD($file);
              $gd->fittedResize($max_w,$max_w);
              $gd->writeTo($targetfile);
              if(file_exists($targetfile))
                unlink($file);
              else
                die('cannot resize file');
            }
            else
            {
              rename($file,$targetfile);
            }

          }
          else
          {
            rename($file,$targetfile);  // no max_w ? do not resize file
          }
          echo "<li>resized base image";
          $ai=new Gallery_Image($gallery->FileDir.'/'.basename($targetfile));
          $ai->setGallery($gallery);
          $ai->createAllFormattedImages();

          echo "<li>moved to $targetfile";
          return 'OK';

        }
        else
         return "file $file or gallery {$p['ID']} does not exist, but anyway, OK";

      }
      
      
      public function ajaxQueueInfo()
      {
        $html="";
        $this->record=Dataobject::get_by_id($this->myClass,Controller::curr()->urlParams['ID']);
        $num=sizeof($this->record->ImportQueue);
      
        if($num>0)
        {
          $html="<div class='space queueinfo'>
            Images are being processed, please wait
            <br>$num image(s) in queue
            </div>
            <script>
            if(images_in_queue!=$num)
            refresh_imagelist();
          images_in_queue=$num;
          window.setTimeout(refresh_queueinfo,2500);
          </script>
            ";
        }
      
        $html.="
          <script>
          refresh_imagelist();
        </script>
          ";
      
        echo $html;
        exit();
      }

      public function ajaxSetFrontImage()
      {
        $this->record=Dataobject::get_by_id($this->myClass,Controller::curr()->urlParams['ID']);
        $filename=array_get($_GET,'file');
        $this->record->FrontImage=$filename;
        $this->record->write();
        echo "Image $filename als startbild gesetzt";
      }

      public function ajaxDelete()
      {
        if($filename=array_get($_GET,'file'))
        {
          $this->record->removeImage($filename);
        }
      }

      public function ajaxImageList()
      {
        $this->record=Dataobject::get_by_id($this->myClass,Controller::curr()->urlParams['ID']);
        echo Controller::curr()->renderWith('MwGallery_ajaxImageList');
        exit();
      }


      // function IframeEditImage()
      // {
      // 
      //   $this->record=Dataobject::get_by_id($this->myClass,Controller::curr()->urlParams['ID']);
      //   $filename=array_get($_GET,'file');
      // 
      //   mwForm::preset($this->record->getImageDataForFile($filename));
      // 
      //   $p=Array(); // ------- new field --------
      //   $p['label']="Titel";
      //   $p['fieldname']="Title";
      //   $fields[$p['fieldname']]=mwForm::render_field($p);
      // 
      // 
      // 
      // 
      //   $p=Array(); // ------- new field --------
      //   $p['label']="Copyright";
      //   $p['fieldname']="Copyright";
      //   $fields[$p['fieldname']]=mwForm::render_field($p);
      // 
      //   $html="
      //     <div style='padding-top:5px'>
      //     <a href='javascript:jQuery(\"#dataform\").submit();' class='iconbutton save'><span></span>Speichern</a>
      //   <a href='javascript:parent.popupWindow.hide();' class='iconbutton cancel'><span></span>Abbrechen</a>
      //     </div>
      //     <form id='dataform' method='POST' action='{$this->URLSegment}/IframeSaveImage/{$this->record->ID}'>
      //     <input type='hidden' name='filename' value='$filename'>
      //     <h3>Bild</h3>
      //     <div class='formsection'>
      //     <table class='ftable'>
      //     ".implode("\n",$fields)."
      //     </table>
      //     </div>
      // 
      //     </form>
      //     ";
      // 
      //   $this->summitSetTemplateFile("main","BackendPage_iframe");
      //   return Array('Form'=>$html);
      // }
      // 
      // 
      // 
      // public function IframeSaveImage()
      // {
      //   $this->record=Dataobject::get_by_id($this->myClass,Controller::curr()->urlParams['ID']);
      //   $filename=array_get($_REQUEST,'filename');
      // 
      //   if(array_get($_POST,'fdata'))
      //   {
      //     $this->record->setImageDataForFile($filename,array_get($_POST,'fdata'));
      //     $this->record->write();
      //   }
      //   return "<script>parent.popupWindow.hide();</script>";
      // 
      // }
      // 
      // 
      // 
      public function handleIncomingValues($incoming = NULL)
      {
      
      
        switch (array_get($_POST,'NextAction')) {
          case 'alldelete':
          $this->record->removeAllImages();
          break;
        }
      
        parent::handleIncomingValues();
      }
      // 
      // 
      // public function Items()
      // {
      //   $items=DataObject::get('Album',"","","",$this->getPagingSQL());
      //   return $items;
      // }


    }
