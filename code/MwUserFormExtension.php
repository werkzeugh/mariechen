<?php
use SilverStripe\ORM\DataExtension;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\View\SSViewer;
use SilverStripe\Control\Email\Email;

/**
 *
 */
class MwUserFormExtension extends DataExtension
{

    private static $db = array(
        'C4Pjson_MwUserFormContent' => DBText::class,
    );

    public function C4P_Place_MwUserFormContent()
    {
        $conf['allowed_types']= array();
        $conf['allowed_types']['MwUserFormExtension_C4P_FormItem_Textfield']['label'] = "Text-Field";
        $conf['allowed_types']['MwUserFormExtension_C4P_FormItem_Textarea']['label']  = "Text-Area";
        $conf['allowed_types']['MwUserFormExtension_C4P_FormItem_Checkbox']['label']  = "Checkbox";
        $conf['allowed_types']['MwUserFormExtension_C4P_FormItem_Dropdown']['label']  = "Dropdown";
        $conf['allowed_types']['MwUserFormExtension_C4P_FormItem_Radiobuttons']['label']  = "Radiobuttons";
        $conf['allowed_types']['MwUserFormExtension_C4P_FormItem_Checkboxes']['label']  = "Checkboxes";
        $conf['allowed_types']['MwUserFormExtension_C4P_FormItem_Infotext']['label']  = "Info-Text";
        return $conf;
    }
    
    public function MwUserForm_ShowFormTab($placename = 'MainContent')
    {
      
        if (!$this->owner->ID) {
            return true;
        }
      
        if ($this->owner->C4P->numElementsInPlace($placename, 'MysiteUserFormExtension_C4P_Form')>0) {
            return true;
        }
      
        
        return false;
    }
    
    
    public function hasMethod($str)
    {
    
        
        return $this->owner->hasMethod($str);
    }
    
    
    // include formhelper (FormHelper) stuff ---------- BEGIN

    public function getFormHelper() //FormHelper
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=new FormHelper($this->owner);
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
    
         $fields=array();
        foreach ($this->owner->C4P->getAll_MwUserFormContent as $item) {
            $p=$item->getFormItemConfigForFormHelper();
            $fields[$p['fieldname']]=$p;
        }


        return $fields;
    }
    

    // include formhelper (FormHelper) stuff ---------- END
}

// to be included
    
class MwUserFormExtension_C4P_Form extends C4P_Element
{
    
    
    public function mySubmitText()
    {
        return ($this->SubmitText)?$this->SubmitText:"OK";
    }
    
    public function myMailSubject()
    {
        return ($this->MailSubject)?$this->MailSubject:"new Form Submission";
    }
    
    public function getDefaultRecipientEmail()
    {
        return "";
    }
    
    public function myRecipientEmail()
    {
        return ($this->RecipientEmail)?$this->RecipientEmail:$this->DefaultRecipientEmail;
    }
    
    
    public function setFormFields()
    {
        MwBackendPageController::includeTinyMCE();


        $formtablink=$this->getFormTabLink();

        $p= array(); // ------- new field --------
        $p['fieldname'] = "info";
        $p['type']      = "html";
        $p['label']     = "Form Elements";
        $p['html']      = '<div class="bootstrap space"><a class="btn btn-mini btn-primary" href="'.$formtablink.'"><i class="icon-arrow-right icon-white"></i> edit form-elements</a></div>';
        $this->formFields['left'][$p['fieldname']] = $p;
        
        $p=array(); // ------- new field --------
        $p['label']      = "Send Form to email";
        $p['fieldname']  = "RecipientEmail";
        $p['validation'] = "email:true";
        $p['tag_addon']=" placeholder=\"{$this->DefaultRecipientEmail}\" ";
        $this->formFields['left'][$p['fieldname']]=$p;


        $p=array(); // ------- new field --------
        $p['label']      = "Mail Subject";
        $p['fieldname']  = "MailSubject";
        $p['tag_addon']=" placeholder=\"new Form Submission\" ";
        
        $this->formFields['left'][$p['fieldname']]=$p;


        $p=array(); // ------- new field --------
        $p['label']      = "Text for SubmitButton";
        $p['fieldname']  = "SubmitText";
        $p['tag_addon']=" placeholder=\"OK\" ";
        $this->formFields['left'][$p['fieldname']]=$p;


        $p=array(); // ------- new field --------
        $p['label']         = "Text after submitting the form";
        $p['fieldname']     = "ResponseText";
        $p['type']          = "textarea";
        $p['addon_classes'] = "tinymce";
        $this->formFields['right'][$p['fieldname']]=$p;
    }
    
    
    
   
    
    
  
