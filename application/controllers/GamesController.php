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
		
		$session = new Zend_Session_Namespace('invites');
		if ($session->sent) {
			// From mailController inviteTypeAction, invites successfully sent, alert
			$this->view->invitesSent = true;
			Zend_Session::namespaceUnset('invites');
		}
	
		$session = new Zend_Session_Namespace('message');
		if ($session->sent) {
			// From mailController messageAction, email message successfully sent, alert
			$this->view->messageSent = true;
			Zend_Session::namespaceUnset('message');
		}
		
		$session = new Zend_Session_Namespace('addToGame');
		if (isset($session->fail)) {
			// From MailController addUserSubscribeGame action, user not added to game from email
			if ($session->fail == 'full') {
				// Game is full
				$this->view->addToGame = 'This game is full.';
			} elseif ($session->fail == 'out') {
				// Confirmed as "out"
				$this->view->addToGame = 'You will be counted as "out".';
			} else {
				// Successfully added
				$this->view->addToGame = 'You have been added to the roster.';
			}
			
			Zend_Session::namespaceUnset('addToGame');
		}
		
		
		$session = new Zend_Session_Namespace('userSport');
		$session->sport = $game->sport;
		
		$this->view->userInGame = $userInGame = $game->players->userExists($this->view->user->userID);
		
		$this->view->userPlus = ($userInGame ? $userInGame->plus : false);
		
		$this->view->pastGame  = ($game->gameDate->format('U') < time() ? true : false);
		$this->view->todayGame = ($game->gameDate->format('mdy') == date('mdy') ? true : false);
		$this->view->gameTitle = $game->getGameTitle();
		
		$this->view->isPublic  = ($game->public == '1' ? true : false);
		
		$this->view->totalPlayers  = $game->totalPlayers;
		$this->view->confirmedPlayers = $game->countConfirmedPlayers();
		$this->view->notConfirmedPlayers = $game->countNotConfirmedPlayers();
		$this->view->maybeConfirmedPlayers = $game->countMaybeConfirmedPlayers();
		$this->view->rosterLimit   = $game->rosterLimit;
		$this->view->minPlayers    = $game->minPlayers;

		$this->view->gameOn		   = ($this->view->confirmedPlayers >= $game->minPlayers ? true : false);
		$this->view->playersNeeded = $game->getPlayersNeeded('more');
		$this->view->gameFull	   = ($game->rosterLimit <= $game->countConfirmedPlayers() ? true : false);
		
		
		$this->view->newsfeed   = $game->messages->getGameMessages($game->gameID);
		$this->view->captain = $captain = $game->isCaptain($this->view->user->userID);
		
		$this->view->subscribed = $subscribed = $game->isSubscriber($this->view->user->userID);
		
		$game->sortPlayersByConfirmed();
		
		/*
		if ($game->isRecurring() && !$subscribed) {
			// Show subscribe button
			$this->view->topAlert = true;
			$this->view->showSubscribe = true;
		}
		*/
		
		
		if ($userInGame) {
			// User is in game, get post form
			$postForm = new Application_Form_PostMessage();
			$postForm->setAction('/games/' . $game->gameID);
			$postForm->login->setName('submitPostMessage');
			$this->view->postForm = $postForm;
			
			$stashSports = array('volleyball', 'football', 'ultimate', 'soccer');
			$session = new Zend_Session_Namespace('joinedGame');
		
			if ($game->park->hasStash() 
				&& in_array(strtolower($game->sport), $stashSports) 
				&& $session->joinedGame
				&& !$game->recurring) {
				// Has stash and sport requires stash and just joined this game
				$this->view->stash = true;
			}
			
			Zend_Session::namespaceUnset('joinedGame');
			
			
			//$this->view->inviteButton = $dropdown->dropdownButton('invite', '', 'Invite');
			
		}
		
		if ($captain) {
			// Allow captain to manage
			/*
			$this->view->manageButton = $dropdown->dropdownButton('manage', array(array('text' => 'Edit Game',
																						'href' => '/games/' . $gameID . '/edit',
																						'image' => '/images/team/icons/edit.png',
																						'background' => '',
																						'imageLocation' => 'left'),
																				  array('text' => 'Remove Player',
																					'image' => '/images/team/icons/x.png',
																					'background' => '',
																					'imageLocation' => 'left'), 
																				  array('text' => 'Cancel Game',
																					'image' => '/images/team/icons/trash.png',
																					'background' => '',
																					'imageLocation' => 'left')), 'Manage');
			*/
			$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
			$sendReminder = $game->getSendReminder(true);
			$this->view->reminderHourDropdown = $dropdown->dropdown('reminder-hour', range(1,12), $sendReminder['hour'], false, true);
			$this->view->reminderAmpmDropdown = $dropdown->dropdown('reminder-ampm', array('am', 'pm'), $sendReminder['ampm'], false, true);					
		}
			
		$this->view->userHasSport = $this->view->user->hasSport($game->sport);
			
		$this->view->parkLocation = $game->park->location;
		
	
	}
	
	public function editAction()
	{
		$this->view->narrowColumn = 'right';
		
		$gameID = $this->getRequest()->getParam('id');
        $game = new Application_Model_Game();
		$game->getGameByID($gameID);
		
		$this->view->game = $game;
		
		if (($game->gameDate->format('U') - time()) < (60 * 60 * 4)) {
			// Only allow editing 4 hours in advance
			$this->view->notEnoughTime = true;
			$this->view->narrowColumn = false;
		}
			
		
		$captain = $game->isCaptain($this->view->user->userID);
	
		if (!$game->gameID || !$captain) {
			// No game found or not captain
			$this->_redirect('/games/' . $gameID);
		}
		
		$parks = new Application_Model_Parks();
		$parks->findParks(array(), $this->view->user);
		
		$this->view->parks = $parks->getAll();
		
		
		$this->view->selectedVisibility = ($game->isPublic() ? 'public' : 'private');
		$this->view->selectedRecurring = ($game->isRecurring() ? 'yes' : 'no');
		
		$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		
		$this->view->hourDropdown = $dropdown->dropdown('hour',range(1,12),$game->gameDate->format('g'));
		$this->view->minDropdown = $dropdown->dropdown('min',array('00','15','30','45'),$game->gameDate->format('i'));
		$this->view->ampmDropdown = $dropdown->dropdown('ampm',array('am','pm'),$game->gameDate->format('a'), false);
		
		$form = $this->view->form = new Application_Form_CreateGame();
	}
	
	public function updateGameAction()
	{
		$gameID = $this->getRequest()->getParam('id');
		$post = $this->getRequest()->getPost();

		$game = $this->view->user->games->exists($gameID);
		
		if (empty($post['parkID']) && !empty($post['parkLocation'])) {
			// Custom park is used
			$park = new Application_Model_Park();
			$park->parkName = $post['parkNameHidden'];
			$park->temporary = 1;
			$park->cityID = $this->view->user->city->cityID;
			$park->save(false);
			
			$parkID = $park->parkID;
			
			$park->location = str_replace(',',' ',$post['parkLocation']);
			$location = $park->location;
			$location->parkID = $parkID;
			
			$location->setDbTable('Application_Model_DbTable_ParkLocations');
			
			$location->save();
			
			$game->parkID = $parkID;
			$game->parkName = $park->parkName;
			
		}
		
		$game->public = ($post['visibility'] == 'private' ? 0 : 1);
		$game->recurring = ($post['recurring'] == 'no' ? 0 : 1);
		$game->date = $post['datetime'];
		$game->minAge = $post['ageLimitMin'];
		$game->maxAge = $post['ageLimitMax'];
		$game->minSkill = $post['skillLimitMin'];
		$game->maxSkill = $post['skillLimitMax'];
		$game->minPlayers = $post['minPlayers'];
		$game->rosterLimit = $post['rosterLimit'];
		
		$game->save(false);
			
			
		$notification = new Application_Model_Notification();
		$notification->action = 'edit';
		$notification->type = 'game';
		$notification->details = 'info';
		$notification->gameID  = $game->gameID;
		$notification->actingUserID = $this->view->user->userID;
		$notification->cityID = $this->view->user->city->cityID;
		
		$notification->save();
			
		$this->_redirect('/games/' . $gameID);
	}
		
		
	
	public function playersAction()
	{
		$this->view->narrowColumn = false;
		
		$gameID = $this->getRequest()->getParam('id');
        $game = new Application_Model_Game();
		$game->getGameByID($gameID);
		
		$game->sortPlayersByConfirmed();
		
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
	
	public function statsAction()
	{
		
		$gameID = $this->getRequest()->getParam('id');
        $game = new Application_Model_Game();
		$game->getGameByID($gameID);
		
		$this->view->game = $game;
		
		$history = $game->getHistoryData(6, $this->view->user->userID);
		
		$this->view->history = $history;
	}
	
	public function subscribersAction()
	{
		//$this->view->narrowColumn = false;
		
		$gameID = $this->getRequest()->getParam('id');
        $game = new Application_Model_Game();
		$game->getGameByID($gameID);
		
		$subscribers = $game->getGameSubscribers();
		
		$this->view->subscribers = $subscribers;
		
		$this->view->game = $game;
		
	}
	
	public function inviteAction()
	{
		$this->view->whiteBacking = false;
		$gameID = $this->getRequest()->getParam('id');
		
		$this->view->type = 'game';
		$this->view->typeID = $gameID;
		
		$form = new Application_Form_General();
							   
		$note = $form->textarea->setName('note')
							   ->setLabel("Write note...");

		$this->view->note = $note;
		
	}

	
}

