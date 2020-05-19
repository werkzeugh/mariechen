<?php 
use SilverStripe\Assets\Filesystem;
use SilverStripe\Control\Director;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Versioned\Versioned;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Assets\Image;



/**
* 
*/
class MwPhotostackExtension extends DataExtension
{
    
    
    public function hasMethod($str)
    {
    
        
        return $this->owner->hasMethod($str);
    }
    
    public function MwPhotostack_GalleryTab_enabled($placename='MainContent')
    {
     
        if(!$this->owner->ID)
            return TRUE;
      
        if($this->owner->C4P->numElementsInPlace($placename,'MysitePhotoStackExtension_C4P_Gallery')>0)
            return TRUE;
        
        return FALSE;
    }


    public function photostack_UploadDir()
    {

      $dir=$this->owner->photostack_FileDir().'/incoming';
      if(!file_exists($dir))
        mkdir($dir, 02775);

      return  $dir;
    }
    
    public function photostack_FileDir()
    {

      $albumbasedir=Director::baseFolder().'/assets/photostack';
      if(!file_exists($albumbasedir))
        mkdir($albumbasedir, 02775);

      $albumdir=$albumbasedir.'/'.$this->owner->ID;

      if(!file_exists($albumdir))
        mkdir($albumdir, 02775);

      return $albumdir;

    }
    
    var   $cachedImageData;
    public function getImageData()
    {
      if(!$this->cachedImageData)
        $this->cachedImageData= unserialize($this->ImageDataSerialized);

      if(!is_array($this->cachedImageData))
        $this->cachedImageData=Array();

      return $this->cachedImageData;
    }
    
    public function getImageDataForFile($filename)
    {
      $data=$this->getImageData();
      return $data[$filename];
    }
    
    public function getCleanFilename($filename)
    {
      return preg_replace('#[^a-z0-9_.-]#','',strtolower($filename));
    }
    
    public function getImportQueue()
    {

      $images=Array();
      if($this->photostack_UploadDir())
      {
        $files = scandir($this->photostack_UploadDir(), 1);
        foreach ($files as $filename) 
        {
          if(stristr($filename,'.jpg'))
            $images[]=$filename;
        }
      }

      return $images;
    }
    
    
    var $photostack_myimages;
    public function photostack_Images()
    {


      if(!$this->photostack_myimages)
      {     
        $this->photostack_myimages=new ArrayList();
        if($this->photostack_FileDir())
        {

          $files = scandir($this->photostack_FileDir(), 1);
          sort($files);
          foreach ($files as $filename) 
          {
            if(stristr($filename,'.jpg'))
            {           
              $ai=new MysitePhotoStack_Image($this->photostack_FileDir().'/'.$filename);
              $ai->setGallery($this);
              $this->photostack_myimages->push($ai);
            }
          }
        }
      }

      return $this->photostack_myimages;
    }
    
    public function photostack_CacheDir()
    {

      $dir=$this->photostack_FileDir().'/cache';

      if(!file_exists($dir))
        mkdir($dir, 02775);

      return  $dir;
    }
 
    public function photostack_removeAllImages()
    {
      $images=$this->photostack_Images();
      foreach ($images as $img) 
      {
        $this->removeImage(basename($img->Filename));
      }
    }
    
    public function removeImage($filename)
    {
      if($filename)
      {
        unlink($this->photostack_FileDir().'/'.$filename);
        $cmd="rm -f {$this->photostack_CacheDir()}/*-$filename";
      }
      shell_exec($cmd);
    }
    
    
    public function removeImageDirs()
    {
      $cmd="rm -rf {$this->photostack_FileDir()}";
      shell_exec($cmd);
    }

