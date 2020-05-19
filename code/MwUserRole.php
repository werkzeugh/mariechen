<?php

use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\Security\Permission;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Security\Security;
use SilverStripe\Security\Member;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Email\Email;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\IdentityStore;

class MwUserRole extends DataExtension
{

    var $cache;
    private static $db=[
        'Username'       => 'Varchar',
        'EmailValidated' => 'Varchar(255)',
        'isFEUser'       => DBBoolean::class,
        'PageRoots4BE'   => 'Varchar(255)',
        'ConfigData'     => 'Text',
    ];

    public function getDefaultUrlAfterLogin()
    {
        return '/';
    }
  
  
    public function isAdmin()
    {
        return Permission::checkMember($this->owner, 'ADMIN');
    }
  

    public function isDeveloper()
    {
        return ($this->owner->Username=='admin' || $this->owner->Email=='admin@werkzeugh.at');
    }
      
  
    public function useNewSkin()
    {
        return $this->owner->Config_useNewSkin;
    }
  
    public function getRootIDsForTree()
    {
        $ids=array();
        if ($this->isAdmin()) {
            return $ids;
        }
      
        $codes=$this->getGroupCodes();
        if ($codes) {
            foreach ($codes as $code) {
                if (preg_match('#^(view|edit)-(.+)$#', $code, $m)) {
                    $path=str_replace('-', '/', $m[2]);
                    //echo "<li>$code .. $path";
                    $page=SiteTree::get_by_link('/'.$path);
                    if ($page) {
                        $ids[]=$page->ID;
                          // echo " ... id: ".$page->ID;
                    }
                }
            }
        }
        // if(array_get($_GET,'d') || 1 ) { $x=$ids; $x=htmlspecialchars(print_r($x,1));echo "\n<li>// ArrayList: <pre>$x</pre>"; }

      
        return $ids;
    }


    public function getGroupCodes()
    {

        $g=$this->owner->getManyManyComponents("Groups");
      
        $groupids=$g->column('Code');

        return $groupids;
    }


    public function getGroupIds()
    {

        $g=$this->owner->getManyManyComponents("Groups");
      
        $groupids=$g->column('ID');

        return $groupids;
    }

    public function setGroupIds($idList)
    {

        $g=$this->owner->getManyManyComponents("Groups");

        if (!is_array($idList)) {
            $idList=explode(',', $idList);
        }
        $g->setByIDList($idList);
    }

    public function setNewPassword($newpassword)
    {
          
        if ($newpassword) {
            $this->owner->changePassword($newpassword, true);
        }
    }


    /**
     * @deprecated 5.0.0 Use Security::setCurrentUser() or IdentityStore::logIn()
     */
    public function v3logIn($persistent = false, HTTPRequest $request = null)
    {
//        Deprecation::notice(
//            '5.0.0',
//            'This method is deprecated and only logs in for the current request. Please use Security::setCurrentUser($user) or an IdentityStore'
//        );
        Security::setCurrentUser($this->owner);
        return Injector::inst()->get(IdentityStore::class)->logIn($this->owner, $persistent, $request);
    }

    public function canAccessIntranet()
    {
        if (strlen($this->owner->Funktionsnummern)>4) {
            return true;
        }
    }

    public function v3logOut()
    {
//        Deprecation::notice(
//            '5.0.0',
//            'This method is deprecated and now does not persist. Please use Security::setCurrentUser(null) or an IdentityStore'
//        );

        Injector::inst()->get(IdentityStore::class)->logOut(Controller::curr()->getRequest());
    }


    //old style password-check
    public function MwCheckPassword($pw)
    {
      // maps the result of checkPassword to a boolean
        $res=$this->owner->checkPassword($pw);
        if ($res===true) {
            return true;
        }

        if (is_object($res) && $res->isValid()) {
            return true;
        }
    }


    function canLogIn($canLogIn)
    {
        if (is_object($canLogIn)) {
            if ($canLogIn->isValid() && $this->owner->hasField('Deactivated')) {
                if ($this->owner->Deactivated) {
                    $canLogIn->error('User is not allowed to log in', 'deactivated');
                }
            }
        }
    }

    public function PublicName()
    {
        if ($this->owner->FirstName) {
            return "{$this->owner->FirstName} {$this->owner->Surname}";
        } elseif ($this->owner->Username) {
            return "{$this->owner->Username}";
        } elseif ($this->owner->Email) {
            return "{$this->owner->Email}";
        }
    }
  
  
    public function getUsernameOrEmail()
    {
        if ($this->owner->Username) {
            return $this->owner->Username;
        }
        if ($this->owner->Email) {
            return $this->owner->Email;
        }
    }
  
  
    public function EmailIsValidated($val)
    {
        $this->owner->EmailValidated=$val;
        $this->owner->write();
    }

