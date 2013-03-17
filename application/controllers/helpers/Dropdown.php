<?php

class Application_Controller_Helper_Dropdown extends Zend_Controller_Action_Helper_Abstract
{
	
	public function dropdown($id, $options, $selected = false)
	{
		if (!$selected) {
			if (is_array($options[0])) {
				// Option is given as array with sub-values
				$selected = $options[0]['text'];
			} else {
				$selected = $options[0];
			}
		}
		
		$output  = "<div id = '" . $id . "-holder' class='dropdown-menu-holder'></div>";
		$output .= "<div id='" . $id . "' class='dropdown-menu-container'>
					<div  class='dropdown-menu-selected dropshadow'>
				   <p class='dropdown-menu-option-text medium'>" . $selected . "</p>
				   <img src='/images/global/dropdown/dropdown_arrow_medium.png' class='dropdown-menu-option-img' id='dropdown-menu-arrow'/>
				   </div>";
		
		$output .= "<div class='dropdown-menu-hidden-container'>
				    <img src='/images/global/dropdown/dropdown_tip.png' class='dropdown-menu-tip' />
				    <div dropdown-menu='" . $id . "' id='dropdown-menu-" . $id . "' class='dropdown-menu-options-container dropshadow'>";
					
		foreach ($options as $option) {
			$output   .= "<div class='dropdown-menu-option-container medium'>";
			$img       = '';
			$textClass = 'medium';
			
			if (is_array($option)) {
				// Option is given as array with sub-values
				
				if (isset($option['color'])) {
					$textClass = $option['color'];
				}
				if (isset($option['image'])) {
					// Image to be shown
					$img = "<img src='" . $option['image'] . "' class='dropdown-menu-option-img " . $textClass . "-background' />";
				}
				
				
				$text = '<p class="dropdown-menu-option-text ' . $textClass . '">' . ucwords($option['text']) . '</p>';
			} else {
				// Simple text
				$text      = '<p class="dropdown-menu-option-text ' . $textClass . '">' . ucwords($option) . '</p>';
			}
			$output .= $text . $img;
			$output .= "</div>";
		}
		
		$output .= "</div></div></div>";
					
		return $output;
	}
}
