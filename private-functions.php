<?php
//---------------------------------------------------------------------------------------------
//Register libraries using new wordpress register_script & enqueue_script functions
//---------------------------------------------------------------------------------------------
$pluginsurl = plugins_url( '', __FILE__ );

function wccp_pro_enqueue_scripts() {

	global $pluginsurl;
	
	$admincore = '';
	
	if (isset($_GET['page'])) $admincore = $_GET['page'];
	
	if( is_admin() && $admincore == 'wccp-options-pro') {
	
		wp_enqueue_script('jquery');
		
		wp_register_style('defaultcss', $pluginsurl.'/css/responsive-pure-css-tabs/default.css');
		wp_enqueue_style('defaultcss');
		
		wp_register_style('stylecss', $pluginsurl.'/css/responsive-pure-css-tabs/style.css?v=4');
		wp_enqueue_style('stylecss');
		
		wp_register_script('responsive_pure_css_tabsjs', $pluginsurl.'/css/responsive-pure-css-tabs/js.js');
		wp_enqueue_script('responsive_pure_css_tabsjs');
		
		if(is_rtl() == 'rtl')
			wp_register_style('bootstrapcss', $pluginsurl.'/bootstrap/css/bootstrap-rtl.min.css');
		else
			wp_register_style('bootstrapcss', $pluginsurl.'/bootstrap/css/bootstrap.min.css');
		
		wp_enqueue_style('bootstrapcss');
		
		wp_register_script('bootstrap-bundle-min-js', $pluginsurl.'/bootstrap/js/bootstrap.bundle.min.js');
		wp_enqueue_script('bootstrap-bundle-min-js');
		
		wp_enqueue_script( 'wccppro_slimselect', $pluginsurl.'/js/slimselect.min.js','1.0.0',true );
		wp_register_style('wccppro_slimselect_css', $pluginsurl.'/css/slimselect.min.css', false, '1.0.0' );
		wp_enqueue_style('wccppro_slimselect_css');
		
		wp_register_script('image-picker.js', $pluginsurl.'/image-picker/image-picker.js');
		wp_enqueue_script('image-picker.js');
		
		wp_register_style('image-picker.css', $pluginsurl.'/image-picker/image-picker.css');
		wp_enqueue_style('image-picker.css');
		
		wp_register_script('autocomplete-search-js', $pluginsurl.'/js/autocomplete.js',['jquery', 'jquery-ui-autocomplete'], null, true);
        wp_enqueue_script('autocomplete-search-js');
        wp_localize_script('autocomplete-search-js', 'AutocompleteSearch', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('autocompleteSearchNonce')
        ]);
        $wp_scripts = wp_scripts();
        wp_enqueue_style('jquery-ui-css','//ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-autocomplete']->ver . '/themes/smoothness/jquery-ui.css',false, null, false);
		
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'my-script-handle', plugins_url('admin_script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
		
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_enqueue_media();
	}
	else
	{
		wp_enqueue_script('jquery');
	}
}
add_action('admin_enqueue_scripts', 'wccp_pro_enqueue_scripts');

function wccp_pro_enqueue_front_end_scripts($wccp_pro_settings)
{
	global $pluginsurl;

	wp_enqueue_script('jquery');

	if($wccp_pro_settings['prnt_scr_msg'] != '')
	{
		wp_register_style('print-protection.css', $pluginsurl.'/css/print-protection.css?wccp_ver_num='.$wccp_pro_settings["wccp_ver_num"]);
		wp_enqueue_style('print-protection.css');
	}
}

//------------------------------------------------------------------------
function wpcp_pro_write_to_file_with_markers( $filename, $marker, $insertion ) {
    if (!file_exists( $filename ) || is_writeable( $filename ) ) {
		
		//Clear the file contents
		if($marker == "CLEAR_FILE_CONTENTS")
		{
			file_put_contents( $filename, "" );
			
			return;
		}

		file_put_contents( $filename, "/* BEGIN {$marker} */\n", FILE_APPEND | LOCK_EX );
		if ( is_array( $insertion ))
			foreach ( $insertion as $insertline )
				file_put_contents( $filename, "{$insertline}\n", FILE_APPEND | LOCK_EX );
		file_put_contents( $filename, "/* END {$marker} */\n\n", FILE_APPEND | LOCK_EX );

        return true;
    } else {
        return false;
    }
}

//---------------------------------------------------------------------------------------------
//Add the plugin icon style to the top admin bar
//---------------------------------------------------------------------------------------------
function wccp_pro_top_bar_enqueue_style() {
    ?>
    <style>
        .pro-wccp:before {
            content: "\f160";
            top: 3px;
        }
        .pro-wccp:before{
            color:#02CA03 !important
        }
        .pro-wccp {
            transform: rotate(45deg);
        }
    </style>
    <?php
}

function wccp_pro_ajax_enqueue_scripts(){
    wp_register_script(
        'wccp_pro_admin_bar_ajax',
        plugins_url('/js/admin_bar_ajax.js', __FILE__),
        array('jquery'),
        false,
        true
    );
    wp_enqueue_script( 'wccp_pro_admin_bar_ajax' );
    wp_localize_script(
        'wccp_pro_admin_bar_ajax',
        'ajax_object',
        array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),'link' => wccp_pro_get_self_url()  )
    );
}
add_action('admin_enqueue_scripts', 'wccp_pro_ajax_enqueue_scripts');

function wccp_pro_ajax_top_bar($wccp_pro_settings)
{
    $obj = new wccp_pro_controls_class();
	
	$link_add = esc_url_raw($_POST['link']);
 
    if(empty($wccp_pro_settings['url_exclude_list'])){
        $wccp_pro_settings['url_exclude_list'] = $link_add;
    }else{
        $wccp_pro_settings['url_exclude_list'] = $wccp_pro_settings['url_exclude_list']."\n".$link_add;
    }
    $obj -> update_blog_option_single_and_multisite( 'wccp_pro_settings' , $wccp_pro_settings );
    wp_die(); // ajax call must die to avoid trailing 0 in your response
}

