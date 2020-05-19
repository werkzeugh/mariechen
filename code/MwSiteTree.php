<?php

use SilverStripe\Control\Director;
use SilverStripe\Versioned\Versioned;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Security\Member;
use SilverStripe\Control\Session;
use SilverStripe\View\ArrayData;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\View\SSViewer;
use SilverStripe\CMS\Controllers\ContentController;

/*

(c) manfred@monochrom.at
2011-02-21

*/

class MwSiteTree extends SiteTree
{
    public $cache;
    public $AliasPage;
    public function getMwLink()
    {
        return "mwlink://SiteTree-{$this->ID}";
    }

    public function onAfterWrite()
    {

        // if($this->current_stage()=='Live')
        // {
        //     $this->publish('Live', 'Stage');
        // }

        singleton('AliasPageManager')->onAfterWrite($this);
        parent::onAfterWrite();
    }

    public function onAfterDelete()
    {
        singleton('AliasPageManager')->onAfterDelete($this);
        parent::onAfterDelete();
    }

    public function __construct($record = null, $isSingleton = false)
    {
        parent::__construct($record, $isSingleton);
        if ($this->hasField('HideOn') && ($this->HideOn || $this->PublishOn)) {
            $this->checkHiddenTimer();
        }

        if ($this->hasField('ArchiveOn') && $this->ArchiveOn) {
            $this->checkArchivedTimer();
        }
    }


    public function restoreVersion($versionNum)
    {
        $this->publish($versionNum, "Live", true);
        $this->writeWithoutVersion();
    }

    public function RawLink($action = null)
    {
        $oldval=MwVhostMapper::conf('hideBaseUrl');
        MwVhostMapper::setConf('hideBaseUrl', 1);
        $link= '/'.$this->RelativeLink();
        MwVhostMapper::setConf('hideBaseUrl', $oldval);
        return $link;
    }
    
    public function PlainLink($action = null)
    {
        $oldval=MwVhostMapper::conf('hidePrefix');
        MwVhostMapper::setConf('hidePrefix', 1);
        $link=$this->RelativeLink();
        $link= $link=='/'?$link:'/'.$link;
        MwVhostMapper::setConf('hidePrefix', $oldval);
        return $link;
    }

    public function RelativeLink($action = null)
    {
        try {
            $url=parent::RelativeLink($action);
        } catch (\Throwable $th) {
            return null;
        }

        if ($prefix=MwVhostMapper::getCurrentBaseUrl()) {
            $url=preg_replace('#^'.$prefix.'(/|$)#', MwVHostMapper::getCurrentPrefix(), $url);
        }
        
        return $url;
    }

    public function Link($action = null)
    {
        $link=parent::Link($action);
        if (Controller::has_curr() && Director::get_current_page()->ClassName=='AliasPage') {
            $link=Director::get_current_page()->rewriteLink($link, $this->ID);
        }

        return $link;
    }

    public function AliasSafeLink($action = null)
    {
        $link=parent::Link($action);
        if (Controller::has_curr() && Director::get_current_page()->ClassName=='AliasPage') {
            $link=Director::get_current_page()->rewriteLink($link, $this->ID, true);
        }

        return $link;
    }

    public function Newline()
    {
        return "\n";
    }

    public function isEditableInBE()
    {
        return true;
    }

    public function isVisibleInBE()
    {
        return true;
    }
  
    public function touch()
    {
        $this->LastEdited=Datum::mysqlDate(time());
        $this->write();
    }

    public function alternateAbsoluteLink($action)
    {
        $link=parent::RelativeLink($action); //get link without mappings
        $link=MwVhostMapper::getAbsoluteUrl($link);
        if (!strstr("^".$link, '^http:')) {
            $link=Director::absoluteURL($this->Link($action));
        }

        return $link;
    }

    public function HTMLTitle()
    {
        return $this->MenuTitle;
    }

    public function getRealMenuTitle()
    {
        return $this->getField("MenuTitle");
    }

    public function setRealMenuTitle($value)
    {
        $this->setField("MenuTitle", $value);
    }

    public function Datum($fieldname)
    {
        return new Datum($this->getField($fieldname));
    }

    public function syncLinkTracking()
    {
        return false;
        // override sapphire's syncLinkTracking because it initialises
    }

    public function CssClass()
    {
        return $this->isSection() ? 'active' :'not-active';
    }

    public function EditLink()
    {
        return '/BE/Pages/edit/'.$this->ID;
    }

    public function checkOrCreatePages($pagesconf)
    {
        if (is_array($pagesconf)) {
            foreach ($pagesconf as $pageurl => $pagedata) {
                $this->checkOrCreatePage($pageurl, $pagedata);
            }
        }
    }

