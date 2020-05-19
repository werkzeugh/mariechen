<?php

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataObject;
//use PageController;
use SilverStripe\Control\Session;

class GenericHolderPage extends Article
{
  
    var $subclass='ToBeOverridden';
    var $cache;
  
    public function allowedChildren()
    {
        return array($this->subclass);
    }
  
    function Items()
    {
        if (!$this->cache[__FUNCTION__]) {
            $this->cache[__FUNCTION__]=DataObject::get('SiteTree', "ClassName in ('".implode("'", $this->allowedChildren())."') and ParentID={$this->ID}", "Created desc");
        }


        return $this->cache[__FUNCTION__];
    }
}


class GenericHolderPageController extends ArticleController
{
    
}


class GenericHolderPageBEController extends ArticleBEController
{
  
    var $texts=array("additem" => "add Item");

      private static $allowed_actions = [
        'ajaxCElementList',
        'ajaxCElement',
      ];

    public function getRawTabItems()
    {
         $items=parent::getRawTabItems();
         $items['15']='Shop-Artikel';
     
        return $items;
    }
  
    public function getText($key)
    {
        return $this->texts[$key];
    }
  
    public function addItem($item)
    {

        $pageData['Title']='new '.$this->record->subclass.' (created '.Date('m/d/Y H:i').')';
        $pageData['ClassName']=$this->record->subclass;
    
        $newpage=$this->createPageUnder($this->record->ID, $pageData);
        if ($newpage->hasMethod('TitleForNewRecords')) {
            $newpage->Title=$newpage->TitleForNewRecords();
            $newpage->write();
        }
    
        if ($newpage->ID) {
            Controller::curr()->redirect("/BE/Pages/edit/".$newpage->ID);
        } else {
            die("ERROR: Page {$this->record->subclass} cannot be created");
        }
    }
  
  
    public function step_15()
    {

        EHP::includeRequirements();


        if ($_GET['addItem']) {
            $this->addItem($_GET['addItem']);
        }

        $tplnames[]='Includes/'.get_class($this)."_list";
        $tplnames[]="Includes/GenericHolderPage_BEController_list";
    
    
        return $this->renderWith($tplnames);
      //list items with an appropriate template
    }

  // include ehp stuff ---------- BEGIN

    public function getEHP()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=new EHP($this);
        }
        return $this->cache[__FUNCTION__];
    }


    public function EHP_Items($options = null)
    {
        return $this->record->Items();
    }
    
    
    function step_ehp()
    {
        echo $this->EHP->dispatch();
        exit();
    }


    public function EHP_roweditHTML($record, $params)
    {

        return "
      <td>
       not available 
          <!-- <input type='text' name='fdata[Title]' value='{$record->Title}'> -->
      </td>
      ";
    }
    
    public function EHP_Columns()
    {
        return explode(',', 'Title,Info');
    }

    public function EHP_getRecordClass()
    {
        return $this->record->subclass;
    }

    public function EHP_rowButtons()
    {
        return implode("\n", array($this->EHP->defaultButton('hide_unhide'), $this->EHP->defaultButton('delete')));
    }


    public function EHP_rowTpl()
    {
        return '
        <td><b><a href="/BE/Pages/edit/$ID">$cmsTitle</a></b></td>
        <td>$cmsShortText</td>
      ';
    }

  // include ehp stuff ---------- END
}