function wccp_pro_ajax_top_bar_remove_Protection($wccp_pro_settings)
{
    $obj = new wccp_pro_controls_class();
	$link_add = esc_url_raw($_POST['link']);
    $data = isset($wccp_pro_settings['url_exclude_list'])?$wccp_pro_settings['url_exclude_list']:'';
    $data = str_replace($link_add, "", $data);
    $data = preg_split("/\r\n|\n|\r/", $data);
    $data = array_filter($data, 'strlen');
    $val='';
    foreach ($data as $row){
        $val = $val."\n".$row;
    }
    $wccp_pro_settings['url_exclude_list'] = $val;
    $obj -> update_blog_option_single_and_multisite( 'wccp_pro_settings' , $wccp_pro_settings );
    wp_die(); // ajax call must die to avoid trailing 0 in your response
}
//---------------------------------------------------------------------------------------------
//Add the plugin icon to the top admin bar
//---------------------------------------------------------------------------------------------
function wpccp_add_items($wccp_pro_settings, $admin_bar)
{
	global $post;
	
	$wccpadminurl = get_admin_url();

    $args = array(
        'id'    => 'Protection',
        'title' => '<span class="ab-icon pro-wccp"></span>'.__('Protection' ),
        'href'  => $wccpadminurl.'admin.php?page=wccp-options-pro',
        'meta'  => array('title' => __('WP Content Copy Protection'),)
    );

    $val = isset($wccp_pro_settings['url_exclude_list'])?$wccp_pro_settings['url_exclude_list']:'';
    $thisLink = wccp_pro_get_self_url();
    $pos = strpos($val, $thisLink);

    //print_r($pos);exit;
    if(!strlen($pos)){
        $sub_args_include = array(
            'id'    => 'WPCCP_Protect',
            'parent' => 'Protection',
            'title' => '<span onclick="wccp_pro_admin_bar_Protection();" style="width: 100%;display: block">'.__('Exclude this page' )."</span>",
            'href'  => "#",
            'meta'  => array('title' => __('WP Content Copy Protection'))
        );
    }else{
        $sub_args_include = array(
            'id'    => 'WPCCP_Protect',
            'parent' => 'Protection',
            'title' => '<span onclick="wccp_pro_admin_bar_remove_Protection();" style="width: 100%;display: block">'.__('Protect This page' )."</span>",
            'href'  => "#",
            'meta'  => array('title' => __('WP Content Copy Protection'))
        );
    }

    $admin_bar->add_menu($args);
    //in front-end pages only
    if(!is_admin()){
        $admin_bar->add_menu($sub_args_include);
    }
}
//---------------------------------------------------------------------------------------------
//Show settings page
//---------------------------------------------------------------------------------------------
function wccp_pro_options_page_pro()
{	
	if( is_plugin_active( 'wp-content-copy-protector/preventer-index.php' ) )
	{
		echo '<p align="center" dir="ltr">&nbsp;</p>
				<p align="center" dir="ltr">&nbsp;</p>
				<p align="center" dir="ltr">&nbsp;</p>
				<p align="center" dir="ltr"><font size="5" color="#FF0000">Alert!</font></p>
				<p align="center" dir="ltr"><font size="5">The free version of WP Content Copy 
				Protection is still active</font></p>
				<p align="center" dir="ltr"><font size="5">Please deactivate it to start using the pro version</font></p>';
	}
	else
	{
		include 'admin_settings.php';
	}
}
function wccp_pro_options_page_pro_loop()
{	
	if( is_plugin_active( 'wp-content-copy-protector/preventer-index.php' ) )
	{
		echo '<p align="center" dir="ltr">&nbsp;</p>
				<p align="center" dir="ltr">&nbsp;</p>
				<p align="center" dir="ltr">&nbsp;</p>
				<p align="center" dir="ltr"><font size="5" color="#FF0000">Alert!</font></p>
				<p align="center" dir="ltr"><font size="5">The free version of WP Content Copy 
				Protection is still active</font></p>
				<p align="center" dir="ltr"><font size="5">Please deactivate it to start using the pro version</font></p>';
	}
	else
	{
		include 'loop.php';
	}
}

//---------------------------------------------------------------------------------------------
//Make our function to call the WordPress function to add to the correct menu
//---------------------------------------------------------------------------------------------
function wccp_pro_add_options() {

	if(!current_user_can('manage_options')) return; //Premissions checker
	
	//show menu in normal
	add_menu_page
		(
			'Copy Protection PRO',       // use null for parent slug to hide it from admin menu
			__('Protection PRO'),    // page title
			'manage_options',           // capability
			'wccp-options-pro', // current menu slug
			'wccp_pro_options_page_pro', // callback
			'dashicons-lock', //$icon_url or icon class
			6
		);
	add_submenu_page //First sub-item
	(
		'wccp-options-pro',       // parent slug
		__('Main Options'),    // page title
		'Main Options',             // menu title
		'manage_options',           // capability
		'wccp-options-pro', // current menu slug
		'wccp_pro_options_page_pro' // callback
	);/*
	add_submenu_page //Second sub-item
	(
		'wccp-options-pro',       // parent slug
		__('Watermarking'),    // page title
		'Watermarking',             // menu title
		'manage_options',           // capability
		'wccp-options-pro_watermark_process', // current menu slug
		'wccp_pro_options_page_pro_loop' // callback
	);*/
	
	//remove_submenu_page("wccp-options-pro" , "first_removable_slug");
}
?>