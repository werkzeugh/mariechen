<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\View\ViewableData;

class AliasPageManager extends ViewableData
{
    public $cache=array();

    public $pm;
    public function __construct()
    {
        $this->pm=PageManager::singleton();
    }

    public function syncPagesUnderneath($aliasPage)
    {
        //find pages on sourcepage
        $sp=$aliasPage->mySourcePage();
        if ($sp) {
            $children=$sp->AllChildren();
            if ($children->count()>0) {
                foreach ($children as $childPage) {
                    $aliasChildPage=$this->aliasPageToLocalTree($childPage, $aliasPage);
                    $this->syncPagesUnderneath($aliasChildPage);
                }
            }
        }
    }

    public function aliasPageToLocalTree($sourcePage, $localParent)
    {
        /* find page in local page-tree: */
        $localRoot=$localParent->myLocalRoot();
        if ($localRoot) {
            $aliasPage=DataObject::get('AliasPage')->filter('LocalRootID', $localRoot->ID)->filter('SourcePageID', $sourcePage->ID)->first();
            if (!$aliasPage) {
                $aliasPage=$this->createAliasPageForSource($sourcePage->ID);
            }
            if ($aliasPage) {
                $aliasPage->ParentID=$localParent->ID;
                $aliasPage->LocalRootID=$localRoot->ID;
                $aliasPage->write();
                $this->updateAliasPageFromSource($aliasPage);
                return $aliasPage;
            } else {
                throw new Exception('cannot create local alias ');
            }
        }
    }


    public function createAliasPageForSource($sourcePageId)
    {
        $sourcePage=$this->pm->getPage($sourcePageId);

        // if ($sourcePage->ClassName=='AliasPage') {
        //   throw new Exception('cannot alias an alias-page');
        // }

        $newPage=new AliasPage();
        $newPage->SourcePageID=$sourcePage->ID;
        $this->updateAliasPageFromSource($newPage);
        return $newPage;
    }

    public function updateAliasPageFromSource($aliasPage)
    {
        if (is_numeric($aliasPage)) {
            $aliasPage=$this->pm->getPage($aliasPage);
        }

        $sourcePage=$aliasPage->mySourcePage();


        $updateArray=array();
        foreach ($this->fieldsToUpdate($aliasPage) as $fieldName) {
            $updateArray[$fieldName]=$sourcePage->getField($fieldName);
        }
        $aliasPage->update($updateArray);
        $aliasPage->write();
    }

    public function fieldsToUpdate($aliasPage)
    {
        if ($aliasPage->isSubPage()) {
            $ret=array('Title','MenuTitle','Hidden','ShowInMenus','Sort','LastEdited','URLSegment');
        } else {
            $ret=array();
        }
        return $ret;
    }

    public function onAfterWrite($page)
    {
        /* called in onAfterWrite on MwSiteTree-Objects */
        if (!$page->isAlias) {
            $this->updateAllAliasedTargetPages($page);
            if (array_get($_GET, 'forceCreateUnderneathAllTargetPages')) {
                $this->createUnderneathAllTargetPages($page);
            }
        }
    }

    public function onAfterDelete($page)
    {
        $this->deleteAllAliasedTargetPages($page);
    }

    public function onAfterCreate($page)
    {
        $parentPage=$page->Parent();
        if ($parentPage) {
            $this->createUnderneathAllTargetPages($page);
        }
    }

    public function onAfterParentIdChange($page)
    {
        $this->checkMoveInAllTargetTrees($page);
    }

    public function checkMoveInAllTargetTrees($page)
    {
        $alreadyCheckedTrees=array();
        $sourceParentPage=$page->Parent();
        /* ----------------------------------- check already existing aliased pages: */
        $localAliasPagesWhichNeedMoveCheck=DataObject::get('AliasPage')->filter('SourcePageID', $page->ID)->where("LocalRootID>1");
        foreach ($localAliasPagesWhichNeedMoveCheck as $pageToCheck) {
            /* find parent in local page-tree*/
            $localParentPage=DataObject::get('AliasPage')->filter('SourcePageID', $sourceParentPage->ID)->where("(LocalRootID={$pageToCheck->LocalRootID} or AliasPage_Live.ID={$pageToCheck->LocalRootID})")->first();
            if ($localParentPage) {
                $pageToCheck->ParentID=$localParentPage->ID;
                $pageToCheck->write();
            } else {
                $pageToCheck->delete();
            }
            $alreadyCheckedTrees[$pageToCheck->LocalRootID]=1;
        }

        /*  ----------------------------------- check already existing aliased pages: */
        $localAliasParentPagesWhichNeedMoveCheck=DataObject::get('AliasPage')->filter('SourcePageID', $sourceParentPage->ID)->where("(LocalRootID>1 or AliasSubPages=1)");
        foreach ($localAliasParentPagesWhichNeedMoveCheck as $localParentPage) {
            $treeId=$localParentPage->myLocalRoot()->ID;
            if (!$alreadyCheckedTrees[$treeId]) {
                $localPage=$this->aliasPageToLocalTree($page, $localParentPage);
                $this->syncPagesUnderneath($localPage);
            }
        }
    }

    public function createUnderneathAllTargetPages($page)
    {
        set_time_limit(60*10);
        $aliasesOfCurrentParentPage=DataObject::get('AliasPage')->filter('SourcePageID', $page->ParentID)->where("(LocalRootID>1 or AliasSubPages=1)");

        foreach ($aliasesOfCurrentParentPage as $localParentPage) {
            $localPage=$this->aliasPageToLocalTree($page, $localParentPage);

            //for nested aliases, create local page in all further down local trees:
            if ($localPage) {
                $this->createUnderneathAllTargetPages($localPage);
            }
        }
    }


    public function deleteAllAliasedTargetPages($page)
    {
        $pages=DataObject::get('AliasPage')->filter('SourcePageID', $page->ID);
        foreach ($pages as $targetPage) {
            $targetPage->delete();
        }
    }

    public function updateAllAliasedTargetPages($page)
    {
        $pages=DataObject::get('AliasPage')->filter('SourcePageID', $page->ID);
        //make it quick:

        $fields2change=array('Title','MenuTitle','Hidden','ShowInMenus','Sort','LastEdited','URLSegment');
        foreach ($fields2change as $fieldname) {
            $setClauses[]="target.$fieldname= source.$fieldname";
        }
    
        //update all at once

        $sql="update 
    SiteTree_Live target
    inner join SiteTree_Live source ON source.ID=".($page->ID*1)." 
    inner join AliasPage_Live aliaspage ON target.ID=aliaspage.ID
    set ".implode(' , ', $setClauses)."
    where aliaspage.SourcePageID=".($page->ID*1)." and aliaspage.LocalRootID>0";

        DB::query($sql);

        // for all: check if aliased-pages have aliases themselves:
        $sql="select target.ID from 
    SiteTree_Live target
    inner join SiteTree_Live source ON source.ID=".($page->ID*1)." 
    inner join AliasPage_Live aliaspage ON target.ID=aliaspage.ID
    where aliaspage.SourcePageID=".($page->ID*1)." and aliaspage.LocalRootID>0";

        $ids_to_handle=DB::query($sql);

        foreach ($ids_to_handle->column() as $id) {
            $page=$this->pm->getPage($id);
            $this->updateAllAliasedTargetPages($page);
        }
    }
}
