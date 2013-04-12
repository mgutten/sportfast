<?php

class Application_Model_SearchMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Messages';
	
	/**
	 * Search database for any user, group, team, park, or league with name similar to searchTerm
	 * @params($searchTerm  => term to search for
	 * 		   $savingClass => where to save the results)
	 */
	public function getSearchResults($searchTerm, $cityID, $savingClass)
	{
		$db 	= Zend_Db_Table::getDefaultAdapter();
		
		$users  = "SELECT userID as id,CONCAT(firstName, ' ', lastName) as name, 'users' as prefix, cityID FROM users 
						WHERE (firstName LIKE '" . $searchTerm . "%' 
							OR CONCAT(firstName, ' ',lastName) LIKE '" . $searchTerm . "%'
							OR lastName LIKE '" . $searchTerm . "%') AND active = '1'";
							   
		$teams  = "SELECT teamID as id,teamName as name, 'teams' as prefix, cityID FROM teams 
						WHERE teamName LIKE '" . $searchTerm . "%' OR
								teamName LIKE 'The " . $searchTerm . "%'";
					
		/*$groups = "SELECT groupID as id,groupName as name, 'groups' as prefix, cityID FROM groups 
						WHERE groupName LIKE '" . $searchTerm . "%'";*/
					 
		$parks  = "SELECT parkID as id,parkName as name, 'parks' as prefix, cityID FROM parks 
						WHERE parkName LIKE '" . $searchTerm . "%'";
					 
		/*$leagues  = "SELECT leagueID as id,leagueName as name, 'leagues' as prefix, cityID FROM leagues 
						WHERE leagueName LIKE '" . $searchTerm . "%'";
					 
					 		
		$select = $users   . ' UNION ' 
				. $teams   . ' UNION '  
				. $groups  . ' UNION ' 
				. $parks   . ' UNION ' 
				. $leagues . ' ORDER BY ABS(' . $cityID . ' - cityID)';*/
				
		$select = $users   . ' UNION ' 
				. $teams   . ' UNION '  
				//. $groups  . ' UNION ' 
				. $parks   . ' ORDER BY ABS(' . $cityID . ' - cityID)';

		$results = $db->fetchAll($select); // returned result is array, not object
		
		for ($i = 0; $i < count($results); $i++) {
			// Capitalize name column
			$results[$i]['name'] = ucwords($results[$i]['name']);
		}
		
		return $results;
	}
	
	/**
	 * search db for league location by name and/or address
	 */
	public function getLeagueLocationResults($locationName, $address, $cityID, $savingClass)
	{
		$db 	= Zend_Db_Table::getDefaultAdapter();
		
		$cityIDRange = $this->getCityIdRange($cityID);
				   
		$leagueLocations  = "SELECT leagueLocationID as id,locationName as name, streetAddress as address, cityID FROM league_locations 
								WHERE temporary = '0' AND cityID IN " . $cityIDRange . " AND (";
		
		$success = false;			
		if (!empty($locationName)) {
			$leagueLocations .= "locationName LIKE '" . $locationName . "%' ";
			$success = true;
		}
		
		if (!empty($address)) {
			if ($success) {
				$leagueLocations .= " OR ";
			}
			$leagueLocations .= " streetAddress LIKE '" . $address . "%'";
		}
		
		$leagueLocations .= ') ';
					 		
		$select = $leagueLocations . ' ORDER BY ABS(' . $cityID . ' - cityID) LIMIT 4';
		

		$results = $db->fetchAll($select); // returned result is array, not object
		
		
		return $results;
	}
}
	
