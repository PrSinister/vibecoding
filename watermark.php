<?php
$watermark_caching = "checked";
$watermark_type = "dw";
$watermark_position = "center-center";
$watermark_r_text = "www.plugin-labs.com";
$r_font_size_factor = "55";
$watermark_text = "WATERMARKED";
$font_size_factor = "90";
$pure_watermark_stamp_image = '';
$margin_left_factor = "98";
$margin_top_factor = "98";
$watermark_color = "#000000";
$watermark_r_color = "#efefef";
$watermark_transparency = "65";
$watermark_rotation = "40";
$watermark_imagefilter = "None";
$watermark_signature = "This image is protected";
$font = './fonts/LiberationSans-Regular.ttf';
/* This used to calculate the excution time for any script
$start = microtime(true);
echo $time_elapsed_secs;
*/
include "watermarking-parameters.php";
include "word2uni.php";
/* 
if (isset($_GET['rr']))
	$rr= $_GET['rr'];
else
	$rr = "no-refer";
if (isset($_GET['rr2']))
	$rr2= $_GET['rr2'] ;
else
	$rr2 = "no-user-agent";

$watermark_signature = $rr2 ;
$watermark_caching = "no";
*/
//phpinfo();
if (strpos(@ini_get('disable_functions'), 'set_time_limit') === false) { 
	if(function_exists('set_time_limit'))
	{
		@set_time_limit(60);
	}
}

$url = $_SERVER['HTTP_HOST'];

//$url = ir_get_domain($url);

$pure_src = $_GET['src'];

$http = "http://";

if (wccp_pro_isSSL()) $http = "https://";

$arr = explode("-", $watermark_position);

$watermark_position_x = $arr[1];

$watermark_position_y = $arr[0];

$watermark_stamp_image = $pure_watermark_stamp_image . '?x=wccp_pro_watermark_pass';

$pure_name = str_replace("/", "-", $pure_src);

$abs_src = $http . $url . $pure_src . '?x=wccp_pro_watermark_pass';

$abs_src = $baseurl . $pure_src . '?x=wccp_pro_watermark_pass';

$pos = strrpos($pure_src, "."); //Find the position of the last occurrence of a substring in a string

$str = substr($pure_src,($pos + 1));

$relative_src = wccp_pro_get_image_relative_path($pure_src ,$upload_dir);

//---------------------------------------------------------------------
// Caching code start
//---------------------------------------------------------------------
global $watermark_caching;

if($watermark_caching == "checked")
{
	$cachefile = $upload_dir. '/wccp_pro_watermarked_images/'.$pure_name;

	if (file_exists($cachefile) && ! empty($pure_name)) {
		
		if($str == 'jpg' || $str == 'jpeg')
		{
			header("Content-type: image/jpeg");	 //header("Content-type: text/html");
		}

		if($str == 'png')
		{
			header("Content-type: image/png");	//header("Content-type: text/html");
		}
		
		if($str == 'webp')
		{
			header("Content-type: image/webp");	 //header("Content-type: text/html");
		}
		
		readfile($cachefile);
		
		exit;
	}
}
//---------------------------------------------------------------------
// In case if imagecreatefromwebp() not exist, but the image exists
//---------------------------------------------------------------------
if($str == 'webp' & !function_exists('imagecreatefromwebp'))
	{
		global $relative_src;
		
		if (file_exists($relative_src))
		{
			header("Content-type: image/webp");	//header("Content-type: text/html");
			
			readfile($relative_src);
			
			exit;
		}
	}
//---------------------------------------------------------------------
// Caching code end
//---------------------------------------------------------------------

$image = false;

error_reporting(-1);

ini_set('display_errors', 'On');

if($watermark_signature == '' && $watermark_text == '' && $watermark_r_text == '' & $pure_watermark_stamp_image == '')
{
	$watermark_signature = "You have to save changes inside the plugin settings page first";
	
	$watermark_text = 'WATERMARKED';
	
	$watermark_r_text = 'Protected image';
}
if($font_size_factor == '') $font_size_factor = 80;

