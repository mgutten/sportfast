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
		/*
		$messages = "SELECT tm.teamID as teamID, 
					   tm.userID as userID,
					   u.firstName as firstName,
					   u.lastName as lastName,
					   tm.message as message, 
					   tm.dateHappened as dateHappened,
					   '' as sport,
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
							   t.sport as sport,
							   n.pictureType as pictureType,
							   'notification' as type
							   FROM notification_log as nl
							INNER JOIN users as u ON nl.actingUserID = u.userID
							INNER JOIN notifications as n ON n.notificationID = nl.notificationID
							INNER JOIN teams as t ON t.teamID = nl.teamID
							WHERE nl.teamID = '" . $teamID . "' AND n.action != 'post'";
		*/
		
		$messages = "SELECT tm.teamID as teamID, 
					   tm.userID as userID,
					   '' as receivingUserID,
					   `u`.firstName as firstName, 
					   `u`.lastName as lastName, 
					   '' as receivingFirstName, 
					   '' as receivingLastName, 
					   tm.message as message, 
					   tm.dateHappened as dateHappened,
					   '' as sport,
					   '' as pictureType,
					   'message' as type,
					   tm.teamMessageID
					   FROM team_messages as tm
					INNER JOIN users as u ON tm.userID = u.userID
					WHERE tm.teamID = '" . $teamID . "'";
				
		$notifications = "SELECT nl.teamID as teamID, 
							   nl.actingUserID as userID, 
							   nl.receivingUserID,
							   `u`.firstName as firstName, 
							   `u`.lastName as lastName, 
							   `u2`.firstName as receivingFirstName, 
							   `u2`.lastName as receivingLastName, 
							   n.text as message, 
							   nl.dateHappened as dateHappened,
							   t.sport as sport,
							   n.pictureType as pictureType,
							   'notification' as type,
							   '' as teamMessageID
							   FROM notification_log as nl
							INNER JOIN users u ON nl.actingUserID = u.userID
							LEFT JOIN users u2 ON nl.receivingUserID = u2.userID
							INNER JOIN notifications as n ON n.notificationID = nl.notificationID
							INNER JOIN teams as t ON t.teamID = nl.teamID
							WHERE nl.teamID = '" . $teamID . "' 
								AND n.action != 'post'
								AND n.public = 1
							GROUP BY nl.notificationLogID";
							
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
	 * get messages for game's wall
	 * @params ($teamID	=> id of game to search for,
	 *			$savingClass => messages model 
	 */
	 public function getGameMessages($gameID, $savingClass)
	 {
		$db		= Zend_Db_Table::getDefaultAdapter();
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$messages = "SELECT tm.gameID as gameID, 
					   tm.userID as userID, 
					   u.firstName as firstName,
					   u.lastName as lastName,
					   tm.message as message, 
					   tm.dateHappened as dateHappened,
					   '' as sport,
					   '' as pictureType,
					   'message' as type,
					   '' as action,
					   '' as actionType,
					   '' as details,
					   tm.gameMessageID,
					   ug.confirmed as confirmed,
					   '' as lastChanged
					   FROM game_messages as tm
					INNER JOIN users as u ON tm.userID = u.userID
					LEFT JOIN user_games ug ON ug.userID = tm.userID AND ug.gameID = tm.gameID
					WHERE tm.gameID = '" . $gameID . "'";
				
		$notifications = "SELECT nl.gameID as gameID, 
							   nl.actingUserID as userID, 
							   u.firstName as firstName,
					   		   u.lastName as lastName,
							   n.text as message, 
							   nl.dateHappened as dateHappened,
							   g.sport as sport,
							   n.pictureType as pictureType,
							   'notification' as type,
							   n.action as action,
							   n.type as actionType,
							   n.details as details,
							   '' as gameMessageID,
							   ug.confirmed as confirmed,
							   ug.lastChanged as lastChanged
							   FROM notification_log as nl
							INNER JOIN users as u ON nl.actingUserID = u.userID
							INNER JOIN notifications as n ON n.notificationID = nl.notificationID
							INNER JOIN games as g ON g.gameID = nl.gameID
							LEFT JOIN user_games as ug ON ug.userGameID = nl.userGameID
							WHERE nl.gameID = '" . $gameID . "' 
								AND n.public = 1 AND n.action != 'create'";
							
		$sql = $messages . " UNION " . $notifications;
		$sql .= " ORDER BY CASE WHEN lastChanged IS NULL OR lastChanged = '' THEN dateHappened ELSE lastChanged END DESC LIMIT 30";
		
		$messages = $db->fetchAll($sql);
		
		foreach ($messages as $message)
		{
				
			$model = $savingClass->addMessage($message);

		}
		
		return $savingClass;
	 }	
	 
	 /**
	  * get user message groups where user received a message
	  */
	 public function getUserMessageGroups($userID, $savingClass)
	 {
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$select = "SELECT `m`.* FROM 
						(SELECT m2.*, u.* FROM messages as m2 
							LEFT JOIN users as u ON (u.userID = m2.sendingUserID)
							WHERE (receivingUserID = '" . $userID . "')
							ORDER BY m2.`dateHappened` desc) AS `m` 
					GROUP BY m.messageGroupID ORDER BY m.dateHappened DESC";
		
		/* if want to normalize to message_groups table
		SELECT `m`.* FROM 
						(SELECT m2.*,u.* FROM messages as m2 
							INNER JOIN users as u ON (u.userID = m2.sendingUserID)
							INNER JOIN message_groups as mg ON (mg.messageGroupID = m2.messageGroupID)
							WHERE (mg.userID1 = '1' OR mg.userID2 = '1') AND m2.sendingUserID != '1'
							ORDER BY m2.`dateHappened` desc) AS `m` 
					GROUP BY m.messageGroupID ORDER BY m.dateHappened DESC
		*/
		
		$results = $db->fetchAll($select);
		
		foreach ($results as $result) {
			$savingClass->addMessage($result, true);
		}
		
		return $savingClass;
		
	 }
	 
	 /**
	  * get all messages in message group
	  */
	 public function getMessageGroup($messageGroupID, $savingClass)
	 {
		 $table = $this->getDbTable();
		 $select = $table->select();
		 $select->setIntegrityCheck(false);
		 
		 $select->from(array('mg' => 'message_groups'))
		 		->where('mg.messageGroupID = ?', $messageGroupID)
				->limit(1);
			
		 $results = $table->fetchAll($select);
		 
		 foreach ($results as $result) {
			 $savingClass->setAttribs($result);
		 }
		 
		 $select = $table->select();
		 $select->setIntegrityCheck(false);
		 
		 $select->from(array('m' => 'messages'))
		 		->joinLeft(array('u' => 'users'),
					   'u.userID = m.sendingUserID')
		 		->where('m.messageGroupID = ?', $messageGroupID)
				->order('m.dateHappened DESC')
				->limit('30');
				
		 $messages = $table->fetchAll($select);
		 
		 foreach ($messages as $message) {
			 $savingClass->addMessage($message, true);
		 }
		 
		 return $savingClass;
	 }
		 
	 
	 /**
	  * test if messageGroupExists, if not, add it
	  */
	 public function messageGroupExists($userID1, $userID2)
	 {
		 $db = Zend_Db_Table::getDefaultAdapter();
		 
		 $select = "SELECT mg.messageGroupID 
		 				FROM message_groups as mg
						WHERE (mg.userID1 = :userID1 AND mg.userID2 = :userID2)
							OR (mg.userID1 = :userID2 AND mg.userID2 = :userID1)
						LIMIT 1";
							
		$statement = $db->query($select,
							  array(':userID1' => $userID1, ':userID2' => $userID2)); // returned result is array, not object
	
							  
	    $results = $statement->fetchAll();
		
		if ($results) {
			foreach ($results as $result) {
				return $result['messageGroupID'];
			}
		} else {
			// No message group exists, insert it
			$data = array('userID1' => $userID1,
						  'userID2' => $userID2);
			$db->insert('message_groups', $data);
			
			return $db->lastInsertID();
		}
		
	 }
	 
	 /**
	  * delete message
	  */
	 public function delete($messageModel)
	 {
		 if ($messageModel->isTeamMessage()) {
			 // Team
			 $table = 'Application_Model_DbTable_TeamMessages';
			 $idType = 'teamMessageID';
			 $messageModel->setTeamMessage();
		 } elseif ($messageModel->isGameMessage()) {
			 // Game
			 $table = 'Application_Model_DbTable_GameMessages';
			 $idType = 'gameMessageID';
			 $messageModel->setGameMessage();
		 } elseif ($messageModel->isUserMessage()) {
			 // User
			 $table = 'Application_Model_DbTable_Messages';
			 $idType = 'messageID';
			 $messageModel->setUserMessage();
		 } else {
			 return false;
		 }
		 
		 $this->setDbTable($messageModel->getDbTable());
		 
		 $table = $this->getDbTable();
		 
		 $primaryKey = $messageModel->_primaryKey;

		 $table->delete(array($primaryKey . ' = ?' => $messageModel->$primaryKey)); 
		 
		 return true;
	 }
			
						
		
	 
}
		
			   
