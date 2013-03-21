<?php

class Application_Model_Sport extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_SportsMapper';
	protected $_dbTable		= 'Application_Model_DbTable_UserSports';
	
	protected $_attribs     = array('positions' 	  => '',
									'sportID' 		  => '',
									'sport' 		  => '',
									'userID'		  => '',
									'gameRosterLimit' => '',
									'teamRosterLimit' => '',
									'types' 		  => '',
									'availabilities'  => '',
									'often'			  => '',
									'skillCurrent'    => '',
									'skillInitial'    => '',
									'userSportID'	  => '',
									'sportsmanship'	  => '',
									'attendance'	  => '',
									'formats'		  => '',
									'overall'		  => ''
									);
	protected $_primaryKey = 'userSportID';	
	protected $_overwriteKeys = array('userID');
	
	public function __construct($resultRow = false) 
	{
		if ($resultRow) {
			$this->setAttribs($resultRow);
		}
	}

	public function save()
	{
		if (empty($this->sportID)) {
			// Fill foreign key before save
			$this->sportID = $this->getMapper()
								  ->getForeignID('Application_Model_DbTable_Sports', 'sportID',array('sport' => $this->sport));
		}
		
		parent::save();
	}
	
	/**
	 * calculate overall rating for user
	 */
	public function getOverall()
	{
		if (!empty($this->_attribs['overall'])) {
			// Overall has been set before
			return $this->_attribs['overall'];
		}
		
		$weight = array('sportsmanship' => .1,
						'attendance'	=> .1,
						'skillCurrent'	=> .8);
		
		$sportsmanship = $weight['sportsmanship'] * $this->sportsmanship;
		$attendance    = $weight['attendance'] * $this->attendance;
		$skill		   = $weight['skillCurrent'] * $this->skillCurrent;
		
		$overall = $sportsmanship + $attendance + $skill;
		
		return ceil($overall);
	}
	
	
	/**
	 * get and return href for sport icon
	 */
	public function getIcon($sport, $size = 'medium', $color = 'outline')
	{
		return '/images/global/sports/icons/' . strtolower($size) . '/' . strtolower($color) . '/' . $sport . '.png';
	}
		

	public function getAvailability($day) 
	{	
		return $this->_attribs['availabilities'][$day];
	}
	
	public function setAvailability($day, $hour) 
	{	
		$this->_attribs['availabilities'][$day][$hour] = new Application_Model_Availability($day, $hour, $this);
		return $this->_attribs['availabilities'][$day][$hour];
	}	
	
	public function getType($type) 
	{
		// Remove overwriting of type to accommodate several same typeName models (ie Singles Match, Singles Rally)
		$newType = $this->_attribs['types'][] = new Application_Model_SportType();
		
		return $newType;
	}
	
	public function getPosition($position) 
	{
		if (!isset($this->_attribs['positions'][$position])) {
			$this->_attribs['positions'][$position] = new Application_Model_SportPosition();
		}
		return $this->_attribs['positions'][$position];
	}
	
	public function getFormat($format) 
	{
		if (!isset($this->_attribs['formats'][$format])) {
			$this->_attribs['formats'][$format] = new Application_Model_SportFormat();
		}
		return $this->_attribs['formats'][$format];
	}
	
	
	public function setAllAttribs($resultRow)
	{
		foreach ($this->getAttribs() as $key => $attrib) {
				$this->$attrib = $resultRow->$attrib;
			}
	}
		
	
}
