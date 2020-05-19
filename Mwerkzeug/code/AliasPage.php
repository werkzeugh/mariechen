<?php

use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;
use SilverStripe\CMS\Controllers\ModelAsController;

class AliasPage extends FrontendPage
{
    public $isAlias=true;
    public $parentAliasPages=array();
    
    private static $db=array(
        'AliasSubPages'=>DBBoolean::class
    );
    
    private static $has_one=array(
        'SourcePage'=>'FrontendPage',
        'LocalRoot'=>'AliasPage',
    );
    
    public function myLocalRoot()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            if ($this->AliasSubPages) {
                $this->cache[__FUNCTION__]=$this;
            } elseif ($this->LocalRootID) {
                $this->cache[__FUNCTION__]=$this->LocalRoot();
            } else {
                $this->cache[__FUNCTION__]=null;
            }
        }
        return $this->cache[__FUNCTION__];
    }
    
    public function getPermission($permissionType, $args)
    {
        $sp=$this->mySourcePage();
        
        
        
        if ($sp && $sp->hasMethod('getPermissionIfAliased')) {
            $ret=$sp->getPermissionIfAliased($permissionType, $args);
            if ($ret=='allow' || $ret=='deny') {
                return $ret;
            }
        }
        
        
        if ($this->isSubPage()) {
            if ($permissionType=='showFormField') {
                if ($args['name']=='SourcePageInfo') {
                    return 'allow';
                }
            }
            return 'deny';
        }
        
        if ($permissionType=='showFormField') {
            if ($args['name']=='ClassName') {
                return false;
            }
        }
        if ($permissionType=='doAction') {
            if ($args['name']=='aliasPage') {
                return false;
            }
            if ($args['name']=='createSubPage') {
                if ($this->AliasSubPages) {
                    return 'deny';
                }
            }
        }
        return true;
    }
    
    public function getIconForPageTree()
    {
        if ($this->isSubPage()) {
            return $this->mySourcePage()->getIconForPageTree();
        }
        return 'fa fa-sign-out';
    }
    
    
    public function isSubPage()
    {
        return ($this->LocalRootID)?true:false;
    }
    
    
    public function myClosestSourcePage()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            if ($this->SourcePageID) {
                $page=DataObject::get_by_id(SiteTree::class, $this->SourcePageID);
            }
            if (!$page) {
                $page=null;
            }
            $this->cache[__FUNCTION__]=$page;
        }
        return $this->cache[__FUNCTION__];
    }
    
    public function mySourcePage()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $closestSourcePage=$this->myClosestSourcePage();
            if ($closestSourcePage) {
                if ($closestSourcePage->isAlias) {
                    $page=$closestSourcePage->mySourcePage();
                } else {
                    $page=$closestSourcePage;
                }
            } else {
                $page=null;
            }
            $this->cache[__FUNCTION__]=$page;
        }
        return $this->cache[__FUNCTION__];
    }
    
    
    
    public function getC4P()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $src=$this->mySourcePage();
            if (!$src) {
                $src=$this;
            }
            $this->cache[__FUNCTION__]=new C4P($src);
        }
        return $this->cache[__FUNCTION__];
    }
    
    public function getFieldForEdit($fieldname)
    {
        if ($fieldname=="RealMenuTitle") {
            $fieldname="MenuTitle";
        }
        
        return $this->getField($fieldname);
    }
    
    public function getRealMenuTitle()
    {
        return $this->__get('MenuTitle');
    }
    
    public function __get($field)
    {
        $value=parent::__get($field);
        if (($field=='Title' || $field=='MenuTitle') && $value=='auto' && $this->mySourcePage()) {
            return $this->mySourcePage()->getField($field);
        }
        
        return $value;
    }
    
    
    
    public function onAfterWrite()
    {
        parent::onAfterWrite();
        
        if ($this->isChanged('AliasSubPages') || array_get($_GET, 'forceSyncPagesUnderneath')) {
            if ($this->AliasSubPages) {
                singleton('AliasPageManager')->syncPagesUnderneath($this);
            } else {
                //delete all Suppages if they were AliasSubPages
                foreach ($this->AllChildren() as $childPage) {
                    if ($childPage->isAlias && $childPage->LocalRootID) {
                        $childPage->delete();
                    }
                }
            }
        }
    }
    
    public function getSourceBaselink()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=$this->myLocalRoot()->mySourcePage()->Link();
        }
        return $this->cache[__FUNCTION__];
    }
    
    public function getLocalBaselink()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=$this->myLocalRoot()->Link();
        }
        return $this->cache[__FUNCTION__];
    }
    
    public function findLocalAliasToLink($pageId)
    {
        if ($this->myClosestSourcePage()->ID!=$this->mySourcePage()->ID) {
            $parentLocalAliasToLink=$this->myClosestSourcePage()->findLocalAliasToLink($pageId);
            if ($parentLocalAliasToLink) {
                $pageId=$parentLocalAliasToLink->ID;
            }
        }
        
        
        $localAliasForPage=DataObject::get('AliasPage')->filter('SourcePageID', $pageId)->filter('LocalRootID', $this->myLocalRoot()->ID)->first();
        
        
        return $localAliasForPage;
    }
    
    public function rewriteLink($str, $pageId=null, $force=false)
    {
        //activeSubtemplate doesnt get set anymore !
        if ($GLOBALS['rewriteLinkRunning']   || !$this->myLocalRoot()) {
            return $str;
        }
        
        $GLOBALS['rewriteLinkRunning']=true;
        
        $localAliasForPage=$this->findLocalAliasToLink($pageId);
        if ($localAliasForPage) {
            $str=$localAliasForPage->Link();
        }
        
        $GLOBALS['rewriteLinkRunning']=false;
        return $str;
    }
    
    
    public function __call($name, $arguments=null)
    {
        if (!parent::hasMethod($name)) {
            $sp=$this->mySourcePage();
            if ($sp) {
                if ($sp->hasMethod($name)) {
                    if (!$sp->AliasPage) {
                        $sp->AliasPage=$this;
                    }
                    return call_user_func_array(array($sp, $name), $arguments);
                }
            }
        }
        return parent::__call($name, $arguments) ;
    }
    
    public function hasMethod($name)
    {
        $sp=$this->mySourcePage();
        
        if ($sp) {
            return $sp->hasMethod($name);
        }
    }
}

