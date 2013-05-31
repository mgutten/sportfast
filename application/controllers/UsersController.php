<?php

class UsersController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		
        $this->view->narrowColumn = 'left';
		
		$userID = $this->getRequest()->getParam('id');
		
		$user = new Application_Model_User();
		$user->getUserBy('u.userID', $userID);
		
		if ($user->fake) {
			
			$this->_redirect('/error/permission');
		}
		
		$user->getUserSportsInfo();
		$user->getUserFriendsGroupsTeams();
		$user->getUserRatings();
		$user->getUserGames(false);
		
		$this->view->currentUser = $user;

		$session = new Zend_Session_Namespace('userSport');
		if ($session->sport) {
			if (!$this->view->currentUser->hasSport($session->sport)) {
				// User does not play sport that was saved in session
				$this->view->selectedSport = false;
			} else {
				$this->view->selectedSport = $session->sport;
			}
		}
		

        $dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		$this->view->inviteButton = $dropdown->dropdownButton('invite-to', '', 'Invite to');
		
		// Get latest user activity
		$activities = new Application_Model_Notifications();
		$activities->getUserActivities($user);
		$this->view->activities = $activities->read;

		$this->view->memberHomepage = $this->view->getHelper('memberhomepage');
		
		//$this->view->user->notifications->deleteNotificationByID('24');
		//var_dump($this->view->user->notifications->read);
		
		
    }
	
	public function uploadAction()
	{
		$this->permission();
		$this->view->narrowColumn = 'false';
		
		$session = new Zend_Session_Namespace('goToURL');
		if ($session->url) {
			$this->view->goToURL = $session->url;
		}
	}
	
	public function ratingsAction()
    {
		$this->view->narrowColumn = 'right';
		$uri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
		$this->view->currentURI = rtrim($uri,'/');
		$this->view->baseURI    = preg_replace('/\/ratings(\/\w+)?/','',$this->view->currentURI);
		
		$userID = $this->getRequest()->getParam('id');
		$sport  = $this->view->sport = $this->getRequest()->getParam('sport');
		
		if (!$sport) {
			$this->_redirect($this->view->currentURI . '/basketball');
		}
		
        $user = new Application_Model_User();
		$user->getUserByID($userID);
		$user->getUserSportsInfo();
	
		
		$this->view->currentUser = $user;
		$this->view->userID = $userID;
		
		$this->view->sport = $this->view->currentUser->getSport($sport);
		$this->view->ratingOrder = array('skillCurrent'  => 'skill',
										 'sportsmanship' => 'sprtmn',
										 'attendance'	 => 'attnd');
		
		$this->view->lastRating = DateTime::createFromFormat('Y-m-d H:i:s', $this->view->user->lastRating)->format('U');

		if ($user->userID == $this->view->user->userID) {
			// User is on own ratings page
			$this->view->isUser = true;
			$this->view->user->setLastRatingCurrent()
							 ->save(false);
		}
		
		$this->view->ratings = $ratings = $user->getUserRatings()->getSport($sport)->ratings;
		$this->view->numRatings = $ratings->countRatings();
		$this->view->ratingWidth = $ratings->getStarWidth('quality') . '%';
		
		$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		
		$sportArray = array();
		foreach ($user->sports as $sportModel) {
			$sportArray[] = array('text'  => $sportModel->sport,
								  'image' => $sportModel->getIcon('small','solid'),
								  'href'  => $this->view->baseURI . '/ratings/' . strtolower($sportModel->sport));
		}
		
		$this->view->sportButton = $dropdown->dropdownButton('sports', $sportArray, ucwords($sport));
		

		
	}
	
	public function teamsAction()
	{
		$this->view->narrowColumn = 'right';
		
		$userID = $this->getRequest()->getParam('id');
		
		$user = new Application_Model_User();
		$user->getUserBy('u.userID', $userID);
		$user->getUserTeams();
		
		$this->view->currentUser = $user;

	}
	
	public function settingsAction()
    {
		$this->permission();
		$this->view->narrowColumn = false;
		
		$session = new Zend_Session_Namespace('goToURL');
		$session->url = '/users/' . $this->view->user->userID . '/settings';
		
		$signupForm = new Application_Form_Signup();
		$this->view->signupForm = $signupForm;
		
		$this->view->signupSportForm = new Application_Form_SignupSportForm();
		
		$this->view->subscribedGames = $this->view->user->getSubscribedGames();
		
		$sports = new Application_Model_Sports();
		$this->view->sports = $sports->getAllSportsInfo(true);
		$this->view->sportsArray = $sports->getAllSportsInfo();
		$this->view->userSports = $this->view->user->getSportNames();
		
	}
	
	public function updateInfoAction()
	{
		$post = $this->getRequest()->getPost();
		
		$user = $this->view->user;
		
		$post['dobYear'] = ($post['dobYear'] < date('y') ? '20' : '19') . $post['dobYear'];
		$post['dob'] = $post['dobYear'] . '-' . $post['dobMonth'] . '-' . $post['dobDay'];
		
		$user->username  = $post['email'];
		$user->firstName = $post['firstName'];
		$user->lastName = $post['lastName'];
		$user->dob = $post['dob'];
		$user->weight = $post['weight'];
		$user->setHeightFromFeetAndInches($post['heightFeet'], $post['heightInches']);
		$user->setAgeFromDob();
		
		
		
		if (!empty($post['signupPassword'])) {
			if ($post['signupPassword'] == $post['signupReenterPassword']) {
				// Passwords match
				$user->password = $user->hashPassword($post['signupPassword']);
			}
		}
		
		if (!empty($post['userLocation'])) {
			// Change userLocation
			$location = new Application_Model_Location();
			$location->location = $post['userLocation'];
			$location->userLocationID = $user->userLocation->userLocationID;
			$location->userID = $user->userLocation->userID;

			$user->userLocation = $location;
			
			$user->cityID = $user->getCity()
								 ->getCityFromZipcode($post['zipcode'])
							 	 ->cityID;
			
			$city = new Application_Model_City();
			$city->setAttribs($user->getCity()->_attribs); // Store user city model to reinstate after save			 
			
			$user->streetAddress = $post['streetAddress'];
			$user->city = $user->getCity()->city;
			
			$user->userLocation->save();
		}
		
		
		$user->save(false);
		
		$user->city = $city;
		
		
		$this->_redirect('/users/' . $user->userID . '/settings');
	}
	
	
	public function updateSportsAction()
	{
		$post = $this->getRequest()->getPost();
		
		$sportModel = new Application_Model_Sport();
		
		$sports = new Application_Model_Sports();
		$sportsArray = $sports->getAllSportsInfo();
		
		$user = $this->view->user;
		
		foreach ($sportsArray as $sport => $section) {
			if ($post[$sport] !== 'true') {
				// Sport was not selected
				continue;
			}
			
			$sportModel = $user->getSport($sport);
			
			$sportModel->userID = $user->userID;
			
			if ($sportModel->hasValue('sportID')) {
				// Sport was already in user model
				$skillInitial = $sportModel->skillInitial;
				$skillCurrent = $sportModel->skillCurrent;
				$attendance = $sportModel->attendance;
				$sportsmanship = $sportModel->sportsmanship;
				
				$user->removeSport($sport, false);
				
				$sportModel = $user->getSport($sport);
				$sportModel->userID = $user->userID;
				$sportModel->skillInitial = $skillInitial;
				$sportModel->skillCurrent = $skillCurrent;
				$sportModel->attendance = $attendance;
				$sportModel->sportsmanship = $sportsmanship;
				
			}
			
			$sportModel->often = $post[$sport . 'Often'];
			$sportModel->sport = $sport;
			
			// Convert rating from slider (0-6) to meaningful rating (64-100)
			if (!empty($post[$sport . 'Rating'])) {
				$sportModel->skillInitial = $sportModel->convertSliderToRating($post[$sport . 'Rating']); 
				$sportModel->skillCurrent = $sportModel->skillInitial;
			
				$sportModel->sportsmanship = 80;
				$sportModel->attendance	   = 100;
			}
			
			$formats = explode(',', $post[$sport . 'What']);
			
			foreach	($formats as $format) {
				// Loop through and create user format selection (e.g. Pickup, League, Weekend Tournament)
				 $formatModel = $sportModel->getFormat($format);
				 $formatModel->format = strtolower($format);

			}

			
			if (!empty($post[$sport . 'Type'])) {
				// Type is set
				$types = explode(',', $post[$sport . 'Type']);
				$typeNames = array();
				foreach ($types as $type) {	
					// Loop through types and create models
					$type = strtolower($type);
					if (!empty($sportsArray[$sport]['type'][$type])) {
						// $type is typeName
						$typeNames[] = $type;
						//$typeModel   = $sportModel->getType($type);
						//$typeModel->typeName = $type;
					} else {
						// $type is typeSuffix
						$typeSuffixes[] = $type;
					}
				}
				
				foreach ($typeNames as $typeName) {
					foreach ($typeSuffixes as $typeSuffix) {
						// Create new type model foreach typeName/typeSuffix combo
						$typeModel = $sportModel->getType($typeName);
						$typeModel->typeName   = $typeName;
						$typeModel->typeSuffix = $typeSuffix;
					}
				}
			} else {
				// No type set, create type for base type of "pickup"
				$typeModel = $sportModel->getType('pickup');
				$typeModel->typeName = 'pickup';
			}
			
			
			if (!empty($post[$sport . 'Position'])) {
				// Position is set
				$positions = explode(',', $post[$sport . 'Position']);
				foreach ($positions as $position) {	
					// Loop through types and create models
					$positionModel = $sportModel->getPosition($position);
					$positionModel->positionAbbreviation = $position;
				}

			} else {
				// No position set, create base position "null" for sportID
				$positionModel = $sportModel->getPosition('null');
			}
			
			
			for ($i = 0; $i < 6; $i++) {
				if (empty($post[$sport . 'Availability' . $i])) {
					// Day has no availabilities saved
					continue;
				}
				$hours = explode(',', $post[$sport . 'Availability' . $i]);
										
				foreach ($hours as $hour) {
					$availabilityModel = $sportModel->setAvailability($i, $hour);
					$availabilityModel->day  = $i;
					$availabilityModel->hour = $hour;
				}
				
			}
			
			$sportModel->save();
			
			$user->setUserRating('skill',$sport);
			$user->setUserRating('attendance',$sport);
			$user->setUserRating('sportsmanship',$sport);
			
		}
		
		$this->_redirect('/users/' . $user->userID . '/settings');

	}


	public function inboxAction()
	{
		$this->permission();	
		$this->view->narrowColumn = 'false';
		
		$groupMessageID = $this->getRequest()->getParam('sport'); // Bootstrap user route has "sport" saved as third parameter, use that
		
		if ($groupMessageID) {
			$this->_forward('message');
		}
		
		$messages = new Application_Model_UserMessages();
		
		$messages->getUserMessageGroups($this->view->user->userID);
		
		$this->view->messages = $messages->read;
		$this->view->numConversations = count($messages->read);
	}
	
	public function messageAction()
	{
		$this->permission();
		$messageGroupID = $this->getRequest()->getParam('sport'); // Bootstrap user route has "sport" saved as third parameter, use that
		
		if (!$messageGroupID) {
			$this->_redirect('/users/' . $this->view->user->userID . '/inbox');
		}
		
		$messages = new Application_Model_UserMessages();
		$messages->getMessageGroup($messageGroupID);
		
		$otherUserID = $messages->getOtherUserID($this->view->user->userID);
		
		$otherUser = new Application_Model_User();
		$otherUser->getUserByID($otherUserID);
		
		$this->view->otherUserName = $otherUser->shortName;
		
		$this->view->messages = $messages->read;
		
		
		$postForm = new Application_Form_PostMessage();
		$postForm->setAction('/post/message');
		$postForm->login->setName('submitPostMessage');
		$postForm->messageType->setValue('user');
		$postForm->messageGroupID->setValue($messageGroupID);
		$postForm->receivingUserID->setValue($messages->getOtherUserID($this->view->user->userID));
		$this->view->postForm = $postForm;
		
		if ($messages->hasValue('read')) {
			foreach ($messages->read as $message) {
				
				if ($message->userID == $this->view->user->userID) {
					continue;
				}
				$message->read = '1';
				
				$message->save();
			}
		}
		
	}
	
	/**
	 * check if message group exists and redirect to appropriate page (from "Message" button on user profile)
	 */
	public function groupAction()
	{
		$receivingUserID = $this->getRequest()->getParam('sport');
		$userID = $this->view->user->userID;
		
		$messages = new Application_Model_Messages();
		
		$messageGroupID = $messages->messageGroupExists($receivingUserID, $userID);
		
		$this->_redirect('/users/' . $userID . '/inbox/' . $messageGroupID);
		
	}
	
	public function permission()
	{
		if ($this->view->user->userID != $this->getRequest()->getParam('id')) {
			// Not this user
			return $this->_forward('permission', 'error', null);
		}
		
	}

}

