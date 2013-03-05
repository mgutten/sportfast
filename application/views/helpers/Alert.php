<?php

class Application_View_Helper_Alert
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function alert()
	{
		return $this;
	}
	
	public function start($id, $header = false)
	{
		$output  = "<div class='alert-container alert' id='" . $id . "-alert-container'>";
		$output .= "<p class='alert-header white heavy'>" . $header . "</p>";
		$output .= "<p class='white bold arial alert-x pointer'>X</p>";
		$output .= "<div class='alert-body-container'>";
					
		return $output;
	}
	
	public function end()
	{
		$output  = "</div></div>";
					
		return $output;
	}
}
