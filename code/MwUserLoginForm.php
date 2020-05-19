<?php

use SilverStripe\Control\Session;
use SilverStripe\Control\Controller;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;

//  -----------------------------------  DEPRECATED

























class MwUserLoginForm extends MemberLoginForm {

  protected $authenticator_class = 'MwUserAuthenticator';

  public  function forTemplate()
  {
    //redirect to our custom controller
    if(strstr(array_get($_SERVER,'REQUEST_URI'),'/Security/login'))
    {
      header('Location:/User/login');
      exit();
    }
  }

  /**
   * Try to authenticate the user
   *
   * @param array Submitted data
   * @return Member Returns the member object on successful authentication
   *                or NULL on failure.
   */

	public function performLogin($data) {
		if($member = MwUserAuthenticator::authenticate($data, $this)) {
			$member->LogIn(isset($data['Remember']));
			return $member;

		} else {
			$this->extend('authenticationFailed', $data);
			return null;
		}
	}

  // this function controls the redirect based on success/failure
   public function dologin($data) {
    if ($this->performLogin($data)) {
     if($bu=array_get($_REQUEST,'BackURL'))
     {
       Mwerkzeug\MwSession::set("BackURL","");
       Controller::curr()->redirect($bu);
     }
     else
       Controller::curr()->redirect("/User/welcome");
    } else {
     Controller::curr()->redirectBack();
    }
   }


/*
	// this function is overloaded on our sublcass (this) to do something different
	public function dologin($data) {
		if($this->performLogin($data)) {
		        $this->redirectByGroup($data);
		} else {
			if($badLoginURL = Mwerkzeug\MwSession::get("BadLoginURL")) {
				Controller::curr()->redirect($badLoginURL);
			} else {
				Controller::curr()->redirectBack();
			}
		}
	}

	function redirectByGroup($data) {

		// gets the current member that is logging in.
		$member = Member::currentUser();

		// Switch redirection based on ID of group, if not found, just redirect back (default behaviour)
		// ASSUMPTION is that the user is only in one group. IF the member is in multiple groups
		// that are listed here, it redirects to the first group it finds for the member.
		if($member->inGroup(1)) {
			Controller::curr()->redirect(Director::baseURL() . 'articles');
		} elseif($member->inGroup(2)) {
			Controller::curr()->redirect(Director::baseURL() . 'home');
		} elseif($backURL = Mwerkzeug\MwSession::get("BackURL")) {
			Mwerkzeug\MwSession::clear("BackURL");
			Controller::curr()->redirect($backURL);
		} else {
			Controller::curr()->redirectBack();
		}
	}

*/

}

?>