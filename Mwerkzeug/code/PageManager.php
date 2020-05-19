<?php

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Security\Member;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\CMS\Model\RedirectorPage;
use SilverStripe\CMS\Model\VirtualPage;
use SilverStripe\Control\Controller;
use SilverStripe\View\ViewableData;

class PageManager extends ViewableData
{
  var $cache=Array();

  static public function singleton($class=NULL) {

    if (class_exists('MyPageManager')) {
      $pm=singleton('MyPageManager');
    } else {
      $pm=singleton('PageManager');
    }

    return $pm;

  }

  public function getParentsOfPage($page)
  {
    $parent = $page;
    $stack = array($parent);
    while($parent = $parent->Parent) {
      array_unshift($stack, $parent);
    }

    return $stack;    
  }

  public function executeCommand($command,$args=Array())
  {
      //returns array for jsonizing
      $ret=array('status'=>"error",'msg'=>"command not found");
      $methodName="command_".$command;
      try {
        
        if($this->hasMethod($methodName)) {
          $ret=$this->$methodName($args);
        } elseif($args['pageId']) {
           $page=$this->getPage($args['pageId']);
           if($page->hasMethod($methodName)) {
              $ret=$page->$methodName($args);
           } 
        } 
      } catch (Exception $e) {
          $ret['msg']=$e->getMessage();
      }

      return $ret;
  }



   public function AllParentIdsOfPage($record)
   {
     $arr=Array();

     if($record) {
         foreach ($record->getAncestors() as $p) {
             $arr[] = $p->ID;
         }
     }
     return $arr;
   }
   

  public function getSettingsForPageTree($mode,$record)
  {

    $settings=Array();

    if ($record && is_subclass_of($record, SiteTree::class)) {
      $settings['idOfCurrentPage']=$record->ID;
      $settings['parentIdsOfCurrentPage']=$this->AllParentIdsOfPage($record);
    }
    $settings['mode']=$mode;

    $settings['treeGroupName']=array_get($_GET,'treeGroupName');
    $tg=$this->getTreeGroups();
    if ($tg) {
      $settings['treeGroups']=$tg;
    }

    return json_encode($settings);
  }

  public function getTreeGroups()
  {
    
    if ($user=Member::currentUser()) {
      
      if ($user->hasMethod('getBackendTreeGroups')) {
        
        return $user->getBackendTreeGroups();
      }

    }
    return NULL;

  }

  public function executeCommandForJson($command,$args=Array())
  {
    $ret=$this->executeCommand($command,$args);
    $ret=self::toArray($ret);
    if(!$ret['status']) {
      if($ret) {
        $ret=Array(
          'status'=>'ok',
          'payload'=>$ret
        );      
      } else {
        $ret=Array(
          'status'=>'error',
          'msg'=>'no value returned'
        );      

      }
    }
    return $ret;
  }


  static public function toArray($item)
  {
    if(is_array($item)) {
      foreach ($item as $key=>$val){
        $item[$key]=self::toArray($val);
      }
      return $item;
    } elseif(is_object($item)) {
      if ($item->hasMethod('toArray')) {
        return $item->toArray();
      } else {
        return get_object_vars($item);
      }

    }  else {
      return $item;
    }
    
  }

 public function getPage($pageId)
  {
    if (is_numeric($pageId)) {
      return DataObject::get_by_id(SiteTree::class,$pageId);
    } elseif (is_object($pageId)  && is_subclass_of($pageId, SiteTree::class)) {
      return $pageId; /* return object if page was passed*/
    } elseif(strstr($pageId,'/')) {
       return SiteTree::get_by_link($pageId);
    }

    return NULL;

  }


  public function getDefaultPageClassForParent($parentPage, $params=Array())
  {

     $classlist=$this->getAllowedPageClassesForParent($parentPage, $params);

     if($classlist){
      foreach ($classlist as $classname => $dummy) {
        $p=singleton($classname);
        if(is_subclass_of($p,SiteTree::class)) {
         return $classname;
        }
      }
     }
     else
      return NULL;
    
  }

