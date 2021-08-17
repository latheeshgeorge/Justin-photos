<div style="background-color:red;display:box;text-align:center;padding:6px;color:#fff;font-weight:bold;font-size:25px;">BSHOP5  Domain</div>

<?php
/*
if($_SERVER['HTTP_HOST'] != 'www.bsecured.co.uk' && $_SERVER['HTTP_HOST'] != 'bsecured.co.uk') {
	exit;
}
if($_SERVER["HTTPS"] != "on") {
	header("Location: https://www.bsecured.co.uk/bshop4superadmin");
	exit;
}
*/ 
/*
#################################################################
# Script Name 	: home.php
# Description 	: Page after successfull login on admin side
# Coded by 		: Sny
# Created on	: 31-May-2007
# Modified by	: 
# Modified On	: 
#################################################################
#	Include Common routines
*/
include_once("functions/functions.php");
include('session.php');
require_once("config.php");
//	Message Handler
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="expires" content="0">
	<title>Bshop v4.0 Super Admin</title>
   <link href="css/bv4.css" rel="stylesheet" type="text/css">
   <script src="js/validation.js" language="javascript"></script>
   <script src="js/ajax.js" language="javascript"></script>
   <script language="JavaScript" src="js/date_picker.js"></script>
	</head>
<body>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0"class="maintable">
	<tr style="height: 10px;">
		<td colspan="2" align="center" valign="top"><?php include_once("header.php")?></td>
	</tr>
	<tr align="center" valign="top">
		<td width="18%"><?php include_once("links.php")?></td>
	    <td width="82%" valign="top" style="padding-left:2px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="1">
		  <tr>
			<td valign="top">
<?php
				//	Show messages
				//isset($errstat) ? $CommFunc->showmessage($emsg,'#ff0000'): true;
				
				//	Select Page to Show
				switch ($_REQUEST['request']) {
				//################### ADMIN OPTIONS #######################
					case 'admin': //# Admin Options
						require_once("services/profile.php");
					break;
					case "clients"://#Clients
						require_once("services/clients.php");
					break;
					case "sites"://#Sites
						require_once("services/sites.php");
					break;
					case "themes"://#themes
						require_once("services/themes.php");
					break;
					case "seo"://#themes
						require_once("services/seo.php");
					break;
					case "levels"://#Console Levels
					
						require_once("services/console_levels.php");
					break;
					case "currency"://#currency
						require_once("services/currency.php");
					break;
					case "country"://#country
						require_once("services/country.php");
					break;
					case "features"://#Features
						require_once("services/features.php");
					break;
					case "services"://#Services
						require_once("services/services.php");
					break;
					case "payment_methods"://#Payment Methods
						require_once("services/payment_methods.php");
					break;
					case "payment_types"://#Payment Methods
						require_once("services/payment_types.php");
					break;
					case "payment_capture_types"://#Payment Methods
						require_once("services/payment_capture_types.php");
					break;
					case "credit_cards"://#Credit cards
						require_once("services/credit_cards.php");
					break;
					case "text_link_ad"://#Text Link Ads
						require_once("services/text_link_ads.php");
					break;
					case "email_templates"://#Email Templetes
						require_once("services/email_templates.php");
					break;
					case "cust_comptype":
						require_once("services/customer_companytypes.php");
					break;
					case "help_message_group":
					    require_once("services/message_group.php");
					break;
					case "help_message":
					    require_once("services/help_message.php");
					break;
					case "console_news":
					    require_once("services/console_news.php");
					break;
					case "console_suggestion":
					    require_once("services/console_suggestion.php");
					break;
					case "setup_groups":
					    require_once("services/setup_groups.php");
					break;
					case "setup_items":
					    require_once("services/setup_items.php");
					break;
					default:
						require_once("home_default.php");
					break;
				}
				
?>
			</td>
		  </tr>
		</table>

		
	    </td>
	</tr>
	<tr style="height: 10px;">
		<td colspan="2" align="center" valign="bottom"><?php include_once("footer.php")?></td>
	</tr>
</table>
</body>
</html>
