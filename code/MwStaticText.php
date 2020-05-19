<?php

use SilverStripe\View\ViewableData;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Core\Injector\Injector;

/**
 *
 */

class MwStaticEntity extends ViewableData
{

    var $Entity;
    var $ScopeTexts;
    
    public function __construct($Entity)
    {
        $this->Entity=$Entity;
        $this->ScopeTexts=MysiteStaticText::getTextsByScope($this->Entity);
    }

    public function Scope($scope)
    {
        return $this->ScopeTexts[$scope];
    }
    
    public function getID()
    {
        return $this->Entity;
    }
    
    public function PreviewString($lang)
    {
        
        $default_str=$this->Scope("default/$lang")->PreviewString;
        $custom_str=$this->Scope("mysite/$lang")->PreviewString;

        if ($custom_str) {
            return "<span class='scope-mysite'>$custom_str</span>";
        } else {
            return "<span class='scope-default'>$default_str</span>";
        }
    }
}

class MwStaticText extends DataObject
{


    static $conf = array();
  
    public static function conf($key)
    {
        return self::$conf[$key];
    }

    public static function setConf($key, $value)
    {
        self::$conf[$key]=$value;
    }


    static $disabled=false;
    static $cache;
  
    private static $db = array(
        'Entity'  => 'Varchar(255)', //anything, silverstripe promotes: Namespace.EntityName, we include: 'RAW_' (multiline, no modification),'TEXT' (multiline, auto-nl2br),'HTML_'(wysiwyg web),'MAIL_' (wysiwyg, mail) or nothing (1 line edit, no modification)
        'Scope'   => 'Varchar(40)', // 'default/$LANG' , or 'scope/lang'  (last 2-char shortcut is treated as lang)
        'String'  => 'HTMLText',
        'Context' => 'Varchar(255)',
    // 'URL' => 'Varchar(255)',
    // 'ReadCount' =>'Int',
    // 'LastAccess' =>'Date'
    );




    private static $indexes = [
        'StaticTextMultiKey' => [
            'type'    => 'unique',
            'columns' => [
                'Entity',
                'Scope',
            ],
        ],
    ];
    
    
    public function getPreviewString()
    {
        return $this->String;
    }
    
    
    
    public static function getTextsByScope($entity)
    {
        $entity=Convert::raw2sql($entity);
        $res=DataObject::get('MwStaticText', "Entity='$entity'");
        $ret=array();
        if ($res) {
            foreach ($res as $r) {
                $ret[$r->Scope]=$r;
            }
        }
        return $ret;
    }
  
    public function isEmpty()
    {
        return ($this->String)?0:1;
    }
  
    public static function saveString($str, $entity, $scope, $context = "")
    {
        $entity=Convert::raw2sql($entity);
        $scope=Convert::raw2sql($scope);
      
        $res=DataObject::get_one('MwStaticText', "Entity='$entity' and Scope='$scope'");
      
        if (!$res) {
            $res=new MwStaticText;
        }
      
        $res->String=$str;
        $res->Entity=$entity;
        $res->Scope=$scope;
        $res->Context=$context;
        $res->write();
      
        return $res;
    }

    public static function disable()
    {
        self::$disabled=1;
    }

    public static function getCurrentLanguage()
    {
        if (!isset(self::$cache[__FUNCTION__])) {
            if ($locale=i18n::get_locale()) {
                self::$cache[__FUNCTION__]=substr($locale, 0, 2);
            }

            if (!self::$cache[__FUNCTION__]) {
                self::$cache[__FUNCTION__]='en';
            }

            if (array_get($_GET, 'd')=='locale') {
                echo "<li>locale 4 string:".i18n::get_locale();
            }
        }
      
        return self::$cache[__FUNCTION__];
    }
    
  // static public function getCurrentScope($l='')
  // {
  //     if(!$l)
  //         $l=self::getCurrentLanguage();
  //
  //     if(!isset(self::$cache[__FUNCTION__.$l]))
  //     {
  //         if(!self::$cache[__FUNCTION__.$l])
  //             self::$cache[__FUNCTION__.$l]='mysite/'.$l;
  //     }
  //     return self::$cache[__FUNCTION__.$l];
  // }
  //

