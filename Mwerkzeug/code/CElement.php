<?php

use Mwerkzeug\MwRequirements;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\Requirements;
use SilverStripe\i18n\i18n;
use SilverStripe\Control\Controller;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Control\Session;
use SilverStripe\View\ViewableData;

class CElement extends ViewableData
{
    public $record=array();
    public $ID;
    public $Position=0;
    public $Fieldname;
    public $ParentPath;
    public $Mainrecord;
    public $MainrecordBeforeAliasing;
    public $editMode;
    public $MwFiles;
    public $CType='';
    public $cache;

    public static $myCasting = array(
      'Image'=>'MwFile',
      'Picture'=>'MwFile',
      'Picture2'=>'MwFile',
      'Picture3'=>'MwFile',
      'Logo'=>'MwFile',
      'Video'=>'MwFile',
      'File'=>'MwFile',
    );


    public function isMod($mod)
    {
        return ($this->Pos(0) % $mod == 0);
    }


    public function isNew()
    {
        return $this->record['CType']?false:true;
    }

    public function setPosition($pos)
    {
        $this->Position=$pos;
    }
  
    public function setData($data)
    {
        $this->record=$data;
    }

    public function setField($key, $value)
    {
        $this->record[$key]=$value;
    }

    public function hide()
    {
        $this->setField('Hidden', 1);
        $this->save(array('Hidden'=>$this->Hidden));
    }

    public function unhide()
    {
        $this->setField('Hidden', 0);
        $this->save(array('Hidden'=>$this->Hidden));
    }
  
    public function toggleHidden()
    {
        $this->setField('Hidden', ($this->Hidden)?'0':'1');
        $this->save(array('Hidden'=>$this->Hidden));
    }
 
    public function hasTimers()
    {
        if ($this->record['HideOn'] || $this->record['PublishOn'] || $this->record['ArchiveOn']) {
            return true;
        }
    }
  


    public function getToprecord()
    {
        return $this->Mainrecord;
    }


    public function getC4PLink($params=array())
    {
        if ($this->AliasTo && !$params['doNotResolveAliases']) {
            return $this->AliasTo;
        }

        if ($params['doNotResolveAliases']) {
            $ret=$this->getToprecordOfAlias()->MwLink.'/'.$this->Fieldname;
        } else {
            $ret=$this->getToprecord()->MwLink.'/'.$this->Fieldname;
        }
        if ($this->ParentPath) {
            $ret.="/".$this->ParentPath;
        }
        // die("\n\n<pre>mwuits-debug 09:09:36 : ".print_r($ret,1));
        return $ret."/".$this->ID;
    }
    public function getAliasRecord()
    {
        $ret=array();
        foreach ($this->record as $key => $value) {
            if (in_array($key, array('CType','Hidden','HideOn','PublishOn','ArchiveOn'))) {
                $ret[$key]=$value;
            }
        }
        $ret['AliasTo']=$this->getC4PLink();
        return $ret;
    }

    public function CssClass()
    {
        if ($this->Hidden) {
            $cclasses.='c4p-hidden ';
        }
      
        if ($this->editMode) {
            $cclasses.='editmode ';
        }
          
        return $cclasses;
    }
  
    public function Mod($mod)
    {
        return ($this->Pos(0) % $mod);
    }
  
    public function getCTypeLabel()
    {
        return $this->CType;
    }
  
    public function getCssID()
    {
        return "{$this->Fieldname}-{$this->Mainrecord->ID}-{$this->ID}";
    }

    public function __construct($mainrecord, $fieldname='none', $celement_id='none', $data=null)
    {
        if (strstr($fieldname, '/')) {
            $parts=explode('/', trim($fieldname, '/'));
            $fieldname=array_shift($parts);
            $this->ParentPath=implode('/', $parts);
        }

        $this->ID=$celement_id;
        $this->Mainrecord=$mainrecord;
        $this->Fieldname=$fieldname;
        if ($data['MainrecordBeforeAliasing']) {
            $this->MainrecordBeforeAliasing=$data['MainrecordBeforeAliasing'];
            unset($data['MainrecordBeforeAliasing']);
        }
        if ($data) {
            $this->record=$data;
        }
    
        $this->init();
    }

    public function setMainrecord($mainrecord)
    {
        $this->Mainrecord=$mainrecord;
    }

  
    public function getParent()
    {
        return $this->Mainrecord;
    }
  
    public function init()
    {
        // to be overridden
    }
    
    public function MwFile($fieldname)
    {
        if (!isset($this->MwFiles[$fieldname])) {
            $fileid=$this->record[$fieldname.'ID'];
            if ($fileid>0) {
                $x=DataObject::get_by_id('MwFile', 42);
        
                if ($this->MwFiles[$fieldname]=DataObject::get_by_id('MwFile', $fileid)) {
                    $this->MwFiles[$fieldname]->setExtParent($this, $fieldname);
                }
            } else {
                $this->MwFiles[$fieldname]=null;
            }
        }
        return $this->MwFiles[$fieldname];
    }

  
    public function __get($fieldname)
    {
        if (isset($this->record[$fieldname])) {
            return $this->record[$fieldname];
        } elseif (self::$myCasting[$fieldname]=='MwFile' && isset($this->record[$fieldname.'ID'])) {
            $ret= $this->MwFile($fieldname);
            return $ret;
        } else {
            return parent::__get($fieldname);
        }
    }
  
