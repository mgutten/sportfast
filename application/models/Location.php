<?php

class Application_Model_Location extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_ParksMapper';
	protected $_attribs     = array('parkID' 	=> '',
									'userID'	=> '',
									'location'	=> '',
									'latitude'  => '',
									'longitude' => ''
									);
									
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
		
									
}
