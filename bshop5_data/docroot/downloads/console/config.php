<?php
/*######################################################################
# Include Necessary Files*/
include_once('classes/db_class.inc.php');
//require_once('classes/browser.php');
//$br = new Browser;
/*######################################################################
# Database Config
#
# dbhost:       SQL Database Hostname
# dbuname:      SQL Username
# dbpass:       SQL Password
# dbname:       SQL Database Name
######################################################################
*/
/*$dbhost  			= 'localhost';
$dbuname 			= 'bshop_am4';
$dbpass 	 		= 'b$H0pF@Ur9642';
$dbname  			= 'business1st_bshop4';
*/
$dbhost  			= '213.171.200.94';
$dbuname 			= 'bshop5_db_usr';
$dbpass  			= 'rPA2UFg2DaH3Sw9J';
$dbname				= 'bshop5_db';
$default_rowcnt = 15;
$db 					= new db_mysql($dbhost,$dbuname,$dbpass,$dbname);
$db->connect();
$db->select_db();
$db->query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';");

//echo $_SERVER['DOCUMENT_ROOT'];
define('SITE_DOCUMENT_ROOT',$_SERVER['DOCUMENT_ROOT']);
//define('SITE_DOCUMENT_ROOT','/var/www/html/bshop4');
define('SITE_URL','http://'.$_SERVER['HTTP_HOST']);
define('IMAGE_ROOT_PATH',$_SERVER['DOCUMENT_ROOT'].'/images');
define('CLIENT_IMAGE_URL','http://'.$_SERVER['HTTP_HOST'].'/images');
//define('ORG_DOCROOT','/var/www/vhosts/bshop4.co.uk/httpdocs');
define('ORG_DOCROOT','/home/storage/346/3559346/user/htdocs');
define('CONVERT_PATH','/home/storage/346/3559346/user/bin'); // path of the convert command to resize the images

//#User Types
$usertype_array = array('sa' => 'System Admin', 'su' => 'System User', 'sm' => 'Shop Manager');
$enable_array   = array(0 => 'No', 1 => 'Yes');
$status_array   = array(0 => 'off.gif', 1 => 'on.gif');

//#Getting site details
$sql_site = "SELECT site_id,site_domain,site_domain_alias,clients_client_id,site_status,themes_theme_id,site_email,console_levels_level_id,site_title,site_intestmod,site_hide_console_error_msgs,site_activate_invoice,site_allpricewithtax,site_delivery_location_country_map,advanced_seo,mobile_themes_theme_id,in_mobile_api,site_grid_enable,enable_searchrefine_category,selfssl_active  FROM sites WHERE $site_where";
$res_site = $db->query($sql_site);
list($ecom_siteid, $ecom_hostname, $ecom_hostname_alias, $ecom_client, $ecom_status,$ecom_themeid,$ecom_email,$ecom_levelid,$ecom_title,$ecom_testing,$ecom_site_hide_console_error_msgs,$ecom_site_activate_invoice,$ecom_allpricewithtax,$ecom_site_delivery_location_country_map,$ecom_advancedseo,$ecom_mobilethemeid,$ecom_site_mobile_api,$ecom_gridenable,$ecom_enable_searchrefine_category,$ecom_selfssl_active) = $db->fetch_array($res_site);
if(!$ecom_siteid) {
	echo 'Error! This domain does not exists in our database';
	exit;
}
if(strtolower($ecom_status) == 'suspended') {
	echo 'Error! This domain is suspended';
	exit;
} else if(strtolower($ecom_status) == 'cancelled') {
	echo 'Error! This domain is cancelled';
	exit;
}
// Image path
$image_path 		= ORG_DOCROOT . '/images/' . $ecom_hostname;

// Decides whether the resize to be applied to the images. Images will be resize if this variabel is set to 1
$Img_Resize			= 1;
?>
