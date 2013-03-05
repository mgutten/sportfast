<?php

class Application_Model_SportsMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Sports';
	
	
	public function getAllSportsInfo()
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
		       ->where('usp.userID = ?', $userID);
			   
		$results = $table->fetchAll($select);
		
		foreach ($results as $result) {
			
		}
			
		return $modelClass;

	}
	
	
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
	
}