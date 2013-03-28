<?php

class Application_Model_Messages extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_MessagesMapper';
	
	protected $_attribs     = array('read' => '',
									'unread' => '');
	

	public function addMessage($resultRow)
	{
		// Result row from getOldUserNotifications and getNewUserNotifications is array, not object
		if ($resultRow->read == '0') {
			// New notification
			$message = $this->_attribs['unread'][] = new Application_Model_Message($resultRow);
		} else {
			// Old notification
			$message = $this->_attribs['read'][] = new Application_Model_Message($resultRow);
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
									
}
