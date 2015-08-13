<?php

class Application_Model_Teams extends Application_Model_TypesAbstract
{
	protected $_mapperClass = 'Application_Model_TeamsMapper';
	
	protected $_attribs     = array('teams' => '',
									'totalRows' => '');
									
	protected $_primaryKey  = 'teamID';
	
	public function findUserTeams($userClass, $options = false)
	{
		return $this->getMapper()->findUserTeams($userClass, $this, $options);
	}
	
	/**
	 * find games for Find controller given options
	 */
	public function findTeams($options, $userClass, $limit = false)
	{
		return $this->getMapper()->findTeams($options, $userClass, $this, $limit);
	}
	
	public function addTeam($resultRow)
	{
		if ($resultRow instanceof Application_Model_Team) {
			// Is team model as used in Ajax addUserToTeam
			$team = new Application_Model_Team();
			foreach ($resultRow->getAttribs() as $key => $val) {
				$team->$key = $val;
			}
		} else {
			$team = $this->_attribs['teams'][] = new Application_Model_Team($resultRow);
		}
		return $team;
	}
	
	public function teamExists($id) 
	{
		foreach ($this->getAll() as $team) {
			if ($team->teamID == $id) {
				return $team;
			}
				
		}
		
		return false;
	}
							
}
