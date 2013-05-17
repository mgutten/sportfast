<?php

class Application_Model_UserMessages extends Application_Model_Messages
{
	protected $_mapperClass = 'Application_Model_MessagesMapper';
	
	protected $_attribs     = array('read' => '',
									'unread' => '',
									'messageGroupID' => '',
									'userID1' => '',
									'userID2' => '',
									'firstName' => '',
									'lastName'  => '');
									
									
	public function getUserMessageGroups($userID)
	{
		return $this->getMapper()->getUserMessageGroups($userID, $this);
	}
	
	public function getMessageGroup($messageGroupID)
	{
		return $this->getMapper()->getMessageGroup($messageGroupID, $this);
	}
	
	public function getOtherUserName()
	{
		return ucwords($this->firstName) . ' ' . ucwords($this->lastName[0]);

	}
	
	public function getOtherUserID($currentUserID)
	{
		if ($this->userID1 == $currentUserID) {
			return $this->userID2;
		} else {
			return $this->userID1;
		}
	}
	
}
