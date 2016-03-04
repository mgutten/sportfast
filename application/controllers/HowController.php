<?php

class HowController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$this->view->narrowColumn = false;
		$sports = new Application_Model_Sports();
		
		$this->view->sports = $sports->getAllSportsInfo(true);
		
    }


}

