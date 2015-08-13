<?php

class Application_View_Helper_RatingBar
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	/**
	 * create full rating bar (with labels for bad, good, decent etc)
	 * @params ($chosen => name of rating that was chosen (eg "good"),
	 *			$ratings => array of potential ratings in order of worst to best
	 */
	public function ratingbar($chosen, $ratings, $title = false, $outerClass = false)
	{
		$output  = "<p class='clear green larger-margin-top smaller-text'>" . $title . "</p>";
		$output .= "<div class='clear rating-outer " . $outerClass . "'>";
		
		$found = false;
		$width = 100/count($ratings);
		foreach ($ratings as $rating) {
			$class = ' green-back';
			$textClass = 'hidden-opacity';
			if ($found) {		
				$class = '';
			}
			
			if (strtolower($chosen) == strtolower($rating['ratingName'])) {
				$found = true;
				$textClass = 'white';
			}
			
			if (!$found) {
				$textClass = 'green';
			}
			
			$output .= "<div class='left rating-bar-text-container " . $class . "' style='width:" . $width . "%'>";
			$output .=		"<p class='" . $textClass . " center left rating-bar-text'>" . $rating['ratingName'] . "</p>";
			$output .=		"<div class='left rating-bar-separator'></div>";
			$output .= "</div>";
			
			
		}
		
		$output .= "</div>";	
		
		return $output;
	}
}