<?php

class Application_View_Helper_UserSideInfo
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function usersideinfo($sport)
	{
		$user = $this->_view->user;
		
		$output  = "<div id='user-side-info-container' class='white-back'>";
		$output .=		"<p class='left darkest smaller-text'>My rating:</p>";
		$output .=		"<div class='clear'>" . $user->getBoxProfilePic('medium') . "</div>";
		$output .=		"<p class='clear width-100 center darkest'>" . $sport . "</p>";
		$output .=		"<p class='clear largest-text darkest heavy width-100 center negative-margin-top'>" . $user->getSport($sport)->overall . "</p>";
		$output .= "</div>";
					
		return $output;
	}
	


}