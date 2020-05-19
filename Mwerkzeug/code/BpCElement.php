<?php

use SilverStripe\Control\Controller;

class BpCElementController extends BackendPageController
{
  var $record;
  var $cache;
  var $c4p_mode=0;

    private static $allowed_actions = [
        'ajaxCElementList',
        'ajaxCElement',
    ];

  public function getSettings()
  {

    if(!isset($this->cache[__FUNCTION__]))
    {
      if(is_array(array_get($_REQUEST,'settings')))
        $this->cache[__FUNCTION__]=array_get($_REQUEST,'settings');
      elseif(array_get($_REQUEST,'settings_json'))
        $this->cache[__FUNCTION__]=json_decode(array_get($_REQUEST,'settings_json'),true);
      else
        $this->cache[__FUNCTION__]=Array();
    }
    return $this->cache[__FUNCTION__];

  }

  public function loadRecord($id=0)
  {
    if(strstr($this->Settings['c4p_record'],'mwlink'))
    {
      $this->record=MwLink::getObjectForMwLink($this->Settings['c4p_record']);
    }
    return is_object($this->record);
  }

  public function ajaxCElementList()
  {

      
    if($this->loadRecord())
    {

      if($this->record)
      {     
        return $this->getAjaxCElementList($this->record,$this->Settings);
      }     
    }
  }
  
   public function ajaxCElement() // analog zu CElement::dispatch($Controller)
   {
    //c4p enabled version of ajaxCElement
    

    
    $action=Controller::curr()->urlParams['OtherID'];
    preg_match("#([^-]+)-([^-]+)-(-?\d+)#",Controller::curr()->urlParams['ID'],$m);
    $celement_id=$m[3];
    

    $record=MwLink::getObjectForMwLink($this->Settings['c4p_record']);
    if(!$record)
      die('record cannot be loaded');
      
    $place=$this->Settings['c4p_place'];
    $fieldname=$record->C4P->getFieldnameForPlace($place);


    // load child-celement if needed ---------- BEGIN
    $child_id=array_get($_POST,'args.child_id');
    if($child_id)
    {
        $child_ids=explode('/',"$child_id");
      
        $celement_id= array_shift($child_ids);
        $celement=CElement::getCElement($record,$fieldname,$celement_id);
        if($celement)
        {
            $celement=$celement->getChildByID(implode('/',$child_ids));
        }
        
        if(!$celement)
            die("cannot load child ".$child_id);
        
    }
    else
        $celement=CElement::getCElement($record,$fieldname,$celement_id);
            
    // load child-celement if needed ---------- END

    
        
    
    if(!$celement)
    {
            
      $defaultType=$record->C4P->getDefaultTypeForPlace($place);
      if(!$defaultType)
        die("defaulttype cannot be determined for place:$place");
  
      if(class_exists($defaultType))
        $celement=new $defaultType($record,$fieldname,$celement_id);
      else
        die("class $defaultType is not defined");
      
      if(array_get($_REQUEST,'duplicateOf'))
      {
              
          // ------------------- duplicate C4P BEGIN --------------------
          preg_match("#([^-]+)-([^-]+)-(-?\d+)#",array_get($_REQUEST,'duplicateOf'),$m);
          $old_celement_id=$m[3];
          $celement2copy=CElement::getCElement($record,$fieldname,$old_celement_id);
          
          if($celement2copy)
          {
            $defaultType=get_class($celement2copy);
            $celement=new $defaultType($record,$fieldname,$celement_id);
            $celement->record=$celement2copy->record;
            $celement->write(Array('insertAfter'=>$old_celement_id));
            
         }
        // ------------------- duplicate C4P END ￼--------------------
        
      }
      
      
    }

    $args=Array(); //no arguments so far (url completely parsed)
    echo call_user_func_array(Array($celement,$action),$args);
    exit();
  }

  static public function getAjaxCElementList($Mainrecord,$Settings=NULL)
  {
    //moved to BackendController

    $c=Array();

    $Fieldname=Controller::curr()->urlParams['OtherID'];

    // c4p mode ---------- BEGIN

    if(!$Fieldname && $placename=$Settings['c4p_place'])
    {
      $Fieldname=$Mainrecord->C4P->getFieldnameForPlace($placename);
      //echo "<li>try to get fieldname from Mainrec($placename) =".$Fieldname;
    }
    // c4p mode ---------- END

    $c['Items']=CElement::getCElementsForField($Mainrecord,$Fieldname,Array('include_hidden'=>1));

    $c['Fieldname']=$Fieldname;
    if(array_get($_GET,'sortmode'))
    {
      $c['SortMode']=1;
      if(class_exists('SwCElement')) //ugly, i know, but for Smartworks only
      {
        $c['SortTreeHTML']=SwCElement::getSortTreeHTML($Mainrecord,$Fieldname);
      }
    }

    return Controller::curr()->customise($c)->renderWith('Includes/CElement_list');
  }


}
