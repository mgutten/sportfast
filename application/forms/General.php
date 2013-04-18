<?php
class Application_Form_General extends Zend_Form
{

    public function init()
    {
        $this->setName('generalForm');
		$this->setMethod('POST');
		$this->setAction('');
		$this->setDecorators(array('FormElements', 'Form'));
		$this->setAttrib('enctype', 'multipart/form-data');
		
		$this->addElementPrefixPath('My_Form_Decorator',
									'My/Form/Decorator/',
									'decorator');
		
								
		$this->addElement('text', 'text', array(
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> '',
				'class'			=> 'dropshadow',
				'autocomplete'  => 'off'
				));
				
		$this->addElement('checkbox', 'checkbox',  array(
				'required'		=> false,
				'decorators'	=> array('Checkbox'),
				'checked'		=> false,
				'class'			=> 'medium'
				));
	}
	
}
