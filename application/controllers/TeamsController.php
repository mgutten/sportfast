<?php

class TeamsController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$this->view->narrowColumn = 'left';
		
		$teamID = $this->getRequest()->getParam('id');
        $team = new Application_Model_Team();
		$team->getTeamByID($teamID);
		
		$this->view->team = $team;
		
		$nextGame = $this->view->nextGame = $team->getNextGame();
		
		if ($nextGame) {
			// There is a next game
			$this->view->team->sortPlayersByConfirmed();
			$this->view->countConfirmedPlayers = $nextGame->countConfirmedPlayers();
			$this->view->nextTeamName = $nextGame->opponent;
			$this->view->nextDay	  = $nextGame->getDay(); 
			$this->view->nextShortDate= $nextGame->getShortDate();
			$this->view->nextHour	  = $nextGame->getHour();
			$this->view->nextLocationName = $nextGame->locationName;
			$this->view->nextLocationStreetAddress = urlencode($nextGame->streetAddress . ',' . $nextGame->city);
			$this->view->userNextGame =$nextGame->userConfirmed($this->view->user->userID);
		}
		
		// Current user is captain
		$this->view->captain  = $captain = $team->isCaptain($this->view->user->userID);
		$this->view->hasCaptain = $team->hasCaptain();
		
		if ($captain) {
			// Need to display "Manage" and "Invite" button
			$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
			$this->view->inviteButton = $dropdown->dropdownButton('invite', '', 'Invite');
		}

		
		$this->view->totalPlayers = $team->totalPlayers;
		$this->view->rosterLimit  = $team->rosterLimit;
		$this->view->messages	  = $team->messages->getTeamMessages($team->teamID);
		
    }


}

