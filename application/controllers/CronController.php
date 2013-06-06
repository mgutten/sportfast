<?php

class CronController extends Zend_Controller_Action
{
	protected $_password = 'gKgjsjGZx9';
	
    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function preDispatch()
	{
		$pass = $this->getRequest()->getParam('pass');

		$this->testPassword($pass);
	}

    public function indexAction()
    {
        
    }
	
	public function moveTypeToOldAction()
	{
		$mapper = new Application_Model_CronMapper();
		
		$mapper->moveGamesToOld();
		$mapper->moveTeamsToOld();
	}
	
	public function testPassword($password)
	{
		if ($password !== $this->_password) {
			return $this->_forward('permission', 'error', null);
		}
	}

}

