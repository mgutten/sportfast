<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initMyActionHelpers()
    {
        $this->bootstrap('frontController');
        $login = Zend_Controller_Action_HelperBroker::getStaticHelper('LoginForm');
        Zend_Controller_Action_HelperBroker::addHelper($login);

    }
	
	protected function _initLayoutSetup()
	{
		$this->bootstrap('layout');
		$this->bootstrap('view');
		$layout = $this->getResource('layout');
		$view = $this->getResource('view');
		
		$auth = Zend_Auth::getInstance();
		
		if (!empty($_COOKIE['user']) || $auth->hasIdentity()) {
			//user is logged in (CHANGE TO "short" WHEN READY TO DEVELOP LOGIN PAGES)
			$headerLayout = 'header/short';
		} else {
			$headerLayout = 'header/tall';
		}
		
		//set global layout
		$layout->setLayout('global/global');
		
		//set header layout for login vs logout
		$view->headerLayout = $headerLayout;
		
	}
	
	protected function _initVars()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
		/* default to white back for page */
		$view->whiteBacking = true;
		
		return $view;
	}
	
	/**
	* initialize all standard view vars
	*/
}

