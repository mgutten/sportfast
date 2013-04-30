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
				$user->login();
				// Must be after get games, teams, and groups call
				$auth->getStorage()->write($user);
			}
			
			$headerLayout  = 'header/short';
			$user		   = $auth->getIdentity();
			
			
			$session = new Zend_Session_Namespace('active');
			if (!isset($session->active)) {
				// Only update user "lastActive" attrib once per session (save on db updates)
				$session->active = true;

				$user->setLastActiveCurrent()
				 	 ->save(false);
			} elseif (time() - strtotime($user->lastActive) > (60*2)) {
				// Every 2 minutes reload user's info (games, teams, etc) to keep it up to date
				$user->setLastActiveCurrent();
				$user->getUserInfo();
			}
			
			$user->resetNewNotifications()
				 ->getNewUserNotifications();
			
			// Count new messages for cog dropdown "inbox" section
			$messages = new Application_Model_Messages();
			$this->view->countNewMessages = $messages->countNewUserMessages($user->userID);
				 
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
			$this->view->narrowColumn 	  = 'right';

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
	
	protected function _initRoutes()
	{
		$view   = $this->getResource('view');
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			$id = $view->user->userID;
		} else {
			$id = 1;
		}
		
		$frontController = Zend_Controller_Front::getInstance(); 
		$router = $frontController->getRouter();
		
		// Users page
		$r = new Zend_Controller_Router_Route_Regex(
				'users(?:/(\d+))?(?:/(\w+))?',
				array(
						'action' => 'index',
						'controller' => 'users',
						'module' => 'default',
						'id'	 => $id
				),
				array(
						1 => 'id',
						2 => 'action'
				),
				'users/%d');
				
		$router->addRoute('users', $r);
		
		// Teams page
		$r = new Zend_Controller_Router_Route_Regex(
				'teams(?:/(\d+))?(?:/(\w+))?',
				array(
						'action' => 'index',
						'controller' => 'teams',
						'module' => 'default',
						'id'	 => '1'
				),
				array(
						1 => 'id',
						2 => 'action'
				),
				'teams/%d');
				
		$router->addRoute('teams', $r);
		
		/*
		// Groups page
		$r = new Zend_Controller_Router_Route_Regex(
				'groups(?:/(\d+))?(?:/(\w+))?',
				array(
						'action' => 'index',
						'controller' => 'groups',
						'module' => 'default',
						'id'	 => '1'
				),
				array(
						1 => 'id',
						2 => 'action'
				),
				'groups/%d');
				
		$router->addRoute('groups', $r);
		*/
		
		// Games page
		$r = new Zend_Controller_Router_Route_Regex(
				'games(?:/(\d+))?(?:/(\w+))?',
				array(
						'action' => 'index',
						'controller' => 'games',
						'module' => 'default',
						'id'	 => '1'
				),
				array(
						1 => 'id',
						2 => 'action'
				),
				'games/%d');
				
		$router->addRoute('games', $r);
		
		// Parks page
		$r = new Zend_Controller_Router_Route_Regex(
				'parks(?:/(\d+))?(?:/(\w+))?',
				array(
						'action' => 'index',
						'controller' => 'parks',
						'module' => 'default',
						'id'	 => '1'
				),
				array(
						1 => 'id',
						2 => 'action'
				),
				'parks/%d');
				
		$router->addRoute('parks', $r);
		
		
		return $view;
	}
	

}

