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
			
		
			//$this->view->user->getMapper()->getCityIdRange('984');
					
			$session = new Zend_Session_Namespace('first_visit');
			if ($session->firstVisit) {
				// First time logging in
				Zend_Session::namespaceUnset('first_visit');
				$this->view->firstVisit = true;
				//$session->firstVisit = false;
			} elseif ($this->view->lastActive) {
				// Not first visit on site
				$users = new Application_Model_Users();
				$usersInArea = $users->getUsersInArea($this->view->user->userID, $this->view->user->userLocation->latitude, $this->view->user->userLocation->longitude, $this->view->lastActive);
				
				if ($usersInArea) {
					// There are insufficient users in area, notify user
					$this->view->usersInArea = $usersInArea;
				}
				
			}
			
			
			$this->view->userSports = $this->view->user->getSportNames();
			
			$userSports  = $this->view->user->sports;
			$dropdown    = Zend_Controller_Action_HelperBroker::getStaticHelper('Dropdown');
			$lookingDropdownSportArray = array();
			$sportsParen = '('; // For use in options with games model below
			$sportKeys   = array_keys($userSports);
			$counter	 = 0;
		
			foreach ($userSports as $sport) {
				// Loop through sports and create properly formatted array for dropdown
				$sportArray = array('text'  => $sport->sport,
									'image' => $sport->getIcon('tiny', 'outline'),
									'color' => 'light');
									
				array_push($lookingDropdownSportArray, $sportArray);
				
				/*
				$sportsParen .= '"' . $sport->sport . '"';
				
				
				if ($sport->sport == $sportKeys[count($sportKeys) - 1]) {
					// Last sport
					break;
				}

				$sportsParen .= ',';
				$counter++;
				*/
			}
			$sportsParen .= '"' . implode('","',$sportKeys) . '"';
			
			$sportsParen .= ')';
			
			
			$lookingTeams = '';
			if (!$this->view->user->wantsTeams()) {
				// User does not want any teams, remove from dropdown
				$lookingTeams = 'not-selected';
			}
			
			$lookingDropdownTypeArray = array(array('text'  => 'Games',
												 	'color' => 'light'),
											  array('text'  => 'Teams',
												 	'color' => 'light',
													'outerClass' => $lookingTeams),
											  array('text'  => 'Tournaments',
												 	'color' => 'light',
													'outerClass' => 'not-selected'));
													
			$lookingDropdownTimeArray = array(array('text'  => 'Any Time',
												 	'color' => 'light'),
											  array('text'  => 'My Availability',
												 	'color' => 'light')
											  );
			
			$this->view->lookingDropdownSport = $dropdown->dropdown('member-looking-sports',$lookingDropdownSportArray, 'Select sports');
			$this->view->lookingDropdownType  = $dropdown->dropdown('member-looking-types',$lookingDropdownTypeArray, 'Select types');
			$this->view->lookingDropdownTime  = $dropdown->dropdown('member-looking-times',$lookingDropdownTimeArray, 'Select times');
			
			// Newsfeed
			$newsfeed = new Application_Model_Notifications();
			$this->view->newsfeed = $newsfeed->getNewsfeed($this->view->user->city->cityID);
			
			// Schedule section
			$this->view->userSchedule = $this->view->user->getNextWeekScheduledGames();
			
			$array = array();
			$array[5] = 'cat';
			
			// Find section matches
			$matches = new Application_Model_Matches();
			
			$games   = new Application_Model_Games();
			$options = array('`g`.`sport` IN ' . $sportsParen);
			
			
			$games->findUserGames($this->view->user, $options, false, 'any', 'any');
			
			$matches->addMatches($games->games);
			
			if (!$lookingTeams) {
				// User wants teams
				$teams  = new Application_Model_Teams();
				$options = array('`t`.`sport` IN ' . $sportsParen);
				$teams->findUserTeams($this->view->user);
				
				$matches->addMatches($teams->teams);
			}
					
			
			$this->view->matches = $matches->sortByMatch();
						
		}
    }


}

