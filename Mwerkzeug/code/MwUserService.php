<?php

use SilverStripe\Security\Member;
use SilverStripe\View\Requirements;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Core\Convert;
use SilverStripe\Control\Session;

//this is NOT a data-object !!!see MwUserRole which decorates "Member"


class MwUserService
{

  static $conf = Array();
  
  static public function conf($key)
  {
    return self::$conf[$key];
  }

  static public function setConf($key,$value)
  {
    self::$conf[$key]=$value;
  }

}


class MwUserServiceController extends  FrontendPageController
{


 var $myClass=Member::class;
 var $record;
 var $errorMsg=Array();
 var $c=Array();

 public function init()
 {
   parent::init();
  
   MwBackendPageController::includePartialBootstrap();
   
     if(MwUser::conf('usePlainLayoutForLogin'))
     {
         Requirements::clear();
         $this->summitSetTemplateFile("main","FrontendPage_Plain");
     }
  
   
   //Requirements::themedCSS("MwUser");


 }

 public function index(SilverStripe\Control\HTTPRequest $request)
 {
   //redirect Security/*** calls which got directed to this controller
   if(strstr(array_get($_SERVER,'REQUEST_URI'),'/Security/'))
   {
     $newurl=str_replace('/Security/',"/User/",array_get($_SERVER,'REQUEST_URI'));
     Controller::curr()->redirect($newurl);
   }
   
 }
 


 public function profile_step_1()
 {

  $p=Array(); // ------- new field --------
  $p['label']=_t("MwUser.username","Benutzername");
  $p['type']="text";
  $p['fieldname']="Username";
  $p['validation']="required:true,username:true";
  $fields[$p['fieldname']]=$p;

  // $p=Array(); // ------- new field --------
  // $p['label']=_t("MwUser.mitgliedsnr <i>(optional)</i>","Mitgliedsnr <i>(optional)</i>");
  // $p['type']="text";
  // $p['fieldname']="Mitgliedsnr";
  // $p['validation']="";
  // $fields[$p['fieldname']]=$p;

  $p=Array(); // ------- new field --------
  $p['label']=_t("MwUser.email"," E-Mail Adresse");
  $p['type']="text";
  $p['fieldname']='Email';
  $p['validation']="required:true,email:true";
  $fields[$p['fieldname']]=$p;


  $p=Array(); // ------- new field --------
  $p['label']=_t("MwUser.password1","Password");
  $p['type']="password";
  $p['fieldname']="password1";
  $p['validation']="required:true,password:true";
  $fields[$p['fieldname']]=$p;


  $p=Array(); // ------- new field --------
  $p['label']=_t("MwUser.password2","Password");
  $p['type']="password";
  $p['fieldname']="password2";
  $p['validation']="equalTo: '#input_password1'";
  $fields[$p['fieldname']]=$p;


  return Array('FormFields'=>$fields);
}

public function FormField($key)
{

  if ($this->c['FormFields'])
  {
    if($x=$this->c['FormFields']->find('Key',$key))
    {
      return $x;
    }
  }
}

static function getStateDropdown()
{
 $states_arr = array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming");
 return $states_arr;
}



public function noUserLoggedInMessage()
{
 $this->summitSetTemplateFile("Layout","MwUser");
 return Array('Content'=>_t('MwUser.noUserLoggedInMessage','Sie sind zur Zeit nicht eingeloggt.'));
}

public function profile()
{
 Requirements::javascript("Mwerkzeug/thirdparty/jquery-validate/jquery.validate.js");

 $step=1;

 if(array_get($_GET,'edit'))
 $this->record=DataObject::get_by_id(Member::class,array_get($_GET,'edit'));
 else   
 {   
   $this->record=Member::currentUser();
   if(!$this->record)
   return $this->noUserLoggedInMessage();
 }

 if($_POST)
 {
   $this->profile_handleIncomingValues();

   if(!$this->errorMsg && array_get($_POST,'NextStep'))
   $step=array_get($_POST,'NextStep');
 }

 $c=Array();
 $c['step']=$step;


 $c=$this->{"profile_step_".$step}($c);

 if(!$c['step'])
 $c['step']=$step;


 $dos=new ArrayList();

 // make ArrayList from formfields ---------- BEGIN

 if($c['FormFields'])
 {
   MwForm::preset($this->record);

   MwForm::set_default_rendertype('css');
   foreach ($c['FormFields'] as $key => $value)
   {
     $dos->push(
       new ArrayData(
         Array( 'Key' => $key, 'HTML'=>MwForm::render_field($value) )
         )
         );
       }
       $c['FormFields']=$dos;
     }

     // make ArrayList from formfields ---------- END
     //TODO: copy tool

     //$this->summitSetTemplateFile("main","BackendPage_iframe");

     $this->c=$c;
     return $c;

   }

