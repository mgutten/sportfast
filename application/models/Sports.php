<?php

class Application_Model_Sports extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_SportsMapper';
	protected $_singleClass = 'Application_Model_Sport';
	
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

	public function getAllSportsInfo()
	{
		return $this->getMapper()->getAllSportsInfo();
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
