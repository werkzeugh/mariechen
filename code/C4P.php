<?php

use SilverStripe\Control\Controller;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ViewableData;
use SilverStripe\Security\Permission;
use SilverStripe\Forms\FormField;
use SilverStripe\View\ArrayData;
// C4P  "Content for Pages" - a class to manage Content-Element Configurations for Pages
// manfred@monochrom.at 2011-08-28

class C4P extends ViewableData {

  // this class gets instantiated and bound to a DataObject (e.g. a page),
  // somehow like an extension, but silverstripe-extensions somewhat suck

  var $cache;
  var $record;
  var $isC4P;


  function __construct($mainrec)
  {
    $this->record=$mainrec;
  }





  public function getPlaces()
  {
      $fields=$this->record->toMap();
      foreach ($fields as $key => $value) {
          if(strstr($key,'C4Pjson_'))
          {
              if(preg_match('#^C4Pjson_(.+)(Archive)?$#',$key,$m))
              {
                  $ffields[$m[1]]=1;
              }
          }
      }

      if($ffields)
       return array_keys($ffields);

  }

  public function getPlaceConfig($place)
  {
    $place=trim($place);
    if(!isset($this->cache[__FUNCTION__][$place]))
    {
      $confmethod="C4P_Place_".$place;

      if ($settingsFromRequest=Controller::curr()->Settings) {
        if ($place==$settingsFromRequest['c4p_place'] && $settingsFromRequest['placeconf']) {
          $conf=$settingsFromRequest['placeconf'];
        }
      }

      if (!$conf) {

        if($this->record->hasMethod($confmethod))
        {
          $conf=call_user_func(array($this->record, $confmethod));
        }

        if(!is_array($conf))
          $conf=Array();

      // set defaults for place ---------- BEGIN

        if(!isset($conf['min'])) $conf['min']       = 0;
        if(!isset($conf['max'])) $conf['max']       = 100;
        if(!isset($conf['fieldname'])) $conf['fieldname'] = "C4Pjson_".$place;

      // set defaults for place ---------- END
      }

       $this->cache[__FUNCTION__][$place]=$conf;
    }
    return $this->cache[__FUNCTION__][$place];


  }

  public function getDefaultTypeForPlace($place)
  {
    $cfg=$this->getPlaceConfig($place);

    if(is_array($cfg['allowed_types']))
    {
      foreach ($cfg['allowed_types'] as $typename => $settings) {
        if (!$settings['disabled']) {
          $ret=$typename;
          break;
        }
      }
    }

    if(!$typename) {
      if($_POST && array_get($_POST,'fdata') && array_get($_POST,'fdata.CType')) {
        $ret=array_get($_POST,'fdata.CType');
      }
    }

    return $ret;

  }

  public function onBeforeWrite($placename)
  {
    //create default-content if not defined yet

    $placeconfig=$this->getPlaceConfig($placename);
    if($placeconfig['default_content'])
    {
        $fieldname="C4Pjson_".$placename;

      if(!$this->record->ID && !$this->record->{$fieldname}) // new record
      {
        //create defaultcontent:

          $defaulitems=Array();
          foreach ($placeconfig['default_content'] as $item) {
                $n++;
                $defaultitems[time()+$n]=$item;

          }
          $this->record->{$fieldname}=json_encode($defaultitems);
      }

    }

  }


  public function numElementsInPlace($place,$ctype=NULL)
  {
      $items=$this->getElementsForPlace($place);

      if($ctype)
      {
          foreach ($items as $el) {
              {
                  if($el->CType==$ctype)
                      $count++;
              }
          }
      }
      else
          $count=$items->count();

      return $count;
  }

