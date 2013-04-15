<?php

class Application_Model_MessagesMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Messages';
	
	/**
	 * Count any new messages
	 * @params($userID   => userID)
	 */
	
	public function countNewUserMessages($userID)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		
		$select->from(array('m'  => 'messages'),
					  array('COUNT(receivingUserID) AS newMessages'))
			   ->where('receivingUserID = ?', $userID)
			   ->where('`m`.`read` = ?', '0');
		
 
		$results = $table->fetchRow($select);
		
		return $results->newMessages;
	}
	
	/**
	 * get messages for team's wall
	 * @params ($teamID	=> id of team to search for,
	 *			$savingClass => messages model 
	 */
	 public function getTeamMessages($teamID, $savingClass)
	 {
		$db		= Zend_Db_Table::getDefaultAdapter();
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$messages = "SELECT tm.teamID as teamID, 
					   tm.userID as userID, 
					   u.firstName as firstName,
					   u.lastName as lastName,
					   tm.message as message, 
					   tm.dateHappened as dateHappened,
					   '' as pictureType,
					   'message' as type
					   FROM team_messages as tm
					INNER JOIN users as u ON tm.userID = u.userID
					WHERE tm.teamID = '" . $teamID . "'";
				
		$notifications = "SELECT nl.teamID as teamID, 
							   nl.actingUserID as userID, 
							   u.firstName as firstName,
					   		   u.lastName as lastName,
							   n.text as message, 
							   nl.dateHappened as dateHappened,
							   n.pictureType as pictureType,
							   'notification' as type
							   FROM notification_log as nl
							INNER JOIN users as u ON nl.actingUserID = u.userID
							INNER JOIN notifications as n ON n.notificationID = nl.notificationID
							WHERE nl.teamID = '" . $teamID . "'";
							
		$sql = $messages . " UNION " . $notifications;
		$sql .= " ORDER BY dateHappened DESC LIMIT 10";
		
		$messages = $db->fetchAll($sql);
		
		foreach ($messages as $message)
		{
			$savingClass->addMessage($message);
		}
		
		return $savingClass;
	 }	
	 
	/**
	 * get messages for team's wall
	 * @params ($teamID	=> id of team to search for,
	 *			$savingClass => messages model 
	 */
	 public function getGameMessages($gameID, $savingClass)
	 {
		$db		= Zend_Db_Table::getDefaultAdapter();
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$messages = "SELECT tm.teamID as teamID, 
					   tm.userID as userID, 
					   u.firstName as firstName,
					   u.lastName as lastName,
					   tm.message as message, 
					   tm.dateHappened as dateHappened,
					   '' as pictureType,
					   'message' as type
					   FROM team_messages as tm
					INNER JOIN users as u ON tm.userID = u.userID
					WHERE tm.teamID = '" . $gameID . "'";
				
		$notifications = "SELECT nl.gameID as gameID, 
							   nl.actingUserID as userID, 
							   u.firstName as firstName,
					   		   u.lastName as lastName,
							   n.text as message, 
							   nl.dateHappened as dateHappened,
							   n.pictureType as pictureType,
							   'notification' as type
							   FROM notification_log as nl
							INNER JOIN users as u ON nl.actingUserID = u.userID
							INNER JOIN notifications as n ON n.notificationID = nl.notificationID
							WHERE nl.gameID = '" . $gameID . "'";
							
		$sql = $notifications;
		$sql .= " ORDER BY dateHappened DESC LIMIT 10";
		
		$messages = $db->fetchAll($sql);
		
		foreach ($messages as $message)
		{
			$savingClass->addMessage($message);
		}
		
		return $savingClass;
	 }	
	 
}
		
			   