if($r_font_size_factor == '') $r_font_size_factor = 50;

if($watermark_transparency == '') $watermark_transparency = 80;

if($watermark_rotation == '') $watermark_rotation = 40;

if($watermark_position_x == '' && $watermark_position_y == '') {$watermark_position_x = "center"; $watermark_position_y = "center";}


try {
	if($str == 'jpg' || $str == 'jpeg')
	{
		header("Content-type: image/jpeg");	 //header("Content-type: text/html");
		if($relative_src != 'None') $image = imagecreatefromjpeg($relative_src);
	}

	if($str == 'gif')
	{
		header("Content-type: image/gif");	//header("Content-type: text/html");
		if($relative_src != 'None') $image = imagecreatefromgif($relative_src);
	}

	if($str == 'png')
	{
		header("Content-type: image/png");	//header("Content-type: text/html");
		if($relative_src != 'None')
		{
			$image = imagecreatefrompng($relative_src);
			
			imagesavealpha($image, true); //saving transparency
		}
	}
	
	if($str == 'webp' & function_exists('imagecreatefromwebp'))
	{
		header("Content-type: image/webp");	//header("Content-type: text/html");
		
		if($relative_src != 'None')
		{
			$image = imagecreatefromwebp($relative_src);
			
			imagesavealpha($image, true); //saving transparency
		}
	}
	
	if($relative_src == 'None') $image = image_create_from_any($abs_src);

} catch (Exception $e) {

	$msg_not_found = 'not found ' .  $e->getMessage();
}

$HTTP_ACCEPT_VALUE = '';

if(isset($_SERVER['HTTP_ACCEPT']))
{
	$HTTP_ACCEPT_VALUE = $_SERVER['HTTP_ACCEPT'];
}else
{
	$HTTP_ACCEPT_VALUE = 'html';
}

$H_T = strpos($HTTP_ACCEPT_VALUE, 'html');

if(isset($_SERVER['HTTP_REFERER'])) $referrerurl = $_SERVER['HTTP_REFERER'];

if(isset($referrerurl)){

	$is_google = strpos($referrerurl, 'google.com');

	//Redirected from google preview
	if($is_google && strpos($HTTP_ACCEPT_VALUE, 'html')){

		//$watermark_image = "watermarkgoogle.png";
	}

	//open from direct link
	if($referrerurl == '' && strpos($HTTP_ACCEPT_VALUE, 'html')){

		//$watermark_image = "watermarknorefere.png";
	}

	//preview inside google images search
	if($referrerurl == '' && strpos($HTTP_ACCEPT_VALUE, 'png')){

		//$watermark_image = "watermarkgoogle.png";
	}
}

$tw = @imagesx($image);

if(!$tw){
	
	//---------------------------------------------------------------------
	// This is what happen when image is not found
	//---------------------------------------------------------------------

    // Size of the font

    $fontSize = 14;

    // Height of the image

    $height = 66;

    // Width of the image

    $width = 600;

    // Text

    $msg_not_found = 'Could not get image!';

    $img_handle = imagecreate ($width, $height) or die ("Cannot Create image");

    // Set the Background Color RGB

    $backColor = imagecolorallocate($img_handle, 255, 255, 255);

    // Set the Text Color RGB

    $txtColor = imagecolorallocate($img_handle, 20, 92, 137); 

    $textbox = @imagettfbbox($fontSize, 0, $font, $msg_not_found) or die('Error in imagettfbbox function');

    $x = ($width - $textbox[4])/2;

    $y = ($height - $textbox[5])/2;

    imagettftext($img_handle, $fontSize, 0, $x, $y, $txtColor, $font , $msg_not_found) or die('Error in imagettftext function');

    header('Content-Type: image/jpeg');

    imagejpeg($img_handle,NULL,100);

    imagedestroy($img_handle);
}
if($tw)
{
	$th = imagesy($image);

    $thumbWidth = 1900;  ///////here update the width of the hotlinked images//////

    if($tw <= $thumbWidth){

        $thumbWidth = $tw;
    }

    $thumbHeight = $th * ($thumbWidth / $tw);

    $thumbImg = imagecreatetruecolor($thumbWidth, $thumbHeight);
		
	imagealphablending($thumbImg, false); //resize pngs with transparent backgrounds
	    
    // Height of the image

    $height = $thumbHeight;

    // Width of the image

    $width = $thumbWidth;
}

