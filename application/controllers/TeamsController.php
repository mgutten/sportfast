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
		
		if (!$team->teamID) {
			// No team found
			$this->view->narrowColumn = false;
			$this->view->fail = true;
			return;
		}
		
		$session = new Zend_Session_Namespace('userSport');
		$session->sport = $team->sport;		
		
		$this->view->isPublic  = $isPublic = $team->isPublic();
		$this->view->isPrivate = ($isPublic ? false : true);
		
		$this->view->invited = $team->isInvited($this->view->user->userID);
		
		$nextGame = $this->view->nextGame = $team->getNextGame();
		
		if ($nextGame) {
			// There is a next game
			$this->view->team->sortPlayersByConfirmed();
			$this->view->countConfirmedPlayers = $nextGame->countConfirmedPlayers();
			$this->view->nextTeamName = $nextGame->opponent;
			$this->view->nextDay	  = $nextDay = $nextGame->getDay(); 
			$nextShortDate			  = $nextGame->getShortDate();
			$this->view->nextShortDate= ($nextShortDate === $nextDay ? '' : $nextShortDate);
			$this->view->nextHour	  = $nextGame->getHour();
			$this->view->nextLocationName = $nextGame->locationName;
			$this->view->nextLocationStreetAddress = urlencode($nextGame->streetAddress . ',' . $nextGame->city);
			if ($nextGame->userConfirmed($this->view->user->userID)) {
				// User is confirmed
				$this->view->userNextGame = 'confirmed';
			} elseif ($nextGame->userNotConfirmed($this->view->user->userID)) {
				// User not confirmed
				$this->view->userNextGame = 'not';
			} else {
				// User has not responded
				$this->view->userNextGame = false;
			}
			$this->view->nextGameTypeID = ' typeID="' . $nextGame->teamGameID . '"';
			$this->view->nextGameType   = ' type="teamGame"';
			$this->view->nextGameTeamID = ' teamID="' . $team->teamID . '"';
		}
		
		$this->view->previousGames = $team->games->getPreviousGames();
		$this->view->events		   = $team->games->games;
		
		
		// Current user is captain
		$this->view->captain    = $captain = $team->isCaptain($this->view->user->userID);
		$this->view->hasCaptain = $team->hasCaptain();
		$this->view->isCreator  = $team->isCreator($this->view->user->userID);
		
		if ($captain) {
			// Need to display "Manage" and "Invite" button
			$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
			$this->view->inviteButton = $dropdown->dropdownButton('invite', '', 'Invite');
			$this->view->manageButton = $dropdown->dropdownButton('manage', array(array('text' => 'Schedule',
																						'image' => '/images/team/icons/schedule.png',
																						'background' => '',
																						'imageLocation' => 'left'),
																				  array('text' => 'Edit Team',
																						'image' => '/images/team/icons/edit.png',
																						'background' => '',
																						'imageLocation' => 'left'),
																				  array('text' => 'Remove Player',
																						'image' => '/images/team/icons/x.png',
																						'background' => '',
																						'imageLocation' => 'left'),
																				  array('text' => 'Delete Team',
																						'image' => '/images/team/icons/trash.png',
																						'background' => '',
																						'imageLocation' => 'left')), 'Manage');
			$this->view->manageScheduleTimeHour = $dropdown->dropdown('manage-schedule-time-hour', array(1,2,3,4,5,6,7,8,9,10,11,12), 7);
			$this->view->manageScheduleTimeMinute = $dropdown->dropdown('manage-schedule-time-minute', array('00', '15', '30', '45'), '00');
			$this->view->manageScheduleTimeAmPm = $dropdown->dropdown('manage-schedule-time-ampm', array('am', 'pm'), 'pm', false);
			
			$avatarNames = array();
			if ($handle = opendir(PUBLIC_PATH . '/images/teams/avatars/small')) {
				/* This is the correct way to loop over the directory. */
				while (false !== ($entry = readdir($handle))) {
					if ($entry === '.' || $entry === '..') continue;
					$avatarNames[] = $entry;
				}
				closedir($handle);
			}
			
			
			$this->view->avatarNames = $avatarNames;
			$this->view->defaultAvatar = $team->picture . '.jpg';
			}
		
		$this->view->userOnTeam   = $userOnTeam = $team->players->userExists($this->view->user->userID);
		
		if ($userOnTeam) {
			// User is on team, get post form
			$postForm = new Application_Form_PostMessage();
			$postForm->setAction('/teams/' . $team->teamID);
			$postForm->login->setName('submitPostMessage');
			$this->view->postForm = $postForm;
			
			$team->setCurrent('lastActive');
			$team->save(false);
		}
		
		$this->view->totalPlayers = $team->totalPlayers;
		$this->view->rosterLimit  = $team->rosterLimit;
		$this->view->newsfeed	  = $team->messages->getTeamMessages($team->teamID);
		
		if ($team->systemCreated && !$userOnTeam) {
			$this->view->topAlert = true;
		}
		
		$generalForm = new Application_Form_General();
		$this->view->generalForm = $generalForm;
		
    }
	
	public function playersAction()
	{
		$teamID = $this->getRequest()->getParam('id');
        $team = new Application_Model_Team();
		$team->getTeamByID($teamID);
		
		$this->view->team = $team;
		
		$this->view->players = $team->players->getAll();
		
	}
	
	public function inviteAction()
	{
		$this->view->whiteBacking = false;
		$teamID = $this->getRequest()->getParam('id');
		$team = new Application_Model_Team();
		$team->getTeamByID($teamID);
		
		$this->view->team = $team;
		
		$this->view->type = 'team';
		$this->view->typeID = $teamID;
		
		$form = new Application_Form_General();
							   
		$note = 	$form->textarea->setName('note')
							   	   ->setLabel("Write note...");

		$this->view->note = $note;
		
	}


}

