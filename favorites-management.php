<?php 

/**
 * Plugin Name: HWP Favorites management
 * Plugin URI:  https://github.com/shgotlib/HWP-favorites-management
 * Text Domain: HWP_favs
 * Domain Path: /languages
 * Description: Just another favorite list management plugin. flexible and simple to use.
 * Version:     1.0
 * Required:    4
 * Author:      Shlomi Gottlieb
 * License:     MIT
 * License URI: http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright (c) 2017 Shlomi Gottlieb
 */


 // Not a WordPress context? Stop.
if(!defined( 'ABSPATH' )) {
    die();
}

class HWP_Favs {
    
    private static $instance;

    /**
    * Returns an instance of this class. 
    */
    public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new HWP_Favs();
		} 
		return self::$instance;
	} 

    /**
    * Initializes the plugin by setting filters and administration functions.
    */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'HWP_favs_Shortcodes_enqueue_script'));
        add_action('plugins_loaded', array($this, 'plugin_init')); 

        require_once(__DIR__.'/inc/HWP_Favs-settings.php');
        require_once(__DIR__.'/inc/HWP_Favs-shortcodes.php');
        require_once(__DIR__.'/inc/HWP_Favs-ajax.php');

        new HWP_Favs_Shortcodes();
        new HWP_Favs_Settings();
        new HWP_Favs_AJAX();
    }

    public function HWP_favs_Shortcodes_enqueue_script() {
        wp_register_script( 'HWP_Favs__custom_script', plugin_dir_url( __FILE__ ) . 'inc/HWP_favs-custom.js', array('jquery'), false, true );
        wp_localize_script( 'HWP_Favs__custom_script', 'HWP_Favs', array(
            'ajaxurl'		=> site_url().'/wp-admin/admin-ajax.php',
            'messages'      => array(
                'areYouSure'  => __('Are you sure you want to delete this item?', 'HWP_favs'),
                'youDonotHaveFavs'   => __('You have not saved any articles yet :(', 'HWP_favs'),
                'removeItem'   => __('Remove this item from your list', 'HWP_favs'),
                'addItem'   => __('Add this item to your list', 'HWP_favs')
            )
        ) );
        wp_enqueue_script( 'HWP_Favs__custom_script' );
        wp_enqueue_style( 'HWP_Favs__custom_style', plugin_dir_url( __FILE__ ) . 'inc/HWP_Favs_fav-style.css' );
    }

    function plugin_init() {
        load_plugin_textdomain( 'HWP_favs', false, basename(  __DIR__  ) . '/languages' );
    }
}


add_action( 'plugins_loaded', array( 'HWP_Favs', 'get_instance' ) );