<?php

use Mwerkzeug\MwRequirements;
use SilverStripe\View\Requirements;
use SilverStripe\i18n\i18n;
use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ViewableData;

class MwLink  extends ViewableData
{

  var $data;
  static $cache;
  public function __construct($data=NULL)
  {
    $this->data=$data;
  }
  
  public function getField($fieldname)
  {
      return $this->data[$fieldname];
  }
  
  public function toMap()
  {
    return $this->data;
  }

  public function getMenuTitle()
   {
      return $this->Title;
   } 

   public function ReadableUrl()
   {
    if($this->data['type']=='email')
     return $this->data['email'];
    else
     return $this->Link();
   }
 
  public function getTitle()
   {
    if($this->data['type']=='email')
     return 'E-Mail';
    else
     return 'external link';
   } 
   
  public function Link($action = NULL)
  {
    if($this->data['type']=='email')
     return 'mailto:'.$this->data['email'];
    else  
      return $this->data['url'];
  }

  public function getTarget()
  {
    if($this->data['type']=='externalurl') {
      if(strstr($this->Link(),'://'))  {
        return "_blank";
      }  
    } else {
      return '';
    }
  }


  public function getTargetAttribute()
  {
    if($t=$this->Target)
      return " target=\"$t\" ";
    else
      return '';
  }
  
  static public function includeRequirementsForMwLinkField()
  {
    Requirements::javascript("Mwerkzeug/javascript/jquery.ui.subclass.js");
    Requirements::javascript('Mwerkzeug/javascript/MwLinkField_jqueryui_widget.js');
    MwRequirements::javascript('mysite/javascript/MwLinkField_jqueryui_widget.js'); //try to load custom class

    if($locale=i18n::get_locale())
    {
      $lang=substr($locale,0,2);
      MwRequirements::javascript("Mwerkzeug/javascript/MwLinkField_jqueryui_widget-{$lang}.js");
      MwRequirements::javascript("mysite/javascript/MwLinkField_jqueryui_widget-{$lang}.js"); //try to load custom translations
    }

    Requirements::css('Mwerkzeug/css/MwLinkField.css');
    MwRequirements::css('mysite/css/MwLinkField.css'); //try to load custom css

  }

  static function resolveLinks($txt)
  {
    return singleton('MwLink')->resolveLinksNonStatic($txt);

  }

  public function resolveLinksNonStaticCallBack($matches)
  {
      $mwlink=$matches[1];
      
      $obj=MwLink::getObjectForMwLink($mwlink);
      if($obj)
      {
        $mwlink=$obj->Link();
      }
      else
        $mwlink='#page_not_found';
      

      $ret="href=\"$mwlink\"{$obj->TargetAttribute}";

      return $ret;
  }

  public function resolveLinksNonStatic($txt)
  {
     $patterndocumentLinks ='/href="(mwlink:\S+)"/i'; 

     $txt=preg_replace_callback($patterndocumentLinks,array($this, 'resolveLinksNonStaticCallBack'),$txt);
     return $txt;

  }

  static function getURLForMwLink($link)
  {
    $obj=self::getObjectForMwLink($link);
    if($obj){
      return $obj->Link();
    }
  }

  static function getAbsoluteURLForMwLink($link)
  {
    $obj=self::getObjectForMwLink($link);
    if($obj) {
      if($obj->hasMethod('AbsoluteLink')) {
        return $obj->AbsoluteLink();
      }
      return $obj->Link();
    }
  }

  // get targeted object (do not mix with html-targets !)
  static function getTargetForMwLink($link)
  {
    $obj=self::getObjectForMwLink($link);
    if($obj)
      return $obj->Target;
  }


  static function getTargetAttributeForMwLink($link)
  {
    $obj=self::getObjectForMwLink($link);
    if($obj)
      return $obj->TargetAttribute;
  }

  static function getTargetAttributeValueForMwLink($link)
  {
    $obj=self::getObjectForMwLink($link);
    if($obj)
      return $obj->Target;
  }



  static  function getObjectForMwLink($link)
  {

    if(!isset(self::$cache[$link]))
    {

        $targetlink=NULL;
        if(preg_match('#^mwlink://([a-zA-Z0-9]+)-([0-9]+)$#',trim($link),$m))
        {
          $classname=$m[1];
          $id=$m[2];
          if(ClassInfo::exists($classname))
          {
            $obj=DataObject::get_by_id($classname,$id);
            $targetlink= $obj;
          }
          elseif(ClassInfo::exists('SilverStripe\\CMS\\Model\\'.$classname))
            {
                $obj=DataObject::get_by_id('SilverStripe\\CMS\\Model\\'.$classname,$id);
                $targetlink= $obj;
            }
        }
        elseif(preg_match('#^mwlink://external\?(.*)$#',trim($link),$m))
        {
          
          $m[1]=str_replace('&amp;','&',$m[1]);
          parse_str($m[1],$parts);
          $parts['type']='externalurl';
          $targetlink= new MwLink($parts);

          
        }
        elseif(preg_match('#^mwlink://email:(.*)$#',trim($link),$m))
        {
          $email=trim($m[1]);
          if(strstr($email,'@'))
          {
            $targetlink= new MwLink(Array('type'=>'email','email'=>$email));
          }
          
        }

       self::$cache[$link]=$targetlink;
    }
    return self::$cache[$link];

  }


}
