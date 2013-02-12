<?php

class Application_View_Helper_Tooltip
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function tooltip()
	{
		$output = "<div id='tooltip'>
						<img src='/images/global/tooltip/tip.png' class='tooltip-tip'/>
						<div id='tooltip-body' class='dropshadow white-back'>
						
						</div>
					</div>";
					
		return $output;
	}
}
