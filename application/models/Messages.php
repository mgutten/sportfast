<?php

class Application_Model_Messages extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_MessagesMapper';
	
	protected $_attribs     = array('read' => '',
									'unread' => '');
									
	protected $_parent;
	
	public function addMessage($resultRow)
	{
		// Result row from getOldUserNotifications and getNewUserNotifications is array, not object
		if (!is_array($resultRow)) {
			$resultRow = $resultRow->toArray();
		}
		if (isset($resultRow['read'])) {
			// Read column exists, test it
			if ($resultRow->read == '0') {
				// New notification
				$message = $this->_attribs['unread'][] = new Application_Model_Message($resultRow);
			} else {
				// Old notification
				$message = $this->_attribs['read'][] = new Application_Model_Message($resultRow);
			}
		} else {
			// Not part of user's notifications (unread or not), simple add to read pile
			if (isset($resultRow['type']) && $resultRow['type'] == 'notification') {
				// Row is a notification
				$message = $this->_attribs['read'][] = new Application_Model_Message($resultRow);
				$notification = $message->notification = new Application_Model_Notification($resultRow);
				$notification->teamName = $this->_parent->teamName;
				$notification->actingUserID = $resultRow['userID'];
				$notification->newsfeed = true;
				$notification->text     = $resultRow['message'];
				$notification->actingFirstName = $resultRow['firstName'];
				$notification->actingLastName = $resultRow['lastName'];
				$notification->pictureType = $resultRow['pictureType'];
			} else {
				// Row is a message
				$message = $this->_attribs['read'][] = new Application_Model_Message($resultRow);
			}
		}
		return $message;
	}
	
	public function countNewUserMessages($userID)
	{
		return $this->getMapper()->countNewUserMessages($userID);
	}
	
	public function countUnread()
	{
		if (($count = count($this->unread)) > 0) {
			return $count;
		} else {
			// Empty unread
			return '';
		}
	}
	
	/**
	 * get team messages
	 */
	public function getTeamMessages($teamID)
	{
		return $this->getMapper()->getTeamMessages($teamID, $this);
	}
	
		
	public function moveUnreadToRead()
	{
		if (count($this->_attribs['unread']) > 0) {
			// Unread messages
			foreach ($this->_attribs['unread'] as $message) {
				$message->read = true;
			}
		
			array_unshift($this->_attribs['read'],$this->_attribs['unread']);
			$this->resetNewMessages();
		}
		return $this;
	}
	
	public function setParent($parent)
	{
		$this->_parent = $parent;
		return $this;
	}
									
}
