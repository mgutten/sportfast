<?php

class Application_Controller_Helper_Dropdown extends Zend_Controller_Action_Helper_Abstract
{
	
	
	/**
	 * create typical dropdown
	 * @params ($id => id for dropdown
	 *			$options => array of options (could container sub arrays of text, image, and class),
	 *			$selected=> which value is selected (str),
	 *			$ucwords => upper case each option
	 *			$changeSelected => when click on option, should change selected and close dropdown?)
	 */
	public function dropdown($id, $options, $selected = false, $ucwords = true, $changeSelected = false)
	{
		if (!$selected) {
			if (is_array($options[0])) {
				// Option is given as array with sub-values
				$selected = $options[0]['text'];
			} else {
				$selected = $options[0];
			}
		}
		
		$class = $selectedContainerClass = '';
		
		if (is_array($selected)) {
			
			if (isset($selected['class'])) {
				$class = $selected['class'];
			}
			
			if (isset($selected['selectedContainerClass'])) {
				$selectedContainerClass = $selected['selectedContainerClass'];
			}
			
			$selected = $selected['text'];
		}
		
		$output = "<div id='" . $id . "' class='dropdown-menu-container' dropdown-id='dropdown-menu-hidden-container-" . $id . "'>
						<div class='dropdown-menu-selected dropshadow " . $selectedContainerClass . "'>
						   <p class='dropdown-menu-option-text medium " . $class . "'>" . $selected . "</p>
						   <img src='/images/global/dropdown/dropdown_arrow.png' class='dropdown-menu-option-img' id='dropdown-menu-arrow'/>
					   </div>
				   </div>";
		
		$output .= $this->createLowerDropdown($id, $options, $ucwords, $changeSelected);
		
		//$output .= "</div>";
					
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
		
		$output = "<div id='" . $id . "' class='dropdown-menu-container dropdown-menu-container-button' dropdown-id='dropdown-menu-hidden-container-" . $id . "'>
						<div class='dropdown-menu-selected button'>
						   <p class='dropdown-menu-option-text medium'>" . $selected . "</p>
						   <img src='/images/global/dropdown/dropdown_arrow.png' class='dropdown-menu-option-img' id='dropdown-menu-arrow-button'/>
					    </div>
				   </div>";
		
		if ($id == 'invite-to') {
			// Invite user to...
			$output .= $this->createLowerDropdownInviteTo();
		} elseif ($id == 'invite') {
			// Invite a group or user...
			$output .= $this->createLowerDropdownInvite();
		} else {
			$output .= $this->createLowerDropdown($id, $options);
		}
		
					
		return $output;
	}

	
	/**
	 * create lower (hidden) portion of custom dropdown
	 */
	public function createLowerDropdown($id, $options, $ucwords = true, $changeSelected = false)
	{
		$changeSelected = (!$changeSelected ? '' : 'true');
		
		$output  = '';
		$output .= "<div class='dropdown-menu-hidden-container' change = '" . $changeSelected . "' id='dropdown-menu-hidden-container-" . $id . "'>
				    <img src='/images/global/dropdown/dropdown_tip.png' class='dropdown-menu-tip' />
				    <div dropdown-menu='" . $id . "' id='dropdown-menu-" . $id . "' class='dropdown-menu-options-container dropshadow'>";
					
		foreach ($options as $option) {
			
			$pre = "<div";
			$post = "</div>";
			
			if (isset($option['href'])) {
				if (strlen($option['href']) > 1 || $option['href'] == '/')  {
					// Bug fix for php 5.4 where isset of sub-key will return first letter of array value (isset returns true then)
					$pre = "<a href='" . $option['href'] . "'";
					$post = "</a>";
				}
			}
			
			$outerClass = '';
			if (isset($option['outerClass'])) {
				// Give outer div a class
				$outerClass = ' ' . $option['outerClass'];
			}
			
			$output   .= $pre . "  change = '" . $changeSelected . "' class='dropdown-menu-option-container medium animate-darker " . $outerClass . "'>";
			$img       = '';
			$textClass = 'medium';
		
			
			if (is_array($option)) {
				// Option is given as array with sub-values
				
				if (isset($option['color'])) {
					$textClass = $option['color'];
				}
				if (isset($option['image'])) {
					// Image to be shown
					$background = $textClass . "-background";
					if (strpos($option['image'], '/solid/') > 0) {
						// solid png, do not give colored background
						$background = '';
					} elseif (isset($option['background'])) {
						if ($option['background'] == 'none' || !$option['background']) {
							$background = '';
						} else {
							$background = $option['background'];
						}
					}
					
					if (isset($option['imageLocation'])) {
						// Set to 'left' or 'right' of text
						$background .= ' ' . $option['imageLocation'];
					}
					
					
					$img = "<img src='" . $option['image'] . "' class='dropdown-menu-option-img " . $background . "' />";
				}
				
				$attribs = '';
				if (isset($option['attr'])) {
					foreach ($option['attr'] as $attr => $val) {
						$attribs .= " " . $attr . "='" . $val . "' ";
					}
				}
				
				if ($ucwords) {
					// ucwords for option
					$option['text'] = ucwords($option['text']);
				}

				
				$text = '<p class="dropdown-menu-option-text ' . $textClass . '" ' . $attribs . ' change="' . $changeSelected . '">' . $option['text'] . '</p>';
			} else {
				// Simple text
				if ($ucwords) {
					// ucwords for option
					$option = ucwords($option);
				}
				$text      = '<p class="dropdown-menu-option-text ' . $textClass . '" change="' . $changeSelected . '">' . $option . '</p>';
			}
			
			if (isset($option['imageLocation'])) {
				// determine which side of the text the image is ('left' or 'right')
				if (strtolower($option['imageLocation']) == 'right') {
					$output .= $text . $img;
				} else {
					$output .= $img . $text;
				}
			} else {
				// Default to image on right
				$output .= $text . $img;
			}
			$output .= $post;
		}
		
		$output .= "</div></div>";
		
		return $output;
	}
	