    public function checkOrCreatePage($suburl, $pagedata)
    {
        $p=$this->getSubPage($suburl);

        if (!$p) {
            //remove trailing and leading slashes from suburl:
            $suburl=preg_replace('#^/#', '', $suburl);
            $suburl=preg_replace('#/$#', '', $suburl);

            if (strstr($suburl, '/')) {
                preg_match('#^(.+)/([^/]+)$#', $suburl, $m);
                $parent_suburl=$m[1];
                $parentpage=$this->getSubPage($parent_suburl);
                $urlsegment=$m[2];
            } else {
                $parentpage=$this;
                $urlsegment=$suburl;
            }

            if (!$parentpage) {
                return false;
            }

            $pagedata['URLSegment']=$urlsegment;
            if (!$pagedata['Title']) {
                $pagedata['Title']=$urlsegment;
            }

            Versioned::set_stage("Live");
            if ($pagedata['ClassName'] && class_exists($pagedata['ClassName']) && $parentpage && $pagedata['URLSegment']) {
                $newpage=new $pagedata['ClassName'];
                $newpage->update($pagedata);
                $newpage->setParent($parentpage);
                $newpage->Hidden=0;
                $newpage->write();
                return $newpage;
            }
        }

        return $p;
    }

    public function getSubPage($suburl)
    {
        $cachekey=__FUNCTION__.$suburl;
        if (!isset($this->cache[$cachekey])) {
            $GLOBALS['nolinkrewrite4prelaunch']=1;

            //remove trailing and leading slashes from suburl:
            $suburl=preg_replace('#^/#', '', $suburl);
            $suburl=preg_replace('#/$#', '', $suburl);
            $p=SiteTree::get_by_link($this->RawLink().$suburl);

            if (!($p && $p->ID)) {
                $p=null;
            }
            $this->cache[$cachekey]=$p;
        }
        return $this->cache[$cachekey];
    }

    public function getSubPageByClass($look4class)
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $res=$this->getSubPagesByClass($look4class);
            if ($res->count()) {
                $ret=$res->First();
            } else {
                $ret=null;
            }

            $this->cache[__FUNCTION__]=$ret;
        }
        return $this->cache[__FUNCTION__];
    }

    public function getSubPagesByClass($look4class)
    {
        $p=DataObject::get($look4class)->filter("ParentID", $this->ID);

        return $p;
    }

    public function Parents()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $parent = $this;
            $al=new ArrayList();
            $al->push($parent);
            while ($parent = $parent->Parent) {
                $al->push($parent);
            }
            $this->cache[__FUNCTION__]=$al;
        }
        return $this->cache[__FUNCTION__];
    }

    public static function getFieldForSure($record, $fieldname)
    {
        // some fields on extensions do not get loaded initially, because they are defined internally in Object::_construct() which gets called after DataObject::_construct()) mwuits 2012-11-14

        $value=$record->getField($fieldname);
        if (!$value) {
            $rec=$record->toMap();
            $value=$rec[$fieldname];
        }

        return $value;
    }


    public function getMenuParent()
    {
        /* gets the parent for menu-contexts, takes Aliased-Pages into account */
        if ($this->AliasPage) {
            return $this->AliasPage->getParent();
        } else {
            return $this->getParent();
        }
    }

    // public function __get($fieldname)
    // {
    //   if ($this->AliasPage) {
    //     if(preg_match('/^(Title|MenuTitle|URLSegment)$/',$fieldname)) {
    //       return $this->AliasPage->$fieldname;
    //     }
    //   }
    //   return parent::__get($fieldname);

    // }


    public function getPermission($permissionType, $args)
    {
        if ($permissionType=='doAction') {
            if ($args['name']=='aliasPage') {
                return MwPage::conf('enableAliasPages')?true:false;
            }
        }
        return true;
    }

    public function getSelfOrAlias()
    {
        if ($this->AliasPage) {
            return $this->AliasPage;
        }
        return $this;
    }

    public function getSelfOrSourcePage()
    {
        if ($this->isAlias) {
            return $this->mySourcePage();
        }
        return $this;
    }

    public function augmentActionMenuKeys($keys)
    {
        $dummy=array();
        $ret=$this->extend('augmentActionMenuKeys', $dummy);
        foreach ($ret as $idx => $returnedKeys) {
            if (is_array($returnedKeys)) {
                foreach ($returnedKeys as $value) {
                    if (!in_array($value, $keys)) {
                        $keys[]=$value;
                    }
                }
            }
        }
        return $keys;
    }


    public function actionMenuItemFields($key, $defaultValues)
    {
        $data=array();
        $ret=$this->extend('actionMenuItemFields', $key, $defaultValues);
        if ($ret[0]) {
            return $ret[0];
        }

        return array();
    }


    public function CurrentLevel()
    {
        $parent = $this->SelfOrAlias;
        $stack = array($parent);
        while ($parent = $parent->Parent) {
            array_unshift($stack, $parent);
        }
 
        return sizeof($stack);
    }

    public function getGrandParentID()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $id=0;
            $parent=$this->Parent();
            if ($parent) {
                $id=$parent->ParentID;
            }
            $this->cache[__FUNCTION__]=$id;
        }
        return $this->cache[__FUNCTION__];
    }
}

class MwSiteTreeController extends ContentController
{
    public $MwSiteTreeTemplateFiles;
    public $cache;

    public function ServerVar($value)
    {
        return $_SERVER[$value];
    }

    public function RequestVar($value)
    {
        return $_REQUEST[$value];
    }

