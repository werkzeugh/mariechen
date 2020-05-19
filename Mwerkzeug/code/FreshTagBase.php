<?php

use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\Requirements;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ViewableData;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;

class Tag extends DataObject
{
    private static $db=array(
        'TagKey'    => 'Varchar(200)',
        'Type'      => 'Varchar(200)',
        'Title'     => 'Varchar(200)',
        'Frequency' => 'Int',
    );
  
   
  
    public static function get_by_key($key)
    {
        $key=Convert::raw2sql($key);

        return DataObject::get_one('Tag', "TagKey='$key'");
    }

    public function onBeforeWrite()
    {

        parent::onBeforeWrite();

        if (!$this->Title || $this->Title=='new') {
            $this->Title=trim($this->TagKey);
        }
        $tagKey=preg_replace("/\s+/", "_", trim($this->TagKey));
        $this->TagKey=preg_replace('#[^a-z0-9_]#', '', strtolower($tagKey));

        if ($this->TagKey) {
            $existing=self::get_by_key($this->TagKey);
            if ($existing) {
                if ($existing->ID != $this->ID) {
                    throw new Exception("Error: Tag-Key already exists: [{$existing->TagKey} : {$existing->Title} ]");
                }
            }
        }

        $options=array_get($_POST, 'options');

        if (!$this->Type && $options['listparams']['tag_group']) {
            $this->Type=$options['listparams']['tag_group'];
        }
    }


    private static $indexes = [
        'TagKeyUnique' => [
            'type'    => 'unique',
            'columns' => ['TagKey'],
        ],
    ];
}

class FreshTagBase extends ViewableData
{

    var $Key;
    var $Type;
    var $Title;
    static $cache;
  
  
    public function __construct($key, $p = array())
    {
        $key=str_replace('tag_', '', $key);
        $this->Key=$key;
        $info=$this->getTagInfo4Key($key);
        $this->Title=$info['name'];
        $this->Type =$info['type'];
        if (!$this->Title && $p['scaffold']) {
            $this->Title=$this->Key;
        }
    }

    public function getFrequency()
    {
        $ret= self::$cache['tagfreqs'][$this->Key];
        if ($ret) {
            return $ret;
        } else {
            return 0;
        }
    }

    public static function getTagGroups()
    {


        $ret=Config::inst()->get('FreshTag', 'tagGroups');

        return $ret;
    }

    function CssSize()
    {
        //http://stackoverflow.com/questions/2378576/best-practice-with-tagclouds-or-tagcloud-logic
        return (50 * ($this->Frequency / FreshTag::$MaximumFrequencyInTagCloud) + 80).'%';
    }
      
    public static function includeRequirements()
    {
        Requirements::javascript("Mwerkzeug/javascript/jquery.ui.subclass.js");
        Requirements::javascript('Mwerkzeug/javascript/FreshTagField_jqueryui_widget.js');
        Requirements::css('Mwerkzeug/css/FreshTag.css');
    }


    static function getTags4Json($str)
    {
        $t=explode(" ", $str);
        $arr=array();
        foreach ($t as $tag) {
            $tag=trim(preg_replace('#^tag_#', '', $tag));
          //echo "<li>$tag";
            if ($tag && $fulltag=self::getTag4Key($tag)) {
                $arr[$fulltag->Key]=$fulltag->Title;
            }
        }
        return $arr;
    }

    public static function getTagInfo4Key($key, $p = array())
    {
        $key=str_replace('tag_', '', $key);


        foreach (FreshTag::getAllTags() as $type => $tags) {
            if ($tags[$key]) {
                return array(
                    'type' => $type,
                    'name' => $tags[$key],
                );
            }
        }

        if ($p['scaffold']) {
            return array(
                'type' => 'dummy',
                'name' => $key,
            );
        }
    }

    public static function getTag4Key($key, $p = array())
    {
        $info=self::getTagInfo4Key($key, $p);
        if ($info) {
            return new FreshTag($key, $p);
        }
    }


    public static function getTagsForType($type)
    {
        $alltags=FreshTag::getAllTags();
        if ($alltags[$type]) {
            return $alltags[$type];
        } else {
            return array();
        }
    }



    public static function getTagMapForType($type)
    {
        $alltags=FreshTag::getAllTags();

        return $alltags[$type];
    }


    public static function getTagMapForString($str)
    {
        $str=str_replace('tag_', '', $str);
    
        $tags=explode(',', $str);
        foreach ($tags as $tag) {
            $ti=self::getTagInfo4Key($tag);
            if ($ti) {
                $taglist[$tag]=$ti['name'];
            }
        }
        return $taglist;
    }

    static $MaximumFrequencyInTagCloud=1;

    static function getTags4TagCloud($_p = array())
    {
  //returns a list of all tags

        if (!array_key_exists('minFreq', $_p)) {
            $_p['minFreq']=3;
        }

        if (!array_key_exists('maxTags', $_p)) {
            $_p['maxTags']=40;
        }


        $tags=self::getTagsForType($_p['type']);
        $tagList=array();
        if ($tags) {
            foreach ($tags as $tagkey => $tagname) {
                $tag=self::getTag4Key($tagkey);
                if ($tag->Frequency<$_p['minFreq']) {
                    continue;
                }
                $totalfreq+=$tag->Frequency;
                if ($tag->Frequency>$Maximum) {
                    $Maximum=$tag->Frequency;
                }
                $n++;
                $tagList[sprintf('%05d', $tag->Frequency).'-'.$n]=$tag;
            }
        }

        $keys=array_keys($tagList);
        rsort($keys);

        $keys=array_slice($keys, 0, $_p['maxTags']);



        $dos=new ArrayList();
        foreach ($tagList as $tagKey => $tag) {
            if (in_array($tagKey, $keys)) {
                $dos->push($tag);
            }
        }

        if ($Maximum) {
            FreshTag::$MaximumFrequencyInTagCloud=$Maximum;
        }



        return $dos;
    }

