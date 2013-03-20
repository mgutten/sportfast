<?php
class Application_Form_ChangeCity extends Zend_Form
{

    public function init()
    {
        $this->setName('changeCity');
		$this->setMethod('POST');
		$this->setAction('/');
		$this->setDecorators(array('FormElements'));
		$this->setAttrib('enctype', 'multipart/form-data');
		
		$this->addElementPrefixPath('My_Form_Decorator',
									'My/Form/Decorator/',
									'decorator');
		
				
		$this->addElement('text', 'changeCity', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'City, State',
				'class'			=> 'dropshadow city-change-input',
				'autocomplete'  => 'off'
				));
				
		$this->addElement('text', 'changeZipcode', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'validators'	=> array('alnum'),
				'label'			=> 'Zipcode',
				'class'			=> 'dropshadow city-change-input',
				'autocomplete'  => 'off'
				));
				
	}
}
