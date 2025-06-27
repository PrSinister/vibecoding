<?php
//---------------------------------------------------------------------------------------------
//Load plugin textdomain to load translations
//---------------------------------------------------------------------------------------------
function wccp_pro_deactivate_the_free_version()
{
	if ( is_plugin_active( 'wp-content-copy-protector/preventer-index.php' ) )
	{
		deactivate_plugins('wp-content-copy-protector/preventer-index.php');
	}
}
register_activation_hook(__FILE__, 'wccp_pro_deactivate_the_free_version');

//---------------------------------------------------------------------------------------------
//Load plugin textdomain to load translations
//---------------------------------------------------------------------------------------------
function wccp_load_textdomain() {
  load_plugin_textdomain( 'wccp_pro_translation_slug', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
add_action( 'init', 'wccp_load_textdomain' );

//---------------------------------------------------------------------------------------------
//Report any error during activation
//---------------------------------------------------------------------------------------------
register_activation_hook( __FILE__, 'wccp_pro_my_activation_func' ); function wccp_pro_my_activation_func() {
    file_put_contents(__DIR__.'/my_loggg.txt', ob_get_contents());
}
//---------------------------------------------------------------------------------------------
//The 
//---------------------------------------------------------------------------------------------
function wccp_pro_add_htaccess($insertion) {
	//Clear the old htaccess file located inside the main website directory
	$htaccess_file = ABSPATH.'.htaccess';
	$filename = $htaccess_file;
	if (is_writable($filename)) {
		wccp_pro_insert_with_markers_htaccess($htaccess_file, 'wccp_pro_image_protection', '');//This will clear the old watermarking rules
	}
	
	//Create and update the new htaccess file located inside the uploads directory
    $htaccess_file = wccp_pro_get_uploads_dir_relative_path() . '/.htaccess';
	$filename = $htaccess_file;
	if (is_writable($filename)) {
		wccp_pro_insert_with_markers_htaccess($htaccess_file, 'wccp_pro_image_protection', '');//This will always clear the old watermarking rules
		return wccp_pro_insert_with_markers_htaccess($htaccess_file, 'wccp_pro_image_protection', (array) $insertion);
	}
}
//---------------------------------------------------------------------------------------------
//The 
//---------------------------------------------------------------------------------------------
function wccp_pro_get_uploads_dir_relative_path()
{
	$upload_dir = wp_upload_dir();
	$uploads_dir_relative_path = $upload_dir['basedir'];  // /home3/server-folder/sitefoldername.com/wp-content/uploads
	return $uploads_dir_relative_path;
}
//---------------------------------------------------------------------------------------------
//This function used to save the new settings after doin any upgrade
//---------------------------------------------------------------------------------------------
function wccp_pro_modify_settings($settings_array = array())
{
	$obj = new wccp_pro_controls_class();
	
	$obj->wccp_pro_save_settings(true);
}

//---------------------------------------------------------------------------------------------
//The 
//---------------------------------------------------------------------------------------------

function wccp_pro_modify_htaccess($settings_array = array())
{
	$wccp_pro_settings = wccp_pro_read_options_from_db('wccp_pro_settings');
	
	//The priority is for settings_array passed value because its more refreshed
	if(!is_array($wccp_pro_settings) || empty($wccp_pro_settings) || !empty($settings_array)) $wccp_pro_settings = $settings_array;
	
	//If still empty, exit this function, this may happen when function called from any location without parameters
	if(!is_array($wccp_pro_settings) || empty($wccp_pro_settings)) return; //If still empty, exit this function, this may happen when function called from any location without parameters
	
	$pluginsurl = '../plugins/wccp-pro'; //instead of plugins_url( '', __FILE__ );
	$url = site_url();
	$url = wccp_pro_get_domain($url);
	$hotlinking_rule_text = 'RewriteRule ^.*$ - [NC,L]';
	$mysite_rule_text = 'RewriteRule ^.*$ - [NC,L]';
	
	$type = 'dw';
	$dw_position = $wccp_pro_settings['dw_position'];
	$dw_text = $wccp_pro_settings['dw_text'];
	$dw_r_text = $wccp_pro_settings['dw_r_text'];
	$dw_font_color = $wccp_pro_settings['dw_font_color'];
	$dw_r_font_color = $wccp_pro_settings['dw_r_font_color'];
	$dw_font_size_factor = $wccp_pro_settings['dw_font_size_factor'];
	$dw_r_font_size_factor = $wccp_pro_settings['dw_r_font_size_factor'];
	$dw_text_transparency = $wccp_pro_settings['dw_text_transparency'];
	$dw_rotation = $wccp_pro_settings['dw_rotation'];
	$dw_imagefilter = $wccp_pro_settings['dw_imagefilter'];
	$dw_signature = $wccp_pro_settings['dw_signature'];
	$dw_logo = $wccp_pro_settings['dw_logo'];
	$dw_margin_left_factor = $wccp_pro_settings['dw_margin_left_factor'];
	$dw_margin_top_factor = $wccp_pro_settings['dw_margin_top_factor'];
	$watermark_caching = $wccp_pro_settings['watermark_caching'];
	$upload_dir = wp_upload_dir();
	$uploads_dir_relative_path = $upload_dir['basedir'];  // /home3/server-folder/sitefoldername.com/wp-content/uploads
	$baseurl = $upload_dir['baseurl'];  // http://example.com/wp-content/uploads
	$home_path = get_home_path();
	
	$exclude_online_services = trim($wccp_pro_settings['exclude_online_services']);
	$exclude_online_services = wccp_pro_multiexplode(array("," , "\r\n", "\n", "\r", "|"),$exclude_online_services);
	$exclude_online_services = wccp_pro_clean($exclude_online_services);
	$exclude_online_services = esc_attr(implode("|", $exclude_online_services));
	if($exclude_online_services == '') $exclude_online_services = "this_is_just_not_any_wanted_service_name";
	$exclude_online_services_rule_text = 'RewriteCond %{HTTP_USER_AGENT} !(' . $exclude_online_services . ') [NC]' . "\n	";
	$exclude_online_services_rule_text .= 'RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?(' . $exclude_online_services . ') [NC]';
	
	$excluded_images_from_watermarking = trim($wccp_pro_settings['excluded_images_from_watermarking']);
	$excluded_images_from_watermarking = wccp_pro_multiexplode(array("," , "\r\n", "\n", "\r", "|"),$excluded_images_from_watermarking);
	$excluded_images_from_watermarking = wccp_pro_clean($excluded_images_from_watermarking);
	$excluded_images_from_watermarking = esc_attr(implode("|", $excluded_images_from_watermarking));
	$excluded_images_from_watermarking = str_replace(".", "\.", $excluded_images_from_watermarking);
	if($excluded_images_from_watermarking == '') $excluded_images_from_watermarking = "this_is_just_not_any_wanted_image_name";
	$excluded_images_from_watermarking_rule_text = 'RewriteCond %{REQUEST_URI} (' . $excluded_images_from_watermarking . ') [NC,OR]' . "\n	";
	
	$exclude_registered_images_sizes = $wccp_pro_settings['exclude_registered_images_sizes'];
	$exclude_registered_images_sizes = wccp_pro_multiexplode(array("," , "\r\n", "\n", "\r", "|"),$exclude_registered_images_sizes);
	$exclude_registered_images_sizes = esc_attr(implode("|", $exclude_registered_images_sizes));
	$exclude_registered_images_sizes = str_replace(".", "\.", $exclude_registered_images_sizes);
	if($exclude_registered_images_sizes == '') $exclude_registered_images_sizes = "this_is_just_not_any_wanted_image_size";
	$excluded_images_from_watermarking_rule_text .= 'RewriteCond %{REQUEST_URI} (' . $exclude_registered_images_sizes . ') [NC]';
	
	$file_content = '<?php' . "\n";
	$file_content .= '$watermark_caching = "' .$watermark_caching. '";' . "\n";
	$file_content .= '$watermark_type = "' .$type. '";' . "\n";
	$file_content .= '$watermark_position = "' .$dw_position. '";' . "\n";
	$file_content .= '$watermark_r_text = "' .$dw_r_text. '";' . "\n";
	$file_content .= '$r_font_size_factor = "' .$dw_r_font_size_factor. '";' . "\n";
	$file_content .= '$watermark_text = "' .$dw_text. '";' . "\n";
	$file_content .= '$font_size_factor = "' .$dw_font_size_factor. '";' . "\n";
	$file_content .= '$pure_watermark_stamp_image = "' .$dw_logo. '";' . "\n";
	
	$file_content .= '$margin_left_factor = "' .$dw_margin_left_factor. '";' . "\n";
	$file_content .= '$margin_top_factor = "' .$dw_margin_top_factor. '";' . "\n";
	$file_content .= '$watermark_color = "' .$dw_font_color. '";' . "\n";
	$file_content .= '$watermark_r_color = "' .$dw_r_font_color. '";' . "\n";
	$file_content .= '$watermark_transparency = "' .$dw_text_transparency. '";' . "\n";
	$file_content .= '$watermark_rotation = "' .$dw_rotation. '";' . "\n";
	$file_content .= '$watermark_imagefilter = "' .$dw_imagefilter. '";' . "\n";
	$file_content .= '$watermark_signature = "' .$dw_signature. '";' . "\n";
	$file_content .= '$home_path = "' .$home_path. '";' . "\n";
	$file_content .= '$upload_dir = "' .$uploads_dir_relative_path. '";' . "\n";
	$file_content .= '$baseurl = "' .$baseurl. '";' . "\n";
	$file_content .= '?>';
	
	$plugin_dir_path = plugin_dir_path( __FILE__ );
	$file = $plugin_dir_path . 'watermarking-parameters.php';  // (Can write to this file)
	
	// Write the contents back to the file
	file_put_contents($file, $file_content);
	
	$dw_query = "type=dw&position=$dw_position&text=$dw_text&font_color=$dw_font_color&r_text=$dw_r_text&r_font_color=$dw_r_font_color&font_size_factor=$dw_font_size_factor&r_font_size_factor=$dw_r_font_size_factor&text_transparency=$dw_text_transparency&rotation=$dw_rotation&imagefilter=$dw_imagefilter&signature=$dw_signature&stamp=$dw_logo&margin_left_factor=$dw_margin_left_factor&margin_top_factor=$dw_margin_top_factor&home_path=$home_path";
	$dw_query = '';
	$hotlinking_rule = $wccp_pro_settings['hotlinking_rule'];
	if($hotlinking_rule == "Watermark"){
		$hotlinking_rule_text = 'RewriteRule ^(.*)\.(jpg|png|jpeg|webp)$ ' . $pluginsurl . '/watermark.php?'. $dw_query . '&src=/$1.$2' . '&w=1' . ' [PT,NC,L]';
	}else if ($hotlinking_rule == "No Action"){
		$hotlinking_rule_text = 'RewriteRule ^.*$ - [NC,L]';
	}
	
	$mysite_rule = $wccp_pro_settings['mysite_rule'];
	if($mysite_rule == "Watermark"){
		$mysite_rule_text = 'RewriteRule ^(.*)\.(jpg|png|jpeg|webp)$ ' . $pluginsurl . '/watermark.php?'. $dw_query . '&src=/$1.$2' . '&w=1' . ' [PT,NC,L]';
	}
	else
	{
		$mysite_rule_text = 'RewriteRule ^.*$ - [NC,L]';
	}
	
	$prevented_agents_rule_text = 'RewriteRule ^.*$ '. $pluginsurl . '/watermark.php [PT,L]';
	
	$ruls[] = <<<EOT
	<IfModule mod_rewrite.c>
	RewriteEngine on
EOT;
	
	$ruls[] = <<<EOT
	RewriteCond %{HTTP_COOKIE} (wccp_pro_functionality=excludethispage)
	RewriteRule ^(.*)\.(jpg|png|jpeg|gif|webp)$ - [NC,L]

	RewriteCond %{QUERY_STRING} (wccp_pro_watermark_pass) [NC,OR]
	RewriteCond %{REQUEST_URI} (wp-content/plugins) [NC,OR]
	RewriteCond %{REQUEST_URI} (wp-content/themes) [NC,OR]
	$excluded_images_from_watermarking_rule_text
	RewriteRule ^(.*)\.(jpg|png|jpeg|gif|webp)$ - [NC,L]
	
	# What happen to images on my site
	#RewriteCond %{HTTP_ACCEPT} (image|png|webp) [NC]
	RewriteCond %{HTTP_REFERER} ^http(s)?://(www\.)?$url [NC,OR]
	RewriteCond %{HTTP_REFERER} ^(.*)$url [NC]
	$mysite_rule_text
	
	#Save as or Click on View image after right click or without any referer
	RewriteCond %{HTTP_ACCEPT} (text|html|application|image|png|webp) [NC]
	$hotlinking_rule_text
	
	RewriteCond %{REQUEST_URI} \.(jpg|jpeg|png)$ [NC]
	RewriteCond %{REMOTE_ADDR} !^(127.0.0.1|162.144.5.62)$ [NC]
	RewriteCond %{REMOTE_ADDR} !^66.6.(32|33|36|44|45|46|40). [NC]
	$exclude_online_services_rule_text
	RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?(www.$url|$url|pinterest.com|tumblr.com|facebook.com|plus.google|twitter.com|Twitterbot|googleapis.com|googleusercontent.com|ytimg.com|gstatic.com) [NC]
	RewriteCond %{HTTP_USER_AGENT} !(linkedin.com|LinkedInBot|WhatsApp|googlebot|msnbot|baiduspider|slurp|webcrawler|teoma|photon|facebookexternalhit|facebookplatform|pinterest|feedfetcher|ggpht) [NC]
	RewriteCond %{HTTP_USER_AGENT} !(photon|smush.it|akamai|cloudfront|netdna|bitgravity|maxcdn|edgecast|limelight|tineye) [NC]
	RewriteCond %{HTTP_USER_AGENT} !(developers|gstatic|googleapis|googleusercontent|google|ytimg) [NC]
	$hotlinking_rule_text
	
</ifModule>
EOT;
//NC (no case, case insensitive, useless in this context) and L (last rule if applied)
	wccp_pro_add_htaccess($ruls);
}
//---------------------------------------------------------------------------------------------
//Remove all special characters from a string
//---------------------------------------------------------------------------------------------
function wccp_pro_clean($string)
{
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

   $string = preg_replace('/[^a-zA-Z0-9_%\[().\]\\/-]/s', '', $string);// Removes special characters but keeps what's allowed in file naming
   
   return array_filter($string);//Remove empty array elements
}

//---------------------------------------------------------------------------------------------
//Create a watermarked images directory within the Uploads Folder when plugin activated
//---------------------------------------------------------------------------------------------
function create_watermarked_images_directory() {
    $can_cache = false;
    $uploads_dir_relative_path = wccp_pro_get_uploads_dir_relative_path();
    $cache_dir_relative_path = $uploads_dir_relative_path . '/wccp_pro_watermarked_images';
    if (! is_dir($cache_dir_relative_path)) {
       $can_cache = mkdir( $cache_dir_relative_path, 0755 );
    }
	return $can_cache;
}
register_activation_hook( __FILE__, 'create_watermarked_images_directory' );

//---------------------------------------------------------------------------------------------
// wccp_pro_clear_htaccess
//---------------------------------------------------------------------------------------------
function wccp_pro_clear_htaccess()
{
	$htaccess_file = ABSPATH.'.htaccess';
	
	wccp_pro_insert_with_markers_htaccess($htaccess_file, 'wccp_pro_image_protection', "");//This will clear the old watermarking rules
	
	$htaccess_file = wccp_pro_get_uploads_dir_relative_path() . '/.htaccess';
	
	wccp_pro_insert_with_markers_htaccess($htaccess_file, 'wccp_pro_image_protection', "");//This will clear the watermarking rules
}
register_deactivation_hook( __FILE__, 'wccp_pro_clear_htaccess' );

function wccp_pro_insert_with_markers_htaccess( $filename, $marker, $insertion ) {
    if (!file_exists( $filename ) || is_writeable( $filename ) ) {
        if (!file_exists( $filename ) ) {
            $markerdata = '';
        } else {
            $markerdata = explode( "\n", implode( '', file( $filename ) ) );
        }
 
        if ( !$f = @fopen( $filename, 'w' ) )
            return false;
 
        $foundit = false;
        if ( $markerdata ) {
            $state = true;
            foreach ( $markerdata as $n => $markerline ) {
                if (strpos($markerline, '# BEGIN ' . $marker) !== false)
                    $state = false;
                if ( $state ) {
                    if ( $n + 1 < count( $markerdata ) )
                        fwrite( $f, "{$markerline}\n" );
                    else
                        fwrite( $f, "{$markerline}" );
                }
                if (strpos($markerline, '# END ' . $marker) !== false) {
                    fwrite( $f, "# BEGIN {$marker}\n" );
                    if ( is_array( $insertion ))
                        foreach ( $insertion as $insertline )
                            fwrite( $f, "{$insertline}\n" );
                    fwrite( $f, "# END {$marker}\n" );
                    $state = true;
                    $foundit = true;
                }
            }
        }
        if (!$foundit) {
            fwrite( $f, "\n# BEGIN {$marker}\n" );
			if ( is_array( $insertion ))
				foreach ( $insertion as $insertline )
					fwrite( $f, "{$insertline}\n" );
            fwrite( $f, "# END {$marker}\n" );
        }
        fclose( $f );
        return true;
    } else {
        return false;
    }
}

//---------------------------------------------------------------------
//To use debug console in PHP because its just allowed using JavaScript 
//---------------------------------------------------------------------
function wccp_pro_debug_to_console($wccp_pro_settings, $title, $data)
{
	if (wccp_pro_is_login_page() || is_admin() || wccp_pro_is_inside_page_builder()) return; // Exit from here if we are inside login page
	 
	if(!is_array($wccp_pro_settings)) return null;
	
	if(array_key_exists("developer_mode", $wccp_pro_settings))
	{	
		if($wccp_pro_settings['developer_mode'] == "Yes")
		{
			$output = $data;
			if ( is_array( $output ))
			{
				foreach ( $output as $element )
					if(isset($element))
					{
						$element = preg_replace("/\r|\n/", "", $element);
						add_action( 'wp_enqueue_scripts', function() use ($title,$element)
						{
							echo "<script>console.log('Array: " . $title . ': ' . $element . "' );</script>";
						});
					}
			}
			if ( is_string( $output ))
			{
				$output = preg_replace("/\r|\n/", "", $output);
				add_action( 'wp_enqueue_scripts', function() use ($title,$output)
						{
							echo "<script>console.log('String: " . $title . ': ' . $output . "' );</script>";
						});
			}
		}
	}
}

//---------------------------------------------------------------------------------------------
//Check if we are inside the login page or not
//---------------------------------------------------------------------------------------------
function wccp_pro_is_login_page()
{
	//true if login page URL is still normal
	if(in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) return true;
	
	//true if login page URL is changed by some security plugins
	//if(has_action('login_init')) return true;
	
	if(isset($_GET["loggedout"]) && $_GET["loggedout"]==true) return true;
	
	if(isset($_GET["redirect_to"]) && str_contains($_GET["redirect_to"], 'wp-admin')) return true;
	
	//Else if nothing above happens >> return false
	return false;
}

//---------------------------------------------------------------------------------------------
//Special definition for str_contains function because its created inside PHP 8.1 and above
//---------------------------------------------------------------------------------------------
if (!function_exists('str_contains'))
{
    function str_contains(string $haystack, string $needle)
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}

//---------------------------------------------------------------------------------------------
// wccp_pro_get_domain
//---------------------------------------------------------------------------------------------
function wccp_pro_get_domain($url)
{
	$nowww = preg_replace('/www\./','',$url);
	
	$domain = parse_url($nowww);
	
	preg_match("/[^\.\/]+\.[^\.\/]+$/", $nowww, $matches);
	
	if(count($matches) > 0)
	{
		return $matches[0];
	}
	else
	{
		return FALSE;
	}
}

//---------------------------------------------------------------------------------------------
//Returns true if $search_for is a substring of $search_in
//---------------------------------------------------------------------------------------------
function wccp_pro_contains($search_in, $search_for)
{
    return strpos($search_in, $search_for) !== false;
}

function inStr($needle, $haystack)
{
  $needlechars = strlen($needle); //gets the number of characters in our needle
  $i = 0;
  for($i=0; $i < strlen($haystack); $i++) //creates a loop for the number of characters in our haystack
  {
    if(substr($haystack, $i, $needlechars) == $needle) //checks to see if the needle is in this segment of the haystack
    {
      return TRUE; //if it is return true
    }
  }
  return FALSE; //if not, return false
}
//---------------------------------------------------------------------------------------------
// Exclude_this_page_or_not
//---------------------------------------------------------------------------------------------
function exclude_this_page_or_not($wccp_pro_settings){

//wccp_pro_completely_disable_any_browser_caching(); // need option to customize it

$exclude_this_page = 'False';

$opposite_mode = 'Inactive';

$allowed_roles = array();

//Check user-type exclusion - Start
if (array_key_exists("exclude_by_user_type",$wccp_pro_settings))
	{
		if(is_array($wccp_pro_settings['exclude_by_user_type']))
			$allowed_roles = $wccp_pro_settings['exclude_by_user_type'];
	}
	
	if(!defined('AUTH_COOKIE'))
		$roles = array();
	else
		$roles = wccp_pro_get_current_user_roles();
	
	if(is_array($roles) && is_array($allowed_roles))
	{
		if( array_intersect($roles, $allowed_roles) ) {
			$exclude_this_page = 'True';
		}
	}
//Check user-type exclusion - End

if (array_key_exists("opposite_mode",$wccp_pro_settings))
	{
		$opposite_mode = $wccp_pro_settings['opposite_mode'];
	}

if($opposite_mode == "Active" && $exclude_this_page == "False")
	{
		$self_url = wccp_pro_get_self_url();

		$exclude_this_page = 'True'; //Exclude all pages and the next code will decide to include some of them or not

		$tag = '';

		$url_included_list = '';

		if(isset($wccp_pro_settings['url_included_list'])) $url_included_list = $wccp_pro_settings['url_included_list']; else $url_included_list = '';

		// Processes \r\n's first so they aren't converted twice.
		$url_included_list = str_replace("\\n", "\n", $url_included_list);

		$self_url = trim($self_url);

		$self_url = preg_replace('{/$}', '', $self_url);

		$urlParts = parse_url($self_url);

		if(isset($urlParts['scheme'])) $urlParts_scheme = $urlParts['scheme'] . '://'; else $urlParts_scheme = '';

		if(isset($urlParts['host'])) $urlParts_host = $urlParts['host']; else $urlParts_host = '';

		if(isset($urlParts['path'])) $urlParts_path = $urlParts['path']; else $urlParts_path = '';

		if(isset($urlParts['query'])) $urlParts_query = '?' . $urlParts['query']; else $urlParts_query = '';

		$self_url = $urlParts_scheme . $urlParts_host . $urlParts_path . $urlParts_query;

		$url_included_list = wccp_pro_multiexplode(array("," ," ", "\n", "|"),$url_included_list);

		wccp_pro_debug_to_console($wccp_pro_settings, "url_included_list", $url_included_list);

		if( !empty($url_included_list) )
			{
				for ($i=0; $i <= count($url_included_list); $i++)
				{
					if (isset($url_included_list[$i]))
					{
						$tag = $url_included_list[$i];
						
						$tag = trim($tag);
					}
					else
					{
						$tag = '';
					}
					if (wccp_pro_contains($tag, '/*')) //Bulk exclusion
					{
						$tag = str_replace("/*", "", $tag);
						
						if (wccp_pro_contains($self_url, $tag))
						{
							$exclude_this_page = 'False';
							
							break;
						}
					}
					else
					{
						if ($self_url == $tag || $self_url. '/' == $tag )
						{
							$exclude_this_page = 'False';
							
							break;
						}
					}
				}
			}
	}

    if(isset($wccp_pro_settings['exclude_by_post_type']) && !empty($wccp_pro_settings['exclude_by_post_type'])){
        if(in_array(get_post_type(url_to_postid(wccp_pro_get_self_url())), $wccp_pro_settings['exclude_by_post_type'])){
            $exclude_this_page = 'True';
        }
    }
    //print_r(wp_get_post_terms(url_to_postid(wccp_pro_get_self_url())));exit;
    if(isset($wccp_pro_settings['exclude_by_category']) && !empty($wccp_pro_settings['exclude_by_category'])){
        $post_id = url_to_postid(wccp_pro_get_self_url());
        $cat_post = array();
        $taxonomies = get_taxonomies();
        if ( ! empty( $taxonomies ) ) {
            foreach ($taxonomies as $taxonomy) {
                $the_terms = get_the_terms( $post_id, $taxonomy );
                if(!empty($the_terms)){
                    foreach ( $the_terms as $term ) {
                        $cat_post[] = $term->term_id;
                    }
                }
            }
        }
        foreach ($wccp_pro_settings['exclude_by_category'] as $catArrey){
            if(in_array($catArrey, $cat_post)){
                $exclude_this_page = 'True';
            }
        }
    }
	
if($opposite_mode == 'Inactive' && $exclude_this_page == "False") //All next settings will not work when opposite_mode is Active
{
	//Check for URL exclusion

	$self_url = wccp_pro_get_self_url();

	$tag = '';

	$url_exclude_list = '';

	if(isset($wccp_pro_settings['url_exclude_list'])) $url_exclude_list = $wccp_pro_settings['url_exclude_list']; else $url_exclude_list = '';

	// Processes \r\n's first so they aren't converted twice.
	$url_exclude_list = str_replace("\\n", "\n", $url_exclude_list);

	$self_url = trim($self_url);

	$self_url = preg_replace('{/$}', '', $self_url);

	$urlParts = parse_url($self_url);

	if(isset($urlParts['scheme'])) $urlParts_scheme = $urlParts['scheme'] . '://'; else $urlParts_scheme = '';

	if(isset($urlParts['host'])) $urlParts_host = $urlParts['host']; else $urlParts_host = '';

	if(isset($urlParts['path'])) $urlParts_path = $urlParts['path']; else $urlParts_path = '';

	if(isset($urlParts['query'])) $urlParts_query = '?' . $urlParts['query']; else $urlParts_query = '';

	$self_url = $urlParts_scheme . $urlParts_host . $urlParts_path . $urlParts_query;

	$url_exclude_list = wccp_pro_multiexplode(array("," ," ", "\n", "|"),$url_exclude_list);
	
	wccp_pro_debug_to_console($wccp_pro_settings, "Current_url_to_exclude", $self_url);
	
	wccp_pro_debug_to_console($wccp_pro_settings, "url_exclude_list", $url_exclude_list);

	if( !empty($url_exclude_list) )
	{
		for ($i=0; $i <= count($url_exclude_list); $i++)
		{
			if (isset($url_exclude_list[$i]))
			{
				$tag = $url_exclude_list[$i];
				
				$tag = trim($tag);
				
				//$tag = rtrim($tag, "/");
				
				//echo '<br>' . $tag;
			}
			else
			{
				$tag = '';
			}
			if (wccp_pro_contains($tag, '/*')) //Bulk exclusion
			{
				$tag = str_replace("/*", "", $tag);
				
				if (wccp_pro_contains($self_url, $tag))
				{
					$exclude_this_page = 'True';
					
					break;
				}
			}
			else
			{
				if ($self_url == $tag || $self_url. '/' == $tag )
				{
					$exclude_this_page = 'True';
					
					break;
				}
			}
		}
	}
}
return $exclude_this_page;
}
//---------------------------------------------------------------------------------------------
//The wccp_pro_get_current_user_roles
//---------------------------------------------------------------------------------------------
function wccp_pro_get_current_user_roles()
{
	$admincore = '';
	
	if (isset($_GET['page'])) $admincore = $_GET['page'];
	
	if ( ! function_exists( 'wp_get_current_user' ) && (!is_admin() || $admincore == 'wccp-options-pro'))// dont want this include if inside plugin settings or inside admin area
	{
		require_once( ABSPATH . 'wp-includes/pluggable.php' );
	}
	if(function_exists('is_user_logged_in') ) {
		if(is_user_logged_in()) {
			$user = wp_get_current_user();
			$roles = ( array ) $user->roles;
			return $roles; // This returns an array
		}
	} else {
	return array();
	}
}

//---------------------------------------------------------------------------------------------
//Detect page builders, Don't serve actions for live editors & builders
//---------------------------------------------------------------------------------------------
function wccp_pro_is_inside_page_builder()
{
	global $pagenow;

	if ($pagenow != 'post.php' && $pagenow != 'upload.php' && !isset($_GET["elementor-preview"]) && !isset($_GET["rml_folder"]) && !isset($_GET["siteorigin_panels_live_editor"]) && !isset($_GET["preview_id"]) && !isset($_GET["fl_builder"]) && !isset($_GET["et_fb"]))
	{
		return false;
	}
	return true;
}
//---------------------------------------------------------------------------------------------
//Detect string inside array of strings
//---------------------------------------------------------------------------------------------
function wccp_pro_str_contains_in_array(string $search_inside, array $search_for)
{
	foreach ($search_for as $sf)
	{
		if (strpos(strtolower($search_inside), strtolower($sf)) !== FALSE) { return true; }
	}
	return false;
}
//---------------------------------------------------------------------------------------------
//wccp_pro_block_machine_user_agents
//---------------------------------------------------------------------------------------------
function wccp_pro_block_machine_user_agents()
{
	$search_for = array("PrintFriendly", "lumen5", "wkhtmltopdf", "site-shot.com", "pdfmyurl");
	
	if(isset($_SERVER["HTTP_REFERER"]) &&  wccp_pro_str_contains_in_array($_SERVER["HTTP_REFERER"], $search_for))
	{
		die("You are not allowed to open or scan this page content");
	}
	
	if(isset($_SERVER["HTTP_USER_AGENT"]) &&  wccp_pro_str_contains_in_array($_SERVER["HTTP_USER_AGENT"], $search_for))
	{
		die("You are not allowed to open or scan this page content");
	}
}
//---------------------------------------------------------------------------------------------
// Print test information for testing purposes
//---------------------------------------------------------------------------------------------
function print_test_information()
{
	phpinfo(INFO_VARIABLES);
}
//---------------------------------------------------------------------------------------------
// All scripts_injection functions called here
//---------------------------------------------------------------------------------------------
function scripts_injection($wccp_pro_settings)
{
	wccp_pro_main_settings($wccp_pro_settings);
	
	wccp_pro_disable_hot_keys($wccp_pro_settings);
	
	wccp_pro_disable_dev_tools($wccp_pro_settings);
	
	wccp_pro_right_click_premium_settings($wccp_pro_settings);
	
	wccp_pro_css_settings($wccp_pro_settings);
	
	wccp_pro_images_overlay_settings($wccp_pro_settings);
	
	wccp_pro_videos_overlay_settings($wccp_pro_settings);
	
	wccp_pro_nojs_inject($wccp_pro_settings);
	
	wccp_pro_remove_img_urls_with_js($wccp_pro_settings);
}
//---------------------------------------------------------------------------------------------
//The autocomplete_search
//---------------------------------------------------------------------------------------------
add_action('wp_ajax_nopriv_wccp_pro_autocompleteSearch', 'wccp_pro_autocomplete_search');
add_action('wp_ajax_wccp_pro_autocompleteSearch', 'wccp_pro_autocomplete_search');
function wccp_pro_autocomplete_search()
{
    check_ajax_referer('autocompleteSearchNonce', 'security');
    $search_term = sanitize_text_field($_REQUEST['term']);
    if (!isset($_REQUEST['term'])) {
        echo json_encode([]);
    }
    $suggestions = [];
    $query = new WP_Query([
        's' => $search_term,
        'posts_per_page' => -1,
    ]);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $suggestions[] = [
                'id' => get_the_ID(),
                'label' => get_the_title(),
                'link' => get_the_permalink()
            ];
        }
        wp_reset_postdata();
    }
    echo json_encode($suggestions);
    wp_die();
}
//---------------------------------------------------------------------------------------------
// wccp_pro_cache_purge_action_js
//---------------------------------------------------------------------------------------------
function wccp_pro_cache_purge_action_js() { 
global $post;
if($post->ID) $my_permalink = get_permalink($post->ID);
if($_REQUEST['tag_ID']) $my_permalink = get_category_link($_REQUEST['tag_ID']);
?>
  <script type="text/javascript" >
     jQuery("li#wp-admin-bar-WPCCPExclude .ab-item").on( "click", function() {
        var data = {
                      'action': 'example_cache_purge',
					  'permalink': '<? echo $my_permalink; ?>',
                    };
		if(jQuery("li#wp-admin-bar-WPCCPExclude .ab-item").text() !== "Exclusion Done!")
		{
			jQuery("li#wp-admin-bar-WPCCPExclude .ab-item").text('Loading..');
			/* since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php */
			jQuery.post(ajaxurl, data, function(response) {
			   jQuery("li#wp-admin-bar-WPCCPExclude .ab-item").text('Exclusion Done!');
			});
		}
       
      });
  </script> <?php
}

