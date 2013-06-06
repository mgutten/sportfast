<?php

class SportsController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$this->view->narrowColumn = false;
		$this->view->whiteBacking = false;
		
        $sports = new Application_Model_Sports();
		$this->view->sports = $sports->getAllSportsInfo(true)->sports;

    }


}

