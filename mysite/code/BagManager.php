<?php

use SilverStripe\View\Requirements;
use SilverStripe\ORM\DataObject;
use SilverStripe\Core\Convert;
use SilverStripe\View\ViewableData;
use Tarsana\Functional as F;
use Tarsana\Functional\Stream;
use SilverStripe\Control\Director;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Control\Controller;

class BagManager extends ViewableData
{
    public $cache=array();

    public static function singleton($class = null)
    {
        if (class_exists('MyBagManager')) {
            $pm=singleton('MyBagManager');
        } else {
            $pm=singleton('BagManager');
        }

        return $pm;
    }

    public function vue_cli($filename)
    {
        return VueCliEngine::singleton()->vue_cli_helper($filename);
    }


    public function getCountryCodesInUse()
    {
        $mdb=DBMS::getMDB();
        $sql="select distinct Country from Angebot where LastDay>=curdate()";
        return $mdb->getCol($sql);
    }



    public function getAllCountries()
    {
        static $list;
        if (!$list) {
            $list= include(Director::baseFolder().'/vendor/umpirsky/country-list/data/de/country.php');
        }
        return $list;
    }

    public function getCountryName($code)
    {
        return array_get($this->getAllCountries(), $code, $code);
    }

  

    public function getCurrentCurrency()
    {
        // return 'eur';
        return 'usd';
    }


    public function getTagCounts($page, $taglist)
    {
        $ret=[];
        // find widget on page
        $elements=$page->getArticleElements();
        $c4p=null;
        foreach ($elements as $el) {
            if ($el->CType=='Article_C4P_Widget' && $el->Type=='baglist') {
                $c4p=$el;
                break;
            }
        }
        if ($c4p) {
            foreach ($taglist as $tag) {
                $ret[$tag]=$this->getCountForTag($c4p, $tag);
            }
        }
        return $ret;
    }

    public function getTagStringForWidget($c4p, $addonTags)
    {
        return F\Stream::of([$c4p->Tags,$c4p->Tags2,$c4p->Tags3, $addonTags])
                ->map(function ($tagstr) {
                    return F\Stream::of($tagstr?$tagstr:"")
                    ->split(" ")
                    ->join(",")
                    ->result();
                })
            // ->fromPairs()
            // ->attributes()
            ->join(" ")
            ->result();
    }

    public function getCountForTag($c4p, $tag)
    {
        $addonTags="#$tag";
        
        $tagString=  $this->getTagStringForWidget($c4p, $addonTags);
        $params=[];
        $n=1;
        foreach (explode(" ", $tagString) as $tagString) {
            $params['tags'.$n++]=$tagString;
        }
        $res=TagEngine::singleton()->callExApi("/bags/count/", $params);
        return array_get($res, "count", 0);
    }

    public function getWidgetHtml($c4p)
    {
        $c=array();
        $addonTags="";
        if ($type=Controller::curr()->Type) {
            $addonTags="#$type";
        }
        $tagString=  $this->getTagStringForWidget($c4p, $addonTags);

    
        $data=array(
            'apiUrl'      => 'https://'.(array_key_exists('HTTP_X_ORIGINAL_HOST', $_SERVER)?$_SERVER['HTTP_X_ORIGINAL_HOST']:$_SERVER['HTTP_HOST']).'/ex',
            'baseUrl'     => $c4p->TopRecord->Link(),
            'slug'        => $c4p->TopRecord->URLSegment,
            'mode'        => 'search',
            'currency'=>$this->getCurrentCurrency(),
            'baseTags'=>$tagString,
            'lang'    =>$GLOBALS['CurrentLanguage'],

            // 'travelCategories' => $categs,
            // 'countries' => $countries,
        );

        $c['settingsAsJson']=json_encode($data);
        return $this->customise($c)->renderWith('Includes/C4P_Widget_BagList');
    }
}
