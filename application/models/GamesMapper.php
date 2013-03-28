<?php

class Application_Model_GamesMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Games';
	
	/**
	 * Get all games that are happening during user's availability
	 * @params($userClass   => user object,
	 *		   $savingClass => games object,
	 *		   $options		=> additional sql "where" constraints)
	 */
	
	public function findUserGames($userClass, $savingClass, $options = false, $points = false)
	{
		$table    = $this->getDbTable();
		$select   = $table->select();
		$userID   = $userClass->userID;
		$cityID   = $userClass->getCity()->cityID;
		
		if ($points) {
			// Map was moved or points have been set to not be around user location
			$upperPoint = $points[0];
			$lowerPoint = $points[1];
		} else {
			// Default location to search near is user's home location, look for games within $distance of user's home location
			$distance = 10; // in miles 
			$rad	  = $distance/69; // (1 degree about = 69 mi) could incorporate haversine formula later for more accurate distance calculation
			$upperPoint = 'POINT(' . ($userClass->location->latitude + $rad) . ',' . ($userClass->location->longitude + $rad) . ')';
			$lowerPoint = 'POINT(' . ($userClass->location->latitude - $rad) . ',' . ($userClass->location->longitude - $rad) . ')';
		}
		
		
		$select->setIntegrityCheck(false);
		$select->from(array('g'  => 'games'))
			   ->join(array('t' => 'sport_types'),
			   		  't.typeID = g.typeID')
			   ->join(array('usa' => 'user_sport_availabilities'),
			   		  't.sportID = usa.sportID')
			   ->join(array('pl' => 'park_locations'),
			   		  'pl.parkID = g.parkID',
					  array('AsText(location) as location'))
			   ->joinLeft(array('ug' => 'user_games'),
			   		 'ug.gameID = g.gameID',
					 array(''))
			   ->joinLeft(array('us' => 'user_sports'),
			   		 'ug.userID = us.userID AND us.sportID = t.sportID',
					 array('avg(us.skillCurrent) as averageSkill',
					 	   'avg(us.attendance) as averageAttendance',
						   'avg(us.sportsmanship) as averageSportsmanship',
						   'avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID) as skillDifference',
						   'COUNT(us.userID) as totalPlayers'
						   ))
			   //->where('g.cityID = ?', $cityID)
			   ->where('usa.userID = ?', $userID)
			   ->where('DATE_FORMAT(g.date,"%w") = usa.day')
			   ->where('HOUR(g.date) = usa.hour')
			   ->where('g.public = "1"')
			   ->where('MBRContains(
								LINESTRING(
								' . $upperPoint . ' , ' . $lowerPoint . '
								), pl.location
								)')
			   ->where('g.date > NOW()');
								
		
		if ($options) {
			// Additional options are set
			foreach ($options as $option) {
				$select->where($option);
			}
		}
		
		$select->group('g.gameID')
			   ->order('abs(avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID)) ASC');
	
		$results = $table->fetchAll($select);

		foreach ($results as $result) {
			$savingClass->addGame($result);
		}
		
		return $savingClass;
	}
	
}
