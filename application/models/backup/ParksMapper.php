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
			   			  'pr.parkID = p.parkID AND pr.success = 1',
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

		if (!empty($options['points'])) {
			// Map was moved or points have been set to not be around user location
			$bounds['upper'] = $options['points'][0];
			$bounds['lower'] = $options['points'][1];
		} else {
			// Default location to search near is user's home location, look for games within $distance of user's home location
			$latitude = $userClass->location->latitude;
			$longitude = $userClass->location->longitude;
			$bounds = $this->getBounds($latitude, $longitude, 7);
		}
		
		
		if (!empty($options['courts'])) {
			// Courts have been chosen
			$statement = '(';
			$counter = 0;
			foreach ($options['courts'] as $court) {

				if ($counter != 0) {
					$statement .= ' OR ';
				}
				
				$statement .= '(';
				
				if ($court == 'basketball') {
					$statement .= 'p.basketballOutdoor > 0 OR p.basketballIndoor > 0';
				} else {
					$statement .= 'p.' . $court . ' > 0';
				}
				
				$statement .= ')';
				$counter++;
			}
			$statement .= ')';
			$where[] = $statement;
		}
		
		if (!empty($options['stash'])) {
			if ($options['stash'] == 'has_stash') {
				// Has stash
				$where[] = 'p.stash = 1';
			} elseif ($options['stash'] == 'no_stash') {
				// No stash
				$where[] = 'p.stash = 0';
			}
		}
		
		if (!empty($options['type'])) {
			$statement = '(';
			$counter = 0;
			
			if (is_array($options['type'])) {
				// Array of options
				foreach ($options['type'] as $type) {
	
					if ($counter != 0) {
						$statement .= ' OR ';
					}
					
					$statement .= '(p.type = "' . $type . '")';
					
					$counter++;
				}
				$statement .= ')';
			} else {
				$statement = 'p.type = "' . $options['type'] . '"';
			}
			$where[] = $statement;
		}

		$select->from(array('p'  => 'parks'),
					  array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.*')))
			   ->join(array('pl' => 'park_locations'),
			   		  'pl.parkID = p.parkID',
					  array('AsText(pl.location) as location'));
					  
		if (!empty($options['courts'])) {
			// Courts have been selected, only show ratings for the court
			if ($options['courts'][0] == 'field') {
				// Looking for field
				$parkWhere = 'pr2.sport IN ("soccer", "ultimate", "football") ';
			} else {
				$parkWhere = 'pr2.sport = "' . strtolower($options['courts'][0]) . '"';
			}
			$parkRatings = "(SELECT AVG(pr2.quality) as quality,
									pr2.parkID,
									pr2.success 
							FROM park_ratings pr2
							WHERE " . $parkWhere . "
							GROUP BY pr2.parkID)";
		
			$select->joinLeft(array('pr' => new Zend_Db_Expr($parkRatings)),
							  'pr.parkID = p.parkID',
							  array('pr.quality'));
		} else {					  
			$select->joinLeft(array('pr' => 'park_ratings'),
							  'pr.parkID = p.parkID',
							  array('AVG(pr.quality) as quality',
									'COUNT(pr.parkRatingID) as numRatings'));
		}
		
		$select->where($this->getAreaWhere($bounds['upper'], $bounds['lower'], 'pl.location'))
			   ->where('p.temporary = 0')
			   ->where('pr.success = 1 OR pr.success IS NULL');
			   
			   
		foreach ($where as $statement) {
			$select->where($statement);
		}
		
		$select->group('p.parkID');
	
		if (!empty($options['order'])) {
			if ($options['order'] == 'distance') {

				$select->order('GLength(
									LineStringFromWKB(
									  LineString(
										pl.location, 
										(SELECT location FROM user_locations WHERE userID = "' . $userID . '")
									  )
									 )
									) ASC');
			} elseif ($options['order'] == 'rating') {
				$select->order('AVG(pr.quality) DESC');
			}
		}
		
		$results = $table->fetchAll($select);
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$totalRows = $db->fetchAll('SELECT FOUND_ROWS() as totalRows');
		$totalRows = $totalRows[0]['totalRows'];
		
		foreach ($results as $result) {
		
			$park = $savingClass->addPark($result);
			$park->ratings->setAttribs($result);
			$park->ratings->addRating($result);
			
		}
		
		$select  = $table->select();		
		$select->setIntegrityCheck(false);

		
		
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
			   ->where('pr.success = ?', '1')
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
			   //->where('p.cityID IN ' . $this->getCityIDRange($cityID))
			   ->where('p.temporary = 0');
			   
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