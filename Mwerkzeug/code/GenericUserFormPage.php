<?php

use SilverStripe\Control\Email\Email;
//use PageController;
use SilverStripe\Control\Controller;

//DEPRECATED

class GenericUserFormPage extends Page
{
  
    private static $db = array(
        'C4Pjson_FormContent' => 'Text',
        'SubmitContent'       => 'HTMLText',
    );
     
    public function C4P_Place_FormContent()
    {
        $conf['allowed_types']= array();
        $conf['allowed_types']['GenericUserFormPage_C4P_FormItem_Textfield']['label'] = "Text-Field";
        $conf['allowed_types']['GenericUserFormPage_C4P_FormItem_Textarea']['label']  = "Textarea";
        return $conf;
    }
}


class GenericUserFormPageController extends PageController
{
    
    

    
    // include formhelper (FormHelper) stuff ---------- BEGIN

    public function getFormHelper() //FormHelper
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=new FormHelper($this);
        }
        return $this->cache[__FUNCTION__];
    }
    
    
    
    function FormHelper_config()
    {
        // $c['MailTemplateName']='Includes/'.$this->ClassName.'_checkout_mail';
    
        return $c;
    }

    function FormHelper_setFields()
    {
    
        foreach ($this->C4P->getAll_FormContent as $item) {
            $p=$item->getFormItemConfigForFormHelper();
            $fields[$p['fieldname']]=$p;
        }


        return $fields;
    }
    

    // include formhelper (FormHelper) stuff ---------- END
    
    
    
   
    public function index(SilverStripe\Control\HTTPRequest $request)
    {

        $this->FormHelper->init();

        return array();
    }
  
  
  
    public function getSenderEmail()
    {
        return 'info@'.array_get($_SERVER, 'HTTP_HOST');
    }
  
    public function submit()
    {

        $data=array_get($_POST, 'fdata');
        
        foreach ($data as $key => $value) {
            $rows.="<tr valign='top'><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        $html="<table border=1>$rows</table>";
    
        $body=MysiteMail::makeNiceHTML($html);
    
        $email = Mwerkzeug\MwEmail::create($this->getSenderEmail(), $this->dataRecord->RecipientEmail, "New Form-Submission", $body);
        $email->replyTo($this->getSenderEmail());
        $email->send();
        MwMailLog::add($email); // log this mail
    

        MwBackendPageController::includePartialBootstrap();

        return array();
    }
  
  
    // include c4p stuff ---------- BEGIN

    public function getC4P()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=new C4P($this);
        }

        return $this->cache[__FUNCTION__];
    }

    // include c4p stuff ---------- END
}


class GenericUserFormPageBEController extends PageBEController
{
  
 
    public function getRawTabItems()
    {
        $items=array(
            "10"                       => "Basics",
            "15_C4P_Place_FormContent" => "Form-Elements",
            "20"                       => "Settings",
        );
     
        return $items;
    }
 
 
    public function step_10()
    {

        BackendHelpers::includeTinyMCE();  //all textareas with class tinymce will be richtext-editors

        //define all FormFields for step "Title"
        $p=array(); // ------- new field --------
        $p['label']="Title";
        $p['fieldname']="Title";
        $this->formFields[$p['fieldname']]=$p;


        //define all FormFields for step "Title"
        $p=array(); // ------- new field --------
        $p['label']="Send Form to email";
        $p['fieldname']="RecipientEmail";
        $p['validation']="email:true";
        $this->formFields[$p['fieldname']]=$p;


        $p=array(); // ------- new field --------
        $p['label']="Text after submit";
        $p['type']='textarea';
        $p['fieldname']="SubmitContent";
        // $p['rendertype']='beneath';
        $p['addon_classes']="tinymce";
        $this->formFields[$p['fieldname']]=$p;
    }
}


class GenericUserFormPage_C4P_FormItem extends C4P_Element
{
  
    public function getHTML($style = 'default')
    {
        
        $field=Controller::curr()->FormHelper->Field($this->FieldName);
        return $field->HTML();
    }

    public function getFormItemConfigForFormHelper()
    {
        $p=array(); // ------- new field --------
        $p['label']=$this->Label;
        $p['fieldname']=$this->FieldName;
        $p['validation']="'required':true";
        
        $p['addon_classes']=$this->CssClasses;
        $p['styles']=$this->CssStyleString;
        

        
        return $p;
    }
    
    

    public function getCssStyles()
    {
        $strparts=array();
        if ($this->CssWidth) {
            $strparts['width']="{$this->CssWidth}px";
        }

        if ($this->CssHeight) {
            $strparts['height']="{$this->CssWidth}px";
        }

        return $strparts;
    }

    public function getCssStyleString()
    {
        $styles=$this->CssStyles;
        $strparts=array();
        
        foreach ($styles as $key => $value) {
            $strparts[]="$key:$value";
        }

        $s=trim(implode(';', $strparts));
            return $s;
    }