    public function getFormTabLink()
    {
                
        $classname=get_class($this->Mainrecord).'BEController';
        
        $c=singleton($classname);
        $c->dataRecord=$this->Mainrecord;
        
        $rawtabs=$c->TabItems();
        foreach ($rawtabs as $tab) {
            if (preg_match('#UserFormContent#i', $tab->URLSegment)) {
                return "/BE/Pages/edit/{$this->Mainrecord->ID}/".$tab->URLSegment;
            }
        }

        return null;
    }



    public function getHTML($style = 'default')
    {
        return $this->Html();
    }
    
    public function Html()
    {
     
        if ($_POST && array_get($_POST, 'fdata')) {
            return $this->handleIncomingValues();
        }
        
        Controller::curr()->FormHelper->init();
     
        $ss_html=$this->getTemplateHtml();
        $tpl=SSViewer::fromString($ss_html);
        return $this->renderWith($tpl);
    }
    
    
    public function mySenderEmail()
    {
           $email=$this->Mainrecord->mySenderEmail();
        if ($email) {
            return $email;
        }
           
           return 'info@'.array_get($_SERVER, 'HTTP_HOST');
    }
    
    public function handleIncomingValues($incoming = null)
    {

        $data=array_get($_POST, 'fdata');
        
        // spambot-check ---------- BEGIN
        if ($data['Email-Verification']!='x12') {
            $isRobot=1;
        } else {
            unset($data['Email-Verification']);
        }
        // spambot-check ---------- END
        
        if ($isRobot) {
            die("cannot process form-data");
        }
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $valuestr="";
                foreach ($value as $v) {
                    $v=trim($v);
                    if ($v) {
                        $valuestr.="<li>$v</li>";
                    }
                }
                $value=$valuestr;
            }
            
            $rows.="<tr valign='top'><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        
        $html="<table border=1>$rows</table>";
    
    
        $body=MysiteMail::makeNiceHTML($html);
    
        $email = Mwerkzeug\MwEmail::create($this->mySenderEmail(), $this->myRecipientEmail(), $this->myMailSubject(), $body);
        $email->replyTo($this->mySenderEmail());

        $email->send();
        MwMailLog::add($email); // log this mail
    
        MwBackendPageController::includePartialBootstrap();

        
        $html= <<<HTML

<div id='mwuserform_response'>
    <div class='well'>
       <div class='typography'> {$this->ResponseText}</div>
    </div>
</div>
<script>
 var responsehtml=document.getElementById('mwuserform_response').innerHTML;
 window.parent.jQuery('#dataform').html(responsehtml);
</script>

HTML;
        die($html);
    }
  
 
    public function getTemplateHtml()
    {
     
        MwBackendPageController::includePartialBootstrap();
        
        return <<<HTML
        <div class='bootstrap MwUserForm space'>
            <form id='dataform' class='form form-horizontal' method='POST' target='mwuserform_submitframe' >
                
                \$FormHelper.DefaultFields
               
                <% loop FormHelper.FormFields %>
    
                \$HTML
               
                <% end_loop %>                    
                        
                <div class="control-group">
                    <div class="controls">
                        <button class="btn btn-primary mwuserform-submit" type="submit"><i class="icon-white icon-ok"></i> \$mySubmitText</button>
                    </div>
                </div>

            </form>
            <iframe style="width:0px;height:0px;border:0px;" name='mwuserform_submitframe'></iframe>
        </div>
        
        <script type="text/javascript" charset="utf-8">
        
        jQuery(document).ready(function($) {
    
            \$FormHelper.validate;

            $('#dataform').validate().settings.submitHandler = function(form,v) {
                
                $('#dataform').append(Base64.decode('PGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iZmRhdGFbRW1haWwtVmVyaWZpY2F0aW9uXSIgdmFsdWU9IngxMiI+'));
                
                $('.mwuserform-submit').after('<img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">').remove();
                form.submit();
              };
        
        });
        
        </script> 
HTML;
    }
    
    public function FormHelper()
    {
        return $this->Mainrecord->FormHelper;
    }
 
    public function PreviewTpl()
    {
        global $count;
        
        $count++;
        
    
        $script=<<<JAVASCRIPT

<script>

jQuery(document).ready(function($) {

    var form_n=0;
   
   $('.MwUserFormExtension_C4P_Form').each(function(e)
   {
       form_n++;
       if(form_n>1)
       {
           $('.previewcol',this).html("<div class='bootstrap'><div class='alert alert-error'><i class='icon-warning-sign'></i> only 1 form-element is allowed per page, only the first one will be displayed</div></div>");    
       }
   });
   
 });
</script>
JAVASCRIPT;
        
        
        $formtablink=$this->getFormTabLink();
        
        return '
        <div class="bootstrap">
             <table class="table table-condensed table-striped table-bordered">
                 <tr><td><strong>Form-Recipient</strong></td><td>$myRecipientEmail</td></tr>
                 <% loop FormHelper.FormFields %>
                   <tr><td>$getParam("type")</td><td><strong>$Key</strong> <em>$Label</em></td></tr>
                 <% end_loop %>   
             </table>
             <div>&nbsp;</div>
             <a class="btn btn-mini btn-primary" href="'.$formtablink.'"><i class="icon-arrow-right icon-white"></i> edit form-elements</a></div>
         </div>
        '
        .$script;
    }
}



