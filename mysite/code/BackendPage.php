<?php

use SilverStripe\i18n\i18n;
use SilverStripe\Control\Controller;

class BackendPage extends MwSiteTree
{
}


class BackendPageController extends MwBackendPageController
{
    public function init()
    {
        i18n::set_locale('de_AT'); //or de_AT
        $this->checkPhoenixToken();
        parent::init();
    }
      
    public function checkPhoenixToken()
    {
        $user= $this->CurrentMember();
        if ($user) {
            if (!$_COOKIE['ex_betoken']) {
                $res=TagEngine::singleton()->callExApi("/bags/get_beuser_token/".intval($user->ID));
                if ($res) {
                    $token=array_get($res, "token");
                    setcookie("ex_betoken", $token, null, "/");
                }
            }
        } else {
            setcookie("ex_betoken", null, null, "/");
        }
    }
    public function index(SilverStripe\Control\HTTPRequest $request)
    {
        Controller::curr()->redirect('/BE/Pages/');
    }
  
    public function getCustomNavigationStructure()
    {
        $nav=array();
        $rootpages=MwPage::conf('RootPages');
        $nav['/BE/Pages/']['data']       = array('Title'=>'Pages');
        $nav['/BE/MwFile/']['data']      = array('Title'=>'Files');
        $nav['/BE/User/']['data']        = array('Title'=>'Users');
        // $nav['/BE/MailLog/']['data']     = array('Title'=>'MailLog');
        // $nav['/BE/StaticTexts/']['data'] = array('Title'=>'Static Texts');
        $nav['/ex/be/orders/']['data'] = array('Title'=>'Orders');
        $nav['/ex/be/carts/']['data'] = array('Title'=>'Carts');
      
        // $nav['BE/FreshTag/']['data']                  = Array('Title'=>'Tags');
        return $nav;
    }
}
