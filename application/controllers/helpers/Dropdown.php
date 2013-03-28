<?php

class Application_Controller_Helper_Dropdown extends Zend_Controller_Action_Helper_Abstract
{
	
	
	/**
	 * create typical dropdown
	 * @params ($id => id for dropdown
	 *			$options => array of options (could container sub arrays of text, image, and class),
	 *			$selected=> which value is selected (str))
	 */
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
				   <img src='/images/global/dropdown/dropdown_arrow.png' class='dropdown-menu-option-img' id='dropdown-menu-arrow'/>
				   </div>";
		
		$output .= $this->createLowerDropdown($id, $options);
		
		$output .= "</div>";
					
		return $output;
	}
	
	
	/**
	 * create button dropdown (looks like a button)
	 * @params ($id => id for dropdown
	 *			$options => array of options (could container sub arrays of text, image, and class),
	 *			$selected=> which value is selected (str))
	 */
	public function dropdownButton($id, $options = false, $selected = false)
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
		$output .= "<div id='" . $id . "' class='dropdown-menu-container dropdown-menu-container-button'>
					<div class='dropdown-menu-selected button'>
				   <p class='dropdown-menu-option-text medium'>" . $selected . "</p>
				   <img src='/images/global/dropdown/dropdown_arrow.png' class='dropdown-menu-option-img' id='dropdown-menu-arrow-button'/>
				   </div>";
		
		$output .= ($id == 'invite' ? $this->createLowerDropdownInvite() : $this->createLowerDropdown($id, $options));
		
		$output .= "</div>";
					
		return $output;
	}

	
	/**
	 * create lower (hidden) portion of custom dropdown
	 */
	public function createLowerDropdown($id, $options)
	{
		$output  = '';
		$output .= "<div class='dropdown-menu-hidden-container' id='dropdown-menu-hidden-container-" . $id . "'>
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
		
		$output .= "</div></div>";
		
		return $output;
	}
	
	/**
	 * create lower (hidden) portion of custom dropdown
	 */
	public function createLowerDropdownInvite()
	{
		$auth    = Zend_Auth::getInstance();
		$user    = $auth->getIdentity();
		
		$output  = '';
		$id 	 = 'invite';
		$output .= "<div class='dropdown-menu-hidden-container' id='dropdown-menu-hidden-container-" . $id . "'>
				    <img src='/images/global/dropdown/dropdown_tip.png' class='dropdown-menu-tip' />
				    <div dropdown-menu='" . $id . "' id='dropdown-menu-" . $id . "' class='dropdown-menu-options-container  dropdown-menu-options-container-dark invite dropshadow'>";
		
		
		$pre 	 = "<div class='dropdown-menu-option-container invite lightest smaller-text'>";
		$post    = "</div>";
		$sections = array('games','teams','groups');
		
		foreach ($sections as $section) {
			if ($user->hasValue($section)) {
				// This section has values, display as options for invite
				$output .= "<p class='smaller-text clear white dropdown-menu-invite-section heavy'>My " . ucwords($section) . "</p>";

				foreach ($user->$section->getAll() as $model) {
					if (($model->rosterLimit == $model->totalPlayers) && !$model instanceof Application_Model_Group) {
						// Team/Game is full do not show, exclude Group from this as it does not have a roster limit
						continue;
					}
					if ($section == 'games') {
						// Game
						$output .= $pre . $model->sport . ' <span class="light">' . date('M j, ga', strtotime($model->date)) . '</span>' . $post;
					} elseif ($section == 'teams') {
						// Team
						$output .= $pre . $model->getLimitedName('teamName', 23) . $post;
					} elseif ($section == 'groups') {
						// Group
						$output .= $pre . $model->getLimitedName('groupName', 23) . $post;
					}
				}
			}
		}
		
		$output .= "</div></div>";
		
		return $output;
	}
		
}
