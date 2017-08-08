<?php

class ffpostage_main {

	var $version = "0.0.0.1";
	var $path;
	var $ffpostage_options;
	var $tablename = 'ffpost_subs';
	
	function ffpostage_main() {
			
	}

	protected function init()
	{
		global $path, $ffpostage_options;
		
		add_action("widgets_init", array('ffpostage_widget', 'register'));
		
		$ffpostage_options 	= (array) json_decode(get_option('ffpostage_options'));
		$path 				= get_option('siteurl'). '/wp-content/plugins/' . basename(dirname(__FILE__));
		
		
		if(!defined('POSTAGE_HOSTNAME')) define ('POSTAGE_HOSTNAME', 'https://api.postageapp.com');
		if(!defined('POSTAGE_API_KEY')) define ('POSTAGE_API_KEY', $ffpostage_options['apikey']);
		
		
		
	}
	
	function activate() {
		$this->create_table();
	}
	
	function deactivate() {
	
	}
	
	function create_table()
	{
		global $wpdb;
		
		if(!require_once(ABSPATH . 'wp-admin/upgrade-functions.php')) {
			die('Foolish plugin has added its own maybe_upgrade* functions');
		}
	
		if($wpdb->get_var("show tables like " . $wpdb->prefix. $this->tablename) != $wpdb->prefix. $this->tablename)
		{
			$sql = "CREATE TABLE " . $wpdb->prefix . $this->tablename . " (
			ID mediumint(9) NOT NULL AUTO_INCREMENT,
			email_email varchar(100) NOT NULL,
			email_registered datetime NOT NULL,
			email_status int(11) NOT NULL,
			UNIQUE KEY ID (ID)
			) DEFAULT CHARSET=utf8;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			
			update_option('ffpostage_version', $this->version);
		}
	}

}