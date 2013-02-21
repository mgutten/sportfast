<?php
class Application_Form_SignupSportForm extends Zend_Form
{

    public function init()
    {
        $this->setName('signupSportForm');
		$this->setDecorators(array('FormElements'));
		
								
		$this->addElement('hidden', 'sport', array(
				'filters'		=> array('StringTrim','StringToLower'),
				'required'		=> false,
				));
    }


}