if($tw <= 150)
{
	// Apply image new size - resizing
	resize_image();
	
	// Generate the image and view it
	generate_image();
}

if($tw <= 300 && $tw>150)
{
	// Add watermark text
	apply_watermak_text();
	
	// Apply image new size - resizing
	resize_image();
	
	// Generate the image and view it
	generate_image();
}


if($tw > 300)
{
	// Apply watermark effect (filter)
	apply_watermak_effect();

	// Apply watermark signature text
	apply_watermak_signature();

	// Add watermark text
	apply_watermak_text();

	// Add Watermark repeated Text function
	apply_watermak_repeated_text();
	
	// Apply watermark logo
	apply_watermak_logo();
	
	// Apply image new size - resizing
	resize_image();
	
	// Generate the image and view it
	generate_image();
}

function resize_image()
{
	global $thumbImg, $image, $thumbWidth, $thumbHeight, $tw, $th, $str, $pure_name;
	
	if (function_exists('imagecopyresampled'))
	{
		imagecopyresampled($thumbImg, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $tw, $th); //imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
	
	} else
	{
		imagecopyresized($thumbImg, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $tw, $th);
	}	
}

function generate_image()
{
	global $thumbImg, $image, $str, $upload_dir, $pure_name, $upload_dir, $watermark_caching;;

	$cachedir = $upload_dir. '/wccp_pro_watermarked_images';
	
	$use_cache = false;

	if($cachedir !== false AND is_dir($cachedir) AND is_writable($cachedir) AND $watermark_caching == "checked")
		{
			$use_cache = true;
		}

    if($str == 'png')
	{
        imagealphablending($thumbImg,TRUE);
		
		imagesavealpha($thumbImg,true);

        if($use_cache) imagepng($thumbImg, $upload_dir. '/wccp_pro_watermarked_images/'.$pure_name, 9, PNG_ALL_FILTERS); //Save image to local cache storage
		
		imagepng($thumbImg, NULL, 9, PNG_ALL_FILTERS); //Render image to browser
    }
	
	if($str == 'gif')
	{
        $colorTransparent = imagecolortransparent($image);

        imagefill($thumbImg, 0, 0, $colorTransparent);

        imagecolortransparent($thumbImg, $colorTransparent);
    }

    if($str == 'jpg' || $str == 'jpeg' || $str == 'webp')
	{
        if($use_cache) imagejpeg($thumbImg, $upload_dir. '/wccp_pro_watermarked_images/'.$pure_name, 70); ////Jpeg quality from 0 - 100// + //Save image to local cache storage
		
		//else
		imagejpeg($thumbImg, NULL, 70); ////Here to generate image withot save it to cache directory, The jpeg quality from 0 - 100
    }

    if($str == 'gif')
	{
        imagegif($thumbImg);
    }
	
	// Free memory after output
    imagedestroy($thumbImg);
}

//---------------------------------------------------------------------
// Add Watermark repeated Text function
//---------------------------------------------------------------------
function apply_watermak_repeated_text()
{
	global $watermark_transparency, $thumbImg, $watermark_rotation,$watermark_r_text, $watermark_r_color, $tw, $th, $font, $r_font_size_factor, $image;
	
	$height = $th;
		
	$width = $tw;
	
	$watermark_printed_text = $watermark_r_text;// Set the Text
	
	$watermark_printed_text = @text2uni($watermark_printed_text);
	
	$text_transparency = $watermark_transparency * 1.27;//number between 0 - 127
	
	$color_ar = html2rgb($watermark_r_color); //Here we control font color
	
	$rotation = $watermark_rotation;//+ values for counter-clockwise rotation
	
	$txtColor_transparent = imagecolorallocatealpha($thumbImg, $color_ar[0], $color_ar[1], $color_ar[2], $text_transparency);// Set the Text Color RGB
	
	$small_fontsize = $height/25 * $r_font_size_factor/100;
	
	for($w = 0 ; $w < $width ; $w = $w+($width * 0.2))
	{
		for($h = 0 ; $h < $height ; $h = $h+($height * 0.2))
		{
			imagettftext($image, $small_fontsize, $rotation, $w, $h, $txtColor_transparent, $font , $watermark_printed_text) or die('Error in imagettftext function'); //the 1st number after fontsize is rotation *******
		}
	}
}

