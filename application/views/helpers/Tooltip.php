<?php

class Application_View_Helper_Tooltip
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	public function tooltip($id = '', $content = '')
	{
		$output = "<div id='tooltip" . $id . "' class='tooltip-container'>
						<img src='/images/global/tooltip/tip.png' class='tooltip-tip'/>
						<div id='tooltip-body' class='dropshadow white-back'>
						" . $content . "
						</div>
					</div>";
					
		return $output;
	}
	

}
