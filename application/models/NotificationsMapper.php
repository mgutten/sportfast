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
		 }
		 
		 
		 $db = Zend_Db_Table::getDefaultAdapter();
		 $db->query($query);
		 
		 $db->query($query2);
		 
	 }

		
	
}