    public static function getLanguageList()  // languages, highest prio first
    {
        if (!isset(self::$cache[__FUNCTION__])) {
            $arr=array();
            $arr[self::getCurrentLanguage()]=1;
            $arr['en']=1;
            self::$cache[__FUNCTION__]=$arr;
        }
        return self::$cache[__FUNCTION__];
    }
  
    public static function getWantedScopes()  // scopes (+languages), highest prios first
    {
        if (!isset(self::$cache[__FUNCTION__])) {
            $scopes=array();

            foreach (self::getLanguageList() as $lang => $dummy) {
                $scopes['mysite/'.$lang]=1;
            }

            foreach (self::getLanguageList() as $lang => $dummy) {
                $scopes['default/'.$lang]=1;
            }
            self::$cache[__FUNCTION__]=$scopes;
        }
        return self::$cache[__FUNCTION__];
    }
  
    public static function translate($entity, $string, $injection)
    {



        if ($entity=='SilverStripe\\Security\\Group.NEWGROUP' || $entity=='AssetAdmin.NEWFOLDER' || $entity=='SilverStripe\\SiteConfig\\SiteConfig.SITENAMEDEFAULT' || $entity=='SilverStripe\\SiteConfig\\SiteConfig.TAGLINEDEFAULT') { //skip some entities (causing trobles because loaded before entity setting)
            return $string;
        }

        if (MwStaticText::conf('useLegacy')=='naturfreunde') {
            $ret=  MysiteStaticText::translate_legacy($entity, $string, $injection);
            if($ret!=="__not_handled__" ) {
                return $ret;
            }

        }

        //find value in db
        $my_candidates=self::getTextsByScope($entity);
      
        // loop thru candidates and find first one
      
      
        //  if(array_get($_GET,'d') || 1 ) { $x=array_keys($my_candidates); $x=htmlspecialchars(print_r($x,1));echo "\n<li>ArrayList: <pre>$x</pre>"; }
      
        foreach (self::getWantedScopes() as $scope => $dummy) {
            //  echo "<li>look 4 <strong>$entity</strong> in scope <strong>$scope</strong>";
            // if (strstr($scope, 'default')) {
            //     continue;
            // }
            if ($retObj=$my_candidates[$scope]) {
                if ($retObj && !$retObj->isEmpty()) {
                    // echo "... <strong>found {$retObj->String}</strong>";
                    break;
                }
            }
        }
      
        if (!$retObj) {
            $default_val=Injector::inst()->get('FallbackMessageProvider')->translate($entity, $string, $injection);


            $retObj=$default_val;
            if (!$my_candidates['default/'.self::getCurrentLanguage()]
            || $my_candidates['default/'.self::getCurrentLanguage()] != $default_val
            ) {
                // save default in db, if changed ---------- BEGIN
                if ($default_val) {
                    // $newrecord=self::saveString($default_val, $entity, 'default/'.self::getCurrentLanguage(), $context);
                    if ($newrecord && !$retObj) {
                        $retObj=$newrecord;
                    }
                }
                // save default in db, if changed ---------- END
            } 
        }
      
        if (is_object($retObj) && !$retObj->isEmpty()) {
            $ret=$retObj->String;
        } else {
            $ret=$retObj;
        }
      
        if (!$ret) {
            $ret="{{$entity}}";
        }
      
        if ($_SESSION['debug_translations']) {
            $ret="[[{$entity}]]$ret";
        }
        return $ret;
    }
  
 
  // public function getEditLink()
  //  {
  //    if(Permission::check("EDIT_STATIC_TEXTS") && !strstr($this->Entity,'Forum') && ! self::$disabled )
  //    {
  //      //load boxy for popups
  //      Requirements::javascript("Mwerkzeug/thirdparty/boxy/javascripts/jquery.boxy.js");
  //          Requirements::CSS("Mwerkzeug/thirdparty/boxy/stylesheets/boxy.css");
  //
  //      return "<a href='/BE/MwStaticText/IframeEdit/{$this->ID}/?type=$type' title='{$this->Entity}' class='statictextedit iframepopup' afterHide='javascript:window.reload();'><span></span>edit</a>";
  //
  //    }
  //  }
}


class MwStaticTextController extends BackendPageController
{



    private static $allowed_actions = [
        'ehp',
    ];

    public function getVisibleScopes()
    {
        $arr=array();
        $arr['mysite/en']=1;
        $arr['mysite/de']=1;
        $arr['default/en']=1;
        $arr['default/de']=1;
        return $arr;
    }
    


