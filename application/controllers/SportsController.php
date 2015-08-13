<?php

class SportsController extends Zend_Controller_Action
{
	
	public function init()
	{
		$sports = new Application_Model_Sports();
		$this->view->sports = $sports->getAllSportsInfo(true)->sports;
		
		$this->view->sport = strtolower($this->getRequest()->getActionName());
	}

    public function indexAction()
    {
		$this->view->narrowColumn = false;
		$this->view->whiteBacking = false;
		

    }
	
	public function basketballAction()
	{
	}
	
	public function footballAction()
	{
	}
	
	public function soccerAction()
	{
	}
	
	public function ultimateAction()
	{
	}
	
	public function tennisAction()
	{
	}
	
	public function volleyballAction()
	{
	}


}

