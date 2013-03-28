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
		
		$this->view->currentUser = $user;
		
		$this->view->currentURL  = '/users/' . $user->userID;

        $dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
		$this->view->inviteButton = $dropdown->dropdownButton('invite', array('Group'), 'Invite to');
		
		// Get latest user activity
		$activities = new Application_Model_Notifications();
		$activities->getUserActivities($user);
		$this->view->activities = $activities->read;

		$this->view->memberHomepage = $this->view->getHelper('memberhomepage');
		
		
    }


}

