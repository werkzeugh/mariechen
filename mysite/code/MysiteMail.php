<?php

use SilverStripe\View\Requirements;
use SilverStripe\Control\Controller;
use SilverStripe\View\ViewableData;

/**
* 
*/
class MysiteMail extends ViewableData
{
    
    
    
    
    static function makeNiceHTML($mailhtml,$templatename=NULL,$context=NULL)
    {
        return singleton('MysiteMail')->renderNiceHTML($mailhtml,$templatename,$context);
    }
    
    function renderNiceHTML($mailhtml,$templatename=NULL,$context=NULL)
    {
        //wrap $html nicely to be sent by mail to f***cking mailclients like Outlook Express
        
        Requirements::clear();
        
        if($context)
        $c=$context;
        
        $c['Layout']=$mailhtml;
        if(!$c['CurrentPortal'])
        {
            $c['CurrentPortal']=Controller::curr()->CurrentPortal;
        }
        if($templatename) {            
            $tpls=$templatename;
        }  else {
            $tpls=Array('Includes/MysiteMailTemplate','Includes/MwMailTemplate');
        }
        
        
        $body=$this->customise($c)->renderWith($tpls);
        
        Requirements::restore();
        
        // remove requirements from html ---------- BEGIN
        $body=preg_replace('#<link[^>]+/>#','',$body);
        $body=preg_replace('#<script[^>]+></script>#','',$body);
        $body=preg_replace('#(</?)p(>| )#mis','\\1div\\2',$body);
        
        
        // remove requirements from html ---------- END        
        return $body;
        
    }
}



?>