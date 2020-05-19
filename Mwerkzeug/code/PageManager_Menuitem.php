<?php

use SilverStripe\Security\Permission;
use SilverStripe\Security\Member;
use SilverStripe\View\ViewableData;

class PageManager_Menuitem extends ViewableData
{

  var $key;
  var $Page;
  var $children=Array();

  public function __construct($key,$Page)
  {
    $this->key=$key;
    $this->Page=$Page;
    // if(!preg_match('(create_|drag|delete|hide|publish)',$key)) {
    //   $this->disabled=1;
    // }
  }

  public function addChild($action)
  {
    $this->children[]=$action;
  }

  public function getLabel()
  {
    $str=MwLang::get('backend.labels.actionmenu_cmd_'.$this->key);
    if(strstr($str,'##')) {
      $str=$this->getCustomMenuItemField('label',$str);

      if(strstr($str,'##')) {
       $str=$this->key;
      }

    }
    if(strstr($str,'devtool') && $str!='devtools') {
      $str=str_replace('devtool','',$str);
    }

    if ($this->children) {
       $str.="...";
    }
    return $str;
    
  }

  public function getLongLabel()
  {
    $str=MwLang::get('backend.labels.actionmenu_cmd_l_'.$this->key);
    if(strstr($str,'##')) { 
      $str=$this->getLabel();
    }

    return $str;

  }

  public function isVisible()
  {
    $visible=false;
    if(preg_match('(create_|drag|delete|preview|duplicate|rename|copy|paste)',$this->key)) {
        $visible=true;
    } elseif(preg_match('(devtool)',$this->key)) {
        $visible=$this->DeveloperAccessGranted();
    } 


    switch ($this->key) {
      case 'edit_page_permissions':
        $visible=$this->Page->hasMethod('C4P_Place_PagePermissions') && Permissions::canDoAction('editPagePermissions',$this->Page,$this,false);
      break;
      case 'hide_page':
         $visible=$this->Page->Hidden?false:true;
        break;
      case 'publish_page':
         $visible=$this->Page->Hidden?true:false;
        break;
      
    }

    $visible=$this->getCustomMenuItemField('visible',$visible);


    return $visible;
  }


  public function isDisabled()
  {

    $context=array('menuItem'=>$this);

    switch ($this->key) {
        case 'delete_page':
        case 'edit_page_permissions':
            $isPermitted=Permissions::canDoAction('deletePage',$this->Page,$context);
          break;
        case 'paste_page_after':
        case 'paste_page_before':
            if ($this->numPagesOnClipboard()>0) {
              $isPermitted=Permissions::canDoAction('createSubPage',$this->Page->Parent(),$context);
            }
          break;
        case 'move_page':
        case 'drag_page':
        case 'duplicate_page':
        case 'create_page_before':
        case 'create_page_after':
            $isPermitted=Permissions::canDoAction('createSubPage',$this->Page->Parent(),$context);
          break;
        case 'rename_page':
          $isPermitted=Permissions::canDoAction('renamePage',$this->Page,$context);
          break;
        case 'create_page_inside':
          $isPermitted=Permissions::canDoAction('createSubPage',$this->Page,$context);
          break;
        case 'paste_page_inside':
          if ($this->numPagesOnClipboard()>0) {
            $isPermitted=Permissions::canDoAction('createSubPage',$this->Page,$context);
          }
          break;
        case 'hide_page':
        case 'publish_page':
            $isPermitted=Permissions::canDoAction('setPageVisibility',$this->Page,$context);
          break;
        case 'preview_page':
        case 'preview_page_framed':
            $isPermitted=Permissions::canDoAction('previewPage',$this->Page,$context);
            break;
        case 'create_page':
        case 'paste_page':
            $isPermitted=false;
            foreach ($this->children as $subAction) {
              if(!$subAction->isDisabled()) {
                $isPermitted=true;
              }
            }
          break;
        case 'copy_page':
            $isPermitted=Permissions::canDoAction('copyPage',$this->Page,$context);
            break;
        default:
          $isPermitted=false;
          break;
      }  

      if (strstr($this->key,'devtool') && $this->DeveloperAccessGranted()) {
          $isPermitted=true;        
      }
    
      $isPermitted=$this->getCustomMenuItemField('permitted',$isPermitted);

    return  $isPermitted?false:true;

  }

  public function getCustomMenuItemField($fieldName,$currentValue)
  {
    if ($this->Page->hasMethod('actionMenuItemFields')) {
      $fields=$this->Page->actionMenuItemFields($this->key,Array($fieldName=>$currentValue));
      if (is_array($fields) && array_key_exists($fieldName, $fields)) {
        $currentValue=$fields[$fieldName];
      }
    }
    return $currentValue;
  }

