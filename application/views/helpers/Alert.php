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
	
	public function changesAlert() 
	{
		$output = $this->start('changes');
		
		$output .= "<div class='changes-alert-container'>";
		$output .= 		"<p class='width-100 largest-text medium center'>Save all changes?</p>";
		$output .= 		"<p class='button larger-text changes-save'>Save</p>";
		$output .= 		"<p class='button larger-text changes-discard'>Discard</p>";
		$output .= "</div>";
		
		$output .= $this->end();
		
		return $output;
	}
	
	public function confirmAlert()
	{
		$output  = $this->start('confirm-action','');
		$output .= 	"<p class='width-100 clear center'>&nbsp;Are you sure you want to <span id='confirm-action-text'></span>?</p>";
		$output .=	"<div class='clear width-100' id='confirm-action-postContent'></div>";
		$output .=	"<p class='button clear' id='confirm-action'>Yes</p>";
		$output .=	"<p class='button' id='deny-action'>No</p>";
		$output .= $this->end();	
		
		return $output;
	}

}
