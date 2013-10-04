<?php

class AboutController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
		$this->view->narrowColumn = 'right';
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
	
	public function privacyAction()
	{
		$this->view->narrowColumn = false;
	}
	
	public function termsAction()
	{
		$this->view->narrowColumn = false;
	}
	
	public function pictureAction()
	{
		/* why do we require profile pictures, answered */
		$this->_helper->layout()->disableLayout(); 
	}
	
	public function ratingsAction()
	{
		/* ratings explained */
		$this->_helper->layout()->disableLayout(); 
	}
	
	public function signupRatingsAction()
	{
		/* what ratings for signup explained */
		$this->_helper->layout()->disableLayout(); 
	}
	
}

