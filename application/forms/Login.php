<?php

class Application_Form_Login extends Zend_Form
{

    public function init()
    {
        $this->setName('loginForm');
		$this->setMethod('POST');
		$this->setAction('/login/auth');
		$this->setDecorators(array('FormElements', 'Form'));
		
		$this->addElementPrefixPath('My_Form_Decorator',
									'My/Form/Decorator/',
									'decorator');
		
								
		$this->addElement('text', 'username', array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'Username/Email'
				));
			
				
		$this->addElement('password', 'password', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'value'			=> 'Password',
				'decorators'	=> array('Overlay'),
				'label'			=> 'Password'
				));
		
		$this->addElement('checkbox', 'rememberMe',  array(
				'required'		=> false,
				'value'			=> true,
				'decorators'	=> array('Checkbox','LoginDropdownCheckbox'),
				'checked'		=> true,
				'text'			=> 'Remember me',
				'class'			=> 'light'
				));
						
		$this->addElement('submit', 'login', array(
				'src'			=> '',
				'required' 		=> false,
				'ignore'   		=> true,
				'decorators'	=> array('ViewHelper'),
				'label'   		=> 'Login',
				'class'			=> 'dropdown-login-submit button'

        ));  	
    }


}

