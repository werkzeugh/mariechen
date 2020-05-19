<?php 
use SilverStripe\View\Requirements;
use SilverStripe\ORM\ArrayList;
use SilverStripe\i18n\i18n;
use SilverStripe\View\ViewableData;



/**
* 
*/
class FormHelper extends ViewableData
{
    
    var $conf=Array(); 
    var $cache;
    var $controller;
    var $record;
    var $FormData;
    var $FormFields;
    var $prefix;
    var $tid;
    var $MwForm;

    function __construct($controller,$prefix='FormHelper')
    {
     $this->controller=$controller;
     $this->prefix=$prefix;
     

     
    }
    
    
    public function DefaultFields()
    {
        return "<input type=\"hidden\" name=\"TransactionID\" value=\"{$this->CurrentTransactionID}\">";
    }
    
    public function init($conf=NULL)
    {
        $this->loadConfig();
        $this->includeRequirements($this->conf);
        $this->setupFormData();
        $this->handleIncomingValues();
        $this->setupMwForm();
    }
    
    
    
    public function loadConfig()
    {
        $methodName="{$this->prefix}_config";
        if($this->controller->hasMethod($methodName))
        {
            $this->conf=$this->controller->$methodName($options);
        }
    }
    


    public function handleIncomingValues($incoming = NULL)
    {
        if(array_get($_POST,'fdata'))
        {
            $incoming=array_get($_POST,'fdata');
            // if(array_get($_GET,'d') || 1 ) { $x=$incoming; $x=htmlspecialchars(print_r($x,1));echo "\n<li>ArrayList: <pre>$x</pre>"; }

            foreach ($incoming as $key => $value) {
                if(strstr($key,'JSON') && trim($value))
                {
                    if(MwUtils::jsonIsValid($value))
                    {
                        $incoming[$key]=MwUtils::tidyJSON($value); //clean up json
                    }
                    else
                    {
                        Requirements::customScript("alert('warning: your json code is invalid !!');");
                    }
                }
            }


            if(is_array($this->FormData))
               $this->FormData=MwUtils::array_merge_recursive_distinct(  $this->FormData, $incoming);
            elseif(is_object($this->FormData))
            {
                if ($this->FormData->hasMethod('myUpdate')) {
                    $this->FormData->myUpdate($incoming);
                } else {
                  $this->FormData->update($incoming);
                }
            }
            else
                $this->FormData=$incoming;
                
            
        }
        
        $this->write();

    }
    
    public function write()
    {
        if(is_object($this->FormData))
        {
            
            $this->FormData->write();
        }
        else
            $_SESSION[$this->SessionName]=$this->FormData; //save session 
        
    }

    public function setupMwForm()
    {
        //get $this->FormData for storage of form-date
        MwForm::set_default_rendertype($this->conf['rendertype']?$this->conf['rendertype']:'bootstrap');
        
        if ($this->conf['arrayBasename']) {
              MwForm::set_array_basename($this->conf['arrayBasename']);
        }

        $fd=$this->FormData;
                
        if(is_object($fd))
            MwForm::presetObject($fd);
        else
            MwForm::preset($fd);
        
    }

    public function setupFormData()
    {
        if(!$this->FormData){
            $this->setFormData($this->getFormData());
        }
    }

    public function setFormData($data)
    {
            $this->FormData=$data;
    }

    public function setFormDataField($key,$value)
    {
            $this->FormData[$key]=$value;
    }


    public function getFormData()
    {
        
    
        
        $methodName="{$this->prefix}_getFormData";
        if($this->controller->hasMethod($methodName))
        {
            return $this->controller->$methodName($options); //from controller-methos
            
        }
        elseif($this->controller->FormHelper_Record) // OR from controller-record
        {
            return $this->controller->FormHelper_Record;
        }
        else
        {
            return $this->getFormDataFromSession(); // or from session
        }

    }
    
    public function setFormFields($fieldarr)
    {
        foreach ($fieldarr as $name => $conf) {
            $this->setFormField($name,$conf);
        }

    }

