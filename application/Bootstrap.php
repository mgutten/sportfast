<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initMyActionHelpers()
    {
        $this->bootstrap('frontController');
        Zend_Controller_Action_HelperBroker::getStaticHelper('LoginForm');

    }
	
	protected function _initVars()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
		/* default to white back for page */
		//$this->view->whiteBacking = true;
		
		
		$auth = Zend_Auth::getInstance();
		
		if (!empty($_COOKIE['user']) || $auth->hasIdentity()) {
			// User is logged in, instantiate change city form
			$this->view->changeCityForm = new Application_Form_ChangeCity();
		}
		
		
		return $view;
	}
	
	protected function _initLayoutSetup()
	{
		$this->bootstrap('layout');
		$this->bootstrap('db'); // Bootstrap db to allow use of Models below
		
		$layout = $this->getResource('layout');
		$view   = $this->getResource('view');
		
		$auth = Zend_Auth::getInstance();
		//$auth->clearIdentity();
		if (!empty($_COOKIE['user']) || $auth->hasIdentity()) {
			// User is logged in 
			if (!$auth->hasIdentity()) {
				// User object not saved, retrieve
				/* any call to $user here should be mimicked on login/auth controller/action */
				$user = new Application_Model_User();
				$user->getUserBy('u.userID',$_COOKIE['user']);
				$user->password = '';
				$user->getUserSportsInfo();
				$user->getUserGames();
				$user->getOldUserNotifications(); // Must be after get games, teams, and groups call
				$auth->getStorage()->write($user);
				
			}
			
			$headerLayout  = 'header/short';
			$user		   = $auth->getIdentity();
			
			$user->resetNewNotifications()
				 ->getNewUserNotifications();
				 
			// Renew user cookie
			setcookie('user', $user->userID, time() + (60*60*24*14), '/');

			
			if (!file_exists(PUBLIC_PATH . '/images/users/profile/pic/large/' . $user->userID . '.jpg')) {
				// No profile photo saved, set to default
				$user->photo = 'no_profile_male.jpg';
			} else {
				// Profile pic is saved
				$user->photo = $user->userID;
			}
			
			//$this->view->notifications    = $notifications;
			$this->view->user			  = $user;
			$this->view->headerSearchForm = new Application_Form_HeaderSearch();
			$this->view->loggedIn 		  = true;


		} else {
			// User is not logged in
			$headerLayout = 'header/tall';
		}
		$this->view->whiteBacking	  = true;
		
		// Set global layout
		$layout->setLayout('global/global');
		
		// Set header layout for login vs logout
		$view->headerLayout = $headerLayout;
		
		return $view;
		
	}
	

}

