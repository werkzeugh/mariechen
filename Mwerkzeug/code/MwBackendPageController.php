<?php

use SilverStripe\ORM\FieldType\DBTime;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\View\SSViewer;
use SilverStripe\View\Requirements;
use SilverStripe\i18n\i18n;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Session;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ArrayData;
use SilverStripe\Security\PermissionProvider;
use Mwerkzeug\MwRequirements;
use SilverStripe\Core\Environment;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\TempFolder;

class MwBackendPageController extends MwSiteTreeController implements PermissionProvider
{
    private static $theme = 'Mwerkzeug:MwBackend';

    private static $allowed_actions = [
        'debug_tmpinfo',
        'debug_phpinfo',
        'debug_error',
        'getDBConfig',
        'debug_serverinfo',
        'processSorting',
    ];

    public $mainurl;
    public $suburl;
    public $record;
    public $allowAccess = false;
    public $UserCanEdit = 1;

    public static $pre_html = "";
    public static $post_html = "";

    public $noQuery13 = false;




    public function handleRequest(HTTPRequest $request, DataModel $model = null)
    {
        $params = $request->latestParams();
        $actionUrl = implode("/", $params);

        if (trim($actionUrl, '/')) {
            $request->setUrl($actionUrl);
        }

        return parent::handleRequest($request, $model);
    }



    public function init()
    {
        SSViewer::set_themes([self::$theme, '$default']);


        $this->checkForBrowser();

        if (!Permission::check("ENTER_BE") && !$this->AccessedViaFlash && !$this->accessIsAllowed()) {
            Security::permissionFailure();
        }

        $user = $this->CurrentMember();
        if ($user && $user->useNewSkin()) {
            MwPage::setConf('skinVersion', 2);
        }

        Requirements::set_write_js_to_body(false);
        Requirements::set_combined_files_enabled(false);
        parent::init();
        Requirements::clear();


        $this->includeJquery();


        //do not forget to update that as well in MwFrontendPage:

        Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.core.js');
        Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.widget.js');
        Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.mouse.js');
        Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.sortable.js');
        Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.draggable.js');

        // Requirements::javascript("sapphire/thirdparty/jquery-livequery/jquery.livequery.js"); // deprecated ??
        Requirements::javascript("Mwerkzeug/thirdparty/jquery-validate/jquery.validate.js");
        if (i18n::get_locale() == "de_DE") {
            Requirements::javascript("Mwerkzeug/thirdparty/jquery-validate/localization/methods_de.js");
            Requirements::javascript("Mwerkzeug/thirdparty/jquery-validate/localization/messages_de.js");
        }
        Requirements::javascript("Mwerkzeug/thirdparty/boxy/javascripts/jquery.boxy.js");

        //for tags (really need to laod always ??)

        MwRequirements::CSS("mysite/css/typography.css");


        Requirements::css("Mwerkzeug/bower_components/font-awesome/css/font-awesome.min.css");

        // Requirements::insertHeadTags('<script src="https://kit.fontawesome.com/b72e881e76.js"></script>');
        

        Requirements::CSS("Mwerkzeug/css/skin2/skin2.css");
        Requirements::javascript("Mwerkzeug/javascript/MwBackend_v2.js");
        // MwRequirements::javascript("mysite/javascript/backend_v2.js");
        MwRequirements::CSS("mysite/css/backend.css");
     

        Requirements::CSS("Mwerkzeug/thirdparty/boxy/stylesheets/boxy.css");

        $html = '';
        Requirements::insertHeadTags("<link rel=\"shortcut icon\" href=\"/Mwerkzeug/images/favicon_be" . ($this->isLocal() ? '_local' : '') . ".ico\" />", $html);

        if ($this->ID == -1) { //no real page from DB
            //fetch URLsegmemnt from URL
            preg_match('#(/BE/[^/]+)#', array_get($_SERVER, 'REQUEST_URI'), $m);
            $this->URLSegment = $m[1];


            if ($this->getRequest()->latestParam('Within')) {
                $this->URLSegment .= "/" . Controller::curr()->urlParams['Within'];
            }

            $this->getRequest()->setLatestParam('URLSegment', $this->URLSegment);
        }

        Requirements::set_combined_files_enabled(false);


        if ((array_get($_GET, 'debug_translations'))) {
            $_SESSION['debug_translations'] = array_get($_GET, 'debug_translations');
        }
    }


