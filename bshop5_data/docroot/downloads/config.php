<?php
/*#################################################################
# Script Name 	: config.php
# Description 	: Page which contains the constants to be used in the site.
# Coded by 		: Sny
# Created on	: 03-Dec-2007
# Modified by	: Sny
# Modified On	: 11-Dec-2007
#################################################################*/
/*######################################################################
# Include Necessary Files*/
//require_once('classes/browser.php');		// Page which holds the class for browser
//require('classes/vImage.php');				// page which holds the class for image verification
//require('classes/paging.php'); 				// page which holds the class for paging
//$br 	= new Browser; // Creating an object of the browser class
//$vImage = new vImage();  // Creating an object of the image verification class

$from_iphone_app = false;  //this is to check the application from iphone or not
$iphone_session_id = ''; // initializing the iphone session variable to blank

// File which holds the db details and db connection details
include "config_db.php";
define('SECURED_URL','www.bsecured.co.uk');
define('SECURED_URL_ALIAS','bsecured.co.uk');

//Variable for Max and Min Length
$short = 50;
$medium = 100;
$long = 150;

// Sort out our hostname
	$protectedUrl = FALSE;
	$ecom_hostname = strtolower($_SERVER['HTTP_HOST']);
	if (($ecom_hostname == SECURED_URL or $ecom_hostname == SECURED_URL_ALIAS) && $_SERVER["HTTPS"] == "on")
	{
			$temp = explode("/",$_SERVER['REQUEST_URI']);
	 		$ecom_hostname = $temp[1];
			$protectedUrl = TRUE;

	}

//#Getting site details
$sql_site = "SELECT 
			a.site_id,a.site_domain,a.clients_client_id,a.site_status,a.themes_theme_id,a.site_email,
			a.console_levels_level_id,a.site_title,a.meta_verificationcode,a.is_meta_verificationcode,
			a.in_web_clinic,a.site_intestmod,site_3dsecured,a.is_google_urchinwebtracker_code,
			a.is_google_webtracker_code,is_google_adword_checkout,
			google_webtracker_code,google_webtracker_urchin_code,
			google_adword_conversion_id,google_adword_conversion_language,
			google_adword_conversion_format,google_adword_conversion_color,
			google_adword_conversion_label,advanced_seo,webtracker_account_id,
			b.path,b.themename,a.site_charset,b.allow_special_category_details,
			a.is_yahoometa_verificationcode,a.yahoo_meta_verificationcode,
			a.is_msnmeta_verificationcode, a.msn_meta_verificationcode,
			site_activate_invoice,site_footer_scripts,site_checkout_scripts,is_twitter_account,
			site_twitter_account_id,site_country_as_textbox,
			site_activate_invoice,site_allpricewithtax,site_delivery_location_country_map,
			is_google_webtracker_ecom,google_ecomtracker_code,a.mobile_themes_theme_id,
			site_fb_enable,site_fb_appid,site_fb_secretkey,site_grid_enable,a.selfssl_active 
		FROM 
				sites a,themes b
		WHERE 
			(a.site_domain like '".$ecom_hostname."' 
			OR a. site_domain_alias like '".$ecom_hostname."' )
			AND a.themes_theme_id = b.theme_id 
		LIMIT 
			1";
$res_site = $db->query($sql_site);
list($ecom_siteid, $ecom_hostname, $ecom_client, $ecom_status,$ecom_themeid,$ecom_email,$ecom_levelid,$ecom_title,$ecom_metacode,$ecom_ismetacode,$ecom_webclinic,$ecom_testing,$ecom_3dsecured,$ecom_isurchinwebtracker,$ecom_iswebtracker,$ecom_isadword,$ecom_webtrackercode,$ecom_urchinwebtrackercode,$ecom_adword_conversionid,$ecom_adword_conversionlanguage,$ecom_adword_conversionformat,$ecom_adword_conversioncolor,$ecom_adword_conversionlabel,$ecom_advancedseo,$ecom_webtracker_account_id,$ecom_themepath,$ecom_themename,$ecom_charset,$ecom_allow_special_category_details,$ecom_isyahoometacode,$ecom_yahoometacode,$ecom_ismsnmetacode,$ecom_msnmetacode,$ecom_activate_invoice,$ecom_footer_script,$ecom_success_script,$ecom_istwitter,$ecom_twitteraccountId,$ecom_is_country_textbox,$ecom_site_activate_invoice,$ecom_allpricewithtax,$ecom_site_delivery_location_country_map,$ecom_isecomtracker,$ecom_ecomtrackercode,$ecom_mobilethemeid,$ecom_fb_enable,$ecom_fb_appid,$ecom_fb_secretkey,$ecom_gridenable,$ecom_selfssl_active) = $db->fetch_array($res_site);


