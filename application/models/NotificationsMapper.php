<?php

class Application_Model_NotificationsMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_NotificationLog';
	
	/**
	 * get the newsfeed of recent events given a cityID
	 * @params ($cityID => id of city that we are searching in)
	 * @return $savingClass
	 */
	public function getNewsfeed($cityID, $savingClass, $limit = 10)
	{
		/*
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		$select->from(array('nl' => 'notification_log'))
			   ->join(array('n' => 'notifications'),
							  	   'n.notificationID = nl.notificationID')
			   ->where('nl.cityID = ?', $cityID);
		   
		$results = $table->fetchAll($select);
		*/
		
		$db = Zend_Db_Table::getDefaultAdapter();   
		
		$select = "SELECT `nl`.*, 
						  `n`.*, 
						  `u`.firstName as firstName, 
						  `u`.lastName as lastName, 
						  `u`.userID as userID,
						  COALESCE(`ga`.sport,`t`.sport,`ur`.sport) as sport,
						  COALESCE(`ga`.date,`ur`.date) as date, 
						  ga.parkName, 
						  ga.parkID, 
						  ga.date, 
						  t.teamName, 
						  gr.groupName
					 FROM `notification_log` AS `nl`
					 INNER JOIN `notifications` AS `n` ON n.notificationID = nl.notificationID
					 LEFT JOIN `users` AS `u` ON u.userID = nl.actingUserID
					 LEFT JOIN `games` AS `ga` ON ga.gameID = nl.gameID
					 LEFT JOIN `teams` AS `t` ON t.teamID = nl.teamID
					 LEFT JOIN `groups` AS `gr` ON gr.groupID = nl.groupID
					 LEFT JOIN `user_ratings` AS `ur` ON ur.userRatingID = nl.ratingID 
					 WHERE (nl.cityID = " . $cityID . ") 
						AND n.public = '1' 
					 LIMIT " . $limit;
						
		$results = $db->fetchAll($select);
		
		$savingClass->isNewsfeed = true;
		
		foreach ($results as $result) {
			$notification = $savingClass->addNotification($result);
			$notification->newsfeed = true;
		}
		
		return $savingClass;
		
	}
				 

		
	
}