    public function HeaderTopHtml()
    {
        return $this->renderWith(array('Includes/BackendPage_HeaderTop_local', 'Includes/BackendPage_HeaderTop'));
    }

    public static function includeJquery()
    {
        Requirements::javascript('Mwerkzeug/thirdparty/jquery/jquery-1.10.2.min.js');
        Requirements::javascript('Mwerkzeug/thirdparty/jquery/jquery-migrate-1.2.1.min.js');
    }

    public function isAdmin()
    {
        return Permission::check('ADMIN');
    }

    public function accessIsAllowed()
    {
        if ($this->calledViaCommandline()) {
            return true;
        }

        return ($this->allowAccess) ? 1 : 0;
    }

    public function includeMustache()
    {
        Requirements::javascript("Mwerkzeug/thirdparty/mustache/mustache.min.js");
    }

    public function includeNoty()
    {
        Requirements::javascript("Mwerkzeug/thirdparty/noty/js/jquery.noty.js");
        Requirements::css('Mwerkzeug/thirdparty/noty/css/jquery.noty.css');
        Requirements::css('Mwerkzeug/thirdparty/noty/css/noty_theme_twitter.css');
        Requirements::customScript("
            $.noty.defaultOptions.theme='noty_theme_twitter';

            ");
    }


    public function getDBConfig()
    {
        global $databaseConfig;

        echo "export SS_DATABASE_SERVER=" . Environment::getEnv('SS_DATABASE_SERVER') . "\n";
        echo "export SS_DATABASE_USERNAME=" . Environment::getEnv('SS_DATABASE_USERNAME') . "\n";
        echo "export SS_DATABASE_PASSWORD=" . Environment::getEnv('SS_DATABASE_PASSWORD') . "\n";
        echo "export SS_DATABASE_NAME=" . Environment::getEnv('SS_DATABASE_NAME') . "\n";


        $REMOTE_HOST_SSH = MwPage::conf('RemoteHostSsh');
        if (!$REMOTE_HOST_SSH) {
            $REMOTE_HOST_SSH = 'vserver3.werkzeugh.at';
        }
        echo "export REMOTE_HOST_SSH=$REMOTE_HOST_SSH \n";
    }


    public function debug_serverinfo()
    {
        echo shell_exec("hostname");
        die();
    }

    public function debug_tmpinfo()
    {
        $dir =  TempFolder::getTempFolder(BASE_PATH);
        echo $dir . "\n";
        echo shell_exec("ls -la $dir");
        echo shell_exec("pwd");
        echo shell_exec("hostname");

        die();
    }

    public function debug_phpinfo()
    {
        phpinfo();
        $x=$_SERVER;
        $x=htmlspecialchars(print_r($x, 1));
        echo "\n\$_SERVER: <pre>$x</pre>";
        echo "\n<div>&nbsp;</div>sys_get_temp_dir: ".sys_get_temp_dir();
        die();
    }
  
    public function debug_error()
    {
        echo 5/0;
        die();
    }

    public function debug_dbms()
    {
        $db = DBMS::getMdb();
        $sql = "select count(*)  as pagecount from SiteTree_Live";
        $res = $db->getOne($sql);
        if (array_get($_GET, 'd') || 1) {
            $x = $res;
            $x = htmlspecialchars(print_r($x, 1));
            echo "\n<li>mwuits: <pre>$x</pre>";
        }


        die();
    }


    public function debug_templates()
    {
        $loader = SS_TemplateLoader::instance();
        echo "<li>current theme:" . SSViewer::current_theme();
        $tpls = $loader->getManifest()->getTemplates();

        if (array_get($_GET, 'd') || 1) {
            $x = $tpls;
            $x = htmlspecialchars(print_r($x, 1));
            echo "\n<li>mwuits: <pre>$x</pre>";
        }


        die();
    }


    public function includeBootstrap()
    {
        Requirements::javascript("Mwerkzeug/bootstrap/js/bootstrap-transition.js");
        Requirements::javascript("Mwerkzeug/bootstrap/js/bootstrap-alert.js");
        Requirements::javascript("Mwerkzeug/bootstrap/js/bootstrap-modal.js");
        Requirements::javascript("Mwerkzeug/bootstrap/js/bootstrap-dropdown.js");
        Requirements::javascript("Mwerkzeug/bootstrap/js/bootstrap-scrollspy.js");
        Requirements::javascript("Mwerkzeug/bootstrap/js/bootstrap-tab.js");
        Requirements::javascript("Mwerkzeug/bootstrap/js/bootstrap-tooltip.js");
        Requirements::javascript("Mwerkzeug/bootstrap/js/bootstrap-popover.js");
        Requirements::javascript("Mwerkzeug/bootstrap/js/bootstrap-button.js");
        Requirements::javascript("Mwerkzeug/bootstrap/js/bootstrap-collapse.js");
        Requirements::javascript("Mwerkzeug/bootstrap/js/bootstrap-carousel.js");
        Requirements::javascript("Mwerkzeug/bootstrap/js/bootstrap-typeahead.js");


        if (!MwBackendPage::conf('skipDefaultBootstrapCSS')) {
            Requirements::CSS("Mwerkzeug/bootstrap/css/bootstrap.css");
            Requirements::CSS("Mwerkzeug/bootstrap/css/bootstrap-responsive.css");
            Requirements::CSS("Mwerkzeug/css/font-awesome.min.css");
        }
    }

    public static function includePartialBootstrap($options = null)
    {
        $available_scripts = explode(',', 'transition,alert,modal,dropdown,scrollspy,tab,tooltip,popover,button,collapse,carousel,typeahead');

        $scripts = array();
        if ($options['scripts']) {
            if ($options['scripts'] == 'all') {
                $scripts = $available_scripts;
            } else {
                $requested_scripts = explode(',', $options['scripts']);
                if ($requested_scripts) {
                    foreach ($requested_scripts as $key) {
                        if (in_array($key, $available_scripts)) {
                            $scripts[] = $key;
                        }
                    }
                }
            }
        }


        foreach ($scripts as $key) {
            Requirements::javascript("Mwerkzeug/bootstrap/js/bootstrap-{$key}.js");
        }


        if (!MwBackendPage::conf('skipDefaultBootstrapCSS')) {
            Requirements::CSS("Mwerkzeug/bootstrap/css/partial_bootstrap.css");
            Requirements::CSS("Mwerkzeug/css/font-awesome.min.css");
            // Requirements::CSS("Mwerkzeug/bootstrap/css/bootstrap-responsive.css");
        }
    }


    public function providePermissions()
    {
        return array(
            "ENTER_BE" => "Access the User-Backend /BE",
        );

        Requirements::set_write_js_to_body(false);
    }

    public static function minimalPageHeader()
    {

        //header("Content-type:text/html; charset=utf8");

        return '
          <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
              "http://www.w3.org/TR/html4/loose.dtd">
           <html ><head>
               <meta http-equiv="content-type" content="text/html; charset=utf-8" />
               <link href="https://fonts.googleapis.com/css?family=PT+Sans:400,700italic" rel="stylesheet" type="text/css">
               <link href="https://fonts.googleapis.com/css?family=Ubuntu:400,700" rel="stylesheet" type="text/css">

               <link rel="stylesheet" href="/Mwerkzeug/css/minimal.css" type="text/css" charset="utf-8">
               </head><body>
          ';
    }


    public static function viewArray($array_in)
    {
        if (!is_array($array_in)) {
            $array_in = array(gettype($array_in) => $array_in);
        }

        if (is_array($array_in)) {
            reset($array_in);
            $result = '<table border=1 cellpadding=1 cellspacing=0 bgcolor="white" class="table table-bordered table-condensed table-striped">';
            if (!count($array_in)) {
                $result .= '<tr><td style="font:11px verdana"><b>' . HTMLSpecialChars("EMPTY!") . '</b></td></tr>';
            }
            while (list($key, $val) = each($array_in)) {
                $result .= '<tr><td style="font:11px verdana">' . HTMLSpecialChars($key) . '</td><td>';
                if (is_array($array_in[$key])) {
                    $result .= self::viewArray($array_in[$key]);
                } else {
                    $result .= '<span style="font:11px verdana;color:red">' . nl2br(HTMLSpecialChars($val)) . '<BR>';
                }
                $result .= '</td></tr>';
            }
            $result .= '</table>';
        }

        return $result;
    }


    public static function calledViaCommandline()
    {
        return (strstr(array_get($_SERVER, 'argv', [""])[0], 'framework/cli-script.php'));
    }

    public function minimalIEVersion()
    {
        if ($this->SkinVersion >= 2) {
            return 9;
        }
        return 8;
    }


    public function checkForBrowser()
    {
        $ua = $this->browser_info();
        if (self::calledViaCommandline()) {
            return true;
        }


        if (($ua['firefox'] >= 5 || $ua['safari'] >= 5 || $ua['msie'] >= $this->minimalIEVersion())) {
            return true;
        } elseif (preg_match('#(wget|AppleWebKit.*Mobile|SetCronJob)#i', array_get($_SERVER, 'HTTP_USER_AGENT'))) {
            return true;
        } elseif (stristr(array_get($_SERVER, 'HTTP_USER_AGENT'), 'Flash')) {
            $this->AccessedViaFlash = true;
            return true;
        } else {
            $debug_info = Date('Y-m-d H:i:s') . " | {array_get($_SERVER,'REMOTE_ADDR')} | {array_get($_SERVER,'HTTP_USER_AGENT')}";

            if (i18n::get_locale() == "de_DE") {
                $txt = "
            <div class='info'>
                <h1>Systemvorraussetzungen nicht erfüllt</h1>
                diese Seite erfordert mindestens einen der folgenden Browser:
                <ul>
                    <li>eine aktuelle Version von Google Chrome / Firefox oder Safari</li>
                    <li>Internet Explorer ab Version 9</li>
                </ul>
                Bei Rückfragen bitte folgende Zeilen angeben:

                 <div>&nbsp;</div>
                 <div style='font-family:courier'>$debug_info</div>
                 <div>&nbsp;</div>

            </div>";
            } else {
                $txt = "
            <div class='info'>
                <h1>minimal requirements not met</h1>
                this Backend requires at least one of those Web-Browsers:
                <ul>
                    <li>a current version of Google Chrome, Safari or Firefox</li>
                    <li>Internet Explorer 9</li>
                </ul>

                in case of inquiry, please include these lines:
                 <div>&nbsp;</div>
                 <div style='font-family:courier'>$debug_info</div>
                 <div>&nbsp;</div>

            </div>";
            }

            die($this->minimalPageHeader() . $txt);
        }
    }


    public function browser_info($agent = null)
    {
        // Declare known browsers to look for
        $known = array(
            'msie',
            'firefox',
            'safari',
            'webkit',
            'opera',
            'netscape',
            'konqueror',
            'gecko',
        );

        // Clean up agent and build regex that matches phrases for known browsers
        // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
        // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
        $agent = strtolower($agent ? $agent : array_get($_SERVER, 'HTTP_USER_AGENT'));
        $pattern = '#(?P<browser>' . join('|', $known) .
            ')[/ ]+(?P<version>[0-9]+(?:\.[0-9]+)?)#';


        // Find all phrases (or return empty array if none found)
        if (!preg_match_all($pattern, $agent, $matches)) {
            if (preg_match('#trident/.*rv:([0-9]{1,}[\.0-9]{0,})#', $agent, $m)) {
                return array('msie' => $m[1]);  //check for ie11 and upwards
            } else {
                return array();
            }
        }


        // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
        // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
        // in the UA).  That's usually the most correct.
        $i = count($matches['browser']) - 1;
        return array($matches['browser'][$i] => $matches['version'][$i]);
    }


    public function HelpTip($value)
    {
        return HelpTip::getHtml($value);
    }


    public function getDirectOutputHeader()
    {
        //css for direct output

        $html = "
    <style>
      body {margin:10px;background:#aaa}
      body,div,td {font-size:12px;font-family:verdana, arial}
      p, form {margin:0px}
      table {border-collapse:collapse}
      table td {border:1px solid #ccc;pdding:4px}
      #content {background:white;border:1px solid #555;padding:20px;min-height:400px;}
      h1,h2,h3 {font-size:15px}
    </style>
    <div id='content'>

    ";
        return $html;
    }

    public function CurrentBaseURL()
    {
        return $this->URLSegment;
    }


    public function urlParam($name)
    {
        return $this->urlParams[$name];
    }

    public function getUrl_ID()
    {
        return $this->urlParam('ID');
    }

    public function getUrl_OtherID()
    {
        return $this->urlParam('OtherID');
    }

    public static function addPostHTML($html)
    {
        self::$post_html .= $html;
    }

    public static function addPreHTML($html)
    {
        self::$pre_html .= $html;
    }

    public static function postHTML()
    {
        return self::$post_html;
    }

    public static function jQueryWrap($js)
    {
        return "
(function($) {
$(document).ready(function() {

$js

})
})(jQuery);

";
    }


    public static function preHTML()
    {
        return self::$pre_html;
    }

    public function getViewer($action)
    {
        $viewer = parent::getViewer($action);

        if ($tpltype = array_get($_REQUEST, 'tpltype')) {
            $tfile = SSViewer::getTemplateFileByType('BackendPage_' . $tpltype, 'main');
            $viewer->setTemplateFile('main', $tfile);
        }
        return $viewer;
    }


    public function getPagingTemplate()
    {
        return 'Includes/BackendPaging';
    }


    public function getPageSize()
    {
        if (array_get($_REQUEST, 'pagesize') && array_get($_REQUEST, 'pagesize') < 100 && array_get($_REQUEST, 'pagesize') > 1) {
            Mwerkzeug\MwSession::set('pagesize', array_get($_REQUEST, 'pagesize'));
        }

        if ($ps = Mwerkzeug\MwSession::get('pagesize')) {
            return $ps;
        }

        return 100; //default pagesize
    }


    public function getNavigationStructure()
    {
        static $nav = array();

        if (!$nav) {
            $nav = $this->getCustomNavigationStructure();
            //find mainnavitem, by looking which subnavitem is currently active (weird, i knows)
            foreach ($nav as $mainurl => $navitem) {
                if (strstr(array_get($_SERVER, 'REQUEST_URI'), $mainurl)) {
                    $matches[$mainurl] = strlen($mainurl);
                }
                if ($navitem['subitems']) {
                    foreach ($navitem['subitems'] as $suburl => $subitem) {
                        if (strstr(array_get($_SERVER, 'REQUEST_URI'), $suburl)) {
                            $matches[$mainurl] = strlen($suburl);
                            $submatches[$suburl] = strlen($suburl);
                        }
                    }
                }
            }

            if ($matches) {
                asort($matches);
                $this->mainurl = array_pop(array_keys($matches));
            }

            if ($submatches) {
                asort($submatches);
                $this->suburl = array_pop(array_keys($submatches));
            }
        }
        return $nav;
    }

    public function MainNavItems()
    {
        $s = $this->getNavigationStructure();
        $ds = new ArrayList();
        foreach ($s as $url => $navitem) {
            $ds->push(new MwBackendPage_NavItem($url, $navitem['data']));
        }
        return $ds;
    }

    public function SubNavItems()
    {
        $s = $this->getNavigationStructure();
        $ds = new ArrayList();
        if ($this->mainurl) {
            $parentNav = $s[$this->mainurl];
            if ($parentNav['subitems']) {
                foreach ($parentNav['subitems'] as $url => $navitem) {
                    $ds->push(new MwBackendPage_NavItem($url, $navitem['data']));
                }
            }
        }
        return $ds;
    }


    public function navIsActive($navitem) //To override, to set custom rules for nav-determination
    {
        return false;
    }

    public function TimeNow()
    {
        $t = new DBTime();
        $t->setValue(Date('H:i'));
        return $t;
    }


    public function fdata($value)
    {
        return array_get($_REQUEST, 'fdata')[$value];
    }

    /*
        - processes Checkbox-Table-Entries and passes it on to the object itself
        - needs $this->myClass to find out about classname to load
     */

    public function processCheckboxTable()
    {
        if ($items = array_get($_POST, 'items')) {
            foreach ($items as $id) {
                $obj = DataObject::get_by_id($this->myClass, $id);
                if ($obj) {
                    $obj->processCheckboxAction(array_get($_POST, 'action'));
                }
            }
        }

        Controller::curr()->redirect(array_get($_POST, 'NextURL'));
    }

    /*
        - processes Sorting-Feedbacks
        - needs $this->myClass to find out about classname to load
     */

    public function processSorting()
    {
        if ($items = array_get($_POST, 'items')) {
            foreach ($items as $id) {
                $obj = DataObject::get_by_id($this->myClass, $id);

                if ($obj) {
                    $obj->writeNextSortTimestamp();  //defined in SortTimestampExtension
                }
            }
        }

        $nextUrl = array_get($_POST, 'NextURL');
        if (!$nextUrl) {
            $nextUrl = array_get($_SERVER, 'HTTP_REFERER');
        }

        Controller::curr()->redirect($nextUrl);
    }


    /*  --------  BEGIN standard-functions for record-handling */

    public function loadRecord($id = 0)
    {
        if (!$id) {
            $id = Controller::curr()->urlParams['ID'];
        }
        if ($id && $this->myClass) {
            $this->record = Dataobject::get_by_id($this->myClass, $id);
            if ($this->record && $this->record->ClassName && $this->record->ClassName != $this->myClass) {
                $this->record = Dataobject::get_by_id($this->record->ClassName, $id);
            }


            if (!$this->record) {
                $this->record = array();
            }
        }

        if ($this->record) {
            return true;
        } else {
            return false;
        }
    }

    public function isBackend()
    {
        return 1;
    }


    public function ajaxGet()
    {
        $this->loadRecord();

        if ($this->record) {
            return $this->customise($this->record)->renderWith($this->myClass . '_ajaxItem');
        } else {
            return "";
        }
    }

    public function ajaxUnlock()
    {
        if ($this->loadRecord()) {
            $this->record->Visible = 1;
            $this->record->write();
        }
        return $this->customise($this->record)->renderWith($this->myClass . '_ajaxItem');
    }

    public function ajaxLock()
    {
        if ($this->loadRecord()) {
            $this->record->Visible = 0;
            $this->record->write();
        }
        return $this->customise($this->record)->renderWith($this->myClass . '_ajaxItem');
    }

    public function ajaxDelete()
    {
        if ($this->loadRecord()) {
            $this->record->delete();
        }
    }

    /*  --------  END standard-functions  for record-handling */


    /*  --------  BEGIN standard-functions  for edit-tab-handling */

    public function TabItems()
    {
        $lastTabItem = null;
        $currentTabItem = null;

        $navitems = new ArrayList();
        foreach ($this->getRawTabItems() as $url => $name) {
            if ($this->calledViaWithin) {
                $link = "";
            } else {
                $link = Controller::curr()->URLSegment . "/" . Controller::curr()->urlParams['Action'] . "/" . Controller::curr()->urlParams['ID'] . "/" . $url;
            }


            $tabItem = new ArrayData(
                array(
                "Link"       => $link,
                "Title"      => $name,
                "URLSegment" => $url,
                "Current"    => ($url == $this->CurrentTab()),
                )
            );
            if ($tabItem->Current) {
                $this->currentTabItem = $tabItem;
                $this->prevTabItem = $lastTabItem;
            }

            if ($lastTabItem->Current) {
                $this->nextTabItem = $tabItem;
            }

            $navitems->push($tabItem);
            $lastTabItem = $tabItem;
        }
        return $navitems;
    }

    public function CurrentTab()
    {
        if ($this->currentTabID) {
            $id = $this->currentTabID; //only used for "within functionality"
        } else {
            $id = Controller::curr()->urlParams['OtherID'];
        }

        if (!$id) {
            $id = array_shift(array_keys(($this->getRawTabItems())));
        }

        return $id;
    }


    /*  --------  END standard-functions  for edit-tab-handling */


    public static function includeTinyMCE()
    {
        Requirements::javascript("Mwerkzeug/thirdparty/tinymce/jquery.tinymce.js");
        Requirements::javascript("Mwerkzeug/javascript/tinymce_master_config.js");
        Requirements::javascript("mysite/javascript/tinymce_local_config.js");
    }


    public static function includeColorPicker()
    {
        Requirements::javascript("Mwerkzeug/thirdparty/jquery-minicolors/jquery.miniColors.transparent.js");
        Requirements::CSS("Mwerkzeug/thirdparty/jquery-minicolors/jquery.miniColors.css");
        Requirements::customScript('
        jQuery(document).ready(function($) {
            $(".colorpicker").miniColors();
        });
        ');
    }


    public static function getUrlParts()
    {
        $c = Controller::curr()->getRequest()->latestParams();
        $c['ID'] = Controller::curr()->urlParams['ID'];
        $c['OtherID'] = Controller::curr()->urlParams['OtherID'];
        $c['Action'] = Controller::curr()->urlParams['Action'];
        $c['BaseUrl'] = "/BE/" . str_replace('Controller', '', get_class(Controller::curr()));
        return $c;
    }

    public function getUrlPart($key)
    {
        static $c;
        if (!isset($c)) {
            $c = $this->getUrlParts();
        }
        return $c[$key];
    }

    public function getMaxAvailableSkinVersion()
    {
        return 1;
    }

    public function getSkinVersion()
    {
        $v = MwPage::conf('skinVersion');
        if ($v <= $this->maxAvailableSkinVersion) {
            return $v;
        }
        return "";
    }

    public function getSkinVersionPostfix()
    {
        $v = $this->SkinVersion;
        if ($v) {
            return "_v" . $v;
        }
        return "";
    }

    public function ThemeDir()
    {
        return "/Mwerkzeug/themes/MwBackend";
    }
}
