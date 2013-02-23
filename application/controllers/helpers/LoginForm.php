<?php



class Application_Controller_Helper_LoginForm extends Zend_Controller_Action_Helper_Abstract

{

    public function preDispatch()

    {
        $view = $this->getActionController()->view;
        $form = new Application_Form_Login();

        $view->dropdownLoginForm = $form;
    }

}