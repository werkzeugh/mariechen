<?php

use SilverStripe\Control\Director;
use SilverStripe\Assets\Filesystem;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\Control\Controller;
//use PageController;
use SilverStripe\Assets\Image;


ini_set('gd.jpeg_ignore_warning', 1);


class MwGalleryPage extends Page
{


  private static $db              = Array(
    'Copyright'           =>'Varchar(255)',
    'LastMessage'         =>'Text',
    'Intro'               =>'Text',
    'Text'                =>'HTMLText',
    'ImageDataSerialized' =>'Text',
    'FrontImage'          =>'Varchar(255)',
    );



    static $conf=Array(
      'resizeMaxLength'=>1280,
      );

    static public function conf($key)
    {
      return self::$conf[$key];
    }

    static public function setConf($key,$value)
    {
      self::$conf[$key]=$value;
    }

    public function myFrontImage()
    {

      if(!$this->getField('FrontImage'))
      {

        if($this->Images)
          return $this->Images->First()->Name;
      }
      else
        return $this->getField('FrontImage');

    }


    public function onBeforeWrite()
    {
      parent::onBeforeWrite();

      if(!$this->ID)
      {
        $this->Title="neues Foto-Album";
        $this->Visible=0;
      }
    }

    public function getMyValidationErrors()
    {
      $err=Array();

      return $err;
    }


    //front image for use from outside
    public function Image()
    {
      $ai=new Album_Image($this->FileDir.'/'.$this->myFrontImage());
      $ai->setAlbum($this);
      return $ai;
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

    public function setImageData($val)
    {
      $this->ImageDataSerialized=serialize($val);
      unset($this->cachedImageData);
    }

    public function setImageDataForFile($filename,$values)
    {
      $data=$this->getImageData();
      if($filename && is_array($values))
      {
        $data[$filename]=$values;
      }

      $this->setImageData($data);
    }

    public function getImageDataForFile($filename)
    {
      $data=$this->getImageData();
      return $data[$filename];
    }

    public function getNiceCopyright()
    {
      if($txt=$this->Copyright)
        $txt="© $txt";

      return $txt;
    }

    public function getFileDir()
    {

      $albumbasedir=Director::baseFolder().'/assets/galleries';
      if(!file_exists($albumbasedir))
        mkdir($albumbasedir, 02775);

      $albumdir=$albumbasedir.'/'.$this->ID;

      if(!file_exists($albumdir))
        mkdir($albumdir, 02775);

      return $albumdir;

    }

    public function getImportQueue()
    {

      $images=Array();
      if($this->UploadDir)
      {
        $files = scandir($this->UploadDir, 1);
        foreach ($files as $filename)
        {
          if(stristr($filename,'.jpg'))
            $images[]=$filename;
        }
      }

      return $images;
    }

    public function getUsageInMB()
    {
      $cmd="du -hsx ".$this->Filedir;
      $line=trim(shell_exec($cmd));
      $parts=preg_split('#\s+#',$line);
      $size=array_shift($parts);
      return $size;
    }


    public function getImages()
    {


      if(!$this->myimages)
      {
        $this->myimages=new ArrayList();
        if($this->FileDir)
        {

          $files = scandir($this->FileDir, 1);
          sort($files);
          foreach ($files as $filename)
          {
            if(stristr($filename,'.jpg'))
            {
              $ai=new Gallery_Image($this->FileDir.'/'.$filename);
              $ai->setGallery($this);
              $this->myimages->push($ai);
            }
          }
        }
      }

      return $this->myimages;
    }

    public function getImageCount()
    {
      return $this->Images->count();
    }

    public function getUploadDir()
    {

      $dir=$this->FileDir.'/incoming';

      if(!file_exists($dir))
        mkdir($dir, 02775);

      return  $dir;
    }


    public function getCacheDir()
    {

      $dir=$this->FileDir.'/cache';

      if(!file_exists($dir))
        mkdir($dir, 02775);

      return  $dir;
    }

    public function getCleanFilename($filename)
    {
      return preg_replace('#[^a-z0-9_.-]#','',strtolower($filename));
    }


    public function removeImage($filename)
    {
      if($filename)
      {
        unlink($this->FileDir.'/'.$filename);
        $cmd="rm -f {$this->CacheDir}/*-$filename";
      }
      shell_exec($cmd);
    }

    public function removeAllImages()
    {
      $images=$this->Images;
      foreach ($images as $img)
      {
        $this->removeImage(basename($img->Filename));
      }
    }


    public function getTitleText($value='')
    {

      return "<em>titeltext</em>";
    }

    public function removeImageDirs()
    {
      $cmd="rm -rf {$this->FileDir}";
      shell_exec($cmd);
    }

  	public function onBeforeDelete() {
  		parent::onBeforeDelete();
      $this->removeImageDirs();
    }


}

class MwGalleryPageController extends PageController
{

  public function index(SilverStripe\Control\HTTPRequest $request)
   {

     return Array();
   }


   public function PageSizeSelector()
   {
     return "";
   }

   public function getPageSize()
   {
     return 36;
   }

   public function liste()
   {

     $c=Array();
     return $c;
   }


   public function ItemsForPage()
   {

     $images=$this->dataRecord->getImages();

     $length=$this->getPageSize();
     if(!isset($_GET['start']) || !is_numeric($_GET['start']) || (int)$_GET['start'] < 1) $_GET['start'] = 0;
     $offset = (int)$_GET['start'];

     $ret= $images->limit($length,$offset);

     return $ret;

     }



     public function Items()
      {
        $images=$this->dataRecord->getImages();

        // if(!isset(array_get($_GET,'start')) || !is_numeric(array_get($_GET,'start')) || (int)array_get($_GET,'start') < 1) array_get($_GET,'start') = 0;
        // $offset = (int)array_get($_GET,'start');
        //
        //$images->setPageLimits($offset,$length,$images->count());
        $items=new PaginatedList($images, Controller::curr()->getRequest());
        $items->setPageLength(Controller::curr()->getPageSize());
        return $items;
      }


}


class MwGallery_Image extends Image
{


  var $Gallery;

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
    return trim($this->Text." \n".$this->Copyright);
  }

  public function setGallery($gallery)
  {
    $this->Gallery=$gallery;
  }

  public function getTitle()
  {
    $data=$this->Gallery->getImageDataForFile($this->Name);
    if($data['Title'])
      return $data['Title'];
    else
      return "";
  }


  public function getText()
  {
      $data=$this->Gallery->getImageDataForFile($this->Name);
      if($data['Text'])
        return $data['Text'];
      else
        return "";
  }

  public function getCopyright()
  {
    $data=$this->Gallery->getImageDataForFile($this->Name);
    if($data['Copyright'])
      return '&copy;'.$data['Copyright'];
    else
      return $this->Gallery->NiceCopyright;
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
    $folder = $this->Gallery->CacheDir;

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

  function Thumbnail($width,$height)
  {
    return $this->getFormattedImage('CroppedImage', 120,100);
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



