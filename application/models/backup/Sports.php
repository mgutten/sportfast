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
		}
		return $this->_attribs['sports'][$sport];
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
		$a = $a->overall;
		$b = $b->overall;
		
       	if ($a == $b) {
			return 0;
		}
		
		return ($a > $b ? -1 : 1);
	}
}
