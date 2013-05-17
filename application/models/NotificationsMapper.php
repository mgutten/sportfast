<?php

class Application_Model_NotificationsMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_NotificationLog';
	
	/**
	 * get the newsfeed of recent events given a cityID
	 * @params ($cityID => id of city that we are searching in)
	 * @return $savingClass
	 */
	public function getNewsfeed($cityID, $savingClass, $onlyNew = false, $limit = 10)
	{
		
		$db = Zend_Db_Table::getDefaultAdapter();   
		
		$select = "SELECT `nl`.*, 
						  `n`.*, 
						  `u`.firstName as actingFirstName, 
						  `u`.lastName as actingLastName, 
						  `u2`.firstName as receivingFirstName, 
						  `u2`.lastName as receivingLastName,
						  `u`.userID as userID,
						  COALESCE(`ga`.sport,`t`.sport,`ur`.sport) as sport,
						  COALESCE(`ga`.date,`ur`.dateHappened) as date, 
						  ga.parkName, 
						  ga.parkID, 
						  ga.date, 
						  t.teamName, 
						  gr.groupName
					 FROM `notification_log` AS `nl`
					 INNER JOIN `notifications` AS `n` ON n.notificationID = nl.notificationID
					 LEFT JOIN `users` AS `u` ON u.userID = nl.actingUserID
					 LEFT JOIN `users` AS `u2` ON u2.userID = nl.receivingUserID
					 LEFT JOIN `games` AS `ga` ON ga.gameID = nl.gameID
					 LEFT JOIN `teams` AS `t` ON t.teamID = nl.teamID
					 LEFT JOIN `groups` AS `gr` ON gr.groupID = nl.groupID
					 LEFT JOIN `user_ratings` AS `ur` ON ur.userRatingID = nl.ratingID 
					 WHERE (nl.cityID = " . $cityID . ") 
						AND n.public = '1' ";
						
		if ($onlyNew) {
			// Select only notifications that are newer than a minute
			$select .= "AND dateHappened > (NOW() - INTERVAL 2 MINUTE)";
		}
		$select .=	 "ORDER BY nl.dateHappened DESC
					 LIMIT " . $limit;
		
		$results = $db->fetchAll($select);
		
		$savingClass->isNewsfeed = true;
		
		foreach ($results as $result) {
			$notification = $savingClass->addNotification($result);
			$notification->newsfeed = true;
		}
		
		return $savingClass;
		
	}
	
	/**
	 * get user's latest activities
	 */
	public function getUserActivities($userClass, $savingClass, $limit = 15)
	{
		$db = Zend_Db_Table::getDefaultAdapter();   
		$userID = $userClass->userID;
		
		$select = "SELECT `nl`.*, 
						  `n`.*, 
						  `u`.firstName as actingFirstName, 
						  `u`.lastName as actingLastName, 
						  `u2`.firstName as receivingFirstName, 
						  `u2`.lastName as receivingLastName, 
						  `u`.userID as actingUserID,
						  `u2`.userID as receivingUserID,
						  COALESCE(`ga`.sport,`t`.sport,`ur`.sport) as sport,
						  COALESCE(`ga`.date,`ur`.dateHappened) as date, 
						  ga.parkName, 
						  ga.parkID, 
						  ga.date, 
						  t.teamName, 
						  gr.groupName
					 FROM `notification_log` AS `nl`
					 INNER JOIN `notifications` AS `n` ON n.notificationID = nl.notificationID
					 LEFT JOIN `users` AS `u` ON u.userID = nl.actingUserID
					 LEFT JOIN `users` AS `u2` ON u2.userID = nl.receivingUserID
					 LEFT JOIN `games` AS `ga` ON ga.gameID = nl.gameID
					 LEFT JOIN `teams` AS `t` ON t.teamID = nl.teamID
					 LEFT JOIN `groups` AS `gr` ON gr.groupID = nl.groupID
					 LEFT JOIN `user_ratings` AS `ur` ON ur.userRatingID = nl.ratingID 
					 WHERE (nl.actingUserID = '" . $userID . "'  OR nl.receivingUserID = '" . $userID . "')
					 	AND n.public = '1'
					 ORDER BY nl.dateHappened DESC LIMIT " . $limit;
						
		
		$results = $db->fetchAll($select);
		
		$savingClass->isNewsfeed = true;
		
		foreach ($results as $result) {
			$notification = $savingClass->addNotification($result);
			$notification->newsfeed = true;
		}
		
		return $savingClass;	
	}
	
	/**
	 * user confirmed friend, team, or group request so add to db
	 * @params ($notificationLogID => id from notification_log table, 
	 *			$type => type from notifications table)
	 */
	 public function notificationConfirm($notificationLogID, $type)
	 {
		 if ($type == 'friend') {
			 $query = "INSERT INTO friends (userID1, userID2, userName1, userName2)
			 			(SELECT nl.actingUserID, 
								nl.receivingUserID,
								CONCAT(u1.firstName, ' ', LEFT(u1.lastName,1)), 
								CONCAT(u2.firstName, ' ', LEFT(u2.lastName,1)) 
							FROM notification_log as `nl`
							INNER JOIN users as u1 ON u1.userID = nl.actingUserID
							INNER JOIN users as u2 ON u2.userID = nl.receivingUserID
							WHERE nl.notificationLogID = '" . $notificationLogID . "')";
			
			 $query2 = "INSERT INTO notification_log (actingUserID, receivingUserID, notificationID, dateHappened)
			 			 (SELECT nl.actingUserID, 
						 		 nl.receivingUserID, 
								 (SELECT notificationID FROM notifications WHERE type IS NULL and action = 'friend'), 
								 NOW()
						 	FROM notification_log as `nl`
							WHERE nl.notificationLogID = '" . $notificationLogID . "')";
		 } elseif ($type == 'team') {
			 $query = "INSERT INTO user_teams (teamID, userID)
			 			(SELECT nl.teamID,
								nl.receivingUserID
							FROM notification_log as `nl`
							WHERE nl.notificationLogID = '" . $notificationLogID . "')";
			
			 $query2 = "INSERT INTO notification_log (actingUserID, teamID, notificationID, dateHappened)
			 			 (SELECT nl.receivingUserID, 
						 		 nl.teamID, 
								 (SELECT notificationID FROM notifications WHERE type ='team' AND action = 'join' AND details IS NULL), 
								 NOW()
						 	FROM notification_log as `nl`
							WHERE nl.notificationLogID = '" . $notificationLogID . "')";
		 } elseif ($type == 'game') {
			 $query = "INSERT INTO user_games (gameID, userID)
			 			(SELECT nl.gameID,
								nl.receivingUserID
							FROM notification_log as `nl`
							WHERE nl.notificationLogID = '" . $notificationLogID . "')";
			
			 $query2 = "INSERT INTO notification_log (actingUserID, gameID, notificationID, dateHappened)
			 			 (SELECT nl.receivingUserID, 
						 		 nl.gameID, 
								 (SELECT notificationID FROM notifications WHERE type ='game' AND action = 'join' AND details IS NULL), 
								 NOW()
						 	FROM notification_log as `nl`
							WHERE nl.notificationLogID = '" . $notificationLogID . "')";
		 }
		 
		 
		 $db = Zend_Db_Table::getDefaultAdapter();
		 $db->query($query);
		 
		 $db->query($query2);
		 
	 }
	 
	 /**
	  * add notification to notification_log
	  * @params ($notificationDetails => array of details to identify what notification was done (eg action, type, details),
	  *			 $data				  => array of data to be stored in notification_log)
	  */
	 public function addNotification($notificationDetails, $data)
	 {
		 $this->setDbTable($this->_dbTableClass); // reset table in case has been reset with "getForeignID" as in AjaxController createNotification
		 $table = $this->getDbTable();
		 
		 $select = $table->select();
		 
		 $select->setIntegrityCheck(false);
		 $select->from(array('nl' => 'notification_log'))
		 		->join(array('n' => 'notifications'),
					   'n.notificationID = nl.notificationID');
				   
		 foreach ($notificationDetails as $key => $val) {
			 if (empty($val)) {
				 $select->where('n.' . $key . ' IS NULL');
			 } else {
				 $select->where('n.' . $key . ' = ?', $val);
			 }
		 }
		 
		 foreach ($data as $key => $val) {
			 if (empty($val)) {
				 $select->where('nl.' . $key . ' IS NULL');
				 $data[$key] = new Zend_Db_Expr('NULL');
			 } else {
				 $select->where('nl.' . $key . ' = ?', $val);
			 }
		 }
		 
		$select->where('nl.dateHappened >= (NOW() - INTERVAL 3 HOUR)');


		$results = $table->fetchAll($select);

		if (count($results) > 0) {
			// A similar notification was added very recently
			return false;
		}
		 
		 
		 $data['dateHappened']	 = new Zend_Db_Expr('NOW()');
		 $data['notificationID'] = $this->getForeignID('Application_Model_DbTable_Notifications', 'notificationID', $notificationDetails);
		 
		 
		 $table->insert($data);
		 
		 
	 }

}
