<?php

class Application_Model_Notification extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_NotificationsMapper';
	protected $_dbTable		= 'Application_Model_DbTable_NotificationLog';	
	
	protected $_userID;
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
									'teamName'			=> '',
									'groupName'			=> '',
									'pictureType'			=> '',
									'url'				=> '',
									'action'			=> '',
									'type'				=> '',
									'details'			=> '',
									'newsfeed'			=> false,
									'read'				=> false,
									'dateHappened'      => '',
									'actionRequired'	=> '',
									'joinOption'		=> '',
									'parentUserID'		=> '',
									'likeNotifications' => ''
									);
									
	protected $_primaryKey = 'notificationLogID';	
			
	
	public function getTimeFromNow($date = false, $maxDays = 7)
	{
		return parent::getTimeFromNow($this->dateHappened, $maxDays);	
	}
	
	public function getPicture($size = 'small')
	{
		$picture = $this->_attribs['pictureType'];
		
		if ($picture == 'users') {
			// User pic
			$path = $this->getProfilePic($size, $this->actingUserID, 'users');
		} elseif ($picture == 'parks') {
			// Park pic
			$path = $this->getProfilePic($size, $this->parkID, 'parks');
		} elseif ($picture == 'sports') {
			// Sports pic
			if (empty($this->sport)) {
				// Game has been deleted or for some reason does not have sport saved, default to basketball
				$path = $this->getSportIcon('basketball', $size, 'solid', 'medium');
			} else {
				$path = $this->getSportIcon($this->sport, $size, 'solid', 'medium');
			}
		} elseif ($picture == 'teams') {
			// Team pic
			$path = $this->getProfilePic($size, $this->teamID, 'teams');
		} elseif ($picture == 'teams') {
			// Group pic
			$path = $this->getProfilePic($size, $this->groupID, 'groups');
		}  else {
			// Default pic
			$path = $this->getProfilePic($size, '', 'logo');
		}
		
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
	
	public function getFormattedText($currentID = false)
	{
		// match %sign holders in text (eg %name has joined the %sport game)
		preg_match_all('/(?:%)[a-zA-Z]+/', $this->text, $matches);
		
		$possession = 'your';
		$class = 'dark heavy text-width';
		$is = 'is';
		
		if ($this->newsfeed) {
			// This notification is meant for newsfeed, give different class
			$class = 'green heavy';
			if ($this->gameID === $currentID) {
				// On that game's page, all notifications should read "this game"
				$possession = 'this';
			} else {
				if (($pos = strpos($this->text, '%sport')) > -1) {
					// Sport is in notification, check to conjugate "a" to "an"
					$vowels = array('a','e','i', 'o', 'u');
					if (in_array(strtolower($this->_attribs['sport'][0]), $vowels)) {
						// Is vowel, possession should be an
						$possession = 'an';
					} else {
						$possession = 'a';
					}
				}
			}
		}
		
		if (strpos($this->text,'&possession') == 0) {
			// First word of text, capitalize
			$possession = ucfirst($possession);
		}
		
		$replace = array();
		foreach ($matches[0] as $match) {
			$match = ltrim($match,'%');
			
			$pre   = '';
			$post  = '';
			$ucwords = false;
			
			$replaceVal = (isset($this->_attribs[$match]) ? $this->$match : '');
			
			if ($match == 'userName' || $match == 'receivingUserName') {
				// Username replace
				if ($this->likeNotifications > 1) {
					$id = $this->actingUserID;
					$replaceVal = $this->likeNotifications . ' players';
					$is = 'are';
				} elseif ($match == 'userName') {
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
				
				if ($id == $this->parentUserID) {
					// User's name, replace with "you"
					$pre = '';
					$replaceVal = 'you';
					$post = '';
					$is = 'are';
					if (strpos($this->text, '%' . $match) == 0) {
						// username is first word in sentence
						$replaceVal = 'You';
					}
				}
				
			} elseif ($match == 'parkName') {
				if ($this->newsfeed) {
					// Is newsfeed, add link
					$pre  = "<a href='/parks/" . $this->parkID . "' class='" . $class . "'>";
					$post = "</a>";
					$ucwords = true;
				} else {
					// Is notification
					$pre  = "<span class='" . $class . "'>";
					$post = "</span>";
					$ucwords = true;
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
				
			} elseif ($match == 'day') {
				// Display day (wednesday, thursday)
				$time = strtotime($this->_attribs['date']);
				$replaceVal = date('l', $time);
				$pre  = "<span class='" . $class . "'>";
				$post = "</span>";
				
			} elseif ($match == 'teamName') {
				if ($this->newsfeed) {
					// Is newsfeed, add link
					$pre  = "<a href='/teams/" . $this->teamID . "' class='" . $class . "'>";
					$post = "</a>";
				} else {
					$pre  = "<span class='" . $class . "'>";
					$post = "</span>";
				} 
				
			} elseif ($replaceVal == '') {
				$pre  = "<span class='" . $class . "'>";
				$replaceVal = $match;
				$post = "</span>";
				
			}
			
			if ($ucwords) {
				// Set ucwords to true, capitalize replaceval
				$replaceVal = ucwords($replaceVal);
			}
			
			
			$replace[] = $pre . $replaceVal . $post;
		}
		
		$str = str_replace($matches[0],$replace, $this->text);
		
		// Replace possession holder with possession var (ie a game, your game)
		
		return str_replace(array('&possession', '&is'), array($possession, $is), $str);
		
	}
	
	public function getTeamName()
	{
		return ucwords($this->_attribs['teamName']);
	}
	
	/**
	 * Is the picture of sports?
	 */
	public function isSports()
	{
		if ($this->_attribs['pictureType'] == 'sports') {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * used in MessagesMapper to let notification be shown in newsfeed with messages
	 */	
	public function setMessage($message) {
		$this->text = $message;
		return $this;
	}
	
	public function save($loopSave = false)
	{
		$this->setCurrent('dateHappened');
		
		$notificationDetails = array('action'  => $this->action,
									 'type'	   => $this->type,
									 'details' => $this->details);
		
		$data = array('actingUserID' => $this->actingUserID,
					   'receivingUserID' => $this->receivingUserID,
					   'gameID'  => $this->gameID,
					   'teamID'  => $this->teamID,
					   'ratingID'  => $this->ratingID,
					   'parkID'  => $this->parkID,
					   'cityID'	 => $this->cityID,
					   'dateHappened' => $this->dateHappened);
					   
		return $this->getMapper()->addNotification($notificationDetails, $data);
	}
	
	
}