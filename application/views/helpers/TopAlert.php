<?php

class Application_View_Helper_TopAlert
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function topalert($id = '', $content = '')
	{
		$output = "<div id='top-alert-" . $id . "' class='top-alert-container green-back pointer'>
						" . $content . "
					</div>";
					
		return $output;
	}
	


}