  function __get($fieldname)
  {
    if(strstr($fieldname,'getAll_'))
    {
      if(preg_match('#^getAll_(.*)$#',$fieldname,$m))
      {
        return $this->getElementsForPlace($m[1]);
      }
    }

    if(strstr($fieldname,'getFirst_'))
    {
      if(preg_match('#^getFirst_(.*)$#',$fieldname,$m))
      {
        return $this->getElementsForPlace($m[1],1)->First();
      }
    }


     if(strstr($fieldname,'getAllRecursive_'))
      {
        if(preg_match('#^getAllRecursive_(.*)$#',$fieldname,$m))
        {
          return $this->getRecursiveElementsForPlace($m[1],NULL);
        }
      }

      if(strstr($fieldname,'getFirstRecursive_'))
      {
        if(preg_match('#^getFirstRecursive_(.*)$#',$fieldname,$m))
        {
          return $this->getRecursiveElementsForPlace($m[1],1)->First();
        }
      }

  }

  public function getRecursiveElementsForPlace($placename,$customMax=NULL,$p=Array())
  {
    if(!isset($this->cache[__FUNCTION__][$placename]))
    {
      // echo "<li>getRecursiveElementsForPlace called for $placename on {$this->record->Link()}";
      $elements=$this->getElementsForPlace($placename,$customMax,$p);
      if(!$elements->count())
      {
        $parent=$this->record->getParent();
        if($parent)
        {
            if($parent->C4P)
                $elements=$parent->C4P->getRecursiveElementsForPlace($placename,$customMax,$p);
        }
      }
       $this->cache[__FUNCTION__][$placename]= $elements;;
    }

    return $this->cache[__FUNCTION__][$placename];

  }


  public function addElementToPlace($celementdata,$placename)
  {
      return CElement::addCElementToObject($celementdata,$this->record,$this->getFieldnameForPlace($placename));
  }

  public function getElementByPlaceAndId($placename,$id)
  {
    return CElement::getCElement($this->record,$this->getFieldnameForPlace($placename),$id);
  }

  public function getElementsForPlace($placename,$customMax=NULL,$p=Array())
  {
    $ret= CElement::getCElementsForField($this->record,$this->getFieldnameForPlace($placename),$p);

    $cfg=$this->getPlaceConfig($placename);


    if(isset($customMax))
      $cfg['maxVisible']=$customMax;

    if($cfg['maxVisible'])
    {
      $ret=$ret->limit($cfg['maxVisible']);
    }

    return $ret;
  }



  public function getGroupedElementsForPlace($placename)
  {
      $elements= CElement::getCElementsForField($this->record,$this->getFieldnameForPlace($placename));

      $cfg=$this->getPlaceConfig($placename);
      //loop thru all items and group them of they want to

      if(is_object($elements) )
      {
          $ret=new ArrayList();
          foreach ($elements as $e) {

              $groupclass = $e->ClassNameForGrouping;

              if($groupElement && get_class($groupElement)!=$groupclass ) // still having a group element?
              {
                  //push it before the current element
                  $ret->push($groupElement);
                  unset($groupElement);
              }

              if($groupclass)                   // element wants to be grouped:
              {
                  if(!$groupElement)
                    $groupElement=new $groupclass;
                  $groupElement->addElement($e);
              }
              else
              {
                 $ret->push($e); // all is full of love (or normal)
              }
          }

          if($groupElement) // group element left over ?
          {
              $ret->push($groupElement);
              unset($groupElement);
          }


      }

      return $ret;
  }

  public function getFieldnameForPlace($place)
  {
    if(strstr($place,'/')) {
      $parts=explode('/',trim($place,'/'));
      $place=array_shift($parts);
      $cfg=$this->getPlaceConfig($place);
      array_unshift($parts,$cfg['fieldname']);
      return implode('/',$parts);
    }
    $cfg=$this->getPlaceConfig($place);


    return $cfg['fieldname'];
  }

