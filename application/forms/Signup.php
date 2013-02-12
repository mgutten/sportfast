<?php
class Application_Form_Signup extends Zend_Form
{

    public function init()
    {
        $this->setName('signupForm');
		$this->setMethod('POST');
		$this->setAction('/signup');
		$this->setDecorators(array('FormElements', 'Form'));
		
		$this->addElementPrefixPath('My_Form_Decorator',
									'My/Form/Decorator/',
									'decorator');
		
								
		$this->addElement('text', 'firstName', array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'First Name',
				'class'			=> 'dropshadow'
				));
			
				
		$this->addElement('text', 'lastName', array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'Last Name',
				'class'			=> 'dropshadow'
				));
				
		$this->addElement('hidden', 'sex',  array(
				'required'		=> true,
				));
		
		$this->addElement('text', 'dobMonth',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'mm',
				'class'			=> 'short-input dropshadow',
				'maxlength'		=> 2,
				'containerTooltip'	=> 'Date of birth:<br> e.g. 07/25/90'
				));
				
		$this->addElement('text', 'dobDay',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'dd',
				'class'			=> 'short-input dropshadow',
				'maxlength'		=> 2
				));
				
		$this->addElement('text', 'dobYear',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'yy',
				'class'			=> 'short-input dropshadow',
				'maxlength'		=> 2
				));
		
		$this->addElement('text', 'heightFeet',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'ft',
				'class'			=> 'short-input dropshadow'
				));
				
		$this->addElement('text', 'heightInches',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'in',
				'class'			=> 'short-input dropshadow'
				));
				
		$this->addElement('text', 'weight',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay','Errors'),
				'label'			=> 'lb',
				'class'			=> 'short-input dropshadow'
				));		
		
		$this->addElement('text', 'email', array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'Username/Email',
				'class'			=> 'dropshadow',
				'containerTooltip'	=> 'Must be a valid email address: <br> <ul><li>e.g. johnsmith@gmail.com</li></ul>'
				));
				
		$this->addElement('password', 'signupPassword', array(
				'filters'			=> array('StringTrim','StringToLower'),
				'required'			=> true,
				'decorators'		=> array('Overlay'),
				'label'				=> 'Password',
				'class'				=> 'dropshadow',
				'containerTooltip'	=> 'Password must be:<br><ul><li>6-12 characters long</li></ul></span>'
				));
														
		$this->addElement('image', 'login', array(
				'src'			=> '',
				'required' 		=> false,
				'ignore'   		=> true,
				'decorators'	=> array('ViewHelper'),
				'label'   		=> 'Login',
				'class'			=> 'dropdown-login-submit'

        ));  	
    }


}
