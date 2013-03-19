<?php

class Application_Model_Location extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_ParksMapper';
	protected $_attribs     = array('userLocationID' => '',
									'parkID' 	=> '',
									'userID'	=> '',
									'location'	=> '',
									'latitude'  => '',
									'longitude' => ''
									);
	
	protected $_primaryKey  = 'userSportTypeID';	
	protected $_dbTable		= 'Application_Model_DbTable_UserLocations';	
	
	public function save($mapper = 'Application_Model_LocationsMapper')
	{
		$this->setMapper($mapper);
		
		return $this->getMapper()->save($this);
	}
	
	public function getLatitude()
	{
		if (empty($this->_attribs['latitude'])) {
			$this->parseLocation();
		}
		
		return $this->_attribs['latitude'];
	}
	
	public function getLongitude()
	{
		if (empty($this->_attribs['longitude'])) {
			$this->parseLocation();
		}
		
		return $this->_attribs['longitude'];
	}
	
	public function parseLocation()
	{
		// Location start is POINT(latitude longitude), explode by space
		$location = explode(' ',$this->location);
		
		$this->latitude  = ltrim($location[0], 'POINT(');
		$this->longitude = rtrim($location[1], ')');
		
		return $this;
	}
	
	public function getLocationByZipcode($zipcode) {
		$this->setMapper('Application_Model_LocationsMapper');
		
		$this->getMapper()->getLocationByZipcode($zipcode, $this);
	}
		
									
}