//---------------------------------------------------------------------------------------------
// wccp_pro_example_cache_purge_callback
//---------------------------------------------------------------------------------------------
function wccp_pro_example_cache_purge_callback() {
    /* You cache purge logic should go here. */
	global $wccp_pro_settings;
	$wccp_pro_settings["url_exclude_list"] = $wccp_pro_settings["url_exclude_list"] . "\n" . $_REQUEST['permalink'];
	update_blog_option_single_and_multisite( 'wccp_pro_settings' , $wccp_pro_settings );
    $response = $wccp_pro_settings["url_exclude_list"];
    echo ($response);
    wp_die(); /* this is required to terminate immediately and return a proper response */
}
/* Here you hook and define ajax handler function */
add_action( 'wp_ajax_example_cache_purge', 'wccp_pro_example_cache_purge_callback' );
//---------------------------------------------------------------------------------------------
//Add plugin settings link to Plugins page
//---------------------------------------------------------------------------------------------
function wccp_pro_plugin_add_settings_link( $links ) {

	$settings_link = '<a href="admin.php?page=wccp-options-pro">' . __( 'Settings' ) . '</a>';
	
	array_push( $links, $settings_link );
	
	$network_dir_append = "";
	
	If (is_multisite()) $network_dir_append = "network/";
	
	$settings_link2 = sprintf('<a href="%s"><b style="color:#f18500">More Plugins</b></a>', admin_url( $network_dir_append . 'plugin-install.php?s=wp-buy&tab=search&type=author' ));
	
	array_push( $links, $settings_link2 );
	
	return $links;
}
$prefix = is_network_admin() ? 'network_admin_' : '';

