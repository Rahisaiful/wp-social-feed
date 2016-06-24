<?php 
/*****
Plugin Name: wp social feed
Author URI:http://www.rahisaiful.com
Plugin URI: http://www.rahisaiful.com/plugin/nisi-feedstream.zip
Version: 1.0
Author: rahisaiful
Description: social media feed use as wordpress widgets.
*****/

if( ! function_exists( 'wsf_block_direct_access' ) ) {
	function wsf_block_direct_access() {
		if( ! defined( 'ABSPATH' ) ) {
			exit ( 'Direct access denied.' );
		}
	}
}


require_once( dirname( __FILE__ ) . '/widgets/wsf-flicker-widgets.php' );
require_once( dirname( __FILE__ ) . '/widgets/wsf-twitter-widgets.php' );
