<?php
class Application_Form_ForgotPassword extends Zend_Form
{

    public function init()
    {
        $this->setName('forgotPassword');
		$this->setMethod('POST');
		$this->setAction('/mail/forgot');
		$this->setDecorators(array('FormElements', 'Form'));
		$this->setAttrib('enctype', 'multipart/form-data');
		
		$this->addElementPrefixPath('My_Form_Decorator',
									'My/Form/Decorator/',
									'decorator');
		
								
		$this->addElement('text', 'email', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'Username/Email',
				'class'			=> 'dropshadow',
				'autocomplete'  => 'off'
				));
				
				
		$this->addElement('submit', 'submit', array(
				'required' 		=> false,
				'ignore'   		=> true,
				'decorators'	=> array('ViewHelper'),
				'label'   		=> 'Submit',
				'class'			=> 'button larger-text'

        ));  
				
	}
	
}
