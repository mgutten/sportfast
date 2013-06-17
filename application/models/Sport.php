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
									'overall'		  => '',
									'ratings'		  => ''
									);
									
	protected $_primaryKey = 'userSportID';	
	protected $_overwriteKeys = array('userID');
	

	public function save($loopSave = false)
	{
		if (empty($this->sportID)) {
			// Fill foreign key before save
			$this->sportID = $this->getMapper()
								  ->getForeignID('Application_Model_DbTable_Sports', 'sportID',array('sport' => $this->sport));
		}
		
		$this->getMapper()->save($this, $loopSave);
	}
	
	/**
	 * get skills (ball handler, shooting, etc) from db
	 * @returns array of skiller and skilling
	 */
	public function getSkills()
	{
		return $this->getMapper()->getSkills($this->sportID);
	}
	
	
	public function getSkill()
	{
		/*
		if ($this->hasValue('ratings')) {
			$average = $this->ratings->getAverage('skill',$this->_attribs['skillCurrent']);
		} else {
			$average = $this->_attribs['skillCurrent'];
		}
		*/
		return $this->_attribs['skillCurrent'];
	}
	
	public function getUserSportData($userID)
	{
		return $this->getMapper()->getUserSportData($userID, $this->sportID);
	}
	
	public function getSportIDByName($name)
	{
		return $this->getMapper()->getForeignID('Application_Model_DbTable_Sports', 'sportID', array('sport = "' . $name . '"'));
	}
	
	public function getSport()
	{
		return ucwords($this->_attribs['sport']);
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
		
		return $this->ratings->getOverall($this->sportsmanship, $this->attendance, $this->skillCurrent);

	}
	
	
	/**
	 * get and return href for sport icon
	 */
	public function getIcon($size = 'medium', $type = 'outline', $color = 'medium')
	{
		$sport = strtolower($this->sport);
		return parent::getSportIcon($sport, $size, $type, $color);
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
	
	public function setType($type) 
	{
		// Remove overwriting of type to accommodate several same typeName models (ie Singles Match, Singles Rally)
		$newType = $this->_attribs['types'][strtolower($type)] = new Application_Model_SportType();
		
		return $newType;
	}
	
	public function getType($typeID = false) 
	{
		//$type = strtolower($typeName);
		/* CHANGED TO ALLOW FOR SEVERAL SPORT TYPES (singles => rally, singles => match)
		if (!isset($this->_attribs['types'][$type])) {
			$newType = $this->setType($type);		
		} else {
			$newType = $this->_attribs['types'][$type];
		}
		*/
		
		if (isset($this->_attribs['types'][$typeID])) {
			$newType = $this->_attribs['types'][$typeID];
		} else {
			$newType = $this->_attribs['types'][$typeID] = new Application_Model_SportType();
		}
		
		return $newType;
	}
	
	public function getTypeNames()
	{
		$typeNames = array();
		foreach ($this->types as $type) {
			$typeName = ucwords($type->typeName);
			if (in_array($typeName, $typeNames)) {
				continue;
			}
			$typeNames[] = $typeName;
		}
		
		return $typeNames;
	}
	
	public function getTypeSuffixes()
	{
		$typeSuffixes = array();
		foreach ($this->types as $type) {
			$typeSuffix = ucwords($type->typeSuffix);
			if (in_array($typeSuffix, $typeSuffixes)) {
				continue;
			}
			$typeSuffixes[$typeSuffix] = $type->typeDescription;
		}
		
		return $typeSuffixes;
	}
	
	public function getPosition($position) 
	{
		if (!isset($this->_attribs['positions'][$position])) {
			$this->_attribs['positions'][$position] = new Application_Model_SportPosition();
		}
		return $this->_attribs['positions'][$position];
	}
	
	public function getRatings() 
	{
		if (!$this->hasValue('ratings')) {
			$this->_attribs['ratings']= new Application_Model_Ratings();
		}
		return $this->_attribs['ratings'];
	}

	
	public function getFormat($format) 
	{
		$format = strtolower($format);
		if (!isset($this->_attribs['formats'][$format])) {
			$this->_attribs['formats'][$format] = new Application_Model_SportFormat();
		}
		return $this->_attribs['formats'][$format];
	}
	
	/**
	 * convert slider position (ie 0, 1, 2, 3) to scale
	 */
	public function convertSliderToRating($rating)
	{
		return floor(($rating * 5.5) + mt_rand(64, 66));
	}
		
	
}