  public function getPublicNameForClass($classname)
  {

    if(singleton($classname)->hasMethod('getPublicClassName')) {
      $publicClassName=singleton($classname)->getPublicClassName();
      if ($publicClassName) {
        $classname=$publicClassName;
      }
    } 

    return $classname;

  }

  public function getAllowedPageClassesForParent($parentPage, $params=Array())
  {

    $parentPageId=$parentPage->ID;

    $cacheKey=$parentPageId.md5(serialize($params));

    if(!isset($this->cache[__FUNCTION__][$cacheKey])) {

      $ret=$parentPage->allowedChildren();


      if(Permission::check('ADMIN')) {
        $doNotLimitClasses=1;
      } 
      if($params['skipAdminOnly']) {
        $doNotLimitClasses=0;
      }

      if($classlist=MwPage::conf('allowedPageClasses')) {
        if(is_string($classlist))
        {
          foreach (explode(',',$classlist) as $classname) {
            $allowedClasses[$classname]=$classname;
          }
        }
      }

      $ret1=array();

      foreach ($ret as $key => $value) {

        $context=array();
        $context['type']=$value;
        if (Permissions::canDoAction('insertPageTypeUnderneath', $parentPage, $context)) {
          $readableClassName=$this->getPublicNameForClass($value);
      //skip Generic Page Types
          if(!stristr($value,'generic')) {

            if(!$allowedClasses || $allowedClasses[$value])
            {
              $ret1[$value]=$readableClassName;
            } elseif($doNotLimitClasses) {
              $ret1[$value]='_ admin only: '.$readableClassName;
            }
          }
        }
      }

      if($ret1) {
        asort($ret1);
      }

      unset($ret1[RedirectorPage::class]);
      unset($ret1[VirtualPage::class]);
      unset($ret1[SiteTree::class]);
      unset($ret1['BackendPage']);
      unset($ret1['MwBackendPage']);
      unset($ret1['MwFrontendPage']);
      unset($ret1['MwSiteTree']);
      unset($ret1['MwFrontendPage']);

      $this->cache[__FUNCTION__][$cacheKey]=$ret1;
    }


    return $this->cache[__FUNCTION__][$cacheKey];
  }



  public function command_getInfoForPageCreation($args)
  {


    if ($args['mode']=='paste' && $args['pageIdToPaste']) {
      $args['copyOf']=$args['pageIdToPaste'];
    }

    $ret=Array();
    $ret['permissions']=Array();
    $pageId=$args['pageId']*1;
    $position=$args['position'];

    $referencePage=$this->getPage($pageId);
    if ($position=='inside') {
      $parentPage=$referencePage;
      if($args['insidePosition']) {
         $ret['insidePosition']=$args['insidePosition'];
      }

    } else {
      $parentPage=$referencePage->Parent();
    }

    $ret['position']=$position;
    if ($args['copyOf']) {
      $ret['copyOf']=$args['copyOf'];
    }
    $ret['referencePage']=$this->smallDataForPage($referencePage);
    $ret['referenceNode']=$this->getNodeDataForPage($referencePage);
    $ret['parentPage']=$this->smallDataForPage($parentPage);
    $ret['allowedClassNames']=$this->HashArrayToArray($this->getAllowedPageClassesForParent($parentPage,array('skipAdminOnly'=>0)));
    $ret['defaultClassName']=$this->getDefaultPageClassForParent($parentPage,array('skipAdminOnly'=>1));
    $ret['baseUrl']=$parentPage->AbsoluteLink();


    if($args['copyOf']){
      $sourcePage=$this->getPage($args['copyOf']);
      $ret['sourceNode']=$this->getNodeDataForPage($sourcePage);
      $ret['sourcePage']=$this->mediumDataForPage($sourcePage);
      $ret['permissions']['pasteAsAlias']=Permissions::canDoAction('aliasPage',$sourcePage);
    }

    if($args['mode']=="edit" || $args['mode']=="rename"){
      $ret['sourcePage']=$this->mediumDataForPage($referencePage);
    }


    return $ret;

  }