class AliasPageController extends FrontendPageController
{
    public $isAliasPageController=1;
    public function index(SilverStripe\Control\HTTPRequest $request)
    {
        die('ERROR, this Page does not exist '.$this->ID);
    }
    
    public function handleRequest(SilverStripe\Control\HTTPRequest $request)
    {
        return parent::handleRequest($request);
    }
    
    public function handleFinalRequest($request)
    {
        $sp=$this->dataRecord->mySourcePage();
        if ($this->dataRecord->AliasPage) {
            //my page already has an AliasPage set, add me to its list, and keep it
            $this->dataRecord->AliasPage->parentAliasPages[]=$this;
            $sp->AliasPage=$this->dataRecord=$this->dataRecord->AliasPage;
        } else {
            $sp->AliasPage=$this->dataRecord;
        }
        if ($sp) {
            if ($sp->ClassName=='RedirectionPage') {
                $originaLinkOfRedirectionPage=$sp->Link();
                if (!strstr($originaLinkOfRedirectionPage, ':')) {
                    $targetPage=PageManager::getPage($originaLinkOfRedirectionPage);
                }
                
                if ($targetPage) {
                    $GLOBALS['activeSubtemplate']='Layout';
                    //rewrite link to target-page in original-page's context
                    $redirectTo=$this->dataRecord->rewriteLink($originaLinkOfRedirectionPage, $targetPage->ID);
                }
            }
            
            if ($redirectTo) {
                header("Location:$redirectTo\n");
                die();
            } else {
                $response=ModelAsController::controller_for($sp)->handleRequest($request);
            }
        } else {
            throw(new Exception('sourcepage not found'));
        }
        
        return $response;
    }
}

class AliasPageBEController extends FrontendPageBEController
{
    public function getRawTabItems()
    {
        $items=array(
            "10" =>"Settings",
        );
        
        return $items;
    }
    
    
    public function step_10()
    {
        $sourcePage=$this->record->mySourcePage();
        if ($sourcePage && $sourcePage->hasMethod('getStep10IfAliased')) {
            return $sourcePage->getStep10IfAliased($this);
        }
        
        
        if ($sourcePage) {
            $nodeData= PageManager::singleton()->getNodeDataForPage($sourcePage);
            $html="<i class=\"{$nodeData[icon]}\"></i> {$nodeData['text']}";
            $html.="<br><small class='dimmed'>".$sourcePage->RawLink()."</small>";
        } else {
            $html="ERROR: source-page not found";
        }
        
        $p=array(); // ------- new field --------
        $p['label']=_t('backend.labels.page_IsAliasFor');
        $p['fieldname']="SourcePageInfo";
        $p['type']="html";
        $p['html']=$html;
        $this->formFields[$p['fieldname']]=$p;
        
        $p=array(); // ------- new field --------
        $p['label']=_t('backend.labels.page_AliasSubPages');
        $p['fieldname']="AliasSubPages";
        $p['type']="checkbox";
        $this->formFields[$p['fieldname']]=$p;
        
        parent::step_20();
    }
}
