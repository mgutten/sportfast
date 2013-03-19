<?php

class Application_Model_UsersMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Users';

	
	public function getUserBy($column, $value, $savingClass = false)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		/*$select->where($column . ' = ' . '?', $value)
			   ->limit(1);
			   */
		$select->from(array('u' => 'users'))
               ->join(array('c' => 'cities'),
                      'u.cityID = c.cityID')
			   ->join(array('ul' => 'user_locations'),
			   		  'ul.userID = u.userID',
					   array('AsText(location) as location'))
			   ->where($column . ' = ?', $value)
			   ->limit(1);   
		
		$results = $table->fetchAll($select);
		
		$user = $this->createUserClasses($results, $savingClass);
		
		return $user;	
	}
	
	public function createUserClasses($results, $savingClass = false) 
	{
		$users = array();
		
		foreach ($results as $result) {
			if (!$savingClass) {
				// No saving class submitted, create new user
				$user = new Application_Model_User();
			} else {
				$user = $savingClass;
			}
			
			$user->getCity()->setAttribs($result);
			$user->getLocation()->setAttribs($result);
			$user->setAttribs($result);
			
			if(count($results) <= 1) {
				$users = $user;
				break;
			}
			
			$users[] = $user;
		}
		
		return $users;
	}
	
	/**
     * Find all of sports info for user (ie sports, types, positions, and availability
     *
     * @param string $password Plain text user password to hash
     * @return string The hash string of the password
     */
	public function getUserSportsInfo($userID, $modelClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		
		$select->setIntegrityCheck(false);
		$select->from(array('s'  => 'sports'))
			   ->join(array('sp' => 'sport_positions'),
			   		  's.sportID = sp.sportID')
			   ->join(array('usp' => 'user_sport_positions'),
			   		  'usp.positionID = sp.positionID')
			   ->join(array('st' => 'sport_types'),
			   		  'st.sportID = s.sportID')
			   ->join(array('ust' => 'user_sport_types'),
			   		  'ust.typeID = st.typeID')
			   ->join(array('us' => 'user_sports'),
			   		  'us.sportID = s.sportID') 
			   ->join(array('usa' => 'user_sport_availabilities'),
			   		  'usa.sportID = s.sportID') 
			   ->where('usp.userID = ?', $userID);

		
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			$sport = $result->sport;
			
			$sportModel = $modelClass->getSport($sport);
			$sportModel->setAttribs($result);
			$sportModel->getType($result->typeName)->setAttribs($result);
			$sportModel->getPosition($result->positionName)->setAttribs($result);
			$sportModel->setAvailability($result->day, $result->hour)->setAttribs($result);
			
			
		}
			
		return $modelClass;

	}
	
	/**
	 * get notifications for user given in $savingClass
	 * @params ($userID 	 => userClass model,
	 			$savingClass => where to save the information (Notifications object),
				$onlyNew	 => only select new notifications )
	 */
	public function getUserNotifications($userClass, $savingClass, $onlyNew = false)
	{
		/*
		$this->setDbTable('Application_Model_DbTable_NotificationLog');
		$table  = $this->getDbTable();
		$select = $table->select();
		
		$select->setIntegrityCheck(false);
		$select->from(array('nl'  => 'notification_log'))
			   ->join(array('n' => 'notifications'),
			   		  'n.notificationID = nl.notificationID')
			   ->joinLeft(array('u' => 'users'),
			   		  'u.userID = nl.actingUserID')
			   ->joinLeft(array('ga' => 'games'),
			   		  'ga.gameID = nl.gameID')
			   ->joinLeft(array('t' => 'teams'),
			   		  't.teamID = nl.teamID')
			   ->joinLeft(array('gr' => 'groups'),
			   		  'gr.groupID = nl.groupID')
			   ->joinLeft(array('ur' => 'user_ratings'),
			   		  'ur.userRatingID = nl.ratingID')
			   ->where('u.userID = ?', $userID);
		*/
		
		/* MUST RETRIEVE GAMEIDS, TEAMIDS, AND GROUPIDS FOR ALL GAMES, TEAMS, GROUPS THAT USER ($userClass) is currently in */
		$db = Zend_Db_Table::getDefaultAdapter();   
		
		$lastRead = $userClass->lastRead;
		
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
					 WHERE ((nl.receivingUserID = " . $userClass->userID . ") 
					 		OR ((nl.gameID IN (1,2,3) 
								OR nl.teamID IN (1,2,3) 
								OR nl.groupID IN (1,2,3))
								AND n.action != 'create')) ";
		if ($onlyNew) {
			// Select only notifications since last read
			$select .= "AND nl.dateHappened > '" . $lastRead . "' ";
		} else {
			// Select old notifications
			$select .= "AND nl.dateHappened <= '" . $lastRead . "' ";
		}
		$select .= "ORDER BY nl.dateHappened DESC";
		
		if (!$onlyNew) {
			$select .= " LIMIT 10";
		}
		 			 
					  
		$results = $db->fetchAll($select);

		foreach ($results as $result) {
			
			$savingClass->addNotification($result);
		}

	}
	
	/**
     * Create a hash (encrypt) of a plain text password.
     *
     * @param string $password Plain text user password to hash
     * @return string The hash string of the password
     */
    public function hashPassword($password) {
        return $this->hasher()->HashPassword($password);
    }
 
    /**
     * Compare the plain text password with the $hashed password.
     *
     * @param string $password
     * @param string $hash The hashed password
     * @param int $user_id The user row ID
     * @return bool True if match, false if no match.
     */
    public function checkPassword($password, $hash, $user_id = '') {
        // Check if we are still using regular MD5 (32 chars)
		
        if (strlen($hash) <= 32) {
            $check = ($hash == md5($password));
            if ($check && $user_id) {
                // Rehash using new PHPass-generated hash
                $this->setPassword($password, $user_id);
                $hash = $this->hashPassword($password);
            }
        }
 
        $check = $this->hasher()->CheckPassword($password, $hash);
 		
        return $check;
    }
  
    /**
     * Checks for Webjawns_PasswordHash in registry. If not present, creates PHPass object.
     *
     * @uses Zend_Registry
     * @uses Webjawns_PasswordHash
     *
     * @return Webjawns_PasswordHash PHPass
     */
    public function hasher() {
	
        if (!Zend_Registry::isRegistered('my_hasher')) {
            Zend_Registry::set('my_hasher', new My_Auth_PasswordHash(8, false));
        }
        return Zend_Registry::get('my_hasher');
    }

}