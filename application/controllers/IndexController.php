<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$this->view->noDefaultHeadTitle = true;
		$auth = Zend_Auth::getInstance();
		
		if (!$auth->hasIdentity()) {
			// Non-member homepage
			$this->view->whiteBacking = false;
		} else {
			// Member homepage
			$this->view->narrowColumn = 'right';
			
			$userSports = $this->view->userSports = $this->view->user->getSportNames();
			
			$dropdown = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
			$lookingDropdownSportArray = array();
			$sportIconPath = '/images/global/sports/icons/small/outline/';
			foreach ($userSports as $sport) {
				// Loop through sports and create properly formatted array for dropdown
				$sportArray = array('text'  => $sport,
									'image' => $sportIconPath . strtolower($sport) . '.png',
									'color' => 'light');
				array_push($lookingDropdownSportArray, $sportArray);
			}
			
			$this->view->lookingDropdownSport = $dropdown->dropdown('member-looking-sports',$lookingDropdownSportArray, 'Select sports');
			$this->view->lookingDropdownType  = $dropdown->dropdown('member-looking-types',$lookingDropdownSportArray, 'Select types');
		}
    }


}

