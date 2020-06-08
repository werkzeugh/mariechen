<?php

use SilverStripe\View\Requirements;
use SilverStripe\Control\Session;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Controller;

class FrontendPage extends MwFrontendPage
{
    public static $masterCacheKeyPart = '2020_03_27_041744'; // =ts\

    public function IsCurrentNav()
    {
        return $this->isSection();
    }


    // needed for MwSiteTreeConfigExtension --- START
    public function __get($fieldname)
    {
        $val=$this->MwSiteTreeConfigExtension__get($fieldname);
        if ($val=="__parent__") {
            return parent::__get($fieldname);
        } else {
            return $val;
        }
    }


    public function update($incoming)
    {
        $this->MwSiteTreeConfigExtension_update($incoming);

        return parent::update($incoming);
    }

    // needed for MwSiteTreeConfigExtension --- END


    public function getTranslated($name)
    {
        if ($GLOBALS['CurrentLanguage']=='de') {
            switch ($name) {
                case 'Title':
                case 'Config_GoogleTitle':
                case 'Config_MetaDescription':
                        $val=$this->{$name."_de"};
                    if ($val) {
                        return $val;
                    }
                break;
            }
        }
        return $this->{$name};
    }

    public function HTMLTitle()
    {
        $title=trim($this->getTranslated('Config_GoogleTitle'));
        return ($title?$title:$this->MenuTitle)." - mariechen";
    }

    public function getOgImageUrl()
    {
        if ($this->Config_OgImageID) {
            $file=DataObject::get_by_id("MwFile", $this->Config_OgImageID);
            if ($file) {
                return $file->Link();
            }
        }
        return "/mysite/images/eva_blut_logo.png";
    }
}



class FrontendPageController extends MwFrontendPageController
{
    public $HeadAddons=[];
   
    public function getHeadAddon()
    {
        return implode("\n", $this->HeadAddons);
    }

    
    public function init()
    {
        parent::init();

        if (trim($this->RedirectURL)) {
            header("Location:{$this->RedirectURL}");
            exit();
        }


        if (($this->ie('MSIE 8.') || $this->ie('MSIE 7.') || $this->ie('MSIE 6.')) && !$this->controllerShowsOutdatedMessage) {
            header("Location:/outdated_browser");
            die();
        }

        Requirements::set_write_js_to_body(false);
        Requirements::set_combined_files_enabled(false);

        Requirements::clear();
        $this->initBack2Edit();


        // Requirements::CSS('mysite/css/reset.css');

        Requirements::CSS('mysite/css/frontend/frontend.css');
        Requirements::CSS('files/custom.css');
        Requirements::javascript('node_modules/jquery/dist/jquery.min.js');

        // Requirements::javascript('node_modules/popper.js/dist/umd/popper.min.js');
        // Requirements::javascript('node_modules/bootstrap/js/dist/util.js');
        // Requirements::javascript('node_modules/bootstrap/js/dist/dropdown.js');

        // Requirements::CSS("mysite/css/typography.css");

        //  MwBackendPageController::includePartialBootstrap();

        // Requirements::javascript("bower_components/angular-14/angular.min.js");
        // Requirements::javascript("mysite/ng/productsearch/js/productsearch.js");



        // Requirements::javascript("mysite/javascript/modernizr.2.6.2.min.js");
        //  Requirements::javascript("mysite/thirdparty/colorbox/colorbox/jquery.colorbox.js");
        //  Requirements::javascript("mysite/thirdparty/jquery_plugins/jquery.backstretch.min.js");
        //  Requirements::javascript("mysite/thirdparty/unslider.min.js");

        //  Requirements::javascript("bower_components/matchHeight/jquery.matchHeight-min.js");

      
      
        // Requirements::CSS("mysite/css/colorbox.css");
        //  Requirements::CSS("mysite/thirdparty/colorbox/example1/colorbox.css");
        Requirements::javascript("mysite/javascript/pageScripts.js");

        //  Requirements::javascript("bower_components/angular-14/angular.min.js");
        //  Requirements::javascript("mysite/ng/productsearch/js/productsearch.js");
        // Requirements::css("bower_components/font-awesome/css/font-awesome.min.css");
        // Requirements::javascript("mysite/thirdparty/fullsize/jquery.fullsize.minified.js");

        if ($_SERVER['HTTP_HOST']=='shop.mariechen.com' || strstr($_SERVER['HTTP_HOST'], 'test')) {
            $this->PageCssClasses="is-preview";
        }
    }