    public function setFormField($name,$conf)
    {
        $this->FormFields[$name]=$conf;
    }

    
    public function setupFormFields()
    {
        
        if(!$this->FormFields)
        {
            $methodName="{$this->prefix}_setFields";
            if($this->controller->hasMethod($methodName))
            {
                $configs=$this->controller->$methodName($options);
                
                
                //mark groups
                foreach ($configs as $key => $value) {
                    if(preg_match('#^group-(.+)$#',$key,$m))
                    {
                        if($value)
                        foreach ($value as $key2 => $fieldconfs) {
                            $configs[$key2]=$fieldconfs;
                            $configs[$key2][groups]=$m[1].' ';
                        }
                        unset($configs[$key]);
                    }
                }
                
                
                $this->setFormFields($configs);
            }
        }
    }
    
    
    public function getFormFieldConfig($fieldname)
    {
        $this->setupFormFields();

        
        return $this->FormFields[$fieldname];
    }
    
    
    public function FormFields($filter='')
    {

        $this->setupFormFields();
        
        
        $dos=new ArrayList();
        if($this->FormFields)
        foreach ($this->FormFields as $fname=>$dummy) {
            $c=$this->getFormFieldConfig($fname);
            if(!$filter || preg_match("#(^| ){$filter}( |\$)#",$c['groups']))
                $dos->push( new MwFormField($c) );
        }
               
        return $dos;
        

    }


   
    
    public function Field($fieldname)
    {
        $c=$this->getFormFieldConfig($fieldname);
    

        
        return new MwFormField($c);
    }
    
    public function getCurrentTransactionID()
    {
        if(!$this->tid)
        {
            if(array_get($_REQUEST,'TransactionID'))
                $this->tid=array_get($_REQUEST,'TransactionID');
            if($_SESSION['FormHelperTransactionID'])
                $this->tid=$_SESSION['FormHelperTransactionID'];
            
            if(!$this->tid)
                $this->tid=md5(time()."_".rand(1,500));
            
        }
        return $this->tid;
        
    }
    
    public function isTrue($fieldname)
    {
        if(is_object($this->FormData))
        {
            if($this->FormData->$fieldname)
                return TRUE;
            
        }
        else
        {
            if($this->FormData[$fieldname])
                return TRUE;
        }
    }
    
    public function isFalse($fieldname)
    {
        return (!$this->isTrue($fieldname));
    }
    
    
    public function FieldValue($fieldname)
    {
        if(is_object($this->FormData))
            return $this->FormData->$fieldname;
        else
            return $this->FormData[$fieldname];
    }
    
    public function getSessionName()
    {
        return $this->prefix.'_'.$this->CurrentTransactionID;
    }
    
    public function getFormDataFromSession()
    {
        return $_SESSION[$this->SessionName];
    }
    
    public function resetFormDataSession()
    {
         $_SESSION[$this->SessionName]=Array();
         
     }
    
    public function getMailTemplateNames($tplname)
    {
        $conftplname=$this->conf['MailTemplateName'];
        if(is_array($conftplname))
            $ret=$conftplname;
        else
            $ret=Array();
        if($conftplname)
            $ret[]=$conftplname;
        $ret[]=$this->controller->ClassName.'_mail';
        
        // if(array_get($_GET,'d') || 1 ) { $x=$ret; $x=htmlspecialchars(print_r($x,1));echo "\n<li>ArrayList: <pre>$x</pre>"; }
        return $ret;
        
    }
    
    public function getMailHTML()
    {
        $html=$this->controller->renderWith($this->getMailTemplateNames($tpl_name));
        
        return $html;
    }
    