  static  function includeAngularRequirements()
  {
      // MwBackendPageController::includePartialBootstrap(Array('scripts'=>'dropdown'));

      // Requirements::javascript('Mwerkzeug/javascript/CElement_jquery_plugin.js');
      // Requirements::javascript('Mwerkzeug/javascript/jquery.json-2.2.min.js');

      // Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.datepicker.js');
      // if(i18n::get_locale()=="de_DE")
      //     Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/i18n/jquery.ui.datepicker-de.js');


      // Requirements::javascript("Mwerkzeug/javascript/MwButtonDropdown_jquery_plugin.js");
      // Requirements::css('Mwerkzeug/css/CElement.css');
      // Requirements::css('mysite/css/CElement.css');
      $lang=MwUtils::getCurrentLanguageFromLocale();
      Requirements::javascript("Mwerkzeug/bower_components/angular/angular.min.js");
      Requirements::javascript("Mwerkzeug/ng/c4p/js/c4p.js");
      Requirements::javascript("Mwerkzeug/bower_components/messageformat/messageformat.js");
      Requirements::javascript("Mwerkzeug/bower_components/messageformat/locale/{$lang}.js");
      Requirements::javascript("Mwerkzeug/bower_components/angular-ui-tree/dist/angular-ui-tree.js");
      Requirements::javascript("Mwerkzeug/bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js");
      Requirements::javascript("Mwerkzeug/bower_components/angular-translate/angular-translate.min.js");
      Requirements::javascript("Mwerkzeug/bower_components/angular-translate-interpolation-messageformat/angular-translate-interpolation-messageformat.min.js");
      Requirements::javascript("Mwerkzeug/bower_components/bootstrap/js/tab.js");
      Requirements::css("Mwerkzeug/css/c4p/c4p.css");
      Requirements::css("Mwerkzeug/bower_components/angular-ui-tree/dist/angular-ui-tree.min.css");
      Requirements::css("Mwerkzeug/bower_components/font-awesome/css/font-awesome.min.css");
      BackendHelpers::includeTinyMCE();
      MwFileField::includeRequirements();
      MwLink::includeRequirementsForMwLinkField();

  }




  static function getCustomTabHTMLForBpPage($record,$step)
  {


      if(Controller::curr()->hasMethod("step_{$step}_plainFields"))
      {
        call_user_func(Array(Controller::curr(),"step_{$step}_plainFields"));
          $html="<div class='formsection'>
            <table class='ftable'>
            \$AllFormFields.RAW
            </table>
            </div>
            <div  class='space'>
            <button class='button save submit btn btn-primary' type='submit' ><span class='ui-icon-check tinyicon'></span>Save</button>
          </div>
            ";
        $tpl=SSViewer::fromString($html);
        $basefieldHTML=Controller::curr()->renderWith($tpl);
      }



     $place=preg_replace('#^[0-9_]+C4P_Place_#','',$step);

     $mwlink=$record->MwLink;
     if(!$mwlink)
      echo "<li>Error, no MwLink can be created for ".$record->ClassName;


     if(Controller::curr()->hasMethod("step_{$step}_plainFieldsWrapper")){
         $basefieldHTML=call_user_func(Array(Controller::curr(),"step_{$step}_plainFieldsWrapper"),$basefieldHTML);
     }


    $placeconf=$record->C4P->getPlaceConfig($place);
      $allowedCTypes=Array();
         if( $placeconf && $placeconf['allowed_types'] )
         {
           foreach ($placeconf['allowed_types'] as $key => $value) {
             $allowedCTypes[$key]=$value['label'];
           }
         }
         $placeconfEncoded=json_encode($placeconf);

  

         C4P::includeAngularRequirements();

         $lang=MwUtils::getCurrentLanguageFromLocale();

         return <<<HTML
           $basefieldHTML
           <!-- C4P Block BEGIN -->
         </form><!-- close form, we have a new one in our stuff -->
         <style>
         .actions {display:none;} /* hide savelink */
         </style>
         <div id="c4p" class="c4p" ng-controller="c4pMainCtrl" >


          <c4p-list
            place='$place'
            record='$mwlink'
            language='$lang'
            placeconf-encoded='$placeconfEncoded'
            app='app'
          >
             ...
          </c4p-list>
         </div>

        <script>
             angular.bootstrap(document.getElementById('c4p'), ['c4p']);
        </script>



         <!-- C4P Block END -->
HTML;

    


  }

    static public function callC4P($Controller)
  {

    //dispatch router calls to this controller


    $path=str_replace('-','/',Controller::curr()->urlParams['ID']);
    $action=Controller::curr()->urlParams['OtherID'];

    $record=$Controller->dataRecord;
    if ($record) {
        $c4pElement=CElement::getCElement($record,$path);

        $methodName="call".ucfirst($action);
        if ($c4pElement->hasMethod($methodName)) {
            return $c4pElement->$methodName($Controller);
        }
    }

  }



}


