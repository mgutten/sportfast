<?php

class CreateController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $this->view->whiteBacking = false;
		$this->view->narrowColumn = false;
    }
	
	public function gameAction()
	{
		$sports = new Application_Model_Sports();
		$this->view->sports = $sports->getAllSportsInfo(true);
		
		$parks = new Application_Model_Parks();
		$parks->findParks(array(), $this->view->user);
		
		$this->view->parks = $parks->getAll();
		
		$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		
		$this->view->hourDropdown = $dropdown->dropdown('hour',range(1,12),'1');
		$this->view->minDropdown = $dropdown->dropdown('min',array('00','15','30','45'),'00');
		$this->view->ampmDropdown = $dropdown->dropdown('ampm',array('am','pm'),'pm', false);
		
		$form = $this->view->form = new Application_Form_CreateGame();
		
		

	}


}

