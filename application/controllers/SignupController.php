<?php

class SignupController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$this->view->narrowColumn = 'right';
		// Create general signup form
		$form = new Application_Form_Signup();
        $this->view->form = $form;
		
		// Create hidden element form
		$sportForm = new Application_Form_SignupSportForm();
		$this->view->signupSportForm = $sportForm;
		
		// Retrieve all available sports, positions, and types
		$sports = new Application_Model_Sports();
		$this->view->sports = $sports->getAllSportsInfo();
		
		
    }
	
	public function testAction()
	{
		/* HOW TO CREATE A DROPDOWN */
        $dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		$this->view->dropdown = $dropdown->dropdown('cat','Volleyball',array(array('text'  => 'Basketball',
																				   'image' => '/images/global/sports/icons/small/basketball.png',
																				   'color' => 'medium'),
																			 'Football'));
	}


}