    public function nl2br($field)
    {
        return nl2br($this->$field);
    }

    public function CurrentMember()
    {
        $ret=Member::currentUser();
        return $ret;
    }

    public static function setSessionMessage($message, $messageType = "info")
    {
        Mwerkzeug\MwSession::set("FormInfo.default.formError.message", $message);
        Mwerkzeug\MwSession::set("FormInfo.default.formError.type", $messageType);
    }

    public static function getSessionMessage()
    {
        $list=Mwerkzeug\MwSession::get('FormInfo');
        if ($list) {
            foreach ($list as $formName => $value) {
                if (isset($value['formError'])) {
                    $message = Mwerkzeug\MwSession::get("FormInfo.{$formName}.formError.message");
                    $messageType = Mwerkzeug\MwSession::get("FormInfo.{$formName}.formError.type");

                    Mwerkzeug\MwSession::clear("FormInfo.{$formName}");
                    return array(
                        "text" => $message,
                        "type" => $messageType,
                    );
                    break;
                }
            }
        }
        return array();
    }

    public static function sessionMessage()
    {
        //look if any forminfo message is present:
        static $sessionMsgCache;

        if (!isset($sessionMsgCache)) {
            if ($m=self::getSessionMessage()) {
                $sessionMsgCache=new ArrayData($m);
            } else {
                $sessionMsgCache=false;
            }
        }
        return $sessionMsgCache;
    }

    public function showOutdated($HostmasterEmail)
    {
        if ($this->urlParams['Action']!='outdated') {
            $url=$this->Link().'/outdated';
            $url=str_replace('//', '/', $url);
            header("Location:".$url);
            die();
        }

        if (strstr($HostmasterEmail, '@')) {
            $this->HostmasterEmail=$HostmasterEmail;
        }
    }

    public function outdated()
    {
        try {
            $this->mwSetTemplateFile('main', array('MwSiteTree_outdated'));
        } catch (Exception $e) {
            $this->mwSetTemplateFile('Layout', 'MwSiteTree_outdated');
        }

        $c=array('outdated' => true);
        return $c;
    }



    // paging functions ---------- BEGIN
    public function getPaginated($items)
    {
        if (is_string($items)) {
            $items=call_user_func([$this, $items]);
        }

        $pitems=new PaginatedList($items, Controller::curr()->getRequest());
        $pitems->setPageLength(Controller::curr()->getPageSize());

        if (!$pitems) {
            $pitems=new ArrayList();
        }

        return $pitems;
    }

    public function getPaging($dataProviderAction, $pagingHash = '', $pagingLinkPrefix = '')
    {
        $items=call_user_func([$this, $dataProviderAction]);

        if (is_object($items) && get_class($items)!=PaginatedList::class) {
            $items=$this->getPaginated($items);
        }

        $c=array(
            "PaginationSource" => $items,
            'PagingHash'       => $pagingHash,
            'PagingLinkPrefix' => $pagingLinkPrefix,
        );
        return $this->customise($c)->renderWith($this->getPagingTemplate());
    }

    public function getPagingSQL()
    {
        echo('unsupported use of old paging-method');
        $limit=$this->getPageSize();

        if (!isset($_GET['start']) || !is_numeric($_GET['start']) || (int)$_GET['start'] < 1) {
            $_GET['start'] = 0;
        }
        $SQL_start = (int)$_GET['start'];

        $sql="{$SQL_start},$limit";

        return $sql;
    }

    // paging functions ---------- END

    public function getViewer($action)
    {
        $viewer=parent::getViewer($action);
        if ($this->MwSiteTreeTemplateFiles) {
            foreach ($this->MwSiteTreeTemplateFiles as $templateType => $templateFile) {
                $viewer->setTemplateFile($templateType, $templateFile);
            }
        }

        return $viewer;
    }

    public function summitSetTemplateFile($templateType, $templateName) //compatibility function
    {
        return $this->mwSetTemplateFile($templateType, $templateName);
    }

    public function mwSetTemplateFile($templateType, $templateName)
    {
        $myTemplateType=($templateType=='main')?'':$templateType;

        //      if($templateType==='Layout' && !strstr($templateName,'/')) {
        //          $templateName="Layout/".$templateName;
        //      }

        $tfile=SSViewer::getTemplateFileByType($templateName, $myTemplateType);

        if ($tfile) {
            $this->MwSiteTreeTemplateFiles[$templateType]=$tfile;
        } else {
            throw new Exception("no template file found while calling mwSetTemplateFile('{$templateType}','{$templateName}')");
        }
    }

    public function CurrentURL()
    {
        $url=preg_replace('#\?.*$#', '', array_get($_SERVER, 'REQUEST_URI'));
        $url=rtrim($url, '/').'/';
        return $url;
    }

    public function QueryString()
    {
        if (strstr(array_get($_SERVER, 'REQUEST_URI'), '?')) {
            return preg_replace('#^[^?]+\?#', '', array_get($_SERVER, 'REQUEST_URI'));
        }
    }

    public function isLocal()
    {
        return file_exists('/Applications');
    }
}
