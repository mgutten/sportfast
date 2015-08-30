<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	
	protected function _initRouterSetup()
	{
		$this->bootstrap('frontController');
		
		$front = Zend_Controller_Front::getInstance();
		$front->registerPlugin(new My_Plugin_Authorization());
		
	}
			
	
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
		
		
		$reset = new Zend_Session_Namespace('reset');
		if ($reset->reset) {
			// Reset attrib has been set, clear identity and force reload
			$auth->clearIdentity();
			Zend_Session::namespaceUnset('reset');
		}
		
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
			
			// Reset cookie
			setcookie('user', $user->userID, time() + (60*60*24*40), '/');
			
			$session = new Zend_Session_Namespace('active');
			if (!isset($session->active)) {
				// Only update user "lastActive" attrib once per session (save on db updates)
				$session->active = true;
				
				$this->view->lastActive = $user->lastActive;

				$user->setLastActiveCurrent()
				 	 ->save(false);
			} elseif (time() - strtotime($user->lastActive) > (60*2)) {
				// Every 2 minutes reload user's info (games, teams, etc) to keep it up to date
				$user->setLastActiveCurrent();
				$user->getUserInfo();
			}
			
			$session = new Zend_Session_Namespace('rating');
			if (!isset($session->rating)) {
				// Only show ratings popups once per session
				$game = $user->getLastGame();
				if ($game) {
					if ($game->players->hasValue('users')) {
						// Game happened in the last week that user played in, make rate 2 users or park and user
						$this->view->rateGame = $game;
						
						$sport = new Application_Model_Sport();
						$sport->sportID = $game->sportID;
						$sport->sport   = $game->sport;
						$this->view->rateGameSkills = $sport->getSkills();
						
						$ratings = new Application_Model_Ratings();
						$this->view->rateGameDescriptions = $ratings->getAvailableRatings('user','skill');
						
						$form = new Application_Form_RateGame();
						$form->sport->setValue($game->sport);
						$this->view->rateGameForm = $form;
						
					}
				}
			}
			$session->rating = true;
			
				
			
			$user->resetNewNotifications()
				 ->getNewUserNotifications();
			
			// Count new messages for cog dropdown "inbox" section
			$messages = new Application_Model_Messages();
			$this->view->countNewMessages = $messages->countNewUserMessages($user->userID);
				 
			// Renew user cookie
			setcookie('user', $user->userID, time() + (60*60*24*14), '/');

			/*
			if (!file_exists(PUBLIC_PATH . '/images/users/profile/pic/large/' . $user->userID . '.jpg')) {
				// No profile photo saved, set to default
				$user->photo = 'no_profile_male.jpg';
			} else {
				// Profile pic is saved
				$user->photo = $user->userID;
			}
			*/
			
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
			
			$sports = $view->user->getSportNames();
			$sport = array_shift($sports); // Get first sport from user as default
		} else {
			$id = 1;
		}
		
		$frontController = Zend_Controller_Front::getInstance(); 
		$router = $frontController->getRouter();
		
		// Users page
		$r = new Zend_Controller_Router_Route_Regex(
				'users(?:/(\d+))?(?:/(\w+))?(?:/(\w+))?',
				array(
						'action' => 'index',
						'controller' => 'users',
						'module' => 'default',
						'id'	 => $id,
						'sport'  => ''
				),
				array(
						1 => 'id',
						2 => 'action',
						3 => 'sport'
				),
				'users/%d/%w');
				
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
				'games(?:/(\d+))?(?:/([a-z]+[-+[a-z]+]?))?(?:/(.+))?',
				array(
						'action' => 'index',
						'controller' => 'games',
						'module' => 'default',
						'id'	 => '1',
						'param2' => '1'
				),
				array(
						1 => 'id',
						2 => 'action',
						3 => 'param2'
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
		
		// Find page
		$r = new Zend_Controller_Router_Route_Regex(
				'find/search(?:/(.*))?',
				array(
						'action' => 'search',
						'controller' => 'find',
						'module' => 'default',
						'id'	 => '1'
				),
				array(
						1 => 'search'
				),
				'find/search/%s');
				
		$router->addRoute('search', $r);
		
		// Signup page
		$r = new Zend_Controller_Router_Route_Regex(
				'signup/verify(?:/(.*))?',
				array(
						'action' => 'verify',
						'controller' => 'signup',
						'module' => 'default',
						'verifyHash' => ''
				),
				array(
						1 => 'verifyHash'
				),
				'signup/verify/%s');
				
		$router->addRoute('verify', $r);
		
		
		// Cron jobs
		$r = new Zend_Controller_Router_Route_Regex(
				'cron(?:/([a-z-]*))?(?:/(.*))?',
				array(
						'action' => 'index',
						'controller' => 'cron',
						'module' => 'default',
						'pass'   => ''
				),
				array(
						1 => 'action',
						2 => 'pass'
				),
				'cron/%s/%s');
				
		$router->addRoute('cron', $r);
		
		
		// Games page
		$r = new Zend_Controller_Router_Route_Regex(
				'mail(?:/([a-z]+[-+[a-z]+]?))?(?:/(\d+))?(?:/(\d+))?(?:/(.+))?',
				array(
						'action' => 'index',
						'controller' => 'mail',
						'module' => 'default',
						'id'	 => '1',
						'param2' => '1',
						'param3' => '1'
				),
				array(
						1 => 'action',
						2 => 'id',
						3 => 'param2',
						4 => 'param3'
				),
				'mail/%s');
				
		$router->addRoute('mail', $r);
		
		return $view;
	}
	

}

