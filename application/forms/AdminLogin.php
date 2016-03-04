<?php
class Application_Form_AdminLogin extends Zend_Form
{

    public function init()
    {
        $this->setName('adminLogin');
		$this->setMethod('POST');
		$this->setAction('/admin/auth');
		$this->setDecorators(array('FormElements'));
		$this->setAttrib('enctype', 'multipart/form-data');
		
		$this->addElementPrefixPath('My_Form_Decorator',
									'My/Form/Decorator/',
									'decorator');
				
		$this->addElement('text', 'username', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'decorators'    => array('Overlay'),
				'label'			=> 'Username',
				'class'			=> 'clear'
				));
				
		$this->addElement('password', 'password', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'label'			=> 'Password',
				'decorators'    => array('Overlay'),
				'class'			=> 'clear'
				));
				
		$this->addElement('submit', 'submit', array(
				'filters'		=> array('StringTrim'),
				'required'		=> true,
				'label'			=> 'Login',
				'class'			=> 'clear-right',
				'autocomplete'  => 'off'
				));
    }


}
