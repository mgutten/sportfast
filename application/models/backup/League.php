<?php

class Application_Model_League extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_LeaguesMapper';
	protected $_attribs     = array('leagueID' 		=> '',
									'leagueName' 	=> '',
									'city'			=> '',
									'leagueLevels'  => '',
									'minSkill'		=> '',
									'maxSkill'		=> '',
									'sport'			=> '',
									'sportID'		=> '',
									);
	
	public function addLeagueLevel($resultRow)
	{
		$leagueLevel = $this->_attribs['leagueLevels'][] = new Application_Model_LeagueLevel($resultRow);
		return $leagueLevel;
	}
						
									
}
