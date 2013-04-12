<?php

class AjaxController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function preDispatch()
	{
		// Test if is AJAX call
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		$request = $this->getRequest();
		
		if (!$request->isXmlHttpRequest()) {
			// Not an ajax call
			$this->_redirect('/');
		}
	}


    public function indexAction()
    {
        // action body
    }
	
	/**
	 * reset user's lastRead column of db to current time (ie after click on notifications button)
	 */
	public function resetNotificationsAction()
	{
		$auth = Zend_Auth::getInstance();
		$user = $auth->getIdentity();
		
		$user->setLastReadCurrent()
			 ->save(false);
		 
		$user->notifications->moveUnreadToRead();
		
		$auth->getStorage()->write($user);
		
		
	}
	
	/**
	 * create notification based on given parameters
	 */
	public function createNotificationAction()
	{
		 $options = $this->getRequest()->getPost('options');
		 $notificationsMapper = new Application_Model_NotificationsMapper();
		 $notificationDetails = array('action'  => $options['action'],
									  'type'	=> $options['type'],
									  'details' => $options['details']);

		 if ($options['receivingUserID'] == 'captain') {
			 $options['receivingUserID'] = $notificationsMapper->getForeignID('Application_Model_DbTable_Teams', 'captain', array($options['idType'] => $options['typeID']));
		 }
		 

		 $data = array('actingUserID' 	  => $options['actingUserID'],
		 			   'receivingUserID'  => $options['receivingUserID'],
					   $options['idType'] => $options['typeID'],
					   'cityID'		 	  => $this->view->user->city->cityID);								 
			
						  
		 $notificationsMapper->addNotification($notificationDetails, $data);
			 
		 
	 }
	 
	 
	 /**
	  * add user to game
	  */
	 public function addUserToGameAction()
	 {
		 $options = $this->getRequest()->getPost('options');
		 
		 if (empty($options['userID']) || empty($options['typeID'])) {
			 return false;
		 }
		 
		 if ($options['idType'] == 'gameID') {
			 // Add user to game, and add game to user's auth session
			 $table = new Application_Model_DbTable_UserGames();
			 $game = new Application_Model_Game();
			 $game->getGameById($options['typeID']);
			 $this->view->user->games->addGame($game);	 
		 }
		 
		 $table->insert(array($options['idType'] => $options['typeID'],
		 					  'userID'		     => $options['userID']));
						
							  
		
	 }
		 
		 
	 
	 /**
	  * add post to team or group page
	  */
	 public function addPostAction()
	 {
		 $options = $this->getRequest()->getPost('options');
		 
		 $messageArray = array();
		 $messageArray[$options['idType']] = $options['typeID'];
		 $messageArray['userID'] = $options['actingUserID'];
		 $messageArray['message'] = $options['message'];
		 $date = new DateTime('now');
		 $messageArray['dateHappened'] = $date->format('Y-m-d H:i:s');
		 
		 $message = new Application_Model_Message($messageArray);
		 
		 $message->save();

	 }
	 
		
	/** 
	 * create and return full html of dropdown
	 * @params(id 		=> what is the id of the dropdown,
	 *		   selected => which option is selected first,
	 *		   options  => array of options)
	 * @return html version of dropdown
	 */
	public function createBasicDropdownAction()
	{
		
		$request = $this->getRequest();
		$post	  = $request->getPost();
		$id       = $post['id'];
		$selected = $post['selected'];
		$options  = $post['options'];
		$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		echo $dropdown->dropdown($id, $options);
		
	}
	
	/**
	 * upload temp picture for use in previews, etc
	 * @params (profilePic => input type file)
	 * @return (type of error if error OR path to temp img (str))
	 */
	public function uploadTempPictureAction()
	{
		$targetPath   = PUBLIC_PATH . "/images/tmp/profile/pic/";
		$pathInfo     = pathinfo(basename($_FILES['profilePic']['name']));
		$targetPath  .= uniqid() . basename($_FILES['profilePic']['name']); 
		//$targetPath  .= uniqid() . $pathInfo['basename']; 
		
		$imgSize = getimagesize($_FILES['profilePic']['tmp_name']);
		
		if (empty($imgSize)) {
			// File is not an image
			echo 'errorFormat';
			return;
		}
		
		
		if(move_uploaded_file($_FILES['profilePic']['tmp_name'], $targetPath)) {
			// File uploaded

			// Resize image
			$image = Zend_Controller_Action_HelperBroker::getStaticHelper('ImageManipulator');
			
			$image->load($targetPath);
			if ($image->getRatio() >= 1.26) {
				// image is too wide
				// 400 and 200 are from signup-import-alert-img ratio
				$image->resizeToWidth(450);
			} elseif ($image->getRatio() < 1.26) {
				// image is too tall
				$image->resizeToHeight(360);
			}
			$image->save($targetPath);
				
			// ALTER TARGETPATH FOR DEVELOPMENT
			$targetPath = str_replace(PUBLIC_PATH,'',$targetPath);
			$targetPath = str_replace(array('.gif','.png'), '.jpg', $targetPath);

			echo $targetPath;
		} else{
			echo "errorUpload";
		}
		
	}

	
	/**
	 * get and return matches based on user's request/info
	 * @params (profilePic => input type file)
	 * @return (type of error if error OR path to temp img (str))
	 */
	public function getMatchesAction()
	{
		$post = $this->getRequest()->getPost();
		$matches = new Application_Model_Matches();
		
		if (in_array('games',$post['types'])) {
			// Games are selected
			$options = array();
			if (!empty($post['sports'])) {
				// Sports is not empty
				$sportStr  = implode("','",$post['sports']);
				$options[] = "g.sport IN ('" . $sportStr  . "')";
			}

			$points = ($post['points'] != 'false' ? $post['points'] : false);
			$games = new Application_Model_Games();
			$games->findUserGames($this->view->user, $options, $points);
			$matches->addMatches($games->games);
		}
		if (in_array('teams',$post['types'])) {
			// Teams are selected
			$options = array();
			if (!empty($post['sports'])) {
				// Sports is not empty
				$sportStr  = implode("','",$post['sports']);
				$options[] = "t.sport IN ('" . $sportStr  . "')";
			}
			$teams = new Application_Model_Teams();
			$teams->findUserTeams($this->view->user, $options);
			$matches->addMatches($teams->teams);
		}
		
		$matches->sortByMatch();
		$this->view->matches = $matches->matches;
		
		$output = array();
		$memberHomepage = $this->view->getHelper('memberHomepage');
		$output[0] = $memberHomepage->buildFindBody();
		
		if (isset($matches->matches[0])) {
			// Matches exist
			foreach ($matches->matches as $match) {
				if (get_class($match) == 'Application_Model_Game') {
					// Get latitude and longitude
					$location = $match->getPark()->getLocation();
					$output[1][] = array($location->getLatitude(), $location->getLongitude());
				}
				
			}
		} else {
			$output[1][] = '';
		}
		
		echo json_encode($output);
		
		return;
		$jsonArray = array();
		
		foreach ($matches->matches as $match) {
			if (get_class($match) == 'Application_Model_Game') {
				// Get latitude and longitude
				$match->getPark()->getLocation()->parseLocation();
			}
			$jsonArray[] = $match->jsonSerialize();
		}
		
		echo json_encode($jsonArray);
		
	}
	
	/**
	 * get either new or old newsfeed data depending on $_POST['oldOrNew'] var
	 */
	public function getNewNewsfeedAction()
	{
		$newsfeed = new Application_Model_Notifications();
		$memberHomepage = $this->view->getHelper('memberHomepage');
		
		$request = $this->getRequest();
		if ($request->getPost('oldOrNew') == 'new') {
			// Get new notifications
			$newsfeed->getNewsfeed($this->view->user->city->cityID, true);
		} else {
			// Get old notifications
			$numNewsfeeds = $request->getPost('numNewsfeeds');
			$newsfeed->getNewsfeed($this->view->user->city->cityID, false, $numNewsfeeds . ',10'); 
		}
		$jsonArray = array();
		foreach ($newsfeed->read as $notification) {
			$jsonArray[] = $memberHomepage->createNotification($notification);
		}
		
		echo json_encode($jsonArray);
	}
	
	/**
	 * get city and state from db
	 * @params (zipcodeOrCity => either zipcode or city name (partial))
	 */
	public function getCityStateAction()
	{
		$zipcodeOrCity = $this->getRequest()->getPost('zipcodeOrCity');
		
		$cities = new Application_Model_Cities();
		$cityParts = explode(',', $zipcodeOrCity);
		$firstPart = trim($cityParts[0]);
		if (isset($cityParts[1]) &&
			(strlen(trim($cityParts[1])) >= 2)) {
			// State was input
			$cityParts[1] = trim($cityParts[1]);
			$state = substr($cityParts[1],0,2);
		} else {
			// default to california
			$state = 'CA';
		}
		$cities->getCitiesLike($firstPart, $state);
		echo $cities->jsonEncodeChildren('cities');
			
	}
	
	/**
	 * change user's city to new city (temporary), redirect to home afterward
	 * (cityID => new cityID)
	 */
	public function changeUserCityAction()
	{

		$cityID = $this->getRequest()->getPost('cityID');
		
		if ($cityID == 'home') {
			// Reset user location to home
			/* OR CREATE FUNCTION FOR USER MODEL TO FIND CITY INFO AND THEN USER_LOCATION (save db queries) */
			$this->view->user->resetHomeLocation();
			return;
		}
		
		$city = new Application_Model_City();
		$city->find($cityID, 'cityID');
		
		$location = new Application_Model_Location();
		$location->getLocationByCityID($cityID);
		
		$this->view->user->changedLocation = true;
		$this->view->user->city = $city;
		$this->view->user->location = $location;
				
	}


	/**
	 * search entire database for matches to search
	 * (search => search term)
	 */
	public function searchDbAction()
	{
		$searchTerm = $this->getRequest()->getPost('search');
		
		$cityID  = $this->view->user->city->cityID;
		
		$search  = new Application_Model_Search();
		$results = $search->getSearchResults($searchTerm, $cityID);
		echo json_encode($results);
		
	}
	
	/** 
	 * search db for league location specifically
	 */
	public function searchDbForLeagueLocationAction()
	{

		$locationName = $this->getRequest()->getPost('locationName');
		$address	  = $this->getRequest()->getPost('address');
		
		$auth = Zend_Auth::getInstance();
		$user = $auth->getIdentity();
		
		$cityID = $user->city->cityID;
		
		$search = new Application_Model_Search();
		$results = $search->getLeagueLocationResults($locationName, $address, $cityID);
		
		echo json_encode($results);
	}
	
	
	/** 
	 * handle click of "confirm", "deny", or "join" button clicks from user's notification dropdown
	 */
	public function notificationActionAction()
	{
		$post = $this->getRequest()->getPost();
		
		if (isset($post['confirmOrDeny'])) {
			// Confirm or deny action
			if ($post['confirmOrDeny'] == 'confirm') {
				// Confirm action, add to db
				$type = $post['type'];
				
				$mapper = new Application_Model_NotificationsMapper();
				
				$mapper->notificationConfirm($post['notificationLogID'], $type);
				
			}
			
			// Delete notification
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->delete('notification_log',array('notificationLogID = ?' => $post['notificationLogID']));
			
			/* If cannot maintain integrity of $auth user notifications, clearIdentity and force reload of everything */
			//$auth = Zend_Auth::getInstance();
			//$auth->clearIdentity();
			
			$this->view->user->notifications->deleteNotificationByID($post['notificationLogID']);
			
		}
			
	}
	

	/*
	 * change team/group name
	 */
	public function changeTypeAttribsAction()
	{
		$options = $this->getRequest()->getPost('options');
		
		if ($options['idType'] == 'teamID') {
			// Team
			$type = $this->view->user->teams->teamExists($options['typeID']);
		} elseif ($options['idType'] == 'groupID') {
			// Group
			$type = $this->view->user->groups->groupExists($options['typeID']);
		}
		

		
		if (!empty($options['city'])) {
			// City is being changed
			$cityID = $type->getMapper()->getForeignID('Application_Model_DbTable_Cities','cityID', array('city' => $options['city']));
			
			if (!$cityID) {
				// City not found in db
				return false;
			}

			$type->cityID = $cityID;
			$type->city   = $options['city'];
		}
		
		if (!empty($options['sport'])) {
			// Sport is being set
			$sportID =  $type->getMapper()->getForeignID('Application_Model_DbTable_Sports','sportID', array('sport' => $options['sport']));
			
			if (!$sportID) {
				// City not found in db
				return false;
			}
			
			$type->sport = $options['sport'];
			$type->sportID = $sportID;
		}
		
		if (!empty($options['public'])) {
			// Public attrib changed
			$type->public = (strtolower($options['public']) == 'public' ? '1' : '0');
		}
		
		if (!empty($options['rosterLimit'])) {
			// Roster limit changed
			$type->rosterLimit = $options['rosterLimit'];
		}
		
		$type->save(false);
	}

	
	/*
	 * change team/group name
	 */
	public function changeTypeNameAction()
	{
		$options = $this->getRequest()->getPost('options');
		
		if (empty($options['name']) || empty($options['typeID'])) {
			// No name or id, return
			return;
		}
		
		if ($options['idType'] == 'teamID') {
			// Team
			$type = $this->view->user->teams->teamExists($options['typeID']);
			$type->teamName = $options['name'];
		} elseif ($options['idType'] == 'groupID') {
			// Group
			$type = $this->view->user->groups->groupExists($options['typeID']);
			$type->groupName = $options['name'];
		}
		
		$type->save();
	}
		
	
	/*
	 * change team/group captain
	 */
	public function changeCaptainAction()
	{
		$options = $this->getRequest()->getPost('options');
		
		$auth = Zend_Auth::getInstance();
		$user = $auth->getIdentity();
		
		
		if ($options['idType'] == 'teamID') {
			// Team
			$model = $user->teams->teamExists($options['typeID']);
		} elseif ($options['idType'] == 'groupID') {
			// Group captain
			$model = new Application_Model_Group();		
		}
		
		$model->captain = $options['userID'];
		
		$model->save();
	}

	
	
	/*
	 * remove player from group or team
	 */
	public function removeUserFromTypeAction()
	{
		$options = $this->getRequest()->getPost('options');

		if ($options['idType'] == 'teamID') {
			// delete user from team
			$table = new Application_Model_DbTable_UserTeams();
		} elseif($options['idType'] == 'groupID') {
			$table = new Application_Model_DbTable_UserGroups();
		} elseif($options['idType'] == 'gameID') {
			$table = new Application_Model_DbTable_UserGames();
		}
			
		if (empty($options['typeID']) || empty($options['userID'])) {
			return false;
		}
			
		$where = array();
		$where[] = $table->getAdapter()->quoteInto($options['idType'] . ' = ?', $options['typeID']);
		$where[] = $table->getAdapter()->quoteInto('userID = ?', $options['userID']);
		
		$table->delete($where);
				
	}
	
	/** 
	 * handle click of "in" or "out" button clicks from game confirmation
	 */
	public function confirmUserAction()
	{
		$post = $this->getRequest()->getPost();
		
		$inOrOut = ($post['inOrOut'] == 'in' ? 1 : 0);
		$type	 = $post['type'];
		$typeID  = $post['id'];
		$insertOrUpdate = $post['insertOrUpdate'];
		$teamID  = $post['teamID'];
		
		$auth = Zend_Auth::getInstance();
		$user = $auth->getIdentity();
		
		$mapper  = new Application_Model_GamesMapper();
		
		if ($type == 'pickupGame') {
			// Pickup game
			$mapper->savePickupGameConfirmation($this->view->user->userID, $typeID, $inOrOut, $insertOrUpdate);
			$idType = 'gameID';
		} elseif ($type == 'teamGame') {
			$mapper->saveTeamGameConfirmation($this->view->user->userID, $typeID, $inOrOut, $insertOrUpdate, $teamID);
			$idType = 'teamGameID';
		}
				
		$game = $user->games->gameExists($typeID, $idType);
		if ($insertOrUpdate == 'insert') {
			$game->confirmedPlayers = $game->confirmedPlayers + 1;
		} else {
			$game->movePlayerConfirmation($user->userID, $inOrOut);
		}
	}
	
	
	/**
	 * add/edit team game from team captain's actions
	 */
	public function addTeamGameAction()
	{
		$post = $this->getRequest()->getPost();
		
		if (isset($post['winOrLoss'])) {
			// Must be edit of old game to tell win or loss
			$game = new Application_Model_Game();
			$game->setPrimaryKey('teamGameID');
			
			$game->teamGameID = $post['teamGameID'];
			
			if ($post['winOrLoss'] == 'delete') {
				$game->delete();
			}
			$game->winOrLoss  = $post['winOrLoss'];
			
			$game->save();
			
		} else {
			// Otherwise editing/adding game 
			$game = new Application_Model_Game();
			$game->setPrimaryKey('teamGameID');
			
			$game->opponent = $post['opponent'];
			$game->teamGameID = $post['teamGameID'];
			$game->locationName = $post['location'];
			$game->streetAddress = $post['address'];
			
			if (empty($post['locationID'])) {
				// No location ID was chosen, search db for similar location else add it
				$post['locationID'] = $game->searchDbForLeagueLocation($post['location'], $post['address'], $this->view->user->city->cityID);
			} else {
				$data = array('locationName' => $post['location'],
							  'streetAddress' => $post['address']);
							  
				$game->updateLeagueLocation($post['locationID'], $data);
			}
			
			$game->leagueLocationID = $post['locationID'];
			$game->teamID = $post['teamID'];
			
			$time = $post['month'] . '-' . $post['day'] . '-' . $post['year'] . ' ' . $post['time'];
			
			$date = DateTime::createFromFormat('m-j-Y g:ia',$time);
			
			$game->setDate($date->format('Y-m-d H:i:s'));
			
			$game->save();
			
			$notificationsMapper = new Application_Model_NotificationsMapper();
			
			$notificationDetails = array('action' => 'edit',
										 'type'	  => 'team',
										 'details'=> 'schedule');
										 
			$data = array('actingUserID' => $this->view->user->userID,
						  'teamID'		 => $post['teamID'],
						  'cityID'		 => $this->view->user->city->cityID);
						  
			$notificationsMapper->addNotification($notificationDetails, $data);
		}
	}
			

}

