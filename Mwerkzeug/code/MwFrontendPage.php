<?php

use SilverStripe\View\Requirements;
use SilverStripe\i18n\i18n;
use SilverStripe\Control\Session;
use SilverStripe\Control\Controller;

class MwFrontendPage extends MwSiteTree
{
    public $cache;

    public function PreviewLink($action = null)
    {
        return $this->Link()."?preview=".time();
    }


    public function Linkify($fieldname)
    {
        return MwLink::resolveLinks($this->$fieldname);
    }
 
    public function canView($member = null)
    {
        return true;
    }
}

class MwFrontendPageController extends MwSiteTreeController
{
    public $cache;

    public function init()
    {
        parent::init();
        $this->initBack2Edit();
    }
  
    public function initBack2Edit()
    {
        if (array_get($_GET, 'preview')) {
            Requirements::insertHeadTags('<script type="text/javascript" src="/BE/Pages/ng_pagemanager/translatedTemplate/Script_Back2Edit"></script>');
            Requirements::CSS("Mwerkzeug/css/skin2/back2edit.css");
        }
    }



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
 
            if (array_key_exists('d', $_GET)) {
                die("\n\n<pre>mwuits-debug 2020-04-07_16:45 ".print_r($key, true));
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


    public function includeBERequirements()
    {
        // include requirements used for Backend-Editing, taken from MwBackendPage.php
      
        Requirements::javascript('Mwerkzeug/thirdparty/jquery-current.min.js');

        Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.core.js');
        Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.widget.js');
        Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.mouse.js');
        Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.sortable.js');
    
        Requirements::javascript("Mwerkzeug/thirdparty/jquery-validate/jquery.validate.js");
        if (i18n::get_locale()=="de_DE") {
            Requirements::javascript("Mwerkzeug/thirdparty/jquery-validate/localization/methods_de.js");
            Requirements::javascript("Mwerkzeug/thirdparty/jquery-validate/localization/messages_de.js");
        }
      
        Requirements::javascript("Mwerkzeug/thirdparty/boxy/javascripts/jquery.boxy.js");
        Requirements::javascript("Mwerkzeug/javascript/MwFrontendBE.js");
        Requirements::CSS("Mwerkzeug/css/MwFrontendBE.css");

        singleton('BackendPageController')->checkForBrowser();
    }
 

    // paging functions ---------- BEGIN



    

    public function getPagingTemplate()
    {
        return 'Includes/Paging';
    }


   
    
    
    
    
    public function getPageSize()
    {
        if (array_get($_GET, 'pagesize') && array_get($_REQUEST, 'pagesize')<100 && array_get($_REQUEST, 'pagesize')>1) {
            Mwerkzeug\MwSession::set('pagesize', array_get($_REQUEST, 'pagesize'));
        }

        if ($ps=Mwerkzeug\MwSession::get('pagesize')) {
            return $ps;
        }

        return 20; //default pagesize
    }

    // paging functions ---------- END

    public static function minimalPageHeader()
    {
        return '
            <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                "http://www.w3.org/TR/html4/loose.dtd">
             <html><head>
                 <link href="https://fonts.googleapis.com/css?family=PT+Sans:400,700italic" rel="stylesheet" type="text/css">
                 <link href="https://fonts.googleapis.com/css?family=Ubuntu:400,700" rel="stylesheet" type="text/css">
           
                 <link rel="stylesheet" href="/Mwerkzeug/css/minimal.css" type="text/css" charset="utf-8">
                 </head><body>
               
            ';
    }
    
    public function CurrentYear()
    {
        return Date('Y');
    }
}
