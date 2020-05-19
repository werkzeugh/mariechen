<?php

use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Controller;
use SilverStripe\Security\Security;
use SilverStripe\CMS\Model\SiteTree;

class TagNode extends Page
{
    private static $db=array(
        "Title_en"            => "Varchar(255)",
        // "BackendColor"       => "Varchar(20)",
        "Color"       => "Varchar(20)",
    );


    public function allowedChildren()
    {
        return array('TagNode');
    }

    public function getIconForPageTree()
    {
        return 'fa fa-tag';
    }

    public function getTitleForPageTree()
    {
        $title="";
        if ($this->Color) {
            $title.="<i style='color:{$this->Color}' class='fa fa-circle'></i> &nbsp;";
        }
        $title.=$this->URLSegment." <em>{$this->Title}</em>";
        return $title;
    }

    public function isTagTypeRoot()
    {
        return $this->ParentID==TagEngine::singleton()->getTagRootId();
    }

    public function getTagType()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=null;
            foreach ($this->Parents() as $p) {
                if ($p->isTagTypeRoot()) {
                    $this->cache[__FUNCTION__]=$p;
                    break;
                }
            }
        }
        return $this->cache[__FUNCTION__];
    }


    public function getUrlSegment()
    {
        return $this->getField('URLSegment');
    }


    public static function get_by_url($key)
    {
        $key=preg_replace("#[^a-z0-9-_/]#", "", $key);
        return SiteTree::get_by_link($key);
    }

    public static function get_by_numeric_id($id)
    {
        $id=Convert::raw2url($id);
        $res= DataObject::get_one('TagNode', "OldID={$id}");
        if (!$res) {
            $res=DataObject::get_by_id('TagNode', $id);
        }
        return $res;
    }


    public function getChildrenRecursive($ParentID, $level = 0, $params = null)
    {
        $children_map=[];

       
        if (!$params['show_hidden']) {
            if ($params['visible_only']) {
                $addonclause.=" AND Hidden=0 ";
            }

            $addonclause.=" AND ShowInMenus=1 ";
        }



        $children=DataObject::get('TagNode', "ParentID='{$ParentID}' ".$addonclause);


        if ($children) {
            foreach ($children as $child) {
                $children_map[]=[
                    'key'=>$child->URLSegment,
                    'classes'=>$child->URLSegment,
                    'text'=>$child->Title];

                if (!array_get($params, "no_recurse", false)) {
                    $children_map=array_merge($children_map, $child->getChildrenRecursive($child->ID, $level+1, $params));
                }
            }
        }

        return $children_map;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
    }

    public static function getTreeMap($URLSegment = "root-rubriken", $params = null)
    {
        if (!$params) {
            $params=array();
        }
        if ($URLSegment=="root-rubriken") {
            $params['rubriken']=1;
        }

        $URLSegment=str_replace('_', '-', $URLSegment);
        $base_cat=DataObject::get_one('TagNode', " URLSegment='{$URLSegment}' ");

        $res=$base_cat->getChildrenRecursive($base_cat->ID, 0, $params);
        return $res;
    }


    public function map($filter = "", $sort = "", $blank = "")
    {
        $ret = new SQLMap(singleton('TagNode')->extendedSQL($filter, $sort));
        if ($blank) {
            $blankCategory = new TagNode();
            $blankCategory->Title = $blank;
            $blankCategory->ID = 0;

            $ret->getItems()->shift($blankCategory);
        }
        return $ret;
    }

    public function getField($name)
    {
        if ($GLOBALS['CurrentLanguage']=='en') {
            switch ($name) {
                case 'Title':
                    $val=parent::getField($name."_en");
                    if ($val) {
                        return $val;
                    }
            break;
            }
        }
        return parent::getField($name);
    }
}


class TagNodeController extends PageController
{
}


class TagNodeBEController extends BpMysitePageController
{
    public function getRawTabItems()
    {
        $items=array(
         "10"=>"Tag",
         "20"=>"Settings",
         );
        
     
        return $items;
    }

    public function step_10()
    {
        MwBackendPageController::includeColorPicker();

        // FreshTagBase::includeRequirements();

        //define all FormFields for step "Title"

        $p=array(); // ------- new field --------
        $p['label']="Name";
        $p['fieldname']="Title";
        $this->formFields[$p['fieldname']]=$p;

        $p=array(); // ------- new field --------
        $p['label']="Name (en)";
        $p['fieldname']="Title_en";
        $this->formFields[$p['fieldname']]=$p;

        $p=array(); // ------- new field --------
        $p['label']="URL";
        $p['fieldname']="URLSegment";
        $this->formFields[$p['fieldname']]=$p;

        $p=array(); // ------- new field --------
        $p['label']='Color';
        $p['fieldname']="Color";
        $p['addon_classes']="colorpicker span2";
        // $p['tag_addon']=' data-default="'.$this->Event->getEscapedDefaultValueFor($p['fieldname']).'" ';
        $this->formFields[$p['fieldname']]=$p;
    }
}
