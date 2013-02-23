<?php

class Application_Controller_Helper_Dropdown extends Zend_Controller_Action_Helper_Abstract
{
	
	public function dropdown($id, $selected, $options)
	{
		$output  = "<div dropdown='" . $id . "' class='dropdown-menu-holder'></div>";
		$output .= "<div id='" . $id . "' class='dropdown-menu-container'>
					<div  class='dropdown-menu-selected dropshadow'>
				   <p class='dropdown-menu-option-text medium'>" . $selected . "</p>
				   <img src='/images/global/dropdown/dropdown_arrow_medium.png' class='dropdown-menu-option-img' id='dropdown-menu-arrow'/>
				   </div>";
		
		$output .= "<div class='dropdown-menu-hidden-container'>
				    <img src='/images/global/dropdown/dropdown_tip.png' class='dropdown-menu-tip' />
				    <div dropdown-menu='" . $id . "' id='dropdown-menu-" . $id . "' class='dropdown-menu-options-container dropshadow'>";
					
		foreach ($options as $option) {
			$output .= "<div class='dropdown-menu-option-container'>";
			$img     = '';
			$text    = $option;
			if (is_array($option)) {
				// Option is given as array with sub-values
				$text = $option['text'];
				if (isset($option['image'])) {
					$class = 'dropdown-menu-option-img';
					// Image to be shown
					$img = "<img src='" . $option['image'] . "' class='dropdown-menu-option-img medium-background' />";
				}
			} 
			$text    = "<p class='dropdown-menu-option-text medium'>" . $text . "</p>";
			$output .= $text . $img;
			$output .= "</div>";
		}
		
		$output .= "</div></div></div>";
					
		return $output;
	}
}
