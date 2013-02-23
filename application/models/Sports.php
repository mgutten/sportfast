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
	
	public function find($id) 
	{
		$result = $this->getMapper()->find($id, new $this->_singleClass());
		return $result;
	}
	
	public function fetchAll() 
	{
		return $this->getMapper()->fetchAll($this->_singleClass);
	}
	
	public function getAllSportsInfo()
	{
		return $this->getMapper()->getAllSportsInfo();
	}
	
}
