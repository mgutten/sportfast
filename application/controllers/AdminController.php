<?php

class AdminController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function preDispatch()
	{
		$this->view->narrowColumn = false;
		$request = $this->getRequest();
		$action  = $request->getActionName();
		
		if ($action != 'index' &&
			$action != 'auth') {
			$session = new Zend_Session_Namespace('admin');
			
			if (!$session->auth) {
				return $this->_forward('permission', 'error', null);
			}
		}
	}

    public function indexAction()
    {
        // action body
		$session = new Zend_Session_Namespace('admin');
		
		if ($session->auth) {
			// Approved auth
			
		} else {
			// Not approved, show login
			$this->view->loginForm = new Application_Form_AdminLogin();
			
			$admin = new Application_Model_Admin();
		}
    }
	
	public function authAction()
	{
		
		$form = new Application_Form_AdminLogin();
		$post = $this->getRequest()->getPost();
		
		if ($form->isValid($post)) {
			// Valid username
			$admin = new Application_Model_Admin();
			
			if ($admin->login($post['username'], $post['password'])) {
				// Login successful
				$session = new Zend_Session_Namespace('admin');
				$session->auth = $admin;
			}
			
		}
		
		$this->_redirect('/admin');
	}
	
	public function logoutAction()
	{
		$session = new Zend_Session_Namespace('admin');
		$session->auth = false;
		
		$this->_redirect('/admin');
	}
	
	public function userMapAction()
	{
		$this->backHome();
		$users = new Application_Model_Users();
		
		$users->getAllUserLocations();
		
		$this->view->users = $users->getAll();
		
		$stats = $users->getAllUsersStats();
		
		$this->view->stats = $stats;
	}
		
	public function citiesAction()
	{	
		$this->backHome();
		$mapper = new Application_Model_AdminMapper();
		
		$cities = $mapper->getCityData();
		
		$this->view->cities = $cities;
	}
	
	public function flaggedAction()
	{
		$this->backHome();
		$mapper = new Application_Model_AdminMapper();
			
		$flagged = $mapper->getFlaggedRatings();
		
		$this->view->flagged = $flagged;
	}
	
	public function backHome()
	{
		echo "<a href='/admin' class='clear-right medium larger-margin-top'>home</a>";
	}
		


}

