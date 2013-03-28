<?php

class Application_Model_Search extends Application_Model_ModelAbstract
{
	protected $_mapperClass = 'Application_Model_SearchMapper';
	
	protected $_attribs     = array('results' => '');
	
	/**
	 * search db for searchTerm
	 * @params ($searchTerm => term to search for
	 * @return (array of results)
	 */
	public function getSearchResults($searchTerm, $cityID)
	{
		return $this->getMapper()->getSearchResults($searchTerm, $cityID, $this);
	}
									
}
