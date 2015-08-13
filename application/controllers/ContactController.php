<?php

class ContactController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $this->view->narrowColumn = false;
		$this->view->whiteBacking = false;
		
		$form = new Application_Form_Contact();
		
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			$form->email->setValue($this->view->user->username);
		}
		
		$this->view->form = $form;
		
    }


}