//---------------------------------------------------------------------------------------------
//Function to get self url
//---------------------------------------------------------------------------------------------
function wccp_pro_get_self_url()
{ 
    return get_site_url().$_SERVER['REQUEST_URI'];
}

//---------------------------------------------------------------------------------------------
//Multiexplode function
//---------------------------------------------------------------------------------------------
function wccp_pro_multiexplode($delimiters,$string)
{   
	if(is_array($string))
		$ready = implode(",", $string); //Convert any array to comma_separated string
	else
		$ready = $string;
	$ready = str_replace(" ", "", $ready);
	$ready = str_replace($delimiters, $delimiters[0], $ready);//Replace all string delimiters with the first delimiter in the array
	$ready = str_replace($delimiters[0].$delimiters[0], $delimiters[0], $ready);
	$launch = explode($delimiters[0], $ready);
	return  $launch;
}

//---------------------------------------------------------------------------------------------
//Add nojs action
//---------------------------------------------------------------------------------------------
function wccp_pro_nojs_inject($wccp_pro_settings)
{
	$cook = "";
	
	$msg = "Sorry,, You can not view this website when JaveScript is disabled, Thank you";
	
	If ($wccp_pro_settings['no_js_action_massage'] != "") $msg = $wccp_pro_settings['no_js_action_massage'];
	
	if (isset($_GET['cook']))
	{
		if($_GET['cook'] == "wccp_h_s") //We don't want this merge to work inside plugin admin panel
		{
			die($msg);//Set default value for any unexisted key
			
			$cook = "wccp_h_s";
		}
	}
	
	if ($wccp_pro_settings['no_js_action'] == 'Hide content' && $cook == "")
	{
		if (!isset($_SESSION["no_js"]))
		{
			$pluginsurl = plugins_url( '', __FILE__ );
			
			$nojs_page_url = $pluginsurl . '/no-js.php';
			
			$referrer = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			
			$nojs_page_url = $nojs_page_url . "?referrer=" .$referrer;
			
			$st = "
				<!-- Redirect to another page (for no-js support) -->
				<noscript><meta http-equiv=\"refresh\" content=\"0;url=$nojs_page_url?cook=wccp_h_s\"></noscript>"
				. '<noscript><style type="text/css">
						body { display:none; }
					</style>
				</noscript>
				<!-- Show a message -->
				<noscript>You dont have javascript enabled! Please enable it!</noscript>
			';

			echo $st;
		}
	}
}

