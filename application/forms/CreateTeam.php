<?php
class Application_Form_CreateTeam extends Zend_Form
{

    public function init()
    {
        $this->setName('createTeam');
		$this->setMethod('POST');
		$this->setAction('/create/validateteam');
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
								
		$this->addElement('hidden', 'visibility',  array(
				'required'		=> true,
				'decorators'    => array('Hidden'),
				'value'			=> 'public'
				));
		
		$this->addElement('hidden', 'avatar',  array(
				'required'		=> true,
				'decorators'    => array('Hidden')
				));
				
				
		$this->addElement('text', 'teamName', array(
				'filters'		=> array('StringTrim'),
				'required'		=> false,
				'decorators'	=> array('Overlay'),
				'label'			=> 'Team Name',
				'class'			=> 'dropshadow heavy',
				'autocomplete'  => 'off',
				'maxlength'		=> 26
				));
				
		$this->addElement('text', 'otherSport', array(
				'filters'		=> array('StringTrim'),
				'required'		=> false,
				'decorators'	=> array('Overlay'),
				'label'			=> 'What sport?',
				'class'			=> 'dropshadow heavy',
				'autocomplete'  => 'off',
				'maxlength'		=> 40
				));
		
		$detailsClass = 'create-input';	
				
		$this->addElement('text', 'rosterLimit', array(
				'filters'		=> array('StringTrim'),
				'required'		=> false,
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
				'text'			=> 'Skill Limit',
				'class'			=> 'darkest'
				));
				
				
				
	}
}
