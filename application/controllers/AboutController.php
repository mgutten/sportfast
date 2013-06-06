<?php

class AboutController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
	
	public function faqAction()
	{
		$this->view->narrowColumn = 'right';
	}

	public function pickupAction()
	{
		$this->view->narrowColumn = false;
		
		$sports = new Application_Model_Sports();
		
		$this->view->sports = $sports->getAllSportsInfo();
	}
	
	public function leaguesAction()
	{
		$this->view->narrowColumn = false;
		
		$sports = new Application_Model_Sports();
		
		$this->view->sports = $sports->getAllSportsInfo();
	}
	
	public function jobsAction()
	{
		$this->view->narrowColumn = false;
	}
}

