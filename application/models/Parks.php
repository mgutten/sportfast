<?php

class Application_Model_Parks extends Application_Model_TypesAbstract
{
	protected $_mapperClass = 'Application_Model_ParksMapper';
	
	protected $_attribs     = array('parks' 	=> '',
									'totalRows' => '');
									
	protected $_primaryKey  = 'parkID';
	
	/**
	 * find parks for Find controller given options
	 */
	public function findParks($options, $userClass, $limit = false)
	{
		return $this->getMapper()->findParks($options, $userClass, $this, $limit);
	}
	
	public function addPark($resultRow)
	{

		$park = $this->_attribs['parks'][] = new Application_Model_Park($resultRow);
		return $park;
	}
	
	public function parkExists($id) 
	{
		foreach ($this->getAll() as $park) {
			if ($team->parkID == $id) {
				return $park;
			}
				
		}
		
		return false;
	}
							
}
