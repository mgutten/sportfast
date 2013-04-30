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


}