  public function numPagesOnClipboard()
  {

    $clipboard=PageManager::singleton()->getClipboard();
    return sizeof($clipboard);
  }

  public function getFirstPageIdOnClipboard()
  {
       $clipboard=PageManager::singleton()->getClipboard();
       if (sizeof($clipboard)>0) {
          return $clipboard[0];
       }
      return null;
  }



  public function getCommand()
  {
    $map=$this->getCommandMap();
    $command=$map[$this->key];
    
    if(!$command) {
        $command=$this->getCustomMenuItemField('command',$command);
    }

    if(!$command) {
      if(strstr($this->key,'devtool')) {
        $command=Array(
          'name'=>'devtool_execute_cmd',
          'args'=>Array('cmd'=>lcfirst((str_replace('devtool','',$this->key)))),
        );
      } else {
        $command=Array(
          'name'=>$this->key,
        );
        
      }
    }

    return $command;
  }

  public function getIconClass()
  {
    $map=$this->getIconMap();
    $iconclass=$map[$this->key];
    if (!$iconclass) {
      $iconclass=$this->getCustomMenuItemField('iconClass',$iconclass);
    }
    return "fa fa-fw ".$iconclass;
  }

  public function getCommandMap()
  {
    $pageIdToPaste=$this->getFirstPageIdOnClipboard();

    return Array(
      'create_page_before'=>Array('name'=>'create_page','args'=>Array('mode'=>'create','position'=>'before')),
      'create_page_after'=>Array('name'=>'create_page','args'=>Array('mode'=>'create','position'=>'after')),
      'create_page_inside'=>Array('name'=>'create_page','args'=>Array('mode'=>'create','position'=>'inside')),
      'paste_page_before'=>Array('name'=>'create_page','args'=>Array('mode'=>'paste','position'=>'before','pageIdToPaste'=>$pageIdToPaste)),
      'paste_page_after'=>Array('name'=>'create_page','args'=>Array('mode'=>'paste','position'=>'after','pageIdToPaste'=>$pageIdToPaste)),
      'paste_page_inside'=>Array('name'=>'create_page','args'=>Array('mode'=>'paste','position'=>'inside','pageIdToPaste'=>$pageIdToPaste)),

      'duplicate_page'=>Array('name'=>'create_page','args'=>Array('mode'=>'duplicate','copyOf'=>$this->Page->ID,'position'=>'after')),
      'hide_page'=>Array('name'=>'update_page','args'=>Array('Hidden'=>1)),
      'publish_page'=>Array('name'=>'update_page','args'=>Array('Hidden'=>0)),
      'page_settings'=>Array('name'=>'edit_page'),
      'rename_page'=>Array('name'=>'edit_page','args'=>Array('mode'=>'rename')),
    );
  }

  public function getIconMap()
  {
    return Array(
      'preview_page_framed'=>'fa-arrow-right',
      'preview_page'=>'fa-external-link',
      'delete_page'=>'fa-trash-o',
      'page_settings'=>'fa-cog',
      'page_settings'=>'fa-cog',
      'publish_page'=>'fa-eye',
      'hide_page'=>'fa-eye-slash',
      'rename_page'=>'fa-edit',
      'edit_page_permissions'=>'fa-user',
      'page_history'=>'fa-list',
      'create_page'=>'fa-plus',
      'paste_page'=>'fa-paste',
      'duplicate_page'=>'fa-files-o',
      'create_page_before'=>'fa-long-arrow-up',
      'create_page_after'=>'fa-long-arrow-down',
      'create_page_inside'=>'fa-level-down',
      'paste_page_before'=>'fa-long-arrow-up',
      'paste_page_after'=>'fa-long-arrow-down',
      'paste_page_inside'=>'fa-level-down',
      'drag_page'=>'fa-arrows',
      'copy_page'=>'fa-copy',
      'devtools'=>'fa-rocket',
    );
  }

  public function toArray()
  {
    $ret=Array(
      'key'=>$this->key,
      'label'=>$this->getLabel(),
      'longlabel'=>$this->getLongLabel(),
      'command'=>$this->getCommand(),
      'icon'=>$this->getIconClass()
      );

    if ($this->isDisabled()) {
        $ret['_disabled']=true;
    }

    if ($this->children) {
      $ret['submenu']=PageManager::toArray($this->children);
    }

    return $ret;
    
  }

  public function DeveloperAccessGranted()
  {
    if(Member::currentUser()->isDeveloper()) {
      return true;
    }   

  }

}






?>