    static public function includeRequirements($conf=Array())
    {

        // Requirements::javascript("Mwerkzeug/javascript/jquery.ui.subclass.js");
        // Requirements::javascript('Mwerkzeug/javascript/EHP_jqueryui_widget.js');
        // Requirements::javascript('mysite/javascript/EHP_jqueryui_widget.js'); //try to load custom class
        // 
        // if(i18n::get_locale()=="de_DE")
        // {
        //     Requirements::javascript('Mwerkzeug/javascript/EHP_jqueryui_widget-de.js'); //try to load localization class
        // }
      
        // Requirements::css('Mwerkzeug/css/EHP.css');
        // Requirements::css('mysite/css/EHP.css'); //try to load custom css

        if(!$conf['SkipBootstrapSetup']) {
            MwBackendPageController::includePartialBootstrap();

        }

    }


    static public function includeValidationRequirements()
    {

        Requirements::javascript("Mwerkzeug/thirdparty/tiny-utils/base64.js");

        Requirements::javascript("Mwerkzeug/thirdparty/jquery-validate/jquery.validate.js");
        if(i18n::get_locale()=="de_DE")
        {
            Requirements::javascript("Mwerkzeug/thirdparty/jquery-validate/localization/methods_de.js");
            Requirements::javascript("Mwerkzeug/thirdparty/jquery-validate/localization/messages_de.js");
        }
   

    }


    public function getJSValidationRules()
    {
      return MwForm::getValidationRules();
    }


    function validate()
    {
    
        $this->includeValidationRequirements();
        return $this->getValidationJS();
    }


    public function getValidationJS()
    {
        
        $text1=_t('mwUserForm.SingleFieldWarning',"Bitte füllen Sie das fehlende, rot markierte Feld aus");
        $text2=_t('mwUserForm.MultipleFieldWarning',"Bitte füllen Sie die rot markierten Felder aus");
        $text_email=_t('mwUserForm.EmailFieldWarning',"Geben Sie bitte eine gültige E-Mail Adresse ein.");
        $text_passwd=_t('mwUserForm.PasswordFieldWarning',"Bitte geben Sie dasselbe Passwort erneut ein.");
        

        $validationErrorCssClass='error';
        if($this->conf['rendertype']=='bootstrap3') {
            $validationErrorCssClass="has-error";
        }
        $formId='dataform';
        if($this->conf['formId']) {
            $formId=$this->conf['formId'];
        }
        

        return <<<JAVASCRIPT
            
        $.extend($.validator.messages, {
        	required: "",
            equalTo: "$text_passwd",
        	email: "$text_email"
          });
            
            
          $.validator.addMethod(
          	"dateDE",
          	function(value, element) {
          		var check = false;
          		var re = /^\d{1,2}\.\d{1,2}\.\d{4}$/;
          		if( re.test(value)){
          			var adata = value.explode('.');
          			var dd = parseInt(adata[0],10);
          			var mm = parseInt(adata[1],10);
          			var yyyy = parseInt(adata[2],10);
          			var xdata = new Date(yyyy,mm-1,dd);
          			if ( ( xdata.getFullYear() == yyyy ) && ( xdata.getMonth () == mm - 1 ) && ( xdata.getDate() == dd ) )
          				check = true;
          			else
          				check = false;
          		} else
          			check = false;
          		return this.optional(element) || check;
          	},
          	"Bitte geben Sie ein Datum in der Form: TT.MM.JJJJ ein"
          );        
          
            
        var validateForm =function(params) {
            
            $("#{$formId}").validate({
                errorClass:"{$validationErrorCssClass}",
                ignore: "input.ignore, .ignore input, .ignore select, select.ignore",
                invalidHandler: function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                      var message = ((errors == 1)
                        ? '$text1'
                        : '$text2');
                        alert(message);
                    }
                  },
                errorPlacement: function(error, element) {
                    error.appendTo(element.closest('.controls,.form-control-wrap')).addClass('help-inline');
                   },
                highlight: function(element, errorClass) {
                    $(element).closest('.control-group,.form-group').addClass(errorClass);
                },
                unhighlight: function(element, errorClass) {
                    $(element).closest('.control-group,.form-group').removeClass(errorClass);
                },
                rules: {
                 {$this->JSValidationRules}
                },
                onkeyup: false
            });
        }
   
        validateForm();
        
JAVASCRIPT;
    }


}


 ?>