    public static function setMaximumFrequencyInTagCloud($value)
    {
        FreshTag::$MaximumFrequencyInTagCloud=$value;
    }

    public static function getAllTags()
    {

        if (!self::$cache['tags']) {
            $db=DBMS::getMdb();
            $tags=$db->getAssoc("select TagKey,Title,Frequency,Type from Tag order by Type,Title");

            foreach ($tags as $key => $rec) {
                self::$cache['tags'][$rec['type']][$key]=$rec['title'];
                self::$cache['tagfreqs'][$key]=$rec['frequency'];
            }
        }
        return self::$cache['tags'];
    }

    public static function getTagsForString($str, $p = array())
    {
        $str=str_replace('tag_', '', $str);

        if ($p['validTypes']) {
            $validTypes=explode(" ", $p['validTypes']);
        }
  
        if ($p['invalidTypes']) {
            $invalidTypes=explode(" ", $p['invalidTypes']);
        }


        $dos=new ArrayList();
    
        if (strstr($str, ',')) {
            $tags=explode(',', $str);
        } else {
            $tags=explode(' ', $str);
        }

        foreach ($tags as $tag) {
            $tag=trim($tag);

            if ($tag && !$tagAlreadyHandled[$tag]) {
                $ti=self::getTag4Key($tag, $p);
                if ($ti
                && (!$validTypes || in_array($ti->Type, $validTypes))
                && (!$invalidTypes || !in_array($ti->Type, $invalidTypes))
                  ) {
                    $dos->push($ti);
                }
        
                $tagAlreadyHandled[$tag]=true;
            }
        }
        return $dos;
    }
}


class FreshTagBaseController extends BackendPageController
{

    private static $allowed_actions = [
        'ehp',
    ];
    var $cache;
    var $CurrentTagGroupKey;

    var $myClass='Tag';

 // include ehp stuff ---------- BEGIN

    public function getEHP()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=new EHP($this);
        }
        return $this->cache[__FUNCTION__];
    }


    function ehp()
    {
    
        return $this->EHP->dispatch();
    }
  
    function EHP_getRecordClass()
    {
      
        return $this->myClass;
    }

    public function EHP_BaseItems($options = null)
    {
        if ($type=$options['listparams']['tag_group']) {
            $type=trim($type);
            return DataObject::get($this->myClass)
                              ->filter('Type', $type);
        }
    }

    public function EHP_roweditHTML($record, $params)
    {

        return "
    <td>
      <input type='text' name='fdata[TagKey]' value='{$record->TagKey}'>  
    </td>
    <td>
      <input type='text' name='fdata[Title]' value='{$record->Title}'>
    </td>
    ";
    }
  
    public function EHP_rowTpl()
    {
        return '
      <td><b>$TagKey</b></td><td>$Title</td>
    ';
    }

  // include ehp stuff ---------- END


    public function index(SilverStripe\Control\HTTPRequest $request)
    {

        EHP::includeRequirements();

        $this->CurrentTagGroupKey=Controller::curr()->urlParams['ID'];
        $this->summitSetTemplateFile("Layout", "FreshTag_BE_listing");

        return array();
    }

    public function pageNavItems()
    {

        $ds=new ArrayList();
           
        foreach (FreshTag::getTagGroups() as $tagGroupKey => $tagGroupDef) {
            $active=($this->CurrentTagGroupKey == $tagGroupKey);

            $ds->push(new MwBackendPage_NavItem("BE/FreshTag/index/{$tagGroupKey}", array('ID' => $tagGroupKey, 'Title' => $tagGroupDef['Title'], 'active' => $active)));
        }

 
        return $ds;
    }


    public function jsonTagInfo()
    {

        $ret=array();
    
        $key=array_get($_REQUEST, 'key');

        $tag=Tag::get_by_key($key);


        if ($tag) {
            return json_encode(array(
                'TagKey'    => $tag->TagKey,
                'Title'     => $tag->Title,
                'Type'      => $tag->Type,
                'Frequency' => $tag->Frequency,
            ));
        }
    }

    public function jsonTagList()
    {

        $ret=array();
        foreach (array_get($_REQUEST, 'types') as $key => $value) {
            $vals=FreshTag::getTagsForType($key);
            if (!$vals) {
                $vals=array('' => 'no tags found');
            }
            $ret[$key]=$vals;
        }

        $html=json_encode($ret);
        echo $html;
        die();
    }


    public function ajaxCreateTagInline()
    {

        $tag=new Tag();

        $tag->TagKey = trim(strtolower(array_get($_REQUEST, 'tagkey')));
        $tag->Title  = trim(array_get($_REQUEST, 'tagname'));
        $tag->Type   = array_get($_REQUEST, 'tagtype');

        if (!$tag->Title) {
            die('no tag name given');
        }

        if (!$tag->TagKey) {
            die('no tag key given');
        }

        if (!$tag->Type) {
            die('no tag key given');
        }

        if (Tag::get_by_key($tag->TagKey)) {
            die("a Tag with key: '{$tag->TagKey}' already exists !");
        }

        if ($tag->write()) {
            echo "<a class=\"freshtag\" href=\"{$tag->TagKey}\"><b>{$tag->TagKey}</b>&nbsp;{$tag->Title}</a>";
        } else {
            echo "could not write tag";
        }


        exit();
    }
}
