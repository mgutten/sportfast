<?php

class Application_Model_SearchMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Messages';
	
	/**
	 * Search database for any user, group, team, park, or league with name similar to searchTerm
	 * @params($searchTerm  => term to search for,
	 *		   $limit => array of types to search for (eg users, teams, etc))
	 * 		   $savingClass => where to save the results)
	 */
	public function getSearchResults($searchTerm, $cityID, $limit = false)
	{
		
		$db 	= Zend_Db_Table::getDefaultAdapter();
		
		$users  = "SELECT userID as id,CONCAT(firstName, ' ', lastName) as name, 'users' as prefix, cityID, city, '' as picture FROM users 
						WHERE (firstName LIKE :prefix 
							OR CONCAT(firstName, ' ',lastName) LIKE :prefix 
							OR lastName LIKE :prefix) AND active = '1'";
							   
		$teams  = "SELECT teamID as id,teamName as name, 'teams' as prefix, cityID, city, picture FROM teams 
						WHERE teamName LIKE :prefix  OR
								teamName LIKE :theprefix ";
					
		/*$groups = "SELECT groupID as id,groupName as name, 'groups' as prefix, cityID FROM groups 
						WHERE groupName LIKE '" . $searchTerm . "%'";*/
					 
		$parks  = "SELECT parkID as id,parkName as name, 'parks' as prefix, cityID, city, '' as picture FROM parks 
						WHERE temporary = '0' AND parkName LIKE :prefix ";
					 
		/*$leagues  = "SELECT leagueID as id,leagueName as name, 'leagues' as prefix, cityID FROM leagues 
						WHERE leagueName LIKE '" . $searchTerm . "%'";
					 
					 		
		$select = $users   . ' UNION ' 
				. $teams   . ' UNION '  
				. $groups  . ' UNION ' 
				. $parks   . ' UNION ' 
				. $leagues . ' ORDER BY ABS(' . $cityID . ' - cityID)';*/
		
		$select = '';
		if ($limit) {
			// only choose those types that are specified
			$counter = 0;
			foreach ($limit as $limitName) {
				if ($counter != 0) {
					$select .= ' UNION ';
				}
				$select .= $$limitName;
			}
		} else {
			// Select all types
			$select .= $users   . ' UNION ' 
					. $teams   . ' UNION '  
					//. $groups  . ' UNION ' 
					. $parks;
		}
		
		$select .= ' ORDER BY ABS(' . $cityID . ' - cityID)';
		
		$prefix = $searchTerm . '%';
		$suffix = '%' . $searchTerm;
		
		$statement = $db->query($select,
							  array(':prefix' => $prefix, ':theprefix' => 'The ' . $prefix)); // returned result is array, not object
	
							  
	    $results = $statement->fetchAll();
		
		if (count($results) == 0) {
			// No results
			return false;
		}
		
		for ($i = 0; $i < count($results); $i++) {
			// Capitalize name column
			$results[$i]['name'] = ucwords($results[$i]['name']);
			$results[$i]['city'] = ucwords($results[$i]['city']);
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
		
		/* does not include temporary 
		$leagueLocations  = "SELECT leagueLocationID as id,locationName as name, streetAddress as address, cityID FROM league_locations 
								WHERE temporary = '0' AND cityID IN " . $cityIDRange . " AND (";*/
		$leagueLocations  = "SELECT leagueLocationID as id,locationName as name, streetAddress as address, cityID FROM league_locations 
								WHERE cityID IN " . $cityIDRange . " AND (";
		
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
	
