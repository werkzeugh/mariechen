<?php

use Mwerkzeug\MwRequirements;
use SilverStripe\View\Requirements;
use SilverStripe\i18n\i18n;
use SilverStripe\View\ViewableData;

class MwLinkField extends ViewableData
{

  static public function includeRequirements()
  {

    MwRequirements::javascript("Mwerkzeug/javascript/jquery.ui.subclass.js");
    MwRequirements::javascript('Mwerkzeug/javascript/MwLinkField_jqueryui_widget.js');
    MwRequirements::javascript('mysite/javascript/MwLinkField_jqueryui_widget.js'); //try to load custom class

    if($locale=i18n::get_locale())
    {
      $lang=substr($locale,0,2);
      MwRequirements::javascript("Mwerkzeug/javascript/MwLinkField_jqueryui_widget-{$lang}.js");
      MwRequirements::javascript("mysite/javascript/MwLinkField_jqueryui_widget-{$lang}.js"); //try to load custom translations
    }

    MwRequirements::css('Mwerkzeug/css/MwLinkField.css');
    MwRequirements::css('mysite/css/MwLinkField.css'); //try to load custom css
  }
 
 public function getNakedField($_p)
 {

   MwLinkField::includeRequirements();

   if (preg_match('#^(.+)ID$#',$_p['fieldname'],$m))
   {
     $val=$_p['default_value'];
     $basename=preg_replace('#[^0-9a-z]#i','',$m[1]);
     
     $fieldname="fdata[{$_p['fieldname']}]";

     {
       //taken from MwForm
       if(!$_p['preset'])
         $_p['preset']=MwForm::$preset;

       if (is_array($_p['preset']) && $_p['fieldname'])
       {
         $cmd='$defval=$_p[\'preset\']['.$_p['fieldname'].'];';
         eval($cmd);
       }

       if ($_p['default_value'])
         $defval=$_p['default_value'];
     }

     $uniqueID="{$basename}_hiddenfield";
     $GLOBALS['MwLinkField']['uidcounter'][$uniqueID]++;
     $uniqueID.="_".$GLOBALS['MwLinkField']['uidcounter'][$uniqueID];
     
     if($_p['options'])
     {
       $options_json=json_encode($_p['options']);
     }
     $html="<input type='hidden' name='$fieldname' class='MwLinkField' value='$defval' id='$uniqueID'>
            <script>
                 $(document).ready(function() {
                   $('#{$uniqueID}').MwLinkField($options_json);
                 });
            </script>
     ";

     return $html;
   }

   return 'no way';

 }
  
}


?>