  public function HashArrayToArray($object)
  {
    $arr=Array();
    foreach ($object as $key=>$value) {
      if(!is_array($value)) {
        $value=Array('name'=>$value);
      }
      $value['key']=$key;
      $arr[]=$value;
    }
    return $arr;
  }



  public function command_aliasPageSyncPagesUnderneath($args)
  {
    $ret=Array('args'=>$args);
    $pageId=$args['singleArgument'];
    $page=$this->getPage($pageId);
    if($page->isAlias) {
      $ret['result']=singleton('AliasPageManager')->syncPagesUnderneath($page);
    }
    return $ret;


  }

  public function command_translatedTemplate($args)
  {
    $tplName="Includes/".$args['singleArgument'];

    die(Controller::curr()->renderWith($tplName));
    
  }

  function smallDataForPage($page)
  {
    return Array(
      'url'=>$page->Link(),
      'title'=>$page->Title,
      'menuTitle'=>$page->RealMenuTitle,
      'id'=>$page->ID,
    );
    
  }

  function mediumDataForPage($page)
  {

    return Array(
     'ID'=>$page->ID,
     'Title'=>$page->Title,
     'ClassName'=>$page->ClassName,
     'MenuTitle'=>$page->RealMenuTitle,
     'URLSegment'=>$page->URLSegment,
     'ParentID'=>$page->ParentID,
     'Sort'=>$page->Sort,
     'Hidden'=>$page->Hidden,
     'ShowInMenus'=>$page->ShowInMenus,
     );
    
  }



  public function command_checkSlug($args)
  {
    $ret=Array(
      'isValid'=>true,
    );
    if($args['slug']) {
      //try to find page with that slug:
      $parentID=$args['parentId'];
      if ($args['suburl']) {
        $subParent=$this->getPage($parentID)->getSubPage($args['suburl']);
        if ($subParent) {
          $parentId=$subParent->ID;
        } else {
          return $ret;
        }
      }

      $query=DataObject::get(SiteTree::class)->filter('ParentID',$parentId)->filter('URLSegment',$args['slug']);
      if ($args['skipId']) {
        $query=$query->where("ID<>".$args['skipId']*1);
      }
      $count=$query->count();

      if($count>0) {
        $ret['slug']=$args['slug'];
        $ret['isValid']=false;
        $ret['message']=_t("backend.texts.slug_already_exists");
      }

    }


    return $ret;
  }

  public function command_createPage($args)
  {
    $ret=Array();
    $ret['paramsReceived']=$args;

    $pageData=$args['pageData'];
    $infoForPageCreation=$args['infoForPageCreation'];
    $parentPageId=$infoForPageCreation['parentPage']['id'];
    $position=$infoForPageCreation['position'];
    $insidePosition=$infoForPageCreation['insidePosition'];
    $pageclass=$pageData['ClassName'];

    if($infoForPageCreation['copyOf']){
      $page=$this->getPage($infoForPageCreation['copyOf']);
      if($page) {

        if($pageData['pasteAsAlias']) {
          unset($pageData['ClassName']);
          $newPage=singleton('AliasPageManager')->createAliasPageForSource($page->ID);
        } else {
          if ($pageData['includeSubPages']) {
           $newPage=$this->duplicatePageWithChildren($page);
          } else {
            $newPage=$this->duplicatePage($page);
          }
        }
      }
    } else {
      $newPage=new $pageclass;
    }

    /* remove all lowercase pseudo-fields */
    unset($pageData['pasteAsAlias'], $pageData['includeSubPages']);
    $newPage->update($pageData);
    
    $newPage->write();


    if ($newPage->ID) {

      if ($position=='before' || $position=='after') {
        $positionNum=$this->getPagePosition($parentPageId,$infoForPageCreation['referencePage']['id']);
        if ($position=='after') {
          $positionNum++;
        }
      } else if($position=='inside')  {
        $positionNum=0;
        if($insidePosition=='append') {
          $positionNum=1000;
        } 

      } else {
        $positionNum=0;
      }

      $this->placePageInPosition($parentPageId,$newPage,$positionNum);

      if(!$newPage->isAlias && $newPage->hasMethod('onAfterCreatePage')) {
        $ret['custom']=$newPage->onAfterCreatePage($args);
      }


      $ret['page']=$this->smallDataForPage($newPage);
    } else {
      $ret['status']='error';
      $ret['msg']='could not create page';
    }


    singleton('AliasPageManager')->onAfterCreate($newPage);

    return $ret;
  }


