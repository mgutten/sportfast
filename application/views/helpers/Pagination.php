<?php

class Application_View_Helper_Pagination
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function pagination()
	{
		$this->_view->headLink()->prependStylesheet($this->_view->baseUrl() . '/css/pagination.css');
		$this->_view->headScript()->prependFile($this->_view->baseUrl() . '/js/pagination.js');
		return $this;
	}
	
	/**
	 * create generic pagination for results
	 * @params ($array => non-associative array of values that are to be looped,
	 *			$action => array('partial' => str location of partial file that will be run for each value of $array,
	 *							 'attrib' => str value or attrib that is passed to the partial file via second argument array),
	 *			$numPerPage => number of results per page
	 */
	public function start($array, $action, $numPerPage = 10)
	{
		$output = '';
		$partial = $action['partial'];
		$attrib = $action['attrib'];
		
		$output .= "<div class='pagination-outer-container'><div class='pagination-inner-container'>";
		
		$counter = 0;
		$pageCounter = 1;
		foreach ($array as $val) {
			if ($counter % $numPerPage == 0 &&
				$counter != 0) {
				$output .= "</div><div class='pagination-inner-container'>";
				$pageCounter++;
			}
			
			$output .= $this->_view->partial($action['partial'], array($attrib => $val));
			$counter++;
		}

		$remaining = ($counter % $numPerPage);
		
		if ($remaining != 0 &&
			$pageCounter > 1) {
				// If  more than 1 page and not an even number with regards to $numPerPage
			$remaining = $numPerPage - $remaining;
		
			for ($i = 0; $i < $remaining; $i++) {
				// Create holders to fill page
				$output .= $this->_view->partial($action['partial'], array($attrib => false));
			}
		}
		
		$output .= "</div>";
		
		if ($pageCounter > 1) {
			$output .= "<div class='pagination-pages-outer-container'>
							<div class='pagination-pages-inner-container'>";
				for ($i = 1; $i <= $pageCounter; $i++) {
					$class = ($i == 1 ? 'selected' : '');
					
					$output .= "<p class='pagination-page pointer medium smaller-text " . $class . "'>" . $i . "</p>";
				}
			$output .= "</div></div>";
		}
		
		$output .= "</div>";
						
			
		return $output;
	}
	


}