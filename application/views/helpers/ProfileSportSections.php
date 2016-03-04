<?php

class Application_View_Helper_ProfileSportSections
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function profilesportsections()
	{
		return $this;
	}
	
	public function start($sports, $selectedSport = false)
	{
		$output = '';
		$count = 0;
		foreach ($sports as $sport) {
			
			$class = $tabClass = '';
			if ($selectedSport) {
				// Sport selected in controller
				if (strtolower($sport->sport) == strtolower($selectedSport)) {
					$class = 'user-sport-container-selected';
					$tabClass = 'user-sport-selected';
				}
			} elseif ($count == 0) {
				// First sport, show box
				$class = 'user-sport-container-selected';
				$tabClass = 'user-sport-selected';
			}
			
			$output .= "<div class='user-sport-tab-container " . $tabClass . "'>
							<img src='" . $sport->getIcon('medium', 'outline') . "' class='user-sport-tab-back pointer'/>
							<div class='user-sport-tab-selected-container'>
								<div class='user-sport-tab-large-container rounded-corners'>
									<img src='" . $sport->getIcon('medium',	'solid', 'medium') . "' class='user-sport-tab-selected' />
									<p class='medium heavy left user-sport-tab-text'>" . ucwords($sport->sport) . "</p>
								</div>
							</div>
						  </div>";
			
			
			$count++;
		}
		
		return $output;
	}
	
		
	

}

	?>
    
