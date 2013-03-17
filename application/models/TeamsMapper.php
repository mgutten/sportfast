<?php

class Application_Model_TeamsMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Teams';
	
	/**
	 * Get all teams that are happening during user's availability
	 * @params($userClass   => user object,
	 *		   $savingClass => games object,
	 *		   $options		=> additional sql "where" constraints)
	 */
	
	public function findUserTeams($userClass, $savingClass, $options = false)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$userID  = $userClass->userID;
		
		$select->setIntegrityCheck(false);
		$select->from(array('t'  => 'teams'))
			   ->join(array('l' => 'leagues'),
			   		 'l.leagueID = t.leagueID',
					 array('l.leagueName'))
			   ->join(array('uus' => 'user_sports'),
			   		 'uus.sportID = t.sportID AND uus.userID = "' . $userID . '"',
					 array('skillCurrent as userSkill'))
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
			   ->where('uus.skillCurrent >= l.minSkill AND uus.skillCurrent <= maxSkill');
		
		if ($options) {
			// Additional options are set
			foreach ($options as $option) {
				$select->where($option);
			}
		}
		
		$select->group('t.teamID');
			   //->order('abs(avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID)) ASC');
		/*
		echo $select->__toString();
		return;
		*/
		
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			$savingClass->addTeam($result);
		}
		
		return $savingClass;
	}
	
}
