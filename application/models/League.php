<?php

class Application_Model_League extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_LeaguesMapper';
	protected $_attribs     = array('leagueID' 		=> '',
									'leagueName' 	=> '',
									'city'			=> '',
									'leagueLevelID' => '',
									'leagueLevel'	=> '',
									'minSkill'		=> '',
									'maxSkill'		=> '',
									'sport'			=> '',
									'sportID'		=> '',
									'startDate'		=> '',
									'endDate'		=> ''
									);
									
	public function setStartDate($date)
	{
		$this->_attribs['startDate'] = DateTime::createFromFormat('Y-m-d', $date);
		
		return $this;
	}
	
	public function setEndDate($date)
	{
		$this->_attribs['endDate'] = DateTime::createFromFormat('Y-m-d', $date);
		
		return $this;
	}
	
	public function getStartFormat($format = 'F')
	{
		return $this->startDate->format($format);
	}
	
	public function getEndFormat($format = 'F')
	{
		return $this->endDate->format($format);
	}
									
}
