<?php

class CronController extends Zend_Controller_Action
{
	protected $_password = 'gKgjsjGZx9';
	
    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function getMapper()
	{
		return new Application_Model_CronMapper();
	}
	
	public function preDispatch()
	{
		$pass = $this->getRequest()->getParam('pass');

		$this->testPassword($pass);
	}

    public function indexAction()
    {
        
    }
	
	/**
	 * reinvite outside users to join any game/team that they were invited to
	 */
	public function reinviteUsersAction()
	{
		$mapper = $this->getMapper();
		
		$games = $mapper->reinviteUserGames();
		
		$teams = $mapper->reinviteUserTeams();
		
		
		if ($teams || $games) {
			return $this->_forward('reinvite-users', 'mail', null, array('games' => $games,
																		 'teams' => $teams));
		}
	
	}
	
	/**
	 * send follow up email to inactive users who signed up yesterday but did not verify
	 */
	public function remindUnverifiedUsersAction()
	{
		$mapper = $this->getMapper();
		$users = $mapper->getUnverifiedUsers();
		
		if ($users) {
			return $this->_forward('remind-verify', 'mail', null, array('users' => $users));
		}
	}
	
	
	/**
	 * move old games and old teams to "old_" tables, also reset recurring games
	 * OFTEN: PER HALF HOUR
	 */
	public function moveTypeToOldAction()
	{
		$mapper = $this->getMapper();
		
		$mapper->moveGamesToOld();
		//$mapper->moveTeamsToOld();
	}
	
	/**
	 * delete temp pictures from signup/upload pages 
	 * OFTEN: PER WEEK
	 */
	public function removeTempPicturesAction()
	{
		$dir = 'images/tmp/profile/pic/';
		
		
		$dirHandle = opendir($dir); 
		// LOOP OVER ALL OF THE  FILES
		while ($file = readdir($dirHandle)) { 
			// IF IT IS NOT A FOLDER, AND ONLY IF IT IS A .JPG WE ACCESS IT
			if (is_file($dir . $file)) {
				unlink($dir . $file);
			}

		}
		// CLOSE THE DIRECTORY
		closedir($dirHandle); 
		
	}
	
	/**
	 * delete teams marked for deletion
	 * OFTEN: PER DAY
	 */
	public function deleteTeamsAction()
	{
		$mapper = $this->getMapper();
		$mapper->deleteTeams();
	}
	
	/**
	 * update users age based on birthday
	 * OFTEN: PER DAY
	 */
	public function updateAgeAction()
	{
		$mapper = $this->getMapper();
		return;
		$mapper->updateAge();
	}
	
	/**
	 * remove fake users from games
	 * OFTEN: PER DAY
	 */
	public function removeFakeUsersAction()
	{
		$mapper = $this->getMapper();
		
		$mapper->removeFakeUsers();
	}
	
	/**
	 * remove inactive types
	 * OFTEN: PER WEEK
	 */
	public function removeInactiveTypesAction()
	{
		$mapper = $this->getMapper();
		
		
		return;
		
		$mapper->removeInactiveUsers();
		$mapper->removeInactiveTeams();
	}
	
	/**
	 * warn inactive types of impending removal
	 * OFTEN: PER WEEK
	 */
	public function warnInactiveTypesAction()
	{
		$mapper = $this->getMapper();
		
		return;
		$inactive = $mapper->getInactiveUsers();
		
		return $this->_forward('warn-inactive', 'mail', null, array('inactive' => $inactive));
		//$mapper->removeInactiveTeams();
	}
	
	/**
	 * check game status
	 * OFTEN: PER HALF HOUR
	 */
	public function updateGameStatusAction()
	{
		$mapper = $this->getMapper();
		
		$date = new DateTime('now');
		
		if ((($date->format('G') >= 6 && $date->format('i') > 31) || $date->format('G') >= 7) && ($date->format('G') < 20)) {
			// Only update games between 7AM and 8PM
			$games = $mapper->updateGameStatus();
			
			return $this->_forward('game-status', 'mail', null, array('games' => $games));
		}
	}
	
	
	/**
	 * inform users that they have a game tomorrow (teams)
	 * OFTEN: PER DAY
	 */
	public function informUsersGameAction()
	{
		$mapper = $this->getMapper();
		
		$teamGames = $mapper->getUserTeamGames();
		$teamGamesToday = $mapper->getUserTeamGames(0);
		
		//$subscribedGames = $mapper->getUserSubscribedGames();
		
		$games = array('games' => array(),
					   'teamGames' => array('twoDays' => $teamGames,
					   						'today'	  => $teamGamesToday));
		
		return $this->_forward('upcoming-game', 'mail', null, array('games' => $games));
	}
	
	/**
	 * inform users that they have a game tomorrow (pickup)
	 * OFTEN: PER HOUR
	 */
	public function informUsersSubscribedGameAction()
	{
		$mapper = $this->getMapper();
		
		$subscribedGames = $mapper->getUserSubscribedGames();
		
		$games = array('games' => $subscribedGames,
					   'teamGames' => array());
		
		return $this->_forward('upcoming-game', 'mail', null, array('games' => $games));
	}
	
	/**
	 * automatically create games 
	 * OFTEN: PER DAY
	 */
	public function createGamesAction()
	{
		$createGames = Zend_Controller_Action_HelperBroker::getStaticHelper('CreateGames');
		
		$games = $createGames->createGames();
		
		if ($games) {
			// Games were created
			return $this->_forward('game-created', 'mail', null, array('games' => $games));
		}
	}
	
	
	
	public function testPassword($password)
	{
		if ($password !== $this->_password) {
			return $this->_forward('permission', 'error', null);
		}
	}
	
	

}

