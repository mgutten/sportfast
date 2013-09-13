<?php

class Application_View_Helper_SignupSportForm
{
	
	public $_view;
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function signupsportform()
	{
		return $this;
	}
	
	/**
	 * Loop and create info for sport form (settings and signup)
	 * @params ($sports => array of sport info returned from getAllSportsInfo,
	 *			$userSports => sports array from user's model)
	 */
	public function loop($sports, $userSports = false)
	{
		$output  = '<div id="signup-sports-container" class="clear width-100">';
		$keysOuter    = array_keys($sports);
		$counterOuter = 0;
		foreach ($sports as $sport) {
			$sport['sport'] = $keysOuter[$counterOuter];
			
			$userSport = false; // reset userSport for each sport
			$remove = '';
			if ($userSports) {
				// User sports set
				$remove = "<p class='clear width-100 center red pointer settings-sport-remove' tooltip='Remove this sport from my account'>remove</p>";
				if (isset($userSports[strtolower($sport['sport'])])) {
					if ($userSports[strtolower($sport['sport'])]->sportID) {
						// Protect against getSport accidentally setting blank sport
						$userSport = $userSports[strtolower($sport['sport'])];
					}
				}
			}
			
			if ($userSport) {
				$class = 'user-sport';
			} else {
				$class = '';
			}
			$output .= "<div id='signup-sports-hidden-" . $sport['sport'] . "' class='animate-hidden-container signup-sports-hidden " . $class . "'>
						<div class='signup-sports-form' id='signup-sports-form-" . $sport['sport'] . "' sport='" . $sport['sport'] . "'>
						<p class='center header signup-sports-title darkest'>" . ucwords($sport['sport']) . "</p>
						" . $remove;
						
			
			if (($userSports && !$userSport) || (!$userSports)) {
				// Using userSports and sport is part of user already OR no userSports at all
				$output .= "<div class='signup-sports-skill signup-sports-form-section' section='skill' id='signup-sports-skill-" . $sport['sport'] . "'>
							<p class='signup-sports-form-section-title'>Skill Level</p>";
				$output .= $this->_view->slider()->create(array('id'    		=> $sport['sport'] . '-rating',
																'desc'			=> true,
																'valuePosition' => 'below',
																'valueClass'	=> 'green-bold'));	
				
				$output .= "</div>";
			}
			
			
			
			if (!empty($sport['type'])) {
				// Type section to be shown
				$output .= "<div class='signup-sports-type signup-sports-form-section' section='type' id='signup-sports-type-" . $sport['sport'] . "'>
							<p class='signup-sports-form-section-title'>Type <span class='light'>what types of games you want to play</p>";
				
				
				$userTypes = array();
				if ($userSport) {
					// Get user types for this sport
					foreach ($userSport->types as $type) {
						$userTypes[strtolower($type->typeName)][strtolower($type->typeSuffix)] = true;
					}
				}
				
				//$sportType = array();
				$keysType      = array_keys($sport['type']);
				$counterType   = 0;
				foreach($sport['type'] as $type) {
					$sportType = array();
					$curType = $keysType[$counterType];
					if ($counterType == 0) {
						$class = 'clear';
					} else {
						$class = 'left';
					}
					$output .= "<div class='darkest width-50 " . $class . " signup-type-prefix-container'>";
					$output .=		"<p class='width-100 center darkest signup-type-header'>" . ucwords($curType) . "</p>";
					
					foreach ($type as $prefix => $val) {
						// Loop through inner arrays of curType (ie suffixes like match, rally, etc)
						//if (!isset($sportType[$prefix])) {
							// Suffix is not set, set it
							$sportType[$prefix] = $sport['type'][$curType][$prefix];
							
							if ($userSport && isset($userTypes[$curType][$prefix])) {
								$sportType[$prefix]['selected'] = true;
							}
							// Set tooltip to true so selectableText creates tooltip from description
							$sportType[$prefix]['tooltip'] = $sport['type'][$curType][$prefix]['description'];
						//}
						
					}
					$output .= $this->selectableText($sportType);
					$output .= "</div>";
					/*
					$curType = $keysType[$counterType];
					
					if (!isset($sportType[$curType])) {
						// curType is not set in sportType, set it
						$sportType[$curType] = array();
						if ($userSport && isset($userTypes[$curType])) {
							$sportType[$curType]['selected'] = true;
						}
					}
					foreach ($type as $prefix => $val) {
						// Loop through inner arrays of curType (ie suffixes like match, rally, etc)
						if (!isset($sportType[$prefix])) {
							// Suffix is not set, set it
							$sportType[$prefix] = $sport['type'][$curType][$prefix];
							
							if ($userSport && isset($userTypes[$prefix])) {
								$sportType[$prefix]['selected'] = true;
							}
							// Set tooltip to true so selectableText creates tooltip from description
							$sportType[$prefix]['tooltip'] = $sport['type'][$curType][$prefix]['description'];
						}
					}
					*/
					$counterType++;
				}
				//$output .= $this->selectableText($sportType);
				$output .= '</div>';
			}
			
			// What do you want to play?
			$output .= "<div class='signup-sports-what signup-sports-form-section' section='what' id='signup-sports-what-" . $sport['sport'] . "'>
						<p class='signup-sports-form-section-title'>What do you want to play? <span class='light'>select any</p>";
			$what    = array('Pickup' 				=> array('tooltip' => 'Pickup games can be found at most parks and rec centers.  They are non-competitive and inspire exercise.'),
							 'League' 				=> array('subtext' => 'w/ refs',
							 				   				 'tooltip' => 'League teams play against one another in competitive, scheduled games with referees.'),
							 'Weekend Tournament'   => array('tooltip' => 'Weekend tournaments give you a taste of league play without the long-term commitment.  It is a reffed tournament that happens on a Saturday and Sunday.')
							 );
			
			if ($userSport) {
				foreach ($what as $formatName => $array) {
					if (isset($userSport->formats[strtolower($formatName)])) {
						// User has this selected
						$what[$formatName]['selected'] = true;
					}
				}
			}
					
			
			$output .= $this->selectableText($what);
			$output .= '</div>';
			
			
			
			if (!empty($sport['position'])) {
				// Position section to be shown
				$output .= "<div class='signup-sports-position hidden signup-sports-form-section' section='position' id='signup-sports-position-" . $sport['sport'] . "'>
							<p class='signup-sports-form-section-title'>Position <span class='light'>only used for league play, select up to two</p>";
				
				$userPositions = array();
				if ($userSport) {
					// Get user positions for this sport
					if ($userSport->hasValue('positions')) {
						foreach ($userSport->positions as $position) {
							$userPositions[$position->positionAbbreviation] = true;
						}
					}
				}
				
				foreach ($sport['position'] as $position => $value) {
					// Set tooltip to true so selectableText will create tooltips using description/name
					$sport['position'][$position]['tooltip'] = '<span class="center medium bold">' 
															 . ucwords($sport['position'][$position]['name']) 
															 . '</span><br>' 
															 . $sport['position'][$position]['description'];
							 
					if (isset($userPositions[$position])) {
						// Is userPosition, select
						$sport['position'][$position]['selected'] = true;
						
					}

				}
				
				$output .= $this->selectableText($sport['position']);
				
				$output .= '</div>';
			}
			
			
			// How often?
			$output .= "<div class='signup-sports-often signup-sports-form-section' section='often' id='signup-sports-often-" . $sport['sport'] . "'>
						<p class='signup-sports-form-section-title'>How often do you want to play?</p>";
			$what    = array('Once/month' => array('value' => 30),
							 'Once/week' => array('value' => 7),
							 '2-3 times/week' => array('value' => 2),
							 'No limit' => array('value' => 0)
							 );
							 
			if ($userSport) {
				foreach ($what as $often => $array) {
					if ($userSport->often == $array['value']) {
						// User has this selected
						$what[$often]['selected'] = true;
					}
				}
			}
			$output .= $this->selectableText($what, true);
			$output .= '</div>';
			
			// Availability
			$output .= "<div class='signup-sports-availability signup-sports-form-section' section='availability'>
						<p class='signup-sports-form-section-title'>When would you want to play?</p>
						<div class='signup-sports-availability-copy-container'>
							<p class='medium'>Copy availability from:</p>
							<div class='signup-sports-availability-copy-option-container'>
								" . $this->_view->copyAvailabilityDropdown . "
							</div>
						</div>";
			$output .= $this->_view->availabilitycalendar()->create($sport['sport'], $userSport);			
						
			$output .= "</div>";
			
			
			// Create hidden inputs for each section
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'])      			  ->setAttrib('id',$sport['sport'] . 'Active');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Rating')      ->setAttrib('id',$sport['sport'] . 'Rating');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Type')        ->setAttrib('id',$sport['sport'] . 'Type');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Position')    ->setAttrib('id',$sport['sport'] . 'Position');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'What')        ->setAttrib('id',$sport['sport'] . 'What');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Often')       ->setAttrib('id',$sport['sport'] . 'Often');
			
			/*$days    = array('Su','M','T','W','Th','F','Sa');
			foreach ($days as $day) {*/
			for ($i = 0; $i < 7; $i++) {
				$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Availability' . $i)->setAttrib('id',$sport['sport'] . 'Availability' . $i);
			}
			$output .= "</div></div>";
			
			$counterOuter++;
			
		}
		$output .= "</div>";
						
		return $output;
	}
	
	/**
	 * create options for selectable text
	 * @params( $array => array of options,
	 *			$onlyOne => only one option can be selected at a time
	 */
	public function selectableText($array, $onlyOne = false)
	{
		
		$output  = '';
		$counter = 0;
		$keys = array_keys($array);
		
		foreach ($array as $key) {
			
					$class = 'signup-sports-selectable selectable-text light pointer';
					if ($counter == 0) {
						$class .= ' clear';
					}
					if ($onlyOne) {
						$class .= ' selectable-text-one';
					}
					
					if (is_array($key)) {
						// $key is associative array with value
						if (!empty($key['subtext'])) {
							$value = ucwords($keys[$counter]) . ' <span class="smaller-text lighter">' . $key['subtext'];
						} else {
							$value = ucwords($keys[$counter]);
						}
						if (!empty($key['selected'])) {
							// Should be selected
							$class .= ' green-bold';
						}
						
						
					} else {
						// Simple value, no associative
						$value = ucwords($key);
					}
					$tooltip = '';
					if (isset($key['tooltip'])) {
						
						
							// Tooltip set explicitly
							$tooltip = $key['tooltip'];
						
					}
					
					$output .= "<p class='" . $class . "' tooltip='" . $tooltip . "'>"
							 . $value
							 . "</p>";
							 
					$counter++;
		}

		return $output;
	}

}