//---------------------------------------------------------------------------------------------
//Replace image urls with nothing
//---------------------------------------------------------------------------------------------
function wccp_pro_replace_image_urls( $content ) {

	global $post;
	
	global $wccp_pro_settings;
	
	$dw_position = $wccp_pro_settings['dw_position'];
	$dw_text = $wccp_pro_settings['dw_text'];
		$dw_text = str_replace(" ","+",$dw_text);
	$dw_r_text = $wccp_pro_settings['dw_r_text'];
		$dw_r_text = str_replace(" ","+",$dw_r_text);
	$dw_font_color = $wccp_pro_settings['dw_font_color'];
	$dw_r_font_color = $wccp_pro_settings['dw_r_font_color'];
	$dw_font_size_factor = $wccp_pro_settings['dw_font_size_factor'];
	$dw_r_font_size_factor = $wccp_pro_settings['dw_r_font_size_factor'];
	$dw_text_transparency = $wccp_pro_settings['dw_text_transparency'];
	$dw_rotation = $wccp_pro_settings['dw_rotation'];
	$dw_imagefilter = $wccp_pro_settings['dw_imagefilter'];
	$dw_signature = $wccp_pro_settings['dw_signature'];
		$dw_signature = str_replace(" ","+",$dw_signature);
	$dw_logo = $wccp_pro_settings['dw_logo'];
	
	$dw_query = "type=dw&position=$dw_position&text=$dw_text&font_color=$dw_font_color&r_text=$dw_r_text&r_font_color=$dw_r_font_color&font_size_factor=$dw_font_size_factor&r_font_size_factor=$dw_r_font_size_factor&text_transparency=$dw_text_transparency&rotation=$dw_rotation&imagefilter=$dw_imagefilter&signature=$dw_signature&stamp=$dw_logo";
	
	$dw_query = str_replace("#","%23",$dw_query);
	
	$pluginsurl = plugins_url( '', __FILE__ );

	$regexp = '<img[^>]+src=(?:\"|\')\K(.[^">]+?)(?=\"|\')';

	//Watermark images inside the content
	if(preg_match_all("/$regexp/", $content, $matches, PREG_SET_ORDER))
	{
		if( !empty($matches) )
		{
			for ($i=0; $i <= count($matches); $i++)
			{
				if (isset($matches[$i]) && isset($matches[$i][0]))
				{
					$img_src = $matches[$i][0];
				}
				else
				{
					$img_src = '';
				}
				$url_parser = parse_url($img_src); //Array [scheme] => http    [host] => www.example.com    [path] => /foo/bar    [query] => hat=bowler&accessory=cane
				
				$img_file_path = $url_parser['path'];
				
				//$http = $pluginsurl . "/watermark.php?w=watermarksaveas.png&p=c&q=90&src=";
				
				$http = $pluginsurl . '/watermark.php?'. $dw_query . '&src=';

				$encrypted_img_src = $http . $img_file_path;

				$content = str_replace($img_src,$encrypted_img_src,$content);
			}
		}
	}
	$content = str_replace(']]>', ']]&gt;', $content);

return $content;
}
if (isset($_SESSION["no_js"]))
{
	add_filter( 'the_content', 'wccp_pro_replace_image_urls');
}
//---------------------------------------------------------------------------------------------
// wccp_pro_completely_disable_any_browser_caching
//---------------------------------------------------------------------------------------------
function wccp_pro_completely_disable_any_browser_caching() 
{
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}
//---------------------------------------------------------------------------------------------
// wccp_pro_
//---------------------------------------------------------------------------------------------
function wccp_pro_read_options_from_db($option = '')
{
	$wccp_pro_settings = array();
	
	if(is_multisite())
	{
		$id = get_current_blog_id();
		
		$wccp_pro_settings = get_blog_option($id, $option);
	}
	else
	{
		$wccp_pro_settings = get_option($option);
	}
	
	if(!is_array($wccp_pro_settings))
	{
		$obj = new wccp_pro_controls_class();
		
		$obj->wccp_pro_save_settings(true);
		
		$wccp_pro_settings = $obj->wccp_pro_read_options('wccp_pro_settings');
	}
	
	return $wccp_pro_settings;
}

//---------------------------------------------------------------------------------------------
// wccp_pro_
//---------------------------------------------------------------------------------------------
function wccp_pro_advanced_get_link()
{
	$link_to_watermark = esc_url_raw($_POST['link']);
	
	//$ahc_data = wp_remote_get($link_to_watermark);
	
	$ahc_data = true;
	
	if ( $ahc_data == false )
	{
		wp_send_json_error();
	} else {
		wp_send_json_success( 'success' );
	}
	
    return json_decode(wp_remote_retrieve_body($ahc_data));
}
?>