//---------------------------------------------------------------------
// Add Watermark main Text function
//---------------------------------------------------------------------
function apply_watermak_text()
{
	global $watermark_text, $watermark_transparency, $watermark_color, $thumbImg, $watermark_rotation, $tw, $th, $font, $font_size_factor, $watermark_position_x, $watermark_position_y, $image, $str;
	
	if($str == 'gif') return;
	
	$height = $th;
		
	$width = $tw;
	
	$watermark_printed_text = $watermark_text;// Set the Text
    
    $text_transparency = $watermark_transparency * 1.27;//number between 0 - 127
    
	$color_ar = html2rgb($watermark_color); //Here we control font color
	
	$txtColor_transparent = imagecolorallocatealpha($thumbImg, $color_ar[0], $color_ar[1], $color_ar[2], $text_transparency);// Set the Text Color RGB
    
    $rotation = $watermark_rotation;//+ values for counter-clockwise rotation
	
	$watermark_printed_text = @text2uni($watermark_printed_text);
	
	$fontSize = $height/8 * $font_size_factor/100;
	
	if($watermark_position_x == "center" && $watermark_position_y == "center")
	{
		$rotation = $watermark_rotation;
	}
	else
	{
		$rotation = 0;
	}

    $textbox = imagettfbbox($fontSize, -$rotation, $font, $watermark_printed_text) or die('Error in imagettfbbox function');
	
    $x = ($width - $textbox[4])/2;

    $y = ($height - $textbox[5])/2;
        
    $watermark_text_position_y = $watermark_position_y;
	
    switch ($watermark_text_position_y) {
    case "top":
		$y = ($fontSize + $textbox[3]) + $height*.02;
        break;
    case "center":
		$y = ($height - $textbox[5])/2;
        break;
    case "bottom":
		$y = $height - $fontSize/2  - $height*.02;
        break;
	}
	
	$watermark_text_position_x = $watermark_position_x;
	
    switch ($watermark_text_position_x) {
    case "left":
		 $x = $textbox[0] + $width*.02;
        break;
    case "center":
		$x = ($width - $textbox[4])/2;
        break;
    case "right":
		$x = ($width - $textbox[4])  - $width*.02;
        break;
	}
	
    imagettftext($image, $fontSize, -$rotation, $x, $y, $txtColor_transparent, $font , $watermark_printed_text) or die('Error in imagettftext function'); //the 1st number after fontsize is rotation *******
}

//---------------------------------------------------------------------
// Watermark Effect function
//---------------------------------------------------------------------
function apply_watermak_effect()
{
	global $watermark_imagefilter, $image, $str;
	
	if($str == 'gif') return;
	
	$watermark_effect = $watermark_imagefilter;
    
    if ($watermark_effect == 'Blur'){
    
		for ($x=1; $x<=25; $x++) imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR); /////////Here control the image Blur Effect/////////WORKING
    }
    
    if ($watermark_effect == 'Grayscale'){
    
		imagefilter($image, IMG_FILTER_GRAYSCALE);  /////////Here control the image GRAYSCALE or Not/////////WORKING
    }
    
    if ($watermark_effect == 'Negate'){
    
    	imagefilter($image, IMG_FILTER_NEGATE);  /////////Negate the image colores عكس ألوان الصورة/////////WORKING
    }
    
    if ($watermark_effect == 'Britness'){
    
    	imagefilter($image, IMG_FILTER_BRIGHTNESS, (-80));  /////////Here control the image britness/////////WORKING
    }
    //////////For more see php.net/manual/en/function.imagefilter.php//////////
}