if($ecom_selfssl_active==1)
{
	$ecom_selfhttp = "https://";
}
else
{
	$ecom_selfhttp = "http://";
}

/* thi section for the mobile device detect*/
	//22 Nov 2011 Start
	include 'includes/mobile_device_detect.php';
	$load_mobile_theme_arr = mobile_device_detect();
    $ecom_load_mobile_theme = false;
	$ecom_show_viewnormalweb = ($_REQUEST['show_viewnormalweb'])?$_REQUEST['show_viewnormalweb']:$_SESSION['view_normalwebsite'];


	if($ecom_show_viewnormalweb==1) // case if clicked to view the normal website from mobile version
	{
		if($ecom_mobilethemeid)
			$ecom_mobilethemeid = 0;
		$_SESSION['view_normalwebsite'] = 1;	
	}
if($ecom_siteid==109)
{
	//$_SERVER['REMOTE_ADDR']!='59.95.72.139' AND 
	/*if($_SERVER['REMOTE_ADDR']!='117.247.106.151' AND $_SERVER['REMOTE_ADDR']!='182.72.159.170' AND $_SERVER['REMOTE_ADDR']!='202.191.171.154')
	{
		$ecom_mobilethemeid =0;
		$ecom_load_mobile_theme = false;
	}*/
}
	
	if($ecom_mobilethemeid)
	{
		// Check whether mobile version is to be loaded
		if($load_mobile_theme_arr[0]==1) // case if mobile theme is to be loaded
		{
			// get the name of the mobile theme and store it in the 
			$sql_theme = "SELECT themename,path 
							FROM 
								themes 
							WHERE 
								theme_id = $ecom_mobilethemeid 
							LIMIT 
								1";
			$ret_theme = $db->query($sql_theme);
			if($db->num_rows($ret_theme))
			{
				$row_theme = $db->fetch_array($ret_theme);
				$ecom_themepath = stripslashes($row_theme['path']);
				$ecom_themename = stripslashes($row_theme['themename']);
				$ecom_load_mobile_theme = true; // This variable decide whether currently viewing the mobile or normal theme
			}	
		}
	}
	if($ecom_load_mobile_theme == true)
	{
		$site_images_folder  	= 'mobile_site_images';
		$css_folder		  		= 'mobile_css';
		$scripts_folder			= 'mobile_scripts';
	}
	else
	{
		$site_images_folder     = 'site_images';
		$css_folder		  		= 'css';
		$scripts_folder	  		= 'scripts';
	}
	// end of mobile device detection 

if(!$ecom_siteid) {
	echo 'Error! This domain does not exists in our database';
	exit;
}
if($ecom_status == 'Suspended') {
	include "suspended.php";
	exit;
} else if($ecom_status == 'Cancelled') {
	include "cancelled.php";
	exit;
}

if(($ecom_hostname != $_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] != SECURED_URL) && ($_SERVER['HTTP_HOST'] != SECURED_URL_ALIAS)) {
	//echo $_SERVER['PHP_SELF'];
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: http://$ecom_hostname".$_SERVER['REQUEST_URI']);
	exit;
}
if($ecom_istwitter==0) // check whether twitter is ticked. If not make the twitter account id to blank
	$ecom_twitteraccountId = '';
	//echo $_SERVER['DOCUMENT_ROOT'];
define('SITE_DOCUMENT_ROOT',$_SERVER['DOCUMENT_ROOT']);
define('SITE_URL',$ecom_selfhttp.$ecom_hostname);
define('IMAGE_ROOT_PATH',$_SERVER['DOCUMENT_ROOT'].'/images');
define('CLIENT_IMAGE_URL',$ecom_selfhttp.$ecom_hostname.'/images');
define('ORG_DOCROOT','/home/storage/346/3559346/user/htdocs');
define('CGI_PATH','/home/storage/346/3559346/user/cgi-bin');

//Image path
$image_path 							= ORG_DOCROOT . '/images/' . $ecom_hostname;
// Get the path for the theme
/*$sql_theme 								= "SELECT 
												path,themename 
											FROM 
												themes 
											WHERE 
												theme_id=$ecom_themeid 
											LIMIT 
												1";
$ret_theme 								= $db->query($sql_theme);
list($ecom_themepath,$ecom_themename) 	= $db->fetch_array($ret_theme);*/
$qty_updated_arr = array();
$add_from_combo = false;


/* FB login script starts here */
if($ecom_fb_enable == 1)
{
	include("fbconnect/facebook.php");
	include("fbconnect/fbconnect.php");
}
//print_r($_SESSION);
/* FB login script ends here */


include "includes/ip_range_check.php"; // decide whether ip range is to be checked or not

?>