class C4P_Element extends CElement {

  var $formFields;
  var $isC4PElement=true;

    private static $casting = [
        'AllFormFields' => 'HTMLText',
        'getHTML' => 'HTMLText',
        'getHtml' => 'HTMLText',
    ];

  public function init()
  {
    $this->CType=get_class($this);
  }

  public function hasTwoCols()
  {
    if($this->formFields['left'] || $this->formFields['right'])
    {
      return 1;
    }
  }


  public function getConfig()
  {
   return Array();
  }

  public function getPermissions()
  {
      $ret=Array();
      foreach (Array('delete','edit','hide','unhide','copy','duplicate','copyAsAlias','editChildren','typeChange') as $actionName) {
       $ret[$actionName]=Permissions::canDoActionOnC4P($actionName,$this);
      }

      $ret['actionmenu']=($ret['copy'] || $ret['duplicate'] || $ret['copyAsAlias'] || $ret['hide'] || $ret['unhide']);

      return $ret;
  }

  public function hasTabs()
  {
    if($this->formFields['tabs'])
    {
      return 1;
    }
  }


  public function Tabs()
  {
      if(!isset($this->cache[__FUNCTION__]))
      {
          $al=new ArrayList();
          if($this->hasTabs())
          {

              foreach($this->formFields['tabs'] as $key=>$tab)
              {
                  $tabdata=Array();
                  $tabdata['Key']=$key;
                  $tabdata['TabTitle']=$tab['title'];
                  if(!$tabdata['TabTitle'])
                      $tabdata['TabTitle']=FormField::name_to_label(ucfirst($key));


                  $hasTwoCols=false;
                  if ($tab['items']['left'] || $tab['items']['right']) {
                    $hasTwoCols=true;
                  }
                  $tabdata['hasTwoCols']= $hasTwoCols;
                  if ($hasTwoCols) {
                    $tabdata['AllFormFieldsLeft']= $this->AllFormFields('left',$tab['items']);
                    $tabdata['AllFormFieldsRight']= $this->AllFormFields('right',$tab['items']);
                  } else {
                    $tabdata['AllFormFields']= $this->AllFormFields('',$tab['items']);
                  }

                  $al->push(new ArrayData( $tabdata ) );
              }

          }

          $this->cache[__FUNCTION__]=$al;
      }
      return $this->cache[__FUNCTION__];


  }



  public function Linkify($fieldname)
  {
      return MwLink::resolveLinks($this->$fieldname);
  }


  public function getTarget()
  {
      if(!isset($this->cache[__FUNCTION__]))
      {
        if($this->TargetLinkID)
          $obj=MwLink::getObjectForMwLink($this->TargetLinkID);

         $this->cache[__FUNCTION__]=$obj;
      }
      return $this->cache[__FUNCTION__];
  }

  public function getLink()
  {
    if(!isset($this->cache[__FUNCTION__]))
    {
      if($this->Target)
       $this->cache[__FUNCTION__]=$this->Target->Link();
      else
       $this->cache[__FUNCTION__]='';
    }
    return $this->cache[__FUNCTION__];

  }



  public function getLinkOpen()
  {
    if($this->TargetLinkID)
     {
       $url=MwLink::getURLForMwLink($this->TargetLinkID);
       $target=MwLink::getTargetAttributeForMwLink($this->TargetLinkID);
       return "<a href=\"$url\" $target>";
     }
    else
      return "";
  }


  public function getLinkClose()
  {
      if($this->TargetLinkID)
          return "</a>";
          else
              return "";
  }

  public function getCTypeShort()
   {
     $long=$this->CType;
     return preg_replace('#^.*C4P_#','',$long);
   }

  public function getCTypeLabel()
  {
    $label=$this->CType;
    $s=Controller::curr()->Settings;
    if($s)
      {
        $place=$s['c4p_place'];
        $cfg=$this->Mainrecord->C4P->getPlaceConfig($place);
        $label=$cfg['allowed_types'][$this->CType]['label'];
      }

    return $label;
  }