    public function CurrentLanguage()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $lang_from_url=$this->getRequest()->getHeader('mw-language');
            $lang=$lang_from_url?$lang_from_url:"en";
            $GLOBALS['CurrentLanguage']=$lang;
            $this->cache[__FUNCTION__]=$lang;
        }
        return $this->cache[__FUNCTION__];
    }


    public function trans($txt_en, $txt_de)
    {
        switch ($this->CurrentLanguage()) {
            case 'de':
                return $txt_de;
                break;
            
            default:
                return $txt_en;
                break;
        }
    }
  
    public function CartUrl()
    {
        return PageManager::getPage('/de/cart')->Link();
    }


    public function ie($version)
    {
        if ($version<11) {
            if (preg_match("/(Trident\/(\d{2,}|7|8|9)(.*)rv:(\d{2,}))|(MSIE\ (\d{2,}|8|9)(.*)Tablet\ PC)|(Trident\/(\d{2,}|7|8|9))/", $_SERVER["HTTP_USER_AGENT"], $match) != 0) {
                // print 'You are using IE11 or above.';
                return false;
            }
        }

        if (strpos($_SERVER['HTTP_USER_AGENT'], $version) !== false) {
            return true;
        }

        return false;
    }



    public function SiteName()
    {
        return 'My Site';
    }
  
  
 

  
    public function getCurrentSite()
    {
        if (!$this->cache[__FUNCTION__]) {
            $ret=DataObject::get_by_id(SiteTree::class, 18);
       
            $this->cache[__FUNCTION__]=$ret;
        }

        return $this->cache[__FUNCTION__];
    }


    public function SiteSection()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $sectionRoot=$this->Level(2)->URLSegment;
            if (!$sectionRoot) {
                $sectionRoot='home';
            }
            $this->cache[__FUNCTION__]=$sectionRoot;
        }
        return $this->cache[__FUNCTION__];
    }
    
    public function LightOrDark()
    {
        // $section=$this->SiteSection()
        switch ($this->SiteSection()) {
            case 'home':
            case 'shop':
            case 'txt':
                    return 'light';
                break;
            
            default:
                return 'dark';
                break;
        }
    }



    public function getFooter()
    {
        $siteForFooter = Controller::curr()->CurrentSite;
        if (!$siteForFooter) {
            $siteForFooter = $this->CurrentSite;
        }
        if ($siteForFooter && $siteForFooter->hasMethod('getFooter')) {
            return $siteForFooter->getFooter();
        }
        return null;
    }
  
  
    public function TopNavItems()
    {
        if ($this->CurrentSite) {
            $ret=$this->CurrentSite->UnHiddenChildren();
            $home=$this->CurrentSite;
            $home->URLSegment='home';
            $ret->unshift($home);
            return $ret;
        }
    }
  
  
    public function SubNavItems()
    {
        if ($this->Level(2)) {
            $subnavitems=$this->Level(2)->UnHiddenChildren();
        }

        return $subnavitems;
    }

    public function index(SilverStripe\Control\HTTPRequest $request)
    {
        return array();
    }

  
    // include c4p stuff ---------- BEGIN

    public function getShop()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=new MysiteShop($this);
        }
        return $this->cache[__FUNCTION__];
    }

    // include c4p stuff ---------- END

    // ---- start caching functions


    public function CacheRequirements()
    {
        Requirements::setMwCacheMode('set');
        return "<!-- caching requirements -->";
    }

    public function MasterCacheKey()
    {
        static $md5key;
        if (!$md5key) {
            Requirements::setMwCacheMode('get');

            $key = FrontendPage::$masterCacheKeyPart;
            $key .= $this->CurrentSite->LastEdited;
            $key .= $this->CurrentLanguage();
            $key .= $_SERVER['HTTP_HOST'];
            $key .= $this->urlParams['Action'];
            $key .= $this->urlParams['ID'];
            $key .= array_get($_GET, 'after_login');
            

            if ($this->dataRecord) {
                if ($cm = $this->dataRecord->CachingMode) {
                    if ($cm == 'daily') {
                        $key .= Date('Ymd');
                    } elseif ($cm == 'hourly') {
                        $key .= Date('YmdH');
                    } elseif ($cm == 'none') {
                        $key .= microtime();
                    } else {
                        $key .= Date('Ym'); //monthly
                    }
                }
                if (Controller::curr()->currentPortal && Controller::curr()->currentPortal->BasisPaket) {
                    $key .= Controller::curr()->currentPortal->LastEdited;
                }
                if ($this->dataRecord->AliasPage) {
                    $key .= $this->AliasPage->LastEdited;
                } else {
                    $key .= $this->dataRecord->LastEdited;
                }
            }

            if (array_get($_SERVER, 'REQUEST_METHOD') == 'POST' || (array_get($_REQUEST, 'no_cache'))  || (array_get($_REQUEST, 'nc'))) {
                $key .= microtime(); // no cache when post
            } else {
                $key .= implode(',', $_GET);
            }

            if (array_get($_GET, 'cachekey')) {
                echo "\n<li>cachekey:$key";
            }
            $md5key=md5($key);
            Requirements::setMwCacheKey($md5key);
        }
        return $md5key;
    }
    public function myLastEdited()
    {
        //for caching:
        $record = $this->dataRecord;
        if (!$record) {
            $record = $this->record;
        }
        if ($record) {
            if ((array_get($_REQUEST, 'clear_cache'))) {
                if (!$record->overridden) {
                    $record->LastEdited = Datum::mysqlDate(time());
                    $record->write();
                }
            }
            return  $record->LastEdited;
        } else {
            return time();
        }
    }

    // ---- end caching functions
}


class FrontendPageBEController extends BpMysitePageController
{
    public function getRawTabItems()
    {
        $items=array(
         "10"                        => "Basics",
         "20"                        => "Settings",
         "23"                        => "SEO",
         );

        return $items;
    }

    public function step_23()
    {

        // -        Ev open Graph Image upload eines Sonderformates (für alle „Nicht Produkt Pages“, i.e. overview Pages, für die man eigene Bilder definieren kann)
        

        $p              = [];
        $p['fieldname'] = "Config_GoogleTitle";
        $p['label']     = "Google Title";
        $this->formFields[$p['fieldname']]=$p;


        $p              = [];
        $p['fieldname'] = "Config_MetaDescription";
        $p['label']     = "Meta Description";
        $p['type']      = 'textarea';
        $this->formFields[$p['fieldname']]=$p;


        $p              = [];
        $p['fieldname'] = "Config_OgImageID";
        $p['label']     = "Open Graph Image";
        $p['type']      = "MwFileField";
        $this->formFields[$p['fieldname']]=$p;
    }
}
