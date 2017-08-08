<?php
/*
Plugin Name: Postage APP for WordPress
Plugin URI: http://codementors.com/ff-postageapp
Description: Postage APP for WordPress
Version: 0.0.0.1
Author: Tolga Kaprol
Author URI: http://www.ff.com.tr
*/

// Main class includes general information about plugin, frontpage and admin classes extends main class
require_once( WP_PLUGIN_DIR . '/wp-newsletter-postage/class.ffpostage_core.php');
require_once( WP_PLUGIN_DIR . '/wp-newsletter-postage/class.ffpostage_main.php');
require_once( WP_PLUGIN_DIR . '/wp-newsletter-postage/class.ffpostage_frontpage.php');
require_once( WP_PLUGIN_DIR . '/wp-newsletter-postage/class.ffpostage_admin.php');
require_once( WP_PLUGIN_DIR . '/wp-newsletter-postage/class.ffpostage_widget.php');

$ffpostage_admin 	= new ffpostage_admin();
$ffpostage_frontpage = new ffpostage_frontpage();

$myclass = new ffpostage_main();

/* Make the class works first */
if (isset($myclass)) {
	register_activation_hook(__FILE__, array($myclass,'activate'));
	register_deactivation_hook(__FILE__, array($myclass, 'deactivate'));
}