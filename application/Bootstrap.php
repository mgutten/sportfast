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
		$layout->setLayout('global/logout');

		
	}
	
	protected function _initVars()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->whiteBacking = true;
		
		return $view;
	}
	
	/**
	* initialize all standard view vars
	*/
}

