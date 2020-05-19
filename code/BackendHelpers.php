<?php

use SilverStripe\Control\Controller;


class BackendHelpers 
{


  static function includeTinyMCE()
  {
      return MwBackendPageController::includeTinyMCE();
  }


  static function getUrlParts()
  {
      $c=Controller::curr()->getRequest()->latestParams();
      $c['ID'] = Controller::curr()->urlParams['ID'];
      $c['OtherID'] = Controller::curr()->urlParams['OtherID'];
      $c['Action'] = Controller::curr()->urlParams['Action'];
      $c['MyBaseUrl']="/BE/".str_replace('Controller','',get_class(Controller::curr()));
      $c['BaseUrl']=$c['MyBaseUrl'];
      return $c;
  }


}

?>