  public function command_movePage($args)
  {

// [pageId] => 18
// [moveInfo] => Array
//     (
//         [newparent] => 16
//         [position] => 0
//     )

    $ret=Array();

    if(is_numeric($args['moveInfo']['newparent'])) {
      $pageId=$args['pageId']*1;
      $newParentPageId=$args['moveInfo']['newparent']*1;
      $position=$args['moveInfo']['position']*1;


      if($newParentPageId>0) {
        $newParentPage=$this->getPage($newParentPageId);
      }

    }


    if($pageId>0 && ($newParentPage || $newParentPageId==0)) {
      $page=$this->getPage($pageId);
      if($page) {

        $context=array(
          'moveInfo'=>$args['moveInfo'],
          'subpage'=>$page,
        );

        if(! Permissions::canDoAction('deletePage',$page) && !Permissions::canDoAction('movePage',$page)) {
            return Array('status'=>'error', 'msg'=>_t('backend.errors.moveSourceLocked'));
        }


        if(! Permissions::canDoAction('createSubPage',$newParentPage,$context)) {
            return Array('status'=>'error', 'msg'=>_t('backend.errors.moveTargetLocked'));
        }


        $oldParentID=$page->ParentID;
        $this->placePageInPosition($newParentPageId,$page,$position);
        if($oldParentID!=$newParentPageId) {
          singleton('AliasPageManager')->onAfterParentIdChange($page);
        }

        return $pageId;
      } 
    }

     return Array(
        'status'=>'error',
        'msg'=>'page or parentPage not found'
      );
  }

  public function getPagePosition($parentPageId,$pageId)
  {
    //0-based
    $page=Dataobject::get_by_id(SiteTree::class,$pageId);
    if ($page) {
      $targetSort=$page->Sort;
      $pagesBefore=DataObject::get(SiteTree::class)->where("Sort < $targetSort")->filter('ParentID',$parentPageId)->sort('Sort','asc')->count();
      return $pagesBefore;
    }

    return NULL;
  }

//update SiteTree_Live s set s.Sort=(select BasisPaket from Portal p where s.URLSegment=p.UrlSegment and p.ClassName='Ort') where  s.ParentID in (2070,2095,2165,2485,2784,2873,3253,4698,2931,113263);
  
  public function reorderPageChildrenByField($page,$fieldname,$direction='asc')
  {
    $children=$page->AllChildren()->sort($fieldname,$direction);

    $sortKey=0;
    foreach ($children as $childPage) {
        $sortKey+=10;
        $childPage->Sort=$sortKey;
        $childPage->write();
    }


    
  }


  public function placePageInPosition($parentPageId,$pageToPosition,$targetPosition)
  {
    $pos=0;
    $targetPosition++; //adapt to have "1" as first pos

    $pageToPosition->Sort=0;
    //renumber other pages
    $list=DataObject::get(SiteTree::class)->filter('ParentID',$parentPageId)->sort('Sort','asc');
    foreach ($list as $page) {
      $pos++;
      if($pos==$targetPosition) {

        $pageToPosition->Sort=$pos*10;
        $pos++;
      }
      if($page->ID==$pageToPosition->ID) {

      } else {
        $sort=$page->Sort;
        if ($sort<>$pos*10) {
          $page->Sort=$pos*10;
        }
        $page->write();        
      }

    }

    if(!$pageToPosition->Sort)  {
      $pos++;
      $pageToPosition->Sort=$pos*10;
    }

    //add my page

    $pageToPosition->ParentID=$parentPageId;
    $pageToPosition->write();
      
    
    return $pageToPosition->Sort;

  }

