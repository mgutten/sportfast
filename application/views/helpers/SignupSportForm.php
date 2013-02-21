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
		$output = '';
		foreach ($sports as $sport) {
			$output .= "<div class='signup-sports-form' id='signup-sports-form-" . $sport['sport'] . "' sport='" . $sport['sport'] . "'>
						<p class='center header signup-sports-title'>" . ucwords($sport['sport']) . "</p>
						
						<div class='signup-sports-skill signup-sports-form-section' id='signup-sports-skill-" . $sport['sport'] . "'>
							<p>Skill Level</p>
							<div class='signup-skill-slider'></div>
							<p class='center medium slider-text'>
							This is the skill level.
							</p>
						</div>";

			if (!empty($sport['position'])) {
				// Position section to be shown
				$output .= "<div class='signup-sports-position signup-sports-form-section' id='signup-sports-position-" . $sport['sport'] . "'>
							<p>Position <span class='light'>select up to two</p>";
				$output .= $this->selectableText($sport['position']);
				$output .= '</div>';
			}
			
			if (!empty($sport['type'])) {
				// Type section to be shown
				$output .= "<div class='signup-sports-type signup-sports-form-section' id='signup-sports-type-" . $sport['sport'] . "'>
							<p>Type <span class='light'>what types of games you want to play</p>";
				$output .= $this->selectableText($sport['type']);
				$output .= '</div>';
			}
		
			// What do you want to play?
			$output .= "<div class='signup-sports-what signup-sports-form-section' id='signup-sports-what-" . $sport['sport'] . "'>
						<p>What do you want to play? <span class='light'>select any</p>";
			$what    = array('Pickup' 				=> array('tooltip' => 'Pickup games can be found at most parks and rec centers.  They are non-competitive and inspire exercise.'),
							 'League' 				=> array('subtext' => 'w/ refs',
							 				   				 'tooltip' => 'League teams play against one another in competitive, scheduled games with referees.'),
							 'Weekend Tournaments'  => array('tooltip' => 'Weekend tournaments give you a taste of league play without the long-term commitment.  It is a reffed tournament that happens on a Saturday and Sunday.')
							 );
			$output .= $this->selectableText($what);
			$output .= '</div>';
			
			// How often?
			$output .= "<div class='signup-sports-often signup-sports-form-section' id='signup-sports-often-" . $sport['sport'] . "'>
						<p>How often do you want to play?";
			$what    = array('Once/month',
							 'Once/week',
							 '2-3 times/week',
							 'No limit'
							 );
			$output .= $this->selectableText($what);
			$output .= '</div>';
			
			// Create hidden inputs for each section
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Rating')      ->setAttrib('id',$sport['sport'] . 'Rating');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Type')        ->setAttrib('id',$sport['sport'] . 'Type');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Position')    ->setAttrib('id',$sport['sport'] . 'Position');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'What')        ->setAttrib('id',$sport['sport'] . 'What');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Often')       ->setAttrib('id',$sport['sport'] . 'Often');
			$output .= $this->_view->signupSportForm->sport->setName($sport['sport'] . 'Availability')->setAttrib('id',$sport['sport'] . 'Availability');
			$output .= "</div>";
			
		}
			
						
		return $output;
	}
	
	public function selectableText($array)
	{
		$output  = '';
		$counter = 0;
		$keys = array_keys($array);
		foreach ($array as $key) {
					$class = 'signup-sports-selectable selectable-text lighter pointer';
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
					if (!empty($key['tooltip'])) {
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