//---------------------------------------------------------------------
// Watermark Logo function
//---------------------------------------------------------------------
function apply_watermak_logo()
{	
	global $pure_watermark_stamp_image, $thumbImg, $watermark_stamp_image, $margin_left_factor, $margin_top_factor, $width, $height, $image;
	
	global $th , $tw;
	
	$height = $th;
		
	$width = $tw;
	
	$stamp = false;
	
	if ($pure_watermark_stamp_image != '')
	{
		// Load the stamp and the photo to apply the watermark to
		$dot_pos = strrpos($pure_watermark_stamp_image, "."); //Find the position of the last occurrence of a substring in a string

		$stamp_extension = substr($pure_watermark_stamp_image,($dot_pos + 1));
		
		if($stamp_extension == 'png' || $stamp_extension == 'jpg' || $stamp_extension == 'jpeg' || $stamp_extension == 'gif')
		{
		
			$stamp = image_create_from_any($watermark_stamp_image);
		
		}
		
		if(!$stamp) return;
		
		//Resample the stamp
		
		$stamp_size_percent = 0.27;
		
		$stamp_width = 128;
		
		$stamp_height = 128;
		
		$stamp_width = @imagesx($stamp);
		
		$stamp_height = @imagesy($stamp);
		
		if($width <= $height)
			$new_stamp_width_factor = $width;
		else
			$new_stamp_width_factor = $height;
		
		$new_stamp_width = $new_stamp_width_factor * $stamp_size_percent;
		
		if($stamp_width == 0) $stamp_width = 128;
		
		if($new_stamp_width == 0) $new_stamp_width = 128;
		
		if($stamp_height == 0) $stamp_height = 128;
		
		$new_stamp_height = $stamp_height / ($stamp_width / $new_stamp_width);
		
		$resized_stamp = imagecreatetruecolor($new_stamp_width, $new_stamp_height);
		
		imagealphablending($resized_stamp, false); //resize pngs with transparent backgrounds
	
		imagecopyresampled($resized_stamp, $stamp, 0, 0, 0, 0, $new_stamp_width, $new_stamp_height, $stamp_width, $stamp_height);

		$sx = @imagesx($resized_stamp);
		
		$sy = @imagesy($resized_stamp);
		
		// Set the margins for the stamp and get the height/width of the stamp image
		$margin_left = 10;
		
		$margin_top = 20;

		$margin_left = $width * $margin_left_factor/100 - ($sx * ($margin_left_factor/100));
		
		if($margin_left < 1 ) $margin_left = 1;
		
		$margin_top = $height * $margin_top_factor/100 - ($sy * ($margin_top_factor/100));
		
		if($margin_top < 1 ) $margin_top = 1;
		
		$ix = $width; //imagesx($image)
		
		$iy = $height; //imagesy($image)

		// Copy the stamp image onto our photo using the margin offsets and the photo
		// width to calculate positioning of the stamp.
		
		imagecopy($image, $resized_stamp, $margin_left, $margin_top, 0, 0, $sx, $sy);
		
		/*
		bool imagecopy ( resource $dst_im , resource $src_im , int $dst_x , int $dst_y , int $src_x , int $src_y , int $src_w , int $src_h )

		Copy a part of src_im onto dst_im starting at the x,y coordinates src_x, src_y with a width of src_w and a height of src_h.
		The portion defined will be copied onto the x,y coordinates, dst_x and dst_y. */
	}
}

