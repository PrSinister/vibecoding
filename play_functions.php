<?php
//---------------------------------------------------------------------------------------------
//Here is how disable selection script will shown
//---------------------------------------------------------------------------------------------
function wccp_pro_main_settings($wccp_pro_settings)
{
	if (((is_home() || is_front_page() || is_archive() || is_post_type_archive() ||  is_404() || is_attachment() || is_author() || is_category() || is_feed() || is_search()))) //dont forget to search for woocommerce pages
	{
		if($wccp_pro_settings['home_page_protection'] == 'checked') wccp_pro_disable_selection($wccp_pro_settings);
						
		return;
	}
	if (is_single())
	{
		if($wccp_pro_settings['single_posts_protection'] == 'checked') wccp_pro_disable_selection($wccp_pro_settings);
						
		return;
	}
	if (is_page() && !is_front_page())
	{
		if($wccp_pro_settings['page_protection'] == 'checked') wccp_pro_disable_selection($wccp_pro_settings);
						
		return;
	}
}
//---------------------------------------------------------------------------------------------
//Here is how disable right click script will shown
//---------------------------------------------------------------------------------------------
function wccp_pro_right_click_premium_settings($wccp_pro_settings)
{
	if (((is_home() || is_front_page() || is_archive() || is_post_type_archive() ||  is_404() || is_attachment() || is_author() || is_category() || is_feed()) && $wccp_pro_settings['right_click_protection_homepage'] == 'checked'))
	{
		wccp_pro_disable_Right_Click($wccp_pro_settings); //Located in js_functions.php
		return;
	}
	if (is_single() && $wccp_pro_settings['right_click_protection_posts'] == 'checked')
	{
		wccp_pro_disable_Right_Click($wccp_pro_settings); //Located in js_functions.php
		return;
	}
	if (is_page() && !is_front_page() && $wccp_pro_settings['right_click_protection_pages'] == 'checked')
	{
		wccp_pro_disable_Right_Click($wccp_pro_settings); //Located in js_functions.php
		return;
	}
}

//---------------------------------------------------------------------------------------------
//Here is how disable selection by CSS style sheet
//---------------------------------------------------------------------------------------------
function wccp_pro_css_settings($wccp_pro_settings)
{
	wccp_pro_css_inject($wccp_pro_settings); //Located in css_functions.php
	if (((is_home() || is_front_page() || is_archive() || is_post_type_archive() ||  is_404() || is_attachment() || is_author() || is_category() || is_feed() || is_search()) && $wccp_pro_settings['home_css_protection'] == 'Yes'))
	{
		wccp_pro_css_script(); //Located in css_functions.php
		return;
	}
	if (is_single() && $wccp_pro_settings['posts_css_protection'] == 'Yes')
	{
		wccp_pro_css_script(); //Located in css_functions.php
		return;
	}
	if (is_page() && !is_front_page() && $wccp_pro_settings['pages_css_protection'] == 'Yes')
	{
		wccp_pro_css_script(); //Located in css_functions.php
		return;
	}
}

function wccp_pro_enqueue_css_scripts($wccp_pro_settings)
{
	$pluginsurl = plugins_url( '', __FILE__ );

	if (((is_home() || is_front_page() || is_archive() || is_post_type_archive() ||  is_404() || is_attachment() || is_author() || is_category() || is_feed() || is_search()) && $wccp_pro_settings['home_css_protection'] == 'Yes'))
	{
		wp_register_style('css-protect.css', $pluginsurl.'/css-protect.css?wccp_ver_num='.$wccp_pro_settings["wccp_ver_num"], array(), '10.9.2');
	
		wp_enqueue_style('css-protect.css');
		
		return;
	}
	if (is_single() && $wccp_pro_settings['posts_css_protection'] == 'Yes')
	{
		wp_register_style('css-protect.css', $pluginsurl.'/css-protect.css?wccp_ver_num='.$wccp_pro_settings["wccp_ver_num"], array(), '10.9.2');
	
		wp_enqueue_style('css-protect.css');
		
		return;
	}
	if (is_page() && !is_front_page() && $wccp_pro_settings['pages_css_protection'] == 'Yes')
	{
		wp_register_style('css-protect.css', $pluginsurl.'/css-protect.css?wccp_ver_num='.$wccp_pro_settings["wccp_ver_num"], array(), '10.9.2');
	
		wp_enqueue_style('css-protect.css');
		
		return;
	}
}
//---------------------------------------------------------------------------------------------
//Here we add specific CSS class by filter
//---------------------------------------------------------------------------------------------
// Add specific CSS class by filter
function wccp_pro_class_names($classes)
{
	$classes[] = 'unselectable';
	return $classes;
}

//---------------------------------------------------------------------------------------------
//Here is how protection overlay is work for images
//---------------------------------------------------------------------------------------------
function wccp_pro_images_overlay_settings($wccp_pro_settings)
{
	if (((is_home() || is_front_page() || is_archive() || is_post_type_archive() ||  is_404() || is_attachment() || is_author() || is_category() || is_feed() || is_search()) && $wccp_pro_settings['protection_overlay_homepage'] == 'checked'))
	{
		wccp_pro_images_overlay($wccp_pro_settings); //Located in js_functions.php
		return;
	}
	if (is_single() && $wccp_pro_settings['protection_overlay_posts'] == 'checked')
	{
		wccp_pro_images_overlay($wccp_pro_settings); //Located in js_functions.php
		return;
	}
	if (is_page() && !is_front_page() && $wccp_pro_settings['protection_overlay_pages'] == 'checked')
	{
		wccp_pro_images_overlay($wccp_pro_settings); //Located in js_functions.php
		return;
	}
}

//---------------------------------------------------------------------------------------------
//Here is how protection overlay is work for videos
//---------------------------------------------------------------------------------------------
function wccp_pro_videos_overlay_settings($wccp_pro_settings)
{
	if (((is_home() || is_front_page() || is_archive() || is_post_type_archive() ||  is_404() || is_attachment() || is_author() || is_category() || is_feed()) && $wccp_pro_settings['right_click_protection_homepage'] == 'checked' && $wccp_pro_settings['videos'] == 'checked')) 
		{
			wccp_pro_video_overlay(); //Located in js_functions.php
			return;
		}
	if (is_single() && $wccp_pro_settings['right_click_protection_posts'] == 'checked' && $wccp_pro_settings['videos'] == 'checked')
		{
			wccp_pro_video_overlay(); //Located in js_functions.php
			return;
		}
	if (is_page() && !is_front_page() && $wccp_pro_settings['right_click_protection_pages'] == 'checked' && $wccp_pro_settings['videos'] == 'checked')
		{
			wccp_pro_video_overlay(); //Located in js_functions.php
			return;
		}
}
?>