<?php


use SilverStripe\Security\Member;
use SilverStripe\Security\Group;
use SilverStripe\ORM\DataObject;

class Permissions  {

  static public function engine()
  {
    return singleton('PermissionsEngine');
  }

  static public function canShowFormField($name,$p,&$object)
  {
      return self::engine()->canShowFormField($name,$p,$object);
  }

  static public function canDoAction($actionName,&$object, &$context=Array(),$defaultValue=true)
  {
      return self::engine()->canDoAction($actionName,$object, $context, $defaultValue);
  }

  static public function canDoActionOnPage($actionName,&$page, &$context=Array())
  {
      return self::engine()->canDoActionOnPage($actionName,$page, $context);
  }

  static public function  canShowPageTab($tabName,&$page, $defaultValue=true) 
  {
      return self::engine()->canShowPageTab($tabName,$page, $defaultValue);
  }

  static public function canDoActionOnC4P($actionName,&$c4p, &$context=Array())
  {
      return self::engine()->canDoActionOnC4P($actionName,$c4p, $context);
  }
  
}

class PermissionsEngine {

  public function CurrentUser()
  {
      return Member::currentUser();
  }

  public function canShowPageTab($tabName,&$object,$defaultValue=true) {

    $user=$this->CurrentUser();
    if(!$user) {
     return false;
   }

    /* check back permission with object */
    if (is_object($object) && $object->hasMethod('getPermission')) {
      $permission=$object->getPermission('showPageTab',Array('name'=>$tabName,'fieldConfig'=>$fieldConfig,'object'=>&$object));
      if (in_array($permission,array('allow','deny'),true)) {
        $ret=$permission;
      }
    }

    if($ret===NULL && $user->hasMethod('getPermission')) {
      $ret=$user->getPermission('showPageTab',Array('name'=>$tabName,'page'=>$object));
    }
    if($ret==='allow') {
      $ret=true;
    }
    if($ret==='deny') {
      $ret=false;
    }
    if($ret===NULL) {
      $ret=$defaultValue; /* allow all by default */
    }
   return $ret;

  }

  public function canShowFormField($fieldName,&$fieldConfig, &$object)
  {

    $ret=NULL;

    $user=$this->CurrentUser();
    if(!$user) {
      $ret=false;
    } else {

      /* check back permission with object */
      if (is_object($object) && $object->hasMethod('getPermission')) {
        $permission=$object->getPermission('showFormField',Array('name'=>$fieldName,'fieldConfig'=>$fieldConfig,'object'=>&$object));
        if (in_array($permission,array('allow','deny'),true)) {
          $ret=$permission;
        }
      }

      if($ret===NULL && $user->hasMethod('getPermission')) {
        $ret=$user->getPermission('showFormField',Array('name'=>$fieldName,'fieldConfig'=>$fieldConfig,'object'=>&$object));
      }
    }

    if($ret==='allow') {
      $ret=true;
    }
    if($ret==='deny') {
      $ret=false;
    }
    if($ret===NULL) {
      $ret=true; /* allow all by default */
    }

    $this->debugMsg("canDoAction: $actionName ".$object->ID." = $ret");

    return $ret;
  }


  public function debugMsg($msg)
  {
    
     // echo "\n<li>Permission: $msg";

  }