  public function AllFormFields($key='',$items=NULL)
  {

    if(!$items) {
      $items=$this->formFields;
    }
    if($key)
      $fields=$items[$key];
    else
      $fields=$items;


    $ret='';
    if(is_array($fields)) {
      foreach ($fields as $fielddata) {
        $ret.=MwForm::render_field($fielddata);
      }
    }

    return $ret;
  }



    public function getJSValidationMessages()
    {
          return MwForm::getValidationMessages();
    }

    public function getJSValidationRules()
    {
          return MwForm::getValidationRules();
    }


  public function FormField($key='',$name,$items=NULL)
  {

    if($key)
      $fielddata=$this->formFields[$key][$name];
    else
      $fielddata=$this->formFields[$name];

    if($fielddata)
      return MwForm::render_field($fielddata);
    else
      return "";
  }

  public function setFormFields()
  {
    //to override
  }
    public function beforeSetFormFields()
  {
    //to override
  }
    public function afterSetFormFields()
  {
    //to override
  }

  public function getDefaultRecord()
  {
      return Array();
      //to override

  }


  public function recordAsJson()
  {
    return json_encode($this->record);
  }

  public function hasFormFields()
  {

      $this->beforeSetFormFields();
      $this->setFormFields();
      $this->afterSetFormFields();
      
      return ($this->formFields && sizeof($this->formFields)>0);

  }

  public function ng_edit($args=Array())
  {

      if($this->record)
          $preset=$this->record;
      else
          $preset=$this->getDefaultRecord();

      $this->checkChildren();

      MwForm::preset($preset);
      MwForm::set_default_rendertype('bootstrap3');

      $this->beforeSetFormFields();
      $this->setFormFields();
      $this->afterSetFormFields();


      $c['id']=$this->ID;
      $c['c4p_record']=$this->Mainrecord->getMwLink();
      $c['c4p_place']=$this->getPlace();
      if($args) {
        $c['params']=new ArrayData($args);
      }

      $tplnames=Array();
      $tplnames[]="Includes/{$this->CType}_ng_edit";
      $tplnames[]="Includes/C4P_Element_ng_edit";

      return $this->customise($c)->renderWith($tplnames);
  }

  public function edit()
  {

      if($this->record)
          $preset=$this->record;
      else
          $preset=$this->getDefaultRecord();

      $this->checkChildren();

      MwForm::preset($preset);
      MwForm::set_default_rendertype('css');

      $this->beforeSetFormFields();
      $this->setFormFields();
      $this->afterSetFormFields();


      $this->editMode=1;
      $c=Array('CssID'=>Controller::curr()->urlParams['ID']);

      $tplnames=Array();
      $tplnames[]="{$this->CType}_ajaxItem";
      $tplnames[]="C4P_Element_ajaxItem";

      return $this->customise($c)->renderWith($tplnames);
  }

  public function show()
  {
        $this->handleShowActions();
        $c=Array();
        $tplnames=Array();
        $tplnames[]="{$this->CType}_ajaxItem";
        $tplnames[]="C4P_Element_ajaxItem";

      return $this->customise($c)->renderWith($tplnames);
  }


  public function getBEPreviewHTML($style='default')
  {
    $this->checkChildren();
    $html="";
    $tplHtml=$this->PreviewTpl($style);
    if($tplHtml)
    {
      $tpl=SSViewer::fromString($tplHtml);
      if($this->AliasTo) {
        $html=$this->getAliasPreviewHtml();
      }
      if (!$this->AliasError) {
        $html.=$this->renderWith($tpl);
      } 
    } else {
      foreach ($this->record as $key => $value) {
        $html.="<li><b>$key</b>: $value</li>";
      }
    }
    return $html;
  }

