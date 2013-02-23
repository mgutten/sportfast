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
	
	public function loop($sports)
	{
		$output  = '';
		$keysOuter    = array_keys($sports);
		$counterOuter = 0;
		foreach ($sports as $sport) {
			$sport['sport'] = $keysOuter[$counterOuter];
			$output .= "<div id='signup-sports-hidden' class='animate-hidden-container'>
						<div class='signup-sports-form' id='signup-sports-form-" . $sport['sport'] . "' sport='" . $sport['sport'] . "'>
						<p class='center header signup-sports-title'>" . ucwords($sport['sport']) . "</p>
						<div class='signup-sports-skill signup-sports-form-section' id='signup-sports-skill-" . $sport['sport'] . "'>
							<p class='signup-sports-form-section-title'>Skill Level</p>";
							
			$output .= $this->_view->slider()->create(array('id'    		=> $sport['sport'] . '-rating',
															'desc'			=> true,
															'valuePosition' => 'below',
															'valueClass'	=> 'green-bold'));			
			$output .= "</div>";

			if (!empty($sport['position'])) {
				// Position section to be shown
				$output .= "<div class='signup-sports-position signup-sports-form-section' id='signup-sports-position-" . $sport['sport'] . "'>
							<p class='signup-sports-form-section-title'>Position <span class='light'>select up to two</p>";
				
				foreach ($sport['position'] as $position => $value) {
					// Set tooltip to true so selectableText will create tooltips using description/name
					$sport['position'][$position]['tooltip'] = '<span class="center medium bold">' 
															 . ucwords($sport['position'][$position]['name']) 
															 . '</span><br>' 
															 . $sport['position'][$position]['description'];

				}
				
				$output .= $this->selectableText($sport['position']);
				
				$output .= '</div>';
			}
			
			if (!empty($sport['type'])) {
				// Type section to be shown
				$output .= "<div class='signup-sports-type signup-sports-form-section' id='signup-sports-type-" . $sport['sport'] . "'>
							<p class='signup-sports-form-section-title'>Type <span class='light'>what types of games you want to play</p>";
				
				$sportType = array();
				$keysType      = array_keys($sport['type']);
				$counterType   = 0;
				foreach($sport['type'] as $type) {
					$curType = $keysType[$counterType];
					if (!isset($sportType[$curType])) {
						// curType is not set in sportType, set it
						$sportType[$curType] = array();
					}
					foreach ($type as $prefix => $val) {
						// Loop through inner arrays of curType (ie suffixes like match, rally, etc)
						if (!isset($sportType[$prefix])) {
							// Suffix is not set, set it
							$sportType[$prefix] = $sport['type'][$curType][$prefix];
							// Set tooltip to true so selectableText creates tooltip from description
							$sportType[$prefix]['tooltip'] = $sport['type'][$curType][$prefix]['description'];
						}
					}
					$counterType++;
				}
				$output .= $this->selectableText($sportType);
				$output .= '</div>';
			}
		
			// What do you want to play?
			$output .= "<div class='signup-sports-what signup-sports-form-section' id='signup-sports-what-" . $sport['sport'] . "'>
						<p class='signup-sports-form-section-title'>What do you want to play? <span class='light'>select any</p>";
			$what    = array('Pickup' 				=> array('tooltip' => 'Pickup games can be found at most parks and rec centers.  They are non-competitive and inspire exercise.'),
							 'League' 				=> array('subtext' => 'w/ refs',
							 				   				 'tooltip' => 'League teams play against one another in competitive, scheduled games with referees.'),
							 'Weekend Tournaments'  => array('tooltip' => 'Weekend tournaments give you a taste of league play without the long-term commitment.  It is a reffed tournament that happens on a Saturday and Sunday.')
							 );
			$output .= $this->selectableText($what);
			$output .= '</div>';
			
			// How often?
			$output .= "<div class='signup-sports-often signup-sports-form-section' id='signup-sports-often-" . $sport['sport'] . "'>
						<p class='signup-sports-form-section-title'>How often do you want to play?</p>";
			$what    = array('Once/month',
							 'Once/week',
							 '2-3 times/week',
							 'No limit'
							 );
			$output .= $this->selectableText($what);
			$output .= '</div>';
			
			// Availability
			$output .= "<div class='signup-sports-availability signup-sports-form-section'>
						<p class='signup-sports-form-section-title'>When would you want to play?</p>
						<div class='signup-sports-availability-copy-container smaller-text'>
							<p class='medium'>Copy availability from:</p>
							<div class='signup-sports-availability-copy-option-container'>
								N/A
							</div>
						</div>";
			$output .= $this->_view->availabilitycalendar()->create($sport['sport']);			
						
			$output .= "</div>";
			
			
			// Create hidden inputs for each section
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'])      			  ->setAttrib('id',$sport['sport'] . 'Active');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Rating')      ->setAttrib('id',$sport['sport'] . 'Rating');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Type')        ->setAttrib('id',$sport['sport'] . 'Type');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Position')    ->setAttrib('id',$sport['sport'] . 'Position');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'What')        ->setAttrib('id',$sport['sport'] . 'What');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Often')       ->setAttrib('id',$sport['sport'] . 'Often');
			
			$days    = array('Su','M','T','W','Th','F','Sa');
			foreach ($days as $day) {
				$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Availability' . $day)->setAttrib('id',$sport['sport'] . 'Availability' . $day);
			}
			$output .= "</div></div>";
			
			$counterOuter++;
			
		}
			
						
		return $output;
	}
	
	public function selectableText($array)
	{
		
		$output  = '';
		$counter = 0;
		$keys = array_keys($array);
		
		foreach ($array as $key) {
			
					$class = 'signup-sports-selectable selectable-text light pointer';
					if ($counter == 0) {
						$class .= ' clear';
					}
					if (is_array($key)) {
						// $key is associative array with value
						if (!empty($key['subtext'])) {
							$value = ucwords($keys[$counter]) . ' <span class="smaller-text lighter">' . $key['subtext'];
						} else {
							$value = ucwords($keys[$counter]);
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
