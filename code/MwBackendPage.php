<?php

use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\View\SSViewer;
use SilverStripe\View\Requirements;
use SilverStripe\i18n\i18n;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Session;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ArrayData;
use SilverStripe\Security\PermissionProvider;

class MwBackendPage extends MwSiteTree {

	private static $db = array(
	);

	private static $has_one = array(
	);


    static $conf=Array( //defaults:
       'skipDefaultBootstrapCSS'=>FALSE
       );

     static public function conf($key)
     {
       return self::$conf[$key];
     }

     static public function setConf($key,$value)
     {
       self::$conf[$key]=$value;
     }
}


class MwBackendPage_NavItem extends ArrayData {

  var $data;
  function __construct($url,$data)
  {
    $this->data=$data;
    $this->data['Url']=$url;
  }




  function Title()
  {
    return $this->data['Title'];
  }

  function Link($action = NULL)
  {
     if($this->data['Status']=='inactive')
          return "#";

    if($this->data['Redirect']=='first_subitem')
    {
      $nav=Controller::curr()->getNavigationStructure();
      $subitems=$nav[$this->data['Url']]['subitems'];
      $firstSubitemUrl=array_shift(array_keys($subitems));
      return $firstSubitemUrl;
    }
    else
      return $this->data['Url'];
  }

  public function Children()
   {
     $s=Controller::curr()->getNavigationStructure();
     $ds=new ArrayList();
     $myNav=$s[$this->data['Url']];
     if($myNav['subitems'])
       foreach ($myNav['subitems']as $url=>$navitem) {
         $ds->push(new MwBackendPage_NavItem($url,$navitem['data']));
       }

     return $ds;
   }

   public function FunctionName($value='')
   {
       # code...
   }

  function LinkingMode()
  {
    if( $this->data['active']
        || Controller::curr()->mainurl==$this->data['Url']
        || Controller::curr()->suburl==$this->data['Url']
        || (Controller::curr()->hasMethod('navIsActive') && Controller::curr()->navIsActive($this->data))
        )
     return "current";
    else
     return "link";
  }

  function CssClass()
  {
    if($this->data['Status']=='inactive')
        return "muted";

    if( $this->data['active']
        || Controller::curr()->mainurl==$this->data['Url']
        || Controller::curr()->suburl==$this->data['Url']
        || (Controller::curr()->hasMethod('navIsActive') && Controller::curr()->navIsActive($this->data))
        )
     return "active";
    else
     return "";
  }



}


?>
