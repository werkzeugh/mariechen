<?php 
use SilverStripe\Security\Member;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;


class MysiteUserRole extends MwUserRole {


    private static $db= [
        'CrmID' => 'Varchar(255)',
        'PartnerID' => 'Varchar(255)',
        'Sex' => "Enum('Herr,Frau','Frau')",
        'PreTitle' => 'Varchar(255)',
        'PostTitle' => 'Varchar(255)',
        'Position' => 'Varchar(255)',
        'Department' => 'Varchar(255)',
        'FonBusiness' => 'Varchar(255)',
        'FonMobile' => 'Varchar(255)',
        'Fax' => 'Varchar(255)',
        'Street' => 'Varchar(255)',
        'Zip' => 'Varchar(255)',
        'City' => 'Varchar(255)',
        'Country' => 'Varchar(255)',
        'PartnerID' => 'Int',
        'KennenlernCode' => 'Varchar(255)',
    ];


    static public function getByCrmID($id)
      {

           return DataObject::get_one(Member::class,"CrmID='".Convert::raw2sql($id)."'");
           
      }
      
       public function useNewSkin()
      {

        return true;
        
      }
      
      
      
      public function sendActivationMail()
      {

        $email['mailtext']=sprintf(_t("MwUser.sendActivationMailText","Um Ihre Anmeldung auf %s, zu vervollstaendigen, klicken Sie bitte auf folgenden Link:
        <br><br><a href='%s'>%s</a>
        
        <div>&nbsp;</div>
        Ihr persönlicher Gutscheincode für den Kennenlern-Einkauf lautet:  <b>%s</b>
        <div>&nbsp;</div>
        
          "),$this->owner->SiteName,$this->owner->ActivationURL,$this->owner->ActivationURL,$this->owner->KennenlernCode);



        $email['subject']=sprintf(_t("MwUser.sendActivationSubject","Anmeldung zu %s "),$this->owner->SiteName);
        $this->sendEmail($email);

      }  

}
