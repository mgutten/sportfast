<?php
class Application_Form_HeaderSearch extends Zend_Form
{

    public function init()
    {
        $this->setName('headerSearchForm');
		$this->setMethod('POST');
		$this->setAction('/find/search-term');
		$this->setDecorators(array('FormElements', 'Form'));
		
		$this->addElementPrefixPath('My_Form_Decorator',
									'My/Form/Decorator/',
									'decorator');
		
								
		$this->addElement('text', 'headerSearchBar', array(
				'filters'		=> array('StringTrim'),
				'required'		=> false,
				'decorators'	=> array('Overlay'),
				'validators'	=> array('alnum'),
				'label'			=> 'player name, team, etc...',
				'class'			=> 'header-search-bar',
				'autocomplete'  => 'off'
				));
			

														
		$this->addElement('image', 'search', array(
				'src'			=> '/images/global/header/header_magnifying.png',
				'required' 		=> false,
				'ignore'   		=> true,
				'decorators'	=> array('ViewHelper'),
				'label'   		=> 'Login',
				'class'			=> 'header-search-magnifying'

        ));  	
    }


}
