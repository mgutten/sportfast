<?php

class Application_Model_Message extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_MessagesMapper';
	
	protected $_attribs     = array('teamMessageID' => '',
									'teamID'		=> '',
									'groupID'		=> '',
									'userID'		=> '',
									'message'		=> '',
									'dateHappened'  => '',
									'firstName'		=> '',
									'lastName'		=> '',
									'notification'  => ''
									);
									
	protected $_primaryKey = 'teamMessageID';
	protected $_dbTable = 'Application_Model_DbTable_TeamMessages';
	
	
	public function save()
	{
		if ($this->isGroupMessage()) {
			// Group message, change db table and primary key
			$this->_dbTable = 'Application_Model_DbTable_GroupMessages';
			$this->_primaryKey = 'groupMessageID';
		}
		
		return parent::save();
	}
	
	
	public function isGroupMessage()
	{
		if ($this->hasValue('groupID')) {
			return true;
		}
		return false;
	}
	
	public function getBoxProfilePic($size, $id = false, $type = 'users', $class = '', $outerClass = '')
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

	
}