   public function register()
   {
     Requirements::javascript("Mwerkzeug/thirdparty/jquery-validate/jquery.validate.js");

     $step=1;
     if($_POST)
     {
       $this->register_handleIncomingValues();

       if(!$this->errorMsg && array_get($_POST,'NextStep'))
       $step=array_get($_POST,'NextStep');
     }
     else
     {
       //no POST , clear the session Now !
       $this->setFdataStore('register',NULL);
     }

     $c=Array();
     $c['step']=$step;


     $c=$this->{"register_step_".$step}($c);

     if(!$c['step'])
     $c['step']=$step;


     $dos=new ArrayList();

     // make ArrayList from formfields ---------- BEGIN

     if($c['FormFields'])
     {
       MwForm::preset($this->getFdataStore('register'));
       MwForm::set_default_rendertype('css');
       foreach ($c['FormFields'] as $key => $value)
       {
         $dos->push(
           new ArrayData(
             Array( 'Key' => $key, 'HTML'=>MwForm::render_field($value) )
             )
             );
           }
           $c['FormFields']=$dos;
         }

         // make ArrayList from formfields ---------- END



         //TODO: copy tool

         //$this->summitSetTemplateFile("main","BackendPage_iframe");

         $this->c=$c;

         return $c;

       }

       public function errorMessages()
       {
        if($this->errorMsg)
        {
          $errorstack=new ArrayList();
          foreach ($this->errorMsg as $key=>$msg) {
            $errorstack->push(new ArrayData(Array('key'=>$key,'msg'=>$msg)));
          }
          return $errorstack;
        }

      }

      public function register_step_1()
      {

       $p=Array(); // ------- new field --------
       $p['label']=_t("MwUser.username","Benutzername");
       $p['type']="text";
       $p['fieldname']="Username";
       $p['validation']="required:true,username:true";
       $fields[$p['fieldname']]=$p;

       // $p=Array(); // ------- new field --------
       // $p['label']=_t("MwUser.mitgliedsnr <i>(optional)</i>","Mitgliedsnr <i>(optional)</i>");
       // $p['type']="text";
       // $p['fieldname']="Mitgliedsnr";
       // $p['validation']="";
       // $fields[$p['fieldname']]=$p;

       $p=Array(); // ------- new field --------
       $p['label']=_t("MwUser.email"," E-Mail Adresse");
       $p['type']="text";
       $p['fieldname']='Email';
       $p['validation']="required:true,email:true";
       $fields[$p['fieldname']]=$p;


       $p=Array(); // ------- new field --------
       $p['label']=_t("MwUser.password1","Password");
       $p['type']="password";
       $p['fieldname']="password1";
       $p['validation']="required:true,password:true";
       $fields[$p['fieldname']]=$p;


       $p=Array(); // ------- new field --------
       $p['label']=_t("MwUser.password2","Password");
       $p['type']="password";
       $p['fieldname']="password2";
       $p['validation']="equalTo: '#input_password1'";
       $fields[$p['fieldname']]=$p;


       return Array('FormFields'=>$fields);
     }

     public function register_step_finish()
     {

       $store=$this->getFdataStore('register');
       
       $this->record=$this->register_createUser($store);

       if(MwUser::conf('sendActivationMailAfterCreation'))
       {
           $this->record->sendActivationMail();
       }

       //$this->setFdataStore('register','');

     }


