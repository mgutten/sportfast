<?php

class Application_View_Helper_Homepage 
{
	public function homepage() 
	{
		//large images
		$output = "<div id='homepage-large-img-container'>
						<img src='' class='homepage-large-img fade-current' id='homepage-large-img1' />
						<img src='' class='homepage-large-img fade-next' id='homepage-large-img2' />
						<img src='' class='homepage-large-img' id='homepage-large-img3' />
						<img src='' class='homepage-large-img' id='homepage-large-img4' />
					</div>
					<div> Here is the body</div>";
		
		return $output;
	}
	
}