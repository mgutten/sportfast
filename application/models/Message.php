<?php

class Application_Model_Message extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_MessagesMapper';
	
	protected $_attribs     = array('teamMessageID' => '',
									'gameMessageID' => '',
									'messageID'		=> '',
									'teamID'		=> '',
									'groupID'		=> '',
									'gameID'		=> '',
									'userID'		=> '',
									'message'		=> '',
									'dateHappened'  => '',
									'firstName'		=> '',
									'lastName'		=> '',
									'notification'  => '',
									'sendingUserID' => '',
									'receivingUserID' => '',
									'read'			=> '',
									'messageGroupID'  => '',
									'type'			  => ''
									);
									
	protected $_primaryKey = 'teamMessageID';
	protected $_dbTable = 'Application_Model_DbTable_TeamMessages';
	
	
	public function save($loopSave = false)
	{
		if ($this->isGroupMessage()) {
			// Group message, change db table and primary key
			$this->_dbTable = 'Application_Model_DbTable_GroupMessages';
			$this->_primaryKey = 'groupMessageID';
		} elseif ($this->isUserMessage()) {
			$this->_dbTable = 'Application_Model_DbTable_Messages';
			$this->_primaryKey = 'messageGroupID';
		} elseif ($this->isGameMessage()) {
			$this->setGameMessage();
		}
		
		return parent::save($loopSave);
	}
	
	public function getFirstName()
	{
		if ($this->sendingUserID == 0) {
			// Sportfast sent
			return "Sportfast";
		} else {
			return $this->_attribs['firstName'];
		}
	}
	
	public function getLastName()
	{
		if ($this->sendingUserID == 0) {
			// Sportfast sent
			return " ";
		} else {
			return $this->_attribs['lastName'];
		}
	}		
	
	public function isGroupMessage()
	{
		if ($this->hasValue('groupID')) {
			return true;
		}
		return false;
	}
	
	public function isUserMessage()
	{
		if ($this->hasValue('messageGroupID')) {
			return true;
		}
		return false;
	}
	
	public function isGameMessage()
	{
		if ($this->hasValue('gameMessageID')) {
			return true;
		}
		return false;
	}
	
	public function isRead()
	{
		if ($this->read == '0') {
			return false;
		} else {
			return true;
		}
	}
	
	public function getMessage($nl2br = false)
	{
		if ($nl2br) {
			return nl2br($this->_attribs['message']);
		} else {
			return $this->_attribs['message'];
		}
	}
	
	public function getBoxProfilePic($size, $id = false, $type = 'users', $class = '', $outerClass = '')
	{
		if ($this->hasValue('notification')) {
			// Notification
			$notification = $this->notification;
			if ($notification->picture == 'users') {
				// Show user picture
				$id = $this->notification->userID;
			} else {
				// Show team picture
				$id = $this->teamID;
			}
		} else {
			// Text message from user
			$id = $this->userID;
		}

		return parent::getBoxProfilePic($size, $id, $type, $class, $outerClass);
	}
	
	public function getProfilePic($size, $id = false, $type = 'users')
	{
		if ($this->hasValue('notification')) {
			// Notification
			$notification = $this->notification;
			if ($notification->picture == 'users') {
				// Show user picture
				$id = $this->notification->actingUserID;
			} else {
				// Show team picture
				$id = $this->teamID;
			}
		} else {
			// Text message from user
			$id = $this->userID;
		}
		
		return parent::getProfilePic($size, $id, $type);
	}
	
	public function getUserName()
	{
		return ucwords($this->firstName) . ' ' . ucwords($this->lastName[0]);
	}
	
	public function getTimeFromNow($date = false, $maxDays = 7)
	{
		$date = $this->dateHappened;
		return parent::getTimeFromNow($date, $maxDays);
	}
	
	public function setGameMessage()
	{
		$this->_dbTable = 'Application_Model_DbTable_GameMessages';
		$this->_primaryKey = 'gameMessageID';
	}

	
}
