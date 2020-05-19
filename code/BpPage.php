<?php

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Versioned\Versioned;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Controller;
use SilverStripe\Security\Permission;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\i18n\i18n;
use SilverStripe\View\Requirements;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Security\Member;
use SilverStripe\Control\Director;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\FormField;
use SilverStripe\View\ArrayData;
use SilverStripe\Control\Session;

class BpPageController extends BackendPageController
{
  
  var $record;
  var $MwForm;
  var $formFields;
  var $prevTabItem = NULL;
  var $nextTabItem= NULL;
  var $currentTabItem= NULL; 
  var $currentTabID;
  
   var $myClass=SiteTree::class;

   static $isActive;


    private static $allowed_actions= [
      'index','treeframe','edit','show','preview','versions','ajaxTreeData_v2','ng_pagemanager','MwLinkChooser','listing'
    ];

    private static $casting = [
        'AllFormFields' => 'HTMLText'
    ];
   public function getTitle()
   {
       return $this->record->Title;
   }

   
   public function UrlHash()
   {
       $url=$this->urlParams['Action'];
       if($id=$this->urlParams['ID'])
           $url.="/$id";
       if($oid=$this->urlParams['OtherID'])
           $url.="/$oid";
       if($oid2=$this->urlParams['OtherID2'])
           $url.="/$oid2";
       
       return $url;
       
   }
   
   public function Redirect2FrameUrl()
   {
       $url=$this->CurrentURL();
       $url=str_replace('/BE/Pages/','/BE/Pages/#',$url);
       return $url;
   }
   

   public function init()
   {
     parent::init();
     $this->ParentID=0;
     self::$isActive = 1;

     if(array_get($_REQUEST,'MwLink'))
     {
       $rec=MwLink::getObjectForMwLink(array_get($_REQUEST,'MwLink'));
       if($rec && $rec->ID)
       {
        Versioned::set_stage("Live");
        $this->record=DataObject::get_by_id(SiteTree::class,$rec->ID);
       }

     }
     if($id=intval(Controller::curr()->urlParams['ID']))
     {

       $this->loadRecord();
     }
   }

   public function conf($key)
   {
     return MwPage::conf($key);
   }

   
  public function getCurrentPageID()
  {
    if(!isset($this->cache[__FUNCTION__]))
    {
     
      if(array_get($_REQUEST,'context'))
        {
          if(preg_match('#/(\d+)/?$#', array_get($_REQUEST,'context') ,$m))
            $id=intval($m[1]);
        }
      elseif(Controller::curr()->urlParams['ID'])
        $id=Controller::curr()->urlParams['ID'];


       $this->cache[__FUNCTION__]=$id;
    }
    return $this->cache[__FUNCTION__];

  }


    public function isAllowed($action)
    {
      return Permissions::canDoAction($action,$this->record);
    }


   public function handleRequest(HTTPRequest $request, DataModel $model = null) 
   {
       $myClass=get_class($this);
     if(($myClass==='BpPageController' || $myClass=== 'BpMysitePageController') && $id=intval($request->latestParam('ID')))
     {
   		 Versioned::set_stage("Live");
       $this->record=DataObject::get_by_id(SiteTree::class,$id);
       $pageclass=$this->record->ClassName;

       if($pageclass!='Page')
       {
         
         //try new nomenclature:
         $pageBpControllerClass="{$pageclass}BEController";
         if(class_exists($pageBpControllerClass))
         {
           $custom_controller = new $pageBpControllerClass($this->record);
           //hand over controller
//           $custom_controller->setSession($request->getSession());
           return $custom_controller->handleRequest($request, $model);
         }
         
         $pageBpControllerClass="Bp{$pageclass}Controller";
         if(class_exists($pageBpControllerClass))
         {
           $custom_controller = new $pageBpControllerClass($this->record);
           //hand over controller
           $custom_controller->setSession($this->getSession());
           return $custom_controller->handleRequest($request, $model);
         }


       }
     }
     
     return parent::handleRequest($request, $model);
     
   }

