<?php

class CreateController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $this->view->whiteBacking = false;
		$this->view->narrowColumn = false;
    }
	
	public function gameAction()
	{
		/*
		if (!$this->view->user->hasProfilePic()) {
			// Do not allow to create game without profile pic
			$session = new Zend_Session_Namespace('pictureRequired');
			$session->fail = 'create';
			$this->_helper->_redirector->goToUrl('/users/' . $this->view->user->userID . '/upload');
		}
		*/
		$sports = new Application_Model_Sports();
		$this->view->sports = $sports->getAllSportsInfo(true);
		
		$userSports = $this->view->user->getSportNames();
		$missingSports = array();
		
		foreach ($sports->getAll() as $sport) {
			if (!in_array(strtolower($sport->sport), $userSports)) {
				// User does not have this sport
				$missingSports[strtolower($sport->sport)] = true;
			}
		}
		
		$this->view->missingSports = $missingSports;
		
		$parks = new Application_Model_Parks();
		$parks->findParks(array(), $this->view->user);
		
		$this->view->parks = $parks->getAll();
		
		$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		
		$this->view->hourDropdown = $dropdown->dropdown('hour',range(1,12),'1');
		$this->view->minDropdown = $dropdown->dropdown('min',array('00','15','30','45'),'00');
		$this->view->ampmDropdown = $dropdown->dropdown('ampm',array('am','pm'),'pm', false);
		
		$ratings = new Application_Model_Ratings();
		$ratings = $ratings->getAvailableRatings('user', 'skill');

		$minSkill = reset($ratings);
		$minSkill = $minSkill['ratingName'];
		$maxSkill = end($ratings);
		$maxSkill = $maxSkill['ratingName'];
		
		$dropdownValues = array();
		foreach ($ratings as $rating) {
			$dropdownValues[] = array('text' => $rating['ratingName'],
									  'attr' => array('value' => $rating['value']));
		}
			
		
		$this->view->minSkill = $dropdown->dropdown('minSkill', $dropdownValues, $minSkill, false);
		$this->view->maxSkill = $dropdown->dropdown('maxSkill', $dropdownValues, $maxSkill, false);
		
		$form = $this->view->form = new Application_Form_CreateGame();
		
		
		$messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        $messages = $messenger->getMessages('gameError');
		if (!empty($messages[0])) {
			// There were errors sent from validation
			$this->view->errors = $messages[0];
		}
		
		
		$session = new Zend_Session_Namespace('createGame');
		
		if (empty($session->visited)) {
			$this->view->topAlert = true;
			$session->visited = true;
		}
		

	}
	
	public function validategameAction()
	{
		$post = $this->getRequest()->getPost();
		$parkID = $post['parkID'];
		
		$form = new Application_Form_CreateGame();
		
		if (!$form->isValid($post)) {
				foreach ($form->getMessages() as $section => $errorType) {
					foreach ($errorType as $val) {
						$errors[$section] = str_replace('Value', ucwords($section), $val);
					}
				}
				
				$this->_helper->FlashMessenger->addMessage($errors, 'gameError');
				
				$this->_redirect('/create/game');
				return;

		}


		if (empty($parkID) && !empty($post['parkLocation'])) {
			// Custom park is used
			$park = new Application_Model_Park();
			$park->parkName = $post['parkName'];
			$park->temporary = '1';
			$park->cityID = $this->view->user->city->cityID;
			$park->save(false);
			
			$parkID = $park->parkID;
			
			$park->location = str_replace(',',' ',$post['parkLocation']);
			$location = $park->location;
			$location->parkID = $parkID;
			
			$location->setDbTable('Application_Model_DbTable_ParkLocations');
			
			$location->save();
			
		}
		
		$game = new Application_Model_Game();
		

		$game->parkID = $parkID;
		$game->parkName = $post['parkNameHidden'];
		$game->date   = $post['datetime'];
		$game->sport  = $post['sport'];
		$game->sportID = $post['sportID'];
		$game->recurring = ($post['recurring'] == 'yes' ? '1' : '0');
		$game->public = ($post['visibility'] == 'public' ? '1' : '0');
		$game->minPlayers = $post['minPlayers'];
		$game->rosterLimit = (!empty($post['rosterLimit']) ? $post['rosterLimit'] : '99');
		$game->cityID = $this->view->user->city->cityID;

		$game->typeID = $game->getSportTypeID($post['sportID'], $post['typeName'], $post['typeSuffix']);
		
		if (!empty($post['ageLimitCheckbox'])) {
			// Limit age was checked
			$game->minAge = $post['ageLimitMin'];
			$game->maxAge = $post['ageLimitMax'];
		} else {
			$game->minAge = 17;
			$game->maxAge = 100;
		}
		
		if (!empty($post['skillLimitCheckbox'])) {
			// Limit skill was checked
			$game->minSkill = $post['skillLimitMin'] - 3;
			$game->maxSkill = $post['skillLimitMax'] + 3;
		} else {
			$game->minSkill = 63;
			$game->maxSkill = 100;
		}
		
		$game->save(false);
		
		// Insert user to game
		$table = new Application_Model_DbTable_UserGames();
		$table->insert(array('gameID' => $game->gameID,
		 					 'userID' => $this->view->user->userID));
							 
		// Insert user as game captain
		$table = new Application_Model_DbTable_GameCaptains();
		$table->insert(array('gameID' => $game->gameID,
		 					 'userID' => $this->view->user->userID));
							 
		if ($game->isRecurring()) {
			$table = new Application_Model_DbTable_GameSubscribers();
			$table->insert(array('gameID' => $game->gameID,
								 'userID' => $this->view->user->userID,
								 'joinDate' => new Zend_Db_Expr('now()')));
		}
		
		
		$notification = new Application_Model_Notification();
		$notification->action = 'create';
		$notification->type   = 'game';
		$notification->gameID = $game->gameID;
		$notification->actingUserID = $this->view->user->userID;
		$notification->cityID = $this->view->user->city->cityID;
		$notification->save();		 
		
		$success = new Zend_Session_Namespace('createSuccess');
		$success->type = 'game';
		$success->gameID = $game->gameID;
		 
		$this->_redirect('/create/success');
							 
	}
	
	public function teamAction()
	{
		$sports = new Application_Model_Sports();
		$this->view->sports = $sports->getAllSportsInfo(true);
		
		$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		
		$userSports = $this->view->user->getSportNames();
		$missingSports = array();
		
		foreach ($sports->getAll() as $sport) {
			if (!in_array(strtolower($sport->sport), $userSports)) {
				// User does not have this sport
				$missingSports[strtolower($sport->sport)] = true;
			}
		}
		
		$this->view->missingSports = $missingSports;
		
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
		$this->view->defaultAvatar = 'spartan.jpg';
				
		$form = new Application_Form_CreateTeam();
		$form->avatar->setValue($this->view->defaultAvatar);
		$this->view->form = $form;
		
		
		//$this->view->leagueDescription = "The following local leagues have not started yet.  Select one of these options and our system will automatically register this team when enough players commit.";
		
		$messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        $messages = $messenger->getMessages('teamError');
		if (!empty($messages[0])) {
			// There were errors sent from validation
			$this->view->errors = $messages[0];
		}

	}

	public function validateteamAction()
	{
		$post = $this->getRequest()->getPost();
		
		$form = new Application_Form_CreateTeam();
		
		if (!$form->isValid($post)) {
				foreach ($form->getMessages() as $section => $errorType) {
					foreach ($errorType as $val) {
						$errors[$section] = str_replace('Value', ucwords($section), $val);
					}
				}
				
				$this->_helper->FlashMessenger->addMessage($errors, 'teamError');
				
				$this->_redirect('/create/team');
				return;

		}

		
		$team = new Application_Model_Team();
		
		$team->teamName = $post['teamName'];
		$team->sport  = $post['sport'];
		$team->sportID = $post['sportID'];
		$team->public = ($post['visibility'] == 'public' ? '1' : '0');
		$team->rosterLimit = $post['rosterLimit'];
		$team->cityID = $this->view->user->city->cityID;
		$team->city   = $this->view->user->city->city;
		$team->picture = str_replace('.jpg','',$post['avatar']);
		$team->setCurrent('lastActive');
				
		if (!empty($post['ageLimitCheckbox'])) {
			// Limit age was checked
			$team->minAge = $post['ageLimitMin'];
			$team->maxAge = $post['ageLimitMax'];
		} else {
			$team->minAge = 17;
			$team->maxAge = 100;
		}
		
		if (!empty($post['skillLimitCheckbox'])) {
			// Limit skill was checked
			$team->minSkill = $post['skillLimitMin'];
			$team->maxSkill = $post['skillLimitMax'];
		} else {
			$team->minSkill = 63;
			$team->maxSkill = 100;
		}
		
		$team->save(false);
		
		
		// Insert user to team
		$table = new Application_Model_DbTable_UserTeams();
		$table->insert(array('teamID' => $team->teamID,
		 					 'userID' => $this->view->user->userID));
							 
		// Insert user as game captain
		$table = new Application_Model_DbTable_TeamCaptains();
		$table->insert(array('teamID' => $team->teamID,
		 					 'userID' => $this->view->user->userID));
							 
		
		$notification = new Application_Model_Notification();
		$notification->action = 'create';
		$notification->type   = 'team';
		$notification->teamID = $team->teamID;
		$notification->actingUserID = $this->view->user->userID;
		$notification->cityID = $this->view->user->city->cityID;
		$notification->save();
	
		
		
		$success = new Zend_Session_Namespace('createSuccess');
		$success->type = 'team';
		$success->teamID = $team->teamID;
		 
		$this->_redirect('/create/success');
							 
	}



	public function successAction()
	{
		$this->view->narrowColumn = false;
		$this->view->whiteBacking = false;
		
		
		$success = new Zend_Session_Namespace('createSuccess');
		
		$this->view->type = $type = $success->type;
		$typeID = strtolower($success->type) . 'ID';
		$this->view->typeID = $success->$typeID;
		$types = $type . 's';
		
		if (count($this->view->user->$types->getAll()) <= 1) {
			// Is first team/game, will show options button on the page
			$session = new Zend_Session_Namespace('first' . ucwords($type));
			$session->first = true;
		}
		
		
		$form = new Application_Form_General();
		$userName = $form->text->setName('userName')
							   ->setLabel("Start typing a player's name...");
							   
		$note = 	$form->textarea->setName('note')
							   	   ->setLabel("Write note...");
							   
		$this->view->userName = $userName;
		$this->view->note = $note;
		
		//$success->unsetAll();
	}
	
			


}