class MwUserFormExtension_C4P_FormItem extends C4P_Element
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
        $p['validation']=$this->getValidationRule();
        $p['addon_classes']=$this->CssClasses;
        $p['styles']=$this->CssStyleString;
        
        return $p;
    }
    
    
    public function isEmailField()
    {
        return preg_match("#e[^a-z]*mail#i", $this->FieldName);
    }
    
    public function getValidationRule()
    {
    
        $rules=array();
        if ($this->isRequired) {
            $rules['required']="required:true";
        }
        if ($this->isEmailField()) {
            $rules['email']="email:true";
        }
        
        if ($this->ValidationFieldType) {
            $rules['fieldtype']="{$this->ValidationFieldType}:true";
        }
        
        return implode(',', $rules);
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

    public function getCssFormElementID()
    {
        return 'input_'.$this->FieldName;
    }
    
    public function getFormHTML()
    {
        return "
                <input id=\"{$this->CssFormElementID}\" class=\"{$this->CssClasses}\" {$this->CssStyleString} type=\"text\" name=\"{$this->FieldName}\" value=\"{$this->Value}\">
                ";
    }
  
 
 
    
    public function setFormFields()
    {

        $p                 = array(); // ------- new field --------
        $p['fieldname']    = "FieldName";
        $p['label']        = "Field-Name";
        $this->formFields['left'][$p['fieldname']]=$p;


   
        $p              = array(); // ------- new field --------
        $p['fieldname'] = "isRequired";
        $p['label']     = "is required";
        $p['type']     = "checkbox";
        $this->formFields['left'][$p['fieldname']]=$p;

        if (strstr($this->FormFieldname, 'Custom')) {
            $p              = array(); // ------- new field --------
            $p['fieldname'] = "FieldType";
            $p['label']     = "Field-type";
            $p['type']      = "option";
            $p['options']   = array(
                'text'     => 'Textfeld',
                'textarea' => 'Textfeld (mehrzeilig)',
                'select'   => 'Dropdown',
                'radio'    => 'Radiobutton',
                'checkbox' => 'checkbox',
            );
            $this->formFields['left'][$p['type']]=$p;
      

            if ($this->FieldType=='select' || $this->FieldType=='radio') {
                $p              = array(); // ------- new field --------
                $p['fieldname'] = "FieldOptions";
                $p['label']     = "FeldWerte, für Feldtypen 'Dropdown' & 'Radiobutton' ";
                $p['type']      = "textarea";
                $p['note']      = "1 Wert pro Zeile (optional \"<strong>|</strong>\" zum Trennen von key und wert )";
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
                $CType        
                <% if isRequired  %>
                  <em>required</em>
                <% end_if %>
            </td>
         </tr>
      </table>
';
    }
}


class MwUserFormExtension_C4P_FormItem_Textfield extends MwUserFormExtension_C4P_FormItem
{
    
    
    
    
    public function setFormFields()
    {

        parent::setFormFields();
        $p                 = array(); // ------- new field --------
        $p['fieldname']    = "CssWidth";
        $p['label']        = "Width (px)";
        $p['styles']       = "width:40px";
        $this->formFields['left'][$p['fieldname']]=$p;
        
        $p=array(); // ------- new field --------
        $p['label']         = "restrict field type to be a valid:";
        $p['fieldname']     = "ValidationFieldType";
        $p['type']          = "dropdown";
        $p['options']      = array(
            'date'   => 'date',
            'dateDE' => 'german date',
            'number' => 'number',
            'email'  => 'e-mail address',
        );
        $this->formFields['left'][$p['fieldname']]=$p;
    }
    
    
    public function getFormItemConfigForFormHelper()
    {
        $p=parent::getFormItemConfigForFormHelper();
        $p['type']='textfield';
        return $p;
    }
}

class MwUserFormExtension_C4P_FormItem_Textarea extends MwUserFormExtension_C4P_FormItem
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

class MwUserFormExtension_C4P_FormItem_Infotext extends MwUserFormExtension_C4P_FormItem
{
    
    
    public function setFormFields()
    {

        BackendHelpers::includeTinyMCE();  //all textareas with class tinymce will be richtext-editors

        $p=array(); // ------- new field --------
        $p['fieldname']     = "Text";
        $p['type']          = "textarea";
        $p['addon_classes'] = "tinymce";
        $this->formFields[$p['fieldname']]=$p;
    }
    
    
    public function getFormItemConfigForFormHelper()
    {
        $p=parent::getFormItemConfigForFormHelper();
        $p['rendertype']='naked';
        $p['fieldname']=$this->ID;
        $p['type']='html';
        $p['html']=$this->getHTML();
        return $p;
    }
    
    public function getHTML($style = 'default')
    {
        return "<div class='space typography'>{$this->Text}</div>";
    }
}


class MwUserFormExtension_C4P_FormItem_Checkbox extends MwUserFormExtension_C4P_FormItem
{
    
    
    public function setFormFields()
    {

        parent::setFormFields();
    }
    
    
    public function getFormItemConfigForFormHelper()
    {
        $p=parent::getFormItemConfigForFormHelper();
        $p['type']='checkbox';
        return $p;
    }
}






class MwUserFormExtension_C4P_FormItem_Dropdown extends MwUserFormExtension_C4P_FormItem
{
    
    
    public function setFormFields()
    {

        parent::setFormFields();


        $p=array(); // ------- new field --------
        $p['label']         = "Values <i>one value per line</i>";
        $p['fieldname']     = "OptionValues";
        $p['type']          = "textarea";
        $p['styles_addon']  = "height:300px";
        $this->formFields['left'][$p['fieldname']]=$p;
    }
    
    
    public function getAllOptions()
    {
        $str=$this->OptionValues;
        $lines=explode("\n", $str);

        $options=array();
        foreach ($lines as $line) {
            //TODO: split | values into key/value pairs
            $val=trim($line);
            $val=preg_replace('#[\'"]#', " ", $val);
            $options[$val]=$val;
        }
    
        return $options;
    }
    
    
    public function getFormItemConfigForFormHelper()
    {
        $p=parent::getFormItemConfigForFormHelper();
        $p['type']='select';
        $p['options']=$this->getAllOptions();
        return $p;
    }
}


class MwUserFormExtension_C4P_FormItem_Radiobuttons extends MwUserFormExtension_C4P_FormItem_Dropdown
{
        
    public function getFormItemConfigForFormHelper()
    {
        $p=parent::getFormItemConfigForFormHelper();
        $p['type']='radio';
        $p['options']=$this->getAllOptions();
        return $p;
    }
}




class MwUserFormExtension_C4P_FormItem_Checkboxes extends MwUserFormExtension_C4P_FormItem_Radiobuttons
{



    public function getFormItemConfigForFormHelper()
    {
        $p=parent::getFormItemConfigForFormHelper();
        $p['type']='checkboxes';
        $p['options']=$this->getAllOptions();
        return $p;
    }
}
