<?php

class Application_Controller_Helper_CreateGames extends Zend_Controller_Action_Helper_Abstract
{
	public $master;
	public $usedPlayers = array();
	public $cronMapper;
	public $games = array();
	
    /**
	 * automatically create games for users
	 */
	public function creategames()
	{

		$parks = $this->getMapper()->getParksCreateGames();
		
		$this->master = $this->convertParksToArray($parks);
		
		$this->loopMaster();
		
		return $this->games;
	}
	
	
	/**
	 * loop master array of sports, timeslots, and available parks and determine where to create game
	 */
	public function loopMaster()
	{
		$master = $this->master;
		
		while ($combo = $this->pickCombo()) {

			if ($combo['sport'] == 'tennis') {
				// Must test for types with tennis (eg singles rally, doubles match etc)
				$tennisTypes = array('singles' => array('rally',
														'match'),
									 'doubles' => array('rally',
									 					'match'));
														
				foreach ($tennisTypes as $typeName => $inner) {
					
					foreach ($inner as $typeSuffix) {
						$type = array('typeName' => $typeName,
									  'typeSuffix' => $typeSuffix);
								  
						$this->testParkGame($combo, $type);
					}
				}
			} else {
				// Just one test for pickup game params (eg basketball, football, etc)
				$this->testParkGame($combo);
			}
			
		}

		
	}
	
	/**
	 * perform test of park for a game and deal with results of test
	 * @params ($combo => array of random combination returned from pickCombo()
	 *			$type => optional array of typeName => and typeSuffix =>)
	 */
	public function testParkGame($combo, $type = false) 
	{

		$returnArray = $this->getMapper()->testParkGame($combo['park'], $combo['sport'], $combo['timeslot'], $this->usedPlayers, 3, $type);
			
		if ($returnArray) {

			$this->usedPlayers = $returnArray['usedPlayers'];
				
			/*
			foreach ($returnArray['usedParks'] as $parkID) {
				$this->removePark($parkID, $combo['timeslot']);
			}
			*/
			
			if (isset($returnArray['game'])) {
				$this->games[] = $returnArray['game'];
			}
		}
			
		$this->removePark($combo['park'], $combo['timeslot']);
	}
	
	/**
	 * remove park from master array after used
	 */
	public function removePark($park, $timeslot)
	{
		$sports = array_keys($this->master);
		for ($i = 0; $i < count($this->master); $i++) {
			
			unset($this->master[$sports[$i]][$timeslot][$park]);
			/*
			var_dump($timeslot);
			var_dump($this->master[$sports[$i]]);
			if (!isset($this->master[$sports[$i]][$timeslot])) {
				//var_dump ($this->master[$sports[$i]]);
			}
			if (!$this->master[$sports[$i]][$timeslot]) {
				// Timeslot is now empty
				$this->unsetTimeslot($sports[$i], $timeslot);
			}
			*/
			if (!$this->master[$sports[$i]]) {
				// Sport is now empty
				$this->unsetSport($sports[$i]);
			}
			
		}
	}
		
	
	/**
	 * pick random combination of sport, timeslot, and park from master
	 */
	public function pickCombo()
	{
		$master = $this->master;

		$sportKeys = array_keys($master);

		if (!$sportKeys) {
			// No sports left, return false
			return false;
		}
		$sport = $sportKeys[mt_rand(0, count($sportKeys) - 1)];
		
		$timeslotKeys = array_keys($master[$sport]);
		if (!$timeslotKeys) {
			$this->unsetSport($sport);
			return $this->pickCombo();
		}
		$timeslot = $timeslotKeys[mt_rand(0, count($timeslotKeys) - 1)];
		
		$parkKeys = array_keys($master[$sport][$timeslot]);
		
		if (!$parkKeys) {
			// Empty timeslot
			$this->unsetTimeslot($sport, $timeslot);
			return $this->pickCombo();
		}
		$park = $parkKeys[mt_rand(0, count($parkKeys) - 1)];
		
		//var_dump($timeslot);
		return array('sport' => $sport,
					 'timeslot' => $timeslot,
					 'park'  => $park);
	}
	
	/**
	 * unset given timeslot
	 */
	public function unsetTimeslot($sport, $timeslot)
	{
		
		unset($this->master[$sport][$timeslot]);
		
	}
	
	/**
	 * unset given sport
	 */
	public function unsetSport($sport)
	{
		unset($this->master[$sport]);
	}
	
