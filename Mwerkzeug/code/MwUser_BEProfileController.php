<?php

use SilverStripe\Security\Member;
use SilverStripe\Control\Email\Email;
    
class MwUser_BEProfileController extends  BackendPageController
{



    var $SkinVersion=2;

    // include formhelper (FormHelper) stuff ---------- BEGIN

      public function getFormHelper() //FormHelper
      {
        if(!isset($this->cache[__FUNCTION__]))
        {
          $this->cache[__FUNCTION__]=new FormHelper($this);
        }
        return $this->cache[__FUNCTION__];
      }
    
    
    
    function FormHelper_config()
    {
        $c['rendertype']="bootstrap3";
    
        return $c;
    }

    public function FormHelper_getFormData()
    {
        return Member::currentUser();
    }

    function FormHelper_setFields()
    {
    
       

        if ($this->isAdmin()) {
          $p=array();
          $p['fieldname']     = 'Email';
          $p['label']         = "e-Mail";
          $p['validation']    = "required";
          $fields[$p['fieldname']]=$p;
        }

        $p=array();
        $p['fieldname']     = "FirstName";
        $p['label']         = "Vorname";
        $p['validation']    = "required";
        $fields[$p['fieldname']]=$p;

        $p=array();
        $p['fieldname']     = "Surname";
        $p['label']         = "Nachname";
        $p['validation']    = "required";
        $fields[$p['fieldname']]=$p;


        $p=array();
        $p['fieldname']     = "Config_useNewSkin";
        $p['label']         = "use new Skin <i><span class='fa fa-rocket fa-2x'></span> experimental !</i>";
        $p['type']    = "checkbox";
        $fields[$p['fieldname']]=$p;


        $p=array();
        $p['fieldname']     = "Submit";
        $p['type']          = "submit";
        $p['label']         = "OK";
        $fields[$p['fieldname']]=$p;



        return $fields;
    
    }
    

    // include formhelper (FormHelper) stuff ---------- END
    

  public function index(SilverStripe\Control\HTTPRequest $request)
  {

    // edit current profile

    $this->FormHelper->init();
    $this->summitSetTemplateFile("Layout","MwUser_BEProfileController_index");

    $c=Array();
    return $c;
      
  }

}