    public function getActivationURL()
    {
        return "http://".array_get($_SERVER, 'HTTP_HOST')."/User/activate/".$this->owner->ID."/".$this->getActivationCode();
    }

 
    public function getPasswordResetURL()
    {
        return "http://".array_get($_SERVER, 'HTTP_HOST')."/User/resetpassword/".$this->owner->ID."/".$this->getActivationCode();
    }

    public function getActivationCode()
    {
        return md5($this->owner->Email.$this->owner->Username.$this->owner->Password);
    }



    public static function getByEmail($email)
    {

         return DataObject::get_one(Member::class, "lower(Email)=lower('".Convert::raw2sql(trim($email))."')");
    }


// email functions ---------- BEGIN


    public function getSiteName()
    {
        return array_get($_SERVER, 'HTTP_HOST');
    }

    public function getMailSignature()
    {
        return sprintf(_t('MwUser.MailSignature', "<br><br>Vielen Dank, Ihr %s - Team"), $this->owner->SiteName);
    }
  
    public function sendEmail($data)
    {
        $data['from']=MwUser::conf('mail_sender');
        if (!$data['from']) {
            $data['from']="noreply@".array_get($_SERVER, 'HTTP_HOST');
        }
    
        $data['mailtext'].=$this->owner->getMailSignature();
    
        $body=MysiteMail::makeNiceHTML($data['mailtext']);
    
        $email = Mwerkzeug\MwEmail::create($data['from'], $this->owner->Email, $data['subject'], $body);

        $email->send();
        MwMailLog::add($email); // log this mail
    
        return true;
    }
  
    public function sendActivationMail()
    {

        $email['mailtext']=sprintf(_t("MwUser.sendActivationMailText", "Um Ihre Anmeldung auf %s, zu vervollstaendigen, klicken Sie bitte auf folgenden Link:
    <br><br><a href='%s'>%s</a>
      "), $this->owner->SiteName, $this->owner->ActivationURL, $this->owner->ActivationURL);

        $email['subject']=sprintf(_t("MwUser.sendActivationSubject", "Anmeldung zu %s "), $this->owner->SiteName);
        $this->sendEmail($email);
    }


    public function sendUsernameMail()
    {

        $email['mailtext']=sprintf(_t("MwUser.sendUsernameMailText", "Ihr Benutzername auf %s lautet:
    <br><br>
      <b>%s</b>        
      "), $this->owner->SiteName, $this->owner->Username);

        $email['subject']=sprintf(_t("MwUser.sendUsernameMailSubject", "Ihr Benutzername auf %s  "), $this->owner->SiteName);
    
        return $this->sendEmail($email);
    }
  
  
 


    public function sendPasswordLinkMail()
    {

        $email['mailtext']=sprintf(_t("MwUser.sendPasswordLinkMailText", "Um Ihr Passwort für den Benutzer '%s' auf %s neu zu setzen,
    <br>klicken Sie bitte auf folgenden Link:
    <br><br><a href='%s'>%s</a> 
      "), $this->owner->UsernameOrEmail, $this->owner->SiteName, $this->owner->PasswordResetURL, $this->owner->PasswordResetURL);
      
        $email['subject']=sprintf(_t("MwUser.sendPasswordLinkMailSubject", "Ihr Passwort-Reset-Link auf %s "), $this->owner->SiteName);
        $ret=$this->sendEmail($email);

        return $ret;
    }

// email functions ---------- END


  // ----------------------------------- begin config-data functions


    public function myUpdate($incoming)
    {

       //filter Config-values
        foreach ($incoming as $key => $value) {
            if (preg_match('#^Config_(.*)$#', $key, $m)) {
                $this->setConfigField($m[1], $value);
                unset($incoming[$key]);
            }
        }

        return $this->owner->update($incoming);
    }

  // to be called from Member-Class magic __get - method
    public function __getConfig($fieldname)
    {
        if (preg_match('#^Config_(.*)$#', $fieldname, $m)) {
            $fname=$m[1];
            return $this->getConfigField($fname);
        }

        return null;
    }


    public function getConfigField($name)
    {
        if (!isset($this->fields)) {
            $this->initConfigFields();
        }

        return $this->fields[$name];
    }

    public function initConfigFields()
    {
        $this->fields=json_decode($this->owner->ConfigData, 1);
    }

    public function setConfigField($name, $value)
    {
     //add field & value to mainrecord->EVTData (does not save !)
        $data=json_decode($this->owner->ConfigData, 1);
        if (!is_array($data)) {
            $data=array();
        }
        $data[$name]=$value;

        $this->owner->ConfigData=json_encode($data);
    }

  // ----------------------------------- end config-data functions
}