     public function profile_step_finish()
     {



    }


    public function register_checkIncomingValues($fdata)
    {

     $SQL_username = Convert::raw2sql($fdata['Username']);
     $existingMember = DataObject::get_one(Member::class, "Username = '$SQL_username'");

     if($existingMember) {
       if($existingMember->ID != $member->ID) {
         $this->errorMsg['UsernameAlreadyUsed']=sprintf(
           _t('MwUser.UsernameAlreadyUsed',"Der Benutzername: '<strong>%s</strong>' ist leider bereits vergeben, bitte wählen Sie einen anderen Benutzernamen aus"),
           Convert::raw2xml( $fdata['Username'] )
           );
         }
       }


       if(!$_SESSION['just_created_email']==$fdata['Email'])
       {
           
       $SQL_email = Convert::raw2sql($fdata['Email']);
       $existingMember = DataObject::get_one(Member::class, "Email = '$SQL_email'");

       if($existingMember) {
         if($existingMember->ID != $member->ID) {
           
           if(! (MwUser::conf('disable_usernames')))
               {
                   $username_text = "<br>Klicken Sie <a href='%s'>hier</a> um sich den Benutzernamen dieses Accounts zusenden zu lassen.";
               }  
     
           $this->errorMsg['EmailAlreadyUsed']=sprintf(
             _t('MwUser.EmailAlreadyUsed',"Für die E-Mail-Adresse: '<strong>%s</strong>' ist bereits ein Benutzeraccount eingerichtet worden.
             $username_text
             "),
             Convert::raw2xml($fdata['Email']),
             '/User/lostpassword/?'.urlencode('fdata[SendUsernameForEmail]').'='.urlencode($fdata['Email'])
             );
           }
         }

 
      }


       }

       public function register_handleIncomingValues()
       {
         $fdata=array_get($_POST,'fdata');

         $member=new Member();

         if($fdata['Email'])
         $fdata['Email'] = strtolower($fdata['Email']);

         $this->register_checkIncomingValues($fdata);

         $storedFdata=$this->getFdataStore('register');
         $storedFdata=MwUtils::array_merge_recursive_distinct($storedFdata, $fdata);
         $this->setFdataStore('register',$storedFdata);

       }

       public function profile_handleIncomingValues()
       {
         $fdata=array_get($_POST,'fdata');

         Member::currentUser()->update($fdata);
         Member::currentUser()->write();
       }

       public function register_createUser($fdata)
       {


         if($_SESSION['just_created_email']!=$fdata['Email'])
         {
             $member=Object::create(Member::class);
             $member->Password=$fdata['password1'];
             $member->update($fdata);
             $member->write();

             $_SESSION['just_created_email']=$fdata['Email'];
         }
         else
         {
             $email=Convert::raw2sql( $fdata['Email'] );
             $member=DataObject::get_one(Member::class,"Email='$email'");
         }
         
         $this->record=$member;
         
         return $this->record;
       }

       public function getFdataStore($key)
       {
         $ret=Mwerkzeug\MwSession::get('fdata_'.$key);
         if(!$ret)
         $ret=Array();
         return $ret;
       }

       public function setFdataStore($key,$data)
       {
         return Mwerkzeug\MwSession::set('fdata_'.$key,$data);
       }

       public function resendActivationMail()
       {
         $c['Title']=_t('MwUser.resendActivationMailTitle',"Aktivierungsmail erneut versenden");

         $username=Convert::raw2sql( array_get($_REQUEST,'username'));

         $user=DataObject::get_one(Member::class,"Username='$username'");
         if(!$user)
         {
          $user=DataObject::get_one(Member::class,"Email='$username'");
        } 

        if(!$user)
        {
         $this->errorMsg['UserNotFound']=sprintf(_t('MwUser.UserNotFound',"der Benutzer %s konnte nicht gefunden werden"),htmlspecialchars($username));
       }
       else
       {
         if($user->EmailValidated)
         $this->errorMsg['UserEmailAlreadyValidated']=sprintf(_t('MwUser.UserEmailAlreadyValidated',"der Benutzer %s wurde bereits aktiviert"),htmlspecialchars($username));

         $user->sendActivationMail();   

         if($_REQUEST) 
         {
           $fdata=array_get($_REQUEST,'fdata');
           if(trim($fdata['SendPasswordLinkForUsername']))
           {
             $c['Content']=$this->sendPasswordLinkForUsername($fdata['SendPasswordLinkForUsername']);
           }
           elseif(trim($fdata['SendUsernameForEmail']))
           {
             $c['Content']=$this->sendUsernameForEmail($fdata['SendUsernameForEmail']);

            }

          return $c;
          }
          }
        }



     //
     // public function welcome($value='')
     // {
       //   Controller::curr()->redirect('/BE/');
       // }
       //
       // public function sendActi()
       //   {
         //   
         //     //need email verification
         //     if(!Member::currentUser()->EmailActived)
         //     {
           //       Member::currentUser()->sendActivationMail();
           //     }
           //     $c[Content]="
           //     <div class='typography space'>
           //     Wir haben Ihnen soeben ein Aktivierungs-Mail an <b>". Member::currentUser()->Email."</b> gesendet.
           //     <p>&nbsp;</p>
           //     Um Ihre Registrierung abzuschliessen, klicken Sie Bitte den Aktivierungs-Link in dieser E-Mail.
           //     <p>&nbsp;</p>
           //   
           //     Sie haben die E-Mail nicht gefunden ?<br>
           //     Sehen Sie auch in Ihrem Spam-Ordner nach, evtl. wurde die Aktivierungsmail dorthin verschoben.
           //     </div>
           //     ";
           //   
           //     $c[Title]="my.naturfreunde.at Registrierung - Schritt 2";
           //     return $c;
           //   
           //   }
           //
           // public function sendActivationMailAgain()
           // {
             //   if(Member::currentUser())
             //     Member::currentUser()->sendActivationMail();
             //
             //   $c[Content]="Ihr Aktivierungsmail wurde erneut versandt an:". Member::currentUser()->Email;
             //   return $c;
             // }


         	public function logout($redirect = true) {
         		$member = Member::currentUser();
         		if($member) $member->logOut();
                
                if($previd=Mwerkzeug\MwSession::get('SudoPreviousUser'))
                {
                    Mwerkzeug\MwSession::set('SudoPreviousUser',0);
                    $record=DataObject::get_by_id(Member::class,$previd);
                    if($record)
                    {
                        $record->v3LogIn();
                        if($redirect)
                            return Controller::curr()->redirect('/BE/User');
                    }
                }
         		if($redirect) Controller::curr()->redirectBack();
         	}



            public function ajaxChangePassword()
            {
                $ret['status']='ERR';
                $ret['errorcode']='cannot_change_password';
                //needs POST {OldPassword, NewPassword1, NewPassword2}
                $user=Member::currentUser();
                
                if(!$user)
                {
                    $ret['errorcode']='user_not_logged_in';
                }
                elseif($_POST)
                {
                    if(array_get($_POST,'NewPassword1')!=array_get($_POST,'NewPassword1'))
                    {
                        $ret['errorcode']='passwords_do_not_match';
                    }
                    else
                    {
                        $new_pw=array_get($_POST,'NewPassword1');
                        
                        if(!$this->validatePassword($new_pw))
                        {
                            $ret['errorcode']='new_password_invalid';
                        }
                        else
                        {
                            if( ! $user->MwCheckPassword(array_get($_POST,'OldPassword')) )
                            {
                                $ret['errorcode']='oldpassword_invalid';
                                
                            }
                            else
                            {
                                $user->changePassword($new_pw);
                                $ret['status']='OK';
                                $ret['errorcode']='new_password_set';
                            }
                        }
                    }
                        
                
                    
                }
                else
                {
                    $ret['errorcode']='no_data_given';
                }
                
                
                header('content-type: application/json; charset=utf-8');
                echo json_encode($ret);
                die();
                
            }
            
            public function ajaxLogin()
            {
                //needs POST {Username, Password, RememberMe}

                $ret['status']='ERR';
                $ret['errorcode']='login_failed';
                $ret['errormsg']=_t('MwUser.LoginFailed','Login fehlgeschlagen');
                
                if($_POST)
                {
                 // check Login Credentials ---------- BEGIN

                 $fdata=$_POST;
                 if(!$fdata['Username'])
                 {
                     $ret['errorcode']='missing_credentials';
                     $ret['errormsg']=_t('MwUser.NoCredentials','Bitte geben Sie Ihren Benutzernamen und Ihr Passwort ein');
                 }
                 else
                 {
                   $authenticated_user=self::authenticate($fdata);
                   if($authenticated_user)  
                   {
                     $autologin=$fdata['RememberMe']?true:false;
                     $authenticated_user->v3LogIn($autologin);

                     $ret['status']='OK';
                     $ret['errorcode']='login_successful';
                     $ret['errormsg']='login successful';
                   } 
                 }
                 
                 // check Login Credentials ---------- END

               }
               
               header('content-type: application/json; charset=utf-8');
               echo json_encode($ret);
               die();

            }

             public function login()
             {

               $c['Title']='Login';

               $c['ShowForm']=TRUE;

               if(Member::currentUser())
               {
                $this->errorMsg['AlreadyLoggedIn']=_t('MwUser.AlreadyLoggedIn','Sie sind bereits im System eingeloggt.');
                $c['ShowForm']=FALSE;
              }

              if($_POST)
              {
               // check Login Credentials ---------- BEGIN

               $fdata=array_get($_POST,'fdata');
               if(!$fdata['Username'])
               $this->errorMsg['NoCredentials']=_t('MwUser.NoCredentials','Bitte geben Sie Ihren Benutzernamen und Ihr Passwort ein');
               else
               {
                 $authenticated_user=self::authenticate($fdata);
                 if($authenticated_user)  
                 {
                   $autologin=$fdata['RememberMe']?true:false;
                   $authenticated_user->v3LogIn($autologin);
                   $this->afterLogin();
                   $c['ShowForm']=FALSE;
                   return '<center style="margin:20px"><img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading"></center>';
                 } 
                 else
                 $this->errorMsg['LoginFailed']=_t('MwUser.LoginFailed','Login fehlgeschlagen');
               }
               // check Login Credentials ---------- END

             }

             $c['BackURL']=array_get($_REQUEST,'BackURL');



             return $c;

           }


           public function lostpassword()
           {
             $c['Title']=_t('MwUser.lostpasswordTitle',"Login-Daten vergessen ?");

             if(array_get($_REQUEST,'fdata')) 
             {


               $fdata=array_get($_REQUEST,'fdata');
               if(trim($fdata['SendPasswordLinkForUsername']))
               {
                 $c['Content']=$this->sendPasswordLinkForUsername($fdata['SendPasswordLinkForUsername']);
               }
               elseif(trim($fdata['SendUsernameForEmail']))
               {
                 $c['Content']=$this->sendUsernameForEmail($fdata['SendUsernameForEmail']);
               }

             }

             return $c;
           }


           public function validatePassword($pw)
           {
             $errcount_start=sizeof($this->errorMsg);
             if(strlen($pw)<6)
             $this->errorMsg['passwordTooShort']=_t('MwUser.passwordTooShort','Das Passwort ist zu kurz (min. 6 Zeichen)');

             return ($errcount_start == sizeof($this->errorMsg));  
           }

           public function resetpassword()
           {
               
               
             $c['Title']=_t('MwUser.resetpasswordTitle',"Passwort zurücksetzen");

             if(Member::currentUser())
             Member::currentUser()->logOut();
             $user=DataObject::get_by_id(Member::class,Controller::curr()->urlParams['ID']);
             $code=Controller::curr()->urlParams['OtherID'];

             if($user && $code && $code == $user->getActivationCode())
             {
               $c['ShowForm']=1;
               $c['User']=$user;
               if($_POST) 
               {
                $fdata=array_get($_POST,'fdata');
                if($fdata['Password1']!=$fdata['Password2'])
                $this->errorMsg['passwordsDoNotMatch']=_t('MwUser.passwordsDoNotMatch','Die beiden Passwörter müssen übereinstimmen');
                $new_pw=$fdata['Password1'];
                if(!$this->errorMsg && $this->validatePassword($new_pw))
                {
                  $user->changePassword($new_pw);
                  $c['Content']=sprintf( _t('Mwuser.PasswordSet','Das Passwort für den Benutzer %s wurde neu gesetzt') ,$user->UsernameOrEmail);
                  $c['Buttons']=new ArrayList();
                  $nextbutton=Array( 'Title' => _t('MwUser.Proceed2Login','Weiter zum Login'),'Primary' => '1', 'IconClass'=>'icon-arrow-right','Link'=>"/User/login");
                  $c['Buttons']->push(new ArrayData(  $nextbutton ));

                }


              }
            }
            else
             $this->errorMsg['resetpasswordWrongLink']=_t('MwUser.resetpasswordWrongLink',"Dieser Link ist leider nicht gültig");

            return $c;
          }


          public function changepassword()
          {
           $c['Title']=_t('MwUser.resetpasswordTitle',"Passwort ändern");

           $user=Member::currentUser();

           if(!$user)
           return $this->noUserLoggedInMessage();

           $c['ShowForm']=1;
           $c['User']=$user;


           if($_POST) 
           {
             $fdata=array_get($_POST,'fdata');


             if(isset($fdata['OldPassword']))
             {       
               if($user->MwCheckPassword($fdata['OldPassword']) == false) 
               $this->errorMsg['oldPasswordNotCorrect']=_t('MwUser.oldPasswordNotCorrect','Ihr altes Passwort ist leider nicht korrekt.');
             }

             if($fdata['Password1']!=$fdata['Password2'])
             $this->errorMsg['passwordsDoNotMatch']=_t('MwUser.passwordsDoNotMatch','Die beiden Passwörter müssen übereinstimmen');

             $new_pw=$fdata['Password1'];
             if(!$this->errorMsg && $this->validatePassword($new_pw))
             {
               $user->changePassword($new_pw);
               $c['Content']=sprintf( _t('Mwuser.PasswordSet',"Das Passwort für den Benutzer '%s' wurde neu gesetzt") ,$user->UsernameOrEmail);
               $c['Buttons']=new ArrayList();
               $nextbutton=Array( 'Title' => _t('MwUser.Proceed2Profile','Weiter'), 'Primary' => '1', 'IconClass'=>'icon-arrow-right','Link'=>"/User/profile");
               $c['Buttons']->push(new ArrayData(  $nextbutton ));
             }
           }


           return $c;
         }



         public function sendPasswordLinkForUsername($username)
         {
           $SQL_user = Convert::raw2sql($username);

           $member = DataObject::get_one(Member::class, "Username = '$SQL_user'");
           if(!$member)
           {
            $member=DataObject::get_one(Member::class,"Email='$username'");
          } 

          if(!$member)
          {
           if(!MwUser::conf('stealthMode'))
           {
             $this->errorMsg['UserNotFound']=sprintf(_t('MwUser.UserNotFound',"der Benutzer %s konnte nicht gefunden werden"),htmlspecialchars($username));
           }
         }
         else
         {
           if ($member->sendPasswordLinkMail())
           return sprintf(_t('MwUser.sendPasswordLinkForUsername',"Ein E-Mail mit dem Link zur Passwort-Rücksetzung wurde soeben an <strong>%s</strong> versendet."),htmlspecialchars($member->Email));
           else
           $this->errorMsg['MailSendFailed']=sprintf(_t('MwUser.MailSendFailed',"Das Versenden der E-Mail an '<strong>%s</strong>' ist fehlgeschlagen"),htmlspecialchars($member->Email));

         }

       } 


       public function sendUsernameForEmail($email)
       {
         $email=strtolower($email);
         $SQL_email = Convert::raw2sql($email);

         $member = DataObject::get_one(Member::class, "Email = '$SQL_email'");
         if(!$member)
         {
           if(!MwUser::conf('stealthMode'))
           {
             $this->errorMsg['EmailNotFound']=sprintf(_t('MwUser.EmailNotFound',"Für die E-Mail-Adresse <b>%s</b> konnte kein Benutzernamen gefunden werden"),htmlspecialchars($email));
           }
         }
         else
         {
           if ($member->sendUsernameMail())
           return sprintf(_t('MwUser.sendUsernameForEmail',"Ein E-Mail mit ihrem Benutzernamen wurde soeben an <strong>%s</strong> versendet."),htmlspecialchars($member->Email));
           else
           $this->errorMsg['MailSendFailed']=sprintf(_t('MwUser.MailSendFailed',"Das Versenden der E-Mail an '<strong>%s</strong>' ist fehlgeschlagen"),htmlspecialchars($member->Email));

         }

       }


       public function afterLogin()
       {
         if(!Member::currentUser())
         return '';

         $BackURL=array_get($_POST,'BackURL');
         if(strstr($BackURL,'/User/login'))
         $BackURL='';

         if(!$BackURL)
         $BackURL=$this->getUrlAfterLogin();

         echo "<meta http-equiv='refresh' content='0; URL=$BackURL' />";

       }

       public function getUrlAfterLogin()
       {
         if ($m=Member::currentUser())
         {
           $url=$m->getDefaultUrlAfterLogin();  
         }

         if (!$url)
         $url='/BE/';

         return $url;
       }

       public static function authenticate($fdata) 
       {

         $SQL_user = Convert::raw2sql($fdata['Username']);
         $isLockedOut = false;

         $member = DataObject::get_one(Member::class, "Username = '$SQL_user' AND Password IS NOT NULL");

         if($member)
         {
           if($member->MwCheckPassword($fdata['Password']) == false) {
             if($member->isLockedOut()) $isLockedOut = true;
             $member->registerFailedLogin();
             $member = null;
           }
         } 
         else
         {
           //try finding user via email as a second option
           $member = DataObject::get_one(Member::class, "Email = '$SQL_user' AND Password IS NOT NULL");
           if($member)
           {
             if($member->MwCheckPassword($fdata['Password']) == false) {
               if($member->isLockedOut()) $isLockedOut = true;
               $member->registerFailedLogin();
               $member = null;
             }
           } 
         }


         return $member;
       } 

       public function activate()
       {
         if(Member::currentUser())
         Member::currentUser()->logOut();

         $c['Title']=_t('MwUser.activateTitle',"Account Aktivierung");

         $user=DataObject::get_by_id(Member::class,Controller::curr()->urlParams['ID']);
         $code=Controller::curr()->urlParams['OtherID'];
         if($user && $code && $code == $user->getActivationCode())
         {
           $user->EmailIsValidated(TRUE);

           $c['Title']=sprintf(
             _t('MwUser.AccountActivatedTitle',"Ihr Account wurde aktiviert")
             );


             $c['Content']=sprintf(
               _t('MwUser.AccountActivated',"Sie können sich nun mit Ihrer E-Mail Adresse
               <strong>'%s'</strong> und Ihrem Passwort einloggen"),
               htmlspecialchars($user->UsernameOrEmail)
               );

               $c['Buttons']=new ArrayList();
               $nextbutton=Array( 'Title' => _t('MwUser.Proceed2Login','Weiter zum Login'),'Primary'=>TRUE, 'IconClass'=>'icon-arrow-right','Link'=>"/User/login");
               $c['Buttons']->push(new ArrayData(  $nextbutton ));

             }
             else
             $this->errorMsg['UserNotFound']=sprintf(_t('MwUser.ActivationLinkBroken',"der Aktivierungslink ist ungültig"));


             return $c;
           }

           //
           //

           //
           //
           //
           //
           //

           public function getJSValidationMessages()
           {
             return MwForm::getValidationMessages();
           }

           public function getJSValidationRules()
           {
             return MwForm::getValidationRules();
           }


         }





         ?>
