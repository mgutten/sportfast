<?php

class Application_Model_Ratings extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_RatingsMapper';
	
	protected $_attribs     = array('ratings' => '',
									'bestSkills' => '');
	
	
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
	
	public function countRatings($ucRating = true)
	{
		if (!$this->hasValue('ratings')) {
			return '0 ratings';
		}
		
		$count = count($this->_attribs['ratings']);
		
		if ($count == 1) {
			$rating = 'rating';
		} else {
			$rating = 'ratings';
		}
		
		if ($ucRating) {
			$rating = ucwords($rating);
		}
		
		return $count . ' ' . $rating;
	}
	
	public function getAverage($attrib)
	{
		$total   = 0;
		$ratings = $this->getAll();
		$count   = count($ratings);
		$value   = $attrib;
		
		if ($count == 0) {
			// No ratings
			return false;
		}
		
		foreach ($ratings as $rating) {
			if ($rating->isUser()) {
				// Is a user rating
				$value = $attrib . 'Value';
			}
			$total += $rating->$value;
		}
		
		$average = $total/$count;
		
		if (!$rating->isUser()) {
			// Is park
			$average = round(($average) / .5) * .5; // Round to nearest half
		} else {
			$average = floor($average);
		}
		
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
			if (!isset($returnArray[$rating->skiller])) {
				$returnArray[$rating->skiller] = 0;
			}
			
			$returnArray[$rating->skiller] += 1;
		}
		
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
