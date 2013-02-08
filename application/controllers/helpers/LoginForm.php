<?php



class Application_Controller_Helper_LoginForm extends Zend_Controller_Action_Helper_Abstract

{

    public function preDispatch()

    {
        $view = $this->getActionController()->view;
        $form = new Application_Form_Login();

		/*
        $request = $this->getActionController()->getRequest();
        if($request->isPost() && $request->getPost('submitlogin')) {
            if($form->isValid($request->getPost())) {
                $data = $form->getValues();
                // process data
                $form->processed = true;
            }
        }
		*/
        $view->dropdownLoginForm = $form;
    }

}