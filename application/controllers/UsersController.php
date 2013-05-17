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
	
	function uploadAction()
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
		$sport  = $this->getRequest()->getParam('sport');
		
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
	
	public function settingsAction()
    {
		$this->permission();
		$this->view->narrowColumn = 'false';
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

