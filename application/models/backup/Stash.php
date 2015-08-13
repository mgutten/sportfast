<?php

class Application_Model_Stash extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_ParksMapper';
	protected $_attribs     = array('parkStashID' 		=> '',
								    'parkID' 			=> '',
								    'items' 			=> '',
									'sports'			=> '',
									'location'			=> ''
									);

	protected $_primaryKey = 'parkStashID';	
	
	
	public function __construct($resultRow = false) {
		if ($resultRow) {
			$this->setAttribs($resultRow);
			$this->addSport($resultRow->sport);
			$this->addItem($resultRow);
		} 
	}
	
	public function addSport($sport)
	{
		$sport = strtolower($sport);
		
		if (!$this->hasValue('sports')) {
			$this->_attribs['sports'] = array();
		}
		
		$model = new Application_Model_Sport();
		$model->sport = $sport;
		
		$this->_attribs['sports'][$sport] = $model;
		/*
		if (empty($this->_attribs['sports'][$sport])) {
			// Already set
			$this->_attribs['sports'][$sport] = ucwords($sport);
		}
		*/
		
		return $this;
	}
	
	public function addItem($resultRow)
	{
		if (!is_array($resultRow)) {
			$resultRow = $resultRow->toArray();
		}
		
		if (!is_array($this->_attribs['items'])) {
			$this->_attribs['items'] = array();
		}
		
		if ($item = $this->itemExists($resultRow['itemID'])) {
			return $item;
		} else {
			$item = $this->_attribs['items'][$resultRow['itemID']] = new Application_Model_Item($resultRow);
		}
		
		return $item;
					
	}
	
	public function itemExists($itemID)
	{
		if ($this->hasValue('items')) {
			foreach ($this->_attribs['items'] as $id => $obj) {
				if ($id == $itemID) {
					return $obj;
				}
			}
		}
		
		return false;
	}
	
	public function getLocation()
	{
		if (empty($this->_attribs['userLocation'])) {
			
			$this->_attribs['userLocation'] = new Application_Model_Location();
		}
		return $this->_attribs['userLocation'];
	}
	
}