    public function includeRequirements()
    {
        MwBackendPageController::includePartialBootstrap(array('scripts'=>'dropdown'));
      
        Requirements::javascript('Mwerkzeug/javascript/CElement_jquery_plugin.js');
        Requirements::javascript('Mwerkzeug/javascript/jquery.json-2.2.min.js');
    
        Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.datepicker.js');
        if (i18n::get_locale()=="de_DE") {
            Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/i18n/jquery.ui.datepicker-de.js');
        }
 
    
        Requirements::javascript("Mwerkzeug/javascript/MwButtonDropdown_jquery_plugin.js");
        Requirements::css('Mwerkzeug/css/CElement.css');
        MwRequirements::css('mysite/css/CElement.css');
        BackendHelpers::includeTinyMCE();
        MwFileField::includeRequirements();
        MwLink::includeRequirementsForMwLinkField();
    }
  
    public function myUnserialize(&$value, $fieldname)
    {
        if (strstr($fieldname, 'json')) {
            $ret=json_decode($value, 1);
        } else {
            $ret=unserialize($value);
        }
    
        if (is_array($ret)) {
            return $ret;
        }
    }


    public function mySerialize(&$value, $fieldname)
    {
        if (strstr($fieldname, 'json')) {
            return json_encode($value);
        } else {
            return serialize($value);
        }
    }
  
  
    public static function dispatch($Controller)
    {
        $action=Controller::curr()->urlParams['OtherID'];
        preg_match("#([^-]+)-(\d+)-(-?\d+)#", Controller::curr()->urlParams['ID'], $m);
    
        $fieldname=$m[1];
        $record_id=$m[2];
        $celement_id=$m[3];


        if ($Controller->myClass) {
            $record=DataObject::get_by_id($Controller->myClass, $record_id);
        } else {
            $record=DataObject::get_by_id(SiteTree::class, $record_id);
        }

        $celement=self::getCElement($record, $fieldname, $celement_id);
    
        if (!$celement) {
            if (array_get($_GET, 'default_CType')) {
                $objname="CElement_".array_get($_GET, 'default_CType');
            } else {
                $objname="CElement_Absatz";
            }
      
            $celement=new $objname($record, $fieldname, $celement_id);
        }

        $args=array(); //no arguments so far (url completely parsed)
        echo call_user_func_array(array($celement,$action), $args);
        exit();
    }
  
    public function BackendItemHtml()
    {
        return $this->show();
    }

    public function handleShowActions()
    {
        if (array_get($_GET, 'action')) {
            switch (array_get($_GET, 'action')) {
                   case 'toggle_hide':
                   $this->toggleHidden();
               }
        }
    }
  
    public function show()
    {
        $this->handleShowActions();
        $c=array();
        return $this->customise($c)->renderWith("CElement_{$this->CType}_ajaxItem");
    }

    public function edit()
    {
        $this->editMode=1;
        $c=array('CssID'=>Controller::curr()->urlParams['ID'],'celementbaseurl' => array_get($_GET, 'baseurl'));
        return $this->customise($c)->renderWith("CElement_{$this->CType}_ajaxItem");
    }
  
  
    public function checkTimers()
    {
        if ($this->hasTimers()) {
            $now=time();
          
            // make timer-queue
            if ($this->HideOn && $this->HideOn<$now) {
                $queue[$this->HideOn]='HideOn';
            }
            if ($this->PublishOn && $this->PublishOn<$now) {
                $queue[$this->PublishOn]='PublishOn';
            }

            if ($queue) {
                ksort($queue);
                foreach ($queue as $ts => $action) {
                    switch ($action) {
                      case 'HideOn':
                      $changefields['HideOn']='';$this->setField('Hidden', 1);
                      break;
                      case 'PublishOn':
                      $changefields['PublishOn']='';$this->setField('Hidden', 0);
                      break;
                  }
                }
                //save values
                $changefields['Hidden']=$this->Hidden;
                $this->save($changefields);
            }
        }
    }

