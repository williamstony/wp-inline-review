<?php
    /*
    Plugin Name: Inline Review
    Plugin URI: http://tonyw.io/inline-review
    Description: A Review engine for WordPress
    Author: TonyW
    Version: 1.1.0


    */

/*-------------------------------
 *
 * Some security
 *
 *------------------------------*/

if ( ! defined( 'WPINC' ) ) {
	die;
}

/*-----------------------------------------
 *
 * Setting our defaults to avoid an error
 *
 *----------------------------------------*/

register_activation_hook( __FILE__, 'nwxrview_defaults' );

function nwxrview_defaults() {
    $tmp = get_option( 'nwxrview_options' );
    if( !is_array( $tmp ) ) {
        $arr = array( "highlight_color" => "#0f0", "border_style" => "Dotted", "header_bg" => "#CCC" );
        update_option( 'nwxrview_options', $arr );
    }
}

/*------------------------------------
 *
 * Including Review Display Function
 *
 *------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'inc/review-display.php' );

/*----------------------------
 *
 * Add a review to a post
 *
 *---------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'inc/add-review.php' );

/*---------------------------
 *
 * Admin Stuff being included in
 *
 *---------------------------*/

require_once( plugin_dir_path( __FILE__  ) . 'inc/admin-settings.php' );

$test1 = new nwxrview_output();