    public function LanguageList()
    {
        $langs=$this->getLanguages();
        $al=new ArrayList();
        if ($langs) {
            foreach ($langs as $lang) {
                $al->push(new ArrayData($lang));
            }
        }
        return $al;
    }
    
    public function getLanguages()
    {
        $scopes=$this->EHP_getScopes();
        foreach ($scopes as $s => $description) {
            list($dummy,$rec['Code'])=explode('/', $s);
            $langs[$rec['Code']]=$rec;
        }
        return $langs;
    }


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
        
        echo  $this->EHP->dispatch();
        exit();
    }

    public function index(SilverStripe\Control\HTTPRequest $request)
    {
        EHP::includeRequirements();
        
        MwBackendPageController::includePartialBootstrap();
        
        return array();
    }
    
    public function EHP_getScopes()
    {
        return $this->getVisibleScopes();
    }
    

    function EHP_getJSONColumnDefinitions()
    {
        
        $coldef=json_decode('{"Entity":{"label":"Key"}}', 1);
    
        $langs=$this->getLanguages();
        foreach ($langs as $lang) {
            $coldef[$lang['Code']]=array('label' => $lang['Code']);
        }

        // $scopes=$this->EHP_getScopes();
        //
        // foreach ($scopes as $s=>$description) {
        //     $coldef[$s]=Array('label' => $s);
        // }
        
        return json_encode($coldef);
    }

    
    
    public function EHP_columnTemplates()
    {

        $ct=array(
            'Key' => '$Entity',
        );

        foreach ($this->getLanguages() as $key => $value) {
            $ct[$key]='$PreviewString("'.$key.'")';
        }
        
        return $ct;
    }
    
    // public function EHP_rowTpl()
    // {
    //     if(!isset($this->cache[__FUNCTION__]))
    //     {
    //         $tpl='
    //             <td>$Entity</td>
    //         ';
    //         $scopes=$this->EHP_getScopes();
    //
    //         foreach ($scopes as $s=>$dummy) {
    //             $tpl.="<td>\$Scope('$s').PreviewString</td>";
    //         }
    //
    //
    //        $this->cache[__FUNCTION__]=$tpl;
    //     }
    //     return $this->cache[__FUNCTION__];
    //
    //
    //     return $tpl;
    // }
    
    public function EHP_loadRecord($dbid)
    {
        $ret= new MysiteStaticEntity($dbid);
        return $ret;
    }
  
    public function EHP_roweditHTML($record, $options)
    {
        
        
        $html=$this->getEditHtmlForEntity($record, $options);
        
        return "<td colspan='".$this->EHP->ColCount."'>
             $html
            </td>";
    }
  
    public function EHP_rowButtons()
    {
        return implode("\n", array($this->EHP->defaultButton('inlineedit')));
    }
    

    public function EHP_Items()
    {
        $dos=new ArrayList();
        
        $db=DBMS::getMdb();
        
        //count query
        
        $sql="
            select count (distinct Entity)
            from
                MwStaticText
            ";
        $totalcount=$db->getOne($sql);
        
        if (!$totalcount) {
            return $dos;
        }
            
        //fetch query
        $sql="
            select
                distinct Entity
            from
                MwStaticText
            order by Entity";
        
        $res=$db->getAssoc($sql);


        if (MwStaticText::conf('entityWhitelist')) {
            $wl=MwStaticText::conf('entityWhitelist');
        }
        
        foreach ($res as $row) {
            $e=$row['entity'];
            if ($wl && !preg_match('#'.$wl.'#', $e)) {
                continue;
            }
            $dos->push(new MysiteStaticEntity($e));
        }
        
        

        //$dos->setPageLimits(array_get($_GET,'start'), $this->getPagesize(), $matchingIDs3->count());
        //TODO fix
        $dos->limit(500, array_get($_GET, 'start'));
        

        
        return $dos;
    }
    
    public function getDescriptionForScope($scope)
    {
        return "";
    }
    
    
    public function getPreviewHtmlForText($mwtext)
    {
        
        if ($mwtext) {
            $str=$mwtext->PreviewString;
        } else {
            $str=""; //<span class=\"label label-info\">empty</span>";
        }
        
        return "<div class=\"control-group\"> 
            <div class=\"controls\">             
            $str
            </div> ";
    }
    
    
    public function ajaxGetScopeEditHtml()
    {
        $scope=array_get($_GET, 'scope');
        $entity=array_get($_GET, 'entity');
        $entityrec=new MysiteStaticEntity($entity);
        echo $this->getEditHtmlForText($entityrec->Scope($scope), $entityrec, $scope);
        exit();
    }
    
    public function ajaxSaveScope()
    {
        
        // if(array_get($_GET,'d') || 1 ) { $x=$_POST; $x=htmlspecialchars(print_r($x,1));echo "\n<li>ArrayList: <pre>$x</pre>"; }

        $rec=MysiteStaticText::saveString(array_get($_POST, 'String'), array_get($_POST, 'Entity'), array_get($_POST, 'Scope'));

        echo $this->getPreviewHtmlForText($rec);
        exit();
    }
    
    public function getEditHtmlForText($mwtext, $entityrec, $scope)
    {
     
        if ($mwtext) {
            $str=$mwtext->String;
        } else {
            $str=""; //<span class=\"label label-info\">empty</span>";
        }
        
        $defaulttext=$entityrec->Scope(str_replace('mysite/', 'default/', $scope))->String;
        
        return "<div class=\"control-group\"> 
             <div class=\"controls\"> 
        
                 <input type='text' class=\"scopetxtinput input-xlarge\" value='$str' data-entity='{$entityrec->Entity}' data-scope='$scope' placeholder='$defaulttext'></div>
        
            </div>";
    }


    
    public function ScopeIsEditable($s)
    {
        return !strstr($s, 'default/');
    }
    
    
    function getEditHtmlForEntity($entityrec, $conf)
    {
        
        if ($entityrec) {
            $html="<h2>{$entityrec->Entity}</h2><table class='table table-bordered'>";
            
            
            $an=$this->EHP->getActiveColumnNames();
            foreach ($this->getLanguages() as $lang => $dummy) {
                if ($an && !in_array($lang, $an)) {  //language-col visible ?
                    continue;
                }
                
                $s='mysite/'.$lang;

                $mwtext=$entityrec->Scope($s);
                
                $previewhtml=$this->getPreviewHtmlForText($mwtext);
                if (!trim(strip_tags($previewhtml))) {
                        $defscope='default/'.$lang;

                        $previewhtml="<span class='scope-default'>".$this->getPreviewHtmlForText($entityrec->Scope($defscope))."</span>";
                }
                
                if ($this->ScopeIsEditable($s)) {
                    $editbtn="<a class=\"btn btn-small editscope\"><i class=\"icon-pencil\"></i></a>";
                } else {
                    $editbtn="&nbsp;";
                }
                $html.="
                        <tr data-scope='$s' data-entity='{$entityrec->Entity}' class='scope_row'>
                            <td><strong>$lang</strong></td>
                            <td class='scope_editbuttons'>
                                $editbtn
                            </td>
                
                            <td class='scope_preview'>
                                $previewhtml
                            </td>
                            
                        </tr>
                
                ";
            }
            $html.="</table>";
            $html.="
                <script>
                
            
                    $('.editscope').live('click',function(e)
            {
                e.preventDefault();
                url='/BE/StaticTexts/ajaxGetScopeEditHtml/?entity='+$(this).closest('.scope_row').data('entity')+'&scope='+$(this).closest('.scope_row').data('scope');
                $('.scope_preview',$(this).closest('tr.scope_row')).load(url);
                var btn='<a class=\"btn btn-small btn-primary savescope\"><i class=\"icon-ok icon-white \"></i></a>';
                $('.scope_editbuttons',$(this).closest('tr.scope_row')).html(btn);
                
            });
                    
            $('.savescope').live('click',function(e){
                
                url='/BE/StaticTexts/ajaxSaveScope/?entity='+$(this).closest('.scope_row').data('entity')+'&scope='+$(this).closest('.scope_row').data('scope');
                var field=$('input.scopetxtinput',$(this).closest('.scope_row'));
                
                var data={
                    'String':field.val(),
                    'Entity':field.data('entity'),
                    'Scope':field.data('scope')
                };
                $('.scope_preview',$(this).closest('tr.scope_row')).load(url,data);
                $('.scope_editbuttons',$(this).closest('tr.scope_row')).html('<a class=\"btn btn-small editscope\"><i class=\"icon-pencil\"></i></a>');
                
                e.preventDefault();
            });
                </script>
                ";
        }

        
        return $html;
    }
}
