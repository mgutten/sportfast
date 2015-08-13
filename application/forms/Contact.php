<?php
class Application_Form_Contact extends Zend_Form
{

    public function init()
    {
        $this->setName('contact');
		$this->setMethod('POST');
		$this->setAction('/mail/contact');
		$this->setDecorators(array('FormElements', 'Form'));
		$this->setAttrib('enctype', 'multipart/form-data');
		
		$this->addElementPrefixPath('My_Form_Decorator',
									'My/Form/Decorator/',
									'decorator');
		
								
		$this->addElement('text', 'email', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'validators'	=> array(array('emailAddress',true)),
				'label'			=> 'Your Email',
				'class'			=> 'dropshadow',
				'autocomplete'  => 'off'
				));

		$this->addElement('textarea', 'question', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'Question, comment, or a lovely haiku...',
				'class'			=> 'dropshadow',
				'autocomplete'  => 'off'
				));
				
		$this->addElement('hidden', 'browser', array(
				'filters'		=> array('StringTrim'),
				'autocomplete'  => 'off'
				));
				
		$this->addElement('submit', 'submit', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'validators'	=> array('alnum'),
				'label'			=> 'Send',
				'class'			=> 'button larger-text right',
				'autocomplete'  => 'off'
				));
				
				
	}
	
}
		