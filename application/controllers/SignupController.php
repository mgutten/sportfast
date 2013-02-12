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
		$form = new Application_Form_Signup();
        $this->view->form = $form;
    }


}

