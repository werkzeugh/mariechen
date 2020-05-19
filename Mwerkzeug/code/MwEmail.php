<?php

namespace  Mwerkzeug;

use SilverStripe\Control\Email\Email;

class MwEmail
{
    
    public function create($from, $to, $subject, $body)
    {
        
        $froms=self::fixEmail($from);
        if(!$froms) {
            $from="anmeldung@naturfreunde.at";
        } else {
            $from=$froms[0];
        }
        
        $to=self::fixEmail($to);
      
        
        if($to) {
            $email=new Email($from, $to, $subject, $body);
        }
        else {
            $email=null;
        }

        if (class_exists('MysiteEmail')) {
            $email = \MysiteEmail::singleton()->fixEmail($email);
        }

        return $email;
        
    }
    
    public function fixEmail($email)
    {
        $email=str_replace(';', ',', $email);
        $emails=explode(",", $email);
        
        $ret=[];
        foreach ($emails as $email) {
            $email=trim($email);
            
            if ($email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $ret[]=$email;
                } else {
                    // todo log invalid email address
                }
            }
        }
        
        return $ret;
    }
    
    
    public function isValidEmail($email)
    {
        $emails=self::fixEmail($email);
        if($emails) {
            return true;
        }  else {
            return false;
        }
    }
}
