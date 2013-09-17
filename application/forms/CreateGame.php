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
				
		$this->addElement('hidden', 'sportID',  array(
				'required'		=> false,
				'decorators'    => array('Hidden')
				));
				
		$this->addElement('hidden', 'typeName',  array(
				'required'		=> false,
				'decorators'    => array('Hidden')
				));
				
		$this->addElement('hidden', 'typeSuffix',  array(
				'required'		=> false,
				'decorators'    => array('Hidden')
				));
				
		$this->addElement('hidden', 'datetime',  array(
				'required'		=> true,
				'decorators'    => array('Hidden')
				));
		
		$this->addElement('hidden', 'parkID',  array(
				'required'		=> false,
				'decorators'    => array('Hidden')
				));
				
		$this->addElement('hidden', 'parkNameHidden',  array(
				'required'		=> true,
				'decorators'    => array('Hidden')
				));
				
		$this->addElement('hidden', 'visibility',  array(
				'required'		=> true,
				'decorators'    => array('Hidden'),
				'value'			=> 'public'
				));
				
		$this->addElement('hidden', 'recurring',  array(
				'required'		=> true,
				'decorators'    => array('Hidden'),
				'value'			=> 'no'
				));
			
		$this->addElement('hidden', 'parkLocation',  array(
				'required'		=> false,
				'decorators'    => array('Hidden'),
				'value'			=> ''
				));
				
		$this->addElement('hidden', 'skillLimitMin',  array(
				'required'		=> false,
				'decorators'    => array('Hidden'),
				'value'			=> 60
				));
				
		$this->addElement('hidden', 'skillLimitMax',  array(
				'required'		=> false,
				'decorators'    => array('Hidden'),
				'value'			=> 100
				));
				
		$this->addElement('text', 'parkName', array(
				'filters'		=> array('StringTrim'),
				'required'		=> false,
				'decorators'	=> array('Overlay'),
				'label'			=> 'Location Name',
				'class'			=> 'dropshadow heavy',
				'autocomplete'  => 'off'
				));
		
		$detailsClass = 'create-input';	
		$this->addElement('text', 'minPlayers', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> '',
				'value'			=> '',
				'class'			=> 'dropshadow ' . $detailsClass,
				'tooltip'		=> 'How many players are needed in order for the game to happen.',
				'autocomplete'  => 'off'
				));
				
		$this->addElement('text', 'rosterLimit', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'decorators'	=> array('Overlay'),
				'label'			=> '',
				'value'			=> '',
				'class'			=> 'dropshadow ' . $detailsClass,
				'tooltip'		=> 'Maximum number of players allowed.',
				'autocomplete'  => 'off'
				));
				
		$this->addElement('text', 'ageLimitMin', array(
				'filters'		=> array('StringTrim'),
				'required'		=> false,
				'decorators'	=> array('Overlay'),
				'label'			=> 'min',
				'value'			=> '',
				'class'			=> 'dropshadow ' . $detailsClass,
				'autocomplete'  => 'off'
				));
				
		$this->addElement('text', 'ageLimitMax', array(
				'filters'		=> array('StringTrim'),
				'required'		=> false,
				'decorators'	=> array('Overlay'),
				'label'			=> 'max',
				'value'			=> '',
				'class'			=> 'dropshadow ' . $detailsClass,
				'autocomplete'  => 'off'
				));
		
		/*
		$this->addElement('text', 'skillLimitMin', array(
				'filters'		=> array('StringTrim'),
				'required'		=> false,
				'decorators'	=> array('Overlay'),
				'label'			=> 'min',
				'value'			=> '',
				'class'			=> 'dropshadow ' . $detailsClass,
				'autocomplete'  => 'off'
				));
				
		$this->addElement('text', 'skillLimitMax', array(
				'filters'		=> array('StringTrim'),
				'required'		=> false,
				'decorators'	=> array('Overlay'),
				'label'			=> 'max',
				'value'			=> '',
				'class'			=> 'dropshadow ' . $detailsClass,
				'autocomplete'  => 'off'
				));
		*/
	
		$this->addElement('checkbox', 'ageLimitCheckbox',  array(
				'required'		=> false,
				'decorators'	=> array('Checkbox'),
				'checked'		=> false,
				'text'			=> 'Age Limit',
				'class'			=> 'darkest'
				));
				
		$this->addElement('checkbox', 'skillLimitCheckbox',  array(
				'required'		=> false,
				'decorators'	=> array('Checkbox'),
				'checked'		=> false,
				'text'			=> 'Skill Limit <span class="medium" tooltip="<span class=\'left inherit\'>64 = beginner</span><span class=\'clear inherit\'>100 = extremely talented</span>">?</span>',
				'class'			=> 'darkest'
				));
				
				
				
	}
}
