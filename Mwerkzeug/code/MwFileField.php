<?php

use Mwerkzeug\MwRequirements;
use SilverStripe\View\Requirements;
use SilverStripe\i18n\i18n;
use SilverStripe\View\ViewableData;

class MwFileField extends ViewableData
{
    
    static $preset;
    static $presetObject;
  
    public static function includeRequirements()
    {
        
        Requirements::javascript("Mwerkzeug/javascript/jquery.ui.subclass.js");
        Requirements::javascript('Mwerkzeug/javascript/MwFileField_jqueryui_widget.js');
        MwRequirements::javascript('mysite/javascript/MwFileField_jqueryui_widget.js'); //try to load custom class
        
        if ($locale=i18n::get_locale()) {
            $lang=substr($locale, 0, 2);
            Requirements::javascript("Mwerkzeug/javascript/MwFileField_jqueryui_widget-{$lang}.js");
            MwRequirements::javascript("mysite/javascript/MwFileField_jqueryui_widget-{$lang}.js"); //try to load custom translations
        }
        
        Requirements::css('Mwerkzeug/css/MwFileField.css');
        MwRequirements::css('mysite/css/MwFileField.css'); //try to load custom css
    }
    
    public function getNakedField($p)
    {
        
        if (!$p['options']) {
            $p['options']=array();
        }
        
        MwFileField::includeRequirements();
        
        if (preg_match('#^(.+)ID$#', $p['fieldname'], $m)) {
            $val=$p['default_value'];
            $basename=preg_replace('#[^0-9a-z]#i', '', $m[1]);
            
            $fieldname="fdata[{$p['fieldname']}]";
            
            {
                //taken from MwForm
                
            if (MwForm::$presetObject && !$p['presetObject']) {
                $p['presetObject']=self::$presetObject;
            }
                
            if (is_object($p['presetObject']) && $p['fieldname']) {
                if ($p['presetObject']->hasMethod('getFieldForEdit')) {
                    $defval=$p['presetObject']->getFieldForEdit($p['fieldname']);
                } else {
                    $defval=$p['presetObject']->{$p['fieldname']};
                }
                    
                // $defval=call_user_func($p['presetObject',"BackendStep_init"));
                    
                // $cmd='$defval=$p[\'preset\']['.$p['fieldname'].'];';
                // eval($cmd);
            } else {
                if (!$p['preset']) {
                    $p['preset']=self::$preset;
                }
                if (is_array($p['preset']) && $p['fieldname']) {
                    $fixed_fieldname=str_replace(']', "']", $p['fieldname']);
                    $fixed_fieldname=str_replace('[', "['", $fixed_fieldname);
                    $cmd='$defval=$p[\'preset\'][\''.$fixed_fieldname.'\'];';
                    // die("\n\n<pre>mwuits-debug 2019-03-14_00:19 ".print_r($cmd,1));
                    eval($cmd);
                }
            }
                
            if ($p['default_value']) {
                $defval=$p['default_value'];
            }
                
            if (isset($p['default']) && (!$defval && $defval!=='0' && $defval!==0)) {
                $defval=$p['default'];
            }
            }
            
            $uniqueID="{$basename}_hiddenfield";
            $GLOBALS['MwFileField']['uidcounter'][$uniqueID]++;
            $uniqueID.="_".$GLOBALS['MwFileField']['uidcounter'][$uniqueID];
            
            
            $base_options=MwFile::conf('options');
            if ($base_options) {
                $p['options']=MwUtils::array_merge_recursive_distinct($base_options, $p['options']);
            }
            
            
            
            if ($p['options']) {
                $options_json=json_encode($p['options']);
            }
            $html="<input type='hidden' name='$fieldname' class='MwFileField' value='$defval' id='$uniqueID'>
            <script>
            var revealIdField;
            $(document).ready(function() {
                
                revealIdField=function(){
                    $('#$uniqueID').prop('type','text');
                };
                
                $('#$uniqueID').prev('label').on('dblclick',function(){
                    revealIdField();
                });
                
                $('#{$uniqueID}').MwFileField($options_json); // jshint ignore:line
                
            });
            </script>
            ";
            
            return $html;
        }
        
        return 'no way';
    }
}
