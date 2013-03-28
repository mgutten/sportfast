<?php

class Application_Model_Notification extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_NotificationsMapper';
	protected $_dbTable		= 'Application_Model_DbTable_NotificationLog';	
	
	protected $_attribs     = array('notificationLogID' => '',
									'actingUserID'		=> '',
									'receivingUserID'   => '',
									'notificationID'	=> '',
									'cityID'			=> '',
									'gameID'			=> '',
									'teamID'			=> '',
									'groupID'			=> '',
									'ratingID'			=> '',
									'parkID'			=> '',
									'textData'			=> array(),
									'text'				=> '',
									'firstName'			=> '',
									'lastName'			=> '',
									'actingFirstName'	=> '',
									'actingLastName'	=> '',
									'receivingFirstName'=> '',
									'receivingLastName'	=> '',
									'sport'				=> '',
									'date'				=> '',
									'parkName'			=> '',
									'picture'			=> '',
									'url'				=> '',
									'action'			=> '',
									'type'				=> '',
									'newsfeed'			=> false,
									'read'				=> false,
									'dateHappened'      => ''
									);
	protected $_primaryKey = 'notificationLogID';	
	
	
	public function __construct($resultRow = false)
	{
		
		if ($resultRow) {
			$this->setAttribs($resultRow);
		}
				
	}
	
	public function getTimeFromNow($date = false, $maxDays = 7)
	{
		return parent::getTimeFromNow($this->dateHappened, $maxDays);	
	}
	
	public function getPicture($size = 'small')
	{
		$picture = $this->_attribs['picture'];
		
		if ($picture == 'users') {
			// User pic
			$path = $this->getProfilePic($size, $this->actingUserID, 'users');
		} elseif ($picture == 'parks') {
			// Park pic
			$path = $this->getProfilePic($size, $this->parkID, 'parks');
		} elseif ($picture == 'sports') {
			// Sports pic
			$path = $this->getSportIcon($this->sport, $size, 'solid', 'medium');
		} elseif ($picture == 'teams') {
			// Team pic
			$path = $this->getProfilePic($size, $this->teamID, 'teams');
		} elseif ($picture == 'teams') {
			// Group pic
			$path = $this->getProfilePic($size, $this->groupID, 'groups');
		}  
		
		/*if (!empty($this->_attribs['actingUserID'])) {
			// Some user did this
			$picturePath = $this->getProfilePic($size, $this->actingUserID);
		} else {
			// Non user (system, team, group, etc)
			
		}
		*/
		
		return $path;
	}
	
	public function getFormattedUrl()
	{
		preg_match_all('/(?:%)[a-zA-Z]+/', $this->url, $matches);
		
		$replace = array();
		foreach ($matches[0] as $match) {
			$match = ltrim($match,'%');
			$replaceVal = $this->_attribs[$match];
			$replace[] = $replaceVal;
		}
		
		return str_replace($matches[0],$replace,$this->url);
	}
	
	public function getFormattedText()
	{
		
		// match %sign holders in text (eg %name has joined the %sport game)
		preg_match_all('/(?:%)[a-zA-Z]+/', $this->text, $matches);

		$replace = array();
		foreach ($matches[0] as $match) {
			$match = ltrim($match,'%');
			
			$pre   = '';
			$post  = '';
			$class = 'dark bold text-width';
			$replaceVal = (isset($this->_attribs[$match]) ? $this->_attribs[$match] : '');
			$possession = 'your';
			
			if ($this->newsfeed) {
				// This notification is meant for newsfeed, give different class
				$class = 'green';
				$possession = 'a';
			}
			
			if ($match == 'userName' || $match == 'receivingUserName') {
				// Username replace
				if ($match == 'userName') {
					// Refers to acting user
					$id = $this->actingUserID;
					$replaceVal = ucwords($this->actingFirstName) . " " . ucwords($this->actingLastName[0]);
				} else {
					// Refers to receiving user
					$id = $this->receivingUserID;
					$replaceVal = ucwords($this->receivingFirstName) . " " . ucwords($this->receivingLastName[0]);
				}
				if ($this->newsfeed) {
					// Is newsfeed, add link
					$pre  = "<a href='/users/" . $id . "' class='" . $class . "'>";
					$post = "</a>";
				} else {
					$pre  = "<span class='" . $class . "'>";
					$post = "</span>";
				}
			} elseif ($match == 'parkName') {
				if ($this->newsfeed) {
					// Is newsfeed, add link
					$pre  = "<a href='/parks/" . $this->parkID . "' class='" . $class . "'>";
					$post = "</a>";
				} else {
					// Is notification
					$pre  = "<span class='" . $class . "'>";
					$post = "</span>";
				}
			} elseif ($match == 'sport') {
				if ($this->newsfeed) {
					// Is newsfeed, add link
					$pre  = "<a href='" . $this->getFormattedUrl() . "' class='" . $class . "'>";
					$post = "</a>";
				} else {
					$pre  = "<span class='" . $class . "'>";
					$post = "</span>";
				}
			} elseif ($match == 'date') {
				$time = strtotime($replaceVal);
				// Format date (Wednesday, Dec 31 at 4pm)
				$replaceVal = date('l', $time) . ' <span class="light">' . date('M j', $time) . '</span> at ' . date('ga', $time);
			}
			
			
			$replace[] = $pre . $replaceVal . $post;
		}
		
		$str = str_replace($matches[0],$replace, $this->text);
		
		// Replace possession holder with possession var (ie a game, your game)
		return str_replace('&possession', $possession, $str);
		
	}
		
			
	
	public function save()
	{
		if (empty($this->positionID)) {
			// Fill foreign key before save
			$this->positionID = $this->getMapper()
									 ->getForeignID('Application_Model_DbTable_SportPositions', 'positionID',array('sportID'    		  => $this->sportID,
																												   'positionAbbreviation' => $this->positionAbbreviation));
		}
		
		parent::save($this);
	}
	
	
}