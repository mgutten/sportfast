<?php

class Application_Model_Park extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_ParksMapper';
	protected $_attribs     = array('parkID' 			=> '',
									'parkName' 			=> '',
									'basketballIndoor'  => '',
									'basketballOutdoor' => '',
									'football'			=> '',
									'soccer'			=> '',
									'volleyballSand'	=> '',
									'volleyballCement'  => '',
									'school'			=> '',
									'city'				=> '',
									'cityID'			=> '',
									'location'			=> ''
									);
	
	
	public function setLocation($value)
	{
		$location = $this->_attribs['location'] = new Application_Model_Location();
		
		$location->location = $value;
		
		return $this;
	}
					
	public function getLocation()
	{
		if (empty($this->_attribs['location'])) {
			$this->_attribs['location'] = new Application_Model_Location();
		}
		
		return $this->_attribs['location'];
	}
	
	public function getDistanceFromUser($userLat, $userLon)
	{
		$parkLat = $this->location->getLatitude();
		$parkLon = $this->location->getLongitude();
		
		return parent::getDistanceInMiles($userLat, $userLon, $parkLat, $parkLon);
	}
										
}
