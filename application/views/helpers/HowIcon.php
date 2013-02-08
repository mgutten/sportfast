<?php

class Application_View_Helper_HowIcon extends Zend_View_Helper_PartialLoop
{
	
	public function howicon($path, $array) 
	{
		return parent::partialLoop($path, $array);
	}
	

}
