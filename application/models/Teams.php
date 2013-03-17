<?php

class Application_Model_Teams extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_TeamsMapper';
	
	protected $_attribs     = array('teams' => '');
	
	public function findUserTeams($userClass, $options = false)
	{
		return $this->getMapper()->findUserTeams($userClass, $this, $options);
	}
	
	public function addTeam($resultRow)
	{

		$team = $this->_attribs['teams'][] = new Application_Model_Team($resultRow);
		return $team;
	}
									
}