//---------------------------------------------------------------------
// Watermark Signature function
//---------------------------------------------------------------------
function apply_watermak_signature()
	{
		global $watermark_signature, $tw, $th, $watermark_transparency,$thumbImg, $font, $image, $str;
		
		if($str == 'gif' || $watermark_signature == '') return;
		
		$height = $th;
		
		$width = $tw;
		
		$signature_text = $watermark_signature;
		
		$signature_text = @text2uni($signature_text);
		
		//global $abs_src;
		//$signature_text = $abs_src;
		
		$referrerurl = '';
		
		//if(isset($_SERVER['HTTP_ACCEPT'])) $referrerurl = $_SERVER['HTTP_ACCEPT'];
		
		//$signature_text = $signature_text. "\r\n" .$referrerurl;

		$transparent_back_top = imagecolorallocatealpha($image, 255, 255, 255, 20);
		
		$transparent_back = imagecolorallocatealpha($image, 255, 255, 255, 70);
		
		$x1 = 0;
		
		$y1 = $height * .85;
		
		$x2 = $width;
		
		$y2 = $y1 + ($height/11);
		
		$fontSize = ($height/11)/3;
		
		$back1_height = ($height/11)/13;
		
		imagefilledrectangle($image, $x1, $y1-$back1_height, $x2, $y2-($height/11), $transparent_back_top);
		
		imagefilledrectangle($image, $x1, $y1, $x2, $y2, $transparent_back);
		
		imagesavealpha($image, TRUE);
		
		$text_transparency = $watermark_transparency * 1.27;//number between 0 - 127
		
		$color_ar = html2rgb('#000000');
		
		$txtColor_transparent = imagecolorallocatealpha($thumbImg, $color_ar[0], $color_ar[1], $color_ar[2], $text_transparency);
		
		$rotation = 0;//+ values for counter-clockwise rotation
		
		$x = $x1 + 35;
		
		$y = $y2 - $fontSize;
		
		imagettftext($image, $fontSize, $rotation, $x, $y, $txtColor_transparent, $font , $signature_text) or die('Error in imagettftext function'); //the 1st number after fontsize is rotation *******
	}
//---------------------------------------------------------------------
//Convert HTML Colors into RGB Colors
//---------------------------------------------------------------------
function html2rgb($color)
{
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}

//---------------------------------------------------------------------
//Get the domain name without www
//---------------------------------------------------------------------
function ir_get_domain($url)
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
//---------------------------------------------------------------------
//Get the domain name without www
//---------------------------------------------------------------------
function wccp_pro_isSSL() {
  return
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || $_SERVER['SERVER_PORT'] == 443;
}
//---------------------------------------------------------------------
//Get wccp_pro_get_image_relative_path
//---------------------------------------------------------------------
function wccp_pro_get_image_relative_path($watermark_file, $upload_dir)
{	
	//Example $upload_dir >>   /home3/main-host-holder/website-folder.com/

	if(isset($watermark_file) && isset($upload_dir))
	{
		$watermark_file = $upload_dir . '/' . $watermark_file;
		
		$watermark_file = str_replace("//", "/", $watermark_file);
		
		$watermark_file = str_replace("wp-content/uploads/wp-content/uploads/", "wp-content/uploads/", $watermark_file);
	}
	else
	{
		$watermark_file = 'None';
	}
	return $watermark_file;
}

//---------------------------------------------------------------------
//Get the domain name without www
//---------------------------------------------------------------------
function image_create_from_any($abs_src)
{
	//echo $abs_src;
	//exit();
	$options = array( CURLOPT_RETURNTRANSFER => true, // return web page
		CURLOPT_HEADER => false, // don't return headers 
		CURLOPT_FOLLOWLOCATION => false, // follow redirects 
		CURLOPT_AUTOREFERER => true, // set referer on redirect 
		CURLOPT_CONNECTTIMEOUT => 5, // timeout on connect 
		CURLOPT_TIMEOUT => 5, // timeout on response 
		CURLOPT_MAXREDIRS => 0, // stop after 10 redirects 
		); 

		$ch = curl_init( $abs_src ); 
		curl_setopt_array( $ch, $options ); 
		$image = curl_exec( $ch );
		

		//$err = curl_errno( $ch ); // helpful for troubleshooting 
		//$errmsg = curl_error( $ch ); // helpful for troubleshooting 
		curl_close( $ch );

		$image = @imagecreatefromstring($image); // to build as image
		
		if($image) imagesavealpha($image,true);
		
		return $image;
}
?>