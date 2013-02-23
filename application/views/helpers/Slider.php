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
		
		$valueClass = 'center medium slider-text-value';
		if (!empty($options['valueClass'])) {
			// Append class to value's class
			$valueClass .= ' ' . $options['valueClass'];
		}
		$valueEle   = "<p class='" . $valueClass . "'></p>";
				
		$slider     = "<div class='signup-skill-slider' id='slider-" . $id . "'></div>";
		
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
			$descClass  = 'center medium slider-text-description';
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