	/**
	 * convert mysql returned list to master array
	 */
	public function convertParksToArray($parks)
	{
		$unavailabilities = $this->getMapper()->getParkUnavailabilities();
		
		$sportsModel = new Application_Model_Sports();
		$sports = $sportsModel->getAllSportsInfo();
		
		$sports = array_keys($sports);
		
		// These sports require a field
		$fieldSports = array('football',
							 'soccer',
							 'volleyball',
							 'ultimate');
		
		$master = array();
		$startTime = 8; // 8am start time
		$endTime = 21; // 9pm end time
		foreach ($sports as $sport) {
			for ($i = $startTime; $i <= $endTime; $i++) {
				// Create basic master array of sports => timeslots
				$master[$sport][$i] = array();
			}
		}
		
		foreach ($parks as $park) {
			
			// First determine what sports can be played at this park
			$parkSports = array();
			
			if ($park['basketball'] > 0 && $park['basketballPrivate'] == 0) {
				// Basketball is available
				$parkSports['basketball'] = true;
			}
			
			if ($park['field'] > 0 && $park['sport'] && $park['fieldPrivate'] == 0) {
				// Field present and stash available, could play football, ultimate, soccer, or volleyball
				$parkSports[$park['sport']] = true;
			}
			
			if ($park['tennis'] > 0 && $park['tennisPrivate'] == 0) {
				$parkSports['tennis'] = true;
			}
			
			if ($park['volleyball'] > 0 && $park['volleyballPrivate'] == 0) {
				$parkSports['volleyball'] = true;
			}
			
			// Next determine when the park is available
			$location = $this->parseLocation($park['location']);
			$sunset = $this->getSunset($location['latitude'], $location['longitude']);
			
			if ($park['fieldLights'] == 0 && $park['closeTime'] > $sunset) {
				// Field has lights and the park closes after sunset
				$fieldCloseTime = floor($sunset - 1);
			} else {
				$fieldCloseTime = floor($park['closeTime'] - 1);
			}
			
			if ($park['basketballLights'] == 0 && $park['closeTime'] > $sunset) {
				$basketballCloseTime = floor($sunset - 1);
			} else {
				$basketballCloseTime = floor($park['closeTime'] - 1);
			}
			
			if ($park['tennisLights'] == 0 && $park['closeTime'] > $sunset) {
				$tennisCloseTime = floor($sunset - 1);
			} else {
				$tennisCloseTime = floor($park['closeTime'] - 1);
			}
			
			if ($park['openTime'] < $startTime) {
				// Park opens before our start time (8am)
				$openTime = $startTime;
			} else {
				$openTime = $park['openTime'];
			}
			
			foreach ($sports as $sport) {
				if (in_array($sport, $fieldSports)) {
					$reference = 'fieldCloseTime';
				} else {
					$reference = $sport . 'CloseTime';
				}
				
				if (!isset($parkSports[$sport])) {
					// Check above showed that park does not support this sport
					continue;
				}
				
				for ($i = $openTime; $i <= $$reference; $i++) {
					
					if (isset($unavailabilities[$park['parkID']][date('w', strtotime('+3 days'))][$i])) {
						// Unavailability is set
						continue;
					}
					
					if ($park['parkType'] == 'school' &&
						($i >= 8 && $i <= 16) &&
						(date('n') < 6 || date('n') > 8 || (date('n') == 8 && date('j') > 19)) &&
						(date('w') != 6 && date('w') != 0)) {
							// Park is school and during school hours and in school season and weekday
							continue;
					}
						
					$master[$sport][$i][$park['parkID']] = true;
				}
			}
		
		}

		
		return $master;
		
	}
	
	/**
	 * get time when sun will set at given lat/lng combo
	 * @returns float of time in 24-hour format
	 */
	public function getSunset($latitude, $longitude)
	{
	
		$sunset = date_sunset(time(), SUNFUNCS_RET_DOUBLE, $latitude, $longitude, ini_get("date.sunset_zenith"), -7);
		
		return $sunset;
	}
	
	/*
	 * parse POINT datatype (e.g. POINT(latitude longitude)) to array of latitude => , longitude => 
	 */
	public function parseLocation($location)
	{
		
		$location = explode(' ', $location);
		
		$latitude = ltrim($location[0], 'POINT(');
		$longitude = rtrim($location[1], ')');
		
		return array('latitude'  => $latitude,
					 'longitude' => $longitude);
	}
	
	public function getMapper()
	{
		if (!$this->cronMapper) {
			$this->cronMapper = new Application_Model_CronMapper();
		}
		
		return $this->cronMapper;
	}	

}