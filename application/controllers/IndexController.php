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
			$sportsParen   = '('; // For use in options with games model below
			foreach ($userSports as $sport) {
				// Loop through sports and create properly formatted array for dropdown
				$sportArray = array('text'  => $sport,
									'image' => $sportIconPath . strtolower($sport) . '.png',
									'color' => 'light');
									
				array_push($lookingDropdownSportArray, $sportArray);
				
				$sportsParen .= '"' . $sport . '"';
				
				if ($sport == end($userSports)) {
					// Last sport
					break;
				}
				
				$sportsParen .= ',';
			}
			
			$sportsParen .= ')';
			
			$lookingDropdownTypeArray = array(array('text'  => 'Games',
												 	'color' => 'light'),
											  array('text'  => 'Teams',
												 	'color' => 'light'),
											  array('text'  => 'Tournaments',
												 	'color' => 'light'));
			
			$this->view->lookingDropdownSport = $dropdown->dropdown('member-looking-sports',$lookingDropdownSportArray, 'Select sports');
			$this->view->lookingDropdownType  = $dropdown->dropdown('member-looking-types',$lookingDropdownTypeArray, 'Select types');
			
			// Newsfeed
			$newsfeed = new Application_Model_Notifications();
			$this->view->newsfeed = $newsfeed->getNewsfeed($this->view->user->city->cityID);
						
			
			// Find section matches
			$matches = new Application_Model_Matches();
			
			$games   = new Application_Model_Games();
			$options = array('`g`.`sport` IN ' . $sportsParen);
			$games->findUserGames($this->view->user, $options);
			//$this->view->games = $games;
			
			$teams  = new Application_Model_Teams();
			$options = array('`t`.`sport` IN ' . $sportsParen);
			$teams->findUserTeams($this->view->user);
			//$this->view->teams = $teams;
			
			
			$matches->addMatches($games->games)
					->addMatches($teams->teams);
			
			$this->view->matches = $matches->sortByMatch();
			
		}
    }


}

