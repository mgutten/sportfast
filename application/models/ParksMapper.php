<?php

class Application_Model_ParksMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Parks';
	
	public function getParkByID($parkID, $savingClass = false)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('p' => 'parks'))
		       ->join(array('pl' => 'park_locations'),
			   		  'pl.parkID = p.parkID',
					  array('AsText(pl.location) as location'))
			   ->joinLeft(array('pr' => 'park_ratings'),
			   			  'pr.parkID = p.parkID',
						  array('avg(pr.quality) as quality',
						  		'count(pr.parkID) as totalRatings'))
			   ->where('p.parkID = ?', $parkID);				
				
		
		$park = $table->fetchRow($select);
		
		$savingClass->setAttribs($park);
		
		
		return $savingClass;						
	}
	
	/**
	 * Get all parks that match $options variable
	 * @params ($options   => array of options including:
	 *					sports => associative array of sport => type,
	 *					distance => distance to look from user's location,
	 *					time => time to look for ('user' for user availability, false for anytime),
	 *					age => array('lower' => lower average age limit, 'upper' => upper average age limit),
	 *					skill => array('lower' => lower average skill limit, 'upper' => upper average skill limit)
	 *			$userClass   => user class,
	 *		    $savingClass => parks object,
	 */
	public function findParks($options, $userClass, $savingClass, $limit = 200)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$userID  = $userClass->userID;
		$where = array();
		$having = array();
		
		$select->setIntegrityCheck(false);
		// Default location to search near is user's home location, look for games within $distance of user's home location
		$latitude  = $userClass->getLocation()->getLatitude();
		$longitude = $userClass->getLocation()->getLongitude();
		$bounds = $this->getBounds($latitude, $longitude, 20);	
				

		$select->from(array('p'  => 'parks'),
					  array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.*')))
			   ->join(array('pl' => 'park_locations'),
			   		  'pl.parkID = p.parkID',
					  array('AsText(pl.location) as location'));
			   /*->join(array('uus' => 'user_sports'),
			   		 'uus.sportID = t.sportID AND uus.userID = "' . $userID . '"',
					 array('skillCurrent as userSkill'))
			   ->join(array('z' => new Zend_Db_Expr($zipcodeQuery)),
			   				'z.cityID = t.cityID')
			   ->joinLeft(array('ut' => 'user_teams'),
			   		 'ut.teamID = t.teamID',
					 array(''))
			   ->joinLeft(array('us' => 'user_sports'),
			   		 'ut.userID = us.userID AND us.sportID = t.sportID',
					 array('avg(us.skillCurrent) as averageSkill',
					 	   'avg(us.attendance) as averageAttendance',
						   'avg(us.sportsmanship) as averageSportsmanship',
						   'avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID) as skillDifference',
						   'COUNT(us.userID) as totalPlayers'
						   ))
			   ->where('t.public = "1"')
			   ->where('uus.skillCurrent >= t.minSkill AND uus.skillCurrent <= t.maxSkill');


		$sportWhere = '';
		$counter = 0;
		
		foreach ($options['sports'] as $sport => $inner) {
			if ($counter != 0) {
				$sportWhere .= ' OR ';
			}
			
			$sportWhere .= "(t.sport = '" . $sport . "' )";
			$counter++;
		}
		
		$where[] = $sportWhere;
		
		/*
		if (!empty($options['skill'])) {
			$having[] = "avg(us.skillCurrent) >= '" . $options['skill']['lower'] . "' AND avg(us.skillCurrent) <= '" . $options['skill']['upper'] . "'";
		}				  
		*/	
		
		/*			   
		foreach ($where as $statement) {
			$select->where($statement);
		}
		
		if (count($having) > 0) {
			$statements = 'CASE WHEN COUNT(ug.userID) = 0 THEN 1=1 ELSE (';
			$counter = 0;
			foreach ($having as $statement) {
				if ($counter != 0) {
					$statements .= ' AND ';
				}
				$statements .= '(' . $statement . ')';
				$counter++;
			}
			$statements .= ') END';
			$select->having(new Zend_Db_Expr($statements));

		}

		
		$select->group('t.teamID');
			   //->order('abs(avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID)) ASC');
		
		if (isset($options['order'])) {
			// Order by
			if ($options['order'] == 'players') {
				$select->order('(COUNT(us.userID) + SUM(ug.plus)) DESC');
			} elseif ($options['order'] == 'date') {
				$select->order('g.date ASC');
			}
		}
		
		$limitArray = explode(',',$limit);
		$totalLimit = trim($limitArray[0]);
		$offsetLimit = (isset($limitArray[1]) ? $limitArray[1] : 0);
		
		$select->limit($totalLimit,$offsetLimit);
		*/
		$results = $table->fetchAll($select);
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$totalRows = $db->fetchAll('SELECT FOUND_ROWS() as totalRows');
		$totalRows = $totalRows[0]['totalRows'];
		
		foreach ($results as $result) {
			$savingClass->addPark($result);
		}
		
		
		$savingClass->totalRows = $totalRows;
		
		return $savingClass;
		
	}
	
	
	/**
	 * get upcoming games at park
	 */
	public function getParkGames($parkID, $newOnly = true)
	{
		// Get upcoming games
		$table   = $this->getDbTable();
		$select  = $table->select();
		$user = Zend_Auth::getInstance()->getIdentity();
		$userID = $user->userID;
		
		$select->setIntegrityCheck(false);
		$select->from(array('g' => 'games'))
			   ->join(array('st' => 'sport_types'),
			   		  'st.typeID = g.typeID')
			   ->joinLeft(array('ug' => 'user_games'),
			   		  	  'ug.gameID = g.gameID',
						  array(''))
			   ->joinLeft(array('us' => 'user_sports'),
			   		  	  'us.userID = ug.userID AND us.sportID = g.sportID',
					 array('avg(us.skillCurrent) as averageSkill',
					 	   'avg(us.attendance) as averageAttendance',
						   'avg(us.sportsmanship) as averageSportsmanship',
						   'avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = st.sportID) as skillDifference',
						   '(COUNT(us.userID) + SUM(ug.plus)) as totalPlayers'
						   ))
			   ->where('g.parkID = ?', $parkID);
			   
		if ($newOnly) {
			$select->where('g.date > now()');
		}
		
		$select->group('g.gameID');
		   
		$results = $table->fetchAll($select);
		
		$games = new Application_Model_Games();
		foreach ($results as $result) {
			$games->addGame($result);
		}
		
		return $games;
	}
	
	/**
	 * get park ratings
	 */
	public function getParkRatings($parkID)
	{
		
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('pr' => 'park_ratings'))
			   ->joinLeft(array('u' => 'users'),
			   		  'u.userID = pr.userID')
			   ->where('pr.parkID = ?', $parkID)
			   ->order('pr.dateHappened DESC');
		   
		$results = $table->fetchAll($select);
		
		$ratings = new Application_Model_Ratings();
		foreach ($results as $result) {
			$ratings->addRating($result);
		}
		
		return $ratings;
		
	}
	
	/**
	 * get stash info for parkID
	 */
	public function getParkStash($parkID)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('ps' => 'park_stashes'))
			   ->join(array('sl' => 'stash_locations'),
			   		  'ps.parkID = sl.parkID',
					  array('AsText(sl.location) as location'))
			   ->joinLeft(array('si' => 'stash_items'),
			   			  'ps.itemID = si.itemID')
			   ->joinLeft(array('spi' => 'sport_items'),
			   			  'spi.itemName = si.itemName')
			   ->where('ps.parkID = ?', $parkID);
		 
		$results = $table->fetchAll($select);
		
		$stash = new Application_Model_Stash();
		foreach ($results as $result) {
			$stash->setAttribs($result);
			$stash->addItem($result);
			$stash->addSport($result->sport);
			$stash->getLocation()->setAttribs($result);
		}
		
		return $stash;
	}
	
	/**
	 * get nearby parks
	 * @params ($latitude => latitude of point to search near,
	 *			$longitude => longitude of point to search near,
	 *			$cityID  => cityID of point (to help limit db search),
	 *			$parkID  => if searching near a park, do not include that park in results)
	 * @returns array of park models
	 */
	public function getNearbyParks($latitude, $longitude, $cityID, $parkID = false, $limit = 3) 
	{
		$bounds = $this->getBounds($latitude, $longitude);
		
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('p' => 'parks'))
			   ->join(array('pl' => 'park_locations'),
			   			  'pl.parkID = p.parkID',
						  array('AsText(pl.location) as location'))
			   ->where($this->getAreaWhere($bounds['upper'],$bounds['lower'],'pl.location'))
			   ->where('p.cityID IN ' . $this->getCityIDRange($cityID));
			   
		if ($parkID) {
			$select->where('p.parkID != ?', $parkID);
		}
		
		$select->order('GLength(
								LineStringFromWKB(
								  LineString(
									pl.location, 
									GeomFromText("POINT(' . $latitude . ' ' . $longitude . ')")
								  )
								 )
								)');
		
		$select->limit($limit);
							
		$results = $table->fetchAll($select);
		
		$parks = array();
		foreach ($results as $result) {
			$park = new Application_Model_Park($result);
			$parks[] = $park;
		}
		
		return $parks;
	}

		
		
}