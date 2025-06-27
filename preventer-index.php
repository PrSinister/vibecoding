<?php ob_start();
/*
Plugin Name: WP Content Copy Protection & No Right Click (premium)

Plugin URI: https://www.wp-buy.com/product/wp-content-copy-protection-pro/

License: Commercial software

License Description: https://en.wikipedia.org/wiki/Commercial_software

Description: This wp plugin protect the posts content from being copied by any other web site author , you dont want your content to spread without your permission!!

Version: 13.4

Author: wp-buy

Text Domain: wccp_pro_translation_slug

Domain Path: /languages

Author URI: https://www.wp-buy.com/
*/

//---------------------------------------------------------------------------------------------
//The updater
//---------------------------------------------------------------------------------------------
//$id = get_current_blog_id();
//delete_blog_option($id,'wccp_pro_settings');
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://www.wp-buy.com/wp-update-server/?action=get_metadata&slug=wccp-pro',
	__FILE__, //Full path to the main plugin file or functions.php.
	'wccp-pro'
);
//---------------------------------------------------------------------------------------------
//All includes here
//---------------------------------------------------------------------------------------------
$wpccp_pluginsurl = plugins_url( '', __FILE__ );

$wccp_pro_plugins_dir = plugin_dir_path( __FILE__ );

include $wccp_pro_plugins_dir . "/functions.php";
include $wccp_pro_plugins_dir . "/controls-functions.php";
include $wccp_pro_plugins_dir . "/common-functions.php";
include $wccp_pro_plugins_dir . "/private-functions.php";
include $wccp_pro_plugins_dir . "/js_functions.php";
include $wccp_pro_plugins_dir . "/css_functions.php";
include $wccp_pro_plugins_dir . "/play_functions.php";

