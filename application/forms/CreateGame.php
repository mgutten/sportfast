<?php
class Application_Form_CreateGame extends Zend_Form
{

    public function init()
    {
        $this->setName('createGame');
		$this->setMethod('POST');
		$this->setAction('/signup/validate');
		$this->setDecorators(array('FormElements', 'Form'));
		$this->setAttrib('enctype', 'multipart/form-data');
		
		$this->addElementPrefixPath('My_Form_Decorator',
									'My/Form/Decorator/',
									'decorator');
		
								
		$this->addElement('hidden', 'sport',  array(
				'required'		=> true,
				'decorators'    => array('Hidden')
				));
				
		$this->addElement('hidden', 'datetime',  array(
				'required'		=> true,
				'decorators'    => array('Hidden')
				));
		
		$this->addElement('hidden', 'parkID',  array(
				'required'		=> true,
				'decorators'    => array('Hidden')
				));
			
				
		$this->addElement('text', 'parkName', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> 'Location Name',
				'class'			=> 'dropshadow heavy',
				'autocomplete'  => 'off'
				));
				
		$this->addElement('text', 'minPlayers', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> '',
				'value'			=> '',
				'class'			=> 'dropshadow',
				'autocomplete'  => 'off'
				));
				
				
				
	}
}
