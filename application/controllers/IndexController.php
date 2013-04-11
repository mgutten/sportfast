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
				
				$sportsParen .= '"' . $sport->sport . '"';
				
				
				if ($sport->sport == $sportKeys[count($sportKeys) - 1]) {
					// Last sport
					break;
				}

				$sportsParen .= ',';
				$counter++;
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
			
			// Schedule section
			$this->view->userSchedule = $this->view->user->getNextWeekScheduledGames();
			
			$array = array();
			$array[5] = 'cat';
			
			// Find section matches
			$matches = new Application_Model_Matches();
			
			$games   = new Application_Model_Games();
			$options = array('`g`.`sport` IN ' . $sportsParen);
			
			$games->findUserGames($this->view->user, $options);
			
			$teams  = new Application_Model_Teams();
			$options = array('`t`.`sport` IN ' . $sportsParen);
			$teams->findUserTeams($this->view->user);
			
			
			$matches->addMatches($games->games)
					->addMatches($teams->teams);
			
			$this->view->matches = $matches->sortByMatch();
			
		}
    }


}