    public static function handleAliasTo(&$data, &$Mainrecord)
    {
        $parts=parse_url($data['AliasTo']);

        $SourceMainrecord=MwLink::getObjectForMwLink($parts['scheme'].'://'.$parts['host']);
        if ($SourceMainrecord) {
            $pathParts=explode('/', trim($parts['path'], '/'));
            $SourceFieldname=array_shift($pathParts);
            $id=array_pop($pathParts);
            $path=implode('/', $pathParts);

            $value=MwSiteTree::getFieldForSure($SourceMainrecord, $SourceFieldname);
            if ($value) {
                $items=self::myUnserialize($value, $SourceFieldname);
            }
            if (!$path) {
                $dataFromSource=$items[$id];
            } else {
                $dataFromSource=self::arrayGetByPath($items, "$path/$id");
            }
            if (!$dataFromSource) {
                $dataFromSource=array('AliasError'=>array('Key'=>'element_not_found','Msg'=>'Source-Element cannot be found anymore !'));
            } elseif ($dataFromSource['CType']!=$data['CType']) {
                $dataFromSource=array('AliasError'=>array('Key'=>'wrong_type','Msg'=>'Source-Element has changed its Type'));
            }

            $MainrecordForElement=$SourceMainrecord;
            $dataFromSource['IsAlias']=true;
            $dataFromSource['MainrecordBeforeAliasing']=$Mainrecord;
        } else {
            $dataFromSource=array('AliasError'=>array('Key'=>'page_not_found','Msg'=>'Source-Page has been deleted !'));
        }

        foreach (array('CType','Hidden','HideOn','PublishOn','ArchiveOn') as $key) {
            unset($dataFromSource[$key]);
        }

        $data=MwUtils::array_merge_recursive_distinct($dataFromSource, $data);

        return $MainrecordForElement;
    }
 
  
    public static function getCElementsForField($Mainrecord, $FieldnameWithPath, $p=array())
    {
        $cachename=__FUNCTION__.$FieldnameWithPath;

        if (!$Mainrecord->cache[$cachename] || $p['no_cache']) {
            if (strstr($FieldnameWithPath, '/')) {
                $pathParts=explode('/', trim($FieldnameWithPath, '/'));
                $Fieldname=array_shift($pathParts);
                $path=implode('/', $pathParts);
            } else {
                $Fieldname=$FieldnameWithPath;
            }

            $value=MwSiteTree::getFieldForSure($Mainrecord, $Fieldname);


            if ($value) {
                $items=self::myUnserialize($value, $Fieldname);
            }


            if ($path) {
                $items=self::arrayGetByPath($items, $path);
            }


            $dos=new ArrayList();

            if (is_array($items)) {
                $pos=0;
                foreach ($items as $itemId => $data) {
                    if (stristr($data['CType'], 'c4p')) {
                        $objname=$data['CType'];
                    } elseif ($data['CType']) {
                        $objname="CElement_".$data['CType'];
                    } else {
                        $objname="CElement_Absatz";
                    }

                    $MainrecordForElement=$Mainrecord;

                    if ($GLOBALS['already_used_aliaslinks']) {
                        unset($GLOBALS['already_used_aliaslinks']);
                    }

                    if ($data['AliasTo']) {
                        $MainrecordForElement=self::handleAliasTo($data, $Mainrecord);
                    }

                    if (class_exists($objname)) {
                        // if($data['AliasTo']) {
                        //   $data['MainrecordBeforeAliasing']=$Mainrecord;
                        // }
                        $obj=new $objname($MainrecordForElement, $FieldnameWithPath, $itemId, $data);

                        $obj->checkTimers();
                        if ($p['include_hidden'] || (!$obj->Hidden && !$obj->AliasError)) {
                            $pos++;
                            $obj->setPosition($pos);
                            $dos->push($obj);
                        }
                    }
                }
            }


            $Mainrecord->cache[$cachename]=$dos;
        }

        return $Mainrecord->cache[$cachename];
    }


    public static function getCElementForMwLink($mwLink)
    {
        $parts=parse_url($mwLink);
        $mainrecord=MwLink::getObjectForMwLink($parts['scheme'].'://'.$parts['host']);
        if ($mainrecord) {
            $cElement=CElement::getCElement($mainrecord, $parts['path']);
            if ($cElement) {
                return $cElement;
            }
        }
    }

  
    public static function getCElement($Mainrecord, $FieldnameWithPath, $itemId=null, $params=array())
    {
        if (strstr($FieldnameWithPath, '/')) {
            $pathParts=explode('/', trim($FieldnameWithPath, '/'));
            $Fieldname=array_shift($pathParts);
            if ($itemId==null && $pathParts) {
                $itemId=array_pop($pathParts);
            }
            $path=implode('/', $pathParts);

            $FieldnameWithPath=trim($Fieldname.'/'.$path, '/');
        } else {
            $Fieldname=$FieldnameWithPath;
        }

        $value=MwSiteTree::getFieldForSure($Mainrecord, $Fieldname);


        if ($value) {
            $items=self::myUnserialize($value, $Fieldname);
        }
        if (!$path) {
            $data=$items[$itemId];
        } else {
            $data=self::arrayGetByPath($items, "$path/$itemId");
        }



        if ($data) {
            if (stristr($data['CType'], 'c4p')) {
                $objname=$data['CType'];
            } elseif ($data['CType']) {
                $objname="CElement_".$data['CType'];
            } else {
                $objname="CElement_Absatz";
            }

            $MainrecordForElement=$Mainrecord;

            if ($data['AliasTo'] && $params['resolve_aliases']) {
                $MainrecordForElement=self::handleAliasTo($data, $Mainrecord);
            }

            if (class_exists($objname)) {
                return new $objname($MainrecordForElement, $FieldnameWithPath, $itemId, $data);
            } else {
                die("\n\n<pre>mwuits-debug 13:36:32 : ".print_r('cannot find class'.$objname, 1));
            }
        }

        return null;
    }
  
