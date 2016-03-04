<?php

class Application_Model_Notifications extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_UsersMapper';
	protected $_dbTable		= 'Application_Model_DbTable_NotificationLog';	
	
	protected $_attribs     = array('read'     => array(),
									'unread'   => array(),
									'lastRead' => '',
									'isNewsfeed' => false
									);
	protected $_parent	   = '';
	protected $_primaryKey = 'notificationLogID';	
	
	
	
	public function __construct ($parent = false)
	{
		if ($parent) {
			$this->_parent = $parent;
		}
	}
	
	public function getNewsfeed($cityID, $onlyNew = false, $limit = 10)
	{
		$this->setMapper('Application_Model_NotificationsMapper');
		return $this->getMapper()->getNewsfeed($cityID, $this, $onlyNew, $limit);
	}
	
	public function combineLikeNotifications()
	{
		$combinedArray = $this->unread;
		array_push($combinedArray, $this->read);
		
		foreach ($combinedArray as $notification) {
			if ($notification->action == 'join'){
			}
		}
				
		
	}
	
	public function addNotification($resultRow)
	{
		// Result row from getOldUserNotifications and getNewUserNotifications is array, not object

		if (($time = strtotime($resultRow['dateHappened']) > strtotime($this->lastRead)) && !$this->isNewsfeed) {
			// New notification
			$notification = $this->_attribs['unread'][] = new Application_Model_Notification($resultRow);
			$notification->read = false;
		} else {
			// Old notification
			$notification = $this->_attribs['read'][] = new Application_Model_Notification($resultRow);
			$notification->read = true;
		}
		return $notification;
	}
	
	/**
	 * search through notifications and delete matching one
	 */
	public function deleteNotificationByID($notificationLogID)
	{
		$sections = array('read', 'unread');
		foreach ($sections as $section) {
			
			if ($this->hasValue($section)) {
				// Section has values, search for id
				$array = $this->_attribs[$section];
				foreach ($array as $key => $notification) {
					if (!is_object($notification)) {
						continue;
					}
					if ($notification->notificationLogID == $notificationLogID) {
						// Match, delete
						unset($this->_attribs[$section][$key]);
						return true;
					}
				}
			}
		}
		
		return false;
		
	}
	
	public function getNotificationByID($notificationLogID)
	{
		$sections = array('read', 'unread');
		foreach ($sections as $section) {
			
			if ($this->hasValue($section)) {
				// Section has values, search for id
				$array = $this->_attribs[$section];
				foreach ($array as $key => $notification) {
					if (!is_object($notification)) {
						continue;
					}
					if ($notification->notificationLogID == $notificationLogID) {
						// Match, delete
						return $notification;
					}
				}
			}
		}
		
		return false;
	}
	
	public function getUserActivities($userClass, $limit = 15)
	{
		$this->setMapper('Application_Model_NotificationsMapper');
		return $this->getMapper()->getUserActivities($userClass, $this, $limit);
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
			// Unread notifications
			for ($i = count($this->_attribs['unread']) - 1; $i >= 0; $i--) {
				$this->_attribs['unread'][$i]->read = true;
				array_unshift($this->_attribs['read'],$this->_attribs['unread'][$i]);
			}
		
			//array_unshift($this->_attribs['read'],$this->_attribs['unread']);
			
			$this->resetNewNotifications();
		}
		return $this;
	}
	
	
	public function getUserNotifications($onlyNew = false, $sinceTime = false)
	{
		return $this->getMapper()->getUserNotifications($this->_parent, $this, $onlyNew, $sinceTime);
	}
	
	
	public function resetNewNotifications()
	{
		$this->_attribs['unread'] = array();
		return $this;
	}
	
	/**
	 * delete all notification with details
	 */
	public function deleteAll($details)
	{
		$this->setMapper('Application_Model_NotificationsMapper');
		return $this->getMapper()->delete($details);
	}
	
}