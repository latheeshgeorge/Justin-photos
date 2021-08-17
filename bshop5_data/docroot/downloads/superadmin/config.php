<?php
/*######################################################################
# Include Necessary Files
*/
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
// For the local system
$dbhost  			= '213.171.200.94';
$dbuname 			= 'bshop5_db_usr';
$dbpass  			= 'rPA2UFg2DaH3Sw9J';
$dbname				= 'bshop5_db';
$default_rowcnt = 15;

$db = new db_mysql($dbhost,$dbuname,$dbpass,$dbname);
$db->connect();
$db->select_db();

/*
$alltables = mysql_query("SHOW TABLES");

while ($table = mysql_fetch_assoc($alltables))
{
   foreach ($table as $db1 => $tablename)
   {
       mysql_query("OPTIMIZE TABLE '".$tablename."'")
       or die(mysql_error());

   }
}
*/ 
//Constants
	//echo $_SERVER['DOCUMENT_ROOT'];

define('SITE_DOCUMENT_ROOT','/home/storage/346/3559346/user/htdocs');
define('SITE_URL','http://http://bshop5.co.uk/bshop4superadmin/');
$site_main_docroot 	= '/home/storage/346/3559346/user/htdocs';
define('IMAGE_ROOT_PATH',$site_main_docroot . '/images');
?>
