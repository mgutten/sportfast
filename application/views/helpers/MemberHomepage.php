<?php
class Application_View_Helper_MemberHomepage
{	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function memberHomepage() 
	{
		$output = 'Member homepage!';
						
		return $output;
	}
}