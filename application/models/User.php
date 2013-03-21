<?php

class Application_Model_User extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	protected $_attribs     = array('userID' 		=> '',
								    'username' 		=> '',
								    'password' 		=> '',
								    'firstName' 	=> '',
								    'lastName' 		=> '',
									'shortName'		=> '',
									'fullName'		=> '',
								    'age' 			=> '',
									'streetAddress' => '',
									'cityID'		=> '',
									'weight' 		=> '',
									'height'		=> '',
									'sex' 			=> '',
									'lastRead' 		=> '',
									'active'		=> '',
									'verifyHash' 	=> '',
									'dob'			=> '',
									'city'			=> '',
									'sports'		=> array(),
									'picture'		=> '',
									'notifications' => '',
									'userLocation'	=> '',
									'games'			=> '',
									'teams'			=> '',
									'changedLocation' => ''
									);

	protected $_primaryKey = 'userID';	
	
	
	public function save($loopSave = true)
	{
		$this->getMapper()->save($this, $loopSave);
		return $this;
	}

	public function getScheduledGames()
	{
		if (is_object($this->games)) {
			// User has games scheduled
			return $this->games->games;
		} else {
			// No games scheduled
			return false;
		}
	}

	public function getUserGames()
	{
		return $this->getMapper()->getUserGames($this);
	}
	
	public function getGames()
	{
		if (empty($this->_attribs['games'])) {
			// No notifications object set
			$this->_attribs['games'] = new Application_Model_Games();
		}
		return $this->_attribs['games'];
	}

	public function getNotifications()
	{
		if (empty($this->_attribs['notifications'])) {
			// No notifications object set
			$this->_attribs['notifications'] = new Application_Model_Notifications($this);
			$this->_attribs['notifications']->lastRead = $this->lastRead;
		}
		return $this->_attribs['notifications'];
	}
	
	public function getOldUserNotifications()
	{
		return $this->notifications->getUserNotifications();
	}
	
	
	public function getNewUserNotifications()
	{
		return $this->notifications->getUserNotifications(true);
	}
	
	public function resetNewNotifications()
	{
		$this->notifications->resetNewNotifications();
		return $this;
	}
		
	
	public function getShortName()
	{
		return $this->firstName . ' ' . $this->lastName[0];
	}
	
	public function getFullName()
	{
		return $this->firstName . ' ' . $this->lastName;
	}
	
	public function setPhoto($photo)
	{
		$this->_attribs['photo'] = $photo;
		return $this;
	}
	
	public function getSport($sport) 
	{
		if (!isset($this->_attribs['sports'][$sport])) {
			$this->_attribs['sports'][$sport] = new Application_Model_Sport();
		}
		return $this->_attribs['sports'][$sport];
	}
	
	public function getSportNames()
	{
		$sports = array();
		foreach ($this->sports as $sport) {
			$sports[] = $sport->sport;
		}
		
		return $sports;
	}
	
	public function getCity()
	{
		if (empty($this->_attribs['city'])) {
			
			$this->_attribs['city'] = new Application_Model_City();
		}
		return $this->_attribs['city'];
	}
	
	public function getLocation()
	{
		if (empty($this->_attribs['userLocation'])) {
			
			$this->_attribs['userLocation'] = new Application_Model_Location();
		}
		return $this->_attribs['userLocation'];
	}
	
	public function setLocation($location) {
		$this->_attribs['userLocation'] = $location;
		
		return $this;
	}
	
	public function getUserSportsInfo()
	{
		return $this->getMapper()->getUserSportsInfo($this->userID,$this);
	}
	
	public function getUserBy($column, $value)
	{
		return $this->getMapper()->getUserBy($column, $value, $this);
	}
	
	public function setLastReadCurrent()
	{
		$this->_attribs['lastRead'] = date("Y-m-d H:i:s", time());
		return $this;
	}
	
	public function getProfilePic($size, $userID = false) 
	{
		return parent::getProfilePic($size, $this->userID);
	}
	
	public function setChangedLocation($value) 
	{
		$this->_attribs['changedLocation'] = $value;
		return $this;
	}
	
	/**
	 * reset stored user's location and city to home
	 */
	public function resetHomeLocation()
	{
		$this->getMapper()->resetHomeLocation($this);
	}


}

