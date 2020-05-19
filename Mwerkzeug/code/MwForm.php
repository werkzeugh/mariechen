<?php

use SilverStripe\Control\Controller;
use SilverStripe\i18n\i18n;
use SilverStripe\View\Requirements;
use SilverStripe\View\ViewableData;
/*********************************************************************
*  Manfred's Form engine
*  moved into a module
*  v 1.02  2011-02-03
**********************************************************************/


class MwForm
{
  // renders a field  the form


  static  $js_validation_rules=Array();
  static  $js_validation_messages=Array();
  static  $preset;
  static  $presetObject;
  static  $default_rendertype="standard";
  static  $fieldname_prefix;
  static  $lastDisplayValue="";
  static  $arrayBaseName='fdata';

  public static function set_default_rendertype($style)
  {

    //z.b. style css, od. standard, mailhtml od. bootstrap
   self::$default_rendertype=$style;

  }

  public static function set_array_basename($arrayBaseName)
  {
   self::$arrayBaseName=$arrayBaseName;
  }

  public static function set_fieldname_prefix($pref)
  {
    //z.b. style css, od. standard
   self::$fieldname_prefix=preg_replace('#^\[(.*)\]$#','\\1',$pref);

  }


  public static function resolve_overrides($array,$lang)
  {
  	 foreach ($array as $key=>$val)
  	 {
  	    if (isset($array[$key."_".$lang]))
  	    {
  	    	$array[$key]=$array[$key."_".$lang];
  	    	unset($array[$key."_".$lang]);
  	    }
   	 }
   	 return $array;
  }

  static public function preset($arr_or_obj)
  {
    if(is_object($arr_or_obj)) {
        self::$preset=$arr_or_obj->toMap();
    }
    else {
        self::$preset=$arr_or_obj;
    }


    self::$presetObject=NULL; 

    
  }


  static public function presetObject($obj)
  {
    self::$presetObject=$obj;
  }


  static function getRequiredMarker()
  {
          
      return "<span class='requiredmarker'>*</span>";
  }
  