    public function canDoAction($actionName,&$object, &$context=Array(),$defaultValue=true)
    {

      //actions:
      /*
        modifyPage
        deletePage
        previewPage
        useActionMenuOnPage
        insertC4P

      */


      $ret=NULL;

      $user=$this->CurrentUser();
      if(!$user) {
        $ret=false;
      } else { 

        //check back permission with object
        if (is_object($object) && $object->hasMethod('getPermission')) {
          $permission=$object->getPermission('doAction',Array('name'=>$actionName,'object'=>$object,'context'=>$context));
          if (in_array($permission,array('allow','deny'),true)) {
            $ret=$permission;
          }
        }

        if($ret===NULL && $user->hasMethod('getPermission')) {
          $ret=$user->getPermission('doAction',Array('name'=>$actionName,'object'=>$object,'context'=>$context));
        }

        if($ret===NULL && $object && $object->hasMethod("C4P_Place_PagePermissions")) {
          $ret=$object->userCanDoActionOnPage($user,$actionName,$context,NULL);
        }
        
      }

      if($ret==='allow') {
        $ret=true;
      }
      if($ret==='deny') {
        $ret=false;
      }
      if($ret===NULL) {
        $ret=$defaultValue; 
      }

      $this->debugMsg("canDoAction: $actionName ".$object->ID." = $ret");

      return $ret;
    }

    //deprecated ??
    public function canDoActionOnPage($actionName,&$page, &$context=Array())
    {

      $user=$this->CurrentUser();
      if(!$user) {
       return false;
     }

     if($user->hasMethod('getPermission')) {
      return $user->getPermission('doActionOnPage',Array('name'=>$actionName,'page'=>$page,'context'=>$context));
    }
    return true; //allow all by default
  }

    public function canDoActionOnC4P($actionName,&$c4p, &$context=Array())
    {

      $user=$this->CurrentUser();
      if(!$user) {
          return false;
        }

        if($c4p->Locked) {
            return false;
        }
        
        if($actionName=='typeChange' && $c4p->AliasTo) {
            return false;
        }
        
        //check back permission with object
        if ($c4p->hasMethod('getPermission')) {
            $permission= $c4p->getPermission($actionName, $context);
            if ($permission==false) {
                return false;
            }
        }
        
        if($user->hasMethod('getPermission')) {
            return $user->getPermission('doActionOnC4P',Array('name'=>$actionName,'c4p'=>$c4p,'context'=>$context));
        }
    return true;
  }

}


class C4P_PagePermissionRule extends C4P_Element {

     public function setFormFields()
      {


        $p=array(); // ------- new field --------
        $p['fieldname']     = "Right";
        $p['label']         = "the right to";
        $p['no_empty_option']=1;
        $p['options']       = $this->getRights();
        $this->formFields['tabs']['rule']['items'][$p['fieldname']]=$p;

        $p=array(); // ------- new field --------
        $p['fieldname']     = "Type";
        $p['label']         = "is";
        $p['type']='radio';
        $p['default']='allow';
        $p['options']       = $this->getTypes();
        $this->formFields['tabs']['rule']['items'][$p['fieldname']]=$p;

        $p=array(); // ------- new field --------
        $p['fieldname']     = Group::class;
        $p['label']         = "for Members of this Group:";
        $p['no_empty_option']=1;
        $p['options']       = $this->getGroups();
        $this->formFields['tabs']['rule']['items'][$p['fieldname']]=$p;




    }

    public function getRights()
    {
      return Array(
          'modifyPage'=>'edit this Page',
          'viewPage'=>'view this Page',
      );
    }


    public function getTypes()
    {
      return Array(
          'allow'=>'allowed',
          'deny'=>'denied',
        );
    }

    public function RightName()
    {
      $arr=$this->getRights();
      return $arr[$this->Right];
      
    }

    public function GroupName()
    {
      $arr=$this->getGroups();
      return $arr[$this->Group];
    }

    public function TypeName()
    {
      $arr=$this->getTypes();
      return $arr[$this->Type];
    }

    public function getGroups()
    {
      
      $ret=DataObject::get(Group::class)->map('Code')->toArray();
      $ret['any_or_none']='any or no Usergroup';
      return $ret;
    }

    public function PreviewTpl()
    {
      return '
        <b>$RightName</b>  <b style="color:<% if $Type=="allow"  %>#33dd55 <% else %>#992222<% end_if %>">$TypeName</b> for Members of: <b>$GroupName</b>
     ';
    }




}
