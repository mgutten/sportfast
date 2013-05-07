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
		if ($this->view->user->userID != $this->getRequest()->getParam('id')) {
			// Not this user
			$this->_forward('permission', 'error', null);
		}
		$this->view->narrowColumn = 'false';
	}
	
	public function ratingsAction()
    {
		$this->view->narrowColumn = 'right';
		$uri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
		$this->view->currentURI = rtrim($uri,'/');
		$this->view->baseURI    = preg_replace('/\/ratings(\/\w+)?/','',$this->view->currentURI);
		
		$userID = $this->getRequest()->getParam('id');
		$sport  = $this->getRequest()->getParam('sport');
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
		if ($this->view->user->userID != $this->getRequest()->getParam('id')) {
			// Not this user
			$this->_forward('permission', 'error', null);
		}
		$this->view->narrowColumn = 'false';
	}


}

