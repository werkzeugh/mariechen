<?php

use SilverStripe\View\Requirements;
use SilverStripe\i18n\i18n;
use SilverStripe\View\ViewableData;

class IVGField extends ViewableData
{

  static public function includeRequirements()
  {

    Requirements::javascript("Mwerkzeug/javascript/jquery.ui.subclass.js");
    Requirements::javascript('Mwerkzeug/javascript/IVGField_jqueryui_widget.js');
    Requirements::javascript('mysite/javascript/IVGField_jqueryui_widget.js'); //try to load custom class

    if($locale=i18n::get_locale())
    {
      $lang=substr($locale,0,2);
      Requirements::javascript("Mwerkzeug/javascript/IVGField_jqueryui_widget-{$lang}.js");
      Requirements::javascript("mysite/javascript/IVGField_jqueryui_widget-{$lang}.js"); //try to load custom translations
    }

    Requirements::css('Mwerkzeug/css/IVGField.css');
    Requirements::css('mysite/css/IVGField.css'); //try to load custom css
  }
 
 public function getNakedField($_p)
 {

   IVGField::includeRequirements();

   if (preg_match('#^(.+)IVG$#',$_p['fieldname'],$m))
   {
     $val=$_p['default_value'];
     $basename=preg_replace('#[^0-9a-z]#i','',$m[1]);
     
     $fieldname="fdata[{$_p['fieldname']}]";

     {
       //taken from MwForm

       
     if (is_object($_p['presetObject']) && $_p['fieldname'])
     {
      $defval=$_p['presetObject']->$_p['fieldname'];
     }
     else
     {
       if(!$_p['preset'])
         $_p['preset']=MwForm::$preset;

       if (is_array($_p['preset']) && $_p['fieldname'])
       {
         $cmd='$defval=$_p[\'preset\']['.$_p['fieldname'].'];';
         eval($cmd);
       }
     }
       if ($_p['default_value'])
         $defval=$_p['default_value'];
     }

     $uniqueID="{$basename}_hiddenfield";
     $GLOBALS['IVGField']['uidcounter'][$uniqueID]++;
     $uniqueID.="_".$GLOBALS['IVGField']['uidcounter'][$uniqueID];
     

    

     $options_json=Array();
     //get default options from IVG-class
     if(class_exists($_p['IVGclass']))
      {
       $IVGobj=new $_p['IVGclass'];
       $options=$IVGobj->getOptionsForIVGField();

       if(is_array($options))
         $options_json=$options;
      }  
      
      
     if($_p['options'] && is_array($_p['options']))
     {
      $options_json=MwUtils::array_merge_recursive_distinct ( $options_json,  $_p['options'] );
     }   

     if($options_json)
       $options_json=json_encode($options_json);

     $html="<input type='hidden' name='$fieldname' class='IVGField' value='$defval' id='$uniqueID'>
            <script>
                 $(document).ready(function() {
                   $('#{$uniqueID}').IVGField($options_json);
                 });
            </script>
     ";

     // $html.= "<li>IVGclass:{$_p['IVGclass']}";

     return $html;
   }

   return 'no way';

 }
  
}


?>