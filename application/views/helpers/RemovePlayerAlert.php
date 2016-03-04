<?php

class Application_View_Helper_RemovePlayerAlert
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	/**
	 * remove player alert for team and game pages
	 * @params ($players => array of players,
	 *			$teamName => teamName, if false, then is assumed to be game)
	 */
	public function removeplayeralert($players, $teamName = false)
	{
		if ($teamName) {
			$name = $teamName;
		} else {
			$name = 'this game';
		}
		$output  = $this->_view->alert()->start('manage-remove-player','Click on a player to remove from roster.');
		$output .=  "<div class='team-manage-remove-player-container width-100 left'>";
						"<div class='clear margin-top'>";
						
			foreach ($players as $player) {
				$output .= "<div class='team-player-container left pointer profile-manage-remove-player' tooltip='" . $player->fullName . "' playerName='" . $player->fullName . "' userID='" . $player->userID . "'>" 
								. $player->getBoxProfilePic('medium', 'users', 'animate-opacity', 'dark-back') 
							. "</div>";
			}
		$output .=	"</div>";
		$output .= 	"<div id='profile-manage-remove-player-confirm-container' class='hidden'>
						<p class='clear larger-margin-top light larger-text'>Remove 
							<span id='profile-manage-remove-player-name' class='heavy red largest-text'></span> from " 
							. $name . "?</p>";
		$output .=		"<div class='clear width-100 larger-margin-top'>
							<p class='red-button profile-manage-button larger-margin-top' id='profile-manage-remove-player-remove'>Remove</p>
							<p class='button profile-manage-button larger-margin-top' id='profile-manage-remove-player-cancel'>Cancel</p>
						</div>";
		$output .= 	"</div>";
		$output .= "</div>";
							
		$output .= $this->_view->alert()->end();

					
		return $output;
	}
	


}