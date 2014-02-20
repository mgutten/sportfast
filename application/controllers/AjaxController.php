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
	 * edit user's details
	 */
	public function editUserAction()
	{

		$post = $this->getRequest()->getPost('attribs');
		foreach ($post as $attrib => $val) {

			$this->view->user->$attrib = $val;
		}
		
		$this->view->user->save(false);
	}
	
	/**
	 * set userRatingID to "incorrect" and to be reviewed as inaccurate rating
	 */
	public function flagRemovalAction()
	{
		$userRatingID = $this->getRequest()->getPost('userRatingID');
		
		$ratingsMapper = new Application_Model_RatingsMapper();
		
		$ratingsMapper->setUserRatingIncorrect($userRatingID);
		
	}
	
	/**
	 * delete game/team invites
	 */
	public function deleteInvitesAction()
	{
		$options = $this->getRequest()->getPost('options');
		$emails = explode(',', $options['emails']);
		
		foreach ($emails as $email) {
			$invite = new Application_Model_Invite();
			$invite->email = $email;
			$invite->$options['idType'] = $options['typeID'];
			$invite->actingUserID = $this->view->user->userID;
			
			$invite->delete();
		}
		
	}
		
	
	/**
	 * minimal signup from game/team page of non-member
	 */
	public function minimalSignupAction()
	{
		$options = $this->getRequest()->getPost('options');
		
		$user = new Application_Model_User();
		$user->firstName = $options['firstName'];
		$user->lastName = $options['lastName'];
		$user->username = $options['email'];
		$user->password = $user->hashPassword($options['password']);
		$user->account = '1';
		
		
		$user->save(false);
		
		$user->login();
		
		
	}
	
	/**
	 * admin handle the removal or reinstatement of rating
	 */
	public function updateFlaggedRatingAction()
	{
		$options = $this->getRequest()->getPost('options');
		
		$rating = new Application_Model_Rating();
		
		$rating->userRatingID = $options['userRatingID'];
		
		if ($options['remove'] == 1) {
			// Delete this rating
			$rating->find($options['userRatingID'], 'userRatingID');
			$sportID = $rating->sportID;
			$userID = $rating->receivingUserID;
			$rating->delete();
			
			$user = new Application_Model_User();
			$user->userID = $userID;
			$user->setUserRating('skill',$sportID);
			$user->setUserRating('attendance',$sportID);
			$user->setUserRating('sportsmanship', $sportID);
			
		} else {
			$rating->incorrect = NULL;
			$rating->save();
		}
		
	}
	
	/**
	 * rate user from rateGame alert
	 */
	public function rateUserAction()
	{
		$post = $this->getRequest()->getPost();
		$ratings = $post['ratings'];
		
		foreach ($ratings as $rating) {	
			// Loop through		
			$sportRating = new Application_Model_RelativeRating();
			$sportRating->actingUserID = $this->view->user->userID;
			$sportRating->setCurrent('dateHappened');
			$sportRating->sportRatingID = $rating['sportRatingID'];
			
			if (isset($rating['oldGameID'])) {
				$sportRating->oldGameID = $rating['oldGameID'];
			} else {
				$sportRating->teamGameID = $rating['teamGameID'];
			}
			
			if (isset($rating['noShow'])) {
				// User did not show up
				
				$sportRating->winningUserID = $rating['winningUserID'];
				$sportRating->noShow = '1';
				
			} else {
				$sportRating->winningUserID = $rating['winningUserID'];
				$sportRating->losingUserID = $rating['losingUserID'];
			}
			
			$sportRating->save();
		}
	}
	
	/**
	 * rate user or park
	 */
	public function rateTypeAction()
	{
		$post = $this->getRequest()->getPost();
		$options = $post['options'];
		$type = $post['type'];
		
		$notification = new Application_Model_Notification();
		$notification->actingUserID = $this->view->user->userID;
		$notification->action = 'rate';
		$notification->cityID = $this->view->user->cityID;
		
		
		if ($type == 'user') {
			// Is user rating
			$rating = new Application_Model_Rating();
			$rating->receivingUserID = $options['userID'];
			$rating->givingUserID = $this->view->user->userID;
			$rating->sport = $options['sport'];
			$rating->sportID = $rating->getMapper()->getSportID($options['sport']);
			$rating->setCurrent('dateHappened');
			$rating->gameID = $options['gameID'];
			echo $options['noShow'];
			if (!empty($options['noShow'])) {
				$rating->attendance = 0;
			}  elseif (empty($options['notSure'])) {
				// Not sure was not selected
				$rating->attendance = 1;
				$rating->skill = $rating->getMapper()
										->getForeignID('Application_Model_DbTable_Ratings','ratingID',array('type' => 'user',
																										   'ratingType' => 'skill',
																										   'ratingName' => $options['skill']));
				$rating->sportsmanship = $rating->getMapper()
												->getForeignID('Application_Model_DbTable_Ratings','ratingID',array('type' => 'user',
																												   'ratingType' => 'sportsmanship',
																												   'ratingName' => $options['sportsmanship']));
																											   
				$rating->bestSkill = $rating->getMapper()
											->getForeignID('Application_Model_DbTable_SportSkills','sportSkillID',array('sport' => $options['sport'],
																													'skilling' => $options['bestSkill']));
			} else {
				// Not sure was selected
				
				$rating->receivingUserID = '';
			}
																													
			$rating->save(false);
			
			if (!empty($options['notSure'])) {
				// Not sure was selected, return
				return;
			}
			
			$notification->receivingUserID = $options['userID'];
			$notification->type = 'user';
			$notification->ratingID = $rating->userRatingID;
			
			$notification->save();
			
			$user = new Application_Model_User();
			$user->userID = $options['userID'];
			if (empty($options['noShow']) && empty($options['notSure'])) {
				$user->setUserRating('skill', $rating->sportID);
				$user->setUserRating('sportsmanship', $rating->sportID);
			} else if (!empty($options['noShow'])) {
				$user->setUserRating('attendance', $rating->sportID);
			}
			
		} elseif ($type == 'park') {
			// Park rating
			$rating = new Application_Model_Rating();
			$rating->parkID = $options['parkID'];
			$rating->userID = $this->view->user->userID;
			$rating->sport = $options['sport'];
			$rating->sportID = $rating->getMapper()->getSportID($options['sport']);
			$rating->setCurrent('dateHappened');
			$rating->gameID = $options['gameID'];
			$rating->success = $options['success'];
			$rating->quality = $options['quality'];
			$rating->comment = $options['comment'];
			
			$rating->setPark();
			
			if ($rating->success == '0') {
				// Unsucessful game, only allow one unsuccessful rating per game

				if ($rating->getUnsuccessfulParkRating($options['parkID'], $options['gameID'])) {
					// Was already rated for this game, return
					return;
				}
			}
																												
			$rating->save(false);
			
			$notification->type = 'park';
			$notification->parkID = $options['parkID'];
			
			$notification->save();
			
		}
		
		
		
	}
	
	
	/**
	 * get new notifications for user
	 */
	public function getNewNotificationsAction()
	{
		$timestamp = $this->getRequest()->getParam('timestamp');
		
		if ($this->view->user) {
		
			$this->view->user->resetNewNotifications()
							 ->getNewUserNotifications($timestamp);
						 
			$numNew = count($this->view->user->notifications->unread);
			
			$output = '';
			
			foreach ($this->view->user->notifications->unread as $notification) {
				$buttons = '';
				$remove = '';
				$class = 'light-back';
				if (!is_object($notification)) {
					continue;
				}
				if ($notification->hasValue('actionRequired')) {	
					$buttons = "<div class='notification-button-container clear-right' notificationLogID='" . $notification->notificationLogID . "' type='" . $notification->type . "' action='" . $notification->action . "'>";
					$buttons .= "<p class='button notification-action-button'>Confirm</p>";
					$buttons .= "<p class='button notification-action-button notification-action-button-second'>Decline</p>";
					$buttons .= "</div>";
					$remove = "<p class='light larger-text right notification-remove' tooltip='Delete'>x</p>";
				} elseif ($notification->hasValue('joinOption')) {
					$buttons = "<div class='notification-button-container clear-right' notificationLogID='" . $notification->notificationLogID . "' type='" . $notification->type . "' action='" . $notification->action . "'>";
					$buttons .= "<p class='button notification-join'>Join</p>";
					$buttons .= "</div>";
					$remove = "<p class='light larger-text right notification-remove' tooltip='Delete'>x</p>";
				}
				
				
				if ($notification->isSports()) {
					// Special case for smaller sports pictures
					$pictureClass = 'notification-picture-sports';
				} else {
					$pictureClass = 'notification-picture';
				}
				
				$output .=  "<a href='" . $notification->getFormattedUrl() . "' class='notification-container " . $class . " pointer' notificationLogID = '" . $notification->notificationLogID . "'>" 
							 . "<div class='notification-text-picture-container'>"
								 . "<img src='" . $notification->getPicture() . "' class='" . $pictureClass . "' />"
								 . "<span class='notification-text'>" . $notification->getFormattedText() . "</span>"
								 . $remove
								 . $buttons
							 . "</div>"
							 . "<span class='notification-time-subscript light'>" . $notification->getTimeFromNow() . "</span>"
							 . "</a>";
			
			}
			
			$return = array();
			$return[0] = $numNew;
			$return[1] = $output;
			
			echo json_encode($return);
		}
		
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
		$auth->clearIdentity(); // clear user identity when check notifications so system can regroup new and old notifications
		
		
	}
	
	/**
	 * create notification based on given parameters
	 */
	public function createNotificationAction()
	{
		 $options = $this->getRequest()->getPost('options');
		 $notificationsMapper = new Application_Model_NotificationsMapper();
		 
		 $notification = new Application_Model_Notification();
		 
		 $notification->action = $options['action'];
		 $notification->type = $options['type'];
		 $notification->details = $options['details'];
		 $notification->actingUserID = $options['actingUserID'];
		 $notification->cityID = $this->view->user->city->cityID;
		 /*
		 
		 $notificationDetails = array('action'  => $options['action'],
									  'type'	=> $options['type'],
									  'details' => $options['details']);
		*/

		 if ($options['receivingUserID'] == 'captain') {
			 if ($options['type'] == 'team') {
				 $table = 'TeamCaptains';
			 } else {
				 // Game
				 $table = 'GameCaptains';
			 }
			 $options['receivingUserID'] = $notificationsMapper->getForeignID('Application_Model_DbTable_' . $table, 'userID', array($options['idType'] => $options['typeID']));
		 }
		 
		 $notification->receivingUserID = $options['receivingUserID'];
		 
		/*
		 $data = array('actingUserID' 	  => $options['actingUserID'],
		 			   'receivingUserID'  => $options['receivingUserID'],
					   'cityID'		 	  => $this->view->user->city->cityID);	
		*/	
					   
		if (!empty($options['idType'])) {
			//$data[$options['idType']] = $options['typeID'];
			$notification->$options['idType'] = $options['typeID'];
		}
		
		$notification->save();  
		// $notificationsMapper->addNotification($notificationDetails, $data);
		 
	 }
	 
	 /**
	  *  subscribe/unsubscribe from game
	  */
	 public function subscribeToTypeAction()
	 {
		 $options = $this->getRequest()->getPost('options');
		 
		 $table = new Application_Model_DbTable_GameSubscribers();
		 
		
		 if ($options['subscribe'] == '0') {
			 // Unsubscribe
			 $where = array('userID = ?' => $options['userID'],
						$options['idType'] . ' = ?' => $options['typeID']);
			 $table->delete($where);
		 } else {
			 // Subscribe
			 $data = array('userID' => $options['userID'],
			 			   $options['idType'] => $options['typeID'],
						   'joinDate' => new Zend_Db_Expr('now()'));
			 $table->insert($data);
		 }
	 }
	 
	 /**
	  * update team avatar (from team page)
	  */
	 public function updateTeamAvatarAction()
	 {
		 $options = $this->getRequest()->getPost('options'); 
		 $teamID = $options['teamID'];
		 $avatar = $options['avatar'];
		 
		 $team = $this->view->user->teams->exists($teamID);
		 
		 $team->picture = $avatar;
		 
		 $team->save();
	 }
	 
	 /**
	  * change doNotEmail column for current user in db for a given game
	  */
	 public function updateEmailAlertSubscribedGameAction()
	 {
		$options = $this->getRequest()->getPost('options'); 
		
		$table = new Application_Model_DbTable_GameSubscribers();
		
		$where = array('userID = ?' => $this->view->user->userID,
					   'gameID = ?' => $options['gameID']);
		
		$data = array($options['column'] => $options['onOrOff']);	
		
		if ($options['column'] == 'gameOn') {
			// Updating games table instead
			$table = new Application_Model_DbTable_Games(); 
			$where = array('gameID = ?' => $options['gameID']);
		}		
		
		
		$table->update($data, $where);
	 }
	 
	 /**
	  * update sendReminder for game
	  */
	 public function updateSendReminderAction()
	 {
		 $options = $this->getRequest()->getPost('options'); 
		 
		 if (empty($options['gameID']) ||
		 	 empty($options['hour'])) {
				 return;
			 }
			 
		$game = new Application_Model_Game();
		$game->gameID = $options['gameID'];
		$game->sendReminder = $options['hour'];
				
		$game->save(false);
	 }
	 
	  /**
	  * update user's "plus" category for game
	  */
	 public function updateUserGamePlusAction()
	 {
		 $options = $this->getRequest()->getPost('options');
		 
		 if (empty($options['userID']) || empty($options['gameID'])) {
			 return false;
		 }
		 
		 $table = new Application_Model_DbTable_UserGames();
		 
		 $where = array();
		 $where[] = $table->getAdapter()->quoteInto('userID = ?', $options['userID']);
		 $where[] = $table->getAdapter()->quoteInto('gameID = ?', $options['gameID']);

		 $table->update(array('plus' => $options['plus']), $where);
	 }
	 
	 /**
	  * add member to game
	  */
	 public function addMemberToGameAction()
	 {
		 
		  $options = $this->getRequest()->getPost('options');
		  
		  if (empty($options['userID']) || empty($options['gameID'])) {
			 return false;
		  }
		  
		  
		  $game = new Application_Model_Game();
		  $game->gameID = $options['gameID'];
		  $fail = $game->addMemberToGame($options['userID']); // echo result so js can handle case where user is not added (already a member)
		  
		  if (!$fail) {
			  if ($options['userID'] != $this->view->user->userID) {
				  // Is not current user adding themself
				  $notification = new Application_Model_Notification();
				  $notification->actingUserID = $this->view->user->userID;
				  $notification->receivingUserID = $options['userID'];
				  $notification->gameID = $options['gameID'];
				  $notification->action = 'become';
				  $notification->type = 'game';
				  $notification->details = 'member';
				  
				  $notification->save();
			  }
		  }
		  
		  echo $fail;
		 
	 }
	 
	 /**
	  * add user to game INACTIVE
	  */
	 public function addUserToGameAction()
	 {
		 $options = $this->getRequest()->getPost('options');
		 
		 if (empty($options['userID']) || empty($options['typeID'])) {
			 return false;
		 }
		 
		 if ($options['idType'] == 'gameID') {
			 // Add user to game, and add game to user's auth session
			 $game = new Application_Model_Game();
			 $game->getGameById($options['typeID']);
			 $this->view->user->games->addGame($game);
		 }
		 
		 $data = array($options['idType'] => $options['typeID'],
		 			   'userID'		      => $options['userID']);
					   
		 if (!empty($options['confirmed']) || $options['confirmed'] == '0') {
			 $data['confirmed'] = $options['confirmed'];
		 }
		 
		 $table = new Application_Model_DbTable_UserGames();
		 
		 if ($game->players->exists($options['userID'])) {
			 // User is in game, update row in db
		 	 $table->update($data, array('userID = ?' => $options['userID'],
			 							 'gameID = ?' => $options['typeID']));
		 } else {
		 
			 $table->insert($data);
		 }
		 
		 return;
		 $notifications = new Application_Model_Notifications();				  
		 $notifications->deleteAll(array('n.action' => array('leave',
															  'join'),
										  'n.type'   => 'game',
										  'nl.actingUserID' => $options['userID'],
										  'nl.gameID' => $options['typeID']));
		 
						
							  
		// Set session for stash alert when redirected back to games/index
		$session = new Zend_Session_Namespace('joinedGame');
		$session->joinedGame = true;
	 }
		 
	 /**
	  * add user to team
	  */
	 public function addUserToTeamAction()
	 {

		 $options = $this->getRequest()->getPost('options');
		 
		 if (empty($options['userID']) || empty($options['teamID'])) {
			 return false;
		 }

		 $table = new Application_Model_DbTable_UserTeams();
		 $team = new Application_Model_Team();
		 $team->getTeamById($options['teamID']);
		 $this->view->user->teams->addTeam($team);	 
		
		 
		 $table->insert(array('teamID' => $options['teamID'],
		 					  'userID' => $options['userID']));
							  
		 $notifications = new Application_Model_Notifications();
		 $notifications->deleteAll(array('n.action' => 'invite',
										 'nl.teamID' => $options['teamID'],
										 'n.type' => 'team',
										 'nl.receivingUserID' => $options['userID']));
										 
		 $notifications = new Application_Model_Notifications();				  
		 $notifications->deleteAll(array('n.action' => array('leave',
															  'join'),
										  'n.type'   => 'team',
										  'nl.actingUserID' => $options['userID'],
										  'nl.teamID' => $options['teamID']));
						
							  
	 }
	 
	 /**
	  * add user to reserve list
	  */
	 public function addUserToReserveAction()
	 {
		 $teamID = $this->getRequest()->getParam('teamID');
		 $userID = $this->getRequest()->getParam('userID');
		 $remove = $this->getRequest()->getParam('remove');
		 
		 $team = new Application_Model_Team();
		 
		 $team->teamID = $teamID;
		 
		 if (!empty($remove)) {
			 // Remove user, not add
			 $team->removeReserve($userID);
		 } else {
			 $teamReserveID = $team->addReserve($userID);
			 echo $teamReserveID;
		 }
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
		 
		 if ($options['idType'] == 'gameID') {
			 // Is game
			 $message->setGameMessage();
		 }
		 
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
	 * upload finalized profile pic
	 */
	public function uploadProfilePicAction()
	{
		$fileInfo = $this->getRequest()->getPost('fileInfo');
		
		if ($fileInfo['fileWidth'] == '' || $fileInfo['fileX'] == '') {
			// Spot check for failure
			return false;
		}

		$images = Zend_Controller_Action_HelperBroker::getStaticHelper('CreateImages');
		
		$images->createimages($fileInfo, $this->view->user->userID);
		
		$this->view->user->avatar += 1;
		
		$this->view->user->save(false);
	}
	
	
	/**
	 * rotate uploaded image
	 */
	public function rotateImageAction()
	{
		$src = $this->getRequest()->getPost('src');
		$leftOrRight = $this->getRequest()->getPost('leftOrRight');
		
		$path = PUBLIC_PATH . $src;
		
		$image = Zend_Controller_Action_HelperBroker::getStaticHelper('ImageManipulator');
		$image->load($path);
		
		$image->rotate($leftOrRight);
		if ($image->getRatio() >= 1.26) {
			// image is too wide
			// 400 and 200 are from signup-import-alert-img ratio
			$image->resizeToWidth(450);
		} elseif ($image->getRatio() < 1.26) {
			// image is too tall
			$image->resizeToHeight(360);
		}
		
		$newPath = PUBLIC_PATH . '/images/tmp/profile/pic/' . mt_rand(1, 200000) . '.jpg';
		$image->save($newPath);
		
		$newPath = str_replace(PUBLIC_PATH,'',$newPath);
		
		echo $newPath;
		
	}
	
	
	public function findParksAction()
	{
		$options = $this->getRequest()->getPost('options');
		
		$findMatches = Zend_Controller_Action_HelperBroker::getStaticHelper('FindMatches');
		$findMatches = $findMatches->findmatches('parks', $options, $this->view->user, false);
	
		$matches = $findMatches->getAll();
		
		
		$output = array();
		
		if (isset($matches[0])) {
			// Matches exist
			foreach ($matches as $match) {
				if ($match instanceof Application_Model_Park) {
					$location = $match->getLocation();
					$output[1][] = array($location->getLatitude(), $location->getLongitude());
					
					$width = $match->ratings->getStarWidth('quality') . '%';
					$star  = $this->view->ratingstar('small',$width);
					
					$output[2][] = array($match->parkName, $star, $match->stash, $match->parkID, $match->membershipRequired);
					
				}
				
			}
		} else {
			$output[1][] = '';
			$output[2][] = '';
		}
		
		echo json_encode($output);
	}
		
	
	/**
	 * get and return matches based on user's find page
	 */
	public function findMatchesAction()
	{
		
		$post    = $this->getRequest()->getPost();
		$options = $post['options'];
		$type    = $post['type'];
		$orderBy = strtolower($post['orderBy']);
		$offset  = $post['offset'];
		
		if ($orderBy != 'match') {
			$options['order'] = $orderBy;
		}
		
		$limit = '30';
		if (!empty($post['offset'])) {
			$limit .= ',' . $post['offset'];
		} else {
			$post['offset'] = 0;
		} 
		
		if ($orderBy == 'match') {
			// Order by match (done in php not mysql), no limit
			$limit = '10000';
		}
			
		$findMatches = Zend_Controller_Action_HelperBroker::getStaticHelper('FindMatches');
		$findMatches = $findMatches->findmatches($type,$options, $this->view->user, $limit);
		
		if ($type == 'players') {
			// Set session var so current sport is shown on user's page
			$session = new Zend_Session_Namespace('userSport');
			$sport = array_keys($options['sports']);
			$session->sport = $sport[0];
		}

		if ($orderBy == 'match') {
			// Order by in php (order by match)
			$matches = $findMatches->sortByMatch($post['offset'], 30);
		} else {
			// Order by in query
			$matches = $findMatches->getAll();
		}
		
		$type = rtrim($type,'s');
		
		$output = array();
		

		$output[0] = $this->view->find()->loopMatches($matches, $type, $post['offset']);
		
		
		if (isset($matches[0])) {
			// Matches exist
			foreach ($matches as $match) {
				if ($match instanceof Application_Model_Game) {
					// Get latitude and longitude
					$location = $match->getPark()->getLocation();
					$output[1][] = array($location->getLatitude(), $location->getLongitude());
				} elseif ($match instanceof Application_Model_Park) {
					$location = $match->getLocation();
					$output[1][] = array($location->getLatitude(), $location->getLongitude());
				}
				
			}
		} else {
			$output[1][] = '';
		}
		
		// Total rows
		$output[2] = $findMatches->totalRows;
		
		echo json_encode($output);
	}

	/**
	 * find leagues near user's city
	 */
	public function findLeaguesAction()
	{
		$options = $this->getRequest()->getPost('options');
		$sports  = $options['sports'];
		
		if (isset($options['limit'])) {
			// Limit has been set on number to return
			$limit = $options['limit'];
		} else {
			$limit = '30';
		}
		
		$leagues = new Application_Model_Leagues();
		
		$leagues->findLeagues($sports, $this->view->user->city->cityID);
		
		$output = array();
		
		$counter = 0;
		foreach ($leagues->getAll() as $league) {
			if ($counter >= $limit) {
				break;
			}
			
			$leagueLevelsArray = array();
			foreach ($league->leagueLevels as $leagueLevel) {
				$leagueLevelsArray[] = $leagueLevel->_attribs;
			}
			
			$output[] = array('leagueID' => $league->leagueID,
							  'leagueName' => $league->leagueName,
							  'sportID'	   => $league->sportID,
							  'sport'	   => $league->sport,
							  'city'	   => $league->city,
							  'leagueLevels' => $leagueLevelsArray);
			}
			
		echo json_encode($output);
	}
	
	
	/**
	 * get similar games on same day (for create game controller)
	 */
	public function getSimilarGamesAction()
	{
		$options = $this->getRequest()->getPost('options');

		$games = new Application_Model_Games();
		
		$where = array();
		if (isset($options['date'])) {
			// date is set, search by day
			$month = ($options['month'] < 10 ? 0 . $options['month'] : $options['month']);
			$date  = ($options['date'] < 10 ? 0 . $options['date'] : $options['date']);
			
			$where[] = "DATE(g.date) = '" . $options['year'] . "-" . $month . "-" . $date . "'";
		}
		
		if (isset($options['sportID'])) {
			// Search by sport
			$where[] = 'g.sportID = "' . $options['sportID'] . '"';
		}		
		
		$games->getGamesNearUser($where, $this->view->user);	
		
		$output = array();
		foreach ($games->getAll() as $game) {
			$output[] = array('gameID' => $game->gameID,
							  'gameTitle' => $game->getGameTitle(),
							  'parkName' => $game->park->parkName,
							  'parkID'	 => $game->park->parkID,
							  'hour'   => $game->getHour(),
							  'date'   => $game->getShortDate(),
							  'rosterLimit' => $game->rosterLimit,
							  'totalPlayers' => $game->totalPlayers);
		}
		
		echo json_encode($output);
		
	}
	
	/**
	 * get number of available players in area (for create game controller)
	 */
	public function getAvailableUsersAction()
	{
		$options = $this->getRequest()->getPost('options');
		
		$formattedDate = $options['month'] . '-' . $options['date'] . '-' . $options['year'] . ' ' . $options['hour'];
		
		
		$datetime = DateTime::createFromFormat('n-j-Y G',$formattedDate);
		
		
		$users = new Application_Model_Users();
		
		$users->getAvailableUsers($datetime, $options['sportID'], array('latitude' => $this->view->user->userLocation->latitude,
																		'longitude' => $this->view->user->userLocation->longitude));
		
		echo $users->countUsers();
	}
	
	
	/**
	 * get and return matches based on user's request/info (homepage)
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

			if (trim($post['time']) == 'my availability') {
				// Use user's availability
				$day = $hour = false;
			} else {
				$day = $hour = '';
			}
			
			$points = ($post['points'] != 'false' ? $post['points'] : false);
			
			$games = new Application_Model_Games();
			
			if ($this->view->user->isMinimal()) {
				// Minimal account, show all matches for any game/teams
				
				$options = array();
				$options['sports'] = array('basketball' => false,
										   'football'	=> false,
										   'ultimate'	=> false,
										   'volleyball'	=> false,
										   'soccer' 	=> false,
										   'tennis'		=> array('singles' => array('rally' => true,'match' => true),
										   						 'doubles' => array('rally' => true,'match' => true))
											);
											
				if ($points) {
					$options['points'] = $points;
				}

				$games->findGames($options, $this->view->user);
			} else {
				$games->findUserGames($this->view->user, $options, $points, $day, $hour);
			}
			
					
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
		
		$output = array();
		$memberHomepage = $this->view->getHelper('memberhomepage');
		$output[0] = $memberHomepage->buildFindBody($matches->getAll());
		
		if (isset($matches->matches[0])) {
			// Matches exist
			foreach ($matches->getAll() as $match) {
				if (get_class($match) == 'Application_Model_Game') {
					// Get latitude and longitude
					$location = $match->getPark()->getLocation();
					$output[1][] = array($location->getLatitude(), $location->getLongitude(), $match->parkName);
				}
				
			}
		} else {
			$output[1][] = '';
		}

		echo json_encode($output);
		
		return;
		/*
		$jsonArray = array();
		
		foreach ($matches->matches as $match) {
			if (get_class($match) == 'Application_Model_Game') {
				// Get latitude and longitude
				$match->getPark()->getLocation()->parseLocation();
			}
			$jsonArray[] = $match->jsonSerialize();
		}
		
		echo json_encode($jsonArray);
		*/
		
	}
	
	/**
	 * get either new or old newsfeed data depending on $_POST['oldOrNew'] var
	 */
	public function getNewNewsfeedAction()
	{
		$newsfeed = new Application_Model_Notifications();
		$memberHomepage = $this->view->getHelper('memberhomepage');
		
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
	 * get sport info
	 */
	public function getSportInfoAction()
	{
		$sportID = $this->getRequest()->getPost('sportID');
		
		$sportsMapper = new Application_Model_SportsMapper();
		
		$sportInfo = $sportsMapper->getSportInfo($sportID);
		
		echo json_encode($sportInfo);
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
		$city->changedLocation = true;
		
		
		$location = new Application_Model_Location();
		$location->getLocationByCityID($cityID);
		
		$this->view->user->city = $city;
		$this->view->user->location = $location;
		/*
		$this->view->user->changedLocation = true;
		$this->view->user->city->city = $city->city;
		$this->view->user->city->cityID = $city->cityID;
		$this->view->user->city->state = $city->state;
		$this->view->user->city->changedLocation = true;
		$this->view->user->location = $location;
		*/
						
	}


	/**
	 * search entire database for matches to search
	 * (search => search term)
	 */
	public function searchDbAction()
	{
		$searchTerm = $this->getRequest()->getPost('search');
		
		$cityID  = $this->view->user->city->cityID;
		
		$limit = $this->getRequest()->getPost('limit');
		
		$search  = new Application_Model_Search();
		$results = $search->getSearchResults($searchTerm, $cityID, $limit);
		
		echo json_encode($results);
		
	}
	
	/**
	 * check if email exists as a user
	 */
	public function emailExistsAction()
	{
		$email = $this->getRequest()->getPost('email');
		
		if (empty($email)) {
			return false;
		}
		
		$users = new Application_Model_Users();
		
		$results = $users->emailsExist(array($email));
		
		if ($results) {
			// Email exists
			echo 'true';
		}
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
		$options = $post['options'];

		if (isset($options['confirmOrDeny'])) {
			// Confirm or deny action
			$options['confirmOrDeny'] = strtolower($options['confirmOrDeny']);

			if ($options['confirmOrDeny'] == 'confirm' || ($options['type'] == 'user' && $options['action'] == 'check')) {
				// Confirm action, add to db	
					
				$mapper = new Application_Model_NotificationsMapper();
				$mapper->notificationConfirm($options['notificationLogID'], $options['confirmOrDeny'],$options['type'], $options['action']);
				
				// Reset session var and force reload to account for new team, game, or teamGame
				$reset = new Zend_Session_Namespace('reset');
				$reset->reset = true;
			}
						
		} else {
			// Join action
			$mapper = new Application_Model_NotificationsMapper();
			
			/*
			if (!$this->view->user->hasProfilePic()) {
				// No profile pic, prevent newly invited user from joining game without profile pic
				$session = new Zend_Session_Namespace('pictureRequired');
				$session->fail = 'join';
				echo '/users/' . $this->view->user->userID . '/upload';
				return;
			}
			*/

			$mapper->notificationConfirm($options['notificationLogID'], false, $options['type']);	
		}
		
		

		// Delete notification
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->delete('notification_log',array('notificationLogID = ?' => $options['notificationLogID']));
		
		
		/* If cannot maintain integrity of $auth user notifications, clearIdentity and force reload of everything */
		//$auth = Zend_Auth::getInstance();
		//$auth->clearIdentity();
		
		// Only removes from user model
		$this->view->user->notifications->deleteNotificationByID($options['notificationLogID']);
			
	}
	
	/**
	 * invite users to team game
	 */
	public function inviteToTeamGameAction()
	{
		$options = $this->getRequest()->getParam('options');
		
		if (!$options['userIDs']) {
			return false;
		}
		
		$notification = new Application_Model_Notification();
		$notification->actingUserID = $this->view->user->userID;
		$notification->action = 'invite';
		$notification->type = 'teamgame';
		$notification->teamID = $options['teamID'];
		$notification->teamGameID = $options['teamGameID'];
		
		foreach ($options['userIDs'] as $userID) {
			$notification->receivingUserID = $userID;
			$notification->save();
		}
		
		$this->_forward('invite-team-game','mail', null);
		
	}
	
	/**
	 * delete notification from db
	 */
	public function deleteNotificationAction()
	{
		$notificationLogID = $this->getRequest()->getParam('notificationLogID');
		
		if (empty($notificationLogID)) {
			return false;
		}
		
		$notification = new Application_Model_Notification();
		$notification->notificationLogID = $notificationLogID;
		
		$notification->delete();
		
		$this->view->user->notifications->deleteNotificationByID($notificationLogID);
		
	}
	
	/**
	 * delete profile picture
	 */
	public function deleteProfilePictureAction()
	{
		$userID = $this->view->user->userID;
		
		if (empty($userID)) {
			return false;
		}
		
		$folders = array('large',
						 'medium',
						 'small',
						 'tiny');
		
		$dir = 'images/users/profile/pic/';
		//$dir = 'X:/Program Files (x86)/wamp/www/Local_Site/sportfast.com/public/images/users/profile/pic/';
		
		foreach ($folders as $folder) {
			
			if (is_file($dir . $folder . '/' . $userID . '.jpg')) {
				unlink($dir . $folder . '/' . $userID . '.jpg');
			}
		}
	}
		
	
	/**
	 * delete team/game message
	 */
	public function deleteMessageAction()
	{
		$options = $this->getRequest()->getParam('options');
		
		if (empty($options['messageID'])) {
			return false;
		}
		
		if ($options['type'] == 'team') {
			// Is team message
			$idType = 'teamMessageID';
		} elseif ($options['type'] == 'game') {
			// Is game message
			$idType = 'gameMessageID';
		} else {
			// Is user message
			$idType = 'messageID';
		}
		
		$message = new Application_Model_Message();
		
		$message->$idType = $options['messageID'];
		
		$message->delete();
		
		
		
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
		} elseif ($options['idType'] == 'gameID') {
			// Group captain
			$model = $user->games->exists($options['typeID']);	
		}
		
		$model->captains = array();
		
		foreach ($options['userIDs'] as $userID) {
			$model->addCaptain($userID);
		}
		
		$model->updateCaptains();
	}
	
	/**
	 * remove all of team's games
	 */
	public function removeTeamGamesAction()
	{
		$teamID = $this->getRequest()->getPost('teamID');
		
		$team = new Application_Model_Team();
		
		$team->teamID = $teamID;
		
		$team->deleteGames();
	}
	
	/**
	 * remove team game
	 */
	public function removeTeamGameAction()
	{
		$teamGameID = $this->getRequest()->getPost('teamGameID');
		
		if (!$teamGameID) {
			return false;
		}
		
		
		$games = $this->view->user->games;
		$game  = $games->setPrimaryKey('teamGameID')
					   ->exists($teamGameID);
		
		if ($game) {
			$game->delete();			
		}
		
		$games->setPrimaryKey('teamGameID')
			  ->remove($teamGameID);
		$games->setPrimaryKey('gameID');
	}
			  
		
	/**
	 * remove player's sport info
	 */
	public function removeSportFromUserAction()
	{
		$options = $this->getRequest()->getPost('options');
		
		//$sport = new Application_Model_Sport();
		//$sportID = $sport->getSportIDByName($options['sport']);
		
		if (empty($options['userID']) || empty($options['sport'])) {
			echo 'Error: Could not remove sport information.  Empty values given.';
		}
		
		$this->view->user->removeSport($options['sport']);
		
		
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
			$types = 'teams';
			$type  = 'team';
		} elseif($options['idType'] == 'groupID') {
			$table = new Application_Model_DbTable_UserGroups();
			$types = 'groups';
			$type  = 'group';
		} elseif($options['idType'] == 'gameID') {
			$table = new Application_Model_DbTable_UserGames();
			$types = 'games';
			$type  = 'game';
		}
			
		if (empty($options['typeID']) || empty($options['userID'])) {
			return false;
		}
		
		$this->view->user->$types->remove($options['typeID']);
			
		$where = array();
		$where[] = $table->getAdapter()->quoteInto($options['idType'] . ' = ?', $options['typeID']);
		$where[] = $table->getAdapter()->quoteInto('userID = ?', $options['userID']);
		
		$table->delete($where);
				
	}
	
	/**
	 * remove friendship
	 */
	public function removeFriendAction()
	{
		$post = $this->getRequest()->getPost();
		$userID1 = $post['userID1'];
		$userID2 = $post['userID2'];
		
		if ($userID1 == $this->view->user->userID) {
			// Userid1 is current user, $otherUser is userID2
			$otherUserID = $userID2;
		} else {
			$otherUserID = $userID1;
		}
		
		
		$this->view->user->removeFriend($otherUserID);
	}
	
	/**
	 * cancel/delete game or team
	 */
	public function cancelTypeAction()
	{
		
		$options = $this->getRequest()->getPost('options');
		$idType  = $options['idType'];
		
		if (empty($options['typeID'])) {
			return false;
		}
		
		$array = array();
		
		if ($idType == 'gameID') {
			$model  = new Application_Model_Game();
			$model->getGameByID($options['typeID']);
			$array['date'] = $model->date;
			$array['cancelReason'] = (!empty($options['cancelReason']) ? $options['cancelReason'] : 'No reason given');
			$type = 'game';
		} elseif ($idType == 'teamID') {
			$model  = new Application_Model_Team();
			$model->getTeamByID($options['typeID']);
			$type = 'team';
		}
		
		$userIDs = $model->players->getIDs('users');
		
		
		$array['sport'] = $model->sport;
		$array['userIDs'] = $userIDs;
		
		
		// Set post data for email that is initiated in forward request
		$this->_request->setPost($array);
		
		if (!empty($options['onceOrAlways'])) {
			// remove recurring game just this once
			
			$model->canceled = '1';
			
			if (!empty($options['cancelReason'])) {
				$model->cancelReason = rtrim($options['cancelReason']);
			}
			
			$model->save();

			
		} else {
			// Mark game/team for removal
			//$model->delete();
			if ($type == 'game') {
				$model->canceled = '1';
			}
			$model->remove   = date("Y-m-d H:i:s", strtotime('+1 week'));
			
			$model->save();
		}
		
		
		$notification = new Application_Model_Notification();
		
		$notification->action = 'delete';
		$notification->type   =  $type;
		$notification->actingUserID = $this->view->user->userID;
		$notification->$options['idType'] = $options['typeID'];
		$notification->cityID = $this->view->user->cityID;
		$notification->save();
		
				
		// Force bootstrap reload
		$reset = new Zend_Session_Namespace('reset');
		$reset->reset = true;
		
		
		$this->_forward('cancel-type','mail', null);
		
	}
		
	/**
	 * uncancel a game
	 */
	public function uncancelGameAction()
	{
		$options = $this->getRequest()->getPost('options');
		$idType  = $options['idType'];
		
		if (empty($options['typeID'])) {
			return false;
		}
		
		if ($idType == 'gameID') {
			// Is game
			$game = new Application_Model_Game();
			$type = 'game';
			$$type->gameID = $options['typeID'];
			$$type->uncancel();
		}
		
		$this->_request->setPost($options);
		
		$reset = new Zend_Session_Namespace('reset');
		$reset->reset = true;
		
		
		$this->_forward('uncancel-type','mail', null);
	}
		
		
	
	
	/** 
	 * handle click of "in" or "out" button clicks from game confirmation
	 */
	public function confirmUserAction()
	{
		$post = $this->getRequest()->getPost();
		
		$post['inOrOut'] = strtolower($post['inOrOut']);
		
		if ($post['inOrOut'] == 'in') {
			$inOrOut = '1';
		} elseif ($post['inOrOut'] == 'out') {
			$inOrOut = '0';
		} else {
			// Maybe
			$inOrOut = '2';
		}
		
		$idType	 = $post['type'];
		$typeID  = $post['id'];
		//$insertOrUpdate = $post['insertOrUpdate'];
		//$teamID  = $post['teamID'];
		
		$auth = Zend_Auth::getInstance();
		$user = $auth->getIdentity();
		
		$game = $user->games->gameExists($typeID, $idType);
		
		if (!$game) {
			$game = new Application_Model_Game();
			
			if ($idType = 'gameID') {
				$game->getGameByID($typeID);
			}
			
			$this->view->user->games->addGame($game);
		}
		
		$game->$idType = $typeID;
		
		
		$game->addUserToGame($this->view->user->userID, $inOrOut);
			
		/*
		$mapper  = new Application_Model_GamesMapper();
		
		if ($idType == 'gameID') {
			// Pickup game
			$mapper->savePickupGameConfirmation($this->view->user->userID, $typeID, $inOrOut);
			
		} elseif ($idType == 'teamGameID') {
			$mapper->saveTeamGameConfirmation($this->view->user->userID, $typeID, $inOrOut);
			
		}
		*/
				
		$game->movePlayerConfirmation($user->userID, $inOrOut);

		
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
	
	/**
	 * delete user from db
	 */
	public function deleteUserAction()
	{
		$userID = $this->getRequest()->getParam('userID');
		
		
		if (empty($userID)) {
			return;
		}
		
		// Delete user
		$this->view->user->delete();
		
		// Clear session
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		
		setcookie('user', '', time() - 1, '/');
	}
			

}

