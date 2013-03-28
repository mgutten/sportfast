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
	
	public function getAverage($attrib)
	{
		$total   = 0;
		$ratings = $this->getAll();
		$count   = count($ratings);
		$value   = $attrib . 'Value';
		
		if ($count == 0) {
			// No ratings
			return false;
		}
		
		foreach ($ratings as $rating) {
			$total += $rating->$value;
		}
		
		return floor($total/$count);
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
}
