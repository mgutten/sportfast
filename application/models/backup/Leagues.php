<?php

class Application_Model_Leagues extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_LeaguesMapper';
	protected $_attribs     = array('leagues' => '');
	
	
	public function findLeagues($sports, $cityID)
	{
		return $this->getMapper()->findLeagues($sports, $cityID, $this);
	}
	
	public function addLeague($resultRow)
	{

		$league = $this->_attribs['leagues'][] = new Application_Model_League($resultRow);
		return $league;
	}
	
	public function leagueExists($id) 
	{
		foreach ($this->getAll() as $league) {
			if ($league->leagueID == $id) {
				return $league;
			}				
		}
		return false;
	}
	
	
							
}