  	public function onBeforeDelete() {
  		parent::onBeforeDelete();
        $this->removeImageDirs();
    }
    




        
    public function MwPhotostack_GalleryTab_dispatch($controller)
    {
   
      if (array_get($_GET,'photostack_receive')) 
      {
        return $this->owner->photostack_receive();
      }

      if (array_get($_GET,'gallery_imagelist')) 
      {
        return $this->owner->gallery_ajaxImageList();
      }
      
      if (array_get($_GET,'rescan_incoming')) 
      {
        return $this->owner->photostack_rescan_incoming();
      }

      if (array_get($_GET,'gallery_queueinfo')) 
      {
        return $this->owner->gallery_ajaxQueueInfo();
      }

      if (array_get($_GET,'ajaxdelete')) 
      {
        return $this->owner->gallery_ajaxDelete();
      }
     
      switch (array_get($_POST,'NextAction')) {
        case 'alldelete':
        $controller->record->photostack_removeAllImages();
        break;
      }

      $p['FiltersJSON']=json_encode(Array('title' => 'Image-files','extensions' => 'jpg'));

      $p['UploadURL']=$controller->CurrentURL().'?photostack_receive=1';

      $uploadcode=$controller->customise($p)->renderWith("BpMwFile_UploadWidget");

      $h="

        <script type=\"text/javascript\" charset=\"utf-8\">
           var onPluploadComplete = function()
           {
             $(\"#uploader\").html('upload complete, please wait ...');
             document.location.href='{$controller->CurrentURL()}';
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
          background-repeat:no-repeat;
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
              $('#imagelist').load('{$controller->CurrentURL()}?gallery_imagelist=1');
            }

            refresh_queueinfo=function()
            {
              $('#queueinfo').load('{$controller->CurrentURL()}?gallery_queueinfo=1');
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

        
        
            $('.imagelist div.thumbnail').live('mouseover',function ()
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


      //hidden step to import uploaded files
      public function photostack_rescan_incoming($value='')
      {
        $ret= "check for uploaded files";
    
        $q=$this->owner->getImportQueue();

        if(is_array($q))
        foreach($q as $f)
        {
      
           $filename=$this->owner->photostack_UploadDir().'/'.$f;
           $ret.="<li>adding job for importing $filename";
           MwJob::addJob(get_class($this),'photostack_import2gallery',Array('ID'=>$this->owner->ID,'file'=>$filename));
        }
    
        return $ret;
      }
   
   
      public function gallery_ajaxQueueInfo()
      {
        $html="";

        $num=sizeof($this->owner->ImportQueue);
     
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
   
   
      public function photostack_import2gallery($p)
      {
      	Versioned::set_stage("Live");
       
        $PhotoStackpage=DataObject::get_by_id(SiteTree::class,$p['ID']*1);

        $file=$p['file'];
       
        if($PhotoStackpage->ID && file_exists($file))
        {
          $max_w=MwGalleryPage::conf('resizeMaxLength'); //max w of resized gallery image
         
          $targetfile=$PhotoStackpage->photostack_FileDir().'/'.basename($file);
         
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
          $ai=new MysitePhotoStack_Image($PhotoStackpage->photostack_FileDir().'/'.basename($targetfile));
          $ai->setGallery($PhotoStackpage);
          $ai->createAllFormattedImages();

          echo "<li>moved to $targetfile";
          return 'OK';

        }
        else
         return "file $file or gallery {$p['ID']} does not exist, but anyway, OK";

      }
   
      public function photostack_receive()
      {
        //taken from MwFile->receive()

        // if(Controller::curr()->urlParams['ID'])
      //     $this->CurrentDirectory=MwFile::get_by_id('MwFile',Controller::curr()->urlParams['ID']);
      //   else
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

        $targetDir = $this->owner->photostack_UploadDir();

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
            if (($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) 
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

  //        $writtenFile=$this->photostack_import2gallery($targetDir . DIRECTORY_SEPARATOR . $fileName);


        $filename = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        if(preg_match('#\.jpg$#i',$filename))
          {
          
          $size               = filesize($filename);
          $mb                 = ceil($size/1024/1024);
          $this->owner->LastMessage = "the file <div><strong>{$fileobject[name]} ({$mb} MB)</strong></div> was uploaded successfully, and will now be converted.
          <script>
          refresh_queueinfo();
          </script>
          ";
          $newfilename        = $this->owner->getCleanFilename(basename($filename));
          $target_file        = $this->owner->photostack_UploadDir().'/'.$newfilename;

          rename($filename,$target_file);
          MwJob::addJob(get_class($this),'photostack_import2gallery',Array('ID'=>$this->owner->ID,'file'=>$target_file));

          $writtenFile= $target_file;
          } 



          // Return JSON-RPC response
          if($writtenFile)
            die('{"jsonrpc" : "2.0", "result" : null, "res":"'.$targetDir . DIRECTORY_SEPARATOR . $fileName.'"}');
          else
            die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to write file '.$targetDir . DIRECTORY_SEPARATOR . $fileName.' "}, "id" : "id"}');


          exit();
        }
   
   
   
   
        public function gallery_ajaxDelete()
        {
          if($filename=array_get($_GET,'file'))
          {
            $this->owner->removeImage($filename);
          }
        }

        public function gallery_ajaxImageList()
        {

          echo Controller::curr()->renderWith('MwPhotoStack_ajaxImageList');
          exit();
        }
   


        public function photostack_UsageInMB()
        {
          $cmd="du -hsx ".$this->photostack_Filedir();
          $line=trim(shell_exec($cmd));
          $parts=preg_split('#\s+#',$line);
          $size=array_shift($parts);
          return $size;
        }

        
        public function photostack_ImageCount()
           {
             return $this->photostack_Images()->count();
           }
   
   
           public function photostack_GalleryItemsForPage()
           {

               $images=$this->owner->photostack_Images();

               $length=$this->owner->getPageSize();
               if(!isset($_GET['start']) || !is_numeric($_GET['start']) || (int)$_GET['start'] < 1) $_GET['start'] = 0;
               $offset = (int)$_GET['start'];

               $ret= $images->limit($length,$offset);  

               return $ret;
     
           }
             
           public function getPageSize()
           {
               return 500;
           }
     
     
     
           public function photostack_GalleryItems()
           {
               $images=$this->owner->photostack_Images();

               if(!isset($_GET['start']) || !is_numeric($_GET['start']) || (int)$_GET['start'] < 1) $_GET['start'] = 0;
               $offset = (int)$_GET['start'];

               //$images->setPageLimits($offset,$length,$images->Count());

               $ret=$images->limit($this->owner->getPageSize(),$offset);

               return $ret;
           }
     
   


}

    
class MwPhotostackExtension_C4P_Gallery extends C4P_Element
{
   
   
    public function getController()
    {
        return Controller::curr();
    }
    
    public function setFormFields()
    {
        
        
        $imagetablink=$this->getImageTabLink();

        $p= Array(); // ------- new field --------
        $p['fieldname'] = "info";
        $p['type']      = "html";
        $p['label']     = "Form Elements";
        $p['html']      = '<div class="bootstrap space"><a class="btn btn-mini btn-primary" href="'.$imagetablink.'"><i class="icon-arrow-right icon-white"></i> upload images</a></div>';
        $this->formFields['left'][$p['fieldname']] = $p;
        
        
        $p=Array(); // ------- new field --------
        $p['fieldname'] = "ThumbnailWidth";
        $p['label']     = "Thumbnail-Width <i>px</i>";
        $p["tag_addon"] = ' placeholder="100" ';
        $p['styles']    = "width:60px";

        $this->formFields['right'][$p['fieldname']]=$p;


        $p=Array(); // ------- new field --------
        $p['fieldname'] = "ThumbnailHeight";
        $p['label']     = "Thumbnail-Height <i>px</i>";
        $p["tag_addon"] = ' placeholder="100" ';
        $p['styles']    = "width:60px";
        $this->formFields['right'][$p['fieldname']]=$p;
    
    
      return $ret;
    }
    
  
    public function getImageTabLink()
    {
                
        $classname=get_class($this->Mainrecord).'BEController';
        
        $c=singleton($classname);
        $c->dataRecord=$this->Mainrecord;
        
        $rawtabs=$c->TabItems();
        foreach ($rawtabs as $tab) {
            if(preg_match('#PhotoStack#i',$tab->URLSegment))
                return "/BE/Pages/edit/{$this->Mainrecord->ID}/".$tab->URLSegment;
        }

        return NULL;
        
    }
    
    
    public function myThumbnailWidth()
    {
        return $this->ThumbnailWidth?$this->ThumbnailWidth:100;
    }

    public function myThumbnailHeight()
    {
        return $this->ThumbnailHeight?$this->ThumbnailHeight:100;
    }
    
    public function getTpl($style='default')
    {
        
        $GLOBALS['myThumbnailWidth']=$this->myThumbnailWidth();
        $GLOBALS['myThumbnailHeight']=$this->myThumbnailHeight();
            
        return <<<'HTML'
        <style type="text/css" media="screen">
  
        .photostack-item {
          display:inline-block;  zoom: 1;  *display: inline;
          margin-right:9px;
          margin-bottom:9px;
        }

        #cboxLoadedContent  {
        overflow:hidden
        }

        </style>

        <div class='gallery'>
    
                       <% loop $Mainrecord.photostack_GalleryItems %>
                           <a class='photostack-item pos-$Pos' href="$ZoomImage.Link" rel='colorbox' title='$CombinedTitle' $HoverCode>$Thumbnail</a>
                           </a>
                       <% end_loop %>

        </div>


        <script type="text/javascript" charset="utf-8">
        $(document).ready(function() {
          $("a[rel='colorbox']").colorbox({current:"Image {current} of {total}",slideshow:true,slideshowSpeed:4000,slideshowAuto:false,slideshowStart:"start slideshow",slideshowStop:"stop slideshow"});
         });
        </script>

HTML;
        
    }

    
    public function PreviewTpl()
     {
       return '
           
           Image-Gallery
           <% if  $Mainrecord.photostack_ImageCount %> 
            ($Mainrecord.photostack_ImageCount Images)
           <% else %>
               (please upload images)
           <% end_if %>
       ';
     }
    
}




class MwPhotoStack_Image extends Image
{

    var $PhotoStack;

    public function __construct($filename = null, $isSingleton = false) {
        parent::__construct(array(), $isSingleton);
        $this->Filename = str_replace(Director::baseFolder().'/','',$filename);
        $this->ID=99;
    }

    public function VisibleOnPage()
    {
        static $min,$max;
        if(!isset($min))
            $min=array_get($_GET,'start')+1;
        if(!isset($max))
            $max= $min + Controller::curr()->getPageSize()-1;

        $pos=$this->Pos();
        if($pos>=$min && $pos <= $max)
            return 1;
        else
            return 0;
    }

    public function getCombinedTitle()
    {
        return $this->Title." \n".$this->Copyright."";
    }

    public function setGallery($PhotoStack)
    {
        $this->PhotoStack=$PhotoStack;
    }

    public function getTitle()
    {
        $data=$this->PhotoStack->getImageDataForFile($this->Name);
        if($data['Title'])
            return $data['Title'];
        else
            return "";
    }


    public function getText()
    {
        return nl2br($this->getField('Text'));
    }

    public function getCopyright()
    {
        $data=$this->PhotoStack->getImageDataForFile($this->Name);
        if($data['Copyright'])
            return '&copy;'.$data['Copyright'];
        else
            return $this->PhotoStack->NiceCopyright;
    }


    public function getName()
    {
        return basename($this->Filename);
    }

    public function NameEscaped()
    {
        return urlencode($this->Name);
    }


    function cacheFilename($format, $arg1 = null, $arg2 = null) {
        $folder = $this->PhotoStack->photostack_CacheDir();

        $folder = str_replace(Director::baseFolder().'/','',$folder);

        $format = $format.$arg1.$arg2;

        return $folder . "/$format-" . $this->Name;
    }
  
  

    // Prevent this from doing anything in the database
    public function requireTable() {

    }

    public function createAllFormattedImages()
    {
        $this->Thumbnail();
        $this->CMSThumbnail();
        $this->ZoomImage();
    }

    

    
    public function myThumbnailWidth()
    {
        return $GLOBALS['myThumbnailWidth']?$GLOBALS['myThumbnailWidth']:100;
    }

    public function myThumbnailHeight()
    {
        return $GLOBALS['myThumbnailHeight']?$GLOBALS['myThumbnailHeight']:100;
    }
    
    function Thumbnail($width=0,$height=0)
    {
        return $this->getFormattedImage('CroppedImage', $this->myThumbnailWidth(),$this->myThumbnailHeight());
    }


    function CMSThumbnail()
    {
        return $this->getFormattedImage('CroppedImage', 100,80);
    }

    function ZoomImage()
    {

        $limit=550;
    
        if($this->getHeight() > $limit)
            return $this->getFormattedImage('SetHeight', $limit);
        else 
            return $this;
    }
  
    public function SetFittedSize($a,$b,$c=FALSE)
    {
      
        $w=$this->getWidth();
        $h=$this->getHeight();
        if($a>$w)
            $a=$w;
        if($b>$h)
            $b=$h;
      
        return $this->SetRatioSize($a,$b,$c);
    }  
  
   
    
    
    
    
}
