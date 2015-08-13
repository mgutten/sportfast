<?php

class Application_Model_ConfirmationsAbstract extends Application_Model_ModelAbstract
{


	/**
	 * order players by whether they are confirmed or not
	 * @params($game => Application_Model_Game if not using next game or current model as $game)
	 */
	public function sortPlayersByConfirmed($game = false)
	{
		if ($this instanceof Application_Model_Game) {
			$game = $this;
		} elseif (!$game) {
			$game = $this->getNextGame();
		}
		
		if ($this->players->hasValue('users')) {
			// There are players stored, sort them
			$players = $this->_attribs['players']->_attribs['users'];
			
			$playerArray = $undecided = $notConfirmed = $maybeConfirmed = array();
			
			foreach ($players as $player) {
				
				if ($player->hasProfilePic()) {
					$function = 'array_unshift';
				} else {
					// Push to end of stack
					$function = 'array_push';
				}
				
				if ($game->userConfirmed($player->userID)) {
					// User is confirmed
					$function($playerArray, $player);
					
				} elseif ($game->userNotConfirmed($player->userID)) {
					// User is not going
					$function($notConfirmed, $player);
				} elseif ($game->userMaybeConfirmed($player->userID)) {
					// Is a maybe
					$function($maybeConfirmed, $player);
				} else {
					array_push($undecided, $player);
				}
			}
			
			foreach ($maybeConfirmed as $player) {
				array_push($playerArray, $player);
			}
			
			foreach ($undecided as $player) {
				array_push($playerArray, $player);
			}
			
			foreach ($notConfirmed as $player) {
				array_push($playerArray, $player);
			}
			
			$players = $this->_attribs['players'];
			$players->users = $playerArray;
			
			return $this->_attribs['players'];
		} else {
			return false;
		}
	}
	
	public function countConfirmedPlayers()
	{
		
		if (!$this->hasValue('confirmedPlayers')) {
			return '0';
		} elseif (is_array($this->confirmedPlayers)) {
			$confirmed = count($this->confirmedPlayers);

		} else {
			
			$confirmed = $this->confirmedPlayers;
		}
		
		if ($this->hasValue('plus')) {
			$confirmed += $this->plus;
		}
		
		return $confirmed;
	}
	
	public function countNotConfirmedPlayers()
	{
		if (!$this->hasValue('notConfirmedPlayers')) {
			return 0;
		} else {
			return count($this->notConfirmedPlayers);
		}
	}
	
	public function countMaybeConfirmedPlayers()
	{
		if (!$this->hasValue('maybeConfirmedPlayers')) {
			return 0;
		} elseif (is_array($this->maybeConfirmedPlayers)) {
			$confirmed = count($this->maybeConfirmedPlayers);

		} else {
			
			$confirmed = $this->maybeConfirmedPlayers;
		}
		
		return $confirmed;
	}
	
}
