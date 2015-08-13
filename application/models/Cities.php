<?php

class Application_Model_Cities extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_CitiesMapper';
	
	protected $_attribs     = array('cities' => '');
	
	
	/**
	 * add city to array
	 * @params ($resultRow => array or result row object with necessary data)
	 */
	public function addCity($resultRow)
	{
		$city = $this->_attribs['cities'][] = new Application_Model_City($resultRow);
		return $city;
	}
	
	public function getCitiesLikeName($city, $state = false)
	{
		return $this->getMapper()->getCitiesLikeName($city, $state, $this);
	}
	
	public function getCitiesLike($zipcodeOrCity, $state = false)
	{
		return $this->getMapper()->getCitiesLike($zipcodeOrCity, $state, $this);
	}
									
}