  public function getAliasPreviewHtml()
  {
    if ($this->AliasError) {
      $errorHtml="<div class='alert alert-danger'><i class='fa fa-warning pull-left fa-2x'></i> {$this->AliasError['Msg']}</div>";
      if ($this->AliasError['Msg']) {
        return $errorHtml;
      }
    }
    $linkstr=$this->getToprecord()->Title;
    $editlink=$this->getToprecord()->EditLink();
    return "<div class='c4p-aliasinfo c4p-nice2have'>
       $errorHtml
       <i class='fa fa-share fa-border pull-left fa-2x'></i>
      ".MwLang::get('backend.texts.c4p_is_alias')."
      <a href='$editlink' class='btn btn-xs btn-default'><i class='fa fa-pencil'></i> <strong>$linkstr</strong></a>
      </div>";

  }

  public function getHTML($style='default')
  {

    $html=$this->getTpl($style);

    if($html)
    {
      if (strstr($html,'tpl:') && preg_match('/^tpl:(.*)$/',$html,$m)) {
            $tpl=$m[1]; /// treat tpl:  as template filename
          } else {
          $tpl=SSViewer::fromString($html); // treat as html
        }

        $x= $this->renderWith($tpl);
        return $x;
      }
    }

  public function getTpl($style)
  {

      //ovverride me
  }




  public function PreviewTpl()
  {

    //override me

  }

  public function checkChildren()
  {
      //override me
  }

  public function isChild()
  {
      if(preg_match('#^_children(_[a-z0-9]+)?$#',$this->Fieldname))
         return true;

      if(preg_match( '#_children(_[a-z0-9]+)?$$#',$this->ParentPath))
         return true;

       return false;
  }


  public function getChildID()
  {
      if($this->isChild())
      {
          return $this->Mainrecord->getChildID().'/'.$this->ID;
      }
      else
         return $this->ID;
  }


  public function getToprecord()
  {
      $rec=$this->Mainrecord;
      if(is_subclass_of($rec, 'CElement')) {
          return $rec->getToprecord();
      }
      return $rec;
  }

  public function getToprecordOfAlias()
  {
    if($this->AliasTo) {
      $rec=$this->MainrecordBeforeAliasing;
      if(is_subclass_of($rec, 'CElement')) {
        return $rec->getToprecord();
      }
      return $rec;
    }
    return $this->getToprecord();
  }


  public function getTopCElement()
  {
      if(is_subclass_of($this->Mainrecord, 'CElement')) {
          return $this->Mainrecord->getTopCElement();
      }
      return $this;
  }


  public function ChildEditControls()
  {
      $html=<<<HTML

<div class='c4p-childeditcontrols pull-right'>
<a class="btn btn-mini c4p-child-editbutton" href="#{$this->ChildID}"><i class="icon-white icon-pencil "></i> edit</a>
</div>
HTML;

    return $html;
  }


  public function getPlace()
  {

       if ($settingsFromRequest=Controller::curr()->Settings) {
        if (isset($settingsFromRequest['c4p_place'])) {
            return $settingsFromRequest['c4p_place'];
        }
      }


      if(preg_match('#^C4Pjson_(.+)$#',$this->Fieldname,$m))
      {
          return $m[1];
      }

  }

    public function createCallingUrl($action)
    {

        $path=$this->getC4PPath();
        if (strstr($path,'-')) {
            throw new Exception("path '$path' must not contain '-' ", 1);
        }
        $myId=str_replace('/','-',$path);

        return $this->Toprecord->SelfOrAlias->Link().'callC4P/'.$myId.'/'.lcfirst($action);
    }

    public function getC4PPath()
    {
      $path=$this->Fieldname;

      if($this->ParentPath)  {
        $path.='/'.$this->ParentPath;
      }

      $path.='/'.$this->ID;
      return $path;

    }

}

// used for grouping in backend-level

class C4P_Container extends C4P_Element
{

    private static $casting = [
        'AllFormFields' => 'HTMLText',
        'getHTML' => 'HTMLText',
        'getHtml' => 'HTMLText',
    ];


    public function checkChildren()
    {
        //echo "<li> i am a ".get_class($this)." and i'm checking my children now";
        $children=$this->getChildrenFromRecord();
        if(!$children )
        {
                $this->createDefaultChildren();
        }

    }


