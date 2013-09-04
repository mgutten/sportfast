<?php

class Application_Model_Ratings extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_RatingsMapper';
	
	protected $_attribs     = array('ratings' => '',
									'bestSkills' => '',
									'numRatings' => '',
									'avgSkill'	 => '',
									'avgSportsmanship' => '',
									'avgAttendance'    => '',
									'avgQuality'		=> '',
									'skillInitial'		=> '',
									'sportsmanship'		=> '',
									'attendance'		=> '',
									'skillRatings'	=> array('beginner',
															 'decent',
															 'good',
															 'better',
															 'talented',
															 'unstoppable')
									);
	
	
	
	/**
	 * get ratings and descriptions from db
	 * @returns array of ratings and corresponding descriptions
	 */
	public function getAvailableRatings($type, $ratingType)
	{
		return $this->getMapper()->getAvailableRatings($type, $ratingType);
	}
	
	/**
	 * get user ratings for use with chart on ratings page
	 * @params ($rating => type of rating to retrieve values for ('overall', 'skill', etc),
	 *			$interval => # of months back to retrieve information (e.g. get 4 months of data separated by month)
	 * @returns array of month => value
	 */
	public function getUserRatingsForChart($userID, $sportID, $rating, $interval = 4)
	{
		$chartRatings = $this->getMapper()->UserRatingsForChart($userID, $sportID, $rating, $interval);
		
		
	}
	
	public function getRatingsForChart($rating, $interval = 4)
	{
		$returnArray = array();
		/*
		$nullCount = 0;
		for ($i = ($interval - 1); $i >= 0; $i--) {
			
			if ($rating == 'overall') {
				$skill = $this->getAverage('skill', $this->skillInitial, $i);
				$sportsmanship = $this->getAverage('sportsmanship', false, $i);
				$attendance = 100;
				
				if (!$skill) {
					// No ratings found
					$nullCount++;
					$return = 0;
				} else {
					$return = $this->getOverall($sportsmanship, $attendance, $skill);
				}
				
				if ($i == 0 && $nullCount == $interval) {
					// No ratings found at all, return overall with skill
					$skill = $this->skillInitial;
					$return = $this->getOverall($this->sportsmanship, $this->attendance, $skill);
					
				}
			} else {
				// Skill, sportsmanship
				if ($rating == 'skill') {
					$additional = $this->skillInitial;
				} else {
					$additional = false;
				}
				$average = $this->getAverage($rating, $additional, $i);
				
				if (!$average) {
					// No ratings, return preset value
					$return = 0;
					$nullCount++;
				}
				
				if ($i == 0 && $nullCount == $interval) {
					// No ratings found at all, return
					$return = ($rating == 'skill' ? $this->skillInitial : $this->$rating);
				} else {
					$return = $average;
				}
			}
			*/
		$combo = $rating . 'Value';
			
		foreach ($this->getAll() as $ratingModel) {
				
				if ($rating == 'overall') {
					$skill = $ratingModel->skillValue;
					$sportsmanship = $ratingModel->sportsmanshipValue;
					$return = $this->getOverall($sportsmanship, 100, $skill);
					
				} else {
					$return = $ratingModel->$combo;
					
				}
				
				
				
				$returnArray[] = array('year' =>  $ratingModel->date->format('Y'),
									   'day' => $ratingModel->date->format('j'),
									   'month' => ($ratingModel->date->format('m') - 1), 
									   'value' => $return);

		}
		
		return $returnArray;
	}

	
	/**
	 * calculate overall rating for user
	 */
	public function getOverall($sportsmanship, $attendance, $skillCurrent)
	{
		
		$weight = array('sportsmanship' => .1,
						'attendance'	=> .05,
						'skillCurrent'	=> .85);
		
		$sportsmanship = $weight['sportsmanship'] * $sportsmanship;
		$attendance    = $weight['attendance'] * $attendance;
		$skill		   = $weight['skillCurrent'] * $skillCurrent;
		
		$overall = $sportsmanship + $attendance + $skill;
		
		return ceil($overall);
	}
	
	public function addRating($resultRow)
	{

		$rating = $this->_attribs['ratings'][] = new Application_Model_Rating($resultRow);
		return $rating;
	}
	
	public function getRandomRating($withText = true)
	{
		$count = count($this->_attribs['ratings']);
		if (!$withText) {
			$index = mt_rand(0,$count);
			return $this->_attribs['ratings'][$index];
		}
		
		$temp = $this->getAll();
		shuffle($temp);
		
		foreach ($temp as $rating) {
			if ($rating->hasValue('comment')) {
				return $rating;
			}
		}
		
		return false;	
		
	}
	
	public function getAvgType($type, $additional = false)
	{
		if ($this->hasValue('avg' . ucwords($type))) {
			return $this->_attribs['avg' . ucwords($type)];
		} else {
			return $this->getAverage($type, $additional);
		}
	}
	
	public function countRatings($ucRating = false, $withRating = true)
	{
		if (!$this->hasValue('ratings') && empty($this->numRatings)) {
			if ($withRating) {
				return '0 ratings';
			} else {
				return '0';
			}
		} elseif ($this->numRatings == '0') {
			if ($withRating) {
				return '0 ratings';
			} else {
				return '0';
			}
		}
		
		$count = count($this->_attribs['ratings']);
		
		if ($this->hasValue('numRatings')) {
			// numRatings was retrieved from db 
			$count = $this->numRatings;
		}
		
		if ($withRating) {
			if ($count == 1) {
				$rating = 'rating';
			} else {
				$rating = 'ratings';
			}
			
			if ($ucRating) {
				$rating = ucwords($rating);
			}
			return $count . ' ' . $rating;
		} else {
			return $count;
		}
		
	}
	
	/**
	 * calculate average of attrib
	 * @params ($additional => add some ammount to the total, (skillInitial?)
	 */
	public function getAverage($attrib, $additional = false, $month = false)
	{
	
		$total   = 0;
		$ratings = $this->getAll();
		$count   = count($ratings);
		$value   = $attrib;

		if (!$ratings) {
			// No ratings
			return false;
		}

		$user = false;
		$count = 0;
		foreach ($ratings as $rating) {
		
			if ($rating->isUser()) {
				// Is a user rating
				$user = true;
				$value = $attrib . 'Value';
			}
			
			if ($month) {
				// Selected ratings from certain month
							
				$date = new DateTime();
				$sub = new DateInterval('P' . $month . 'M');
				$date->sub($sub);
				
				$ratingDate = $rating->date;
				
				$interval = $date->diff($ratingDate);
				if ($interval->format('%R') == '+' && $interval->format('%m') != 0) {
					// Rating happened after (in time) the cutoff month
		
					continue;
				}
			}
				
			$total += $rating->$value;
			$count++;
		}
		
		if ($month && $count == 0) {
			// No ratings selected for this month
			return false;
		}
		
		if ($additional) {
			$total += $additional;
			$count++;
		}
		
		$average = $total/$count;
		

		if (!$user) {
			// Is park
			$average = round(($average) / .5) * .5; // Round to nearest half
		} else {
			$average = floor($average);
		}
		
		$combo = 'avg' . ucwords($attrib);
		$this->$combo = $average; // Set average so do not need to run more than once
		
		return $average;
	}
	
	/**
	 * get best skill from ratings array
	 * @params ($rank => index of value to retrieve (starts at 0))
	 * @returns str of which skill is best (eg shooter)
	 */
	public function getBestSkill($rank)
	{
		
		if ($this->hasValue('bestSkills')) {
			// Skills have been sorted already
			$skills = $this->bestSkills;
		} else {
			// Skills need to be sorted
			$skills = $this->getBestSkills();
			$values = array_values($skills);
			$keys = array_keys($skills);
	
			//first sort by values desc, then sort by keys asc
			array_multisort($values, SORT_DESC, $keys, SORT_ASC, $skills);
		}

		if (count($skills) >= ($rank + 1)) {
			// Skills exist
			$keys = array_keys($skills);
			return ucwords($keys[$rank]);	
		} else {
			return false;
		}
	}
	
	public function getBestSkills()
	{
		
		$ratings = $this->getAll();
		
		$returnArray = array();
		
		foreach ($ratings as $rating) {
			if ($rating->skiller == '') {
				continue;
			}
			
			if (!isset($returnArray[$rating->skiller])) {
				$returnArray[$rating->skiller] = 0;
			}
			
			$returnArray[$rating->skiller] += 1;
		}
		
		arsort($returnArray); // Sort array from highest to lowest
		
		return $returnArray;
	}
	
	public function getStarWidth($attrib)
	{
		$average = $this->getAverage($attrib);
		
		//$rounded = round($average/10);
		$rounded = $average * 20;
		
		return $rounded;
	}
}