try{
$wccp_pro_settings = wccp_pro_read_options_from_db('wccp_pro_settings');

add_action( 'upgrader_process_complete', function() use( $wccp_pro_settings ){ wccp_pro_modify_settings($wccp_pro_settings); },10, 2);

register_activation_hook( __FILE__, function() use( $wccp_pro_settings ){ wccp_pro_modify_settings($wccp_pro_settings); } );

register_activation_hook( __FILE__, function() use( $wccp_pro_settings ){ wccp_pro_modify_htaccess($wccp_pro_settings); } );

add_action( 'upgrader_process_complete', function() use( $wccp_pro_settings ){ wccp_pro_modify_htaccess($wccp_pro_settings); },10, 2);

add_action('init', 'wccp_pro_run'); //The main function
}
//catch exception
catch(Exception $e) {
  echo 'Message: ' .$e->getMessage();
}
///////////////////Main plugin function/////////////////
function wccp_pro_run(){
	
	$wccp_pro_settings = wccp_pro_read_options_from_db('wccp_pro_settings');
	
	//print_r($wccp_pro_settings);
	
	wccp_pro_block_machine_user_agents();
	
	$exclude_this_page = 'False';
	
	$wccp_pro_is_admin = false;
	
	$wccp_pro_is_inside_page_builder = wccp_pro_is_inside_page_builder();
	
	if ( is_admin() || is_blog_admin()) $wccp_pro_is_admin = true;
	
	if($wccp_pro_settings['show_admin_bar_icon'] == 'Yes')
	{
		add_action('admin_bar_menu',function($admin_bar) use( $wccp_pro_settings ){ wpccp_add_items($wccp_pro_settings, $admin_bar); }, 40);
	}
	
	add_action( "wp_ajax_wccp_pro_ajax_top_bar",function() use( $wccp_pro_settings ){ wccp_pro_ajax_top_bar($wccp_pro_settings); });
	
	add_action( "wp_ajax_nopriv_wccp_pro_ajax_top_bar",function() use( $wccp_pro_settings ){ wccp_pro_ajax_top_bar($wccp_pro_settings); });

	add_action( "wp_ajax_wccp_pro_ajax_top_bar_remove_Protection",function() use( $wccp_pro_settings ){ wccp_pro_ajax_top_bar_remove_Protection($wccp_pro_settings); });
	
	add_action( "wp_ajax_nopriv_wccp_pro_ajax_top_bar_remove_Protection",function() use( $wccp_pro_settings ){ wccp_pro_ajax_top_bar_remove_Protection($wccp_pro_settings); });
	
	add_filter( "plugin_action_links_".plugin_basename(__FILE__), 'wccp_pro_plugin_add_settings_link', 10, 4 ); // To add settings link under the plugin name
	
	add_action('admin_menu', 'wccp_pro_add_options');
	
	add_action( 'wp_enqueue_scripts', 'wccp_pro_ajax_enqueue_scripts' );
	
	//---------------------------------------------------------------------------------------------
	//Add the plugin icon style to the top admin bar
	//---------------------------------------------------------------------------------------------
	add_action('wp_enqueue_scripts', 'wccp_pro_top_bar_enqueue_style');
	
	add_action('admin_enqueue_scripts', 'wccp_pro_top_bar_enqueue_style');
	
	//---------------------------------------------------------------------------------------------
	//Add the plugin icon style to the top admin bar
	//---------------------------------------------------------------------------------------------
	add_action( "wp_ajax_wccp_pro_advanced_get_link",function() use( $wccp_pro_settings ){ wccp_pro_advanced_get_link($wccp_pro_settings); });
	
	add_action( "wp_ajax_nopriv_wccp_pro_advanced_get_link",function() use( $wccp_pro_settings ){ wccp_pro_advanced_get_link($wccp_pro_settings); });
	
	if($wccp_pro_is_admin) return; //Exit from this function when inside admin dashboard
	
	if(!$wccp_pro_is_admin)
	{
		$exclude_this_page = exclude_this_page_or_not($wccp_pro_settings);
	}
	
	$do_not_use_cookies = $wccp_pro_settings["do_not_use_cookies"];
	
	if($do_not_use_cookies != "checked") //Dont use any cookies if the option checked
	{
		if($exclude_this_page == 'True' || $wccp_pro_is_admin)
		{
			// Set the expiration date to one hour ago
			
			$value = "excludethispage";
			
			$cookie_time = time()+ (20); // cookie time is 20 seconds by default
			
			if($wccp_pro_is_admin) $cookie_time = time() + (30); //increase cookie time for admin area
			
			@setcookie("wccp_pro_functionality", $value, $cookie_time , "/", "", false, true); //set a timed cookie
		}
		else
		{
			@setcookie("wccp_pro_functionality", "", time() - 3600, "/"); // Clear the cookie
		}
	}

	if($exclude_this_page == 'False' && !$wccp_pro_is_admin && !$wccp_pro_is_inside_page_builder)
	{
		add_action('wp_head',function() use( $wccp_pro_settings ){ scripts_injection($wccp_pro_settings); }); //Located on play_functions.php
		
		add_action('wp_enqueue_scripts',function() use( $wccp_pro_settings ){ wccp_pro_enqueue_css_scripts($wccp_pro_settings); }); //Located on play_functions.php
		
		add_action('wp_enqueue_scripts',function() use( $wccp_pro_settings ){ wccp_pro_enqueue_front_end_scripts($wccp_pro_settings); });
		
		if ($wccp_pro_settings['home_css_protection'] == 'Yes' || $wccp_pro_settings['posts_css_protection'] == 'Yes' ||  $wccp_pro_settings['pages_css_protection'] == 'Yes')
		{
			add_filter('body_class','wccp_pro_class_names');
		}
	}
	
	add_action('wp_head',function() use( $wccp_pro_settings ){ wccp_pro_global_js_scripts($wccp_pro_settings); });
	
	add_action('wp_footer',function() use( $wccp_pro_settings ){ wccp_pro_disable_selection_footer($wccp_pro_settings); });
	
	add_action('wp_footer',function() use( $wccp_pro_settings ){ wccp_pro_alert_message($wccp_pro_settings); });
	
	//Show the alert message inside admin panel for preview usage only
	
	$admincore = '';
	
	if (isset($_GET['page'])) $admincore = $_GET['page'];
	
	if( $wccp_pro_is_admin && $admincore == 'wccp-options-pro')
	{
		add_action( 'admin_footer',function() use( $wccp_pro_settings ){ wccp_admin_pro_alert_message($wccp_pro_settings); });
	}
}
?>