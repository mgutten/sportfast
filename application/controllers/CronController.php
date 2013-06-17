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
		$files = glob(PUBLIC_PATH . '/images/tmp/profile/pic/*'); // get all file names
		foreach($files as $file){ // iterate files
		  if(is_file($file))
			unlink($file); // delete file
		}
	}
	
	/**
	 * update users age based on birthday
	 * OFTEN: PER DAY
	 */
	public function updateAgeAction()
	{
		$mapper = $this->getMapper();
		
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
		
		$games = $mapper->updateGameStatus();
		
		return $this->_forward('game-status', 'mail', null, array('games' => $games));
	}
	
	public function testPassword($password)
	{
		if ($password !== $this->_password) {
			return $this->_forward('permission', 'error', null);
		}
	}
	
	

}

