<?php

class Application_View_Helper_PlayersSection
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	/** 
	 * create players section for team, group, or game profile pages
	 * @params ($players => array of player object,
	 *			$nextGame => if there is a next game, then next game object
	 */
	public function playerssection($players, $nextGame = false, $limited = true)
	{
		$output = '';
		$counter = 0;
		$totalPlayers = count($players);
		
		if ($this->_view->team) {
			// Players for team page
			$type = 'teams';
			$typeID = $this->_view->team->teamID;
			$typeModel = $this->_view->team;
		} elseif ($this->_view->game){
			// Players for game page
			$type = 'games';
			$typeID = $this->_view->game->gameID;
			$typeModel = $this->_view->game;
		} else {
			// Players for group page
			$type = 'groups';
			$typeID = $this->_view->group->groupID;
			$typeModel = $this->_view->group;
		}
		
		if ($players) {
			// Players in game
			foreach ($players as $player) {
				
				if ($counter >= 14 && $limited) {
					// Only show 14 players
									
					$output .= "<a href='/" . $type . "/" . $typeID . "/players' class='medium clear-right smaler-text'>" . ($totalPlayers - $counter) . " more players</a>";
					break;
				}
				$output .= "<a href='/users/" . $player->userID . "' class='left team-player-container'>";
				$output .= 	$player->getBoxProfilePic('medium');
				
				$success = false;
				
				if ($nextGame) {
					if ($nextGame->userConfirmed($player->userID)) {
						// User is going to next game
						$src = "/images/team/confirm/small.png";
						$success = true;
					} elseif ($nextGame->userNotConfirmed($player->userID)) {
						// User is confirmed as not going
						$src = "/images/team/deny/small.png";
						$success = true;
					}
				}
				
				if ($success) {
					// User is either confirmed or not
					$output .= "<img src='" . $src . "' class='clear team-confirm-img' />";
				}
				
				$output .= 	"<div class='hover-dark profile-player-overlay-container'>";
				$output .=		"<div class='profile-player-overlay'>";
				$output .=			"<p class='white width-100 center left'>" . $player->shortName . "</p>";
				$output .=			"<p class='white width-100 center left smaller-text'>age " . $player->age . "</p>";
				$output .=			"<p class='white width-100 center left largest-text heavy'>" . $player->getSport($typeModel->sport)->overall . "</p>";
				$output .=		"</div>";
				$output .=	"</div>";
				$output .= "</a>";
				
				$counter ++;
			}
		}

		if ($typeModel instanceof Application_Model_Game) {
			if ($counter < $typeModel->totalPlayers) {
				// There are "plus-ones"
				for($counter = $counter; $counter < $typeModel->totalPlayers; $counter++) {
					$player  = new Application_Model_User();					
					$output .= "<div class='left team-player-container'>";
					$output .= 	$player->getBoxProfilePic('medium');
					$output .= 	"<div class='hover-dark profile-player-overlay-container'>";
					$output .=		"<div class='profile-player-overlay'>";
					$output .=			"<p class='light width-100 center left'>Guest</p>";
					$output .=			"<p class='light width-100 center left smaller-text'>not a member</p>";
					$output .=		"</div>";
					$output .=	"</div>";
					$output .= "</div>";
				}
			}
		}
		
		$remaining = ($typeModel->rosterLimit >= 7 ? 7 - $counter : $typeModel->rosterLimit - $counter);
		if ($remaining > 0 && $typeModel->public == '1') {
			// Populate remaining with empty open spots
			$text = 'Join';
			$class = 'profile-join-player-container pointer';
			if ($this->_view->userOnTeam || $this->_view->userInGame) {
				// No need to show join spots for users on team already
				$text = 'Open';
				$class = 'profile-open-player-container default';
			}
			
			for ($i = 0; $i < $remaining; $i++) {
				$output .= "<div class='left " . $class . " light animate-opacity'>" . $text . "</div>";
			}
		}

					
		return $output;
	}
	

}
