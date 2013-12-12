<?php
class Application_Form_Invite extends Zend_Form
{

    public function init()
    {
        $this->setName('inviteForm');
		$this->setMethod('POST');
		$this->setAction('');
		$this->setDecorators(array('FormElements', 'Form'));
		$this->setAttrib('enctype', 'multipart/form-data');
		
		$this->addElementPrefixPath('My_Form_Decorator',
									'My/Form/Decorator/',
									'decorator');
		
								
		$this->addElement('textarea', 'note', array(
				'required'		=> false,
				'decorators'	=> array('Overlay'),
				'label'			=> 'Write note...',
				'class'			=> 'dropshadow',
				'autocomplete'  => 'off'
				));
				
		$this->addElement('textarea', 'emailsTextArea', array(
				'required'		=> false,
				'decorators'	=> array('Overlay'),
				'label'			=> '<span class="heavy medium larger-text">Copy and paste emails</span>',
				'class'			=> 'emails',
				'id'			=> 'profile-invite-emails',
				'autocomplete'  => 'off'
				));
				
		$this->addElement('submit', 'submit', array(
				'label'			=> '',
				'class'			=> '',
				'autocomplete'  => 'off'
				));
				
		$this->addElement('hidden', 'emails', array(
				'label'			=> '',
				'class'			=> '',
				'autocomplete'  => 'off'
				));
	}
	
}
