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
		
		$this->view->pastGame  = ($game->gameDate->format('U') < time() ? true : false);
		$this->view->todayGame = ($game->gameDate->format('mdy') == date('mdy') ? true : false);
		$this->view->gameTitle = $game->getGameTitle();
		
		
		$this->view->totalPlayers  = $game->totalPlayers;
		$this->view->rosterLimit   = $game->rosterLimit;
		$this->view->minPlayers    = $game->minPlayers;
		$this->view->gameOn		   = ($game->totalPlayers >= $game->minPlayers ? true : false);
		$this->view->playersNeeded = $game->getPlayersNeeded();
		
		$this->view->userInGame = $game->players->userExists($this->view->user->userID);
		
		$this->view->parkLocation = $game->park->location;
		
	
	}


}