  private  function command_updatePage($args)  
  {

    $page=$this->getPage($args['pageId']);
    if($page) {
      $page->update($args['pageData']);
      $page->write();

      return $this->smallDataForPage($page);

    }

    return Array('status'=>'err','msg'=>'page not found');

  }

  public function duplicatePage($page,$newValues=Array())
  { 
    /* preserve some values */
    foreach (Array('Hidden','Sort') as $fieldName) {
      if(!array_key_exists($fieldName, $newValues)) {
        $newValues[$fieldName]=$page->getField($fieldName);
      }
    }

    $clone = $page->duplicate();
    if ($clone->hasMethod('myOnBeforeDuplicate')) {
      $clone->myOnBeforeDuplicate();
    }
    $clone->update($newValues);
    $clone->write();
    
    return $clone;
  }

  public function duplicatePageWithChildren($page,$newValues=Array())
  {
    $clone = $this->duplicatePage($page,$newValues);
    if(!$clone->AliasSubPages) {
      $children = $page->AllChildren();
    }

    if($children) {
      foreach($children as $child) {
        $childClone = $this->duplicatePageWithChildren($child,Array('ParentID'=>$clone->ID));
      }
    }

    return $clone;
  
    
  }

  private  function command_deletePage($args)  
  {

    $page=$this->getPage($args['pageId']);
    if($page) {
      $page->delete();
    }

    return Array('status'=>'ok');

  }

  var $clipboard;
  public function setClipboard($value)
  {
    $this->clipboard=$value;
  }

  public function getClipboard()
  {
    return $this->clipboard; 
  }

  private  function command_actionmenuItemsForPage($args)  // ['id'] ... Page - Id
  {

    $page=$this->getPage($args['id']);
    $this->setClipboard($args['clipboard']);


    $actionMenuItems=Array();
    if ($page) {

      $keys=$this->getAllActionMenuKeysForPage($page);

      foreach($keys as $key) {
        if (strstr($key,'.')) {
          list($parentKey,$childKey)=explode('.',$key);
          if($actionMenuItems[$parentKey]) {
            $menuItem=new PageManager_Menuitem($childKey,$page);
            if($menuItem->isVisible()) {
              $actionMenuItems[$parentKey]->addChild($menuItem);
            }
          }
        } else {
          $menuItem=new PageManager_Menuitem($key,$page);
          if($menuItem->isVisible()) {
            $actionMenuItems[$key]=new PageManager_Menuitem($key,$page);
          }
        }

      }

    }
    return array_values($actionMenuItems);

  }

  public function getAllActionMenuKeysForPage($page)
  {
    $keys=$this->getDefaultActionMenuKeys();
    if ($page->hasMethod('augmentActionMenuKeys')) {
      $keys=$page->augmentActionMenuKeys($keys);
    }

    return $keys;    
  }

  public function getDefaultActionMenuKeys()
  {

    $keys=Array(
      "preview_page",
      "delete_page",
      "hide_page",
      "publish_page",
      "create_page",
      "create_page.create_page_before",
      "create_page.create_page_after",
      "create_page.create_page_inside",
      "duplicate_page", 
      "page_history",
      "page_settings",
      "edit_page_permissions",
      "rename_page",
      "drag_page",
      "move_page",
      "copy_page",
      "paste_page",
      "paste_page.paste_page_before",
      "paste_page.paste_page_after",
      "paste_page.paste_page_inside",
      "devtools",
      "devtools.devtoolAliasPageSyncPagesUnderneath",

    );
    return $keys;
    
  }