	/**
	 * create lower (hidden) portion of custom "invite to" dropdown (used on user profile)
	 */
	public function createLowerDropdownInviteTo()
	{
		$auth    = Zend_Auth::getInstance();
		$user    = $auth->getIdentity();
		
		$output  = '';
		$id 	 = 'invite-to';
		$output .= "<div class='dropdown-menu-hidden-container' id='dropdown-menu-hidden-container-" . $id . "'>
				    <img src='/images/global/dropdown/dropdown_tip.png' class='dropdown-menu-tip' />
				    <div dropdown-menu='" . $id . "' id='dropdown-menu-" . $id . "' class='dropdown-menu-options-container  dropdown-menu-options-container-dark invite dropshadow'>";
		
		
		$sections = array('games','teams');
		
		foreach ($sections as $section) {
			
			if ($user->$section->hasValue($section)) {
				// This section has values, display as options for invite
				$output .= "<p class='smaller-text clear dark dropdown-menu-invite-section heavy default'>My " . ucwords($section) . "</p>";	
				$post    = "</p></div>";
				
				foreach ($user->$section->getAll() as $model) {
					if (($model->rosterLimit == $model->totalPlayers) && !$model instanceof Application_Model_Group) {
						// Team/Game is full do not show, exclude Group from this as it does not have a roster limit
						continue;
					}
					
					if ($section == 'games' && $model->isPickup()) {
						// Game
						$pre 	 = "<div class='dropdown-menu-option-container animate-darker invite medium smaller-text' idType='gameID' gameID='" . $model->gameID . "'>
										<p class='dropdown-menu-option-text medium'>";
						
						$content = $model->sport . ' <span class="light">' . date('M j, ga', strtotime($model->date)) . '</span>';
					} elseif ($section == 'teams') {
						// Team
						$pre 	 = "<div class='dropdown-menu-option-container animate-darker invite medium smaller-text' idType='teamID' teamID='" . $model->teamID . "'>
										<p class='dropdown-menu-option-text medium'>";
						$content = $model->getLimitedName('teamName', 23);
					} else {
						continue;
					}/*elseif ($section == 'groups') {
						// Group
						$output .= $pre . $model->getLimitedName('groupName', 23) . $post;
					}*/
					
					$output .= $pre . $content . $post;
				}
			} else {
				$output .= "<p class='smaller-text clear light'>You have no " . $section . ".</p>";
			}
		}
		
		$output .= "</div></div>";
		
		return $output;
	}
	
	/**
	 * create lower (hidden) portion of custom "invite" dropdown (used on team profile)
	 */
	public function createLowerDropdownInvite()
	{
		$auth    = Zend_Auth::getInstance();
		$user    = $auth->getIdentity();
		$form    = new Application_Form_HeaderSearch();
		
		$output  = '';
		$id 	 = 'invite';
		$output .= "<div class='dropdown-menu-hidden-container' id='dropdown-menu-hidden-container-" . $id . "'>
				    <img src='/images/global/dropdown/dropdown_tip.png' class='dropdown-menu-tip' />
				    <div dropdown-menu='" . $id . "' id='dropdown-menu-" . $id . "' class='dropdown-menu-options-container invite dropshadow'>";
		
		
		$pre 	 = "<div class='dropdown-menu-option-container animate-darker invite medium smaller-text'>
						<p class='dropdown-menu-option-text medium'>";
		$post    = "</p></div>";
		//$sections = array('groups');
		
		$output  .= $form->headerSearchBar->setName('inviteSearchBar')->setLabel("player name...");
		
		$output  .= "<div class='dropdown-menu-option-results' id='dropdown-menu-option-results-" . $id . "'></div>";
		
		$output  .= "<div class='dropdown-menu-option-default'>";

		$output .= "</div>";
		
		$currentURL = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
		
		$output .= "<a href='" . $currentURL . "/invite' class='width-100 clear medium margin-top smaller-text center margin-bottom'>or by email</a>";
		
		$output .= "</div></div>";
		
		return $output;
	}

		
}
