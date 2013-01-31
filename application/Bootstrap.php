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
		$layout = $this->bootstrap('layout')->getResource('layout');
		$layout->setLayout('global/logout');
	}
}

