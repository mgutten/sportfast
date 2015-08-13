<?php

class Application_Model_Location extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_ParksMapper';
	protected $_attribs     = array('userLocationID' => '',
									'parkLocationID' => '',
									'parkID' 	=> '',
									'userID'	=> '',
									'location'	=> '',
									'latitude'  => '',
									'longitude' => '',
									'changedLocation' => ''
									);
	
	protected $_primaryKey  = 'userLocationID';	
	protected $_dbTable		= 'Application_Model_DbTable_UserLocations';	
	
	public function save($loopSave = false, $mapper = 'Application_Model_LocationsMapper')
	{
		if (!empty($this->_attribs['changedLocation'])) {
			// Location was changed temporarily
			return;
		}
		
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
		$location = explode(' ',$this->_attribs['location']);

		$this->latitude  = ltrim($location[0], 'POINT(');
		$this->longitude = rtrim((isset($location[1]) ? $location[1] : ''), ')');
		
		return $this;
	}
	
	public function getLocationByZipcode($zipcode) {
		$this->setMapper('Application_Model_LocationsMapper');
		
		$this->getMapper()->getLocationByZipcode($zipcode, $this);
	}
	
	public function getLocationByCityID($cityID) {
		$this->setMapper('Application_Model_LocationsMapper');
		
		return $this->getMapper()->getLocationByCityID($cityID, $this);
	}
	
	public function getCityIDByLocation() {
		$this->setMapper('Application_Model_LocationsMapper');
		
		return $this->getMapper()->getCityIDByLocation($this->location);
	}
			
									
}
