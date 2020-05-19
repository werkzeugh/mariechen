<?php

use SilverStripe\Versioned\Versioned;
use SilverStripe\Control\Controller;



/**
*  use this to call your own stuff from local subclass of this (MysiteCron.php)
*/
class MwCronController extends Controller
{

    var $isCron=1;
    public function init()
    {
        parent::init();
        
        Versioned::set_stage("Live");
        
    }
        
    public function processJobs()
    {
        singleton('MwJobController')->cronprocess();
        die();
    }
    

}

