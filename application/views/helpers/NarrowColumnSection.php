<?php

class Application_View_Helper_NarrowColumnSection
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function narrowcolumnsection()
	{
		return $this;
	}
	
	public function start($array)
	{
		$id 	 = str_replace(' ','-',strtolower($array['title']));
		$title   = ucwords($array['title']);
		$tooltip = (!empty($array['tooltip']) ? $array['tooltip'] : '');
		
		$output = "<div id='narrow-column-" . $id . "'>
						<div class='narrow-column-header' tooltip='" . $tooltip . "'>" 
						. $title
						. "</div>
						<div class='animate-hidden-container'>
						<div class='narrow-column-body' id='narrow-column-body-" . $id . "'>";
						
		return $output;
	}
	
	public function end()
	{
		$output = "</div></div></div>";
		
		return $output;
	}

}
