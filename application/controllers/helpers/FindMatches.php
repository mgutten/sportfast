<?php
class Application_Controller_Helper_FindMatches extends Zend_Controller_Action_Helper_Abstract
{
	/**
	 * find matches for options given in $options for Find controller
	 * @params ($type => 'games', 'teams', or 'tournaments'
	 *			$options => array of options)
	 */
	public function findmatches($type, $options, $userClass, $limit = false)
	{
		
		$matches = new Application_Model_Matches();
		if ($type == 'games') {
			// Looking for games
			$games = new Application_Model_Games();
			$games->findGames($options, $userClass, $limit);
			$matches->addMatches($games->getAll());
			$matches->totalRows = $games->totalRows;
		} elseif ($type == 'teams') {
			$teams = new Application_Model_Teams();
			$teams->findTeams($options, $userClass, $limit);
			$matches->addMatches($teams->getAll());
			$matches->totalRows = $teams->totalRows;
		} elseif ($type == 'players') {
			$players = new Application_Model_Users();
			$players->findUsers($options, $userClass, $limit);
			$matches->addMatches($players->getAll());
			$matches->totalRows = $players->totalRows;
		} elseif ($type == 'parks') {
			$parks = new Application_Model_Parks();
			$parks->findParks($options, $userClass, $limit);
			$matches->addMatches($parks->getAll());
			$matches->totalRows = $parks->totalRows;
		}
		
		return $matches;
		
	}
	
	
}
		
		
		
