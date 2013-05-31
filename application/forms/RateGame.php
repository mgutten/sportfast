<?php
class Application_Form_RateGame extends Zend_Form
{

    public function init()
    {
        $this->setName('rateGame');
		$this->setDecorators(array('FormElements'));
		$this->addElementPrefixPath('My_Form_Decorator',
									'My/Form/Decorator/',
									'decorator');
		
		$this->addElement('hidden', 'sport', array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> false,
				));
								
		$this->addElement('hidden', 'skill', array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> false,
				));
		
		$this->addElement('hidden', 'sportsmanship', array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> false,
				));
				
		$this->addElement('hidden', 'id', array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> false,
				));

		$this->addElement('textarea', 'comment', array(
				'filters'		=> array('StringTrim'),
				'required'		=> false,
				'decorators'	=> array('Overlay'),
				'label'			=> 'Comment <span class="light">optional</span>',
				'class'			=> 'rateGame-comment dropshadow',
				'autocomplete'  => 'off'
				));
				
    }


}
