<?php
/*######################################################################
# Database Config
#
# dbhost:       SQL Database Hostname
# dbuname:      SQL Username
# dbpass:       SQL Password
# dbname:       SQL Database Name
######################################################################
*/
include_once('classes/db_class.inc.php');	// Page which holds the class for db operations
//$dbhost  			= 'localhost';
//$dbuname 			= 'bshop_am4';
//$dbpass  			= 'b$H0pF@Ur9642';
$dbhost  			= '213.171.200.94';
$dbuname 			= 'bshop5_db_usr';
$dbpass  			= 'rPA2UFg2DaH3Sw9J';
$dbname				= 'bshop5_db';
$db	 				= new db_mysql($dbhost,$dbuname,$dbpass,$dbname);
$db->connect();
$db->select_db();
$db->query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';");


?>
