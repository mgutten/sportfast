<?php

class Application_Model_TeamsMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Teams';
	
	/**
	 * Get all teams that meet user needs
	 * @params($userClass   => user object,
	 *		   $savingClass => teams object,
	 *		   $options		=> additional sql "where" constraints)
	 */
	
	public function findUserTeams($userClass, $savingClass, $options = false)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$userID  = $userClass->userID;
		$teamIDs = ($userClass->hasValue('teams') ? $userClass->teams->implodeIDs('teams') : '');

		
		$select->setIntegrityCheck(false);
		$select->from(array('t'  => 'teams'))
			   ->join(array('tll' => 'team_leagues'),
			   		 'tll.teamID = t.teamID')
			   ->join(array('ll' => 'league_levels'),
			   		 'll.leagueLevelID = tll.leagueLevelID')
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
		
		if (!empty($teamIDs)) {
			// Do not select teams that are already the user's
			$select->where('t.teamID NOT IN (' . $teamIDs . ')');
		}
		
		$select->group('t.teamID');
			   //->order('abs(avg(us.skillCurrent) - (SELECT skillCurrent FROM user_sports WHERE userID = "' . $userID . '" AND sportID = t.sportID)) ASC');
		
		echo $select;
		
		
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
		
		// Get all leagues
		$select  = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('tll'  => 'team_leagues'))
			   ->join(array('ll' => 'league_levels'),
			   		 'tll.leagueLevelID = ll.leagueLevelID')
			   ->join(array('l' => 'leagues'),
			   		 'l.leagueID = ll.leagueID')
				->where('tll.teamID = ?', $teamID);
		
					   
		$leagues = $table->fetchAll($select);
		
		foreach ($leagues as $league) {
			$savingClass->leagues->addLeague($league);
		}
		
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
			   		 'tg.teamGameID = utg.teamGameID',
					 array('utg.userID','utg.confirmed'))
			   ->where('tg.teamID = ?', $teamID)
			   ->where('tg.date > DATE_SUB(NOW(), INTERVAL 4 WEEK)')
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
		
		// Get team record
		$select = $table->select();
		$select->setIntegrityCheck(false);	
			
		$select->from(array('tg' => 'team_games'),
					  array('tg.winOrLoss'))
			   ->where('tg.teamID = ?', $teamID);
			   
		$results = $table->fetchAll($select);
		
		$recordArray = array('W' => array(),
							 'L' => array(),
							 'T' => array()
							 );
		
		foreach ($results as $result) {
			if ($result->winOrLoss !== NULL) {
				$recordArray[$result->winOrLoss][] = true;
			}
		}
		
		$wins = count($recordArray['W']);
		$losses = count($recordArray['L']);
		$ties = count($recordArray['T']);
		
		$savingClass->wins = $wins;
		$savingClass->losses = $losses;
		$savingClass->ties = $ties;	
		
		return $savingClass;

	}
			   
			

			
}