    /**
     * Get an item from an array using "/" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function &arrayGetByPath(&$array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('/', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return $default;
            }

            $array = &$array[$segment];
        }

        return $array;
    }



    public static function ajaxCElementList($Mainrecord)
    {
        //moved to BpCelement
        return BpCElementController::getAjaxCElementList($Mainrecord);
    }


    public function update($fdata)
    {
        //clean up fdata
        if (!is_array($this->record)) {
            $this->record=array();
        }
        $this->record=MwUtils::array_merge_recursive_distinct($this->record, $fdata);
    }

    public function write($params=null)
    {
        if ($this->Mainrecord && $this->Fieldname) {
            if (!$this->record['CType'] && $this->CType) {
                $this->record['CType']=$this->CType;
            }
          
            if ($this->hasMethod('onBeforeWrite')) {
                $this->onBeforeWrite($this->record);
            }
         
            if (preg_match('#^_children(_[a-z0-9]+)?$#', $this->Fieldname)) {
                // handle c4p-children ---------- BEGIN
              
                $this->Mainrecord->update(array($this->Fieldname=>array($this->ID=>$this->record)));
                $this->Mainrecord->write();
            // handle c4p-children ---------- END
            } else {
                // normal save in field ---------- BEGIN
            
                $existing_serialized_data=MwSiteTree::getFieldForSure($this->Mainrecord, $this->Fieldname);
      
                if ($existing_serialized_data) {
                    $topLevelData=self::myUnserialize($existing_serialized_data, $this->Fieldname);
                } else {
                    $topLevelData=array();
                }

                //$data is the array given from our parent, which contains all our siblings
                // where want to save ourselves in

                if ($this->ParentPath) {
                    $data=&self::arrayGetByPath($topLevelData, $this->ParentPath);
                    if (!$data && preg_match('#^.*/_children(_[a-z0-9]+)?$#', $this->ParentPath)) {
                        //if '_children' array is asked for but not given in our parent
                        $upperData=&self::arrayGetByPath($topLevelData, dirname($this->ParentPath));
                        if (is_array($upperData)) {
                            $upperData[basename($this->ParentPath)]=array();
                            //parent exists, we can save into an empty array now, which will be added at $this->ParentPath
                        }
                        $data=&$upperData[basename($this->ParentPath)];
                    }
                } else {
                    $data=&$topLevelData;
                }

                if (!isset($data[$this->ID])) {

                  //CElement not already present in list ?
                    if (array_get($_POST, 'insertAfter')) {
                        $params['insertAfter']=array_get($_POST, 'insertAfter');
                    }
                  
                    if ($params['insertAfter']) {
                        $insertAfterId=self::getIdFromCssId($params['insertAfter']);
                        $newdata=array();
                        foreach ($data as $id => $element) {
                            $newdata[$id]=$element;
                            if ($insertAfterId == $id) {
                                $newdata[$this->ID]=array();
                            }
                        }
                        $data=$newdata;
                    } elseif ($params['newitem_duplicateof']) {
                        $insertAfterId=$params['newitem_duplicateof'];
                        $newdata=array();
                        foreach ($data as $id => $element) {
                            $newdata[$id]=$element;
                            if ($insertAfterId == $id) {
                                $newdata[$this->ID]=array(); //create record in place
                            }
                        }
                        $data=$newdata;
                    } elseif ($params['newitem_before']) {
                        $insertBeforeId=$params['newitem_before'];
                        $newdata=array();
                        foreach ($data as $id => $element) {
                            if ($insertBeforeId == $id) {
                                $newdata[$this->ID]=array(); //create record in place
                            }
                            $newdata[$id]=$element;
                        }
                        $data=$newdata;
                    }
                }

                //append new data if it not already exists
                if (!$data[$this->ID]) {
                    $data[$this->ID]=array();
                }
        
                if (is_array($this->record)) {
                    $data[$this->ID]=MwUtils::array_merge_recursive_distinct($data[$this->ID], $this->record);
                  
                    //preserve children, do not merge them
                    foreach (array_keys($this->record) as $key) {
                        if (preg_match('#_children(_[a-z0-9]+)?#', $key) && isset($this->record[$key])) {
                            $data[$this->ID][$key]=$this->record[$key];
                        }
                    }
                }

                foreach ($data as $key=>$val) {
                    if (!$key) {
                        unset($data[$key]);
                    }
                }
              

                if ($this->record['ID'] && $this->ID!=$this->record['ID']) {
                    $newid=$this->record['ID'];
                    if (array_key_exists($newid, $topLevelData)) {
                        $msg="Error: ID already in use !";
                        die("<script>alert('$msg')</script>");
                    }
                    //replace element at same position:
                    $fixedTopLevelData=array();
                    foreach ($topLevelData as $key => $value) {
                        if ($key==$this->ID) {
                            $key=$newid;
                        }
                        $fixedTopLevelData[$key]=$value;
                    }
                    $topLevelData=$fixedTopLevelData;
                    $this->ID=$newid;
                }


                $this->Mainrecord->setField($this->Fieldname, self::mySerialize($topLevelData, $this->Fieldname));

                $this->Mainrecord->ShortText=time();
                $this->Mainrecord->write();
            }
            if ($this->hasMethod('onAfterWrite')) {
                $this->onAfterWrite($this->record);
            }
        }
    }

    public function remove()
    {
    
    // still with code for old frameversion-delete-method


        $existing_serialized_data=MwSiteTree::getFieldForSure($this->Mainrecord, $this->Fieldname);
    
        if ($existing_serialized_data) {
            $topLevelData=self::myUnserialize($existing_serialized_data, $this->Fieldname);

            if ($this->ParentPath) {
            
            //delete special
                //get array from parent
                $data=&self::arrayGetByPath($topLevelData, $this->ParentPath);
            } else {
                $data=&$topLevelData;
            }

            unset($data[$this->ID]);
            $this->Mainrecord->setField($this->Fieldname, self::mySerialize($topLevelData, $this->Fieldname));
            $this->Mainrecord->write();
        }

        
        $ret['preview_url']=$this->Mainrecord->Link();
        $ret['status']='ok';

        return json_encode($ret);
    }


    public function savetreesortelements()
    {
        $sortids=trim(array_get($_POST, 'sortIds'));
        $sortparentids=trim(array_get($_POST, 'sortParentIds'));

        if ($sortparentids && $sortids) {
            $existing_serialized_data=MwSiteTree::getFieldForSure($this->Mainrecord, $this->Fieldname);
        
            if ($existing_serialized_data) {
                $data=self::myUnserialize($existing_serialized_data, $this->Fieldname);
                $result_data=array();
                $sortparentids=explode(',', $sortparentids);
                foreach (explode(',', $sortids) as $id) {
                    $id=str_replace('node_', '', $id);
                    $parentid=array_shift($sortparentids);
                    $parentid=str_replace('node_', '', $parentid);
                    if ($data[$id]) {
                        $result_data[$id]=$data[$id];
                        $result_data[$id]['ParentID']=$parentid;
                    }
                }
          
          
                if (sizeof($data)==sizeof($result_data)) {
                    $this->Mainrecord->setField($this->Fieldname, self::mySerialize($result_data, $this->Fieldname));
                    $this->Mainrecord->write();
                } else {
                    die('error occured while sorting (nonequal list-sizes)');
                }
            }
        }
      
        $url=$this->Mainrecord->Link();

        $html=<<<HTML
        <script type="text/javascript" charset="utf-8">
        item=parent.jQuery('#{$this->CssID}');
        item.closest('.CElementList').CElement('list' );
        window.location='$url?preview='+new Date().getTime();
        </script>
HTML;
        return $html;
    }

    public function copySelectionToClipboard($params=null)
    {
        //create json-version of selected records
        $itemids=array_get($_POST, 'items');
        if (!$itemIds && $params) {
            $itemids=$params['items'];
        }

        if ($params['options']) {
            $options=$params['options'];
        } else {
            $options=array();
        }



        if ($itemids) {
            foreach ($itemids as $itemid) {
                if (preg_match("#([^-]+)-(\d+)-(\d+)#", $itemid, $m)) {
                    $celement_id=$m[3];
                    $celements2copy[]=CElement::getCElement($this->Mainrecord, $this->Fieldname, $celement_id);
                } elseif ($itemid) {
                    $fieldname=$this->Fieldname;
                    if ($this->ParentPath) {
                        $fieldname.='/'.$this->ParentPath;
                    }
                    $celements2copy[]=CElement::getCElement($this->Mainrecord, $fieldname, $itemid);
                }
            }
        } else {
            $ret['msg']="no itemids given";
        }


        $ret['status']='error';

        if ($celements2copy) {
            // if(array_get($_GET,'d') || 1 ) { $x=$_SESSION; $x=htmlspecialchars(print_r($x,1));echo "\n<li>mwuits: <pre>$x</pre>"; }

            $data2store=array();
            foreach ($celements2copy as $ce) {
                if ($options['makealias']) {
                    $data2store[]=$ce->getAliasRecord();
                } else {
                    $data2store[]=$ce->record;
                }
            }

            Mwerkzeug\MwSession::set('CElement_clipboard', null); //save clipboard to session
            Mwerkzeug\MwSession::save();
            Mwerkzeug\MwSession::set('CElement_clipboard', $data2store); //save clipboard to session
            Mwerkzeug\MwSession::save();

            $dataFromStore=Mwerkzeug\MwSession::get('CElement_clipboard'); //get clipboard
            $ret['num_items']=sizeof($dataFromStore);
            $ret['status']='ok';
            $ret['msg']=$ret['num_items']." item(s) have been put on the clipboard";

            if ($params && $params['options']) {
                if ($params['options']['storage']=='text') {
                    $ret['data']=$data2store;
                }
            } else {
                $ret['data']=json_encode($data2store); //old stuff
            }
        }
        if ($params['items']) {
            return $ret; //called new style ? -- return unencoded
        } else {
            return json_encode($ret);
        }
    }
  
  
  

    public function pasteSelectionFromClipboard($params=null)
    {
        $ret['status']='error';
        $allowed_ctypes=array_get($_POST, 'settings.allowed_CTypes');

        $jsonData=array_get($_POST, 'jsonData');
        if ($params && $params['settings']) {
            $allowed_ctypes=array();
            if ($params['settings']['placeconf']['allowed_types']) {
                foreach ($params['settings']['placeconf']['allowed_types'] as $key => $ctypeObj) {
                    $allowed_ctypes[$key]=$ctypeObj['label'];
                }
            }
            $jsonData=$params['jsondata'];
        }

        if ($params['allowedTypesForAdd']) {
            //if allowedTypesForAdd was sent explicitly, use this:
            $allowed_ctypes=array();
            foreach ($params['allowedTypesForAdd'] as $key=>$item) {
                $allowed_ctypes[$key]=$item['label'];
            }
        }

        $items=null;
        //get items from browser-clipboard if available
        $jsondata=trim($jsonData);
        if ($jsondata && $jsondata!='clipboard') {
            if (MwUtils::jsonIsValid($jsonData)) {
                $items=json_decode($jsonData, 1);
            } else {
                header('content-type: application/json; charset=utf-8');
                $ret['msg']="invalid JSON";
                echo json_encode($ret);
                exit();
            }
        }
        
        if (!$items) {
            $items=Mwerkzeug\MwSession::get('CElement_clipboard');
        }

        if ($items) {
            $good_items=0;
            $incompatible_items=0;
            foreach ($items as $itemdata) {

                // if(class_exists($itemdata['CType']))
                //     $obj=new $itemdata['CType']($this,'_dummy','dummy',$itemdata);
                
                $isCompatible=0;
                foreach ($allowed_ctypes as $allowedCType => $label) {
                    if ($itemdata['CType']==$allowedCType && class_exists($itemdata['CType'])) {
                        $isCompatible=1;
                        break;
                    }
                    // echo "<li> {$itemdata['CType']} ➜  {$allowedCType}";
                    if (is_subclass_of($itemdata['CType'], $allowedCType)
                        || is_subclass_of($allowedCType, $itemdata['CType'])) {
                        $isCompatible=1;
                        break;
                    }
                }

                if ($isCompatible) {
                    $fieldname=$this->Fieldname;
                    if ($this->ParentPath) {
                        $fieldname.='/'.$this->ParentPath;
                    }

                    $writeParams=array();
                    if (is_array($params['position']) && $params['position']['before']) {
                        $writeParams['newitem_before']=$params['position']['before'];
                    }

                    self::addCElementToObject($itemdata, $this->Mainrecord, $fieldname, $writeParams);
                    $good_items++;
                } else {
                    $incompatibleTypes[$itemdata['CType']]++;
                    $incompatible_items++;
                }
            }

            $ret['num_items']=$good_items;
            $ret['incompatible_items']=$incompatible_items;

            if ($incompatibleTypes) {
                $incompatibleTypeStr=" [".implode(',', array_keys($incompatibleTypes))."]";
            }
            if ($good_items && !$incompatible_items) {
                $ret['msg']=sprintf(MwLang::get('backend.texts.pasteC4Psuccess'), $good_items);
                $ret['status']='ok';
            } elseif (!$good_items && $incompatible_items) {
                $ret['msg']=sprintf(MwLang::get('backend.texts.pasteC4Pfail'), $incompatible_items, $incompatibleTypeStr);
                $ret['status']='error';
            } elseif ($good_items && $incompatible_items) {
                $ret['msg']=sprintf(MwLang::get('backend.texts.pasteC4Pfailsuccess'), $good_items, $incompatible_items, $incompatibleTypeStr);
                $ret['status']='warning';
            }
        } else {
            $ret['msg']="paste failed, the clipboard is empty.";
            $ret['status']='warning';
        }



        header('content-type: application/json; charset=utf-8');
      
        echo json_encode($ret);
        exit();
    }

    public function ng_customaction($params=array())
    {
        $action=$params['action'];
        if ($this->hasMethod($action)) {
            $ret=call_user_func(array($this,$action), $params['args']);
        } else {
            $ret=array('status'=>'err','msg'=>'action '.$action.' not found');
        }
        header('content-type: application/json; charset=utf-8');
        echo json_encode($ret);
        die();
    }

    public function ng_savesortelements($params)
    {
        $rec=array('status'=>'error');

        $existing_serialized_data=MwSiteTree::getFieldForSure($this->Mainrecord, $this->Fieldname);

        if ($existing_serialized_data) {
            $topLevelData=self::myUnserialize($existing_serialized_data, $this->Fieldname);
        } else {
            $topLevelData=array();
        }

        if ($this->ParentPath) {
            $oldData=&self::arrayGetByPath($topLevelData, $this->ParentPath);
        } else {
            $oldData=&$topLevelData;
        }

        $newArray=array();

        //order level 1
        foreach ($params['items'] as $item) {
            $itemId=$item['id'];
            if ($itemId) {
                $newItem=$this->findItemById($itemId, $oldData);

                if ($newItem['config'] && $newItem['config']['_children'] && $newItem['config']['_children']['enable_treesort']) {
                    //only sort treesort-enabled items
                    unset($newItem['_children']);
                    //fetch children:
                    if (array_key_exists('_children', $item)) {
                        $newItem['_children']=array();
                        foreach ($item['_children'] as $childItem) {
                            $childItemId=$childItem['id'];
                            if ($childItemId) {
                                $newChildItem=$this->findItemById($childItemId, $oldData);
                                if ($childItemId) {
                                    $newItem['_children'][$childItemId]=$newChildItem;
                                }
                            }
                        }
                    }
                }

                if ($item) {
                    $newArray[$itemId]=$newItem;
                } else {
                    die('old item not found '.$item['id']);
                }
            }
        }



        $oldData=$newArray;


        $this->Mainrecord->setField($this->Fieldname, self::mySerialize($topLevelData, $this->Fieldname));
        $this->Mainrecord->write();

        $ret['preview_url']=$this->Mainrecord->Link();
        $ret['status']='ok';

        return json_encode($ret);
    }

    public function findItemById($itemId, &$array)
    {
        //simple case:level 0
        if (array_key_exists($itemId, $array)) {
            return $array[$itemId];
        }

        //look deeper
        foreach ($array as $item) {
            if ($item['_children']) {
                if (array_key_exists($itemId, $item['_children'])) {
                    return $item['_children'][$itemId];
                }
            }
        }
    }

  
    public function savesortelements()
    {
        if ($sortids=trim(array_get($_POST, 'sortIds'))) {
            $existing_serialized_data=MwSiteTree::getFieldForSure($this->Mainrecord, $this->Fieldname);
        
        
            if ($existing_serialized_data) {
                $data=self::myUnserialize($existing_serialized_data, $this->Fieldname);
                $result_data=array();
                foreach (explode(' ', $sortids) as $cssId) {
                    if (preg_match("#([^-]+)-(\d+)-(\d+)#", $cssId, $m)) {
                        $id=$m[3];
                        $result_data[$id]=$data[$id];
                    }
                }
                if (1 || sizeof($data)==sizeof($result_data)) {
                    $this->Mainrecord->setField($this->Fieldname, self::mySerialize($result_data, $this->Fieldname));
                    $this->Mainrecord->write();
                } else {
                    die('error occured while sorting (nonequal list-sizes)');
                }
            }
        }
      
        $url=$this->Mainrecord->Link();

        $html=<<<HTML
        <script type="text/javascript" charset="utf-8">
        item=parent.jQuery('#{$this->CssID}');
        item.closest('.CElementList').CElement('list' );
        window.location='$url?preview='+new Date().getTime();
        </script>
HTML;
        return $html;
    }
    
    public static function getIdFromCssId($CssId)
    {
        if (preg_match("#([^-]+)-([^-]+)-(-?\d+)#", $CssId, $m)) {
            return $m[3];
        } else {
            return $CssId;
        }
    }

    public function prepareFdata($fdata)
    {
        if ($fdata['HideOn']) {
            $fdata['HideOn']=Datum::get_unixtime($fdata['HideOn']);
        }
        if ($fdata['PublishOn']) {
            $fdata['PublishOn']=Datum::get_unixtime($fdata['PublishOn']);
        }
        if ($fdata['ArchiveOn']) {
            $fdata['ArchiveOn']=Datum::get_unixtime($fdata['ArchiveOn']);
        }
    
        return $fdata;
    }

    public function ng_save($params=array())
    {
        if (!$fdata) {
            $fdata=array_get($_POST, 'fdata');
        }


        $oldID=$this->ID;

        if ($fdata && $fdata['record_as_json']) {
            $record=json_decode($fdata['record_as_json'], 1);
            if (is_Array($record)) {
                $this->record=$record;
                $this->write($params);
            } else {
                die('invalid JSON');
            }
        } elseif ($fdata) {
            foreach ($fdata as $key => $value) {
                if (is_Array($fdata[$key])) {
                    foreach ($fdata[$key] as $key2=>$val2) {
                        if (!$fdata[$key][$key2] || $fdata[$key][$key2]=="-1") {
                            unset($fdata[$key][$key2]);
                        }
                    }  //remove empties
                    $fdata[$key]=implode(',', $fdata[$key]);
                }
            }
        
            $this->update($fdata);
            $this->write($params);
        }
    
        $url=$this->getToprecord()->Link();

        $nextaction=$params['nextaction'];


        if ($oldID!=$this->ID) {
            // id was renamed ?
            $addonScripts.="scope.renameItemId('$oldID','{$this->ID}');\n";
        }
    
        $html.=<<<HTML
    
    <script type="text/javascript" charset="utf-8">
    
      var scope = parent.angular.element('#c4p').scope();

      $addonScripts

      scope.reloadItemById('{$this->ID}','$nextaction');
      scope.reloadPreview('$url?preview='+new Date().getTime());

    </script>
HTML;

        return $html;
    }

    public function save($fdata=null)
    {
        if (!$fdata) {
            $fdata=array_get($_POST, 'fdata');
        }
    
        if ($fdata) {
            foreach ($fdata as $key => $value) {
                if (is_Array($fdata[$key])) {
                    foreach ($fdata[$key] as $key2=>$val2) {
                        if (!$fdata[$key][$key2] || $fdata[$key][$key2]=="-1") {
                            unset($fdata[$key][$key2]);
                        }
                    }  //remove empties
                    $fdata[$key]=implode(',', $fdata[$key]);
                }
            }
        
            $this->update($fdata);
            $this->write();
        }
    
        $url=$this->getToprecord()->Link();
    
        if (array_get($_POST, 'nextaction')) {
            $nextaction = array_get($_POST, 'nextaction');
        } else {
            $nextaction = 'show';
        }
    
    
        if (array_get($_POST, 'nextaction_jsonargs')) {
            $nextaction_argstr = array_get($_POST, 'nextaction_jsonargs');
        } else {
            $nextaction_argstr = '""';
        }
    
    
        $cssid=$this->CssID;
        if (array_get($_REQUEST, 'cssid')) {
            $cssid=array_get($_REQUEST, 'cssid');
        }
        $html=<<<HTML
    
    <script type="text/javascript" charset="utf-8">
    
    item=parent.jQuery('#{$cssid}');

    var args=$nextaction_argstr;
    
    item.closest('.CElementList').CElement('$nextaction', item, args );

     window.location='$url?preview='+new Date().getTime();
    </script>
HTML;
        return $html;
    }
  
    public static function addCElementToObject($dataAsArray, $object, $fieldname, $params=array())
    {
        if (strstr($dataAsArray['CType'], 'C4P')) {
            $classname=$dataAsArray['CType'];
        } else {
            $classname='CElement_'.$dataAsArray['CType'];
        }

        if (class_exists($classname)) {
            list($usec, $sec) = explode(" ", microtime());
            $celement_id = str_replace("0.", "", "$sec$usec");
      
            $celement = new $classname($object, $fieldname, $celement_id, $dataAsArray);
            return $celement->write($params);

            // $existing_serialized_data=MwSiteTree::getFieldForSure($object,$fieldname);

      // if($existing_serialized_data)
      //   $data=self::myUnserialize($existing_serialized_data,$fieldname);
      // else
      //   $data=Array();

      // $data[$celement->ID]=MwUtils::array_merge_recursive_distinct ( $data[$celement->ID], $dataAsArray );


      // $object->setField($fieldname,self::mySerialize($data,$fieldname));
        }
    }
  
    public function _t($arg1, $arg2, $arg3=null)
    {
        return _t($arg1, $arg2, $arg3);
    }
  

    public function NiceDateTime($fieldname)
    {
        $val=$this->record[$fieldname];
        if ($val) {
            $d=new Datum($val);
            $datestr=$d->FormattedDate('Y-m-d H:i');
            $datestr=trim(str_replace('00:00', '', $datestr));
            return $datestr;
        }
    }

    public function Datum($fieldname)
    {
        return new Datum($this->record[$fieldname]);
    }

  
    public function editButtons()
    {
        if (!$this->editMode) {
            $html=<<<HTML
      <div class='CType'>{$this->_t('MwCElement.Type', 'Typ')}:{$this->CTypeLabel}</div>
               <a href='#' class='button celementedit' onClick='return CElement_edit(this)'><span class='tinyicon ui-icon-pencil'></span>edit</a><br/>
               <a href='#' class='button button-secondary celementduplicate' onClick='return CElement_duplicate(this)'><span class='tinyicon ui-icon-copy'></span>duplicate</a><br/>
               
               <a href='#' class='button button-secondary celementremove' onClick='return CElement_remove(this)' title='delete'><span class='tinyicon ui-icon-trash'></span></a>
               
               <a href='#' class='button button-secondary celementhide_unhide' onClick='return CElement_hide_unhide(this)' title='hide/unhide'><span class='tinyicon ui-icon-cancel'></span></a>
               
HTML;
        } else {
            $html=<<<HTML
      <div style='position:relative' class='space'>
        <div class='group space'>

            {$this->_t('MwCElement.Type', 'Typ')}:&nbsp;
            <div class="btn-group MwCheckboxDropdown celement-ctypeswitcher pull-right">
                <button class="btn dropdown-toggle btn-mini corner-all" data-toggle="dropdown"></button>
                <input type='hidden' name='fdata[CType]' value='{$this->CType}' id='CType_field' class='celement-ctypefield'>
                <ul class="dropdown-menu"></ul>
            </div>
            
        </div>
      <a href='#' class='btn btn-small btn-primary celement_submit' ><i class='icon-ok'></i> OK</a>
      <div>&nbsp;</div>
      <a href='#' class='btn btn-mini celement_cancel' ><i class='icon-remove'></i> cancel</a>



HTML;
        }

        if ($this->editMode) {
            $editclass=' editMode';
        }
        return "<div class='editbuttons $editclass' >$html</div>";
    }


  
    public function EditField($name)
    {
        if (!$this->MwForm) {
            $this->MwForm=new MwForm;
            $preset=array('Fehlstaende'=>array($this->record['ID']=>$this->record));
            $this->MwForm->preset($preset);
            $this->MwForm->set_fieldname_prefix('[Fehlstaende]['.$this->record[ID].']');
        }
    
    
        switch ($name) {
      case 'artikel':
      $products=DataObject::get('Product');
      
      $p=array();
      $p['type']='select';
      $p['fieldname']='artikel';
      $p['options']=$products->map('ID', 'DropdownTitle')->toArray();
      $ret=$this->MwForm->render_naked_field($p);
      break;
      
      case 'anzahl':
      $p=array();
      $p['fieldname']='anzahl';
      $ret=$this->MwForm->render_naked_field($p);
      break;
      
    }
    
        return $ret;
    }
    

    public function getParentElement()
    {
        if (preg_match('#^(.*)/_children(_[a-z0-9]+)?$#', $this->ParentPath, $m)) {
            $cleanedParentPath=$this->Fieldname."/".$m[1];
            return $this->getCElement($this->Mainrecord, $cleanedParentPath);
        }
    }
}

