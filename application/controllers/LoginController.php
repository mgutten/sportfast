<?php
//include PHPass
require_once(APPLICATION_PATH . "/../library/My/Auth/PasswordHash.php");

class LoginController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$this->view->whiteBacking = false;
        $this->view->test = $this->getRequest()->getPost('username');
		
		$form     = $this->getForm();
		$username = $form->getElement('username');
		$password = $form->getElement('password');
		$checkbox = $form->getElement('rememberMe');
		
		//change id of main login form to differ from dropdown
		$form    ->setAttrib('id','loginFormMain');
		$username->setAttrib('id','username-main')
				 ->setAttrib('class','dropshadow');
		$password->setAttrib('id','password-main')
				 ->setAttrib('class','dropshadow')
				 ->addDecorator('Errors');
		$checkbox->setAttrib('id','remember-me')
				 ->setAttrib('class','medium');
		
		//display username if failed attempt
		$session = new Zend_Session_Namespace('login');
		if ($session) {
			$username->setValue($session->username);
			Zend_Session::namespaceUnset('login');
		}
		
		$this->view->form = $form;
		
		//$error = $this->_helper->FlashMessenger->getMessages('error');

    }
	
	
	public function authAction()
	{
		$request = $this->getRequest();
		
		//check if POST data exists
		if (!$request->isPost()) {
			return $this->_helper->redirector('index');
		}
		
		$loginSession = new Zend_Session_Namespace('login');
		$loginSession->username = $request->getPost('username');
		
		$form = $this->getForm();
        if (!$form->isValid($request->getPost())) {
            // Did not pass validation...
            $error = $form->getMessages();
			if ($error['username']) {
				$error = array('Please enter your username.');
			} elseif ($error['password']) {
				$error = array('Incorrect password.');
			}
            $this->_helper->FlashMessenger->addMessage($error, 'error');
			return $this->_helper->redirector('index');
        }
 		
		// Get authentication adapter and check credentials
        $authAdapter = $this->_getAuthAdapter($request->getPost());														  					
		$auth = Zend_Auth::getInstance();
											  
        $result = $auth->authenticate($authAdapter);
        if (!$result->isValid()) {
            // Invalid username/password
            $error = $result->getMessages();
			$this->_helper->FlashMessenger->addMessage($error, 'error');
            return $this->_helper->redirector->goToUrl('/login');
        } else {
			// Authentication success
			Zend_Session::namespaceUnset('login');
			setcookie('user', $auth->getIdentity(), time() + 2000, '/');
			
			$this->_helper->redirector->goToUrl('/how');
		}
        
	}
	
	public function testAction()
	{
		$username = 'guttenberg.m@gmail.com';
		$password = 'Westberg.7';
		$hasher   = new My_Auth_PasswordHash(8, false);
		$password = $hasher->HashPassword($password);
		
		$user = new Application_Model_User();
		$user->username = $username;
		$user->password = $password;
		//$user->save();
		/*
		$users = new Application_Model_Users();
		$results = $users->getUserBy('username','guttenberg.m@gmail.com');
		var_dump($results->username);
		*/
		
	}
	
	
	public function getForm()
	{
		return new Application_Form_Login();
	}
	
    protected function _getAuthAdapter(array $params)
    {
        return new My_Auth_Adapter(
				$params['username'],
				$params['password'],
				new Application_Model_Users()
        );

    }


}

