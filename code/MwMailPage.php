<?php

use SilverStripe\Control\Email\Email;
use SilverStripe\View\Requirements;

class MwMailPage extends FrontendPage
{
  
    private static $db=array(
        'Subject'     => 'Varchar(255)',
        'SenderEmail' => 'Varchar(255)',
        'SenderName'  => 'Varchar(255)',
    
    );
    
    
  
    
    
    
    public function __get($fieldname)
    {
        

        //retrieve vals via my - prefix, they get defaulted later
        if (strstr($fieldname, 'my') && preg_match('#^my(.*)$#', $fieldname, $m)) {
            $fname=$m[1];
            if ($this->$fname) {
                return $this->$fname;
            } else {
                return $this->getDefaultValue($fname);
            }
        }
        return parent::__get($fieldname);
    }
    
  
    

    var $defaultValues;
    public function initDefaultValues()
    {
        // to override
        // $this->defaultValues['rundmail']['Content']  = trim('
    }

    public function getDefaultValue($name)
    {
        if (!$this->defaultValues) {
            $this->initDefaultValues();
        }
      
        if (is_array($this->defaultValues[$this->URLSegment]) && $this->defaultValues[$this->URLSegment][$name]) {
            return $this->defaultValues[$this->URLSegment][$name];
        } else {
            return $this->defaultValues[$name];
        }
    }
  


    public function html()
    {
        //get html for this email via controller

        $html=$this->myContent;
        $html=str_replace('<p>', '<div>', $html);
        $html=str_replace('</p>', '</div>', $html);
        $html=preg_replace('#<div>\s</div>#', '<div>&nbsp;</div>', $html);
        
        $serverurl='http://'.array_get($_SERVER, 'HTTP_HOST');

        $html= MysiteMail::makeNiceHTML($html, null, array('Page' => $this));
        $html=preg_replace("#([\"'])/(files|mysite|themes|home)/#", "\\1$serverurl/\\2/", $html);
        return $html;
    }

    public function from()
    {
        $sender=trim($this->mySenderEmail);
        $sendername=trim($this->mySenderName);
        if (!$sendername) {
            $from=$sender;
        } else {
            $from="$sendername <{$sender}>";
        }
        return $from;
    }

    public function sendToEmail($email)
    {

        $subject=$this->Subject;
       
        
               
        $mailtext= $this->html();
        $mail = Mwerkzeug\MwEmail::create($this->from(), $email, $subject, $mailtext);
        $mail->send();
        MwMailLog::add($mail); // log this mail
    }
}

class MwMailPageController extends FrontendPageController
{

    public function index(SilverStripe\Control\HTTPRequest $request)
    {
        
        $c=array();
        $c['MailHTML']=$this->html();
        
        Requirements::clear();
        
                          
        $this->summitSetTemplateFile("main", "MwMailPage_index");
        return $c;
    }

    public function htmltest()
    {
        Requirements::clear();
        $mailtext=$this->html();
        echo $mailtext;
        die();
    }

    function testsend()
    {
        $email=array_get($_POST, 'email');
       
        $this->dataRecord->sendToEmail($email);
        echo "<li>test-mail was sent";
        echo "<p><a href=\"{array_get($_SERVER,'HTTP_REFERER')}\">continue</a>";
       
        die();
    }
}

class MwMailPageBEController extends FrontendPageBEController
{

    public function getRawTabItems()
    {
        $items=array(
            "10" => "Basics",
            "20" => "Settings",
        // "30"=>"Videos",
        // "40"=>"See Also",
        // "50"=>"Parents",
        );
        return $items;
    }

    public function step_10()
    {

        BackendHelpers::includeTinyMCE();  //all textareas with class tinymce will be richtext-editors

        //define all FormFields for step "Title"
        $p=array(); // ------- new field --------
        $p['label']="Subject";
        $p['fieldname']="Subject";
        if ($defaultValue=$this->record->getDefaultValue($p['fieldname'])) {
            $p['note']='<span  style="color:#aaa">default: </span>'. strip_tags($defaultValue);
        }
        
        $this->formFields[$p['fieldname']]=$p;


        //define all FormFields for step "Title"
        $p=array(); // ------- new field --------
        $p['label']="Sender Name";
        $p['fieldname']="SenderName";
        if ($defaultValue=$this->record->getDefaultValue($p['fieldname'])) {
            $p['note']='<span  style="color:#aaa">default: </span>'. strip_tags($defaultValue);
        }
        $this->formFields[$p['fieldname']]=$p;

        //define all FormFields for step "Title"
        $p=array(); // ------- new field --------
        $p['label']="Sender Email";
        $p['fieldname']="SenderEmail";
        $p['validation']="email:true";
        if ($defaultValue=$this->record->getDefaultValue($p['fieldname'])) {
            $p['note']='<span  style="color:#aaa">default: </span>'. strip_tags($defaultValue);
        }
        $this->formFields[$p['fieldname']]=$p;


        $p=array(); // ------- new field --------
        $p['label']="Mail-Text";
        $p['type']='textarea';
        $p['fieldname']="Content";
        $p['rendertype']='beneath';
        $p['addon_classes']="tinymce_minimal tinymce_mail";
        $this->formFields[$p['fieldname']]=$p;
        
        $p=array(); // ------- new field --------
        $p['label']="Default-Mailtext (for copy & paste)";
        $p['type']='html';
        $p['fieldname']="DefaultContent";
        $p['rendertype']='beneath';
        $p['html']=$this->record->getDefaultValue('Content');
        $p['html']="<div style=\"border:1px solid black;padding:20px 10px;background:#eee;\">{$p['html']}</div>";
        $this->formFields[$p['fieldname']]=$p;
    }
}
