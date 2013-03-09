<?php

class Application_Model_Notifications extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	protected $_dbTable		= 'Application_Model_DbTable_NotificationLog';	
	
	protected $_attribs     = array('read'     => array(),
									'unread'   => array(),
									'lastRead' => ''
									);
	protected $_parent	   = '';
	protected $_primaryKey = 'notificationLogID';	
	
	
	
	public function __construct ($parent)
	{
		if ($parent) {
			$this->_parent = $parent;
		}
	}
	
	public function addNotification($resultRow)
	{
		// Result row from getOldUserNotifications and getNewUserNotifications is array, not object

		if ($time = strtotime($resultRow['dateHappened']) > strtotime($this->lastRead)) {
			// New notification
			$notification = $this->_attribs['unread'][] = new Application_Model_Notification($resultRow);
			$notification->read = false;
		} else {
			// Old notification
			$notification = $this->_attribs['read'][] = new Application_Model_Notification($resultRow);
			$notification->read = true;
		}
		return $this;
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
		foreach ($this->_attribs['unread'] as $notification) {
			$notification->read = true;
		}
		
		array_unshift($this->_attribs['read'],$this->_attribs['unread']);
		
		$this->resetNewNotifications();
		return $this;
	}
	
	
	public function getUserNotifications($onlyNew = false)
	{
		return $this->getMapper()->getUserNotifications($this->_parent, $this, $onlyNew);
	}
	
	
	
	public function resetNewNotifications()
	{
		$this->_attribs['unread'] = array();
		return $this;
	}
	
	
}