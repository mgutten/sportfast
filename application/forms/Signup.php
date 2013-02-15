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
				'maxlength'		=> 2
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
				'decorators'	=> array('Overlay','QuestionMark'),
				'label'			=> 'yy',
				'class'			=> 'short-input dropshadow',
				'maxlength'		=> 2,
				'containerTooltip'	=> 'Date of birth:<ul><li>e.g. 07/25/90</li></ul>'
				));
		
		$this->addElement('text', 'heightFeet',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'ft',
				'class'			=> 'short-input dropshadow',
				'maxlength'		=> 1
				));
				
		$this->addElement('text', 'heightInches',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'in',
				'class'			=> 'short-input dropshadow',
				'maxlength'		=> 2
				));
				
		$this->addElement('text', 'weight',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay','Errors'),
				'label'			=> 'lb',
				'class'			=> 'short-input dropshadow',
				'maxlength'		=> 3
				));		
		
		$this->addElement('text', 'email', array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay','QuestionMark'),
				'label'			=> 'Username/Email',
				'class'			=> 'dropshadow',
				'containerTooltip'	=> 'Must be a valid email address: <br> <ul><li>e.g. johnsmith@gmail.com</li></ul>'
				));
				
		$this->addElement('password', 'signupPassword', array(
				'filters'			=> array('StringTrim','StringToLower'),
				'required'			=> true,
				'decorators'		=> array('Overlay'),
				'label'				=> 'Password',
				'class'				=> 'dropshadow'
				));
				
		$this->addElement('text', 'streetAddress', array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'Street Address',
				'class'			=> 'dropshadow',
				'containerTooltip'	=> 'Must be a valid street address: <br> <ul><li>e.g. 710 E Blithedale Ave #10</li></ul>'
				));
				
		$this->addElement('text', 'city', array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay','QuestionMark'),
				'label'			=> 'City, State',
				'class'			=> 'dropshadow',
				'containerTooltip'	=> 'Must be a valid street address: <br> <ul><li>e.g. 710 E Blithedale Ave #10 San Jose, CA</li></ul>'
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