class CElement_Absatz extends CElement
{
    public function init()
    {
        $this->CType='Absatz';
    }
}



class CElement_Download extends CElement
{
    public function init()
    {
        $this->CType='Download';
    }
}


class CElement_HTML extends CElement
{
    public function init()
    {
        $this->CType='HTML';
    }

    public function getTextAsHtml()
    {
        return htmlspecialchars($this->Text);
    }
}




class CElement_Picture extends CElement
{
    public function init()
    {
        $this->CType='Picture';
    }
  
    public function PictureWidth()
    {
        if (!$this->record['PictureWidth']) {
            $w=200;
        } else {
            $w=$this->record['PictureWidth'];
        }
      
        return $w;
    }
    
    public function NicePictureCopyright()
    {
        $c=$this->getField('PictureCopyright');
        if ($c) {
            return "© $c";
        }
    }
  
    public function getSizedPicture()
    {
        if ($picture=$this->Picture) {
            return $picture->Image()->getFormattedImage('SetWidth', $this->PictureWidth());
        }
    }
  
    public function getFrontendHTML()
    {
        if ($picture=$this->Picture) {
            $img = $picture->Image()->getFormattedImage('CroppedImage', 100, 100);
            if ($img) {
                return "
         <div class='group'>
           <div style='float:left;width:120px'>{$img->forTemplate()}</div>
           <div>
             <div><b>{$this->PictureText}</b></div>
             <div>{$this->NicePictureCopyright()}</div>
           </div>
         </div>
         
         
         ";
            }
        }
    }
}
