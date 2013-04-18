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
		
		$this->view->userInGame = $userInGame = $game->players->userExists($this->view->user->userID);
		$this->view->userPlus = $userInGame->plus;
		
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
		
		if ($userInGame) {
			// User is in game, get post form
			$postForm = new Application_Form_PostMessage();
			$postForm->setAction('/games/' . $game->gameID);
			$postForm->login->setName('submitPostMessage');
			$this->view->postForm = $postForm;
			
			$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
			$this->view->inviteButton = $dropdown->dropdownButton('invite', '', 'Invite');
		}
				
		$this->view->parkLocation = $game->park->location;
		
	
	}


}

