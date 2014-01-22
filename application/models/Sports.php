<?php

class Application_Model_Sports extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_SportsMapper';
	
	protected $_attribs = array('sports' => '');
	
	/*
	public function save(Application_Model_DbTable_Users $guestbook)
	{
		$data = array(
			'email'		=> $guestbook->getEmail(),
			'comment'	=> $guestbook->getComment(),
			'created'	=> date('Y-m-d H:i:s'),
			);
			
		if (($id = $guestbook->getId()) === null) {
			unset($data['id']);
			$this->getDbTable()->insert($data);
		} else {
			$this->getDbTable->update($data, array('id = ?' => $id));
		}
	}
	*/

	
	public function __construct(array $options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		}
	}

	public function addSport($resultRow)
	{
		if (is_object($resultRow)) {
			$resultRow = $resultRow->toArray();
		}
		
		if (isset($this->_attribs['sports'][strtolower($resultRow['sport'])])) {
			return $this->_attribs['sports'][strtolower($resultRow['sport'])];
		}
		
		$sport = $this->_attribs['sports'][strtolower($resultRow['sport'])] = new Application_Model_Sport($resultRow);
		return $sport;
	}
	
	public function getSport($sport) 
	{
		$sport = strtolower($sport);
		if (!isset($this->_attribs['sports'][$sport])) {
			$this->_attribs['sports'][$sport] = new Application_Model_Sport();
			$this->_attribs['sports'][$sport]->sport = $sport;
		}
		return $this->_attribs['sports'][$sport];
	}
	
	/**
	 * get array of sports types used on Find controller pages where userSports is needed but user is minimal account (no sports)
	 */
	public function getSportsTypes()
	{
			
		$returnArray = array();
		
		foreach ($this->getAll() as $sport) {

			$sportName = $sport->_attribs['sport'];
			foreach ($sport->types as $type) {
				if (strtolower($type->_attribs['typeName']) == 'pickup' && strtolower($type->_attribs['typeSuffix']) == null) {
					$returnArray[$sportName] = false;
					continue;
				}
				  $innerArray = array();
				  $typeName = $type->_attribs['typeName'];
				  
				  $suffix = $type->_attribs['typeSuffix'];
				  if ($suffix == 'null') {
					  $suffix = false;
				  }
				  //$innerArray['typeSuffix'] = $suffix;
				  $returnArray[$sportName][$typeName][$suffix] = true;
			}
		}			
		
		return $returnArray;
	}
		

	/**
	 * retrieve all sports info from db
	 * @params ($asClasses => return the information as Sport models? boolean)
	 */
	public function getAllSportsInfo($asClasses = false)
	{
		return $this->getMapper()->getAllSportsInfo($this, $asClasses);
	}
	
	public function getUserSportsInfo($userID)
	{
		return $this->getMapper()->getUserSportsInfo($userID,$this);
	}
	
		
	public static function overallSort($a,$b) 
	{
		// Weight order based on skillDifference and # of players (weight skillDifference more)
		$a = $a->avgSkill;
		$b = $b->avgSkill;
		
       	if ($a == $b) {
			return 0;
		}
		
		return ($a > $b ? -1 : 1);
	}
}