   public function URLSegmentCheck()
   {
      $this->record=MwLink::getObjectForMwLink(array_get($_POST,'MwLink'));
      $currentURLSegment=array_get($_POST,'URLSegment');
      $Title=array_get($_POST,'Title');
      $html="<script>";

      if($this->record && $this->record->hasField('URLSegment')  && $Title)
      {
        if(!$currentURLSegment)
          $currentURLSegment=$this->record->URLSegment;

        $newURLSegment=$this->record->generateURLSegment($Title);
        
        if($currentURLSegment!=$newURLSegment && !MwPage::conf('skipAskForUrlnameChanges')) {
//          $html.= "<li>URLSegment changed: $currentURLSegment!=$newURLSegment";
          if(i18n::get_locale()=="de_DE")
            $question="soll die Seiten-URL auch geändert werden ? \n\n Klicken Sie 'OK' für: \"$newURLSegment\" \n \n oder 'Abbrechen' um : \"$currentURLSegment\" zu behalten.";
          else
            $question="should the URL-Name of the page be changed as well ? \n\n press OK to set URL to: \"$newURLSegment\" \n \n press Cancel to keep: \"$currentURLSegment\"";

          $question=json_encode($question);
          $html.="
            if(confirm($question))
            {
                if(jQuery('#input_URLSegment').length<1)
                {
                  jQuery('#input_Title').after(\"<input type='hidden' name='fdata[URLSegment]' id='input_URLSegment'>\");
                }
                jQuery('#input_URLSegment').val('$newURLSegment');
            }
            ";
        }
      }
      $html.="
      input_Title_stored=\$('#input_Title').val();
      \$('#dataform').submit();
      </script>";

      echo $html;
      exit();

   }

   public function listing()
   {
     $c=Array();
     
     $this->summitSetTemplateFile('Layout','BpPage_listing');
     return $c;
   }

   public function MwLinkChooser()
   {

    if ($this->SkinVersion>=2) {

      if (array_get($_GET,'MwLink')) {
       $this->record=MwLink::getObjectForMwLink(array_get($_GET,'MwLink'));
      }


       Requirements::clear();
       Requirements::css("Mwerkzeug/bower_components/font-awesome/css/font-awesome.min.css");

       Requirements::javascript('Mwerkzeug/bower_components/jquery/dist/jquery.min.js');
       Requirements::javascript("Mwerkzeug/bower_components/jstree/dist/jstree.min.js");
       Requirements::javascript("Mwerkzeug/bower_components/angular/angular.min.js");
       Requirements::javascript("Mwerkzeug/bower_components/perfect-scrollbar/js/min/perfect-scrollbar.jquery.min.js");
       Requirements::javascript("Mwerkzeug/ng/pagetree/js/pagetree.js");

       Requirements::CSS("Mwerkzeug/bower_components/perfect-scrollbar/css/perfect-scrollbar.min.css");
       Requirements::CSS("Mwerkzeug/css/skin2/pagetree.css");

       $pm=PageManager::singleton();

       $this->summitSetTemplateFile("main","BpPage_MwLinkChooser".$this->SkinVersionPostfix);
     return Array('time'=>time(),'settingsAsJson'=>$pm->getSettingsForPageTree('linkchooser',$this->record));

    } else {
      $this->summitSetTemplateFile("main","BackendPage_iframe");
    }

    return Array();
  }



   public function show()
   {
     if($this->record)
     {
       $url=$this->record->Link(); 
       Controller::curr()->redirect($url);
     }
   }

   public function preview()
   {
     if($this->record)
     {
       $url=$this->record->PreviewLink(); 
       Controller::curr()->redirect($url);
     }
   }



   public function versions()
   {
       
       $this->includeBootstrap();
       
       
       if($_POST)
       {
           if(is_numeric(array_get($_POST,'rollback2version')))
           {
               $vid=array_get($_POST,'rollback2version');
               $this->record->restoreVersion($vid);
               $c['RestoredVersion']=$vid;
           }
       }

       if($this->UseFrames)
       {
             
           $this->summitSetTemplateFile("main","BackendPage_iframe");

           if(!$this->record)
           {
               return "&nbsp;";
           }

       }
       
       $versions=new ArrayList();
       
       $vs=$this->record->allVersions();
       
       if($vs)
       {
           $lastversion=NULL;
           foreach ($vs->reverse() as $v) {
               $versions->push( new MwPageVersion($v,$lastversion) );
               $lastversion=$v;
           }
           $vs->reverse();

           $c['versions']=$versions->reverse();
           
       }
       
       
       return $c;
   }



   public function index(SilverStripe\Control\HTTPRequest $request)
   {

       if($this->UseFrames)
       {
           if ($this->SkinVersion==2) {
              Requirements::clear();
              Requirements::CSS("Mwerkzeug/css/skin2/pageframe.css");
              Requirements::CSS("Mwerkzeug/bower_components/font-awesome/css/font-awesome.min.css");
              Requirements::insertHeadTags( "<link rel=\"shortcut icon\" href=\"/Mwerkzeug/images/favicon_be".($this->isLocal()?'_local':'').".ico\" />", $html );
              Requirements::javascript("Mwerkzeug/bower_components/jquery/dist/jquery.min.js");

              Requirements::insertHeadTags('<script src="/Mwerkzeug/bower_components/requirejs/require.min.js" data-main="/Mwerkzeug/ng/pageframe/js/main.js"></script>' );

              $this->MwSetTemplateFile("main","BpPage_framed".$this->SkinVersionPostfix);
              return Array();
           }
           $this->MwSetTemplateFile("Layout","BpPage_framed");
           return Array();
       }
       
       return Controller::curr()->redirect('/BE/Pages/edit');

   }


   public function CustomTreeGroupHeader($settingsAsJson)
   {
    if (Member::currentUser()->hasMethod('customTreeGroupHeader')) {
      return Member::currentUser()->customTreeGroupHeader(json_decode($settingsAsJson,true));
    }
  }

  public function CustomTreeGroupFooter($settingsAsJson)
  {
    if (Member::currentUser()->hasMethod('customTreeGroupFooter')) {
      return Member::currentUser()->customTreeGroupFooter(json_decode($settingsAsJson,true));
    }
  }


   public function treeframe()
   {

      if ($this->SkinVersion==2) {

          if (Member::currentUser()->hasMethod('customTreeframeUrl')) {
            
            $url=Member::currentUser()->customTreeframeUrl();
            if ($url) {
              return $this->redirect($url);
            }
          } 
          Requirements::clear();
          Requirements::css("Mwerkzeug/bower_components/font-awesome/css/font-awesome.min.css");

          Requirements::javascript('Mwerkzeug/bower_components/jquery/dist/jquery.min.js');
          Requirements::javascript("Mwerkzeug/bower_components/jstree/dist/jstree.min.js");
          Requirements::javascript("Mwerkzeug/bower_components/angular/angular.min.js");
          Requirements::javascript("Mwerkzeug/bower_components/perfect-scrollbar/js/min/perfect-scrollbar.jquery.min.js");
          Requirements::CSS("Mwerkzeug/bower_components/perfect-scrollbar/css/perfect-scrollbar.min.css");
          Requirements::CSS("Mwerkzeug/css/skin2/pagetree.css");
          Requirements::javascript("Mwerkzeug/ng/pagetree/js/pagetree.js");

          $this->mwSetTemplateFile("main","BpPage_treeframe".$this->SkinVersionPostfix);
//          $this->mwSetTemplateFile("Layout","BpPage_treeframe".$this->SkinVersionPostfix);

          
          $pm=PageManager::singleton();

          return Array('time'=>time(),'settingsAsJson'=>$pm->getSettingsForPageTree('cms',$this->record));
        

      }

       if($this->UseFrames)
       {
         Requirements::insertHeadTags('<base target="rightframe">');
       }
       $this->mwSetTemplateFile("main","BackendPage_iframe");
       $this->mwSetTemplateFile("Layout","BpPage_Pagetree".$this->SkinVersionPostfix);
  
       return Array();
   }
   


   public function getUseFrames()
   {
      return MwPage::conf('UseFramesInBE');
   }

  
   


   public function edit()
   {

     if($this->UseFrames)
     {
        
       if ($this->SkinVersion>=2) {
         $this->summitSetTemplateFile("main","BackendPage_rightframe");

       } else {
         $this->summitSetTemplateFile("main","BackendPage_iframe");
       }

         if(!$this->record)
         {
             return "&nbsp;";
         }

     }

     if($_POST)
     {
       $this->handleIncomingValues();
     }

     $this->MwForm=new MwForm;
     $this->MwForm->presetObject($this->record);
     if ($this->SkinVersion>=2) {
       $this->MwForm->set_default_rendertype('bootstrap3');
     }

     $this->summitSetTemplateFile("Layout","BpPage_edit".$this->SkinVersionPostfix);

     //prepare form-fields:
     $step=$this->CurrentTab();
     $stepfunc="step_{$step}";
     if($this->hasMethod($stepfunc))
     {
       $this->CustomTabHTML=call_user_func(array($this,$stepfunc));
     }     
     elseif(strstr($stepfunc,'C4P'))
     {
         $this->CustomTabHTML = C4P::getCustomTabHTMLForBpPage($this->record,$step);         
     }
     elseif(strstr($stepfunc,'EVT'))
     {
         $this->CustomTabHTML =EVT::getCustomTabHTMLForBpPage($this->record,$step);         
     }

     if(stristr($this->CustomTabHTML,'<body'))
     {
         return $this->CustomTabHTML;
     }
     $c['ClassName']=$this->record->ClassName;
     $c['ReturnURL']=array_get($_POST,'ReturnURL')?array_get($_POST,'ReturnURL'):array_get($_SERVER,'HTTP_REFERER');
     return $c;

   }

   public function handleIncomingValues($incoming=NULL)
   {
     if(!is_array($incoming))
     {
       $incoming=array_get($_POST,'fdata'); 
     }

      if(array_key_exists('main_savelink', $_POST)) {
        $incoming['dummy_timestamp']=time();
      }

     if($incoming)
     {
       
       foreach ($incoming as $key => $value) {

           if(strstr($key,'JSON') && trim($value))
           {
             if(MwUtils::jsonIsValid($value))
             {
                 $incoming[$key]=MwUtils::tidyJSON($value); //clean up json
             }
             else
             {
                 echo "<script>alert('warning: your json code is invalid !!')</script>";
             }
           }

         if(strstr($key,'2serialize'))
         {
           $incoming[str_replace('2serialize','',$key)]=serialize($value);
           unset($incoming[$key]);
           continue;
         }

         if(is_Array($incoming[$key]))
         {
           foreach ($incoming[$key] as $key2=>$val2)
             if(!$incoming[$key][$key2] || $incoming[$key][$key2]=="-1" )
             unset($incoming[$key][$key2]);  //remove empties
           $incoming[$key]=implode(',',$incoming[$key]);
         }

       }
       
       if($incoming['ClassName'] && $incoming['ClassName'] != $this->record->ClassName)
       {
         $this->record = $this->record->newClassInstance($incoming['ClassName']);
         $classNameChanged=1;
       }

       
       $this->record->update($incoming);
       $this->record->write();

       //inform jstree about update
       
       $this->notifyJsTree();
       

       if($classNameChanged) {
         $this->redirect('/BE/Pages/edit/'.$this->record->ID);
       }
     }

   }
   
   public function notifyJsTree()
   {
       if ($this->SkinVersion>=2) {



       } else {

         $js="
         if(top.frames['leftframe'])
         {
             var newattr={};
             newattr.className='';
             if({$this->record->Hidden}+0==1)
                 newattr.className+='hidden ';

             if({$this->record->ShowInMenus}+0==0)
                 newattr.className+=' notinmenu';
             
             newattr.title='{$this->record->URLSegment}/ - {$this->record->ClassName}';
              if (window.console && console.log) { console.log(newattr);  }
             top.frames['leftframe'].updateNode('{$this->record->ID}','{$this->record->MenuTitle}',newattr);
         }
         ";
         Requirements::customScript($js);
       }
   }


   function BaseUrlForSteps()
   {
       return '/BE/Pages/edit/'.$this->urlParam('ID');
   }

   public function step_10()
   {

     BackendHelpers::includeTinyMCE();  //all textareas with class tinymce will be richtext-editors

     //define all FormFields for step "Title"
     $p=Array(); // ------- new field --------
     $p['label']="Title";
     $p['fieldname']="Title";
     $this->formFields[$p['fieldname']]=$p;


     $p=Array(); // ------- new field --------
     $p['label']="Text";
     $p['type']='textarea';
     $p['fieldname']="Content";
     $p['styles']="height:450px;width:520px";
     $p['addon_classes']="tinymce";
     $this->formFields[$p['fieldname']]=$p;


   }


   public function getAllowedPageClasses()
   {
     $pm=PageManager::singleton();

     return $pm->getAllowedPageClassesForParent($this->Parent());

    

   }

 
  public function step_20()
  {

    $pm=PageManager::singleton();

    //define all FormFields for step "Title"
    $p=Array(); // ------- new field --------
    $p['label']=MwLang::get('backend.labels.page_ClassName');
    $p['fieldname']="ClassName";
    $p['type']="select";
    $p['options']=$this->getAllowedPageClasses();
    $p['no_empty_option']=1;
    $this->formFields[$p['fieldname']]=$p;


    // if($this->record && $this->record->hasField('Archived'))
    // {
    //     //define all FormFields for step "Title"
    //     $p=Array(); // ------- new field --------
    //     $p['label']="this Page is Archived";
    //     $p['fieldname']="Archived";
    //     $p['type']="checkbox";
    //     $this->formFields[$p['fieldname']]=$p;
    // }
    


    //define all FormFields for step "Title"
    $p=Array(); // ------- new field --------
    $p['label']=MwLang::get('backend.labels.page_Title');
    $p['fieldname']="Title";
    $this->formFields[$p['fieldname']]=$p;



    //define all FormFields for step "Title"
    $p=Array(); // ------- new field --------
    $p['label']=MwLang::get('backend.labels.page_MenuTitle');
    $p['fieldname']="RealMenuTitle";
    $this->formFields[$p['fieldname']]=$p;


    //define all FormFields for step "Title"
    $p=Array(); // ------- new field --------
    $p['label']=MwLang::get('backend.labels.page_URLSegment');
    $p['fieldname']="URLSegment";
    $this->formFields[$p['fieldname']]=$p;

    //define all FormFields for step "Title"
    $p=Array(); // ------- new field --------
    $p['label']= MwLang::get('backend.labels.page_Hidden');
    $p['fieldname']="Hidden";
    $p['type']="radio";
    $p['options']=Array('0' => MwLang::get('backend.labels.page_Hidden_0'),'1' => MwLang::get('backend.labels.page_Hidden_1'));
    $this->formFields[$p['fieldname']]=$p;

    $p['label']= MwLang::get('backend.labels.page_ShowInMenus');
    $p['fieldname']="ShowInMenus";
    $p['type']="checkbox";
    $this->formFields[$p['fieldname']]=$p;

/*    //define all FormFields for step "Title"
    $p=Array(); // ------- new field --------
    $p['label']="Redirect to URL";
    $p['fieldname']="RedirectURL";
    $this->formFields[$p['fieldname']]=$p;*/

    //define all FormFields for step "Title"
/*    $p=Array(); // ------- new field --------
    $p['label']="Meta-Description";
    $p['fieldname']="MetaDescription";
    $p['type']="textarea";
    $this->formFields[$p['fieldname']]=$p;
*/
    $p=Array(); // ------- new field --------
    $p['label']=MwLang::get('backend.labels.page_PublishOn');
    $p['note']=MwLang::get("backend.labels.page_PublishOn_note");
    $p['fieldname']="PublishOn";
    $p['type']="text";
    $p['date_locale']="de_DE";
    $p['addon_classes']="datetimepicker";
    $this->formFields[$p['fieldname']]=$p;

    $p=Array(); // ------- new field --------
    $p['label']=MwLang::get('backend.labels.page_HideOn');
    $p['note']=MwLang::get("backend.labels.page_HideOn_note");
    $p['fieldname']="HideOn";
    $p['date_locale']="de_DE";
    $p['type']="text";
    $p['addon_classes']="datetimepicker";
    $this->formFields[$p['fieldname']]=$p;

    // if($this->record && $this->record->hasField('ArchiveOn'))
    // {
    //     $p=Array(); // ------- new field --------
    //     $p['label']="archive on";
    //     $p['note']="archive Page on this day";
    //     $p['fieldname']="ArchiveOn";
    //     $p['type']="text";
    //     $p['date_locale']="de_DE";
    //     $p['addon_classes']="datetimepicker";
    //     $this->formFields[$p['fieldname']]=$p;
    // }
    


  }


  public function accessIsAllowed()
  {
    $ret=parent::accessIsAllowed();
    if ($ret==false) {
      
      if (Controller::curr()->urlParams['Action']=='ng_pagemanager') {

        $command=Controller::curr()->urlParams['ID'];
        $singleArgument=Controller::curr()->urlParams['OtherID'];
        if ($command=='translatedTemplate') {
          $ret=true;
        }
        
      }

    }
    return $ret;
  }

  public function ng_pagemanager()
  {
    //command / args
    
    $pm=PageManager::singleton();

    $command=Controller::curr()->urlParams['ID'];
    $singleArgument=Controller::curr()->urlParams['OtherID'];
    if ($command) {
      $args = json_decode(file_get_contents('php://input'),1);
      if($singleArgument) {
        $args['singleArgument']=$singleArgument;
      }

      $ret=$pm->executeCommandForJson($command,$args);
      header('content-type: application/json; charset=utf-8');
    } else {
      $ret['status']='error';
      $ret['msg']='no command given';
    }

    echo json_encode($ret);
    exit();

  }


  public function ng_insert_page_at_position()
  {
     

    $args = json_decode(file_get_contents('php://input'),1);
    $ret=$this->insertPageAtPosition($args['insertData'],$args['insertPosition'],$args['insertOptions']);
    header('content-type: application/json; charset=utf-8');
    echo json_encode($ret);
    exit();


  }



  // public function ng_dummy()
  // {

  //   $args = file_get_contents('php://input');

  //   $ret=Array('status'=>'ok');
  //   echo json_encode($ret);
  //   exit();
  // }



   public function ajaxAdd()
   {

      header('content-type: application/json; charset=utf-8');
      
      if($id=intval(array_get($_POST,'id')))
      {
        $pageData['Title']=array_get($_POST,'title');
        $newpage=$this->createPageUnder(array_get($_POST,'id'),$pageData);
        
        $ret['status']='OK';
        $ret['id']=$newpage->ID;
      }

      echo json_encode($ret);
      exit();

   }


 public function ajaxDelete()
   {

      header('content-type: application/json; charset=utf-8');
      
      
      if($id=intval(array_get($_POST,'id')))
      {
        $page=SiteTree::get_by_id(SiteTree::class,$id);
        $page->delete();
      }

       $ret =null;
       echo json_encode($ret);
      exit();

   }

   
 public function ajaxHide()
   {

      header('content-type: application/json; charset=utf-8');
      
      if($id=intval(array_get($_POST,'id')))
      {
        $page=SiteTree::get_by_id(SiteTree::class,$id);
        $page->Hidden=1;
        $page->write();
      }
       $ret=null;

      echo json_encode($ret);
      exit();

   }

 public function ajaxUnHide()
   {

      header('content-type: application/json; charset=utf-8');
      
      if($id=intval(array_get($_POST,'id')))
      {
        $page=SiteTree::get_by_id(SiteTree::class,$id);
        $page->Hidden=0;
        $page->write();
      }
       $ret=null;
      echo json_encode($ret);
      exit();

   }


 public function ajaxDuplicate()
   {

      header('content-type: application/json; charset=utf-8');
      
      if($id=intval(array_get($_POST,'id')))
      {
        $newpage=$this->duplicatePage(array_get($_POST,'id'));
        $ret['status']='OK';
        $ret['id']=$newpage->ID;
      }

      echo json_encode($ret);
      exit();

   }

   function duplicatePage($id,$newvalues=Array())
   {
       Versioned::set_stage("Live");

       $existingPage=SiteTree::get_by_id(SiteTree::class,$id);

       $newpage=$existingPage->duplicate();
       if(!$newvalues['Title']) {
         $newpage->Title.=' (Copy)';
       }

       if($newvalues)
       {
           $newpage->update($newvalues);
       }
       $newpage->write();
       return $newpage;
   }

   public function ajaxDuplicateWithChildren()
   {

       header('content-type: application/json; charset=utf-8');
      
       if($id=intval(array_get($_POST,'id')))
       {
           MwPage::setConf('ajaxDuplicateWithChildren_in_progress',1);
           $newpage=$this->duplicateWithChildren(array_get($_POST,'id'));
           $ret['status']='OK';
           $ret['id']=$newpage->ID;
       }

       echo json_encode($ret);
       exit();

   }

   function duplicateWithChildren($id,$newvalues=NULL)
   {
       Versioned::set_stage("Live");

       $existingPage=SiteTree::get_by_id(SiteTree::class,$id);

       $newpage=$existingPage->duplicateWithChildren();
       $newpage->Title.=' (Copy)';
       if($newvalues)
       {
           $newpage->update($newvalues);
       }
       $newpage->write();
       return $newpage;
   }

   public function parseID($str)
   {
     // makes node_nnn to nnn
     preg_match('#^node_(\d+)$#',$str,$m);
     if($m[1] || $m[1]=='0')
      return $m[1];
     else
      return NULL;
   }
   
   public function ajaxUpdateNodeChildren()
   {
     header('content-type: application/json; charset=utf-8');

     $ret=Array();

     $id=$this->parseID(array_get($_POST,'id'));

     if($id!=NULL)
     {

         if($id>0)
         {
             $ParentPage=Dataobject::get_by_id(SiteTree::class,$id);
         }

       if($ParentPage->ID || $id==0)
       {
         $sortnum=0;
         if(is_array(array_get($_POST,'childrenids')))
         foreach (array_get($_POST,'childrenids') as $childrenid) {
           $sortnum+=10;
           if($page=Dataobject::get_by_id(SiteTree::class,$this->parseID($childrenid)))
           {
             //update sort
             $page->Sort=$sortnum;
             $page->setParent($id);   
             $page->write();
             $ret['status']='OK';
             $ret['affected_nodes'][]='node_'.$page->ID;
           }else
             $ret['node_not_found'][]='node_'.$page->ID;
          }
       }
     }
     

     echo json_encode($ret);
     exit();

   }

  public function delete()
  {
     $id=Controller::curr()->urlParams['ID'];
     if($id)
       $this->record=Dataobject::get_by_id($this->myClass,$id);
       $parent=$this->record->getParent();
       if($this->record) {
         $this->record->delete();
       }
//     $this->summitSetTemplateFile("Layout","Bp{$this->myClass}_listing");
    

       if ($this->SkinVersion>=2) {
         $this->summitSetTemplateFile("main","BackendPage_rightframe");
       } else {
         $this->summitSetTemplateFile("main","BackendPage_iframe");
       }

     return Array('Content'=>"<div class='error'>Page was deleted</div>");
  }
   
   
   public function getDefaultPageClass()
   {
     $classlist=$this->getAllowedPageClasses();

     if($classlist){
      foreach ($classlist as $classname => $dummy) {
        $p=singleton($classname);
        if(is_subclass_of($p,SiteTree::class))
        {
         return $classname;
        }
      }
     }
     else
      return 'Page';
   }


    public function insertPageAtPosition($insertData, $insertPosition=Array(), $insertOptions=Array())
    {

//❖ TODO: check access
//❖ prepare page:
        Versioned::set_stage("Live");



      if(!$insertPosition['position']) {
        $insertPosition['position']='append';
      }

      if ($insertOptions['templateId']) {
        $sourceId=$insertOptions['templateId'];
      }
      if ($insertOptions['sourceId']) {
        $sourceId=$insertOptions['sourceId'];
      }

      if ($sourceId) {
        /* copy page: */
        $existingPage=SiteTree::get_by_id(SiteTree::class,$sourceId);
        if (!$existingPage) {
          throw new Exception('cannot find existing page to duplicate');
        }
        $newPage=$existingPage->duplicate();
      } else {
        /* create page: */

        $pageClass=$insertData['pageClass'];
        if(!$pageClass) {
          throw new Exception('no pageclass given');
        }
        $newPage=new $pageClass;

       

      }
      if(!$newPage) {
        throw new Exception('Page could not be created');
      }


      if(!$insertOptions['sourceId']) {

       if($insertData['Title'] && !$insertData['URLSegment']) {
          $insertData['URLSegment']=MwUtils::generateURLSegment($insertData['Title']);
       }
       
      }



//❖ find ParentID of Page
      if($insertPosition['position']=='append' || $insertPosition['position']=='prepend') {
        $newPage->setParent($insertPosition['referenceId']);
        if(!$newPage->ParentID) {
          throw new Exception('ParentID could not be determined');
        }
        if ($insertPosition['position']=='append') {
          $listPosition='last';

        }
        if ($insertPosition['position']=='prepend') {
          $listPosition='first';
        }
      }

      $newPage->update($insertData);

      $newPage->Sort=$this->getSortNumberForPage($newPage->ParentID,$listPosition /*,$referenceId*/ );
      $newPage->write();



      $ret['status']='ok';
      $ret['payload']=array(
            'ID'=>$newPage->ID,
            'ClassName'=>$newPage->ClassName,
            'ParentID'=>$newPage->ParentID,
            'Sort'=>$newPage->Sort,
            'Title'=>$newPage->Title,
            'URLSegment'=>$newPage->URLSegment
      );

      return $ret;





  }

 public function getSortNumberForPage($parentId,$listPosition,$referenceId=NULL)
  {

    $this->fixSortNumbersForChildren($parentId);

    $db=DBMS::getMdb();


    if ($listPosition=='first') {
      $minSort=$db->getOne("select min(Sort) from SiteTree_Live where ParentID=".Convert::raw2sql($parentId));
      return $minSort-10;
    }

    if ($listPosition=='last') {
      $maxSort=$db->getOne("select max(Sort) from SiteTree_Live where ParentID=".Convert::raw2sql($parentId));
      return $maxSort+10;
    }


    if ($listPosition=='after' || $listPosition=='before') {
      $pageSort=$db->getOne("select Sort from SiteTree_Live where ParentID=".Convert::raw2sql($parentId)."ID=".Convert::raw2sql($referenceId));
      if ($listPosition=='after' ) {
        return $pageSort+5;

      } elseif ($listPosition=='before') {
        return $pageSort-5;
      }

    }

    
  }

  public function fixSortNumbersForChildren($parentID)
  {
    
    if(!is_numeric($parentID)) {
        throw(new Exception('wrong parentID '.$parentID));
    }
     $db=DBMS::getMdb();
     $sql="select ID,Sort from SiteTree_Live where ParentID={$parentID} order by Sort asc";
     $res=$db->getAssoc($sql);
     $n=0;
     foreach ($res as $pageId=>$pageSort) {
       $n=$n+10;
       if($pageSort!=$n) {
          $db->query("update SiteTree_Live set Sort={$n} where ID={$pageId}");
       }
     }


  }
 

   
   function createPageUnder($parentID,$pageData)
   {

     Versioned::set_stage("Live");

     if($pageData['ClassName']) {
       $pageclass=$pageData['ClassName'];
     } else {
       $parentObj=SiteTree::get_by_id(SiteTree::class,$parentID);
       $childclasses=$parentObj->allowedChildren();
       $pageclass=$childclasses[0];
       if($pageclass==SiteTree::class || !$pageclass)
       {
         $pageclass=$this->getDefaultPageClass();
       }
     }
     
     $newpage=new $pageclass;
    
      if(!$pageData['Title'])
        $pageData['Title']='new '.FormField::name_to_label($newpage->ClassName).' created on '.Date('m/d/Y');
        
     $newpage->update($pageData);
     $newpage->setParent($parentID);   
     $newpage->write();
     

     return $newpage;
   }
   
   public function Pages()
   {
     $pages=DataObject::get(SiteTree::class,"ParentID={$this->ParentID}");
     
     return $pages;
   }
   
   
   static public function getRootIDsForTree()
   {

     $pm=PageManager::singleton();

     return $pm->getRootIDsForTree();

 
   }

     
   
   public function AllParentNodesOfCurrentPage()
   {
     $arr=Array();

     if($this->record && is_subclass_of($this->record, SiteTree::class))
     foreach ($this->record->getAncestors() as $p) {
       $arr[]="node_".$p->ID;
     }
     
     return json_encode($arr);
   }
   



   public function ng_context_menu_items_for_page_tree_item()
   {

        header('content-type: application/json; charset=utf-8');
        $ret=Array('status'=>'ok');
        $ret['payload']=Array(
          'action1'=> Array(
            'label'=> "kopieren",
            'icon' => 'fa fa-trash-o',
            ),
          'action2'=> Array(
            'label'=> " open popup ",
            'icon' => 'fa fa-star',
            ),
          );

        echo json_encode($ret);
        die();

       
   }
    public function ajaxTreeData_v2()
    {
     
      $pm=PageManager::singleton();

      return $pm->getAjaxTreeData();

    }

    public function ajaxTreeData()
    {
        
      $db=DBMS::getMdb();

      $context=array_get($_REQUEST,'context');
      $mode='edit';
      if(strstr($context,'MwLinkChooser'))
      {
        $mode='mwlinkchooser';
      }
    
      if(array_get($_GET,'id'))
      {
        $parentid=intval(array_get($_GET,'id'));
        $parentpage=DataObject::get_by_id(SiteTree::class,$parentid);
        $rootPages=$parentpage->liveChildren(TRUE);
      }
      else
      {
        $rootIds=$this->getRootIDsForTree();

        if($rootIds)
          $rootPages=DataObject::get(SiteTree::class,"SiteTree_Live.ID in (".implode(',',$rootIds).")");
        else
          $rootPages=DataObject::get(SiteTree::class,"ParentID=0");
      }

      if($rootPages)
       foreach ($rootPages as $p) {
         
          if($mode=='mwlinkchooser' || !method_exists($p,'isVisibleInBpPageTree') || $p->isVisibleInBpPageTree())
          {
            $hasChildren=$db->getOne("select ID from SiteTree_Live where ParentID={$p->ID} LIMIT 1");
            
            if($mode=='mwlinkchooser')
              $url="/BE/Pages/MwLinkChooser/{$p->ID}";
            else              
              $url="/BE/Pages/edit/{$p->ID}";

              $cssClasses='';
              if($p->Hidden)
                $cssClasses.='hidden ';

              if($p->ShowInMenus==0)
                $cssClasses.=' notinmenu';

            $node=Array('data' => Array('title' => $p->MenuTitle,
                                        'attr'=>Array('href' => $url,"class"=>$cssClasses,"title"=>"{$p->URLSegment}/ - {$p->ClassName}")
                                        ),
                        'attr' =>Array('id' => 'node_'.$p->ID),
                        'state'=>$hasChildren?"closed":"",                 
                       );
            $tree[]=$node;
          }
         }

       header('content-type: application/json; charset=utf-8');
       return json_encode($tree);
       
       exit();
    }
   
   
   public function folderTreeAsUL()
   {
     $rootIds=$this->getRootIDsForTree();
     $tree='';
     if($rootIds)
       $rootPages=DataObject::get(SiteTree::class,"SiteTree_Live.ID in (".implode(',',$rootIds).")");
     else
       $rootPages=DataObject::get(SiteTree::class,"ParentID=0");

     foreach ($rootPages as $p) {
       $subtree = $p->getChildrenAsUL('','"<li id=\'" . $child->ID . "\'><a href=\'/BE/Pages/edit/" . $child->ID . "\'  class=\'" . ($child->ShowInMenus?"":"hidden") . "\' >" . $child->Title . "&nbsp;</a>" ',null,$limittomarked=1,'liveChildren'); 
       $tree.="<li id='{$p->ID}'><a href='/BE/Pages/edit/{$p->ID}'>{$p->Title}</a>$subtree</li>";
     }
     
     return "<ul>$tree</ul>";
   }
   
  


   public function AllFormFields()
   {
       $ret='';
       if($this->formFields){
           foreach (array_keys($this->formFields) as $name) {
               $ret.=$this->FormField($name);
           }
       }
       return $ret;
   }

   public function FormField($name)
   {

    $fielddata=$this->formFields[$name];

    if($fielddata) {
      if (Permissions::canShowFormField($name,$fielddata, $this->record)) {
        return $this->MwForm->render_field($fielddata);
      }
    }
    else {
      return "";
    }
  }

    public function getRawTabItems()
    {
      $items=Array(
        "10"=>"Main",
        "20"=>"Settings",
        );

      return $items;
    }


    // CElement related functions BEGIN -----------------------------------

    /*
       shows a list of CElements given for a certain field
    */

    public function ajaxCElementList()
    {
       if($this->loadRecord())
       {     
         return CElement::ajaxCElementList($this->record);
       }     
    }

    public function ajaxCElement()
    {
       //further handling is done in CElement-Class
       CElement::dispatch($this);
    }

    // CElement related functions END -----------------------------------





    public function TabItems()
    {


      $lastTabItem=NULL;
      $currentTabItem=NULL;

      $navitems=new ArrayList();
      $tabitems=$this->getRawTabItems();
      ksort($tabitems);
      foreach($tabitems as $url=>$name)
      {

        if($this->calledViaWithin)
          $link="";
        else
          $link="/BE/Pages/".Controller::curr()->urlParams['Action']."/".Controller::curr()->urlParams['ID']."/".$url;

        $tabItem=new ArrayData(
        Array(
          "Link"=>$link,
          "Title"=>$name,
          "URLSegment"=>$url,
          "Current"=>($url==$this->CurrentTab()),
          )
          );
        if($tabItem->Current)
        {
          $this->currentTabItem=$tabItem;
          $this->prevTabItem=$lastTabItem;
        }

        if($lastTabItem->Current)
        {
          $this->nextTabItem=$tabItem;
        }

        if (Permissions::canShowPageTab($url,$this->record)) {
          $navitems->push($tabItem);
          $lastTabItem=$tabItem;
        }
      }

      return $navitems;

    }

    public function CurrentTabIn($values)
    {
      $values=explode('-',$values);
      
      return in_array($this->CurrentTab(),$values);
    }

    public function CurrentTab()
    {
    
      static $available_keys1;
      $id=Controller::curr()->urlParams['OtherID'];

      if(!$id)
      {
          if(!$available_keys1)
          {
              $available_keys1=array_keys(($this->getRawTabItems()));
              sort($available_keys1);
          }
          $available_keys=$available_keys1;
          foreach ($available_keys as $key => $value) {
              $available_keys[$key]="$value";
          }
                  
        //try to find ID from previous page
        if(preg_match('#/edit/\d+/([^/]+)$#',array_get($_SERVER,'HTTP_REFERER'),$m))
        {
          //take the one which was used lately
          if(in_array($m[1],$available_keys))
            $id=$m[1];
        }
        else {
            
            $id=Mwerkzeug\MwSession::get('LastSelectedTab');
            if(!in_array($id,$available_keys,TRUE) || !Permissions::canShowPageTab($id,$this->record))
            {
                unset($id);
            }
        }

        
        if(!$id)
         $id=array_shift($available_keys);//take first one
      }
      else
      {
          $available_tabs=$this->getRawTabItems();
          if(is_array($available_tabs) && $available_tabs[$id])
          {
              Mwerkzeug\MwSession::set('LastSelectedTab',$id);
          }
      }

      return $id;
    }

  
    public function getJSValidationMessages()
    {
        if($this->MwForm) {
          return $this->MwForm->getValidationMessages();
        }
    }

    public function getJSValidationRules()
    {
        if($this->MwForm) {
          return $this->MwForm->getValidationRules();
        }
    }
  

     public function getMaxAvailableSkinVersion()
   {
       return 2;
   }  

   public function NodeData()
   {

    $pm=PageManager::singleton();
    $nd=$pm->getNodeDataForPage($this->record);
    return new ArrayData($nd);

     
   }

   public function LinkBaseUrl()
   {
      $parent=$this->record->Parent();
      if ($parent) {
        $url=$parent->Link();
      } else {
        $url="/";
      }
     return $url;
   }



  public function step_99_C4P_Place_PagePermissions_plainFieldsWrapper($html)
  {

    return singleton('MwPagePermissions_Page')->getBackendPageHeader($this->record);

  }



}




?>
