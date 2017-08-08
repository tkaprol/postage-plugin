<?php

class ffpostage_frontpage extends ffpostage_main {
	
 	public function __construct()
	{
		global $path;
		$this->init();

		add_action('init', array($this, 'frontpage_init'));
		
		

		
	}

	
	
	
	
	function frontpage_init() {
		
	}
	
	
	
	
	
	
	
}
