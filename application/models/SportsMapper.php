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
	
	public function getUserBy($column, $value)
	{
		$table   = $this->getDbTable();
		$select  = $table->select();
		$select->where($column . ' = ' . '?', $value)
			   ->limit(1);
		$results = $table->fetchAll($select);
		//$result = $result->current();
		$user = $this->createUserClasses($results);
			
		return $user;	
		//return $result;
	}
	
	public function createUserClasses($results) 
	{
		$users = array();
		
		foreach ($results as $result) {
			$user = new Application_Model_User();
			$user->setUsername($result->username)
				 ->setUserID($result->userID)
				 ->setPassword($result->password);
			
			if(count($results) <= 1) {
				$users = $user;
				break;
			}
			
			$users[] = $user;
		}
		
		return $users;
	}
	
}