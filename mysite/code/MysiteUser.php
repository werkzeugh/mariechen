<?php

use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\Security\Member;
use SilverStripe\ORM\DataObject;


	class MysiteUserController extends MwUserController {




        private static $allowed_actions= [
            'profile','register','activate'
        ];


        public function profile()
		{
			Controller::curr()->redirect($this->CurrentPortal->UserProfilePage->Link());
		}


        public function register()
        {
          $c = parent::register();
          return $c;
        }
        
        
        
         public function register_step_1()
              {

               // $p=Array(); // ------- new field --------
               //              $p['label']=_t("MwUser.username","Benutzername");
               //              $p['type']="text";
               //              $p['fieldname']="Username";
               //              $p['validation']="required:true,username:true";
               //              $fields[$p['fieldname']]=$p;
             
               // $p=Array(); // ------- new field --------
               // $p['label']=_t("MwUser.mitgliedsnr <i>(optional)</i>","Mitgliedsnr <i>(optional)</i>");
               // $p['type']="text";
               // $p['fieldname']="Mitgliedsnr";
               // $p['validation']="";
               // $fields[$p['fieldname']]=$p;

               $p=Array(); // ------- new field --------
               $p['label']=_t("MwUser.email"," E-Mail Adresse");
               $p['type']="text";
               $p['fieldname']=Email::class;
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
        
        
             public function register_createUser($fdata)
             {
                 parent::register_createUser($fdata);

                 if(!$this->record->KennenlernCode)
                     $this->record->KennenlernCode=$this->generateCode();
                 $this->record->write();
                 
               return $this->record;
             }
      
             public function generateCode()
             {

                     $characters = '1234567890ABCDEFGHIJKLMNPQRST';
                     $len=4;
                     $newcode = '';
                     for ($i = 0; $i < $len; $i++) {
                         $newcode .= $characters[rand(0, strlen($characters) - 1)];
                     }
                     $newcode=Date('Ym').$newcode;
          
                 return $newcode;
             }
        
             public function activate()
             {
               if(Member::currentUser())
               Member::currentUser()->logOut();

               $c['Title']="Ihr Account wurde aktiviert";

               $user=DataObject::get_by_id(Member::class,Controller::curr()->urlParams['ID']);
               $code=Controller::curr()->urlParams['OtherID'];
               if($user && $code && $code == $user->getActivationCode())
               {
                 $user->EmailIsValidated(TRUE);

                   $c['Content']=<<<HTML
                 <div class='bootstrap'>  
                                    <div class='space well'>
                                        
                                        Wenn Sie Ihre nächste Bestellung durchführen, 
                                        können Sie Sich nun mit Ihrer Email-Adresse  '<strong>{$user->Email}</strong>' und Ihrem gewählten Passwort anmelden.
                                        <div>&nbsp;</div>
                                        
                                        Dort können Sie auch Ihren persönlichen  Gutscheincode  für ihren erstmaligen Kennenlerneinkauf einlösen,
                                        den sie in der Aktivierungs-Email erhalten haben.
                                        
                                        <div>&nbsp;</div>
                                        
                                        <span class='label label-warning'>Achtung: der Gutscheincode kann nur einmal pro Person und Haushalt verwendet werden.</span>
                                        
                                        
                                    </div></div>

HTML;
                   
                     // 
                     // $c['Buttons']=new ArrayList();
                     // $nextbutton=Array( 'Title' => _t('MwUser.Proceed2Login','Weiter zum Login'),'Primary'=>TRUE, 'IconClass'=>'icon-arrow-right','Link'=>"/User/login");
                     // $c['Buttons']->push(new ArrayData(  $nextbutton ));

                   }
                   else
                       $this->errorMsg['UserNotFound']=sprintf(_t('MwUser.ActivationLinkBroken',"dieser Aktivierungslink ist ungültig"));


                   return $c;
                 }
        
        
        

}