    public function addChild($data,$groupname=NULL,$params=array())
    {
       if($groupname) {
          $childHolderFieldName="_children_$groupname";
        } else {
          $childHolderFieldName="_children";
        }

        $objname=$data['CType'];
        if($objname && class_exists($objname)) {
            $celement_id = time().rand(1,1000);
            if($params['position']=='prepend' && is_array($this->record[$childHolderFieldName])) {
              $this->record[$childHolderFieldName]=array($celement_id=>$data) + $this->record[$childHolderFieldName];
            } elseif(is_numeric($params['position'])) {
              $pos=$params['position'];
              $result=array();
              $n=0;
              $dataWasSet=false;
              foreach ($this->record[$childHolderFieldName] as $key=>$value) {
                $n++;
                if($n==$pos) {
                  $result[$celement_id]=$data;
                  $dataWasSet=1;
                }
                $result[$key]=$value;
              }
              if(!$dataWasSet) {
                $result[$celement_id]=$data;
              }
              $this->record[$childHolderFieldName]=$result;

            } else {
              $this->record[$childHolderFieldName][$celement_id]=$data;
            }
        }

    }

    public function replaceChild($celement_id, $data,$groupname=NULL,$params=array())
    {
       if($groupname) {
          $childHolderFieldName="_children_$groupname";
        } else {
          $childHolderFieldName="_children";
        }

        $objname=$data['CType'];
        if($objname && class_exists($objname)) {
            if (array_key_exists($celement_id, $this->record[$childHolderFieldName])) {
              $this->record[$childHolderFieldName][$celement_id]=$data;
            }
        }
    }

    public function removeChild($celement_id,$groupname=NULL,$params=array())
    {
       if($groupname) {
          $childHolderFieldName="_children_$groupname";
        } else {
          $childHolderFieldName="_children";
        }

        if (is_array($this->record[$childHolderFieldName]) && array_key_exists($celement_id, $this->record[$childHolderFieldName])) {
          unset($this->record[$childHolderFieldName][$celement_id]);
        }
    }

    public function removeAllChildren($groupname=NULL)
    {
        if($groupname) {
          $childHolderFieldName="_children_$groupname";
        } else {
          $childHolderFieldName="_children";
        }

         $this->record[$childHolderFieldName]=Array();
    }


    public function getChildColumnCount()
    {
        return 3;
    }



    public function getChildren($groupname=NULL,$p=Array())
    {
      if($groupname=='all') {
            $al=new ArrayList();
            foreach ($this->getChildGroupNames() as $groupname1) {
              $al->merge($this->getChildren($groupname1));
            }
            return $al;
      }

      if($groupname) {
        $childHolderFieldName="_children_$groupname";
      } else {
        $childHolderFieldName="_children";
      }


      if(!isset($this->cache[__FUNCTION__.$childHolderFieldName]))
      {
        $pos=0;
        $childs=$this->getChildrenFromRecord($groupname);

        $dos=new ArrayList();
        foreach ($childs as $itemid => $data) {
          $MainrecordForElement=$this;
          if($data['AliasTo']) {
            if ($GLOBALS['already_used_aliaslinks'][$groupname.$itemid.$data['AliasTo']]) {
              continue;
            }
            $GLOBALS['already_used_aliaslinks'][$groupname.$itemid.$data['AliasTo']]++;

            $MainrecordForElement=self::handleAliasTo($data,$this);
          }

          /* alias end */

          if($data['CType'])
          {
            $objname=$data['CType'];

            if(class_exists($objname))
            {
              if($MainrecordForElement->isC4PElement) {
                $localChildHolderFieldName=$MainrecordForElement->getC4PPath().'/'.$childHolderFieldName;
              } else {
                $localChildHolderFieldName=$childHolderFieldName;
              }
              $obj=new $objname($MainrecordForElement,$localChildHolderFieldName,$itemid,$data);
              // $obj->checkTimers();
              $objHidden=$obj->Hidden;
              if( $data['AliasError']) {
                $objHidden=1;
              }
              if($p['include_hidden'] || !$objHidden ) {
                $pos++;
                $obj->setPosition($pos);
                $dos->push($obj);
              }

            }


          }
        }
        $this->cache[__FUNCTION__.$childHolderFieldName]=$dos;
      }
      return $this->cache[__FUNCTION__.$childHolderFieldName];

    }

