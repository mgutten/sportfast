<?php
class Application_Form_PostMessage extends Zend_Form
{

    public function init()
    {
        $this->setName('postMessage');
		$this->setMethod('POST');
		$this->setAction('/post');
		$this->setDecorators(array('FormElements', 'Form'));
		
		$this->addElementPrefixPath('My_Form_Decorator',
									'My/Form/Decorator/',
									'decorator');
		
								
		$this->addElement('textarea', 'postMessage', array(
				'filters'		=> array('StringTrim'),
				'required'		=> false,
				'decorators'	=> array('Overlay'),
				'label'			=> 'Write something...',
				'class'			=> 'post-message',
				'autocomplete'  => 'off'
				));
				
		$this->addElement('submit', 'login', array(
				'required' 		=> false,
				'ignore'   		=> true,
				'decorators'	=> array('ViewHelper'),
				'label'   		=> 'Post',
				'class'			=> 'profile-post-button button'

      			));  

													
    }


}
