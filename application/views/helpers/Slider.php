<?php

class Application_View_Helper_Slider
{
	
	public $_view;
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function slider()
	{
		return $this;
	}
	
	public function create($options)
	{
		$id         = $options['id'];
		$output     = "<div class='slider-container' id='slider-container-"	. $id . "'>";
		
		$valueClass = 'auto-center center medium slider-text-value';
		if (!empty($options['valueClass'])) {
			// Append class to value's class
			$valueClass .= ' ' . $options['valueClass'];
		}
		$valueEle   = "<div class='clear width-100'><p class='" . $valueClass . "'></p></div>";
		
		$sliderClass = 'signup-skill-slider ';
		if (!empty($options['sliderClass'])) {
			// Add class to slider container
			$sliderClass .= $options['sliderClass'];
		}
		
		if (!empty($options['type'])) {
			// Type (ie skill, sportsmanship) used in js to determine value/description var
			$sliderClass .= ' slider-' . $options['type'];
		}
			
		$slider     = "<div class='" . $sliderClass . "' id='slider-" . $id . "'></div>";
		
		switch ($options['valuePosition']) {			
			case 'below':
				$output .= $slider . $valueEle;
				break;
			default: 
				$output .= $valueEle . $slider;
				break;
		}
		
		if (!empty($options['desc'])) {
			// Description to be included below all values
			$descClass  = 'center medium slider-text-description width-100 clear';
			if (!empty($options['descClass'])) {
				$descClass .= ' ' . $options['descClass'];
			}
			$descEle = "<p class='" . $descClass . "'></p>";
			$output .= $descEle;  
		}
		
		$output .= '</div>';

						
		return $output;
	}
	

}
