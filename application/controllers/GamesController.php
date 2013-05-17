<?php

class GamesController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$this->view->narrowColumn = 'left';
		
		$gameID = $this->getRequest()->getParam('id');
        $game = new Application_Model_Game();
		$game->getGameByID($gameID);
		
		$this->view->game = $game;
		
		if (!$game->gameID) {
			// No game found
			$this->view->narrowColumn = false;
			$this->view->fail = true;
			return;
		}
		
		if (!$this->view->user->hasProfilePic()) {
			// No profile pic, set this page as go to url if upload
			$session = new Zend_Session_Namespace('goToURL');
			$session->url = '/games/' . $gameID;
			
			$this->view->topAlert = true;
		} else {
			$session = Zend_Session::namespaceUnset('goToURL');
		}
		
		$this->view->userInGame = $userInGame = $game->players->userExists($this->view->user->userID);
		$this->view->userPlus = ($userInGame ? $userInGame->plus : false);
		
		$this->view->pastGame  = ($game->gameDate->format('U') < time() ? true : false);
		$this->view->todayGame = ($game->gameDate->format('mdy') == date('mdy') ? true : false);
		$this->view->gameTitle = $game->getGameTitle();
		
		$this->view->isPublic  = ($game->public == '1' ? true : false);
		
		$this->view->totalPlayers  = $game->totalPlayers;
		$this->view->rosterLimit   = $game->rosterLimit;
		$this->view->minPlayers    = $game->minPlayers;
		$this->view->gameOn		   = ($game->totalPlayers >= $game->minPlayers ? true : false);
		$this->view->playersNeeded = $game->getPlayersNeeded();
		
		
		
		$this->view->newsfeed   = $game->messages->getGameMessages($game->gameID);

		$this->view->captain = $captain = $game->isCaptain($this->view->user->userID);
		$this->view->subscribed = $game->isSubscriber($this->view->user->userID);
		
		
		if ($game->recurring && $userInGame && !$game->isSubscriber($this->view->user->userID)) {
			// Show subscribe button
			$this->view->topAlert = true;
		}
		
		
		if ($userInGame) {
			// User is in game, get post form
			$postForm = new Application_Form_PostMessage();
			$postForm->setAction('/games/' . $game->gameID);
			$postForm->login->setName('submitPostMessage');
			$this->view->postForm = $postForm;
			
			$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
			$this->view->inviteButton = $dropdown->dropdownButton('invite', '', 'Invite');
			if ($captain) {
				// Allow captain to manage
				$this->view->manageButton = $dropdown->dropdownButton('manage', array('Remove Player',
																					  'Game Info', 'Cancel Game'), 'Manage');
					
			}
		}
				
		$this->view->parkLocation = $game->park->location;
		
	
	}
	
	public function playersAction()
	{
		$this->view->narrowColumn = 'false';
		
		$gameID = $this->getRequest()->getParam('id');
        $game = new Application_Model_Game();
		$game->getGameByID($gameID);
		
		$this->view->game = $game;
		
		$this->view->players = $game->players->getAll();
		
		$gameDate = $game->gameDate->format('U');
		$curTime  = time();
		
		$diff = $gameDate - $curTime;
		$hours = floor(($diff/ 60) / 60);
		$remaining = $diff - ($hours * 60 * 60);
		$minutes = floor($remaining/60);
		
		$this->view->timeUntil = $hours . '<span class="inherit smaller-text">hr</span> ' . $minutes . '<span class="inherit smaller-text">min</span>';
	}


}