    public function getChildrenFromRecord($groupname=NULL)
    {
           if($groupname) {
              $childHolderFieldName="_children_$groupname";
            } else {
              $childHolderFieldName="_children";
            }
            $children=$this->record[$childHolderFieldName];
            if(!$children)
                $children=Array();
            return $children;
    }


    function getChildGroupNames()
    {
      $conf=$this->getConfig();

      if(is_string($conf['childgroups']) && $conf['childgroups']) {
        return explode(',',$conf['childgroups']);
      }
      if(is_array($conf['childgroups'])) {
        return $conf['childgroups'];
      }

      return Array('');
    }

    public function getAllChildrenFromRecord()
    {
      $res=Array();
      foreach ($this->getChildGroupNames() as $groupname) {
        $res+=$this->getChildrenFromRecord($groupname);
      }
      return $res;

    }




    public function getChildByID($id,$groupname=NULL)
    {
       if($groupname) {
          $childHolderFieldName="_children_$groupname";
        } else {
          $childHolderFieldName="_children";
        }

        $ids=explode('/',"$id");

        if($ids[0]==$this->ID)
        {
            array_shift($ids);
        }

        if($ids)
        {
            $childid=array_shift($ids);
            $children=$this->getChildrenFromRecord($groupname);
            $childrec=$children[$childid];
            if($childrec)
            {

                // make child from rec ---------- BEGIN
                if($childrec['CType'])
                {
                    $classname=$childrec['CType'];

                    if(class_exists($classname))
                    {
                        $child=new $classname($this,$childHolderFieldName,$childid,$childrec);
                    }
                }
                // make child from rec ---------- END
                if($ids)
                {
                    $sub_childids=implode('/',$ids);
                    return $child->getChildByID($sub_childids);
                }
                else
                {
                    return $child;
                }
            }

        }

    }

    public function createDefaultChildren($value='')
    {
        //override me
    }





    public function getChildrenPreviewHTML()
    {
        $this->checkChildren();
        $html=$this->ChildrenPreviewTpl();
        if($html)
        {
          $tpl=SSViewer::fromString($html);
          return $this->renderWith($tpl);
        }
    }

    public function getChildrenEditHTML()
    {
        $this->checkChildren();
        $html=$this->ChildrenEditTpl();
        if($html)
        {
          $tpl=SSViewer::fromString($html);
          return $this->renderWith($tpl);
        }
    }


    public function PreviewTpl()
    {
        return '$ChildrenPreviewHTML';

    }

    public function ChildrenPreviewTpl()
     {
       return $this->ChildrenEditTpl();

     }

     public function ChildrenEditTpl()
      {
        return <<<HTML
        <ul class='c4p-childlist c4p-childlist-{\$getChildColumnCount}col group \$CType'>
               <% loop Children %>
               <li class='c4p-child \$CType'>
                   \$ChildEditControls
                   \$BEPreviewHTML
               </li>
               <% end_loop %>
        </ul>
HTML;

      }






}


// used for on-the-fly-grouping of adjacent content-elements on output

class C4P_ElementGroup extends ViewableData
{
    var $Children;



  public function getHTML($style='default')
  {
      $html=$this->getTpl($style);

      if($html)
      {
        if (strstr($html,'tpl:') && preg_match('/^tpl:(.*)$/',$html,$m)) {
            $tpl=$m[1]; /// treat tpl:  as template filename
        } else {
          $tpl=SSViewer::fromString($html); // treat as html
        }

        return $this->renderWith($tpl);
      }
  }


    public function getCType()
    {
     return get_class($this);
    }

    public function getCTypeShort()
     {
       $long=$this->CType;
       return preg_replace('#^.*C4P_#','',$long);
     }



    public function addElement($e)
    {
        if(!$this->Children)
        {
            $this->Children=new ArrayList();
        }
        $this->Children->push($e);
    }


}
