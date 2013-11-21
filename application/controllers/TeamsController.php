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
		
		$session = new Zend_Session_Namespace('signupInvite');
		
		if (!$this->view->user) {
				$this->view->user = new Application_Model_User();
				$this->view->user->userID = '0';
				
				$this->view->signupInvite = true;
				$this->view->user->username = (isset($session->email) ? $session->email : '');
				
				$location = new Application_Model_Location();
				$this->view->cityLocation = $location->getLocationByCityID($team->cityID);
				
				$session = new Zend_Session_Namespace('postLoginURL');
				$session->url = '/teams/' . $teamID;
				
			}
		/*
		$session = new Zend_Session_Namespace('firstTeam');
		if ($session->first) {
			// Is first team/game, show profile buttons
			$this->view->firstTeam = true;
			Zend_Session::namespaceUnset('firstTeam');
		}
		*/
		
		$session = new Zend_Session_Namespace('invites');
		if ($session->sent) {
			// From mailController inviteTypeAction, invites successfully sent, alert
			$this->view->invitesSent = true;
			Zend_Session::namespaceUnset('invites');
		}
		
		$session = new Zend_Session_Namespace('message');
		if ($session->sent) {
			// From mailController inviteTypeAction, invites successfully sent, alert
			$this->view->messageSent = true;
			Zend_Session::namespaceUnset('message');
		}
		
		$session = new Zend_Session_Namespace('addToTeam');
		if (isset($session->fail)) {
			// From MailController addUserSubscribeGame action, user not added to game from email
			if ($session->fail == 'already') {
				// User is already in this game
				$this->view->addToTeam = 'You are already on this team.';
			} elseif ($session->fail == 'full') {
				// Game is full
				$this->view->addToTeam = 'This team is full.';
			} else {
				// Successfully added
				$this->view->addToTeam = 'You have been added to the roster.';
			}
			
			
			Zend_Session::namespaceUnset('addToTeam');
		}
		
		$session = new Zend_Session_Namespace('userSport');
		$session->sport = $team->sport;		
		
		$this->view->isPublic  = $isPublic = $team->isPublic();
		$this->view->isPrivate = ($isPublic ? false : true);
		
		
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
			
			if ($team->reserves->exists($this->view->user->userID)) {
				// User is in reserves, see if was invited to next game
				
				$invited = $team->isInvitedToGame($this->view->user->userID, $nextGame->teamGameID);
				
				if ($invited ||
					$nextGame->userConfirmed($this->view->user->userID) ||
					$nextGame->userNotConfirmed($this->view->user->userID)) {
						// Either pending invite, or is already confirmed/not confirmed
						$this->view->invitedToNextGame = true;
					}
			}
		}
		
		$this->view->previousGames = $team->games->getPreviousGames();
		$this->view->events		   = $team->games->getAll();
		
		
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
																				  array('text' => 'Reserves',
																						'image' => '/images/team/icons/reserve.png',
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
			
			$form = new Application_Form_General();
			$userName = $form->text->setName('userName')
								   ->setLabel("Start typing a player's name...");
								   
			$this->view->reserveUserName = $userName;
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
		} else {
			$this->view->invited = $team->isInvited($this->view->user->userID);
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
	
	public function pendingAction()
	{
		$teamID = $this->getRequest()->getParam('id');
        $team = new Application_Model_Team();
		$team->getTeamByID($teamID);
		
		$this->view->team = $team;
		
		$this->view->pendingInvites = $team->getPendingInvites($this->view->user->userID);
	}


}