  public function getAjaxTreeData()
  {
      
      if (is_numeric(array_get($_GET,'curr'))) {
        Controller::curr()->CurrentPageID=array_get($_GET,'curr');
      }
      $context=array_get($_REQUEST,'context');
      $mode='edit';
      if(strstr($context,'MwLinkChooser')) {
        $mode='mwlinkchooser';
      }
    
      if(is_numeric(array_get($_GET,'id'))) {
        $parentid=array_get($_GET,'id')*1;
        $parentpage=DataObject::get_by_id(SiteTree::class,$parentid);
        if($parentpage->hasMethod('getChildrenForPageTree')) {
          $rootPages=$parentpage->getChildrenForPageTree();
        } else {
          $rootPages=$parentpage->liveChildren(TRUE);
        }
      } else {
        $rootIds=$this->getRootIDsForTree(array_get($_GET,'tgn'));

        if($rootIds) {
          $rootPages=array();
          foreach ($rootIds as $id) {
            if(is_numeric($id)) {
              $page=$this->getPage($id);
            } else {
              $page=$id;
            }
            if (is_a($page,SiteTree::class)) {
              $rootPages[]=$page;
            }
          }
        } else {
          $rootPages=DataObject::get(SiteTree::class,"ParentID=0");
        }
      }

       foreach ($rootPages as $p) {
         
          if(!method_exists($p,'isVisibleInBpPageTree') || $p->isVisibleInBpPageTree()) {
            $tree[]=$this->getNodeDataForPage($p);
          }

        }

        if (!$tree) {
          $tree=Array();
        }


       header('content-type: application/json; charset=utf-8');
       echo json_encode($tree);
       
       exit();
  }


    public function getNodeDataForPage($p)
    {

      static $db;
      if(!$db) {
        $db=DBMS::getMdb();
      }


      $hasChildren=$db->getOne("select ID from SiteTree_Live where ParentID={$p->ID} LIMIT 1");

      $url="/BE/Pages/edit/{$p->ID}";
      if ($p->Type) {
        $typaddon=' ('.$p->Type.')';
      }

      $node=Array(
        'id' => $p->ID,
        'text' => "<span>".($p->hasMethod('getTitleForPageTree')?$p->getTitleForPageTree():$p->MenuTitle." <em>(".$p->URLSegment.")</em>")."</span>",
        'children'=>$hasChildren?true:false,
        'a_attr'=>Array(
          'title'=>'ID:'.$p->ID.', Type:'.$p->ClassName.$typaddon
        ),
        'icon'=>'fa fa-file'
      );

      if($p->hasMethod('getIconForPageTree')) {
        $icon=$p->getIconForPageTree();
        if($icon) {
            $node['icon']=$icon;
        }
        //  $node['a_attr']['style']='color:#ff0000';
      } 
      

      if($p->hasMethod('getClassesForPageTree')) {
        $node['a_attr']['class']=$p->getClassesForPageTree();
      } 

      if($p->ColorLabel) {
        $node['icon'].=' cl-'.$p->ColorLabel;
      }

      if($p->Hidden && !$p->doNotHideInPageTree){
        $node['a_attr']['class'].=' hiddenpage';
      }

      if($p->isAlias && $p->isSubPage()){
        $node['a_attr']['class'].=' alias-subpage';
      }


      if(!$p->ShowInMenus && !$p->doNotHideInPageTree){
        $node['a_attr']['class'].=' hide-in-menu';
      }


      return $node;
    }

    public function command_getNodeData($args)
    {
      $page=$this->getPage($args['pageId']);
      if ($page) {
        return $this->getNodeDataForPage($page);
      }
    }

    public function getRootIDsForTree($treeGroupName=NULL)
    {

      $ret=Array();

      if($user=Member::currentUser()) {

       if($user->hasMethod('getRootIDsForTreeGroup')) {
         $res=$user->getRootIDsForTreeGroup($treeGroupName);
       } else {
         $res=$user->getRootIDsForTree();
       }


       if($res) {
        return $res;
      }
    }

    if(is_array(MwPage::conf('RootPages'))) {
      return MwPage::conf('RootPages');
    } elseif(MwPage::conf('RootPages')) {
      return Array(MwPage::conf('RootPages'));
    }


    return $ret;
  }
}






?>
