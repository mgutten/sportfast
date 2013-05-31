<?php

class Application_Model_SportsMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Sports';
	
	
	public function getAllSportsInfo($savingClass, $asClasses = false)
	{
		$table   = $this->getDbTable();
		// setIntegrityCheck(false) to allow join
		$select  = $table->select()->setIntegrityCheck(false);
		$select->from(array('s' => 'sports'),
                      array('sport'))
               ->join(array('sp' => 'sport_positions'),
                    	    's.sportID = sp.sportID')
			   ->join(array('st' => 'sport_types'),
			   				's.sportID = st.sportID');
		
		$results = $table->fetchAll($select);
		
		if ($asClasses) {
			// Create sport models, not array
			foreach ($results as $result) {
				$sport = $savingClass->addSport($result);
				$sport->getPosition($result->positionName)->setAttribs($result);
				$sport->getType($result->typeID)->setAttribs($result);
			}
			
			return $savingClass;
		}
		
				
		$sports = array();
		for($i = 0; $i < count($results); $i++) {
			// Shorten current results array
			$current = $results[$i];
			
			$sport = $current['sport'];
			if (!isset($sports[$sport])) {
				$sports[$sport] = array();
			}
			
			if ($current['positionName'] !== null) {
				// This sport has positions
				$sports[$sport]['position'][$current['positionAbbreviation']] = array('name'        => $current['positionName'],
																					  'description' => $current['positionDescription']);
			}
			if ($current['typeSuffix'] !== null) {
				// This sport has different types
				$sports[$sport]['type'][$current['typeName']][$current['typeSuffix']]['description'] = $current['typeDescription'];
			}
			
			$sports[$sport]['gameRosterLimit'] = $current['gameRosterLimit'];
			$sports[$sport]['teamRosterLimit'] = $current['teamRosterLimit'];
		}
		
		return $sports;
	}
	
	/**
	 * get skills (shooter/shooting, quick/quickness, etc) from db
	 * @returns associative array of skiller and skilling
	 */
	public function getSkills($sportID)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from('sport_skills')
			   ->where('sportID = ?', $sportID);
			   
		$results = $table->fetchAll($select);
		
		$returnArray = array();
		foreach ($results as $result) {
			$returnArray[$result->sportSkillID] = array('skiller' => $result->skiller,
														'skilling' => $result->skilling);
		}
		
		return $returnArray;
	}
			   
		
	
	/* used in usersMapper
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
			   ->join(array('us' => 'user_sports'),
			   		  'sp.sportID = us.sportID',
					  array('skillCurrent', 'sportsmanship', 'attendance'))
		       ->where('usp.userID = ?', $userID);
			   
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			
		}
			
		return $modelClass;

	}
	*/
	
	
	/*
	public function createClasses($results, $modelClass) 
	{
		$classes = array();
		
		foreach ($results as $result) {
			$class = new $modelClass->_singleClass();
			
			if(count($results) <= 1) {
				$classes = $class;
				break;
			}
			
			$classes[] = $class;
		}
		
		return $classes;
	}
	*/
	
	/**
	 * get sport info (minPlayers, rosterLimit, etc) for sportID
	 * @params ($type => 'game' or 'team')
	 */
	public function getSportInfo($sportID)
	{
		$table = $this->getDbTable();		
		$select = $table->select();
		$select->setIntegrityCheck(false);
			
		
		$select->from(array('st' => 'sport_types'),
					  array('gameRosterLimit', 'teamRosterLimit', 'minPlayers'))
			   ->where('st.sportID = ?', $sportID);
		
		
		$result = $table->fetchRow($select);
		
		return $result->toArray();
	}
}