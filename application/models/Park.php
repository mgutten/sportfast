<?php

class Application_Model_Park extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_ParksMapper';
	protected $_attribs     = array('parkID' 			=> '',
									'parkName' 			=> '',
									'basketballIndoor'  => '',
									'basketballOutdoor' => '',
									'field'				=> '',
									'volleyball'		=> '',
									'tennis'			=> '',
									'hours'				=> '',
									'type'				=> '',
									'cost'				=> '',
									'city'				=> '',
									'cityID'			=> '',
									'location'			=> '',
									'openTime'			=> '',
									'closeTime'			=> ''
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
	
	public function getParkByID($parkID)
	{
		return $this->getMapper()->getParkByID($parkID, $this);
	}
	
	public function getParkGames($parkID = false)
	{
		if (!$parkID) {
			$parkID = $this->parkID;
		}
		return $this->getMapper()->getParkGames($parkID);
	}
	
	public function getParkStash($parkID = false)
	{
		if (!$parkID) {
			$parkID = $this->parkID;
		}
		return $this->getMapper()->getParkStash($parkID);
	}

	
	public function getParkRatings($parkID = false)
	{
		if (!$parkID) {
			$parkID = $this->parkID;
		}
		return $this->getMapper()->getParkRatings($parkID);
	}
	
	public function getNearbyParks()
	{
		$latitude = $this->getLocation()->latitude;
		$longitude = $this->getLocation()->longitude;
		
		return $this->getMapper()->getNearbyParks($latitude, $longitude, $this->cityID, $this->parkID);
	}
	
	public function getProfilePic($size, $id = false, $type = 'parks')
	{
		$id = $this->parkID;
		
		return parent::getProfilePic($size, $id, $type);
	}
	
	public function getTotalBasketball()
	{
		return ($this->basketballIndoor + $this->basketballOutdoor);
	}
	
	public function getType()
	{
		return ucwords($this->_attribs['type']);
	}
	
	public function getHours()
	{
		if ($this->_attribs['type'] == 'school') {
			return 'Not School Hours';
		}
		$openTime = $this->getTime('openTime',true);
		$closeTime = $this->getTime('closeTime',true);
		
		return $openTime . '-' . $closeTime;
	}
	
	public function getTime($attrib, $ampm = true) {
		
		$time  = $this->formatTime($this->_attribs[$attrib]);
		
		$ampm = 'am';
		$output = '';
		if ($time['hour'] >= 12) {
			// PM
			if ($time['hour'] != 12) {
				$output .= ($time['hour'] - 12);
			}
			$ampm = 'pm';
			
		} else {
			$output .= (int)$time['hour'];
		}
		
		if ($time['minute'] !== '00') {
			$output .= ':' . $time['minute'];
		}
		
		if ($ampm) {
			// display 'am' or 'pm'
			$output .= $ampm;
		}
		
		return $output;
	}
	
	/**
	 * change time type from mysql (HH:MM:SS) into array of "hour", "minute", and "second"
	 */
	public function formatTime($time)
	{
		list($hour, $minute, $second) = explode(':', $time);
		
		$timeArray = array('hour' => $hour,
						   'minute' => $minute,
						   'second' => $second);
						   
		return $timeArray;
	}
}
