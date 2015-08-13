<?php

class Application_View_Helper_ChangeCaptain
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	/**
	 * create html for changing captain alert box on games/edit and teams/index pages
	 */
	public function changecaptain($typeModel)
	{
		
		$pictures = "<div class='clear width-100 larger-margin-top'>";
		
		foreach ($typeModel->players->getAll() as $player) {
			$class = 'not-clicked';
			if ($typeModel->isCaptain($player->userID)) {
				$class = 'clicked full-opacity';
			} 
				$pictures .= "<div class='left pointer team-manage-team-info-captain' id='change-captain-" . $player->userID . "' playerName='" . $player->getLimitedName('fullName', 21) . "' userID='" . $player->userID . "' tooltip='" . $player->fullName . "'>";
				$pictures .= $player->getBoxProfilePic('small', 'users', 'animate-opacity ' . $class, 'dark-back');
				$pictures .= "</div>";		
		}
		
		$pictures .= "</div>";
		
		
		$captains  = "<div class='clear larger-margin-top width-100'>";
		$captains .= "<span id='change-captain-name-holder' class='clear largest-text darkest heavy team-manage-team-info-name team-manage-team-info-captain-real default' xClass='left header red hidden largest-text remove-captain pointer'></span>";
		
		foreach ($typeModel->captains as $captain => $true) {
			$player = $typeModel->players->getUser($captain);
			if (!$player) {
				// Has not joined game, so captain's name details are not stored in $typeModel, retrieve from db
				$player = new Application_Model_User();
				$player->getUserBy('u.userID', $captain);
			}
			$captains .= "<p class='clear largest-text darkest heavy team-manage-team-info-name default team-manage-team-info-captain-real' userID='" . $player->userID . "' id='change-captain-name-" . $player->userID . "' defaultName='" . $player->getLimitedName('fullName',21) . "'>"
							 . $player->getLimitedName('fullName', 21) . "
						</p><span class='left header red hidden largest-text remove-captain pointer'>x</span>";
		}
						
		$captains .= 	"<p class='margin-top medium smaller-text clear'>Note: you will not be able to access any management controls if you are no longer the captain.</p>";
		$captains .= 	"<p class='hidden' id='team-manage-team-info-add-captain'></p>"; // Used in js
		$captains .=  "</div>";
		
		
		
		
		$output = $pictures . $captains;
		
					
		return $output;
	}
	


}