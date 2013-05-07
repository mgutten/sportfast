<?php

class Application_View_Helper_RatingStar
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function ratingstar($size, $width, $url = false, $outerClass = false)
	{
		$class = 'clear rating-star-container-' . $size . ' ';
		if ($outerClass) {
			$class .= $outerClass;
		}
		
		if ($url) {
			// Do not make linkable
			$pre = "<a href='" . $url . "' class='" . $class . "'>";
			$post = "</a>";
		} else {
			$pre = "<div class='" . $class . "'>";
			$post = "</div>";
		}
		
		$output = $pre .
						"<img class='clear' src='/images/global/rating/stars/" . $size . ".png'/>
						<div class='clear rating-star-back-" . $size . " green-back' style='width:" . $width . "'></div>"
				  . $post;
					
		return $output;
	}
	
	// Create clickable star
	public function clickablestar($size, $id = false)
	{
		if ($id) {
			$id = " id='" . $id . "' ";
		}
		$output  = "<div class='clear rating-star-container-" . $size . "  rating-star-clickable pointer' " . $id . ">";
		$output .= "<img class='clear' src='/images/global/rating/stars/" . $size . ".png'/>
						<div class='clear rating-star-back-" . $size . " green-back rating-star-back'></div>";
		$output .= "</div>";
						
		return $output;
	}
		
	

}
