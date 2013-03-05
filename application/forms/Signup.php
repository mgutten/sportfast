<?php
class Application_Form_Signup extends Zend_Form
{

    public function init()
    {
        $this->setName('signupForm');
		$this->setMethod('POST');
		$this->setAction('/signup/validate');
		$this->setDecorators(array('FormElements', 'Form'));
		$this->setAttrib('enctype', 'multipart/form-data');
		
		$this->addElementPrefixPath('My_Form_Decorator',
									'My/Form/Decorator/',
									'decorator');
		
								
		$this->addElement('text', 'firstName', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'validators'	=> array('alnum'),
				'label'			=> 'First Name',
				'class'			=> 'dropshadow',
				'autocomplete'  => 'off'
				));
			
				
		$this->addElement('text', 'lastName', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'validators'	=> array(array('Regex', false, array('pattern' => '/^[a-zA-Z]+-*[a-zA-Z]*$/'))),
				'label'			=> 'Last Name',
				'class'			=> 'dropshadow',
				'autocomplete'  => 'off'
				));
				
		$this->addElement('hidden', 'sex',  array(
				'required'		=> true,
				'decorators'    => array('Hidden')
				));
		
		$this->addElement('text', 'dobMonth',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'validators'	=> array('digits'),
				'label'			=> 'mm',
				'class'			=> 'short-input dropshadow',
				'maxlength'		=> 2
				));
				
		$this->addElement('text', 'dobDay',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'validators'	=> array('digits'),
				'label'			=> 'dd',
				'class'			=> 'short-input dropshadow',
				'maxlength'		=> 2
				));
				
		$this->addElement('text', 'dobYear',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay','QuestionMark'),
				'validators'	=> array('digits'),
				'label'			=> 'yy',
				'class'			=> 'short-input dropshadow',
				'maxlength'		=> 2,
				'containerTooltip'	=> 'Date of birth:<ul><li>e.g. 07/25/90</li></ul>'
				));
				
		$this->addElement('hidden', 'age',  array(
				'validators'	=> array('digits'),
				'decorators'    => array('Hidden')
				));
		
		$this->addElement('text', 'heightFeet',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'validators'	=> array('digits'),
				'label'			=> 'ft',
				'class'			=> 'short-input dropshadow',
				'maxlength'		=> 1
				));
				
		$this->addElement('text', 'heightInches',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'validators'	=> array('digits'),
				'label'			=> 'in',
				'class'			=> 'short-input dropshadow',
				'maxlength'		=> 2
				));
				
		$this->addElement('hidden', 'height',  array(
				'validators'	=> array('digits'),
				'decorators'    => array('Hidden')
				));
				
		$this->addElement('text', 'weight',  array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'validators'	=> array('digits'),
				'decorators'	=> array('Overlay'),
				'label'			=> 'lb',
				'class'			=> 'short-input dropshadow',
				'maxlength'		=> 3
				));		
		
		$this->addElement('text', 'email', array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> true,
				'validators'	=> array(array('Db_NoRecordExists', true, array('table' => 'users', 
																				'field' => 'username', 
																				'messages' => array('recordFound' => 'This email has an account associated with it <a href="/forgot" class="underline red">Forgot password?</a>'))),
										 array('emailAddress',true)),
				'decorators'	=> array('Overlay','QuestionMark'),
				'label'			=> 'Username/Email',
				'class'			=> 'dropshadow',
				'containerTooltip'	=> 'Must be a valid email address: <br> <ul><li>e.g. johnsmith@gmail.com</li></ul>',
				'autocomplete'  => 'off'
				));
				
		$this->addElement('password', 'signupPassword', array(
				'filters'			=> array('StringTrim','StringToLower'),
				'required'			=> true,
				'decorators'		=> array('Overlay'),
				'label'				=> 'Password',
				'class'				=> 'dropshadow'
				));
				
		$this->addElement('text', 'streetAddress', array(
				'filters'			=> array('StringTrim','StringToLower'),
				'required'			=> true,
				'decorators'		=> array('Overlay'),
				'label'				=> 'Street Address <span class="medium smaller-text">number and street name</span>',
				'class'				=> 'dropshadow',
				'containerTooltip'	=> 'Must be a valid street address: <br> <ul><li>e.g. 710 E Blithedale Ave #10</li></ul>',
				'autocomplete'      => 'off'
				));
				
		$this->addElement('text', 'zipcode', array(
				'filters'			=> array('StringTrim','StringToLower'),
				'required'			=> true,
				'decorators'		=> array('Overlay','QuestionMark'),
				'validators'		=> array('digits'),
				'label'				=> 'Zip Code',
				'class'				=> 'dropshadow',
				'maxlength'			=> 5,
				'containerTooltip'	=> 'Must be a valid street address: <br> <ul><li>e.g. 35 Silvertree Dr #10, 95131</li></ul>'
				));
				
		$this->addElement('checkbox', 'noAddress',  array(
				'required'		=> false,
				'decorators'	=> array('Checkbox'),
				'checked'		=> false,
				'text'			=> 'I don\'t trust computers!  I do not wish to enter my street address.  <i>They might find me.</i> <br><span class="light">(Note: The system will not be able to find your best matches)</span>',
				'class'			=> 'medium'
				));
				
		$this->addElement('hidden', 'fileName',  array(
				'required'		=> false,
				'decorators'    => array('Hidden')
				));
				
		$this->addElement('hidden', 'fileX',  array(
				'required'		=> false,
				'decorators'    => array('Hidden')
				));
		
		$this->addElement('hidden', 'fileY',  array(
				'required'		=> false,
				'decorators'    => array('Hidden')
				));
				
		$this->addElement('hidden', 'fileWidth',  array(
				'required'		=> false,
				'decorators'    => array('Hidden')
				));
				
		$this->addElement('hidden', 'fileHeight',  array(
				'required'		=> false,
				'decorators'    => array('Hidden')
				));
				
		$this->addElement('checkbox', 'agree',  array(
				'required'		=> true,
				'decorators'	=> array('Checkbox'),
				'checked'		=> false,
				'text'			=> 'I have read and agree to both Sportup\'s Pledge and the <a href="/terms" class="underline medium">Terms and Conditions</a>.',
				'class'			=> 'darkest'
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
