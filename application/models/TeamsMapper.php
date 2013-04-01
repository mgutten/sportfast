<?php

class Application_Model_TeamsMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Teams';
	
	/**
	 * Get all teams that meet user needs
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
			   ->join(array('ll' => 'league_levels'),
			   		 'll.leagueLevelID = t.leagueLevelID')
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
			   ->where('uus.skillCurrent >= ll.minSkill AND uus.skillCurrent <= ll.maxSkill')
			   ->where('ll.cityID = ?', $userClass->city->cityID);
		
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
	
	/**
	 * get team info from db
	 * @params ($teamID => teamID
	 *			$savingClass => team model)
	 */
	public function getTeamByID($teamID, $savingClass)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('t'  => 'teams'))
			   ->join(array('ll' => 'league_levels'),
			   		 'll.leagueLevelID = t.leagueLevelID')
			   ->joinLeft(array('ut' => 'user_teams'),
			   		 'ut.teamID = t.teamID',
					 array(''))
			   ->joinLeft(array('us' => 'user_sports'),
			   		 'ut.userID = us.userID AND us.sportID = t.sportID',
					 array('avg(us.skillCurrent) as averageSkill',
					 	   'avg(us.attendance) as averageAttendance',
						   'avg(us.sportsmanship) as averageSportsmanship',
						   'COUNT(us.userID) as totalPlayers'
						   ))
				->where('t.teamID = ?', $teamID)
				->limit(1);
		
					   
		$team = $table->fetchRow($select);

		$savingClass->setAttribs($team);
		
		// Get all players
		$sportID = $savingClass->sportID;
		$select = $table->select();
		$select->setIntegrityCheck(false);	
			
		$select->from(array('ut' => 'user_teams'))
			   ->join(array('u' => 'users'),
			   		  'ut.userID = u.userID')
			   ->join(array('us' => 'user_sports'),
			   		  'ut.userID = us.userID AND us.sportID = "' . $sportID . '"')
			   ->join(array('s' => 'sports'),
			   		  's.sportID = ' . $sportID)
			   ->where('ut.teamID = ?', $teamID);
			   
		$players = $table->fetchAll($select);

		foreach ($players as $player) {
			$savingClass->addPlayer($player)
						->getSport($player->sport)
						->setAttribs($player);
				
		}
		
		// Get all games as well as players playing/played in those games
		$select = $table->select();
		$select->setIntegrityCheck(false);	
			
		$select->from(array('tg' => 'team_games'))
			   ->join(array('ll' => 'league_locations'),
			   		  'll.leagueLocationID = tg.leagueLocationID')
			   ->joinLeft(array('utg' => 'user_team_games'),
			   		 'tg.teamID = utg.teamID')
			   ->where('tg.teamID = ?', $teamID)
			   ->where('tg.date > DATE_SUB(NOW(), INTERVAL 3 WEEK)')
			   ->order('tg.date ASC');
		
		
		$games = $table->fetchAll($select);
		
		foreach ($games as $game) {
			$gameModel = $savingClass->addGame($game);
			
			if ($game->userID !== NULL) {
				if ($game->confirmed == true) {
					// Player is going
					$gameModel->addConfirmedPlayer($game->userID);	
				} else {
					// Player is confirmed not going
					$gameModel->addNotConfirmedPlayer($game->userID);
				}
			}
			
		}
		
		return $savingClass;

	}
			   
			

			
}