    public function getCssID()
    {
        return 'input_'.$this->FieldName;
    }
    
    public function getFormHTML()
    {
        return "
                <input id=\"{$this->CssID}\" class=\"{$this->CssClasses}\" {$this->CssStyleString} type=\"text\" name=\"{$this->FieldName}\" value=\"{$this->Value}\">
                ";
    }
  
 
 
    
    public function setFormFields()
    {

        $p                 = array(); // ------- new field --------
        $p['fieldname']    = "FieldName";
        $p['label']        = "Feld-Name";
        $this->formFields['left'][$p['fieldname']]=$p;


   
        $p              = array(); // ------- new field --------
        $p['fieldname'] = "isRequired";
        $p['label']     = "is required";
        $p['type']     = "checkbox";
        $this->formFields['left'][$p['fieldname']]=$p;

        if (strstr($this->FormFieldname, 'Custom')) {
            $p              = array(); // ------- new field --------
            $p['fieldname'] = "FieldType";
            $p['label']     = "Feldtyp";
            $p['type']      = "option";
            $p['options']   = array(
                'text'     => 'Textfield',
                'textarea' => 'Textfield (multi-line)',
                'select'   => 'Dropdown',
                'radio'    => 'Radiobutton',
                'checkbox' => 'checkbox',
            );
            $this->formFields['left'][$p['type']]=$p;
      

            if ($this->FieldType='select' || $this->FieldType='radio') {
                $p              = array(); // ------- new field --------
                $p['fieldname'] = "FieldOptions";
                $p['label']     = "FieldValue, for Field-Types 'Dropdown' & 'Radiobutton' ";
                $p['type']      = "textarea";
                $p['note']      = "1 value per line (optional \"<strong>|</strong>\" for splitting key & value )";
                $this->formFields['left'][$p['type']]=$p;
            }
        }

        $p              = array(); // ------- new field --------
        $p['fieldname'] = "Label";
        $p['label']     = "Label";
        $p['type']      = "text";
        $this->formFields['right'][$p['fieldname']]=$p;
      
      
        $p              = array(); // ------- new field --------
        $p['fieldname'] = "Note";
        $p['label']     = "Addon-Info";
        $p['type']      = "textarea";
        $this->formFields['right'][$p['fieldname']]=$p;
    }
  
    public function getParsedFieldOptions()
    {
        $ret=array();
        if ($this->FieldOptions) {
            $lines=preg_split('#[\n\t]+#', trim($this->FieldOptions));
            foreach ($lines as $line) {
                $line=trim($line);
                if ($line) {
                    if (preg_match('#^([^|]+)\|([^|]+)$#', $line, $m)) {
                         $key=trim($m[1]);
                        $value=trim($m[2]);
                    } else {
                        $key=$line;
                        $value=$line;
                    }
                  
                    if (trim($value)) {
                        $ret[$key]=$value;
                    }
                }
            }
        }
        return $ret;
    }
  
    public function PreviewTpl()
    {
        return '
      <table>
        <tr>
            <td><strong>$FieldName</strong></td>
            <td>$Label <div style="color:#888">$Note</div></td>
            <td>
                $FieldType        
                <% if isRequired  %>
                  <em>Pflichtfeld</em>
                <% end_if %>
                    
            </td>
         </tr>
      </table>
';
    }
}


class GenericUserFormPage_C4P_FormItem_Textfield extends GenericUserFormPage_C4P_FormItem
{
    
    
    public function setFormFields()
    {

        parent::setFormFields();
        $p                 = array(); // ------- new field --------
        $p['fieldname']    = "CssWidth";
        $p['label']        = "Width (px)";
        $p['styles']       = "width:40px";
        $this->formFields['left'][$p['fieldname']]=$p;
    }
    
    
    public function getFormItemConfigForFormHelper()
    {
        $p=parent::getFormItemConfigForFormHelper();
        $p['type']='textfield';
        return $p;
    }
}

class GenericUserFormPage_C4P_FormItem_Textarea extends GenericUserFormPage_C4P_FormItem
{
    
    
    public function setFormFields()
    {

        parent::setFormFields();
        $p                 = array(); // ------- new field --------
        $p['fieldname']    = "CssWidth";
        $p['label']        = "Width (px)";
        $p['styles']       = "width:40px";
        
        $this->formFields['left'][$p['fieldname']]=$p;

        $p                 = array(); // ------- new field --------
        $p['fieldname']    = "CssHeight";
        $p['label']        = "Height (px)";
        $p['styles']       = "width:40px";
        $this->formFields['left'][$p['fieldname']]=$p;
    }
    
    
    public function getFormItemConfigForFormHelper()
    {
        $p=parent::getFormItemConfigForFormHelper();
        $p['type']='textarea';
        return $p;
    }
}