  static public function render_field($p)
  {

    if(!$p['rendertype'])
        $p['rendertype']=self::$default_rendertype;

    $p['orig_fieldname']=$p['fieldname'];
    if (self::$fieldname_prefix)
      $p['fieldname']=self::$fieldname_prefix.']['.$p['fieldname'];

    if ($p['rendertype']=='bootstrap3' && $p['type']=="iagree") {
      $p['type']="checkbox";
    }


    if($p['rendertype']=='bootstrap3' && $p['type']!="checkbox" && $p['type']!="checkboxes" && $p['type']!="radio" ) {
      $p['addon_classes'].=" form-control";
    }
    //override from _EN-values
    if($GLOBALS['TSFE']->sys_language_uid==1)
        $p=self::resolve_overrides($p,"EN");
    $label=$p['label']?$p['label']:$p['fieldname'];
    if ($p['html'])
      $field=$p['html'];
    else
      $field=self::render_naked_field($p);

    

    if($GLOBALS['email'])
    {
      if (strstr($field,"<a"))
	    $field="";
    }

    if(isset($p['validation_marker']))
    {
      if($p['validation_marker'])
        $label.="*";
    }
    elseif (strstr($p['validation'].$p['addon_classes'],"required") && !$GLOBALS['email'] && $label )
    {
      $label.=self::getRequiredMarker();
    }

    if($p['helptipp'])
    {
      $helptipp=HelpTip::wrapInHtml($p['helptipp']);
    }
    else
    {
      if($p['helptipp_basekey'])
        $basekey=$p['helptipp_basekey'];
      else
        $basekey=Controller::curr()->myClass;

      $charkey=strtolower($basekey)."-".$p['fieldname'];
      if($p['helptipp_key_addon'])
        $charkey.=$p['helptipp_key_addon'];
      $helptipp=HelpTip::getHtml($charkey);
    }





    if ($GLOBALS['email'])
     $html=wordwrap($label, 60, "\n", false).":  $field \n\n";
    elseif($p['rendertype']=='naked')
      $html=$field;
    elseif($p['rendertype']=='css')
    {

      if($p['type']!="iagree" && $label && $p['label']!='none')
        $label="<label for='".self::$arrayBaseName."[{$p['fieldname']}]' >$label</label>";
      else
        $label="";
      
      if($p['note']) {
           $note="<div class=\"formitem-note\">{$p['note']}</div>";
      }
      
      $html="
          <span class='formitem formitem-{$p['orig_fieldname']}'>
            $label 
            $field
            $note
          </span>
          ";
    }
    elseif($p['rendertype']=='bootstrap')
    {

        if($p['note']) {
             $note="<p class=\"help-block\">{$p['note']}</p>";
        }

      
       if($p['type']=="iagree" || $p['type']=="checkbox")
       {
           //extract hiddenfield from field:

           if(preg_match('#^(.*)(<input type="hidden"[^>]+>)(.*)$#i',$field,$m))
            {
                $hiddenfield=$m[2];
                $field=$m[1].$m[3];
            }
           
           $html="
               <div class=\"control-group formitem-{$p['orig_fieldname']}\">
                   <label class=\"control-label\" for=\"input_{$p['fieldname']}\" >&nbsp;</label>
                   <div class=\"controls\">
                       $hiddenfield
                       <label class=\"checkbox\">$field $label</label>    
                       $note
                   </div> 
               </div>
               ";

           }
       elseif($p['type']=="hidden" )
       {
           $html=$field;
       }
       else
       {
           if($p['type']!="iagree" && $label && $p['label']!='none')
             $label="<label  class=\"control-label\" for='".self::$arrayBaseName."[{$p['fieldname']}]' >$label</label>";
           else
             $label="";

           $html="
               <div class=\"control-group formitem-{$p['orig_fieldname']}\">
                 $label
                 <div class=\"controls\">$field$note</div>
                 
               </div>
               ";
       }
      
    } elseif($p['rendertype']=='bootstrap3') {

        if($p['note']) {
             $note="<p class=\"help-block\">{$p['note']}</p>";
        }

      
       if($p['type']=="iagree" || $p['type']=="checkbox" )
       {
           //extract hiddenfield from field:

           if(preg_match('#^(.*)(<input type="hidden"[^>]+>)(.*)$#i',$field,$m))
            {
                $hiddenfield=$m[2];
                $field=$m[1].$m[3];
            }

           $html="
               <div class=\"checkbox formitem-{$p['orig_fieldname']}\">
                   <label for=\"input_{$p['fieldname']}\" >
                       $hiddenfield
                       $field
                       $label
                   </label>    
                   $note
               </div> 
               ";
           
           $html="
               <div class=\"form-group formitem-{$p['orig_fieldname']}\">
                 <span class='control-label'></span>
                 <div class=\"form-control-wrap\">$html</div>
               </div>
               ";
      } elseif($p['type']=="hidden" && !$label ) {
           $html=$field;
       } else {
           if($p['type']!="iagree" && $p['type']!="submit" && $label && $p['label']!='none') {
             $label="<label for='".self::$arrayBaseName."[{$p['fieldname']}]' class='control-label'>$label</label>";
           } else {
             $label="";
           }

           if($p['width']) {
            $fg_style_addons[]="width:".$p['width'];
           }

           if($fg_style_addons) {
              $fg_style_addon_tag='style="'.implode(';',$fg_style_addons).'"';            
           }

           if ($p['postfix'] || $p['prefix']) {
            if ($p['postfix']) {
              $postfix="<span class=\"input-group-addon\">{$p['postfix']}</span>";
            }
            if ($p['prefix']) {
              $prefix="<span class=\"input-group-addon\">{$p['prefix']}</span>";
            }
           }
           if($fg_style_addon_tag || $prefix || $postfix) {
             $field="<div class=\"input-group\"  $fg_style_addon_tag>$prefix$field$postfix</div>";
           }

           $field="<div class=\"form-control-wrap\">$field$note</div>";
           
           $html="
               <div class=\"form-group formitem-{$p['orig_fieldname']}\">
                 $label
                 $field
               </div>
               ";
       }
      
    } elseif($p['rendertype']=='beneath')
      $html="
      <tr class='tr_{$p['fieldname']}'>
        <td class='beneath' colspan='2'>
        <div class='label'>
          <label for='".self::$arrayBaseName."[{$p['fieldname']}]' >$label</label>
        </div>
          <div>
             $field {$p['note']}
          </div>
        </td>
        <td class='help'>
           $helptipp
         </td>
        </tr>
      </tr>";
    else
     $html="
     <tr class='tr_{$p['fieldname']}'>
       <td class='label'>
         <label for='".self::$arrayBaseName."[{$p['fieldname']}]' >$label</label>
       </td>
       <td class='val'>
            $field {$p['note']}
       </td>
       <td class='help'>
         $helptipp
       </td>
       </tr>
     </tr>";





    return $html;
  }

static public function getValidationRules()
{
  return implode(",\n",self::$js_validation_rules);
}

static public function getValidationMessages()
{
  return implode(",\n",self::$js_validation_messages);
}


static function addValidationRules($p)
{
  //if(strstr($p[validation],"required"))
  {
    if($p['type']=="checkboxes")
    {
      $fieldname_addon='[]';
      if($p['validation']=="required" || $p['validation']=='required:true')
        $valstr="required:true, minlength:1";
    }
    elseif($p['validation']=="required")
      $valstr="required:true";
    else
      $valstr=$p['validation'];

    $valstr=trim($valstr);
    if ($valstr) {
      self::$js_validation_rules[]="'".self::$arrayBaseName."[{$p['fieldname']}]$fieldname_addon': { $valstr }";
    }
    
    $msg=$p['validation_msg'];
    if(!$msg)
    {
      if(i18n::get_locale()=="de_DE")
        $msg="Bitte geben Sie einen Wert für das Feld \"".self::cleanLabel($p['label'])."\" ein.";
      else
        $msg="please enter a value for the field \"".self::cleanLabel($p['label'])."\".";
        
    }

    self::$js_validation_messages[]="'".self::$arrayBaseName."[{$p['fieldname']}]$fieldname_addon': { required:	'$msg' }";

  }

}

static function render_naked_field($p)
  {
    if (self::$fieldname_prefix && !$p['orig_fieldname'])
       {
         $p['orig_fieldname']=$p['fieldname'];
         $p['fieldname']=self::$fieldname_prefix.']['.$p['fieldname'];
       }
       
    //override from _EN-values
    if($GLOBALS['TSFE']->sys_language_uid==1)
        $p=self::resolve_overrides($p,"EN");

     if ($p['styles'])
        $styles=$p['styles'].";";

     if(self::$presetObject && !$p['presetObject']) {
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
       if(!$p['preset']){
           $p['preset']=self::$preset;
       }
       if (is_array($p['preset']) && $p['fieldname']) {
        $fixed_fieldname=str_replace(']',"']",$p['fieldname']);
        $fixed_fieldname=str_replace('[',"['",$fixed_fieldname);
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
     
     $fieldvalue=$defval;

     if(strstr($p['addon_classes'],"datepicker") || strstr($p['addon_classes'],"datetimepicker")) {
       Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.datepicker.js');
       if(i18n::get_locale()=="de_DE" || $p['date_locale']=="de_DE") {
         Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/i18n/jquery.ui.datepicker-de.js'); // set to     
       }
              
        if(strstr($p['addon_classes'],"datepicker") && $defval)
        {
          if(i18n::get_locale()=="de_DE" || $p['date_locale']=="de_DE")
            $defval=Datum::german_date('d.m.Y',$defval);
          if($defval=="01.01.1970" || $defval=="31.12.2037" ||
             $defval=="1970-01-01" || $defval=="2037-12-31")
            $defval="";
        }

        if(strstr($p['addon_classes'],"datetimepicker") && $defval)
        {
          if(i18n::get_locale()=="de_DE")
          $defval=Datum::german_date('d.m.Y H:i',$defval);
          if($defval=="01.01.1970 00:00" || $defval=="31.12.2037 00:00" ||
             $defval=="1970-01-01" || $defval=="2037-12-31")
            $defval="";
        }
         
     }

     if(strstr($p['addon_classes'],"MwLinkField" ) )
      {
        MwLink::includeRequirementsForMwLinkField();                  
        $jsconf=$p['jsconf'];

        $uniqueID="MwLinkField";
        $GLOBALS['MwLinkField']['uidcounter'][$uniqueID]++;
        $uniqueID.="_".$GLOBALS['MwLinkField']['uidcounter'][$uniqueID];

        $p['addon_classes'].=" $uniqueID";
        
        $p['after']="
          <script>
          jQuery(document).ready(function() {
            jQuery('.MwLinkField.$uniqueID').MwLinkField($jsconf); // jshint ignore:line
           });
          </script>
        ";
        
      }
    

     if($p['addon_classes']=="time" && $defval)
     {
       $defval=Datum::german_date('H:i',$defval);
     }


     $fieldtype="text";

     if ($p['options'] || $p['optiongroups'] || $p['text_options'])
     {
         $fieldtype="select";
         if(is_object($p['options'])) 
         {
             $p['options']=$p['options']->toArray();
         }
     }


		if($fieldtype=="text" && !$p['type']) {
		   $p['type']="text";
    }


    switch ($p['type'])
    {
      case "checkbox":
      case "html":
      case "checkboxes":
      case "radio":
      case "iagree":
      case "portalchooser":
      case "MwFileField":
      case "IVGField":
      case "upload":
      case "hidden":
      case "mapfield":
      case "select":
      case "submit":
	  		$fieldtype=$p['type'];
		  	break;
	    case "percent":
        if (!$p['after'])
	      $p['after']="%";
      case "date":
	        $defval=Date('d.m.Y',strtotime($defval));
          break;
      case "price":
          if (!$p['after'])
      	    $p['after']="&euro;";
	        if (!$p['validation']) $p['validation']="validate-currency-dollar";
            break;
      case "textarea":
      case "file":
      case "password":
      case "number":
        $fieldtype=$p['type'];
        break;
      case "text":
      default:
          break;
      case "spaltenfelder":
      case "intranet":
    	  $fieldtype=$p['type'];
        $defval=$p['value'];
        break;
      case "iagree":
        $fieldtype=$p['type'];
        break;
      }


     if ($GLOBALS['email'])
     {
     	  if (!is_array($defval))
          $field="$defval";

				if ($fieldtype=="radio" && $defval==1)
			            $field="x";

				if ($fieldtype=="select" && !$p['text_options'])
			        {
			            $field=$p['options'][$defval];
			        }

				if ($fieldtype=="checkbox" || $fieldtype=="iagree")
				  {
				    if ($defval==1)
   				    $field="[x]";
   				  else
   				    $field="[ ]";

			    }

				if ($fieldtype=="checkboxes")
				  {
			     if(is_array($defval))
			  	   foreach ($defval as $val)
			  	         if (trim($val))
			               $field.="\n - ".$p['options'][$val]."";
			          }
				    $fieldtype="";

			   	$styles.=";enabled:false";
			     }

		     if ($fieldtype=="intranet")
		     {
		        $field="$defval";
		     }


     if ($fieldtype=="spaltenfelder")
     {
       $p1=$p;
       $p1['fieldname'].="_b";
       $p1['before']="B:";
       $p1['type']="number";
       $p1['after']="x";
       $field=render_naked_field($p1);

       $p1=$p;
       $p1['fieldname'].="_h";
       $p1['before']="H:";
       $p1['type']="number";
       $p1['after']="in mm";
       $field.=render_naked_field($p1);
       $p['fieldname']="";
     }

     $addon_classes.=$p['addon_classes'];

     $tag_addon=$p['tag_addon'];

     if (is_array($p['jsdata'])) {
        $tag_addon.=" data-jsdata='".json_encode($p['jsdata'])."' ";
     }


     if ($p['placeholder']) {
        $tag_addon.=" placeholder=\"".str_replace('"','&quot;',$p['placeholder'])."\" ";
     }


     if ($fieldtype=="portalchooser")
     {
      $p['_defval']=$defval;
      $p['_styles']=$styles;
      $p['_addon_classes']=$addon_classes;
       $field=PortalChooserField::getNakedField($p);
     }

     if ($fieldtype=="MwFileField")
     {
        $p['_defval']=$defval;
        $p['_styles']=$styles;
        $p['_addon_classes']=$addon_classes;
        $field=MwFileField::getNakedField($p);
     }

     if ($fieldtype=="IVGField")
     {
        $p['_defval']=$defval;
        $p['_styles']=$styles;
        $p['_addon_classes']=$addon_classes;
        $field=IVGField::getNakedField($p);
     }

     // 
     // if ($fieldtype=="upload")
     // {
     //  $p['_defval']=$defval;
     //  $p['_styles']=$styles;
     //  $p['_addon_classes']=$addon_classes;
     //   $field=NaturfreundeUploadField::getNakedField($p);
     // }

     if ($fieldtype=="mapfield")
     {
      $p['_defval']=$defval;
      $p['_styles']=$styles;
      $p['_addon_classes']=$addon_classes;
       $field=MapField::getNakedField($p);


     }

     if ($fieldtype=="html")
     {
       $field=$p['html'];
     }




     if ($fieldtype=="text" || $fieldtype=="number") {

      $defval=str_replace('"',"&quot;",$defval);
      $field="<input type=$fieldtype name='".self::$arrayBaseName."[{$p['fieldname']}]' id='input_{$p['fieldname']}' value=\"$defval\"
                   style='$styles' class=\"$addon_classes\" $tag_addon>";
     }

     if ($fieldtype=="hidden")
      {
       $field="<input type=hidden name='".self::$arrayBaseName."[{$p['fieldname']}]' id='input_{$p['fieldname']}' value=\"$defval\"
                    style='$styles' class=\"$addon_classes\" $tag_addon >";
      }


      if ($fieldtype=="submit")
      {
        $iconclass=$p['iconclass'];
        if(!$iconclass) {
          $iconclass="fa fa-check";
        }
        $btnText=$p['label'];
        if(!$btnText) {
          $btnText="OK";
        }

       $field="<button type=\"submit\" id='input_{$p['fieldname']}' 
                  style='$styles' class=\"btn btn-primary\" $tag_addon ><i class='$iconclass'></i> $btnText</button>";
      }

     if ($fieldtype=="password")
     {
      $field="<input type=password name='".self::$arrayBaseName."[{$p['fieldname']}]' id='input_{$p['fieldname']}' value=\"$defval\"
                   style='$styles' class=\"$addon_classes\" $tag_addon >";
     }

     if ($fieldtype=="textarea")
     {
    
         $defval=preg_replace("#(</?)textarea#i","\\1textarea_REMOVEME",$defval);
      $field="<textarea type=text name='".self::$arrayBaseName."[{$p['fieldname']}]' id='input_{$p['fieldname']}'
                   style='$styles' class=\"$addon_classes\" $tag_addon >".$defval."</textarea>";
     }

     if ($fieldtype=="file")
     {
      if ($defval)
      {

        $file= $GLOBALS['conf']['upload_dir']."/".$defval;
        if (file_exists($file))
	      {
          $size=filesize($file);
				  $kb=ceil($size/1024);
			          $url= $GLOBALS['conf']['upload_url']."/".$defval;
				  $p['before']="<div style='border: 1px solid #eeeeee; padding:4px;margin:4px'><a href=$url target=_new >&raquo; $defval</a> ( $kb Kb)</div>";
			   }

        }


      $field="Upload: <input type=file name='".self::$arrayBaseName."[{$p['fieldname']}]' id='input_{$p['fieldname']}' value=\"$defval\"
                   style='$styles'> $addon";
     }


     if ($fieldtype=="checkbox") {
      $value=($p['value'])?$p['value']:1;

#   if($p['invert_value'])
#    $defval=($defval)?0:1;

      $checked=($defval==$value)?"checked":"";
      $field="";

      if(!$p['no_hidden_fallbackfield']){
        if ($value==1) {
          $nullValue='0'; 
        } else {
          $nullValue='';
        }
        $field.="<input type=\"hidden\" name='".self::$arrayBaseName."[{$p['fieldname']}]' id='input_{$p['fieldname']}_hidden' value=\"$nullValue\">";
      }

      $field.="<input type=\"checkbox\" name='".self::$arrayBaseName."[{$p['fieldname']}]'  id='input_{$p['fieldname']}' value=\"$value\" $checked  style='{$styles}' class=\"nowidth {$addon_classes}\" $tag_addon  {$p['add2tag']}>";
    }


     if ($fieldtype=="checkboxes")
     {
        $fieldvalue=Array();
       if ($p['text_options'])
         $p['options']=$p['text_options'];
              
       $defArr=Array();
       if(!is_array($defval))
      {
          foreach(explode(',',$defval) as $defkey)
            $defArr[]=$defkey;
      }
      else
        $defArr=$defval;

      $field="<input type=hidden name='".self::$arrayBaseName."[{$p['fieldname']}][0]' id='input_{$p['fieldname']}' value=\"\">";
      $fields="";
      if(is_array($p['options']))
      foreach($p['options'] as $value=>$label)
      {
        if ($p['text_options'])
          $value=strip_tags($label);

        $checked=(in_array($value,$defArr))?"checked":"";
        $id="input_".preg_replace('#[^a-zA-Z0-9]#','_',$p['fieldname']."_".$value);

        if($checked && trim($label))
            $fieldvalue[]=$label;

        if($p['rendertype']=='css')
        {
            $fields.="<span class=\"inlineblock\"><input type='checkbox' name='".self::$arrayBaseName."[{$p['fieldname']}][]' id=$id value=\"$value\" $checked {$p['add2tag']} class='nowidth $p[addon_classes] ' > $label</span>";
            
        }
        elseif($p['rendertype']=='bootstrap')
        {
            if(!$p['one_line_per_option'])
                $labelclass_addon='inline';
            else
                $labelclass_addon='';
            $fields.="<label class=\"checkbox $labelclass_addon\"><input type=\"checkbox\" name=\"".self::$arrayBaseName."[{$p['fieldname']}][]\" id=\"$id\" value=\"$value\" $checked {$p['add2tag']} class='$p[addon_classes]'  >$label</label>";
            
        }
        elseif($p['rendertype']=='bootstrap3')
        {
            if(!$p['one_line_per_option']) {
            $fields.="<div class='checkbox'><label><input type=\"checkbox\" name=\"".self::$arrayBaseName."[{$p['fieldname']}][]\" id=\"$id\" value=\"$value\" $checked {$p['add2tag']} calass='$p[addon_classes]'  >$label</label></div>";
          } else {
            $fields.="<label class=\"checkbox-inline\"><input type=\"checkbox\" name=\"".self::$arrayBaseName."[{$p['fieldname']}][]\" id=\"$id\" value=\"$value\" $checked {$p['add2tag']} calass='$p[addon_classes]'  >$label</label>";
          }            
        }
        else
        {
            $fields.="<tr class='field_{$p['fieldname']} input_{$p['fieldname']}{$value}'><td><input type='checkbox' name='".self::$arrayBaseName."[{$p['fieldname']}][]' id=$id value=\"$value\" $checked {$p['add2tag']} class='nowidth' ></td><td>
              <label for='$id' >$label</label></td></tr>";
        }
      
                    

          
      }

      if(strstr($fields,'</td></tr>'))
        $fields="<table border='0' cellspacing='0' cellpadding='0'>$fields</table>";

#       $field.="<input type='checkbox' class=checkbox name='".self::$arrayBaseName."[{$p['fieldname']}][]' id=$id value=\"$value\" $checked><label for='$id' class='checkbox'>$label</label>";

       $field.="<div style='margin:0px 0px 0px 0px' class='checkboxes'>$fields</div>";

     }

     if ($fieldtype=="radio")
     {
         
     
       if ($p['text_options'])
	       $p['options']=$p['text_options'];

       $field="";
       $fields="";

       $optnr = count($p['options']);
       foreach ($p['options'] as $key=>$val)
			 {
			   if ($p['text_options'])
			     $key=$val;
  		   $nr = $key;
		     $checked=($defval==$key)?"checked":"";

           if($p['rendertype']=='css') {
               $fields.="<span class='radio_option field_{$p['fieldname']} input_{$p['fieldname']}{$nr}'><input type=\"radio\"  name=\"".self::$arrayBaseName."[{$p['fieldname']}]\" id='input_{$p['fieldname']}$nr' value=\"$key\" $checked {$p['add2tag']} class='nowidth $p[addon_classes]'> <span class='radio_label' for='input_{$p['fieldname']}$nr'>$val</span> </span>";
           } elseif($p['rendertype']=='bootstrap') {
               $fields.="<label class=\"radio inline\"><input type=\"radio\" name=\"".self::$arrayBaseName."[{$p['fieldname']}]\" id='input_{$p['fieldname']}$nr' class='$p[addon_classes]' value=\"$key\" $checked {$p['add2tag']}><span>$val</span></label>";
           } elseif($p['rendertype']=='bootstrap3') {

            if(!$p['one_line_per_option']) {
              $fields.="<label class=\"radio-inline\"><input type=\"radio\" name=\"".self::$arrayBaseName."[{$p['fieldname']}]\" id='input_{$p['fieldname']}$nr' class='$p[addon_classes]' value=\"$key\" $checked {$p['add2tag']}><span>$val</span></label>";
            } else {
              $fields.="<label class=\"radio\"><input type=\"radio\" name=\"".self::$arrayBaseName."[{$p['fieldname']}]\" id='input_{$p['fieldname']}$nr' class='$p[addon_classes]' value=\"$key\" $checked {$p['add2tag']}><span>$val</span></label>";

           }


           }
           else
               $fields.="<div class='field_{$p['fieldname']} input_{$p['fieldname']}{$nr}'><input type=\"radio\"  name=\"".self::$arrayBaseName."[{$p['fieldname']}]\" id='input_{$p['fieldname']}$nr' value=\"$key\" $checked {$p['add2tag']} class='nowidth'> <label for='input_{$p['fieldname']}$nr'>$val</label> </div>";
       }

         $field.="<div style='margin:0px 0px 0px 0px;min-height: 27px;' class='radiobuttons'>$fields</div>";
     }
 
     if ($fieldtype=="iagree")
       {
       $checked=($defval)?"checked":"";

        $field = "<input type=checkbox class=\"$addon_classes\" name='".self::$arrayBaseName."[{$p['fieldname']}]' $checked id=input_{$p['fieldname']} value=\"\" {$p['add2tag']} onchange=\"if (this.checked == true) { this.value='1' } else { this.value='' };\" class='nowidth' $tag_addon>";
        if($p['rendertype']!='naked')
        $field.="<label for='input_{$p['fieldname']}'  style='font-size:80%;'>$p[label]</label><br>";
        
        $fieldvalue=($fieldvalue==1)?"x":"";
     }


     if ($fieldtype=="select")
     {

       if ($p['text_options'])
         $p['options']=$p['text_options'];

       if(!$p['no_empty_option'])
         $options="<option value=''>{$p['empty_option_text']}</option>";

       if($p['optiongroups'])
       {
         foreach( $p['optiongroups'] as $grouplabel=>$suboptions )
         {
           $options.="<optgroup label=\"$grouplabel\">";
           foreach ($suboptions as $key=>$val)
           {
             if ($p['text_options'])
               $key=$val;
             $checked=($defval==$key)?"selected='selected'":"";
             $options.="<option value=\"$key\" $checked >$val</option>";
           }
           $options.="</optgroup>";
         }
       }
       elseif(is_array($p['options'])) {
         foreach ($p['options'] as $key=>$val) {
          if ($p['text_options']) {
             $key=$val;
           }

           $style="";
           if($p['style_for_toplevels'] || $p['style_for_sublevels']) {
            if(preg_match('#^[^-].*$#',$val)) { 
//apply to all but items starting with "-" 
              $style=" style=\"{$p['style_for_toplevels']}\" ";
            } elseif($p['style_for_sublevels']) {
              $style=" style=\"{$p['style_for_sublevels']}\" ";
            }
          }

         $checked=($defval==$key)?"selected='selected'":"";
         if($checked) {
          $checkedAtLeastOnce=1;
        }

        if($p['options_css']) {
         $cssclass=' class="'.$p['options_css'][$key].'" ';
        }

        if($p['use_data_text']) {
         $options.="<option  $style value=\"$key\" $cssclass $checked data-text=\"$val\"></option>";
        } else {
         $options.="<option  $style value=\"$key\" $cssclass $checked >$val</option>";

        }
     }
   }
       
       //add default option to selectbox, if present, but not present in option-list
       if($defval && !$checkedAtLeastOnce && !$p['do_not_add_default_option'])
         $options.="<option  $style value=\"$defval\" selected='selected' >$defval</option>";
        

       $field="<select name='".self::$arrayBaseName."[{$p['fieldname']}]' id='input_{$p['fieldname']}' value=\"$defval\"
         class=\"$addon_classes\"  style=\"$styles\" $tag_addon {$p['add2tag']} >$options</select>";

     }
     
     


    if ($GLOBALS['email'] && !trim($field))
    {
         $field="";
         $p['after']="";$p['before']="";
    }

    if ($p['after'])
      $field.=" ".$p['after']." ";

    if ($p['before'])
      $field=$p['before']." ".$field;


    if ($GLOBALS['create_table_sql'])
    {
      if ($p['type']!="intranet" && $p['fieldname'] && !strstr( $p['fieldname'],']'))
      {

          $sql_fieldtype="varchar(255) NOT NULL default ''";
          if ($p['type']=="textarea")
             $sql_fieldtype="text NOT NULL default ''";
          if ($p['type']=="checkbox")
             $sql_fieldtype="int(1) NOT NULL DEFAULT 0";

          if ($p['type']=="date")
             $sql_fieldtype="date";


          $GLOBALS['create_sql'].="  `{$p['fieldname']}` $sql_fieldtype,\n";
      }
    }

   
    if($fieldvalue!=$p['empty_value'])
    {
        if(is_array($fieldvalue))
        {
            self::$lastDisplayValue=implode(' | ', $fieldvalue);
        }
        elseif($p['options'] && !$p['text_options'] )
        {
            if(!is_array($fieldvalue))
             self::$lastDisplayValue=$p['options'][$fieldvalue];
        }
        else
        {
            self::$lastDisplayValue=$fieldvalue;
        }    
    }
    else
        self::$lastDisplayValue="";
    

    self::addValidationRules($p);

    return $field;

  }

  public function get_display_value($p)
  {
      $ret=MwForm::render_naked_field($p);
      
      return MwForm::$lastDisplayValue;
  }

  static public function cleanLabel($l,$allow='')
  {
        $l=preg_replace('#<i>.*</i>#','',$l);
        $l=strip_tags($l);
        if(!strstr($allow,":"))
            $l=preg_replace('#:#','',$l);
        return  trim($l);
  }

}


// helper class to use form fields as objects:
    
class MwFormField extends ViewableData
{

    var $p=Array();

    public function __construct($p)
    {
        $this->p=$p;
    }

    public function getKey()
    {
        return $this->p['key']?$this->p['key']:$this->p['fieldname'];
    }
    
    public function getParams()
    {
        return $this->p;
    }
    
    public function getParam($key)
    {
        return $this->p[$key];
    }

    public function forTemplate()
    {
        return $this->getHTML();
    }

    public function getHTML($style='default')
    {

        if($this->p['readonly'])
            return $this->getReadOnlyHTML();
        else
            return  MwForm::render_field($this->p);
    }

    public function getMailHTML()
    {
        $label=$this->getCleanLabel(':');
        $value=$this->getDisplayValue();

        if($value)
            return "<tr class='formitem formitem-{$this->p['fieldname']}' valign='top'><td class='formvaluelabel' style='padding:3px'><b>$label</b></td><td class='formvalue' style='padding:3px'>$value</td></tr>\n";

    }



    public function getReadOnlyHTML()
    {
            $label=$this->getCleanLabel(':');
            $value=$this->getDisplayValue();

            if($label)
                $label="<label>$label</label>";
            if($value)
                $value="<span class='formvalue'>$value</span>";
            
            return "<span class='formitem formitem-{$this->p['fieldname']}'>$label$value</span>";
    }


    public function getNakedHTML()
    {
        return  MwForm::render_naked_field($this->p);
    }


    public function getLabel()
    {
        return  trim($this->p['label']);
    }

    public function getShortLabel()
    {
        return  trim(($this->p['shortlabel'])?$this->p['shortlabel']:$this->p['label']);
    }


    public function getCleanLabel($allow='')
    {
        if($this->p['type']=='checkbox')
            return "";
        
        $l=$this->Label;
        return MwForm::cleanLabel($l,$allow);
    }

    public function getDisplayValue()
    {
        $val=  MwForm::get_display_value($this->p);
        
        if($this->p['type']=='checkbox')
        {
            $val='✔ '.$this->Label;
        }
        
        if($this->p['type']=='textarea')
        {
            $val=nl2br($val);
        }
        return $val;
    }


}



?>
