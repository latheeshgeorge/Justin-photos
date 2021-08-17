<?php   
#################################################################
# Script Name 	: functions.php
# Description 		: General Functions page
# Coded by 		: Sny
# Created on		: 09-Jun-2007
# Modified by		: Sny
# Modified On		: 01-Feb-2010
# Modified by		: Joby
# Modified On		: 12-Apr-2011
#################################################################
/*10 Nov 2011*/
function add_line_break($content)
{
	$data = str_replace('</tr>','</tr>'."\n",$content);
	$data = str_replace('</td>','</td>'."\n",$data);
	$data = str_replace('</TR>','</TR>'."\n",$data);
	$data = str_replace('</TD>','</TD>'."\n",$data);
	return $data;
}
function send_support_notification_emails($mail_type,$det_arr)
{
    global $ecom_siteid,$db,$ecom_hostname;
    
    if(check_IndividualSslActive())
	{
		$http = 'https://';
	}
	else
	{
		$http = 'http://';
	}
    
    // get the website alias name for current website
    $sql_site_alias = "SELECT site_domain_alias,advanced_seo,site_updation_notification_emailids   
                            FROM 
                                sites 
                            WHERE 
                                site_id = $ecom_siteid 
                            LIMIT 
                                1";
    $ret_site_alias = $db->query($sql_site_alias);
    if(!$db->num_rows($ret_site_alias))
        return;
    else
        $row_site_alias = $db->fetch_array($ret_site_alias);
        
    $email_header       = "From: Bshop4 Notifier <info@".$row_site_alias['site_domain_alias'].">\n";
    $email_header       .= "MIME-Version: 1.0\n";
    $email_header       .= "Content-type: text/html; charset=iso-8859-1\n";
    $email_subject      = 'Updation on '.$ecom_hostname;
    $email_content      = '';
    switch($mail_type)
    {
        case 'home': // case of updating home page content
            // Check whether the current page is home page
            $sql_check = "SELECT page_id 
                            FROM 
                                static_pages 
                            WHERE 
                                page_id=".$det_arr['cur_id']." 
                                AND pname='Home' 
                                AND sites_site_id = $ecom_siteid 
                            LIMIT 
                                1";
            $ret_check = $db->query($sql_check);
            if ($db->num_rows($ret_check))
                $email_content .= '<br/>Home Page Content have been Updated';
        break;
        case 'shelf': // case of adding a new shelf or adding more products to an existing shelf
            // Get the details of current shelf
            $sql_shelf = "SELECT shelf_name 
                            FROM 
                                product_shelf 
                            WHERE 
                                shelf_id = ".$det_arr['cur_id']."
                            LIMIT 
                                1";
            $ret_shelf = $db->query($sql_shelf);
            if($db->num_rows($ret_shelf))
            {
                $row_shelf = $db->fetch_array($ret_shelf);
            }
            $shelf_prod_arr = $det_arr['prod_id_arr'];
            if($shelf_prod_arr)
            {
                if(count($shelf_prod_arr)>1)
                    $prod_str = 'products';
                else
                    $prod_str = 'product';
                $email_content .= "<br/><br/>The following $prod_str have been added to the shelf <strong>".stripslashes($row_shelf['shelf_name'])."</strong>";
            }    
            else
                $email_content .= "<br/>A new shelf have been created with name <strong>".stripslashes($row_shelf['shelf_name'])."</strong>";
            $table_str = '';
            if($shelf_prod_arr)
            {
                $sql_pro = "SELECT product_id,product_name 
                                FROM 
                                    products 
                                WHERE 
                                    sites_site_id=$ecom_siteid 
                                    AND product_id IN (".implode(',',$shelf_prod_arr).")";
                $ret_prod = $db->query($sql_pro);
                if($db->num_rows($ret_prod))
                {
                    $table_str = '<table width="100%" cellpadding="1" cellspacing="1" border="0">';
                    while ($row_prod = $db->fetch_array($ret_prod))
                    {
                        $url = url_product($row_prod['product_id'],$row_prod['product_name'],$row_site_alias['advanced_seo'],1);
                        $table_str .='<tr>
                                        <td align="left">
                                        &bull;&nbsp;<a href="'.$url.'" target="_blank" style="color:#000000;text-decoration:underline">'.stripslashes($row_prod['product_name']).'</a>
                                        </td>
                                        </tr>';
                    }
                    $table_str .='</table>';
                }
                $email_content .= "<br/>".$table_str;
            }
        break;
        case 'featured': // case of setting or changing featured products
            $email_content .= '<br/>Featured Product has been changed';
        break;
    };
     // Sending the mail
     if($email_content != '' and $row_site_alias['site_updation_notification_emailids'] != '')
     { 
        $email_content .= "<br><br><strong>Note:</strong>
                                This is an automated email send by Site Updation Tracking Script";
        $email_arr = explode(',',$row_site_alias['site_updation_notification_emailids']);
        for($i=0;$i<count($email_arr);$i++)
        {
            mail($email_arr[$i],$email_subject,$email_content,$email_header);
        }
            
     }   
    
}
function display_discount_type($products_arr)
{
	
	if($products_arr['order_detail_discount_type']!='')
	{
		$disc_caption = '';
		$disc_arr = explode(',',$products_arr['order_detail_discount_type']);
		$disp_cap = array();
		foreach ($disc_arr as $k=>$v)
		{
			switch ($v)
			{
				case 'PROM':
					$disp_cap[] = 'Promotional Code Discount';//$Captions_arr['CART']['CART_PROM_DISC'];
				break;
				case 'CUST_DIR':
					$disp_cap[] =  'Customer Direct Discount';//$Captions_arr['CART']['CART_CUST_DIR_DISC'];
				break;
				case 'CUST_GROUP':
					$disp_cap[] =  'Customer Group Discount ';//'$Captions_arr['CART']['CART_GROUP_DISC'];
				break;
				case 'COMBO':
					$disp_cap[] =  'Combo Deal Discount';//$Captions_arr['CART']['CART_COMBO_DISC'];
				break;
				case 'BULK':
					$disp_cap[] =  'Bulk Discount';//$Captions_arr['CART']['CART_BULK_DISC'];
				break;
				case 'PRICE_PROMISE_DISC':
					$disp_cap[] =  'Price Promise Discount';//$Captions_arr['CART']['CART_BULK_DISC'];
				break;	
				default:
					$disp_cap[] = 'Normal Product Discount ';
			};
		}
		if(count($disp_cap))
		{
			$disc_caption = implode('<br/>',$disp_cap);
		}
		return $disc_caption;
	}
}
// Function to pick the currency rate from XE .net
function quote_xe_currency($to, $from = 'GBP') 
{
    $page = file('http://www.xe.com/ucc/convert.cgi?Amount=1&From=' . $from . '&To=' . $to);
    $match = array();
    preg_match('/[0-9.]+\s*' . $from . '\s*=\s*([0-9.]+)\s*' . $to . '/', implode('', $page), $match);
    if (sizeof($match) > 0) {
		if(is_numeric($match[1])) { 
      		return $match[1];
	  	} else {
			return false;
		}
    } else {
      return false;
    }
 }
  // Function to pick currency rate from Yahoo finance
function quote_yahoofinance_currency($to, $from) 
{
	$url			= 'http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s='. $from . $to .'=X';
	$filehandler 	= @fopen($url, 'r');
	if ($filehandler) 
	{
		$data = fgets($filehandler, 4096);
		fclose($filehandler);
	}
	$InfoData 		= explode(',',$data); 
	$curr_rate 		= $InfoData[1];
	if($curr_rate)
	{
		return round($curr_rate,2);
	}
	else
	{
		return false;
	}
}
 // Function to show the status of free delivery 
 function getFreedeliveryCaption($code)
 {
 	switch ($code)
	{
		case 'Prom':
			$caption = 'Free Delivery due to Promotional Code';
		break;
		case 'Vouch':
			$caption = 'Free Delivery due to Gift Voucher';
		break;
		case 'Delivery':
			$caption = 'Free Delivery to Selected Delivery Location';
		break;
	};
	return $caption;
 }
 // Function to set the currency rates picked from xe.net to the tables
 function Currency_Rates_GetandSave()
 {
 	global $db,$ecom_siteid;
	$sql_default = "SELECT curr_code 
						FROM 
							general_settings_site_currency 
						WHERE 
							sites_site_id=$ecom_siteid 
							AND curr_default=1 
						LIMIT 
							1";
	$res_default = $db->query($sql_default);
	if ($db->num_rows($res_default)) // do the following only if default currency exists
	{
		list($default_curr) = $db->fetch_array($res_default);
		$sql_all_curr = "SELECT currency_id,curr_code 
							FROM 
								general_settings_site_currency 
							WHERE 
								sites_site_id=$ecom_siteid 
								AND curr_default=0 ";
		$res_all_curr = $db->query($sql_all_curr);
		if ($db->num_rows($res_all_curr))
		{
			while(list($all_currency_id,$all_curr_code) = $db->fetch_array($res_all_curr)) 
			{
				//$rate = quote_xe_currency($all_curr_code,$default_curr);
				$rate = quote_yahoofinance_currency($all_curr_code,$default_curr);
				if($rate)
				{
					$sql_update = "UPDATE general_settings_site_currency 
									SET 
										curr_rate = $rate 
									WHERE 
										sites_site_id = $ecom_siteid 
										AND currency_id=$all_currency_id 
										AND curr_default = 0 
									LIMIT 
										1";
					$db->query($sql_update);								
				}
			}
		}	
	}	
}	
// Function to generate the starting of page number
function getStartOfPageno($recs,$pg)
{
	if(!is_numeric($pg) or $pg<1) $pg=1;
	if(!is_numeric($recs) or $recs<1) $recs=10;
	if($pg>1)
		return ($recs*($pg-1)+1);
	else
		return 1;
}
// Function to get the captions for the current site
function getCaptions($section= 'COMMON')
{
	global $db,$ecom_siteid;
	// check whether section name is given
	if ($section)
	{
		$sql_sec = "SELECT section_id FROM general_settings_section WHERE section_code = '$section'";
		$ret_sec = $db->query($sql_sec);
		if ($db->num_rows($ret_sec))
		{
			$row_sec = $db->fetch_array($ret_sec);
			$add_condition  = " AND general_settings_section_section_id = ".$row_sec['section_id'];
		}
	}
	$sql_cap = "SELECT general_key,general_text FROM general_settings_site_captions WHERE sites_site_id=$ecom_siteid $add_condition";
	$ret_cap = $db->query($sql_cap);
	if ($db->num_rows($ret_cap))
	{
		while ($row_cap = $db->fetch_array($ret_cap))
		{
			$key 			= $row_cap['general_key'];
			$cap 			= stripslashes($row_cap['general_text']);
			$caption[$key] 	= stripslashes($cap);
		}
	}
	return $caption;
}
function redirect($url,$arg = "")
{
	if($arg) {
		@header("Location: ".$url."?".session_name()."=".session_id()."&".$arg);
	} else {
		@header("Location: ".$url."?".session_name()."=".session_id());
	}
	exit;
}
function getConsoleUserName($userid)
{
	global $db,$ecom_siteid;
	$sql_usr = "SELECT user_title,user_fname,user_lname,sites_site_id
					FROM
						sites_users_7584
					WHERE
						user_id = $userid
					LIMIT
						1";
	$ret_usr = $db->query($sql_usr);
	if($db->num_rows($ret_usr))
	{
		$row_usr = $db->fetch_array($ret_usr);
		if ($row_usr['sites_site_id']==0)// case of super admin
		$cap = stripslashes($row_usr['user_fname'])." ".stripslashes($row_usr['user_lname']);
		else
		$cap = stripslashes($row_usr['user_title']).".".stripslashes($row_usr['user_fname'])." ".stripslashes($row_usr['user_lname']);
	}
	return $cap;
}
function redirect_illegal()
{
	?>
	<script language="javascript">
	window.close();
	location.href= 'http://www.yahoo.com';
	</script>
	<?
	exit;
}
function display_price($price)
{
	global $db,$ecom_siteid;
	$sql_curr = "SELECT curr_sign_char FROM general_settings_site_currency WHERE
				sites_site_id=$ecom_siteid AND curr_default=1";
	$ret_curr = $db->query($sql_curr);
	if ($db->num_rows($ret_curr))
	{
		$row_curr 	= $db->fetch_array($ret_curr);
		$curr		= $row_curr['curr_sign_char'];
	}
	$price = sprintf("%.2f",$price);
	return $curr.$price;
}
// Function To Display Currency Symbol of this site
function display_curr_symbol()
{
	global $db,$ecom_siteid;
	$sql_curr = "SELECT curr_sign_char FROM general_settings_site_currency WHERE
				sites_site_id=$ecom_siteid AND curr_default=1";
	$ret_curr = $db->query($sql_curr);
	if ($db->num_rows($ret_curr))
	{
		$row_curr 	= $db->fetch_array($ret_curr);
		$curr		= $row_curr['curr_sign_char'];
	}
	return $curr;
}
// ** Function to display the price with currency symbol
function print_price_selected_currency($price,$rate=1,$sign,$always_return=false)
{
	if ($always_return==true) // if always_return is set to true then return the price even if it is 0
	return $sign.sprintf('%0.2f',($price * $rate));
	else // return only if the price is >0
	{
		if ($price>0)
		return $sign.sprintf('%0.2f',($price * $rate));
	}
}
// ** Function to display the price with currency symbol
function print_price_default_currency($price,$rate=1,$sign,$always_return=false)
{
	if ($always_return==true) // if always_return is set to true then return the price even if it is 0
	return $sign.sprintf('%0.2f',($price/$rate));
	else // return only if the price is >0
	{
		if ($price>0)
		return $sign.sprintf('%0.2f',($price/$rate));
	}
}
function list_countries()
{
	global $db,$ecom_siteid;
	$sql_countries="SELECT country_id,country_name from general_settings_site_country WHERE sites_site_id=$ecom_siteid ";
	$res = $db->query($sql_countries);
	$countries[0]='Select a country';
	while(list($id,$CountryList) = $db->fetch_array($res))
	{
		$countries[$id]=$CountryList;
	}
	return $countries;
}
function generateselectbox($name,$option_values,$selected,$onblur='',$onchange='',$multiple=0,$def_arr= array(),$onclick='') {
	global $catSET_WIDTH;
	if(!is_array($option_values)) return ;
	$return_value = "<select name='$name' id='$name'";
	if ($multiple > 0) $return_value .= " multiple='multiple' size='$multiple'";
	if($onblur) {
		$return_value .= " onblur='$onblur'";
	}
	if($onchange) {
		$return_value .= " onchange='$onchange'";
	}
	if($onclick) {
		$return_value .= " onclick='$onclick'";
	}
	if($catSET_WIDTH!='')
		$return_value .=' style="width:'.$catSET_WIDTH.'"';
	$return_value .= ">";
	if (is_array($def_arr))
	{
		if(count($def_arr))
		{
			foreach ($def_arr as $k=>$v)
			{
				$return_value .= "<option value='".$k."' title='".$v."'>$v</option>";
			}
		}
	}
	foreach($option_values as $k => $v) {
		if(is_array($selected)) {
			if(in_array($k,$selected)) {
				$return_value .= "<option value='$k' selected>$v</option>";
			} else {
				$return_value .= "<option value='$k'>$v</option>";
			}
		} else {
			if($selected == $k) {
				$return_value .= "<option value='$k' selected>$v</option>";
			} else {
				$return_value .= "<option value='$k'>$v</option>";
			}
		}
	}
	$return_value .= "</select>";
	return $return_value;
}
function add_slash($varial,$strip_tags=true)
{
	#checking whether magic quotes are on
	//if (!get_magic_quotes_gpc()){
		$ret=addslashes(trim($varial));
	/*} else {
		$ret=trim($varial);
	}*/
	if($strip_tags==true)
	$ret = strip_tags($ret);
	return $ret;
}
function serverside_validation($fieldRequired, $fieldDescription, $fieldEmail, $fieldConfirm, $fieldConfirmDesc, $fieldNumeric, $fieldNumericDesc) {
	global $alert;
	foreach($fieldRequired as $k => $v) {
		if(trim($v) == "" || $v == '0') {
			$alert = "Enter ".$fieldDescription[$k];
			return false;
		}
	}
	foreach($fieldEmail as $v) {
		if(!ereg("^[-a-zA-Z0-9_.]+@[-a-zA-Z0-9]+\.([-a-zA-Z.]{2,15})",trim($v))) {
			$alert = "Enter a valid Email address";
			return false;
		}
	}
	if (isset($fieldConfirm[0])) {
		if($fieldConfirm[0] != $fieldConfirm[1]) {
			$alert = "Your ".$fieldConfirmDesc[0]." and ".$fieldConfirmDesc[1]." does not match";
			return false;
		}
	}
	foreach($fieldNumeric as $k => $v) {
		if($v && !is_numeric($v)) {
			$alert = "Enter numeric value for ".$fieldNumericDesc[$k];
			return false;
		}
	}
	return true;
}
/****************Check date difference***********************/
function check_date_difference($from,$to)
{
	$from_date_array = explode("-",$from);
	$from_date_time = mktime(0,0,0,$from_date_array[1],$from_date_array[2],$from_date_array[0]);
	$to_date_array = explode("-",$to);
	$to_date_time = mktime(0,0,0,$to_date_array[1],$to_date_array[2],$to_date_array[0]);
	if($from_date_time <= $to_date_time) {
		return true;
	} else {
		return false;
	}
}
/*******************Function to validate Password**********************/
function check_password($password)
{
	If (ereg('^([a-zA-Z0-9_]{4,12})$', $password)) {
		return true;
	} else {
		return false;
	}
}
function dateFormat($passdt, $type = "default") {
	#############fromat of displaying date '4:21pm -Mar 11 Sat'
	//$arow[orddt] in yyyy-mm-dd hh:mm:sec format
	$sp_dt1=explode(" ",$passdt);
	$sp_dt = explode("-",$sp_dt1[0]);
	$rt_year=intval($sp_dt[0]);
	$rt_month=(integer)$sp_dt[1];
	$rt_day=(integer)$sp_dt[2];
	$sp_dt2 = explode(":",$sp_dt1[1]);
	$rt_hr = (integer)$sp_dt2[0];
	$rt_min = (integer)$sp_dt2[1];
	$rt_sec = (integer)$sp_dt2[2];
	$unixstamp=mktime ($rt_hr,$rt_min,$rt_sec,$rt_month,$rt_day,$rt_year);
	// $dtdisp=@date("h :i a"." - "."M d Y D",$unixstamp);
	if($type == 'time') {
		$dtdisp = @date("h :i a",$unixstamp);
	}
	elseif($type == 'datetime'){
		$dtdisp = @date("d-M-Y",$unixstamp)."&nbsp;".@date("h:i a",$unixstamp);
	}
	elseif($type == 'datetime_break'){
		$dtdisp = @date("d-M-Y",$unixstamp)."<br/>".@date("h:i a",$unixstamp);
	}
	else {
		$dtdisp = @date("d-M-Y",$unixstamp);
	}
	return $dtdisp;
	//int mktime (int hour, int minute, int second, int month, int day, int year [, int is_dst])
}
//Function for urldecode
function urlExtract( $foo )
{
	$temp = array();
	$foo = urldecode(base64_decode($foo));
	$vars = explode('&',$foo);
	$i = 0;
	while ($i < count($vars)) {
		$b = split('=', $vars[$i]);
		$temp[$b[0]] = $b[1];
		$i++;
	}
	return $temp;
}
function table_header($array,$header_positions)
{
	if(!is_array($array) || !count($array)>0) 
		return ;
	$return_value = '<tr class="maininnertabletd1">';
	foreach ($array as $k=>$value)
	{
		$align='center';
		if($header_positions[$k])
		{
			$align=$header_positions[$k];
		}
		$return_value .= '<td align="'.$align.'" valign="middle" class="listingtableheader">'.$value.'</td>';
	}
	$return_value .= '</tr>';
	return $return_value;
}
function table_header_print($array,$header_positions) {

	$return_value = '<tr class="maininnertabletd1">';
	foreach ($array as $k=>$value) {
		$align='center';
		if($header_positions[$k])
		{
			$align=$header_positions[$k];
		}
		$return_value .= '<td align="'.$align.'" valign="middle" class="listingtableheader">'.$value.'</td>';
	}
	$return_value .= '</tr>';
	return $return_value;
}
function generate_category_tree($id,$level=0,$all=false,$only_cat=false,$select=false)
{
	global $db,$ecom_siteid;
	if($id == 0) {
		if(!$only_cat) {
			if($all)  $categories[0] = '--- All ---';
			elseif($select) $categories[0] = '--- Select ---';
			else $categories[0] = '--- Root Level ---';
		}
	}
	$query = "select category_id,category_name from product_categories where parent_id=$id AND sites_site_id=$ecom_siteid ORDER BY category_name";
	$result = $db->query($query);
	while(list($id,$name) = $db->fetch_array($result))
	{
		$space = '';
		for($i=0; $i<=$level-1; $i++) {
			$space .= '--';
		}
		$categories[$id] = $space.$name;
		$subcategories = generate_category_tree($id,$level+1);
		if(is_array($subcategories))
		{
			$space = '';
			for($i=0; $i<=$level-1; $i++) {
				$space .= '--';
			}
			foreach($subcategories as $k => $v)
			{
				$categories[$k] = $space.$v;
			}
		}
	}
	return $categories;
}
function generate_mobile_api_category_tree($id,$level=0,$all=false,$only_cat=false,$select=false,$root_level_also=false)
{
	global $db,$ecom_siteid;
	if($id == 0) {
		if(!$only_cat) {
			if($all)
			{
			 	if ($root_level_also==true)
			 	{
			 		$categories[-1] = '--- All ---';
			 		$categories[0] = '--- Root Level Only---';
				}	
				else
					$categories[0] = '--- All ---';
			} 
			elseif($select) $categories[0] = '--- Select ---';
			else $categories[0] = '--- Root Level ---';
		}
	}
	$query = "select category_id,category_name from product_categories where mobile_api_parent_id=$id AND sites_site_id=$ecom_siteid AND in_mobile_api_sites = 1 ORDER BY category_name";
	$result = $db->query($query);
	while(list($id,$name) = $db->fetch_array($result))
	{
		$space = '';
		for($i=0; $i<=$level-1; $i++) {
			$space .= '--';
		}
		$categories[$id] = $space.$name;
		$subcategories = generate_mobile_api_category_tree($id,$level+1);
		if(is_array($subcategories))
		{
			$space = '';
			for($i=0; $i<=$level-1; $i++) {
				$space .= '--';
			}
			foreach($subcategories as $k => $v)
			{
				$categories[$k] = $space.$v;
			}
		}
	}
	return $categories;
}
function generate_category_tree_special($id,$level=0,$all=false,$only_cat=false,$select=false,$root_level_also=false)
{
	global $db,$ecom_siteid;
	if($id == 0) {
		if(!$only_cat) {
			if($all)
			{
			 	if ($root_level_also==true)
			 	{
			 		$categories[-1] = '--- All ---';
			 		$categories[0] = '--- Root Level Only---';
				}	
				else
					$categories[0] = '--- All ---';
			} 
			elseif($select) $categories[0] = '--- Select ---';
			else $categories[0] = '--- Root Level ---';
		}
	}
	$query = "select category_id,category_name from product_categories where parent_id=$id AND sites_site_id=$ecom_siteid ORDER BY category_name";
	$result = $db->query($query);
	while(list($id,$name) = $db->fetch_array($result))
	{
		$space = '';
		for($i=0; $i<=$level-1; $i++) {
			$space .= '--';
		}
		$categories[$id] = $space.$name;
		$subcategories = generate_category_tree($id,$level+1);
		if(is_array($subcategories))
		{
			$space = '';
			for($i=0; $i<=$level-1; $i++) {
				$space .= '--';
			}
			foreach($subcategories as $k => $v)
			{
				$categories[$k] = $space.$v;
			}
		}
	}
	return $categories;
}
function generate_subcategory_tree($id)
{
	global $db,$ecom_siteid;
	$query = "select category_id,category_name from product_categories where parent_id=$id AND sites_site_id=$ecom_siteid ORDER BY category_name";
	$result = $db->query($query);
	while(list($id,$name) = $db->fetch_array($result))
	{
		$categories[$id] = $name;
		$subcategories = generate_subcategory_tree($id);
		if(is_array($subcategories))
		{
			foreach($subcategories as $k => $v)
			{
				$categories[$k] = $space.$v;
			}
		}
	}
	return $categories;
}
function generate_mobile_api_subcategory_tree($id)
{
	global $db,$ecom_siteid;
	$query = "select category_id,category_name from product_categories where mobile_api_parent_id=$id AND sites_site_id=$ecom_siteid AND in_mobile_api_sites = 1 ORDER BY category_name";
	$result = $db->query($query);
	while(list($id,$name) = $db->fetch_array($result))
	{
		$categories[$id] = $name;
		$subcategories = generate_mobile_api_subcategory_tree($id);
		if(is_array($subcategories))
		{
			foreach($subcategories as $k => $v)
			{
				$categories[$k] = $space.$v;
			}
		}
	}
	return $categories;
}
function generate_shop_tree($id,$level=0,$all=false,$only_shop=false,$select=false,$root_level_also=false)
{
	global $db,$ecom_siteid;
	if($id == 0) {
		if(!$only_shop) {
			if($all)
			{
			  if ($root_level_also==true)
			 	{
				$shops[-1] = '--- All ---';
				$shops[0] = '--- Root Level Only---';

				}
				else
			  	$shops[0] = '--- All ---';
			 }
			elseif($select) $shops[0] = '--- Select ---';
			else $shops[0] = '--- Root Level ---';
		}
	}
	$query = "select shopbrand_id,shopbrand_name from product_shopbybrand where shopbrand_parent_id=$id AND sites_site_id=$ecom_siteid ORDER BY shopbrand_name";
	$result = $db->query($query);
	while(list($id,$name) = $db->fetch_array($result))
	{
		$space = '';
		for($i=0; $i<=$level-1; $i++) {
			$space .= '--';
		}
		$shops[$id] = $space.$name;
		$subshops = generate_shop_tree($id,$level+1);
		if(is_array($subshops))
		{
			$space = '';
			for($i=0; $i<=$level-1; $i++) {
				$space .= '--';
			}
			foreach($subshops as $k => $v)
			{
				$shops[$k] = $space.$v;
			}
		}
	}
	return $shops;
}
function generate_subshop_tree($id)
{
	global $db,$ecom_siteid;
	$query = "select shopbrand_id,shopbrand_name from product_shopbybrand where shopbrand_parent_id=$id AND sites_site_id=$ecom_siteid ORDER BY shopbrand_name";
	$result = $db->query($query);
	while(list($id,$name) = $db->fetch_array($result))
	{
		$shops[$id] = $space.$name;
		$subshops = generate_shop_tree($id,$level+1);
		if(is_array($subshops))
		{
			foreach($subshops as $k => $v)
			{
				$shops[$k] = $space.$v;
			}
		}
	}
	return $shops;
}
function checkassign_subcat($pass_cat_id)
{
	global $ecom_siteid,$db;
	$str_checkassigned='-1';
	$sql_checkassigned="SELECT parent_id,category_name FROM product_categories WHERE sites_site_id=$ecom_siteid AND category_id=".$pass_cat_id;
	$ret_checkassigned = $db->query($sql_checkassigned);
	while(list($id,$name) = $db->fetch_array($ret_checkassigned))
	{
		$categories[$id] = $id;
		$subcategories = checkassign_subcat($id);
		if(is_array($subcategories))
		{
			foreach($subcategories as $k => $v)
			{
				$categories[$v] = $space.$v;
			}
		}
	}
	return  $categories;
}
function checkassign_subshop($pass_sub_id)
{
	global $ecom_siteid,$db;
	$str_checkassigned='-1';
	$sql_checkassigned = "SELECT shopbrand_id,shopbrand_parent_id,shopbrand_name FROM product_shopbybrand
					WHERE sites_site_id=$ecom_siteid AND shopbrand_id=".$pass_sub_id; "";
	$ret_checkassigned = $db->query($sql_checkassigned);
	while(list($id,$parent_id,$name) = $db->fetch_array($ret_checkassigned))
	{
		$shops[$parent_id] = $parent_id;
		$shops[$id] = $id;
		$subshops = checkassign_subshop($parent_id);
		if(is_array($subshops))
		{
			foreach($subshops as $k => $v)
			{
				$shops[$v] = $space.$v;
			}
		}
	}
	return  $shops;
}
function generate_directory_tree($id,$level=0,$only_dir=false)
{
	global $db,$ecom_siteid;
	if($id == 0) {
		if(!$only_dir) {
			$directories[0] = '--- Root Directory ---';
		}
	}
	$query = "select directory_id,directory_name from images_directory where parent_id=$id AND sites_site_id = $ecom_siteid ORDER BY directory_name";
	$result = $db->query($query);
	while(list($id,$name) = $db->fetch_array($result))
	{
		$space = '';
		for($i=0; $i<=$level-1; $i++) {
			$space .= '--';
		}
		$directories[$id] = $space.$name;
		$subdirectories = generate_directory_tree($id,$level+1);
		if(is_array($subdirectories))
		{
			$space = '';
			for($i=0; $i<=$level-1; $i++) {
				$space .= '--';
			}
			foreach($subdirectories as $k => $v)
			{
				$directories[$k] = $space.$v;
			}
		}
	}
	return $directories;
}
//Function to resize and copy the uploaded files to required location
function resize_image($old, $new, $geometry, $exten,$resize_me = 1,$overwrite=false)
{
	global $image_path,$copy_only;
	$convert_path = CONVERT_PATH;
	// Probably not necessary, but might as well
	if(!is_uploaded_file($old)) {
		return FALSE;
	}
	//echo "Image_path".$image_path.' <br> convert path'.$convert_path;
	$base = substr($new, 0, strrpos($new, "."));
	if($exten == "image/gif")
	{
		$new = "$base.gif";
	}
	elseif($exten == "image/png")
	{
		$new = "$base.png";
	}
	else
	{
		$new = "$base.jpg";
	}
	$n = 0;
	if ($overwrite==false)
	{
		while(file_exists("$image_path/$new"))
		{
			$n++;
			if($exten == "image/gif")
			{
				$new = "$base$n.gif";
			}
			elseif($exten == "image/png")
			{
				$new = "$base$n.png";
			}
			else
			{
				$new = "$base$n.jpg";
			}
		}
	}
	if ($resize_me==1)
	{
		$command = $convert_path."/convert \"$old\" -auto-orient -geometry \"$geometry\" -interlace Line  \"$image_path/$new\" 2>&1";
		//echo htmlentities($command) . "<br><br>";

		$p = popen($command, "r");
		$error = "";
		while(!feof($p)) {
			$s = fgets($p, 1024);
			$error .= $s;
		}
		$res = pclose($p);

		if($res == 0) return $new;
		else {
			echo ("Failed to resize image: $error<br>");
			return FALSE;
		}
	}
	else
	{
		$new_path = "$image_path/$new";
		if ($copy_only==true)
			$res = copy($old,$new_path);
		else
			$res = move_uploaded_file($old,$new_path);
		if ($res)
		return $new;
		else
		{
			echo "Upload Failed";
			return FALSE;
		}
	}
}
function save_to_displaysettings($position,$modulename,$fieldname,$tablename,$order=0)
{
	global $ecom_themeid,$ecom_siteid,$db;
	//Get the layouttypes for current theme id
	$sql_layouttypes = "SELECT * from themes_layouttypes WHERE theme_id=$ecom_themeid";
	$ret_layouttypes = $db->query($sql_layouttypes);
	while ($row_layouttypes = $db->fetch_array($ret_layouttypes))
	{
		$layoutpos = explode(",",$row_layouttypes['layout_positions']);
		if(in_array($position,$layoutpos))
		{
			//Check whether any entry exists with the osition value for current siteid in current layout
			$sql_exists = "SELECT display_id FROM display_settings a, features b WHERE a.site_id=$ecom_siteid AND
									b.module_name='$modulename' AND a.display_position='".$position."'
									AND a.feature_id = b.feature_id AND layout_code='".$row_layouttypes['layout_code']."'";
			$ret_exists = $db->query($sql_exists);
			if ($db->num_rows($ret_exists)==0)// Case if entry not exists
			{
				//Get the menu id from site_menu table for current module name
				$sql_sitemenu = "SELECT menu_id FROM site_menu a, features b WHERE b.module_name='$modulename' AND
							a.feature_id = b.feature_id AND a.site_id=$ecom_siteid";
				$ret_sitemenu = $db->query($sql_sitemenu);
				if ($db->num_rows($ret_sitemenu))
				{
					$row_sitemenu = $db->fetch_array($ret_sitemenu);
				}
				$sql_features 	= "SELECT feature_id,feature_title FROM features WHERE module_name = '$modulename'";
				$res_features 	= $db->query($sql_features);
				$row_check 		= $db->fetch_array($res_features);
				$insert_array						= array();
				$insert_array['display_title'] 		= $row_check['feature_title'];
				$insert_array['feature_id'] 		= $row_check['feature_id'];
				$insert_array['site_id'] 			= $ecom_siteid;
				$insert_array['display_order'] 		= $order;
				$insert_array['menu_id']			= $row_sitemenu['menu_id'];
			}
			$insert_array['display_position'] 	= $position;
			$insert_array['layout_code']		= $row_layouttypes['layout_code'];
			$db->insert_from_array($insert_array, 'display_settings');
		}
	}
}
function validate_pint(&$n)
{
	if(isset($n)) $n = abs(intval($n));
}
function htmlize(&$d)
{
	$d = htmlentities($d);
}
function add_quotes(&$d)
{
	$d = '"' . str_replace('"', '""', stripslashes($d)) . '"';

}
function html_entity($a) {
	$a = htmlentities($a);
	return $a;
}
function sql_fields(&$d)
{
	if(strpos($d, ".") !== FALSE)
	$d = substr($d, strpos($d, ".") + 1);
}
function sql_data(&$d,$quote_req= true)
{
	if($quote_req==true)
		$d = "'" .  mysql_escape_string($d) . "'";
	else
		$d =   mysql_escape_string($d) ;
	
}
function cleanHtml($html_string) {

	$patterns[0] = "'<STYLE[^>]*?>.*?</STYLE>'si"; //Style tags
	$patterns[1] = "'<HEAD[^>]*?>.*?</HEAD>'si"; // Head tags
	$patterns[2] = "'<HTML[^>]*>'si"; //Html tag
	$patterns[3] = "'<BODY[^>]*>'si"; //Body tag
	$patterns[4] = "'<script[^>]*?>.*?</script>'si"; //Javascript
	$patterns[5] = "'<SPAN[^>]*>'si"; // Span
	$patterns[6] = "'</SPAN[^>]*>'si";
	$patterns[7] = "'<o:p>'si";
	$patterns[8] = "'</o:p>'si";
	$patterns[9] = "'</BODY>'si";
	$patterns[10] = "'</HTML>'si";
	$patterns[11] = "'<H1>'si";
	$patterns[12] = "'<P[^>]*>'si";
	$patterns[13] = "'<I [^>]*>'si";
	$patterns[14] = "'<B [^>]*>'si";
	$patterns[15] = "'<STRONG [^>]*>'si";
	$patterns[16] = "'<LI [^>]*>'si";
	$patterns[17] = "'<DIV [^>]*>'si";
	$patterns[18] = "'<!DOCTYPE[^>]*>'si";
	for ($i=0; $i<=11; $i++){
		$replacements[$i] = "";
	}
	$replacements[12] = "<P>";
	$replacements[13] = "<I>";
	$replacements[14] = "<B>";
	$replacements[15] = "<STRONG>";
	$replacements[16] = "<LI>";
	$replacements[17] = "<DIV>";
	$html_string = preg_replace($patterns, $replacements, $html_string);
	$html_string = str_replace("<p></p>","<br>",$html_string);
	$html_string = str_replace("<P></P>","<br>",$html_string);
	return $html_string;
}
// Function to get the filesize display
function getfilesize($size) {
	$units = array(' B', ' KB', ' MB', ' GB', ' TB');
	for ($i = 0; $size > 1024; $i++) { $size /= 1024; }
	return ceil(round($size, 2)).$units[$i];
}
function strip_url($name) {
    $name = trim($name);
    $name = str_replace(" ","-",$name);
    $name = str_replace("_","-",$name);
    $name = preg_replace("/[^0-9a-zA-Z-]+/", "", $name);
    $name = str_replace("----","-",$name);
    $name = str_replace("---","-",$name);
    $name = str_replace("--","-",$name);
    $name = str_replace(".","-",$name);
    return strtolower($name);
}
function url_product($prodId,$prodName='',$ecom_advancedseo='N',$ret=-1)
{
    global $productPageUrlHash, $db, $ecom_siteid, $ecom_hostname,$ecom_advancedseo;                
    
    if(check_IndividualSslActive())
	{
		$http = 'https://';
	}
	else
	{
		$http = 'http://';
	}
    
    if($prodName=='') // case if product name is not passed, then get it from product table
    {
            $sql_prod = "SELECT product_name 
                                    FROM 
                                            products 
                                    WHERE
                                            product_id = $prodId 
                                            AND sites_site_id = $ecom_siteid 
                                    LIMIT 1";
            $ret_prod = $db->query($sql_prod);
            if ($db->num_rows($ret_prod))
            {
                    $row_prod = $db->fetch_array($ret_prod);
                    $prodName = stripslashes($row_prod['product_name']);
            }
    }
    $prodName = strip_url($prodName); // Stripping unwanted characters from the product name
    
    if($ecom_advancedseo=='Y')
    {
            $productPageUrlHash = $http.$ecom_hostname."/".$prodName."-p$prodId.html";
    }
    else
    {
            $productPageUrlHash = $http.$ecom_hostname."/p$prodId/".$prodName.".html";
    }       
    
    if($ret == -1) // default case of printing the url
    {
            echo ($productPageUrlHash);
    }
    else  // just return the url for the page
    {
            return $productPageUrlHash;
    }       
}
function show_paging($page_name,$qry_string,$start,$limit,$nume,$p_limit,$p_f)
{
	global $ecom_hostname;
	//limit - No of records to be shown per page.
	//$p_limit - This should be more than $limit and set to a value for whick links to be breaked
	$eu = ($start -0);

	$this1 = $eu + $limit;
	$back = $eu - $limit;
	$next = $eu + $limit;
	///// Variables set for advance paging///////////
	$p_fwd=$p_f+$p_limit;
	$p_back=$p_f-$p_limit;

	//////////// End of variables for advance paging ///////////////
	/////////////// Start the buttom links with Prev and next link with page numbers /////////////////
	if ($qry_string!='')
	$qry_string .= '&amp;';
	if($p_f<>0){print "<a href='$page_name?".$qry_string."start=$p_back&p_f=$p_back'><img src='images/arrow-first.gif' border='0'></a>"; }
	//// if our variable $back is equal to 0 or more then only we will display the link to move back ////////
	if($back >=0 and ($back >=$p_f)) {
		print "<a href='$page_name?".$qry_string."start=$back&p_f=$p_f'><img src='images/arrow-prev.gif' border='0'></a>";
	}
	//////////////// Let us display the page links at  center. We will not display the current page as a link ///////////
	for($i=$p_f;$i < $nume and $i<($p_f+$p_limit);$i=$i+$limit){
		if($i <> $eu){
			$i2=$i+$p_f;
			echo "&nbsp;<a href='$page_name?".$qry_string."start=$i&p_f=$p_f'>".($i+1)."</a>";
		}
		else { echo "&nbsp;".($i+1);}        /// Current page is not displayed as link and given font color red
	}


	///////////// If we are not in the last page then Next link will be displayed. Here we check that /////
	if($this1 < $nume and $this1 <($p_f+$p_limit)) {
		print "&nbsp;<a href='$page_name?".$qry_string."start=$next&p_f=$p_f'><img src='images/arrow-next.gif' border='0'></a>";}
		if($p_fwd < $nume){
			print "&nbsp;<a href='$page_name?".$qry_string."start=$p_fwd&p_f=$p_fwd'><img src='images/arrow-last.gif' border='0'></a>";
		}
}
function getconsolemenu($id,$level=0,$first=0)
{
	global $db,$ecom_siteid,$exist_feature_site;
	// Getting the subfeatures for given feature
	$ext_feat = implode(",",$exist_feature_site);
	$query = "SELECT a.features_feature_id,b.feature_title,b.feature_consoleurl,b.feature_new_icon,b.feature_disable_icon FROM mod_menu a,features b WHERE
			sites_site_id=$ecom_siteid AND b.feature_hide = 0
			AND a.features_feature_id=b.feature_id AND b.parent_id=$id AND b.feature_id IN ($ext_feat)
			AND b.feature_displaytouser = 1 ORDER BY feature_name ASC ";		
	$result = $db->query($query);
	if($db->num_rows($result))
	{
		while(list($ids,$name,$url,$enable_icon,$disable_icon) = $db->fetch_array($result))
		{
			$strip_name = str_replace(" ","_",$name);

			$myMenu .= ",";
			if (in_array($ids,$exist_feature_site))
			$myMenu .= "['<img src=\"js/ThemeOffice/".$enable_icon."\" />','".$name."','".$url."',null,'$strip_name'";
			else
			{
				//if($_SESSION['user_type']=='sa')
				$myMenu .= "['<img src=\"js/ThemeOffice/".$disable_icon."\" />','".$name."','',null,'$strip_name'";
			}
			// calling the function recurssively
			$myMenu  .= getconsolemenu($ids,$level=$level+1);
		}
		$myMenu  .= "],";
	}
	else
	{
		$myMenu .='],';
	}
	return $myMenu;
}
function pageNavApp ($pagenum, $pages, $query_str='') {
	global $pg, $records_per_page;
	// offset = (page - 1) * thumbs
	//$a = "<a href='$query_str&amp;pg=";
	//$b = "'>";
	//$c = "</a>\n";
	if($query_str) {
		$a = "<a class='edittextlink' href='home.php?$query_str&records_per_page=$records_per_page&pg=";
	} else {
		$a = "<a class='edittextlink' href='home.php?records_per_page=$records_per_page&pg=";
	}
	$b = "'>";
	$c = "</a>\n";
	$nav = ""; // init page nav string
	if ($pagenum == 1) {

		//$nav .= "<img src='images/paging/left2_disabled.gif' border='0'>[First]&nbsp;&nbsp;";
		$nav .= "<img src='images/paging/left2_disabled.gif' border='0' alt='First'>&nbsp;&nbsp;";
		//$nav .= "<img src='images/paging/left_disabled.gif' border='0'>[Prev]&nbsp;&nbsp;&nbsp;&nbsp;";
		$nav .= "<img src='images/paging/left_disabled.gif' border='0' alt='Prev'>&nbsp;&nbsp;&nbsp;&nbsp;";

	} else {

		//$nav .= $a."1".$b."<img src='images/paging/left2.gif' border='0'>[First]".$c."&nbsp;&nbsp;";
		$nav .= $a."1".$b."<img src='images/paging/left2.gif' border='0' alt='First'>".$c."&nbsp;&nbsp;";
		//$nav .= $a.($pagenum - 1).$b."<img src='images/paging/left.gif' border='0'>[Prev]".$c."&nbsp;&nbsp;&nbsp;&nbsp;";
		$nav .= $a.($pagenum - 1).$b."<img src='images/paging/left.gif' border='0' alt='Prev'>".$c."&nbsp;&nbsp;&nbsp;&nbsp;";

	}
	if ($pagenum == $pages) {

		//$nav .= "<img src='images/paging/right_disabled.gif' border='0'>[Next]&nbsp;&nbsp;";
		$nav .= "<img src='images/paging/right_disabled.gif' border='0' alt='Next'>&nbsp;&nbsp;";
		//$nav .= "<img src='images/paging/right2_disabled.gif' border='0'>[Last]<br>";
		$nav .= "<img src='images/paging/right2_disabled.gif' border='0' alt='Last'><br>";

	} else {
		//$nav .= $a.($pagenum +1).$b."<img src='images/paging/right.gif' border='0'>[Next]".$c."&nbsp;&nbsp;";
		$nav .= $a.($pagenum +1).$b."<img src='images/paging/right.gif' border='0' alt='Next'>".$c."&nbsp;&nbsp;";
		//$nav .= $a.($pages).$b."<img src='images/paging/right2.gif' border='0'>[Last]".$c."<br>";
		$nav .= $a.($pages).$b."<img src='images/paging/right2.gif' border='0' alt='Last'>".$c."<br>";

	}
	$nav .= makeNavApp ($pages, $pagenum, $query_str, $javascript_fn);
	return $nav;
}
function makeNavApp ($pages, $pagenum, $query_str='', $nav = "", $mag = 1) {
	global $pg, $records_per_page, $theme_folder;
	$n = 25; // Number of pages or groupings
	$m = 25; // Order of magnitude of groupings
	//$a = "<a href='$query_str&amp;pg=";
	//$b = "'>";
	//$c = "</a>\n";
	if($query_str) {
		$a = "<a class='edittextlink' href='home.php?$query_str&records_per_page=$records_per_page&pg=";
	} else {
		$a = "<a class='edittextlink' href='home.php?records_per_page=$records_per_page&pg=";
	}
	$b = "'>";
	$c = "</a>\n";
	if ($mag == 1) {
		// single page level
		$minpage = (ceil ($pagenum/$n) * $n) + (1-$n);
		for ($i = $minpage; $i < $pagenum; $i++) {
			if ( isset($nav[1]) ) {
				$nav[1] .= $a.($i).$b;
			} else {
				$nav[1] = $a.($i).$b;
			}
			$nav[1] .= "$i";
			$nav[1] .= $c;
		}
		if ( isset($nav[1]) ) {
			$nav[1] .= "<span class='redtext'>$pagenum</span> ";
		} else {
			$nav[1] = "<span class='redtext'>$pagenum</span> ";
		}
		$maxpage = ceil ($pagenum/$n) * $n;
		if ( $pages >= $maxpage ) {
			for ($i = ($pagenum+1); $i <= $maxpage; $i++) {
				$nav[1] .= $a.($i).$b;
				$nav[1] .= "$i";
				$nav[1] .= $c;
			}
			$nav[1] .= "<br>";
		} else {
			for ($i = ($pagenum+1); $i <= $pages; $i++) {
				$nav[1] .= $a.($i).$b;
				$nav[1] .= "$i";
				$nav[1] .= $c;
			}
			$nav[1] .= "<br> ";
		}
		if ( $minpage > 1 || $pages > $n ) {
			// go to next level
			$nav = makeNavApp ($pages, $pagenum, $query_str, $nav, $n);
		}
		// Construct outgoing string from pieces in the array
		$out = $nav[1];
		for ($i = $n; isset ($nav[$i]); $i = $i * $m) {
			if (isset($nav[$i][1]) && isset($nav[$i][2])) {
				$out = $nav[$i][1].$out.$nav[$i][2];
			} else if (isset($nav[$i][1])) {
				$out = $nav[$i][1].$out;
			} else if (isset($nav[$i][2])) {
				$out = $out.$nav[$i][2];
			} else {
				$out = $out;
			}
		}
		return $out;
	}
	$minpage = (ceil ($pagenum/$mag/$m) * $mag * $m) + (1-($mag * $m));
	$prevpage = (ceil ($pagenum/$mag) * $mag) - $mag; // Page # of last pagegroup before pagenum's page group
	if ( $prevpage > $minpage ) {
		for ($i = ($minpage - 1); $i < $prevpage; $i = $i + $mag) {
			if (isset($nav[$mag][1])) {
				$nav[$mag][1] .= $a.($i+1).$b;
			} else {
				$nav[$mag][1] = $a.($i+1).$b;
			}
			$nav[$mag][1] .= $a.($i+1).$b;
			$nav[$mag][1] .= "[".($i+1)."-".($i+$mag)."]";
			$nav[$mag][1] .= $c;
		}
		$nav[$mag][1] .= "<br>";
	} // Otherwise, it's this page's group, which is handled the mag level below, so skip
	$maxpage = ceil ($pagenum/$mag/$m) * $mag * $m;
	if ( $pages >= $maxpage ) {
		// If there are more pages than we are accounting for here
		$nextpage = ceil ($pagenum/$mag) * $mag;
		if ($maxpage > $nextpage) {
			for ($i = $nextpage; $i < $maxpage; $i = $i + $mag) {
				if (isset($nav[$mag][2])) {
					$nav[$mag][2] .= $a.($i+1).$b;
				} else {
					$nav[$mag][2] = $a.($i+1).$b;
				}
				$nav[$mag][2] .= $a.($i+1).$b;
				$nav[$mag][2] .= "[".($i+1)."-".($i+$mag)."]";
				$nav[$mag][2] .= $c;
			}
			$nav[$mag][2] .= "<br>";
		}
	} else {
		// This is the end
		if ( $pages >= ((ceil ($pagenum/$mag) * $mag) + 1) ) {
			// If there are more pages than just this page's group
			for ($i = (ceil ($pagenum/$mag) * $mag); $i < ($pages-$mag); $i = $i + $mag) {
				if (isset($nav[$mag][2])) {
					$nav[$mag][2] .= $a.($i+1).$b;
				} else {
					$nav[$mag][2] = $a.($i+1).$b;
				}
				$nav[$mag][2] .= "[".($i+1)."-".($i+$mag)."]";
				$nav[$mag][2] .= $c;
			}
			$nav[$mag][2] .= $a.($i+1).$b;
			$nav[$mag][2] .= "[".($i+1)."-".$pages."]";
			$nav[$mag][2] .= $c;
			$nav[$mag][2] .= "<br>";
		}
	}
	if ( $minpage > 1 || $pages >= $maxpage ) {
		$nav = makeNavApp ($pages, $pagenum, $query_str, $nav, $mag * $m);
	}
	return $nav;
}

function paging_footer($query_string,$numcount,$pg,$pages,$page_type='',$colspan='')
{
    global $pg, $records_per_page, $theme_folder;
    // How many adjacent pages should be shown on each side?
    $adjacents = 3;
    /* 
       First get total number of rows in data table. 
       If you have a WHERE clause in your query, make sure you mirror it here.
    */
    $total_pages = $numcount;
    //print_r($_REQUEST);
    /* Setup vars for query. */
    $limit = ($records_per_page)?$records_per_page:50;                                 //how many items to show per page
    $start = $pg;                             //if no page var is given, set start to 0
    
    
    /* Setup page vars for display. */
    if ($pg == 0) 
		$page = 1; 
	else
		$page = $pg;                 //if no page var is given, default to 1.
    $prev = $page - 1;                          //previous page is page - 1
    $next = $page + 1;                          //next page is page + 1
    $lastpage = ceil($total_pages/$limit);      //lastpage is = total pages / items per page, rounded up.
    $lpm1 = $lastpage - 1;                      //last page minus 1
   // echo "page = $page, prev=$prev , next = $next,lastpage = $lastpage";
    /* 
        Now we apply our rules and draw the pagination object. 
        We're actually saving the code to a variable in case we want to draw it more than once.
    */
    $pagination = "";
	if($query_string) {
		$a = "home.php?$query_string&records_per_page=$records_per_page&pg=";
	} else {
		$a = "home.php?records_per_page=$records_per_page&pg=";
	}
    if($lastpage > 1)
    {   
        $pagination .= "<div class=\"pagination\">";
        //previous button
        if ($page > 1) 
		{
            $pagination.= "<a href=\"$a"."$prev\"class=\"enabled_prev\"> &nbsp;&nbsp;&nbsp;&nbsp;</a>";
		}	
        else
            $pagination.= "<span class=\"disabled_prev\"> &nbsp;&nbsp;&nbsp;&nbsp;</span>";    
        
        //pages 
        if ($lastpage < 7 + ($adjacents * 2))   //not enough pages to bother breaking it up
        {   
            for ($counter = 1; $counter <= $lastpage; $counter++)
            {
                if ($counter == $page)
                    $pagination.= "<span class=\"current\">$counter</span>";
                else
                    $pagination.= "<a href=\"$a"."$counter\" class='pagination_a'>$counter</a>";                   
            }
        }
        elseif($lastpage > 5 + ($adjacents * 2))    //enough pages to hide some
        {
            //close to beginning; only hide later pages
            if($page < 1 + ($adjacents * 2))        
            {
                for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
                {
                    if ($counter == $page)
                        $pagination.= "<span class=\"current\">$counter</span>";
                    else
                        $pagination.= "<a href=\"$a"."$counter\" class='pagination_a'>$counter</a>";                   
                }
                $pagination.= "<span class='pagination_dots'>...</span>";
                $pagination.= "<a href=\"$a"."$lpm1\" class='pagination_a'>$lpm1</a>";
                $pagination.= "<a href=\"$a"."$lastpage\" class='pagination_a'>$lastpage</a>";     
            }
            //in middle; hide some front and some back
            elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
            {
                $pagination.= "<a href=\"$a"."=1\" class='pagination_a'>1</a>";
                $pagination.= "<a href=\"$a"."2\" class='pagination_a'>2</a>";
                $pagination.= "<span class='pagination_dots'>...</span>";
                for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
                {
                    if ($counter == $page)
                        $pagination.= "<span class=\"current\">$counter</span>";
                    else
                        $pagination.= "<a href=\"$a"."$counter\" class='pagination_a'>$counter</a>";                   
                }
                $pagination.= "<span class='pagination_dots'>...</span>";
                $pagination.= "<a href=\"$a"."$lpm1\" class='pagination_a'>$lpm1</a>";
                $pagination.= "<a href=\"$a"."$lastpage\" class='pagination_a'>$lastpage</a>";     
            }
            //close to end; only hide early pages
            else
            {
                $pagination.= "<a href=\"$a"."1\" class='pagination_a'>1</a>";
                $pagination.= "<a href=\"$a"."2\" class='pagination_a'>2</a>";
                $pagination.= "<span class='pagination_dots'>...</span>";
                for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
                {
                    if ($counter == $page)
                        $pagination.= "<span class=\"current\">$counter</span>";
                    else
                        $pagination.= "<a href=\"$a"."$counter\" class='pagination_a'>$counter</a>";                   
                }
            }
        }
        
        //next button
        if ($page < $counter - 1) 
            $pagination.= "<a href=\"$a"."$next\" class=\"enabled_next\">&nbsp;&nbsp;&nbsp;&nbsp; </a>";
        else
            $pagination.= "<span class=\"disabled_next\">&nbsp;&nbsp;&nbsp;&nbsp; </span>";
        $pagination.= "</div>\n";       
    }
	echo  $pagination;
 }
 
function old_paging_footer($query_string,$numcount,$pg,$pages,$page_type,$colspan)
{
	echo "Page <b>$pg</b> of <b>$pages</b>&nbsp;&nbsp;";
	if($numcount) {
		echo pageNavApp ($pg, $pages, $query_string,$directpage);
	}
}
function advance_index($vars, &$indices, $values)
{
	$var_id = array_shift($vars);

	if(!$var_id) return FALSE;

	$indices[$var_id]++;
	if($indices[$var_id] >= count($values[$var_id])) {
		$indices[$var_id] = 0;
		return advance_index($vars, $indices, $values);
	}
	return TRUE;
}

function track_stock_transfer($source,$destination,$qty,$prodid,$reqdet=0,$combid=0)
{
	global $db,$ecom_siteid;
	if($qty>0)
	{
		$current_user = $_SESSION['console_id'];
		$insert_array												= array();
		$insert_array['track_date']									= 'now()';
		$insert_array['track_source_id']							= $source;
		$insert_array['track_destination_id']						= $destination;
		$insert_array['track_qty']									= $qty;
		$insert_array['product_id']									= $prodid;
		$insert_array['comb_id']									= $combid;
		$insert_array['user_id']									= $current_user;
		$insert_array['product_stock_request_details_reqdet_id']	= $reqdet;
		$db->insert_from_array($insert_array,'product_stock_transfer_track');
	}
}

function recalculate_actual_stock($prodid)
{
	global $db,$ecom_siteid;
	$stock = 0;
	$stock_exists = false;
	// Get the basic details of current product from products table
	$sql_prod = "SELECT product_variablestock_allowed,product_webstock,product_hide_on_nostock,product_alloworder_notinstock FROM products WHERE product_id=$prodid";
	$ret_prod = $db->query($sql_prod);
	if ($db->num_rows($ret_prod))
	{
		$row_prod = $db->fetch_array($ret_prod);
		if($row_prod['product_variablestock_allowed'] == 'Y') // Case of variable stock exists
		{
			// ** If variable stock is maintained for current product, then set the webstock
			// and the fixed stock in various shops for current product to 0
			$update_array										= array();
			$update_array['product_webstock']		= 0;
			$update_array['product_actualstock']		= 0;
			$db->update_from_array($update_array,'products',array('product_id'=>$prodid));

			$update_array				= array();
			$update_array['shop_stock']	= 0;
			$db->update_from_array($update_array,'product_shop_stock',array('products_product_id'=>$prodid));
			$comb_arr = array();
			// Get the combinations for current product
			$sql_comb = "SELECT comb_id,web_stock FROM product_variable_combination_stock WHERE products_product_id=$prodid";
			$ret_comb = $db->query($sql_comb);
			if ($db->num_rows($ret_comb))
			{
				while($row_comb = $db->fetch_array($ret_comb))
				{
					$combid = $row_comb['comb_id'];
					$stock += $row_comb['web_stock']; // webstock for current combination
					// Get the sum of stocks existing for current product from product_shop_variable_combination_stock table
					$sql_shop = "SELECT sum(shop_stock) FROM product_shop_variable_combination_stock WHERE products_product_id=$prodid
								AND comb_id=$combid";
					$ret_shop = $db->query($sql_shop);
					list($shop_stock) = $db->fetch_array($ret_shop);
					$stock += $shop_stock; // adding the sum of stock in shops for current combination
					$var_actualstock = $row_comb['web_stock'] + $shop_stock; // actual stock to be placed in the

					if ($var_actualstock>0)
					$stock_exists = true;
					//Updating the actual_stock in product_variable_combination_stock table
					$update_array					= array();
					$update_array['actual_stock']	= $var_actualstock;
					$db->update_from_array($update_array,'product_variable_combination_stock',array('comb_id'=>$combid));
				}
				// Updating the product_actualstock field with the total of variable combination stock value
				$update_sql = "UPDATE 
									products 
								SET 
									product_actualstock = $stock 
								WHERE 
									product_id = $prodid 
									AND sites_site_id =$ecom_siteid 
								LIMIT 
									1";
				$db->query($update_sql);
			}
		}
		else // Case variable stock does not exists
		{
			$stock = $row_prod['product_webstock'];// getting the webstock
			// Get the sum of stocks existing for current product from product_shop_stock table
			$sql_shop = "SELECT sum(shop_stock) FROM product_shop_stock WHERE products_product_id=$prodid";
			$ret_shop = $db->query($sql_shop);
			list($shop_stock) = $db->fetch_array($ret_shop);
			$stock += $shop_stock;

			if ($stock>0)
			$stock_exists = true;
			//Updating the product_actualstock in products table with the calculated value
			$update_array									= array();
			$update_array['product_actualstock']	= $stock;
			$db->update_from_array($update_array,'products',array('product_id'=>$prodid));

			// Making the stock value to 0 for current product in table
			// 1. product_variable_combination_stock
			// 2. product_shop_variable_combination_stock

			$update_array						= array();
			$update_array['web_stock']			= 0;
			$update_array['actual_stock']		= 0;
			$db->update_from_array($update_array,'product_variable_combination_stock',array('products_product_id'=>$prodid));

			$update_array						= array();
			$update_array['shop_stock']			= 0;
			$db->update_from_array($update_array,'product_shop_variable_combination_stock',array('products_product_id'=>$prodid));


		}
		// If stock exists then reset the status of allow preorder for current product
		if ($stock_exists)
		{
			$update_array									= array();
			$update_array['product_preorder_allowed']		= 'N';
			$update_array['product_total_preorder_allowed']	= 0;
			$update_array['product_instock_date']			= '0000-00-00';
			$db->update_from_array($update_array,'products',array('product_id'=>$prodid));
		}
		else // case if stock does not exists for current product
		{
			if($row_prod['product_hide_on_nostock']=='Y' and $row_prod['product_alloworder_notinstock']=='N') // case if product is to be made hidden. Hiding is allowed when hide on no stock is set to Y and allow stock even if stock in not there option to 'N'
			{
				$sql_update = "UPDATE 
											products 
										SET 
											product_hide = 'Y' 
										WHERE 
											product_id = $prodid 
											AND sites_site_id = $ecom_siteid 
										LIMIT 
											1";
				$db->query($sql_update);
			}
		}
	}
}
// Function to get the value(s) of general settings field for sites
function get_general_settings($fields,$table='general_settings_sites_common')
{
	global $db,$ecom_siteid;
	$sql = "SELECT $fields FROM $table WHERE sites_site_id=$ecom_siteid";
	$ret  = $db->query($sql);
	if ($db->num_rows($ret))
	{
		$row = $db->fetch_array($ret);
	}
	return $row;
}
// Function to check whether a module is available for a site
function is_module_valid($module,$mod='any')
{
	global $db,$ecom_siteid;
	if ($mod=='any') // either in site_menu or mod_menu
	{
		// Check whether the current module is available in site_menu table for current site
		$sql_check = "SELECT a.menu_id FROM site_menu a,features b WHERE b.feature_modulename='$module' AND
		a.sites_site_id=$ecom_siteid AND a.features_feature_id = b.feature_id LIMIT 1";
		$ret_check = $db->query($sql_check);
		if($db->num_rows($ret_check))
		{
			return true;
		}
		else
		{
			// Check whether the current module is available in mod_menu table for current site
			$sql_check = "SELECT a.menu_id FROM mod_menu a,features b WHERE b.feature_modulename='$module' AND
			a.sites_site_id=$ecom_siteid AND a.features_feature_id = b.feature_id LIMIT 1";
			$ret_check = $db->query($sql_check);
			if($db->num_rows($ret_check))
			{
				return true;
			}
			else
			return false;
		}
	}
	elseif ($mod=='onsite') // Check in site_menu only
	{
		// Check whether the current module is available in site_menu table for current site
		$sql_check = "SELECT a.menu_id FROM site_menu a,features b WHERE b.feature_modulename='$module' AND
		a.sites_site_id=$ecom_siteid AND a.features_feature_id = b.feature_id LIMIT 1";
		$ret_check = $db->query($sql_check);
		if($db->num_rows($ret_check))
		{
			return true;
		}
		else
		return false;
	}
	elseif ($mod=='onconsole') // Check in mod_menu only
	{
		// Check whether the current module is available in mod_menu table for current site
		$sql_check = "SELECT a.menu_id FROM mod_menu a,features b WHERE b.feature_modulename='$module' AND
			a.sites_site_id=$ecom_siteid AND a.features_feature_id = b.feature_id LIMIT 1";
		$ret_check = $db->query($sql_check);
		if($db->num_rows($ret_check))
		{
			return true;
		}
		else
		return false;
	}
}
function getgallerysubdirectorymenu($id)
{
	global $db,$ecom_siteid;
	// Getting the subdirectories of a given directory
	$query = "SELECT directory_id,directory_name FROM images_directory WHERE sites_site_id=$ecom_siteid
							AND parent_id=$id ORDER BY directory_name";

	$result = $db->query($query);
	if($db->num_rows($result))
	{
		$mnu_str  .= "<ul>";
		while(list($id,$name) = $db->fetch_array($result))
		{
			$mnu_str .= "<li><a href='javascript:call_ajax_handle_subdirclick(\"".$id."\");' class='treenode'>".$name."</a>";
			// calling the function recurssively
			$mnu_str  .= getgallerysubdirectorymenu($id);
		}
		$mnu_str  .= "</ul>";
	}
	else
	{
		$mnu_str  .= "</li>";
	}
	return $mnu_str;
}
function getComponenttitle($featureid,$compid=0)
{
	global $db,$ecom_siteid;
	// get the name of the module
	$sql_mod = "SELECT feature_modulename,feature_name FROM features WHERE feature_id=$featureid";
	$ret_mod = $db->query($sql_mod);
	if($db->num_rows($ret_mod))
	{
		$row_mod = $db->fetch_array($ret_mod);
		$curmodule = $row_mod['feature_modulename'];
	}
	if($compid==0)
	$showname = stripslashes($row_mod['feature_name']);
	else
	{
		switch($curmodule)
		{
			case 'mod_productcatgroup':
			$sql = "SELECT catgroup_name as curname FROM product_categorygroup WHERE catgroup_id=".$compid;
			break;
			case 'mod_staticgroup':
			$sql = "SELECT group_name as curname FROM static_pagegroup WHERE group_id=".$compid;
			break;
			case 'mod_shelf':
			$sql = "SELECT shelf_name as curname FROM product_shelf WHERE shelf_id=".$compid;
			break;
			case 'mod_shelfgroup':
			$sql = "SELECT name as curname FROM shelf_group WHERE id=".$compid;
			break;
			case 'mod_adverts':
			$sql = "SELECT advert_title as curname FROM adverts WHERE advert_id=".$compid;
			break;
			case 'mod_combo':
			$sql = "SELECT combo_name as curname FROM combo WHERE combo_id=".$compid;
			break;
			case 'mod_survey':
			$sql = "SELECT survey_title as curname FROM survey WHERE survey_id=".$compid;
			break;
			case 'mod_shopbybrandgroup':
			$sql = "SELECT shopbrandgroup_name as curname FROM product_shopbybrand_group WHERE shopbrandgroup_id=".$compid;
			break;
		};
		if($sql)
		{
			$ret = $db->query($sql);
			if ($db->num_rows($ret))
			{
				$row = $db->fetch_array($ret);
				$showname = stripslashes($row['curname']);
			}
		}
	}
	return $showname;
}
function updateComponenttitle($dispid,$title)
{
	global $db,$ecom_siteid;

	//Update the title field of display_settings table
	$update_array								= array();
	$update_array['display_title']				= add_slash(trim($title));
	$db->update_from_array($update_array,'display_settings',array('display_id'=>$dispid));
}
function validate_attachment($filename,$filetype,$mod='')
{
	global $alert;
	if($_FILES[$filename]['name'])
	{
		$ext_arr = explode(".",$_FILES[$filename]['name']);
		if($filetype == 'Audio')
		{
			$len = count($ext_arr)-1;
			$valid_arr = array('mp3','wma','wav');
			if(!in_array(strtolower($ext_arr[$len]),$valid_arr))
			{
				$alert = 'Sorry!! Invalid Audio Format';
			}
		}
		elseif ($filetype == 'Video')
		{
			$len = count($ext_arr)-1;
			$valid_arr = array('mpg','mpeg','wmv');
			if(!in_array(strtolower($ext_arr[$len]),$valid_arr))
			{
				$alert = 'Sorry!! Invalid Video Format';
			}
		}
		elseif ($filetype == 'Pdf')
		{
			$len = count($ext_arr)-1;
			$valid_arr = array('pdf');
			if(!in_array(strtolower($ext_arr[$len]),$valid_arr))
			{
				$alert = 'Sorry!! PDF file expected';
			}
		}
	}
	else
	{
		if($mod=='')//done to handle the situation of image not selected in case of edit
		$alert = 'Select file';

	}
	if(!$alert)
	return true;
	else
	return false;
}
function save_attachment($filename,$img_id)
{
	global $image_path;
	$attach_path = "$image_path/attachments";
	if(!file_exists($attach_path)) mkdir($attach_path, 0777);
	$ext_arr = explode(".",$_FILES[$filename]['name']);
	$len = count($ext_arr)-1;
	$fname = $img_id.".".$ext_arr[$len];
	$res = move_uploaded_file($_FILES[$filename]['tmp_name'],$attach_path.'/'.$fname);
	if (!$res)
	{
		$ret_arr['alert'] 		= 'Upload Failed';
	}
	else
	{
		$ret_arr['alert'] 		= '';
		$ret_arr['extension'] 	= $ext_arr[$len];
		$ret_arr['filename'] 	= $fname;
	}
	return $ret_arr;
}
function save_attachment_icon($filename,$img_id)
{
	global $image_path;
	$attach_path = "$image_path/attachments/icons";
	if(!file_exists($attach_path)) mkdir($attach_path, 0777);
	$ext_arr = explode(".",$_FILES[$filename]['name']);
	$len = count($ext_arr)-1;
	$fname = $img_id.".".$ext_arr[$len];
	$res = move_uploaded_file($_FILES[$filename]['tmp_name'],$attach_path.'/'.$fname);
	if (!$res)
	{
		$ret_arr['alert'] 		= 'Icon Upload Failed';
	}
	else
	{
		$ret_arr['alert'] 		= '';
		$ret_arr['extension'] 	= $ext_arr[$len];
		$ret_arr['filename'] 		= $fname;
	}
	return $ret_arr;
}
function save_commonattachment($filename,$img_id)
{
	global $image_path;
	$attach_path = "$image_path/commonattachments";
	if(!file_exists($attach_path)) mkdir($attach_path, 0777);
	$ext_arr = explode(".",$_FILES[$filename]['name']);
	$len = count($ext_arr)-1;
	$fname = $img_id.".".$ext_arr[$len];
	$res = move_uploaded_file($_FILES[$filename]['tmp_name'],$attach_path.'/'.$fname);
	if (!$res)
	{
		$ret_arr['alert'] 		= 'Upload Failed';
	}
	else
	{
		$ret_arr['alert'] 		= '';
		$ret_arr['extension'] 	= $ext_arr[$len];
		$ret_arr['filename'] 	= $fname;
	}
	return $ret_arr;
}
function save_downloadable($filename,$img_id,$rem_name='')
{
	global $image_path;
	$download_path = "$image_path/product_downloads";
	
	if(!file_exists($download_path)) mkdir($download_path, 0777); 
	$ext_arr	 	= explode(".",$_FILES[$filename]['name']);
	$len 			= count($ext_arr)-1;
	if ($len>0)
	{
		$fname 		= $img_id.".".$ext_arr[$len];
		$res 			= move_uploaded_file($_FILES[$filename]['tmp_name'],$download_path.'/'.$fname);
		if (!$res)
		{
			$ret_arr['alert'] 		= 'Upload Failed';
		}
		else
		{
			$ret_arr['alert'] 			= '';
			$ret_arr['extension'] 	= $ext_arr[$len];
			$ret_arr['filename'] 		= $fname;
			if($rem_name!='')
			{
				$rem_ext_arr	 	= explode(".",$rem_name);
				$rem_len			= count($rem_ext_arr)-1;
				if($rem_ext_arr[$rem_len]!=$ext_arr[$len])
				{
					$del_name 		= $img_id.".".$rem_ext_arr[$rem_len];
					if ($del_name!='')
					{
						if (file_exists($download_path.'/'.$del_name))
							unlink($download_path.'/'.$del_name);
					}		
				}
			}
		}
	}
	else
	{
		$ret_arr['alert'] 		= 'Invalid Extension .. Upload Failed';
	}
	return $ret_arr;
}
function get_default_site_currency()
{
	global $db,$ecom_siteid;
	$sql_currency = "SELECT curr_sign_char FROM general_settings_site_currency WHERE sites_site_id=$ecom_siteid AND curr_default=1";
	$ret_currency = $db->query($sql_currency);
	if ($db->num_rows($ret_currency))
	{
		$row_currency	= $db->fetch_array($ret_currency);
		return $row_currency['curr_sign_char'];
	}
}
function save_registration_additional_fields($customer_id)
{
	global $db,$ecom_siteid;


	foreach ($_REQUEST as $k=>$v)
	{
		if (substr($k,0,2)=='e_'){ // to insert newly added feild while updating
		$sql_sel_element_id = "SELECT element_id,element_sections_section_id,element_type,element_label FROM elements WHERE sites_site_id = $ecom_siteid AND element_name = '$k' LIMIT 1 ";
		$ret_element_id = $db->query($sql_sel_element_id);
		list($element_id,$section_id,$element_type,$element_label) = $db->fetch_array($ret_element_id);

		$insert_array									= array();
		$insert_array['customers_customer_id']			= $customer_id;
		$insert_array['sites_site_id']					= $ecom_siteid;
		$insert_array['element_sections_section_id']	= $section_id;
		$insert_array['elements_element_id']			= $element_id;
		//$insert_array['element_sections_section_name']	= $row_dyn['section_name'];
		$insert_array['element_type']					= $element_type;
		$insert_array['reg_label']						= $element_label;
		if(is_array($v))
		$v = implode(",", $v);
		$insert_array['reg_val']						= add_slash($v);
		if($insert_array['reg_val'])
		$db->insert_from_array($insert_array,'customer_registration_values');

		}
	}
}
function update_customer_additional_fields($customer_id)
{
	global $db,$ecom_siteid;
	foreach ($_REQUEST as $k=>$v)
	{
		if (substr($k,0,4)=='New_'){ // to insert newly added feild while updating
		$sql_sel_element_id = "SELECT element_id,element_sections_section_id,element_type,element_label FROM elements WHERE sites_site_id = $ecom_siteid AND element_name = '".substr($k,4)."' LIMIT 1 ";
		$ret_element_id = $db->query($sql_sel_element_id);
		list($element_id,$section_id,$element_type,$element_label) = $db->fetch_array($ret_element_id);
		$sql_chk_existing = "SELECT id FROM customer_registration_values WHERE sites_site_id = $ecom_siteid AND elements_element_id = $element_id AND customers_customer_id = $customer_id  ";
		$ret_chk_existing = $db->query($sql_chk_existing);
		if(!$db->num_rows($ret_chk_existing)) {
			$insert_array									= array();
			$insert_array['customers_customer_id']			= $customer_id;
			$insert_array['sites_site_id']					= $ecom_siteid;
			$insert_array['element_sections_section_id']	= $section_id;
			$insert_array['elements_element_id']			= $element_id;
			//$insert_array['element_sections_section_name']	= $row_dyn['section_name'];
			$insert_array['element_type']					= $element_type;
			$insert_array['reg_label']						= $element_label;
			if(is_array($v))
			$v = implode(",", $v);
			$insert_array['reg_val']						= add_slash($v);
			if($insert_array['reg_val'])
			$db->insert_from_array($insert_array,'customer_registration_values');
		}

		}else if(substr($k,0,2)=='e_') //if (substr($k,0,11)=='Additional_')
		{
			$sql_sel_element_id = "SELECT element_id,element_sections_section_id,element_type,element_label FROM elements WHERE sites_site_id = $ecom_siteid AND element_name = '".$k."' LIMIT 1 ";
			$ret_element_id = $db->query($sql_sel_element_id);
			list($element_id,$section_id,$element_type,$element_label) = $db->fetch_array($ret_element_id);
			$update_array				= array();
			if(is_array($v))
			$v = implode(",", $v);
			$update_array['reg_val']	= add_slash($v);
			if($update_array['reg_val'])
			$db->update_from_array($update_array,'customer_registration_values',array('sites_site_id'=>$ecom_siteid,'customers_customer_id'=>$customer_id,'elements_element_id'=>$element_id));
			else
			$db->delete_from_array(array('sites_site_id'=>$ecom_siteid,'customers_customer_id'=>$customer_id,'elements_element_id'=>$element_id),'customer_registration_values');
		}
	}

}
// Function to get the help message(s) of each section
function get_help_messages($code)
{
	global $db,$ecom_siteid;
	$fields = 'help_help_message';
	$table  = 'console_help_messages';
	$sql    = "SELECT $fields FROM $table WHERE help_code='$code' LIMIT 1";
	
	$ret    = $db->query($sql);
	if ($db->num_rows($ret))
	{
		$row = $db->fetch_array($ret);
	}
	return $row['help_help_message'];
}
// Function to check whether the given date is valid
function is_valid_date($date,$format='normal',$sep='-')
{
	$date_arr 	= explode(" ",$date); // done to extract the time section if exists
	$t_date		= $date_arr[0];
	$sp_date	= explode($sep,$t_date); // splitting the date base on the seperator
	$valid_Date	= true;
	if(count($sp_date)!= 3) // check whether there is exactly 3 elements in array after splitting
	$valid_Date = false;
	if($valid_Date)
	{
		// Check whether all the splitted elements are valid
		if(!is_numeric($sp_date[0]) or $sp_date[0]==0 or !is_numeric($sp_date[1]) or $sp_date[1]==0 or !is_numeric($sp_date[2]) or $sp_date[2]==0)
		$valid_Date = false;
		else
		{
			if($sp_date[0]<1 or $sp_date[1]<1 or $sp_date[2]<1)
			$valid_Date = false;
		}
	}
	if($valid_Date)
	{
		switch($format)
		{
			case 'normal':
			if (!checkdate($sp_date[1],$sp_date[0],$sp_date[2]))
			$valid_Date = false;
			break;
			case 'mysql':
			if (!checkdate($sp_date[1],$sp_date[2],$sp_date[0]))
			$valid_Date = false;
			break;
		};
	}
	return $valid_Date;
}

// ############################################################
// 					Cache removing functions
// ############################################################

function delete_body_cache()			// Function to delete cache for body
{
	global $image_path,$ecom_siteid;
	$cache_path = $image_path.'/cache/body';
	if ($cache_path=='' or !$cache_path)		// just for a double protection
		exit;
	if (file_exists($cache_path))				// Check whether directory exists
	{
		if (is_dir($cache_path))
		{
			$dirhandle=opendir($cache_path);
			while(($file = readdir($dirhandle)) !== false)
			{
				if (($file!=".")&&($file!=".."))
				{
					$currentfile=$cache_path."/".$file;
					if (!$i) $i = 0;
					if(!is_dir($currentfile))
					{
						$file_arr = explode('.',$file);
						if($file_arr[1]=='txt')
						{
						 	unlink($currentfile);
						}
					}
					$i++;
				}
			}
		}
	}
}
function delete_advert_cache($id)			// Function to delete cache for component advert
{
	global $image_path,$ecom_siteid;
	if ($id)
	{
		$cache_path = $image_path.'/cache/advert';
		if (file_exists($cache_path))				// Check whether directory exists
		{
			if ($root = @opendir($cache_path))		// open the directory
			{
				while ($file=readdir($root))		// reading the files in current directory
				{
					if($file=="." || $file=="..")
					{
						continue;
					}
					else
					{
						$file_arr 	= explode("_",$file);			// exploding based on /
						$id_arr		= explode(".",$file_arr[2]); 	// exploding based on .
						if ($id==$id_arr[0] and $id_arr[1]=='txt') 	// check if id match
						{
							if (file_exists($cache_path.'/'.$file))
							{
								//echo $cache_path.'/'.$file;
								unlink($cache_path.'/'.$file);		// delete the cache file
							}
						}
					}

				}
			}
		}
	}
}
function delete_bestseller_cache()		// Function to delete cache for best sellers
{
	global $image_path,$ecom_siteid;
	$cache_path = $image_path.'/cache/bestseller';
	if ($cache_path=='' or !$cache_path)		// just for a double protection
	exit;
	if (file_exists($cache_path))				// Check whether directory exists
	{
		if (is_dir($cache_path))
		{
			$dirhandle=opendir($cache_path);
			while(($file = readdir($dirhandle)) !== false)
			{
				if (($file!=".")&&($file!=".."))
				{
					$currentfile=$cache_path."/".$file;
					if (!$i) $i = 0;
					if(!is_dir($currentfile))
					{
						$file_arr = explode('.',$file);
						if($file_arr[1]=='txt')
						{
						 	unlink($currentfile);
						}
					}
					$i++;
				}
			}
		}
	}
}
function delete_catgroup_cache($id)				// Function to delete cache for category group
{
	global $image_path,$ecom_siteid;
	$cache_path = $image_path.'/cache/catgroup';
	if ($id)
	{
		if (file_exists($cache_path))				// Check whether directory exists
		{
			if ($root = @opendir($cache_path))		// open the directory
			{
				while ($file=readdir($root))		// reading the files in current directory
				{
					if($file=="." || $file=="..")
					{
						continue;
					}
					else
					{
						$file_arr 	= explode("_",$file);					// exploding based on /
						$id_arr		= explode(".",$file_arr[1]);		// exploding based on .
						if ($id==$id_arr[0] and $id_arr[1]=='txt')	// check if id match
						{
							if (file_exists($cache_path.'/'.$file))
							unlink($cache_path.'/'.$file);					// delete the cache file
						}
					}

				}
			}
		}
		// Deleting the category cache also
		$cache_path = $image_path.'/cache/category';
		if (file_exists($cache_path))				// Check whether directory exists
		{
			if ($root = @opendir($cache_path))		// open the directory
			{
				while ($file=readdir($root))		// reading the files in current directory
				{
					if($file=="." || $file=="..")
					{
						continue;
					}
					else
					{
						/*$file_arr 	= explode("_",$file);			// exploding based on /
						$id_arr		= explode(".",$file_arr[1]);	// exploding based on .*/
						$file_arr 		= explode("-",$file);			// exploding based on -
						$id_arr		= explode(".",$file_arr[1]); 	// exploding based on .
						if ($id==$id_arr[0] and $id_arr[1]=='txt')	// check if id match
						{
							if (file_exists($cache_path.'/'.$file))
							unlink($cache_path.'/'.$file);		// delete the cache file
						}
					}

				}
			}
		}
	}
}
// Function to delete cache for category
function delete_category_cache($id)
{
	global $image_path,$db;
	if ($id)
	{
		// Find the ids of category groups groups in which the current category is mapped with
		$sql_ct = "SELECT catgroup_id FROM product_categorygroup_category WHERE
					category_id=$id";
		$ret_ct = $db->query($sql_ct);
		if ($db->num_rows($ret_ct))
		{
			while($row_ct = $db->fetch_array($ret_ct))
			{
				$ct = $row_ct['catgroup_id'];
				if ($ct)
				delete_catgroup_cache($ct);
			}
		}
		$cache_path = $image_path.'/cache/category';
		if (file_exists($cache_path))				// Check whether directory exists
		{
			if ($root = @opendir($cache_path))		// open the directory
			{
				while ($file=readdir($root))		// reading the files in current directory
				{
					if($file=="." || $file=="..")
					{
						continue;
					}
					else
					{
						$file_arr 		= explode("-",$file);			// exploding based on -
						$id_arr		= explode(".",$file_arr[2]); 	// exploding based on .
						if ($id==$id_arr[0] and $id_arr[1]=='txt')	// check if id match
						{
							if (file_exists($cache_path.'/'.$file))
							unlink($cache_path.'/'.$file);		// delete the cache file
						}
					}

				}
			}
		}
	}
}
function delete_combo_cache($id)			// Function to delete cache for component product shops
{
	global $image_path,$ecom_siteid;
	if ($id)
	{
		$cache_path = $image_path.'/cache/combo';
		if (file_exists($cache_path))				// Check whether directory exists
		{
			if ($root = @opendir($cache_path))		// open the directory
			{
				while ($file=readdir($root))		// reading the files in current directory
				{
					if($file=="." || $file=="..")
					{
						continue;
					}
					else
					{
						$file_arr 	= explode("_",$file);			// exploding based on /
						$id_arr		= explode(".",$file_arr[2]); 	// exploding based on .
						if ($id==$id_arr[0] and $id_arr[1]=='txt') 	// check if id match
						{
							if (file_exists($cache_path.'/'.$file))
							{
								unlink($cache_path.'/'.$file);		// delete the cache file
							}
						}
					}

				}
			}
		}
	}
}

function delete_compshelf_cache($id)			// Function to delete cache for component shelves
{
	global $image_path,$ecom_siteid;
	$cache_path = $image_path.'/cache/shelf';
	if ($id)
	{
		if (file_exists($cache_path))				// Check whether directory exists
		{
			if ($root = @opendir($cache_path))		// open the directory
			{
				while ($file=readdir($root))		// reading the files in current directory
				{
					if($file=="." || $file=="..")
					{
						continue;
					}
					else
					{
						//echo '<br>'.$file;
						$file_arr 	= explode("_",$file);			// exploding based on /
						$id_arr		= explode(".",$file_arr[4]); 	// exploding based on .
						if ($id==$id_arr[0] and $id_arr[1]=='txt') 	// check if id match
						{
							if (file_exists($cache_path.'/'.$file))
								unlink($cache_path.'/'.$file);		// delete the cache file
						}
					}

				}
			}
		}
	}
}
function delete_compshopgroup_cache($id)			// Function to delete cache for component product shops
{
	global $image_path,$ecom_siteid;
	if ($id)
	{
		$cache_path = $image_path.'/cache/shop';
		if (file_exists($cache_path))				// Check whether directory exists
		{
			if ($root = @opendir($cache_path))		// open the directory
			{
				while ($file=readdir($root))		// reading the files in current directory
				{
					if($file=="." || $file=="..")
					{
						continue;
					}
					else
					{
						$file_arr 	= explode("_",$file);			// exploding based on /
						$id_arr		= explode(".",$file_arr[2]); 	// exploding based on .
						if ($id==$id_arr[0] and $id_arr[1]=='txt') 	// check if id match
						{
							if (file_exists($cache_path.'/'.$file))
							{
								unlink($cache_path.'/'.$file);		// delete the cache file
							}
						}
					}

				}
			}
		}
	}
}
function delete_statgroup_cache($id)			// Function to delete cache for static page groups
{
	global $image_path,$ecom_siteid;
	if ($id)
	{
		$cache_path = $image_path.'/cache/statgroup';
		if (file_exists($cache_path))				// Check whether directory exists
		{
			if ($root = @opendir($cache_path))		// open the directory
			{
				while ($file=readdir($root))		// reading the files in current directory
				{
					if($file=="." || $file=="..")
					{
						continue;
					}
					else
					{
						$file_arr 	= explode("_",$file);			// exploding based on /
						$id_arr		= explode(".",$file_arr[1]); 	// exploding based on .
						if ($id==$id_arr[0] and $id_arr[1]=='txt') 	// check if id match
						{
							if (file_exists($cache_path.'/'.$file))
							unlink($cache_path.'/'.$file);		// delete the cache file
						}
					}
				}
			}
		}
	}
}
function clear_all_cache($cache_path='')
{
	global $image_path;
	if ($cache_path=='' or !$cache_path)		// If parameter is empty then set it to the root of cache
	$cache_path = $image_path.'/cache';
	if ($cache_path=='' or !$cache_path)		// just for a double protection
	exit;

	if (file_exists($cache_path))				// Check whether directory exists
	{
		if (is_dir($cache_path))
		{
			$dirhandle=opendir($cache_path);
			while(($file = readdir($dirhandle)) !== false)
			{
				if (($file!=".")&&($file!=".."))
				{
					$currentfile=$cache_path."/".$file;
					if (!$i) $i = 0;
					if(!is_dir($currentfile))
					{
						$file_arr = explode('.',$file);
						/*if(substr($cache_path,-13)=='websitelayout')
						{
							if($file_arr[1]=='txt' or $file_arr[1]=='php')
							{
								unlink($currentfile);
							}
						}
						else
						{*/
						//echo '<br> filename '.$currentfile;
							if($file_arr[1]=='txt')
							{
								//echo "<br>$currentfile ---> Unlinked";
								unlink($currentfile);
							}
						/*}	*/
					}
					$i++;
					if(is_dir($currentfile))
					{
						clear_all_cache($currentfile);
					}
				}
			}
		}
	}
}

function delete_shelf_cache($id)
{
	global $image_path;
	if($id)
	{
		delete_compshelf_cache($id);	// delete cache for shelf
		//delete_body_cache();			// delete cache for body
	}
}
// Function to delete cache for static pages
function delete_static_page_cache($id)
{
	global $image_path,$db;
	if ($id)
	{
		// Find the ids of static page groups in which the current page exists
		$sql_pg = "SELECT static_pagegroup_group_id FROM static_pagegroup_static_page_map WHERE
					static_pages_page_id=$id";
		$ret_pg = $db->query($sql_pg);
		if ($db->num_rows($ret_pg))
		{
			while($row_pg = $db->fetch_array($ret_pg))
			{
				$pg = $row_pg['static_pagegroup_group_id'];
				if ($pg)
				delete_statgroup_cache($pg);
			}
		}
	}
}
// Function to delete cache for shops
function delete_shop_cache($id)
{
	global $image_path,$db;
	if ($id)
	{
		// Find the ids of static page groups in which the current page exists
		$sql_gp = "SELECT product_shopbybrand_shopbrandgroup_id FROM product_shopbybrand_group_shop_map WHERE
					product_shopbybrand_shopbrand_id=$id";
		$ret_gp = $db->query($sql_gp);
		if ($db->num_rows($ret_gp))
		{
			while($row_gp = $db->fetch_array($ret_gp))
			{
				$gp = $row_gp['product_shopbybrand_shopbrandgroup_id'];
				if ($gp)
				delete_compshopgroup_cache($gp);
			}
		}
		
		$cache_path = $image_path.'/cache/shop';
		if (file_exists($cache_path))				// Check whether directory exists
		{
			if ($root = @opendir($cache_path))		// open the directory
			{
				while ($file=readdir($root))		// reading the files in current directory
				{
					if($file=="." || $file=="..")
					{
						continue;
					}
					else
					{
						$file_arr 		= explode("-",$file);			// exploding based on -
						$id_arr		= explode(".",$file_arr[1]); 	// exploding based on .
						if ($id==$id_arr[0] and $id_arr[1]=='txt') 	// check if id match
						{
							if (file_exists($cache_path.'/'.$file))
							{
								unlink($cache_path.'/'.$file);		// delete the cache file
							}
						}
					}

				}
			}
		}
		
	}
}
/* Product cache delete function */
function delete_product_cache($product_id)
{
	global $image_path,$db,$ecom_siteid;
	if ($product_id)
	{
		delete_body_cache();			// delete cache for body
		delete_bestseller_cache(); 		// delete cache for bestseller

		// Find the combo ids which are linked with current product
		$sql_combo = "SELECT combo_combo_id FROM combo_products WHERE
						products_product_id = $product_id";
		$ret_combo = $db->query($sql_combo);
		if ($db->num_rows($ret_combo))
		{
			while ($row_combo = $db->fetch_array($ret_combo))
			{
				$cmb = $row_combo['combo_combo_id'];
				if ($cmb)
				delete_combo_cache($cmb); // Delete cache for combo deals
			}
		}
		// Find the shelves to which the current product is linked
		$sql_shelf = "SELECT product_shelf_shelf_id FROM product_shelf_product WHERE
						products_product_id = $product_id";
		$ret_shelf = $db->query($sql_shelf);
		if ($db->num_rows($ret_shelf))
		{
			while ($row_shelf = $db->fetch_array($ret_shelf))
			{
				$shf = $row_shelf['product_shelf_shelf_id'];
				if($shf)
				delete_compshelf_cache($shf); // Delete cache for shelf
			}
		}
		// Find the shops to which the current product is linked
		$sql_shop = "SELECT product_shopbybrand_shopbrand_id FROM product_shopbybrand_product_map WHERE
						products_product_id = $product_id";
		$ret_shop = $db->query($sql_shop);
		if ($db->num_rows($ret_shop))
		{
			while ($row_shop = $db->fetch_array($ret_shop))
			{
				$shp = $row_shop['product_shopbybrand_shopbrand_id'];
				if($shp)
				delete_shop_cache($shp); // Delete cache for shop
			}
		}
	}
}
function recreate_entire_websitelayout_cache()
{
	global $db,$ecom_siteid,$image_path,$ecom_themeid;
	// get the layout details available for current theme
	$sql_layout = "SELECT layout_id, layout_code 
						FROM 
							themes_layouts 
						WHERE 
							themes_theme_id = $ecom_themeid";
							
	$ret_layoutset = $db->query($sql_layout);
	if($db->num_rows($ret_layoutset))
	{
		while ($row_layoutset = $db->fetch_array($ret_layoutset))
		{
			save_cache_website_layout($row_layoutset['layout_id'],$row_layoutset['layout_code']);
		}	
	}	
}
/* Function to create website layout cache */
function save_cache_website_layout($layout_id,$layout_code)
{
	global $db,$ecom_siteid,$image_path;
	$cache_path = $image_path.'/cache/websitelayout';
	if(!file_exists($cache_path))
		mkdir ($cache_path,0777);
	// Get the list of components set in current layout
	$query	= "SELECT 
					a.display_title,a.display_id,a.display_position,feature_id,feature_modulename,display_component_id 
				FROM 
					display_settings a, features b 
				WHERE 
					a.sites_site_id='$ecom_siteid' 
					AND a.features_feature_id=b.feature_id 
					AND a.themes_layouts_layout_id='$layout_id' 
				ORDER BY 
					a.display_order;";		
	$result = $db->query($query);
	if($db->num_rows($result))
	{
		$fp = fopen($cache_path.'/'.$layout_code.'.php','w');
		fwrite($fp,'<?php'."\n");
		while ($row = $db->fetch_array($result))
		{
			$save_txt = '$display_layout_cache_array[\''.$layout_code.'\'][\''.$row['display_position'].'\'][] 
							= array ("display_title"		=>"'.$row['display_title'].'",
								"display_id"			=>'.$row['display_id'].',
								"feature_modulename"	=>"'.$row['feature_modulename'].'",
								"display_component_id"	=>'.$row['display_component_id'].'
								);'."\n"; 																		
			fwrite($fp,$save_txt);
		}
		fwrite($fp,'?>'."\n");
		fclose($fp);
	}
	else // case if no components. then delete the file
	{
		@unlink($cache_path.'/'.$layout_code.'.php');
	}
}
/////////********BY ANU ******///////////
function getParameters_DynamicFormAdd($position,$section_type)
{
	// #######################################################################################################
	// Start ## Section to builds the dynamic fields to be placed in the javascript validation
	// #######################################################################################################
	global $ecom_siteid,$db;
	$field_str = '';
	$field_msg = '';
	// Check whether any dynamic section set up for customer registration in current site  and is compulsory
	$sql_dyn = "SELECT section_id FROM element_sections WHERE sites_site_id=$ecom_siteid AND
			activate = 1 AND section_type = '".$section_type."' AND position= '".$position."' ORDER BY sort_no";
	$ret_dyn = $db->query($sql_dyn);
	if ($db->num_rows($ret_dyn))
	{
		while ($row_dyn = $db->fetch_array($ret_dyn))
		{
			$sql_elem = "SELECT element_id,element_type,element_name,error_msg FROM elements WHERE sites_site_id=$ecom_siteid AND
					element_sections_section_id =".$row_dyn['section_id']." AND mandatory ='Y' ORDER BY sort_no";
			$ret_elem = $db->query($sql_elem);
			if ($db->num_rows($ret_elem))
			{
				while ($row_elem = $db->fetch_array($ret_elem))
				{

					if($row_elem['error_msg'])// check whether error message is specified
					{
						if ($row_elem['element_type'] == 'checkbox')
						{
							// Check whether their exists values
							$sql_val = "SELECT value_id FROM element_value WHERE elements_element_id = ".$row_elem['element_id'];
							$ret_val = $db->query($sql_val);
							if ($db->num_rows($ret_val))
							{
								$mandatory_element_name = $row_elem['element_name'];
								$ret_values_array[0][$mandatory_element_name] = $row_elem['error_msg'];
							}

						}
						elseif ($row_elem['element_type'] == 'radio')
						{
							// Check whether their exists values
							$sql_val = "SELECT value_id FROM element_value WHERE elements_element_id = ".$row_elem['element_id'];
							$ret_val = $db->query($sql_val);
							if ($db->num_rows($ret_val))
							{
								$mandatory_element_name = $row_elem['element_name'];
								$ret_values_array[1][$mandatory_element_name] = $row_elem['error_msg'];
							}

						}
						else
						{
							if($field_str!='')
							{
								$field_str .= ',';
								$field_msg .= ',';
							}
							$field_str .= "'".trim($row_elem['element_name'])."'";
							$field_msg .= "'".trim($row_elem['error_msg'])."'";
						}
					}
				}
			}

		}
		if($field_str)
		{
			$ret_values_array[2][$field_str] = $field_msg;
		}
	}
	return $ret_values_array;
	// #######################################################################################################
	// Finish ## Section to builds the dynamic fields to be placed in the javascript validation
	// #######################################################################################################
}
function getParameters_DynamicFormEdit($position,$section_type,$customer_id){
	// #######################################################################################################
	// Start ## Section to builds the dynamic fields to be placed in the javascript validation
	// #######################################################################################################
	global $ecom_siteid,$db;
	$field_str = '';
	$field_msg = '';
	// Check whether any dynamic section set up for customer registration in current site  and is compulsory
	$sql_dyn = "SELECT section_id FROM element_sections WHERE sites_site_id=$ecom_siteid AND
			activate = 1 AND section_type = '".$section_type."' AND position= '".$position."' ORDER BY sort_no";
	$ret_dyn = $db->query($sql_dyn);
	if ($db->num_rows($ret_dyn))
	{
		while ($row_dyn = $db->fetch_array($ret_dyn))
		{
			$sql_elem = "SELECT  e.element_id,e.element_type,e.element_name,e.error_msg,crv.id,crv.reg_label,crv.reg_val  FROM elements e LEFT JOIN customer_registration_values crv ON (crv.elements_element_id=e.element_id)";
			if($customer_id)
			$sql_elem .= " AND customers_customer_id= $customer_id";
			$sql_elem .= " WHERE e.sites_site_id=$ecom_siteid AND
			e.element_sections_section_id =".$row_dyn['section_id']." AND mandatory ='Y'   ORDER BY sort_no";

			$ret_elem = $db->query($sql_elem);
			if ($db->num_rows($ret_elem))
			{
				while ($row_elem = $db->fetch_array($ret_elem))
				{

					if($row_elem['error_msg'])// check whether error message is specified
					{
						if ($row_elem['element_type'] == 'checkbox')
						{
							// Check whether their exists values
							$sql_val = "SELECT value_id FROM element_value WHERE elements_element_id = ".$row_elem['element_id'];
							$ret_val = $db->query($sql_val);
							if ($db->num_rows($ret_val))
							{
								if($row_elem['reg_val']=='')
								$mandatory_element_name = 'New_'.$row_elem['element_name'];
								else
								$mandatory_element_name = $row_elem['element_name'];
								$ret_values_array[0][$mandatory_element_name] = $row_elem['error_msg'];
							}
						}
						elseif ($row_elem['element_type'] == 'radio')
						{
							// Check whether their exists values
							$sql_val = "SELECT value_id FROM element_value WHERE elements_element_id = ".$row_elem['element_id'];
							$ret_val = $db->query($sql_val);
							if ($db->num_rows($ret_val))
							{
								if($row_elem['reg_val']=='')
								$mandatory_element_name = 'New_'.$row_elem['element_name'];
								else
								$mandatory_element_name = $row_elem['element_name'];
								$ret_values_array[1][$mandatory_element_name] = $row_elem['error_msg'];
							}
						}
						else
						{
							if($field_str!='')
							{
								$field_str .= ',';
								$field_msg .= ',';
							}
							if($row_elem['reg_val']=='')
							$mandatory_element_name = 'New_'.$row_elem['element_name'];
							else
							$mandatory_element_name = $row_elem['element_name'];
							$field_str .= "'".trim($mandatory_element_name)."'";
							$field_msg .= "'".trim($row_elem['error_msg'])."'";
						}
					}
				}
			}

		}
		if($field_str)
		{
			$ret_values_array[2][$field_str] = $field_msg;
		}
	}
	return $ret_values_array;
	// #######################################################################################################
	// Finish ## Section to builds the dynamic fields to be placed in the javascript validation
	// #######################################################################################################
}
////########################
//Function to get the image by Image ID and type
###############################
function getImageByID($imageid,$type='thumb'){
	global $ecom_siteid,$db;
	switch($type){
		case 'big':
		$sql_image = "SELECT image_bigpath FROM images WHERE sites_site_id = ".$ecom_siteid." AND image_id=".$imageid;
		$ret_image = $db->query($sql_image);
		$image	   = $db->fetch_array($ret_image);
		return $image_path = $image['image_bigpath'];
		break;
		case 'thumb':
		$sql_image = "SELECT image_thumbpath FROM images WHERE sites_site_id = ".$ecom_siteid." AND image_id=".$imageid;
		$ret_image = $db->query($sql_image);
		$image	   = $db->fetch_array($ret_image);
		return $image_path = $image['image_thumbpath'];
		break;
	}
}
/*
Function to get the name of payment type
*/
function getpaymenttype_Name($key)
{
	global $db,$ecom_siteid;
	$site_cap = '';
	if ($key)
	{
		if($key=='none')
		{
			return 'None';
		}
		else
		{
			$sql = "SELECT paytype_id,paytype_name
					FROM
						payment_types
					WHERE
						paytype_code = '".$key."'
					LIMIT
						1";
			$ret = $db->query($sql);
			if ($db->num_rows($ret))
			{
				$row = $db->fetch_array($ret);
				return $row['paytype_name'];
			}
		}
	}
}
/*
Function to get the name of payment method
*/
function getpaymentmethod_Name($key)
{
	global $db,$ecom_siteid;
	$site_cap = '';
	if ($key)
	{
		$sql = "SELECT paymethod_id,paymethod_name
				FROM
					payment_methods
				WHERE
					paymethod_key = '".$key."'
				LIMIT
					1";
		$ret = $db->query($sql);
		if ($db->num_rows($ret))
		{
			$row = $db->fetch_array($ret);
			return $row['paymethod_name'];
		}
	}
}
function getpaymentstatus_Name($key)
{
	global $db,$ecom_siteid;
	switch($key)
	{
		case 'pay_on_phone':
		case 'pay_on_account':
		case 'cash_on_delivery':
		case 'invoice':
		case 'cheque':
		case 'SELF':
			$caption = 'Not Paid';
		break;
		/* ------------------- 4 min finance - start ---------------------------*/
		case '4min_finance':
			$caption = 'Awaiting Finance Approval';
		break;
		case 'INITIALISE':
		case 'PREDECLINE':
		case 'ACCEPT':
		case 'DECLINE':
		case 'REFER':
		case 'VERIFIED':
		case 'AMENDED':
		case 'FULFILLED':
		case 'COMPLETE':
		case 'CANCELLED':
		case 'CANCEL':
		case 'ACTION-LENDER':
			$caption = ucwords(strtolower($key));
		break;
		case 'ACTION-CUSTOMER':
			$caption = 'Pending Verification';
		break;
		
		/* ------------------- 4 min finance - end ---------------------------*/
		case 'HSBC':
		case 'GOOGLE_CHECKOUT':
		case 'WORLD_PAY':
		case 'PAYPAL_EXPRESS':
		case 'PAYPALPRO':
		case 'PAYPAL_HOSTED':
		case 'NOCHEX':
		case 'REALEX':
		case 'ABLE2BUY':
		case 'PROTX_VSP':
		case 'PROTX':
		case 'BARCLAYCARD':
		case 'FIDELITY':
		case 'VERIFONE':
		case 'CARDSAVE':
			$caption = 'Check '.getpaymentmethod_Name($key);
		break;
		case 'Pay_Failed':
			$caption = 'Payment Failed';
		break;
		case 'Paid':
			$caption = 'Paid';
		break;
		case 'Pay_Hold':
			$caption = 'Placed on Account';
		break;
		case 'REFUNDED':
			$caption = 'Refunded';
		break;
		case 'DEFERRED':
			$caption = 'Deferred';
		break;
		case 'PREAUTH':
			$caption = 'Preauth';
		break;
		case 'AUTHENTICATE':
			$caption = 'Authenticate';
		break;
		case 'ABORTED':
			$caption = 'Deferred Aborted';
		break;
		case 'CANCELLED':
			$caption = 'Authorise Cancelled';
		break;
		case 'free':
			$caption = 'Free';
		break;
		case 'FRAUD_REVIEW':
			$caption = 'Fraud rule review check';
		break;
		
		/* additional statsus */
		case 'CARD':
			$caption = 'Credit Card';
		break;
		case 'CHEQUE':
			$caption = 'Cheque / DD';
		break;
		case 'BANK':
			$caption = 'Bank Transfer';
		break;
		case 'PHONE':
			$caption = 'Pay on Phone';
		break;
		case 'CASH':
			$caption = 'Cash';
		break;
		case 'OTHER':
			$caption = 'Other';
		break;
		case '3D_SEC_CHECK':
			$caption = 'Redirected for 3D Secure Password';
		break;
	};
	return $caption;
}
function getorderstatus_Name($key,$clean_output=false)
{
	global $db,$ecom_siteid;
	switch($key)
	{
		case 'NEW':
		$caption = 'Unviewed';
		break;
		case 'PENDING':
		$caption = 'Pending';
		break;
		case 'INPROGRESS':
		$caption = 'In Progress';
		break;
		case 'DESPATCHED':
		$caption = 'Despatched';
		break;
		case 'ONHOLD':
		$caption = 'On Hold';
		break;
		case 'BACK':
		$caption = 'Back Order';
		break;
		case 'CANCELLED':
		$caption = 'Cancelled';
		break;
		case 'NOT_AUTH':
		if ($clean_output==false)
			$caption = '<span style="color:#FF0000">Incomplete Order</span>';
		else
			$caption = 'Incomplete Order';
		break;

	};
	return $caption;
}
/* Function to show the name of various types of emails related to order */
function getorderEmailname($key)
{
	global $db,$ecom_siteid;
	$sql_email = "SELECT lettertemplate_title
					FROM
						general_settings_site_letter_templates
					WHERE
						sites_site_id = $ecom_siteid
						AND lettertemplate_letter_type = '".$key."'
					LIMIT
						1";
	$ret_email = $db->query($sql_email);
	if ($db->num_rows($ret_email))
	{
		$row_email 	= $db->fetch_array($ret_email);
		$caption	= stripslashes($row_email['lettertemplate_title']);
	}
	return $caption;
}
/* Function to save the email history for orders*/
function save_EmailHistory($emailid,$orderid)
{
	global $db, $ecom_siteid;

	// Inserting to order email history table
	$insert_array						= array();
	$insert_array['orders_order_id']	= $orderid;
	$insert_array['email_id']			= $emailid;
	$insert_array['send_date']			= 'now()';
	$insert_array['send_by']			= $_SESSION['console_id'];
	$db->insert_from_array($insert_array,'order_emails_console_send');

	// Updating the fields related to emails
	$update_array						= array();
	$update_array['email_sendonce']		= 1;
	$update_array['email_lastsenddate']	= 'now()';
	$db->update_from_array($update_array,'order_emails',array('email_id'=>$emailid));
	return true;
}
/* Function to resend a selected email */
function resend_orderEmail($emailid,$orderid)
{
	global $db,$ecom_siteid,$ecom_site_activate_invoice;
	// Get the details of the email
	$sql_email = "SELECT email_to,email_subject,email_messagepath,email_headers,
							email_type
						FROM
							order_emails
						WHERE
							orders_order_id = $orderid
							AND email_id = $emailid
						LIMIT
							1";
	$ret_email = $db->query($sql_email);
	if ($db->num_rows($ret_email))
	{
		$row_email 		= $db->fetch_array($ret_email);
		$to				= explode(",",stripslashes($row_email['email_to']));
		$header			= stripslashes($row_email['email_headers']);
		$subject		= stripslashes($row_email['email_subject']);
		//$content		= stripslashes($row_email['email_message']);
		$content		= read_email_from_file('ord',$emailid);
		if ($ecom_site_activate_invoice==1) // case if invoice feature is active for current website
		{
			if($row_email['email_type']=='ORDER_CONFIRM_CUST') // case if email currently being resend is the order confirmation to customer
			{
				$order_invoice_id = 0;
				if($ecom_site_activate_invoice==1)
				{
					$sql_inv = "SELECT invoice_id 
									FROM 
										order_invoice 
									WHERE 
										orders_order_id = $orderid 
									LIMIT 
										1";
					$ret_inv = $db->query($sql_inv);
					if ($db->num_rows($ret_inv))
					{
						$row_inv = $db->fetch_array($ret_inv);
						$order_invoice_id = $row_inv['invoice_id'];
					}					
				}
				$sql_check = "SELECT lettertemplate_disabled  
										FROM 
											general_settings_site_letter_templates  
										WHERE 
											lettertemplate_letter_type='ORDER_CONFIRM_INVOICE' 
											AND sites_site_id = $ecom_siteid 
										LIMIT 
											1";
				$ret_check = $db->query($sql_check);
				if($db->num_rows($ret_check))
				{
					$row_check = $db->fetch_array($ret_check);		
				}
				if($order_invoice_id>0 and $row_check['lettertemplate_disabled']==0) // case if got the invoice id
				{
					sendOrderMailWithAttachment($orderid,$order_invoice_id,$to[0],$subject,$content,1);
				}
				else // case if did not obtained the invoice id
				{
					mail($to[0],$subject,$content,$header);
				}
			}
			else 
			{
				for ($i=0;$i<count($to);$i++)
				{
					mail($to[$i],$subject,$content,$header);
				}
			}	
		}
		else
		{
			for ($i=0;$i<count($to);$i++)
			{
				mail($to[$i],$subject,$content,$header);
			}
		}	
	}
}

/* Function to save the email history for vouchers*/
function save_VoucherEmailHistory($emailid,$voucherid)
{
	global $db, $ecom_siteid;

	// Inserting to order email history table
	$insert_array								= array();
	$insert_array['gift_vouchers_voucher_id']	= $voucherid;
	$insert_array['email_id']					= $emailid;
	$insert_array['send_date']					= 'now()';
	$insert_array['send_by']					= $_SESSION['console_id'];
	$db->insert_from_array($insert_array,'gift_voucher_emails_console_send');

	// Updating the fields related to emails
	$update_array						= array();
	$update_array['email_sendonce']		= 1;
	$update_array['email_lastsenddate']	= 'now()';
	$db->update_from_array($update_array,'gift_voucher_emails',array('email_id'=>$emailid));
	return true;
}
/* Function to resend a selected email for vouchers */
function resend_voucherEmail($emailid,$voucherid)
{
	global $db,$ecom_siteid;
	// Get the details of the email
	$sql_email = "SELECT email_id,email_to,email_subject,email_headers,
							email_type
						FROM
							gift_voucher_emails
						WHERE
							gift_vouchers_voucher_id = $voucherid
							AND email_id = $emailid
						LIMIT
							1";
	$ret_email = $db->query($sql_email);
	if ($db->num_rows($ret_email))
	{
		$row_email 		= $db->fetch_array($ret_email);
		$to				= explode(",",stripslashes($row_email['email_to']));
		$header			= stripslashes($row_email['email_headers']);
		$subject		= stripslashes($row_email['email_subject']);
		//$content		= stripslashes($row_email['email_message']);
		
		$content		= read_email_from_file('vouch',$row_email['email_id']);
		for ($i=0;$i<count($to);$i++)
		{
			mail($to[$i],$subject,$content,$header);
		}
	}
}

/* Function to handle the case of cancelling the orders and returning the stock to respective products */
function do_ordercancelReturns($order_id,$stat_arr)
{
	global $db,$ecom_siteid;
	$proceed_calculation = 1;
	// get the order status for current order
	$sql_ords = "SELECT order_paystatus,order_paymenttype  
				FROM 
					orders 
				WHERE 
					order_id=$order_id   
					AND sites_site_id=$ecom_siteid 
				LIMIT 
					1";
	$ret_ords = $db->query($sql_ords);
	if($db->num_rows($ret_ords))
	{
		$row_ords = $db->fetch_array($ret_ords);
		if($row_ords['order_paymenttype']=='credit_card')
		{
			if($row_ords['order_paystatus']=='Paid' or $row_ords['order_paystatus']=='FRAUD_REVIEW' or $row_ords['order_paystatus']=='Pay_Hold') // if payment status is paid or fraurd_review then calculations should be done
				$proceed_calculation = 1;
			else
				$proceed_calculation = 0;
		}		
	}
	
	
		// Check whether the current site is maintaining a stock
		$sql_stockmaintain = "SELECT product_maintainstock,product_decrementstock 
								FROM
									general_settings_sites_common
								WHERE
									sites_site_id = $ecom_siteid
								LIMIT
									1";
		$ret_stockmaintain = $db->query($sql_stockmaintain);
		if($db->num_rows($ret_stockmaintain))
			$row_stockmaintain = $db->fetch_array($ret_stockmaintain);
		// Get few of the details required related to current order
		/* Donate bonus Start */
		$sql_ord = "SELECT customers_customer_id,order_bonuspoints_used,gift_vouchers_voucher_id,
							costperclick_id,order_bonuspoint_inorder,promotional_code_code_id,
							order_bonuspoints_donated  
						FROM
							orders
						WHERE
							order_id=$order_id
						LIMIT
							1";
		/* Donate bonus End */
		$ret_ord = $db->query($sql_ord);
		if ($db->num_rows($ret_ord))
		{
			$row_ord = $db->fetch_array($ret_ord);
		}
		// Check whether there exists any product which have been refunded or despatched in current order details
		$sql_check = "SELECT orderdet_id
						FROM
							order_details
						WHERE
							orders_order_id = $order_id
							AND (order_refunded = 'Y' OR order_dispatched='Y')
						LIMIT
							1";
		$ret_check = $db->query($sql_check);
		$prodext_arr	= array(-1);
		if ($db->num_rows($ret_check)==0 or $stat_arr['force_cancel']==1) // case if no products refunded or despatched in current order or forcefully done by console user
		{
				if ($proceed_calculation==1)
				{
				// Get all the products involved with the current order
				$sql_prods = "SELECT products_product_id,order_qty,order_refunded,order_dispatched,order_stock_combination_id,order_preorder 
								FROM
									order_details
								WHERE
									orders_order_id = $order_id";
				$ret_prods = $db->query($sql_prods);
				if ($db->num_rows($ret_prods))
				{
					while ($row_prods = $db->fetch_array($ret_prods))
					{
						// Check whether the product is in fixed or variable stock
						$sql_prodcheck = "SELECT product_variablestock_allowed,product_preorder_allowed,product_total_preorder_allowed 
											FROM
												products
											WHERE
												product_id = ".$row_prods['products_product_id']."
											LIMIT
												1";
						$ret_prodcheck = $db->query($sql_prodcheck);
						if ($db->num_rows($ret_prodcheck))
						{
							$row_prodcheck = $db->fetch_array($ret_prodcheck);
							if ($row_prodcheck['product_preorder_allowed']=='N' and $row_prods['order_preorder']=='N') // return the stock only if the product was not is preorder at the time of ordering and also at present
							{
								if($row_stockmaintain['product_maintainstock']==1 and $row_stockmaintain['product_decrementstock']==1 and $stat_arr['stock_return']==1)// Do the following only if current site maintains stock (settings from console)
								{
									// Check whether direct stock or combination stock
									if ($row_prods['order_stock_combination_id']==0 or $row_prodcheck['product_variablestock_allowed']=='N')// fixed stock
									{
										// Check whether the product is still in fixed stock
										if($row_prodcheck['product_variablestock_allowed']=='N')
										{ 
											// Increment the web stock and actual stock field for the current product by qty in order
											// and also making the preorder to N. This is done to handle the case of curreent product is placed in preorder
			
											 $sql_update = "UPDATE products
															SET
																product_webstock 	= product_webstock + ".$row_prods['order_qty']." ,
																product_actualstock = product_actualstock + ".$row_prods['order_qty']." ,
																product_preorder_allowed = 'N',product_total_preorder_allowed=0,
																product_instock_date='0000-00-00'
															WHERE
																product_id = ".$row_prods['products_product_id']."
																	LIMIT
																1";
			
											$db->query($sql_update);
										}
									}
									else // case of variable stock
									{
										// Check whether the product is still in variable stock
										if($row_prodcheck['product_variablestock_allowed']=='Y')
										{
											// Check whether the combination still exists
											$sql_check = 'SELECT comb_id
															FROM
																product_variable_combination_stock
															WHERE
																comb_id = '.$row_prods['order_stock_combination_id']."
																AND products_product_id = ".$row_prods['products_product_id']."
															LIMIT
																1";
											$ret_check = $db->query($sql_check);
											if ($db->num_rows($ret_check)) // case if combination already exists
											{  
												$sql_update = "UPDATE product_variable_combination_stock
																SET
																	web_stock = web_stock + ".$row_prods['order_qty'].",
																	actual_stock = actual_stock + ".$row_prods['order_qty']."
																WHERE
																	comb_id = ".$row_prods['order_stock_combination_id']."
																	AND products_product_id = ".$row_prods['products_product_id']."
																LIMIT
																	1";
												$db->query($sql_update);
			
												//Updating the products table in the fields product_preorder_allowed to make it to 'N'
												$sql_upd = "UPDATE products
																SET
																	product_preorder_allowed ='N',product_total_preorder_allowed=0,
																	product_instock_date='0000-00-00'
																WHERE
																	product_id = ".$row_prods['products_product_id']."
																LIMIT
																	1";
												$db->query($sql_upd);
											}
										}
									}
								}
							}
							elseif($row_prodcheck['product_preorder_allowed']=='Y' and $row_prods['order_preorder']=='Y')// case product was in preorder at the time of ordering also is in preorder at present
							{
								if(!in_array($row_prods['products_product_id'],$prodext_arr))// This is done to handle the case to decrement the total preorder value only once even if the product exists in cart more than once
								{
									$update_sql = "UPDATE 
														products 
													SET 
														product_total_preorder_allowed = product_total_preorder_allowed + 1 
													WHERE 
														product_total_preorder_allowed > 0 
														AND product_id = ".$row_prods['products_product_id']." 
														AND sites_site_id = $ecom_siteid  
													LIMIT 
														1";
									$db->query($update_sql);
									$prodext_arr[] = $row_prods['products_product_id'];
								}	
							}
						}
					}
				}
				
			// ############################################################################
			// Check whether bonus points used in current order.
			// ############################################################################
			if($row_ord['order_bonuspoints_used']>0 and $row_ord['customers_customer_id']>0 and $stat_arr['bonusused_return']==1)
			{
				// Return the bonus points used to the respective customer account
				$sql_bonus = "UPDATE customers
								SET
									customer_bonus = customer_bonus + ".$row_ord['order_bonuspoints_used']."
								WHERE
									customer_id = ".$row_ord['customers_customer_id']."
									AND sites_site_id = $ecom_siteid
								LIMIT
									1";
				$db->query($sql_bonus);
			}
			/* Donate bonus Start */
			if($row_ord['order_bonuspoints_donated']>0 and $row_ord['customers_customer_id']>0 and $stat_arr['bonusdonated_return']==1)
			{
				// Return the bonus points used to the respective customer account
				$sql_bonus = "UPDATE customers
								SET
									customer_bonus = customer_bonus + ".$row_ord['order_bonuspoints_donated']."
								WHERE
									customer_id = ".$row_ord['customers_customer_id']."
									AND sites_site_id = $ecom_siteid
								LIMIT
									1";
				$db->query($sql_bonus);
			}
			/* Donate bonus End */	
			// ############################################################################
			// Check whether customer received any bonus points due to this order
			// ############################################################################
			if($row_ord['order_bonuspoint_inorder']>0 and $row_ord['customers_customer_id']>0 and $stat_arr['bonusearned_return']==1)
			{
				// Get how many bonus points exists for current customer
				$sql_cust = "SELECT customer_bonus
								FROM
									customers
								WHERE
									customer_id = ".$row_ord['customers_customer_id']."
								LIMIT
									1";
				$ret_cust = $db->query($sql_cust);
				if ($db->num_rows($ret_cust))
				{
					$row_cust 	= $db->fetch_array($ret_cust);
					$upd_bonus	= 0;
					// case if customer bonus is >= to the bonus earned from current order
					/*if($row_cust['customer_bonus']>=$row_ord['order_bonuspoint_inorder'])
					{*/
						$upd_bonus = $row_cust['customer_bonus'] - $row_ord['order_bonuspoint_inorder'];
					/*}
					else
						$upd_bonus = 0;
					*/	
					// Updating the customer table with this new bonus value
					$sql_upd = "UPDATE customers
									SET
										customer_bonus = $upd_bonus
									WHERE
										customer_id = ".$row_ord['customers_customer_id']."
									LIMIT
										1";
					$db->query($sql_upd);
				}
				// Return the bonus points used to the respective customer account
				/*$sql_bonus = "UPDATE customers
								SET
									customer_bonus = customer_bonus + ".$row_ord['order_bonuspoints_used']."
								WHERE
									customer_id = ".$row_ord['customers_customer_id']."
									AND sites_site_id = $ecom_siteid
								LIMIT
									1";
				$db->query($sql_bonus);*/
				}	
			
			// ############################################################################
			// Check whether voucher exists in current order
			// ############################################################################
			if($row_ord['gift_vouchers_voucher_id']>0 and $stat_arr['maxvoucher_return']==1)
			{
				// Check whether current voucher still exists
				$sql_check = "SELECT voucher_id
								FROM
									gift_vouchers
								WHERE
									voucher_id = ".$row_ord['gift_vouchers_voucher_id']."
									AND sites_site_id = $ecom_siteid
									AND voucher_max_usage >0
								LIMIT
									1";
				$ret_check = $db->query($sql_check);
				if ($db->num_rows($ret_check))
				{
					$sql_update = "UPDATE gift_vouchers
										SET
											voucher_usage = voucher_usage - 1
										WHERE
											voucher_id = ".$row_ord['gift_vouchers_voucher_id']."
											and sites_site_id = $ecom_siteid
										LIMIT
											1";
					$db->query($sql_update);
				}
			}
			}
			// ############################################################################
			// Check whether promotional code exists in current order
			// ############################################################################
			if($row_ord['promotional_code_code_id']>0 and $stat_arr['maxvoucher_return']==1)
			{
				$sql_del = "DELETE FROM 
								order_promotionalcode_track 
							WHERE 
								orders_order_id = $order_id 
							LIMIT 
								1";
				$db->query($sql_del);
				if ($proceed_calculation==1)
				{
					if($stat_arr['maxvoucher_return']==1)
					{
						// Check whether this code still exists and if limit is to be decremented
						$sql_pc = "SELECT code_id, code_unlimit_check, code_limit, code_usedlimit,code_customer_unlimit_check,
						code_customer_limit,code_customer_usedlimit,code_login_to_use 
										FROM
											promotional_code
										WHERE
											sites_site_id = $ecom_siteid
											AND code_id=".$row_ord['promotional_code_code_id']."
										LIMIT
											1";
						$ret_pc = $db->query($sql_pc);
						if ($db->num_rows($ret_pc))
						{
							$row_pc = $db->fetch_array($ret_pc);
							if ($row_pc['code_unlimit_check']==0) // if not unlimited
							{
								$update_sql = "UPDATE promotional_code
														SET
															code_usedlimit = code_usedlimit - 1
														WHERE
															sites_site_id = $ecom_siteid 
															AND code_id = ".$row_ord['promotional_code_code_id']." 
															AND code_usedlimit > 0
															AND code_limit >= code_usedlimit
														LIMIT
															1";
								$db->query($update_sql);
							}
							// Check whether customer id is there in current order
							if($row_ord['customers_customer_id'])
							{
								if ($row_pc['code_login_to_use']==1 and $row_pc['code_customer_unlimit_check']==0 and $row_pc['code_customer_usedlimit']>0)
								{
									$sql_update = "UPDATE promotional_code 
														SET 
															code_customer_usedlimit = code_customer_usedlimit -1 
														WHERE 
															code_id = ".$row_ord['promotional_code_code_id']." 
															AND code_customer_usedlimit >0
														LIMIT 
															1";
									$db->query($sql_update);			
								}
							}
						}
					}
				}
			}
			// ############################################################################
			// Check whether this order is related to any cost per click section
			// ############################################################################
			if($row_ord['costperclick_id']>0)
			{
				// handle the reduction of total and other things from cost per click table here
	
				// THIS SECTION CAN BE IMPLEMENTED AFTER THE COSTPERCLICK DB DESIGN
	
	
	
	
			}
		}
		else
		{
			$ret_arr['order_id'] 	= $order_id;
			$ret_arr['msg']			= 'REFUND_OR_DESPATCH';
			return $ret_arr;
		}
	
}
/* Function to get the display logic to be included for alternate products */
function get_AlternateProductDetailsString($alt_prods,$rate,$symbol)
{
	global $db,$ecom_siteid,$ecom_hostname;
	
	if(check_IndividualSslActive())
	{
		$http = 'https://';
	}
	else
	{
		$http = 'http://';
	}
	
	$alt_str = '';
	// Case if alternate products selected while cancelling the order
	$style_head_main = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#ffffff;font-weight:bold;border-bottom:1px solid  #acacac;background-color:#acacac;'";
	$style_head = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:bold;border-bottom:1px solid  #f8f8f8;'";
	$style_desc = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;'";
	$style_desc_prodname ="style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;text-decoration:none;'";
	if($alt_prods!='')
	{
		$prods_arr = explode('~',$alt_prods);
		$prods_str = implode(",",$prods_arr);
		// Get the details of alternate products
		$sql_prod = "SELECT product_id,product_name,product_webprice,product_discount,
							product_discount_enteredasval
						FROM
							products
						WHERE
							product_id IN ($prods_str) ";
		$ret_prod = $db->query($sql_prod);
		if($db->num_rows($ret_prod))
		{
			$alt_str = '<table width="100%" border="0" cellspacing="1" cellpadding="1">
			 				<tr>
							<td colspan="4" align="left" '.$style_head_main.'>Alternate Products</td>
						  </tr>
						  <tr>
							<td width="4%" '.$style_head.'>#</td>
							<td width="46%" '.$style_head.'>Product Name </td>
							<td width="34%" align="right" '.$style_head.'>Price</td>
							<td width="16%" align="right" '.$style_head.'>Discount</td>
						  </tr>';
			$srno = 1;
			while($row_prod = $db->fetch_array($ret_prod))
			{
			$prodName = strip_url(stripslashes($row_prod['product_name']));
           $prodId = $row_prod['product_id'];
		   $productPageUrlHash = $http.$ecom_hostname."/".$prodName."-p$prodId.html";		
				$alt_str .='<tr>
								<td>'.$srno++.'</td>
								<td><a href='.$productPageUrlHash.' '.$style_desc_prodname.'>'.stripslashes($row_prod['product_name']).'</a></td>
								<td align="right" '.$style_desc.'>'.print_price_selected_currency($row_prod['product_webprice'],$rate,$symbol,true).'</td>
								<td align="right" '.$style_desc.'>';
				if($row_prod['product_discount_enteredasval']==1)
				$alt_str .= print_price_selected_currency($row_prod['product_discount'],$rate,$symbol,true);
				else
				$alt_str .= $row_prod['product_discount'].'%';
				$alt_str .='</td>
							</tr>';
			}
			$alt_str .= '</table>';
		}
	}
	return $alt_str;
}
/* Function to get the display logic for product details to be included in the mail */
function get_ProductsInOrdersForMail($order_id,$row_ords,$detail_arr='')
{
	global $db,$ecom_siteid,$ecom_hostname,$Captions_arr;
	
	if(check_IndividualSslActive())
	{
		$http = 'https://';
	}
	else
	{
		$http = 'http://';
	}
	
	$totals_req		= true;
	$qty_req 		= false;
	$det_arr 		= $detail_arr['prods'];
	$detqty_arr		= $detail_arr['qtys'];
	if (is_array($det_arr)) // Check whether only selected products are to be displayed
	{
		if(count($det_arr))
		{
			$additional_condition 	= " AND orderdet_id IN (".implode(",",$det_arr).") ";
			$totals_req				= false;
		}
	}
	if (is_array($detqty_arr)) // check whether quantity array exists
	{
		if(count($detqty_arr))
		{
			$qty_req				= true;
		}
	}
	$Captions_arr['CART'] 	= getCaptions('CART'); // Getting the captions to be used in this page
	// ##############################################################################
	// Product Details
	// ##############################################################################
	$style_head = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:bold;border-bottom:1px solid  #f8f8f8;'";
	$style_desc = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;'";
	$style_desc_prodname ="style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;text-decoration:none;'";
	$sql_prods  = "SELECT orderdet_id,product_name,products_product_id,order_qty,product_soldprice,order_retailprice,order_discount,
							order_discount_type,order_rowtotal
					FROM
						order_details
					WHERE
						orders_order_id = $order_id
						$additional_condition";
	$ret_prods = $db->query($sql_prods);
	if ($db->num_rows($ret_prods))
	{
		$prod_str	= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
		if($totals_req==true)
		{
			$prod_str	.= '<tr>
								<td align="left" width="40%"'.$style_head.'>'.$Captions_arr['CART']['CART_ITEM'].'</td>
								<td align="left" width="20%"'.$style_head.'>'.$Captions_arr['CART']['CART_PRICE'].'</td>
								<td align="left" width="15%"'.$style_head.'>'.$Captions_arr['CART']['CART_DISCOUNT'].'</td>
								<td align="left" width="25%" '.$style_head.'>'.$Captions_arr['CART']['CART_QTY'].'</td>
								<td align="left" width="25%" '.$style_head.'>'.$Captions_arr['CART']['CART_TOTAL'].'</td>
							</tr>';
		}
		elseif ($qty_req==true)
		{
			$prod_str	.= '<tr>
								<td align="left" width="40%" '.$style_head.'>'.$Captions_arr['CART']['CART_ITEM'].'</td>
								<td align="left" width="25%" '.$style_head.'>'.$Captions_arr['CART']['CART_QTY'].'</td>
								<td align="left" colspan="3" '.$style_head.'.>&nbsp;</strong></td>
							</tr>';
		}
		else
		{
			$prod_str	.= '<tr>
								<td align="left" width="40%" '.$style_head.'>'.$Captions_arr['CART']['CART_ITEM'].'</td>
								<td align="left" colspan="4" '.$style_head.'>&nbsp;</td>
							</tr>';
		}
		while ($row_prods = $db->fetch_array($ret_prods))
		{  
		$prodName = strip_url(stripslashes($row_prods['product_name']));
           $prodId = $row_prods['products_product_id'];
		   $productPageUrlHash = $http.$ecom_hostname."/".$prodName."-p$prodId.html";			
		 $qty = ($totals_req)?stripslashes($row_prods['order_qty']):$detqty_arr[$row_prods['orderdet_id']];
			if($totals_req==true)
			{
				$prod_str	.= '<tr>
								<td align="left" width="30%" '.$style_desc.'><a href='.$productPageUrlHash.' '.$style_desc_prodname.'>'.stripslashes($row_prods['product_name']).'</a></td>
								<td align="left" width="15%" '.$style_desc.'>'.print_price_selected_currency($row_prods['product_soldprice'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td>
								<td align="left" width="20%" '.$style_desc.'>'.print_price_selected_currency($row_prods['order_discount'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td>
								<td align="left" width="15%" '.$style_desc.'>'.$qty.'</td>
								<td align="right" width="20%" '.$style_desc.'>'.print_price_selected_currency($row_prods['order_rowtotal'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td>
								</tr>';
			}
			elseif ($qty_req==true)
			{
				$prod_str	.= '<tr>
								<td align="left" width="30%" '.$style_desc.'><a href='.$productPageUrlHash.' '.$style_desc_prodname.'>'.stripslashes($row_prods['product_name']).'</a></td>
								<td align="left" width="15%" '.$style_desc.'>'.$qty.'</td>
								<td align="left" colspan="3" '.$style_desc.'>&nbsp;</td>
								</tr>';
			}
			else
			{
				$prod_str	.= '<tr>
								<td align="left" width="30%" '.$style_desc.'><a href='.$productPageUrlHash.'  '.$style_desc_prodname.'>'.stripslashes($row_prods['product_name']).'</a></td>
								<td align="left" colspan="4" '.$style_desc.'>&nbsp;</td>
								</tr>';
			}
			// Check whether any variables exists for current product in order_details_variables
			$sql_var = "SELECT var_name,var_value
							FROM
								order_details_variables
							WHERE
								orders_order_id = $order_id
								AND order_details_orderdet_id =".$row_prods['orderdet_id'];
			$ret_var = $db->query($sql_var);
			if ($db->num_rows($ret_var))
			{
				while ($row_var = $db->fetch_array($ret_var))
				{
					$prod_str	.= '<tr>
									<td align="left" colspan="5" '.$style_head.'>'.stripslashes($row_var['var_name']).': '.stripslashes($row_var['var_value']).'</td>
									</tr>';
				}
			}
			// Check whether any variables messages exists for current product in order_details_messages
			$sql_msg = "SELECT message_caption,message_value
							FROM
								order_details_messages
							WHERE
								orders_order_id = $order_id
								AND order_details_orderdet_id =".$row_prods['orderdet_id'];
			$ret_msg = $db->query($sql_msg);
			if ($db->num_rows($ret_msg))
			{
				while ($row_msg = $db->fetch_array($ret_msg))
				{
					$prod_str	.= '<tr>
									<td align="left" colspan="5" style="padding-left:10px" '.$style_head.'>'.stripslashes($row_msg['message_caption']).':'.stripslashes($row_msg['message_value']).'</td>
									</tr>';
				}
			}
		}
		if($totals_req==true)
		{
			// ##################################################################################
			// Building order totals
			// ##################################################################################
			// subtotal
			$prod_str	.= '<tr>
									<td align="right" width="50%" colspan="2" '.$style_head.'>'.$Captions_arr['CART']['CART_TOTPRICE'].'</td>
									<td align="right" colspan="3" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_subtotal'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td>
							</tr>';
			// giftwrap total and delivery type total and tax total
			if($row_ords['order_giftwraptotal']>0)
			{
				$prod_str	.= '<tr>
									<td align="right" width="50%" colspan="2" '.$style_head.'>Gift Wrap Total</td>
									<td align="right" width="50%" colspan="3" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_giftwraptotal'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td>
									</tr>';
			}
			if($row_ords['order_deliverytotal']>0)
			{
				$prod_str	.= '<tr>
									<td align="right" width="50%" colspan="2" '.$style_head.'>Delivery Total</td>
									<td align="right" width="50%" colspan="3" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_deliverytotal'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td>
								</tr>';
			}
			if($row_ords['order_tax_total']>0)
			{
				$prod_str	.= '<tr>
									<td align="right" width="50%" colspan="2" '.$style_head.'>Total Tax</td>
									<td align="right" width="50%" colspan="3" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_tax_total'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td>
								</tr>';
			}

			// Customer / Corporate discount
			if ($row_ords['order_customer_discount_value']>0)
			{
				if ($row_ords['order_customer_or_corporate_disc']=='CUST')
				{
					if($row_ords['order_customer_discount_type']=='Disc_Group')
					$caption = 'Customer Group Discount ('.$row_ords['order_customer_discount_percent'].'%)';
					else
					$caption = 'Customer Discount ('.$row_ords['order_customer_discount_percent'].'%)';
					$caption_val = $row_ords['order_customer_discount_value'];
					$prod_str	.= '<tr>
										<td align="right" width="50%" colspan="2" '.$style_head.'>'.$caption.'</td>
										<td align="right" colspan="3" '.$style_desc.'>'.print_price_selected_currency($caption_val,$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td>
									</tr>';
				}
				else // case of corporate discount
				{
					$caption = 'Corporate Discount ('.$row_ords['order_customer_discount_percent'].'%)';
					$caption_val = $row_ords['order_customer_discount_value'];
					$prod_str	.= '<tr>
										<td align="right" width="50%" colspan="2" '.$style_head.'>'.$caption.'</td>
										<td align="right" colspan="3" '.$style_desc.'>'.print_price_selected_currency($caption_val,$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td>
									</tr>';
				}
			}
			if($row_ords['gift_vouchers_voucher_id'])
			{
				// Get the gift voucher details
				$sql_voucher = "SELECT voucher_value_used
									FROM
										order_voucher
									WHERE
										orders_order_id = $order_id
									LIMIT
										1";
				$ret_voucher = $db->query($sql_voucher);
				if ($db->num_rows($ret_voucher))
				{
					$row_voucher 	= $db->fetch_array($ret_voucher);
					$prod_str	.= '<tr><td align="left" width="50%" colspan="2" '.$style_head.'>Gift Voucher Discount</td><td align="left" width="50%" colspan="3" '.$style_desc.'>'.print_price_selected_currency($row_voucher['voucher_value_used'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
				}
			}
			elseif($row_ords['promotional_code_code_id'])
			{
				// Get the promotional code details
				$sql_prom = "SELECT code_number,code_lessval,code_type
									FROM
										order_promotional_code
									WHERE
										orders_order_id = $order_id
									LIMIT
										1";
				$ret_prom = $db->query($sql_prom);
				if ($db->num_rows($ret_prom))
				{
					$row_prom 	= $db->fetch_array($ret_prom);
					if ($row_prom['code_type']!='product') // show only if not of type 'product' if type is product discount will be shown with product listing
					{
						$prod_str	.= '<tr><td align="right" width="50%" colspan="2" '.$style_head.'>Promotional Code Discount</td><td align="right" width="50%" colspan="3" '.$style_desc.'>'.print_price_selected_currency($row_prom['code_lessval'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
					}
				}
			}
			if($row_ords['order_bonuspoint_discount']>0)
			{
				$prod_str  .= '<tr>
								<td align="right" width="50%" colspan="2" '.$style_head.'>Bonus Points Discount</td>
								<td align="right" width="50%" colspan="3" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_bonuspoint_discount'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td>
							</tr>';
			}
			// Total Final Cost
			$prod_str	.= '<tr>
								<td align="right" width="50%" colspan="2" '.$style_head.'>Grand Total</td>
								<td align="right" colspan="3" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_totalprice'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td>
							</tr>';
			// Check whether product deposit exists
			if($row_ords['order_deposit_amt']>0)
			{
				$dep_less = $row_ords['order_totalprice'] - $row_ords['order_deposit_amt'];
				$prod_str	.= '<tr>
								<td align="right" width="50%" colspan="2" '.$style_head.'>Less Product Deposit Amount</td>
								<td align="right" colspan="3" '.$style_desc.'>'.print_price_selected_currency($dep_less,$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td>
							</tr>';
				$prod_str	.= '<tr>
								<td align="right" width="50%" colspan="2" '.$style_head.'>Amount Payable Now</td>
								<td align="right" colspan="3" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_deposit_amt'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td>
							</tr>';
			}
		}
		$prod_str		.= '</table>';
	}
	return $prod_str;
}
/* Function to send order mail */
function save_and_send_OrderMail($mail_type,$ord_arr)
{
	global $db,$ecom_siteid,$ecom_hostname;
	// Check the mail type
	switch($mail_type)
	{
		case 'cancel': // Order cancellation mail
		// Get the content of email template
		$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
								FROM
									general_settings_site_letter_templates
								WHERE
									sites_site_id = $ecom_siteid
									AND lettertemplate_letter_type = 'ORDER_CANCELLATION_CUST'
								LIMIT
									1";
		$ret_template = $db->query($sql_template);
		if ($db->num_rows($ret_template))
		{
			$row_template 		= $db->fetch_array($ret_template);
			$email_from			= stripslashes($row_template['lettertemplate_from']);
			$email_subject		= stripslashes($row_template['lettertemplate_subject']);
			$email_content		= stripslashes($row_template['lettertemplate_contents']);
			$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);
			$note		= $ord_arr['note'];
			$alt_prod	= $ord_arr['alt_prods'];
			// Get cancel date from orders table
			$sql_ords = "SELECT DATE_FORMAT(order_cancelled_on,'%d-%b-%Y') canceldate
								FROM
									orders
								WHERE
									order_id =".$ord_arr['order_id']."
								LIMIT
									1";
			$ret_ords = $db->query($sql_ords);
			if ($db->num_rows($ret_ords))
			{
				$row_ords = $db->fetch_array($ret_ords);
			}
			// Calling function to get the product details of current order
			$prod_details_str		= get_ProductsInOrdersForMail($ord_arr['order_id'],$ord_arr);
			$cname					= stripslashes($ord_arr['order_custtitle']).stripslashes($ord_arr['order_custfname'])." ".stripslashes($ord_arr['order_custsurname']);
			$search_arr	= array (
			'[cust_name]',
			'[domain]',
			'[orderid]',
			'[cancel_date]',
			'[product_details]',
			'[alternate_prods]',
			'[cancel_reason]'
			);
			$replace_arr= array(
			$cname,
			$ecom_hostname,
			$ord_arr['order_id'],
			$row_ords['canceldate'],
			$prod_details_str,
			$ord_arr['alt_prods'],
			$ord_arr['reason']
			);
			
			// Do the replacement in email template content
			$email_content = str_replace($search_arr,$replace_arr,$email_content);
			// Building email headers to be used with the mail
			$email_headers 	= "From: $ecom_hostname	<'$email_from'>\n";
			$email_headers 	.= "MIME-Version: 1.0\n";
			$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";

			// Saving the email to order_emails table
			$insert_array						= array();
			$insert_array['orders_order_id']		= $ord_arr['order_id'];
			$insert_array['email_to']				= addslashes(stripslashes(strip_tags($ord_arr['order_custemail'])));
			$insert_array['email_subject']		= addslashes(stripslashes(strip_tags($email_subject)));
			//$insert_array['email_message']		= addslashes(stripslashes($email_content));
			$insert_array['email_headers']		= addslashes(stripslashes($email_headers));
			$insert_array['email_type']			= 'ORDER_CANCELLATION_CUST';
			$insert_array['email_sendonce']		= ($email_disabled==0)?1:0;
			$insert_array['email_lastsenddate']	= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
			$db->insert_from_array($insert_array,'order_emails');
			$mail_insert_id = $db->insert_id();
			/*10 Nov 2011*/
			$email_content = add_line_break($email_content);
			write_email_as_file('ord',$mail_insert_id,(stripslashes($email_content)));
			
			if($email_disabled==0)// check whether mail sending is disabled
			{
				mail($ord_arr['order_custemail'], $email_subject, $email_content, $email_headers);
			}
		}
		break;
		case 'Paid': // Order payment success
		case 'Pay_Failed': // Order payment failed
		if($mail_type=='Paid')
		$letter_type = 'ORDER_PAYMENT_AUTHORIZE';
		else
		$letter_type = 'ORDER_PAYMENT_FAILED';

		// Get the content of email template
		$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
								FROM
									general_settings_site_letter_templates
								WHERE
									sites_site_id = $ecom_siteid
									AND lettertemplate_letter_type = '".$letter_type."'
								LIMIT
									1";
		$ret_template = $db->query($sql_template);
		if ($db->num_rows($ret_template))
		{
			$row_template 		= $db->fetch_array($ret_template);
			$email_from			= stripslashes($row_template['lettertemplate_from']);
			$email_subject		= stripslashes($row_template['lettertemplate_subject']);
			$email_content		= stripslashes($row_template['lettertemplate_contents']);
			$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);
			$note		= $ord_arr['note'];
			// Get cancel date from orders table
			$sql_ords = "SELECT DATE_FORMAT(order_paystatus_changed_manually_on,'%d-%b-%Y') order_paystatus_changed_manually_on,order_paystatus_changed_manually_paytype 
									FROM
										orders
									WHERE
										order_id =".$ord_arr['order_id']."
									LIMIT
										1";
			$ret_ords = $db->query($sql_ords);
			if ($db->num_rows($ret_ords))
			{
				$row_ords = $db->fetch_array($ret_ords);
			}
			// Calling function to get the product details of current order
			$prod_details_str		= get_ProductsInOrdersForMail($ord_arr['order_id'],$ord_arr);
			$cname					= stripslashes($ord_arr['order_custtitle']).stripslashes($ord_arr['order_custfname'])." ".stripslashes($ord_arr['order_custsurname']);
			if ($row_ords['order_paystatus_changed_manually_paytype']!='')
			{
				$manual_paytype = 'Payment Type: '.ucwords(strtolower($row_ords['order_paystatus_changed_manually_paytype']))."<br/>";
			}
			if($ord_arr['reason']!='')
			{
			
			$despatch_note_str=   ' <tr>
                                    	<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top">Note</td>
                                    </tr>';
			$despatch_note_str .= '<tr>
                                    	<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top">'.$ord_arr['reason'].'</td>
								  </tr>'	;
			}
			$search_arr	= array (
			'[cust_name]',
			'[domain]',
			'[orderid]',
			'[auth_date]',
			'[fail_date]',
			'[product_details]',
			'[reason]'
			);
			$replace_arr= array(
			$cname,
			$ecom_hostname,
			$ord_arr['order_id'],
			$row_ords['order_paystatus_changed_manually_on'],
			$row_ords['order_paystatus_changed_manually_on'],
			$prod_details_str,
			$manual_paytype.$despatch_note_str
			);
			// Do the replacement in email template content
			$email_content = str_replace($search_arr,$replace_arr,$email_content);
			// Building email headers to be used with the mail
			$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
			$email_headers 	.= "MIME-Version: 1.0\n";
			$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";

			// Saving the email to order_emails table
			$insert_array						= array();
			$insert_array['orders_order_id']		= $ord_arr['order_id'];
			$insert_array['email_to']				= addslashes(stripslashes(strip_tags($ord_arr['order_custemail'])));
			$insert_array['email_subject']		= addslashes(stripslashes(strip_tags($email_subject)));
			//$insert_array['email_message']		= addslashes(stripslashes($email_content));
			$insert_array['email_headers']		= addslashes(stripslashes($email_headers));
			$insert_array['email_type']			= $letter_type;
			$insert_array['email_sendonce']		= ($email_disabled==0)?1:0;
			$insert_array['email_lastsenddate']	= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
			$db->insert_from_array($insert_array,'order_emails');
			$mail_insert_id = $db->insert_id();
			/*10 Nov 2011*/
			$email_content = add_line_break($email_content);
			write_email_as_file('ord',$mail_insert_id,(stripslashes($email_content)));
			if($email_disabled==0)// check whether mail sending is disabled
			{
				mail($ord_arr['order_custemail'], $email_subject,$email_content, $email_headers);
			}
		}
		break;
		case 'DESPATCHED': // Ordered item despatched
		$order_id						= $ord_arr['order_id'];
		$despatch_number				= $ord_arr['despatch_id'];
		$despatch_note					= $ord_arr['despatch_note'];
		$despatch_arr					= $ord_arr['despatched_prods'];
		$despatchqty_arr				= $ord_arr['despatched_qtys'];
		$despatch_completely			= $ord_arr['completly_despatched'];
		$despatch_delivery_str			= $ord_arr['despatched_delivery_date'];
		if($despatch_delivery_str!='')
		{
			$del_mon_arr = array('01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec');
			$despatch_delivery_arr = explode('-',$despatch_delivery_str);
			$despatch_delivery_date = $despatch_delivery_arr[0].'-'.$del_mon_arr[$despatch_delivery_arr[1]].'-'.$despatch_delivery_arr[2];
		}
		
		// Get the email content
		$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
								FROM
									general_settings_site_letter_templates
								WHERE
									sites_site_id = $ecom_siteid
									AND lettertemplate_letter_type = 'ORDER_DESPATCHED'
								LIMIT
									1";
		$ret_template = $db->query($sql_template);
		if ($db->num_rows($ret_template))
		{
			$row_template 		= $db->fetch_array($ret_template);
			$email_from			= stripslashes($row_template['lettertemplate_from']);
			$email_subject		= stripslashes($row_template['lettertemplate_subject']);
			$email_content		= stripslashes($row_template['lettertemplate_contents']);
			$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);
			// Get some details from orders table for current order
			$sql_ords = "SELECT order_id,order_custtitle,order_custfname,order_custmname,order_custsurname,
									order_custemail,order_currency_convertionrate,order_currency_symbol,
									order_subtotal,order_giftwraptotal,order_deliverytotal,order_extrashipping,order_deliveryprice_only,order_tax_total,
							order_customer_discount_value,order_customer_or_corporate_disc,
							order_customer_discount_type,order_customer_discount_percent,order_totalprice,
							order_deposit_amt,order_deposit_amt,gift_vouchers_voucher_id,promotional_code_code_id,
							order_bonuspoint_discount
					FROM
						orders
					WHERE
						order_id = $order_id
					LIMIT
						1";
			$ret_ords = $db->query($sql_ords);
			if ($db->num_rows($ret_ords))
			{
				$row_ords = $db->fetch_array($ret_ords);
			}
			// Calling function to get the product details of current order
			$pass_arr['prods']	= $despatch_arr;
			$pass_arr['qtys']		= $despatchqty_arr;
			$prod_details_str		= get_ProductsInOrdersForMail($ord_arr['order_id'],$row_ords,$pass_arr);
			$cname					= stripslashes($row_ords['order_custtitle']).stripslashes($row_ords['order_custfname'])." ".stripslashes($row_ords['order_custsurname']);
			$search_arr	= array (
			'[cust_name]',
			'[domain]',
			'[orderid]',
			'[despatch_date]',
			'[product_details]',
			'[note]',
			'[despatch_id]',
			'[delivery_date]'
			);
			if($despatch_note!='')
			{
				if($ecom_siteid==88) // skatesrus
				{
					$despatch_note_str = nl2br($despatch_note);
				}
				else
				{
					$despatch_note_str=   ' <tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top">Note</td>
											</tr>';
					$despatch_note_str .= '<tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top">'.$despatch_note.'</td>
										  </tr>'	;
				}						  
			}
			$replace_arr= array(
			$cname,
			$ecom_hostname,
			$row_ords['order_id'],
			date('d-M-Y'),
			$prod_details_str,
			$despatch_note_str,
			$despatch_number,
			$despatch_delivery_date
			);
			// Do the replacement in email template content
			$email_content = str_replace($search_arr,$replace_arr,$email_content);
			// Check whether despatch email is to be send to any other email id
			$additional_emailid = '';
			$sql_gen = "SELECT order_despatch_additional_email 
							FROM 
								general_settings_sites_common 
							WHERE 
								sites_site_id = $ecom_siteid 
							LIMIT 1";
			$ret_gen = $db->query($sql_gen);
			if($db->num_rowS($ret_gen))
			{
				$row_gen = $db->fetch_array($ret_gen);
				$additional_emailid = trim(stripslashes($row_gen['order_despatch_additional_email']));
			}
			// Building email headers to be used with the mail
			$email_headers 	 = "From: $ecom_hostname	<$email_from>\n";
			/*if($additional_emailid != '')
				$email_headers  .= "Cc: ".$additional_emailid."\n";*/
			$email_headers 	.= "MIME-Version: 1.0\n";
			$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";

			// Saving the email to order_emails table
			$insert_array						= array();
			$insert_array['orders_order_id']		= $ord_arr['order_id'];
			$insert_array['email_to']				= addslashes(stripslashes(strip_tags($row_ords['order_custemail'])));
			$insert_array['email_subject']		= addslashes(stripslashes(strip_tags($email_subject)));
			//$insert_array['email_message']		= addslashes(stripslashes($email_content));
			$insert_array['email_headers']		= addslashes(stripslashes($email_headers));
			$insert_array['email_type']			= 'ORDER_DESPATCHED';
			$insert_array['email_sendonce']		= ($email_disabled==0)?1:0;
			$insert_array['email_lastsenddate']	= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
			$db->insert_from_array($insert_array,'order_emails');
			$mail_insert_id = $db->insert_id();
			/*10 Nov 2011*/
			$email_content = add_line_break($email_content);
			write_email_as_file('ord',$mail_insert_id,(stripslashes($email_content)));
			if($email_disabled==0)// check whether mail sending is disabled
			{
				mail($row_ords['order_custemail'], $email_subject,$email_content, $email_headers);
				if($additional_emailid != '')
					mail($additional_emailid, $email_subject,$email_content, $email_headers);
			}
		}
		break;
		case 'REFUNDED': // Refunding
		$order_id						= $ord_arr['order_id'];
		$refund_amt					= print_price_selected_currency($ord_arr['refund_amt'],$ord_arr['order_currency_convertionrate'],$ord_arr['order_currency_symbol'],true);
		$refund_note					= $ord_arr['refund_note'];
		$refund_arr					= $ord_arr['refunded_prods'];
		// Get the email content
		$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
								FROM
									general_settings_site_letter_templates
								WHERE
									sites_site_id = $ecom_siteid
									AND lettertemplate_letter_type = 'ORDER_REFUNDED'
								LIMIT
									1";
		$ret_template = $db->query($sql_template);
		if ($db->num_rows($ret_template))
		{
			$row_template 		= $db->fetch_array($ret_template);
			$email_from			= stripslashes($row_template['lettertemplate_from']);
			$email_subject		= stripslashes($row_template['lettertemplate_subject']);
			$email_content		= stripslashes($row_template['lettertemplate_contents']);
			$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);
			// Get some details from orders table for current order
			$sql_ords = "SELECT order_id,order_custtitle,order_custfname,order_custmname,order_custsurname,
									order_custemail,order_currency_convertionrate,order_currency_symbol,
							order_subtotal,order_giftwraptotal,order_deliverytotal,order_extrashipping,order_deliveryprice_only,order_tax_total,
							order_customer_discount_value,order_customer_or_corporate_disc,
							order_customer_discount_type,order_customer_discount_percent,order_totalprice,
							order_deposit_amt,order_deposit_amt,gift_vouchers_voucher_id,promotional_code_code_id,
							order_bonuspoint_discount
					FROM
						orders
					WHERE
						order_id = $order_id
					LIMIT
						1";
			$ret_ords = $db->query($sql_ords);
			if ($db->num_rows($ret_ords))
			{
				$row_ords = $db->fetch_array($ret_ords);
			}
			// Calling function to get the product details of current order
			$pass_arr['prods']		= $refund_arr;
			
			$prod_details_str		= get_ProductsInOrdersForMail($ord_arr['order_id'],$row_ords,$pass_arr);
			$cname					= stripslashes($row_ords['order_custtitle']).stripslashes($row_ords['order_custfname'])." ".stripslashes($row_ords['order_custsurname']);
			if($refund_note!='')
			{
			
				$note_str=   ' <tr>
											<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top">Note</td>
							 </tr>';
				$note_str .= '<tr>
										<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top">'.$refund_note.'</td>
						 </tr>'	;
			}
			$search_arr	= array (
			'[cust_name]',
			'[domain]',
			'[orderid]',
			'[refund_date]',
			'[product_details]',
			'[note]',
			'[refund_amt]'
			);
			$replace_arr= array(
			$cname,
			$ecom_hostname,
			$row_ords['order_id'],
			date('d-M-Y'),
			$prod_details_str,
			$note_str,
			$refund_amt
			);
			// Do the replacement in email template content
			$email_content = str_replace($search_arr,$replace_arr,$email_content);
			// Building email headers to be used with the mail
			$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
			$email_headers 	.= "MIME-Version: 1.0\n";
			$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";

			// Saving the email to order_emails table
			$insert_array						= array();
			$insert_array['orders_order_id']		= $ord_arr['order_id'];
			$insert_array['email_to']				= addslashes(stripslashes(strip_tags($row_ords['order_custemail'])));
			$insert_array['email_subject']		= addslashes(stripslashes(strip_tags($email_subject)));
			//$insert_array['email_message']		= addslashes(stripslashes($email_content));
			$insert_array['email_headers']		= addslashes(stripslashes($email_headers));
			$insert_array['email_type']			= 'ORDER_REFUNDED';
			$insert_array['email_sendonce']		= ($email_disabled==0)?1:0;
			$insert_array['email_lastsenddate']	= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
			$db->insert_from_array($insert_array,'order_emails');
			$mail_insert_id = $db->insert_id();
			/*10 Nov 2011*/
			$email_content = add_line_break($email_content);
			write_email_as_file('ord',$mail_insert_id,(stripslashes($email_content)));
			if($email_disabled==0)// check whether mail sending is disabled
			{
				mail($row_ords['order_custemail'], $email_subject,$email_content, $email_headers);
			}
		}
		break;
		case 'DEFERRED_RELEASE':
			// Get the content of email template
			$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
									FROM
										general_settings_site_letter_templates
									WHERE
										sites_site_id = $ecom_siteid
										AND lettertemplate_letter_type = 'ORDER_PAYMENT_RELEASE'
									LIMIT
										1";
			$ret_template = $db->query($sql_template);
			if ($db->num_rows($ret_template))
			{
				$row_template 		= $db->fetch_array($ret_template);
				$email_from			= stripslashes($row_template['lettertemplate_from']);
				$email_subject		= stripslashes($row_template['lettertemplate_subject']);
				$email_content		= stripslashes($row_template['lettertemplate_contents']);
				$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);

				$billing_str		= '';
				$delivery_str		= '';
				$gift_str			= '';
				$bonus_str			= '';
				$tax_str			= '';
				$prod_details_str	= '';
				$note				= $ord_arr['note'];
				// Get the full details of current order from orders table
				$row_ord 			= get_FullOrderDetails($ord_arr['order_id']);
				// Calling function to get the billing details
				$billing_str		= get_EmailBillingDetails($ord_arr['order_id'],$row_ord);
				// Calling function to get the delivery details
				$delivery_str		= get_EmailDeliveryDetails($ord_arr['order_id'],$row_ord);
				// Calling function to get the giftwrap details
				$gift_str			= get_EmailGiftwrapDetails($ord_arr['order_id'],$row_ord);
				// Calling function to get the bonus points details
				$bonus_str			= get_EmailBonusDetails($ord_arr['order_id'],$row_ord);
				// Calling function to get the tax details
				$tax_str			= get_EmailTaxDetails($ord_arr['order_id'],$row_ord);
				// Calling function to get the promotional or voucher details
				$voucher_str		= get_EmailPromVoucherDetails($ord_arr['order_id'],$row_ord);
				// Calling function to get the product details of current order
				$prod_details_str		= get_ProductsInOrdersForMail($ord_arr['order_id'],$row_ord);
				$cname					= stripslashes($row_ord['order_custtitle']).stripslashes($row_ord['order_custfname'])." ".stripslashes($row_ord['order_custsurname']);
				if($note!='')
				{
					$note_str=   ' <tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top">Note</td>
								 </tr>';
					$note_str .= '<tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top">'.$note.'</td>
								 </tr>'	;
				}
				$search_arr	= array (
				'[cust_name]',
				'[domain]',
				'[orderid]',
				'[orderdate]',
				'[billing_details]',
				'[delivery_details]',
				'[giftwrap_details]',
				'[bonus_details]',
				'[tax_details]',
				'[promvoucher_details]',
				'[product_details]',
				'[release_date]',
				'[note]'
				);
				$replace_arr= array(
				$cname,
				$ecom_hostname,
				$ord_arr['order_id'],
				dateFormat($row_ord['order_date'],'datetime'),
				$billing_str,
				$delivery_str,
				$gift_str,
				$bonus_str,
				$tax_str,
				$voucher_str,
				$prod_details_str,
				dateFormat($row_ord['order_paystatus_changed_manually_on'],'datetime'),
				$note_str
				);
				// Do the replacement in email template content
				$email_content = str_replace($search_arr,$replace_arr,$email_content);
				// Building email headers to be used with the mail
				$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
				$email_headers 	.= "MIME-Version: 1.0\n";
				$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";

				// Saving the email to order_emails table
				$insert_array						= array();
				$insert_array['orders_order_id']		= $ord_arr['order_id'];
				$insert_array['email_to']				= addslashes(stripslashes(strip_tags($row_ord['order_custemail'])));
				$insert_array['email_subject']		= addslashes(stripslashes(strip_tags($email_subject)));
				//$insert_array['email_message']		= addslashes(stripslashes($email_content));
				$insert_array['email_headers']		= addslashes(stripslashes($email_headers));
				$insert_array['email_type']			= 'ORDER_PAYMENT_RELEASE';
				$insert_array['email_sendonce']		= ($email_disabled==0)?1:0;
				$insert_array['email_lastsenddate']	= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
				$db->insert_from_array($insert_array,'order_emails');
				$mail_insert_id = $db->insert_id();
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('ord',$mail_insert_id,(stripslashes($email_content)));
				if($email_disabled==0)// check whether mail sending is disabled
				{
					mail($row_ord['order_custemail'], $email_subject,$email_content, $email_headers);
				}
		}
		break;
		case 'DEFERRED_ABORT': // Aborting deferrred payment
			// Get the content of email template
			$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
									FROM
										general_settings_site_letter_templates
									WHERE
										sites_site_id = $ecom_siteid
										AND lettertemplate_letter_type = 'ORDER_PAYMENT_ABORT'
									LIMIT
										1";
			$ret_template = $db->query($sql_template);
			if ($db->num_rows($ret_template))
			{
				$row_template 		= $db->fetch_array($ret_template);
				$email_from			= stripslashes($row_template['lettertemplate_from']);
				$email_subject		= stripslashes($row_template['lettertemplate_subject']);
				$email_content		= stripslashes($row_template['lettertemplate_contents']);
				$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);

				$billing_str		= '';
				$delivery_str		= '';
				$gift_str			= '';
				$bonus_str			= '';
				$tax_str			= '';
				$prod_details_str	= '';

				$note				= $ord_arr['note'];

				// Get the full details of current order from orders table
				$row_ord 			= get_FullOrderDetails($ord_arr['order_id']);

				// Calling function to get the billing details
				$billing_str		= get_EmailBillingDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the delivery details
				$delivery_str		= get_EmailDeliveryDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the giftwrap details
				$gift_str			= get_EmailGiftwrapDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the bonus points details
				$bonus_str			= get_EmailBonusDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the tax details
				$tax_str			= get_EmailTaxDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the promotional or voucher details
				$voucher_str		= get_EmailPromVoucherDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the product details of current order
				$prod_details_str		= get_ProductsInOrdersForMail($ord_arr['order_id'],$row_ord);
				$cname					= stripslashes($row_ord['order_custtitle']).stripslashes($row_ord['order_custfname'])." ".stripslashes($row_ord['order_custsurname']);
				$search_arr	= array (
				'[cust_name]',
				'[domain]',
				'[orderid]',
				'[orderdate]',
				'[billing_details]',
				'[delivery_details]',
				'[giftwrap_details]',
				'[bonus_details]',
				'[tax_details]',
				'[promvoucher_details]',
				'[product_details]',
				'[abort_date]',
				'[note]'
				);
				if($note!='')
				{
				      $note_str=' <tr>
                                    <td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top">Note</td>
                                </tr>
                                <tr>
                                    <td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top">'.$note.'</td>
                                </tr>';
				}
				$replace_arr= array(
				$cname,
				$ecom_hostname,
				$ord_arr['order_id'],
				dateFormat($row_ord['order_date'],'datetime'),
				$billing_str,
				$delivery_str,
				$gift_str,
				$bonus_str,
				$tax_str,
				$voucher_str,
				$prod_details_str,
				dateFormat($row_ord['order_paystatus_changed_manually_on'],'datetime'),
				$note_str
				);
				// Do the replacement in email template content
				$email_content = str_replace($search_arr,$replace_arr,$email_content);
				// Building email headers to be used with the mail
				$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
				$email_headers 	.= "MIME-Version: 1.0\n";
				$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";

				// Saving the email to order_emails table
				$insert_array						= array();
				$insert_array['orders_order_id']		= $ord_arr['order_id'];
				$insert_array['email_to']				= addslashes(stripslashes(strip_tags($row_ord['order_custemail'])));
				$insert_array['email_subject']		= addslashes(stripslashes(strip_tags($email_subject)));
				//$insert_array['email_message']		= addslashes(stripslashes($email_content));
				$insert_array['email_headers']		= addslashes(stripslashes($email_headers));
				$insert_array['email_type']			= 'ORDER_PAYMENT_RELEASE';
				$insert_array['email_sendonce']		= ($email_disabled==0)?1:0;
				$insert_array['email_lastsenddate']	= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
				$db->insert_from_array($insert_array,'order_emails');
				$mail_insert_id = $db->insert_id();
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('ord',$mail_insert_id,(stripslashes($email_content)));
				if($email_disabled==0)// check whether mail sending is disabled
				{
					mail($row_ord['order_custemail'], $email_subject,$email_content, $email_headers);
				}
			}
		break;
		case 'PREAUTH_REPEAT': // Repeat Preauth payment
			// Get the content of email template
			$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
									FROM
										general_settings_site_letter_templates
									WHERE
										sites_site_id = $ecom_siteid
										AND lettertemplate_letter_type = 'ORDER_PAYMENT_REPEAT'
									LIMIT
										1";
			$ret_template = $db->query($sql_template);
			if ($db->num_rows($ret_template))
			{
				$row_template 		= $db->fetch_array($ret_template);
				$email_from			= stripslashes($row_template['lettertemplate_from']);
				$email_subject		= stripslashes($row_template['lettertemplate_subject']);
				$email_content		= stripslashes($row_template['lettertemplate_contents']);
				$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);

				$billing_str		= '';
				$delivery_str		= '';
				$gift_str			= '';
				$bonus_str			= '';
				$tax_str			= '';
				$prod_details_str	= '';

				$note				= $ord_arr['note'];

				// Get the full details of current order from orders table
				$row_ord 			= get_FullOrderDetails($ord_arr['order_id']);

				// Calling function to get the billing details
				$billing_str		= get_EmailBillingDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the delivery details
				$delivery_str		= get_EmailDeliveryDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the giftwrap details
				$gift_str			= get_EmailGiftwrapDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the bonus points details
				$bonus_str			= get_EmailBonusDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the tax details
				$tax_str			= get_EmailTaxDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the promotional or voucher details
				$voucher_str		= get_EmailPromVoucherDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the product details of current order
				$prod_details_str		= get_ProductsInOrdersForMail($ord_arr['order_id'],$row_ord);
				$cname					= stripslashes($row_ord['order_custtitle']).stripslashes($row_ord['order_custfname'])." ".stripslashes($row_ord['order_custsurname']);
				if($note!='')
				{
				  $note_str=' <tr>
								<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top">Note</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top">'.$note.'</td>
							</tr>';
				}
				$search_arr	= array (
				'[cust_name]',
				'[domain]',
				'[orderid]',
				'[orderdate]',
				'[billing_details]',
				'[delivery_details]',
				'[giftwrap_details]',
				'[bonus_details]',
				'[tax_details]',
				'[promvoucher_details]',
				'[product_details]',
				'[repeat_date]',
				'[note]'
				);
				$replace_arr= array(
				$cname,
				$ecom_hostname,
				$ord_arr['order_id'],
				dateFormat($row_ord['order_date'],'datetime'),
				$billing_str,
				$delivery_str,
				$gift_str,
				$bonus_str,
				$tax_str,
				$voucher_str,
				$prod_details_str,
				dateFormat($row_ord['order_paystatus_changed_manually_on'],'datetime'),
				$note_str
				);
				// Do the replacement in email template content
				$email_content = str_replace($search_arr,$replace_arr,$email_content);
				// Building email headers to be used with the mail
				$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
				$email_headers 	.= "MIME-Version: 1.0\n";
				$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";

				// Saving the email to order_emails table
				$insert_array						= array();
				$insert_array['orders_order_id']		= $ord_arr['order_id'];
				$insert_array['email_to']				= addslashes(stripslashes(strip_tags($row_ord['order_custemail'])));
				$insert_array['email_subject']		= addslashes(stripslashes(strip_tags($email_subject)));
				//$insert_array['email_message']		= addslashes(stripslashes($email_content));
				$insert_array['email_headers']		= addslashes(stripslashes($email_headers));
				$insert_array['email_type']			= 'ORDER_PAYMENT_RELEASE';
				$insert_array['email_sendonce']		= ($email_disabled==0)?1:0;
				$insert_array['email_lastsenddate']	= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
				$db->insert_from_array($insert_array,'order_emails');
				$mail_insert_id = $db->insert_id();
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('ord',$mail_insert_id,(stripslashes($email_content)));
				if($email_disabled==0)// check whether mail sending is disabled
				{
					mail($row_ord['order_custemail'], $email_subject,$email_content, $email_headers);
				}
			}
		break;
		case 'AUTHENTICATE_AUTHORISE': // Repeat Authenticate Authorise
			// Get the content of email template
			$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
									FROM
										general_settings_site_letter_templates
									WHERE
										sites_site_id = $ecom_siteid
										AND lettertemplate_letter_type = 'ORDER_PAYMENT_AUTHORISE'
									LIMIT
										1";
			$ret_template = $db->query($sql_template);
			if ($db->num_rows($ret_template))
			{
				$row_template 		= $db->fetch_array($ret_template);
				$email_from			= stripslashes($row_template['lettertemplate_from']);
				$email_subject		= stripslashes($row_template['lettertemplate_subject']);
				$email_content		= stripslashes($row_template['lettertemplate_contents']);
				$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);

				$billing_str		= '';
				$delivery_str		= '';
				$gift_str			= '';
				$bonus_str			= '';
				$tax_str			= '';
				$prod_details_str	= '';

				$note				= $ord_arr['note'];
				$amt				= $ord_arr['amt'];

				// Get the full details of current order from orders table
				$row_ord 			= get_FullOrderDetails($ord_arr['order_id']);

				// Calling function to get the billing details
				$billing_str		= get_EmailBillingDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the delivery details
				$delivery_str		= get_EmailDeliveryDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the giftwrap details
				$gift_str			= get_EmailGiftwrapDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the bonus points details
				$bonus_str			= get_EmailBonusDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the tax details
				$tax_str			= get_EmailTaxDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the promotional or voucher details
				$voucher_str		= get_EmailPromVoucherDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the product details of current order
				$prod_details_str		= get_ProductsInOrdersForMail($ord_arr['order_id'],$row_ord);
				$cname					= stripslashes($row_ord['order_custtitle']).stripslashes($row_ord['order_custfname'])." ".stripslashes($row_ord['order_custsurname']);
				if($note!='')
				{
					$note_str=   ' <tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top">Note</td>
								 </tr>';
					$note_str .= '<tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top">'.$note.'</td>
								 </tr>'	;
				}
				$search_arr	= array (
				'[cust_name]',
				'[domain]',
				'[orderid]',
				'[orderdate]',
				'[billing_details]',
				'[delivery_details]',
				'[giftwrap_details]',
				'[bonus_details]',
				'[tax_details]',
				'[promvoucher_details]',
				'[product_details]',
				'[authorise_date]',
				'[authorise_amt]',
				'[note]'
				);
				$replace_arr= array(
				$cname,
				$ecom_hostname,
				$ord_arr['order_id'],
				dateFormat($row_ord['order_date'],'datetime'),
				$billing_str,
				$delivery_str,
				$gift_str,
				$bonus_str,
				$tax_str,
				$voucher_str,
				$prod_details_str,
				dateFormat($row_ord['order_paystatus_changed_manually_on'],'datetime'),
				$amt,
				$note_str
				);
				// Do the replacement in email template content
				$email_content = str_replace($search_arr,$replace_arr,$email_content);
				// Building email headers to be used with the mail
				$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
				$email_headers 	.= "MIME-Version: 1.0\n";
				$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";

				// Saving the email to order_emails table
				$insert_array						= array();
				$insert_array['orders_order_id']		= $ord_arr['order_id'];
				$insert_array['email_to']				= addslashes(stripslashes(strip_tags($row_ord['order_custemail'])));
				$insert_array['email_subject']		= addslashes(stripslashes(strip_tags($email_subject)));
				//$insert_array['email_message']		= addslashes(stripslashes($email_content));
				$insert_array['email_headers']		= addslashes(stripslashes($email_headers));
				$insert_array['email_type']			= 'ORDER_PAYMENT_RELEASE';
				$insert_array['email_sendonce']		= ($email_disabled==0)?1:0;
				$insert_array['email_lastsenddate']	= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
				$db->insert_from_array($insert_array,'order_emails');
				$mail_insert_id = $db->insert_id();
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('ord',$mail_insert_id,(stripslashes($email_content)));
				if($email_disabled==0)// check whether mail sending is disabled
				{
					mail($row_ord['order_custemail'], $email_subject,$email_content, $email_headers);
				}
			}
		break;
		case 'AUTHENTICATE_CANCEL': // Repeat Authenticate Cancel payment
			// Get the content of email template
			$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
									FROM
										general_settings_site_letter_templates
									WHERE
										sites_site_id = $ecom_siteid
										AND lettertemplate_letter_type = 'ORDER_PAYMENT_CANCEL'
									LIMIT
										1";
			$ret_template = $db->query($sql_template);
			if ($db->num_rows($ret_template))
			{
				$row_template 		= $db->fetch_array($ret_template);
				$email_from			= stripslashes($row_template['lettertemplate_from']);
				$email_subject		= stripslashes($row_template['lettertemplate_subject']);
				$email_content		= stripslashes($row_template['lettertemplate_contents']);
				$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);

				$billing_str		= '';
				$delivery_str		= '';
				$gift_str			= '';
				$bonus_str			= '';
				$tax_str			= '';
				$prod_details_str	= '';

				$note				= $ord_arr['note'];
				$amt				= $ord_arr['amt'];

				// Get the full details of current order from orders table
				$row_ord 			= get_FullOrderDetails($ord_arr['order_id']);

				// Calling function to get the billing details
				$billing_str		= get_EmailBillingDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the delivery details
				$delivery_str		= get_EmailDeliveryDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the giftwrap details
				$gift_str			= get_EmailGiftwrapDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the bonus points details
				$bonus_str			= get_EmailBonusDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the tax details
				$tax_str			= get_EmailTaxDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the promotional or voucher details
				$voucher_str		= get_EmailPromVoucherDetails($ord_arr['order_id'],$row_ord);

				// Calling function to get the product details of current order
				$prod_details_str		= get_ProductsInOrdersForMail($ord_arr['order_id'],$row_ord);
				$cname					= stripslashes($row_ord['order_custtitle']).stripslashes($row_ord['order_custfname'])." ".stripslashes($row_ord['order_custsurname']);
				if($note!='')
				{
					$note_str=   ' <tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top">Note</td>
								 </tr>';
					$note_str .= '<tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top">'.$note.'</td>
								 </tr>'	;
				}
				$search_arr	= array (
				'[cust_name]',
				'[domain]',
				'[orderid]',
				'[orderdate]',
				'[billing_details]',
				'[delivery_details]',
				'[giftwrap_details]',
				'[bonus_details]',
				'[tax_details]',
				'[promvoucher_details]',
				'[product_details]',
				'[cancel_date]',
				'[note]'
				);
				$replace_arr= array(
				$cname,
				$ecom_hostname,
				$ord_arr['order_id'],
				dateFormat($row_ord['order_date'],'datetime'),
				$billing_str,
				$delivery_str,
				$gift_str,
				$bonus_str,
				$tax_str,
				$voucher_str,
				$prod_details_str,
				dateFormat($row_ord['order_paystatus_changed_manually_on'],'datetime'),
				$note_str
				);
				// Do the replacement in email template content
				$email_content = str_replace($search_arr,$replace_arr,$email_content);
				// Building email headers to be used with the mail
				$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
				$email_headers 	.= "MIME-Version: 1.0\n";
				$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";

				// Saving the email to order_emails table
				$insert_array						= array();
				$insert_array['orders_order_id']		= $ord_arr['order_id'];
				$insert_array['email_to']				= addslashes(stripslashes(strip_tags($row_ord['order_custemail'])));
				$insert_array['email_subject']		= addslashes(stripslashes(strip_tags($email_subject)));
				//$insert_array['email_message']		= addslashes(stripslashes($email_content));
				$insert_array['email_headers']		= addslashes(stripslashes($email_headers));
				$insert_array['email_type']			= 'ORDER_PAYMENT_RELEASE';
				$insert_array['email_sendonce']		= ($email_disabled==0)?1:0;
				$insert_array['email_lastsenddate']	= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
				$db->insert_from_array($insert_array,'order_emails');
				$mail_insert_id = $db->insert_id();
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('ord',$mail_insert_id,(stripslashes($email_content)));
				if($email_disabled==0)// check whether mail sending is disabled
				{
					mail($row_ord['order_custemail'], $email_subject,$email_content, $email_headers);
				}
			}
		break;
	};
	return;
}

/* Function to find the price of one item from an order details*/
function get_priceofOneitem($orddetid)
{
	global $db,$ecom_siteid;
	$sql_det = "SELECT orders_order_id,order_orgqty,product_soldprice
					FROM
						order_details
					WHERE
						orderdet_id = $orddetid
					LIMIT
						1";
	$ret_det 	= $db->query($sql_det);
	$row_det 	= $db->fetch_array($ret_det);
	$price		= $row_det['product_soldprice'];
	return $price;
}
/* function to get the full details from orders table for a given order id*/
function get_FullOrderDetails($order_id)
{
	global $db,$ecom_siteid;
	$sql_ords = "SELECT *
						FROM
							orders
						WHERE
							order_id =".$order_id."
						LIMIT
							1";
	$ret_ords = $db->query($sql_ords);
	if ($db->num_rows($ret_ords))
	{
		$row_ords = $db->fetch_array($ret_ords);
	}
	return $row_ords;
}
/* Function to get the billing related details */
function get_EmailBillingDetails($order_id,$row_ords)
{
	global $db,$ecom_siteid;

	$cname = stripslashes($row_ords['order_custtitle']).stripslashes($row_ords['order_custfname']).' '.stripslashes($row_ords['order_custmname']).' '.stripslashes($row_ords['order_custsurname']);
	// ##############################################################################
	// 								Billing details
	// ##############################################################################
	// Get the checkout fields from general_settings_sites_checkoutfields table
	$sql_checkout = "SELECT field_key,field_name
						FROM
							general_settings_site_checkoutfields
						WHERE
							sites_site_id = $ecom_siteid
							AND field_type IN ('PERSONAL')
						ORDER BY
							field_order";
	$ret_checkout = $db->query($sql_checkout);
	if($db->num_rows($ret_checkout))
	{
		while ($row_checkout = $db->fetch_array($ret_checkout))
		{
			$chkorder_arr[$row_checkout['field_key']] = stripslashes($row_checkout['field_name']);
		}
	}
	$style_head = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:bold;border-bottom:1px solid  #f8f8f8;'";
	$style_desc = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;'";
	$style_desc_prodname ="style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;text-decoration:none;'";

	// ##############################################################################
	// Dynamic Values on top of billing details
	// ##############################################################################
	$sql_dynamic = "SELECT section_id,section_name,dynamic_label,dynamic_value
					FROM
						order_dynamicvalues
					WHERE
						orders_order_id = $order_id
						AND position='Top'
					ORDER BY
						section_id,id";
	$ret_dynamic = $db->query($sql_dynamic);
	if($db->num_rows($ret_dynamic))
	{
		$prev_sec = 0;
		$dynamictop_str	= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
		while ($row_dynamic = $db->fetch_array($ret_dynamic))
		{
			if ($prev_sec!=$row_dynamic['section_id']) // Check whether section name is to be displayed
			{
				$prev_sec = $row_dynamic['section_id'];
				if ($row_dynamic['section_name']!='')
					$dynamictop_str		.= '<tr><td align="left" colspan="2" '.$style_head.'>'.stripslashes($row_dynamic['section_name']).'</td></tr>';
			}
			$dynamictop_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.stripslashes($row_dynamic['dynamic_label']).'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_dynamic['dynamic_value']).'</td></tr>';
		}
		$dynamictop_str	.= '</table>';
	}

	$bill_str		= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
	// ##############################################################################
	// Dynamic Values on topinstatic of billing details
	// ##############################################################################
	$sql_dynamic = "SELECT section_id,section_name,dynamic_label,dynamic_value
					FROM
						order_dynamicvalues
					WHERE
						orders_order_id = $order_id
						AND position='TopInStatic'
					ORDER BY
						section_id,id";
	$ret_dynamic = $db->query($sql_dynamic);
	if($db->num_rows($ret_dynamic))
	{
		$prev_sec = 0;
		while ($row_dynamic = $db->fetch_array($ret_dynamic))
		{
			$bill_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.stripslashes($row_dynamic['dynamic_label']).'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_dynamic['dynamic_value']).'</td></tr>';
		}
	}
	// ##############################################################################
	// Main Billing address details
	// ##############################################################################
	$bill_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Name</td><td align="left" width="50%">'.$cname.'</td></tr>';
	$bill_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_comp_name'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_ords['order_custcompany']).'</td></tr>';
	$bill_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_building'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_ords['order_buildingnumber']).'</td></tr>';
	$bill_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_street'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_ords['order_street']).'</td></tr>';
	$bill_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_city'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_ords['order_city']).'</td></tr>';
	$bill_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_state'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_ords['order_state']).'</td></tr>';
	$bill_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_country'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_ords['order_country']).'</td></tr>';
	$bill_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_zipcode'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_ords['order_custpostcode']).'</td></tr>';
	$bill_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_phone'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_ords['order_custphone']).'</td></tr>';
	$bill_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_mobile'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_ords['order_custmobile']).'</td></tr>';
	$bill_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_fax'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_ords['order_custfax']).'</td></tr>';
	$bill_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_email'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_ords['order_custemail']).'</td></tr>';

	// ##############################################################################
	// Dynamic Values on bottominstatic of billing details
	// ##############################################################################
	$sql_dynamic = "SELECT section_id,section_name,dynamic_label,dynamic_value
					FROM
						order_dynamicvalues
					WHERE
						orders_order_id = $order_id
						AND position='BottomInStatic'
					ORDER BY
						section_id,id";
	$ret_dynamic = $db->query($sql_dynamic);
	if($db->num_rows($ret_dynamic))
	{
		$prev_sec = 0;
		while ($row_dynamic = $db->fetch_array($ret_dynamic))
		{
			$bill_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.stripslashes($row_dynamic['dynamic_label']).'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_dynamic['dynamic_value']).'</td></tr>';
		}
	}
	$bill_str		.= '</table>';
	// ##############################################################################
	// Dynamic Values on top of billing details
	// ##############################################################################
	$sql_dynamic = "SELECT section_id,section_name,dynamic_label,dynamic_value
					FROM
						order_dynamicvalues
					WHERE
						orders_order_id = $order_id
						AND position='Bottom'
					ORDER BY
						section_id,id";
	$ret_dynamic = $db->query($sql_dynamic);
	if($db->num_rows($ret_dynamic))
	{
		$prev_sec = 0;
		$dynamicbottom_str	= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
		while ($row_dynamic = $db->fetch_array($ret_dynamic))
		{
			if ($prev_sec!=$row_dynamic['section_id']) // Check whether section name is to be displayed
			{
				$prev_sec = $row_dynamic['section_id'];
				if ($row_dynamic['section_name']!='')
					$dynamicbottom_str		.= '<tr><td align="left" colspan="2" '.$style_head.'>'.stripslashes($row_dynamic['section_name']).'</td></tr>';
			}
			$dynamicbottom_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.stripslashes($row_dynamic['dynamic_label']).'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_dynamic['dynamic_value']).'</td></tr>';
		}
		$dynamicbottom_str	.= '</table>';
	}
	// ##############################################################################
	// Concatenating the billing address
	// ##############################################################################
	$billing_addr		= $dynamictop_str.$bill_str.$dynamicbottom_str;
	return $billing_addr;
}
/* Function to get the billing related details */
function get_EmailDeliveryDetails($order_id,$row_ords)
{
	global $db,$ecom_siteid;
	// ##############################################################################
	// Delivery details
	// ##############################################################################
	// ##############################################################################
	// 								Billing details
	// ##############################################################################
	// Get the checkout fields from general_settings_sites_checkoutfields table
	$sql_checkout = "SELECT field_key,field_name
						FROM
							general_settings_site_checkoutfields
						WHERE
							sites_site_id = $ecom_siteid
							AND field_type IN ('DELIVERY')
						ORDER BY
							field_order";
	$ret_checkout = $db->query($sql_checkout);
	if($db->num_rows($ret_checkout))
	{
		while ($row_checkout = $db->fetch_array($ret_checkout))
		{
			$chkorder_arr[$row_checkout['field_key']] = stripslashes($row_checkout['field_name']);
		}
	}
	$style_head = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:bold;border-bottom:1px solid  #f8f8f8;'";
	$style_desc = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;'";
	$style_desc_prodname ="style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;text-decoration:none;'";

	// Get the delivery details corresponding to current order
	$sql_del		= "SELECT delivery_title,delivery_fname,delivery_mname,delivery_lname,
						delivery_companyname,delivery_buildingnumber,delivery_street,delivery_city,delivery_state,
						delivery_country,delivery_zip,delivery_phone,delivery_fax,delivery_mobile,
						delivery_email
					FROM
						order_delivery_data
					WHERE
						orders_order_id = $order_id
					LIMIT
						1";
	$ret_del		= $db->query($sql_del);
	if ($db->num_rows($ret_del))
	{
		$row_del = $db->fetch_array($ret_del);
	}
	$del_str		= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
	$del_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Name</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_del['delivery_title']).''.stripslashes($row_del['delivery_fname']).' '.stripslashes($row_del['delivery_mname']).' '.stripslashes($row_del['delivery_lname']).' '.'</td></tr>';
	$del_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkoutdelivery_comp_name'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_del['delivery_companyname']).'</td></tr>';
	$del_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkoutdelivery_building'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_del['delivery_buildingnumber']).'</td></tr>';
	$del_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkoutdelivery_street'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_del['delivery_street']).'</td></tr>';
	$del_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkoutdelivery_city'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_del['delivery_city']).'</td></tr>';
	$del_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkoutdelivery_state'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_del['delivery_state']).'</td></tr>';
	$del_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkoutdelivery_country'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_del['delivery_country']).'</td></tr>';
	$del_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkoutdelivery_zipcode'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_del['delivery_zip']).'</td></tr>';
	$del_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkoutdelivery_phone'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_del['delivery_phone']).'</td></tr>';
	$del_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkoutdelivery_mobile'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_del['delivery_mobile']).'</td></tr>';
	$del_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkoutdelivery_fax'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_del['delivery_fax']).'</td></tr>';
	$del_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkoutdelivery_email'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_del['delivery_email']).'</td></tr>';
	$del_str		.= '</table>';

	// ##############################################################################
	// Delivery Type
	// ##############################################################################
	if ($row_ord['order_delivery_type']!='None')
	{
		$del_str	.= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
		$del_str	.= '<tr><td align="left" width="50%" '.$style_head.'>Method</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_ords['order_deliverytype']).'</td></tr>';
		if($row_ords['order_delivery_option']!='') // case if delivery option exists
			$del_str	.= '<tr><td align="left" width="50%" '.$style_head.'>Option</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_ords['order_delivery_option']).'</td></tr>';
		if ($row_ord['order_deliveryprice_only']>0) // case of delivery charge along
		{
			$del_str	.= '<tr><td align="left" width="50%" '.$style_head.'>Base Delivery Charge</td><td align="left" width="50%" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_deliveryprice_only'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
		}
		if ($row_ord['order_splitdeliveryreq']!='Y') // Case of split delivery
		{
			$del_str	.= '<tr><td align="left" width="50%" '.$style_head.'>Split Delivery</td><td align="left" width="50%" '.$style_desc.'>Yes</td></tr>';
		}
		if ($row_ord['order_extrashipping']>0) // case of extra shipping exists
		{
			$del_str	.= '<tr><td align="left" width="50%" '.$style_head.'>Extra Shipping Cost</td><td align="left" width="50%" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_extrashipping'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
		}
		$del_str	.= '<tr><td align="left" width="50%" '.$style_head.'>Delivery Total</td><td align="right" width="50%" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_deliverytotal'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
		$del_str 	.= '</table>';
	}
	return $del_str;
}
/* Function to get the giftwrap related details */
function get_EmailGiftwrapDetails($order_id,$row_ords)
{
	global $db,$ecom_siteid;
	// ##############################################################################
	// Gift wrap details
	// ##############################################################################
	// Check whether gift wrap exists
	if($row_ords['order_giftwrap']=='Y')
	{
		$giftdet_str		= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
			$style_head = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:bold;border-bottom:1px solid  #f8f8f8;'";
			$style_desc = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;'";
			$style_desc_prodname ="style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;text-decoration:none;'";

		$giftdet_str		.= ' <tr>
                                    <td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top" colspan="2">Giftwrap Details</td>
                                </tr>';
		if ($row_ords['order_giftwrap_per']=='order')
		{
			$giftdet_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Apply to </td><td align="left" width="50%" '.$style_desc.'>Order</td></tr>';
		}
		else
			$giftdet_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Apply to </td><td align="left" width="50%" '.$style_desc.'>Individual Items</td></tr>';

		if ($row_ords['order_giftwrap_minprice']>0)
			$giftdet_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Minimum Price for Gift wrap </td><td align="left" width="50%" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_giftwrap_minprice'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';

		if ($row_ords['order_giftwrapmessage']=='Y')
		{
			$giftdet_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Message</td><td align="left" width="50%" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_giftwrap_message_charge'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
			$giftdet_str		.= '<tr><td align="left" colspan="2" '.$style_desc.'>'.stripslashes($row_ords['order_giftwrapmessage_text']).'</td></tr>';
		}

		$sql_gift			= "SELECT giftwrap_name,giftwrap_price,giftwrap_price
								FROM
									order_giftwrap_details
								WHERE
									orders_order_id=$order_id";
		$ret_gift			= $db->query($sql_gift);
		if ($db->num_rows($ret_gift))
		{
			while ($row_gift = $db->fetch_array($ret_gift))
			{
				$giftdet_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.stripslashes($row_gift['giftwrap_name']).'</td><td align="left" width="50%" '.$style_desc.'>'.print_price_selected_currency($row_gift['giftwrap_price'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
			}
		}
		$giftdet_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Gift Wrap Total</td><td align="left" width="50%" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_giftwraptotal'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
		// Catting the total of gift wrap to giftdet_str variable
		$giftdet_str		.= '</table>';
	}
	else
		$giftdet_str = '';
	return $giftdet_str;
}

/* Function to get the bonus details */
function get_EmailBonusDetails($order_id,$row_ords)
{
	global $db,$ecom_siteid;
	$bonus_str = '';
	// ##############################################################################
	// Bonus Points Checking
	// ##############################################################################
	$style_head = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:bold;border-bottom:1px solid  #f8f8f8;'";
	$style_desc = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;'";
	$style_desc_prodname ="style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;text-decoration:none;'";

	if($row_ords['order_bonuspoints_used']>0 or $row_ords['order_bonuspoint_inorder']>0)// if bonus points used or bonus points achieved in currenr order
	{
		$bonus_str	= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
		if ($row_ords['order_bonuspoints_used']>0)
		{
			$bonus_str	.= '<tr><td align="left" width="50%" '.$style_head.'>Bonus Points Used</td><td align="left" width="50%" '.$style_desc.'>'.$row_ords['order_bonuspoints_used'].'</td></tr>';
		}
		if ($row_ords['order_bonuspoints_used']>0)
		{
			$bonus_str	.= '<tr><td align="left" width="50%" '.$style_head.'>Bonus Points Rate</td><td align="left" width="50%" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_bonusrate'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
		}
		if($row_ords['order_bonuspoint_discount']>0)
		{
			$bonus_str	.= '<tr><td align="left" width="50%" '.$style_head.'>Bonus Points Discount</td><td align="left" width="50%" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_bonuspoint_discount'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
		}
		if ($row_ords['order_bonuspoint_inorder']>0)
		{
			$bonus_str	.= '<tr><td align="left" width="50%" '.$style_head.'>Bonus Points gained due to this order</td><td align="left" width="50%" '.$style_desc.'>'.$row_ords['order_bonuspoint_inorder'].'</td></tr>';
		}
		$bonus_str 	.= 	'</table>';
		/* Donate bonus Start */
		if($row_ords['order_bonuspoints_donated']>0)
		{
			$bonus_str	.= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
			$bonus_str	.= '<tr><td align="left" width="50%" '.$style_head.'>Bonus Points Donated</td><td align="left" width="50%" '.$style_desc.'>'.$row_ords['order_bonuspoints_donated'].'</td></tr>';
			$bonus_str 	.= 	'</table>';
		}
		/* Donate bonus End */
	}
	return $bonus_str;
}
/* Function to get the tax details */
function get_EmailTaxDetails($order_id,$row_ords)
{
	global $db,$ecom_siteid;
	$tax_str = '';
	// ##############################################################################
	// Tax Details
	// ##############################################################################
    $style_head = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:bold;border-bottom:1px solid  #f8f8f8;'";
	$style_desc = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;'";

	$sql_tax 	= "SELECT tax_name,tax_percent,tax_charge
					FROM
						order_tax_details
					WHERE
						orders_order_id = $order_id";
	$ret_tax	= $db->query($sql_tax);
	if ($db->num_rows($ret_tax))
	{
		$tax_str	= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
		$tax_str	.=  '<tr>
                                    <td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top" colspan="2">Tax Details</td>
                        </tr>';
		while ($row_tax = $db->fetch_array($ret_tax))
		{
			$tax_str	.= '<tr><td align="left" width="50%" '.$style_head.'>'.stripslashes($row_tax['tax_name']).'('.$row_tax['tax_percent'].'%)'.'</td><td align="left" width="50%" '.$style_desc.'>'.print_price_selected_currency($row_tax['tax_charge'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
		}
		$tax_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Total Tax</td><td align="left" width="50%" '.$style_desc.'>'.print_price_selected_currency($row_ords['order_tax_total'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
		$tax_str 		.= 	'</table>';
	}
	return  $tax_str;
}

/* Function to get the promotional / gift voucher details */
function get_EmailPromVoucherDetails($order_id,$row_ords)
{
	global $db,$ecom_siteid;
	$prom_str = '';
	// ##############################################################################
	// Promotional Code or Gift Voucher Details
	// ##############################################################################
	  $style_head = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:bold;border-bottom:1px solid  #f8f8f8;'";
	  $style_desc = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;'";

	if($row_ords['gift_vouchers_voucher_id'] or $row_ords['promotional_code_code_id'])
	{
		$prom_str		= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
		if($row_ords['gift_vouchers_voucher_id'])
		{
			// Get the gift voucher details
			$sql_voucher = "SELECT voucher_no,voucher_value_used
								FROM
									order_voucher
								WHERE
									orders_order_id = $order_id
								LIMIT
									1";
			$ret_voucher = $db->query($sql_voucher);
			if ($db->num_rows($ret_voucher))
			{
				$row_voucher 	= $db->fetch_array($ret_voucher);
				$prom_str	.=  '<tr>
                                    <td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top" colspan="2">Gift voucher Details</td>
                        		</tr>';
				$prom_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Gift Voucher Code</td><td align="left" width="50%" '.$style_desc.'>'.$row_voucher['voucher_no'].'</td></tr>';
				$prom_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Gift Voucher Discount</td><td align="left" width="50%" '.$style_desc.'>'.print_price_selected_currency($row_voucher['voucher_value_used'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
				$promtotal_str	= '<tr><td align="left" width="50%" colspan="2" '.$style_head.'>Gift Voucher Discount</td><td align="left" width="50%" colspan="3" '.$style_desc.'>'.print_price_selected_currency($row_voucher['voucher_value_used'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
			}
		}
		elseif($row_ords['promotional_code_code_id'])
		{
			// Get the promotional code details
			$sql_prom = "SELECT code_number,code_lessval,code_type
								FROM
									order_promotional_code
								WHERE
									orders_order_id = $order_id
								LIMIT
									1";
			$ret_prom = $db->query($sql_prom);
			if ($db->num_rows($ret_prom))
			{
				$row_prom 	= $db->fetch_array($ret_prom);
				$prom_str	.=  '<tr>
                                    <td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top" colspan="2">Promotional Code Details</td>
                        		</tr>';
				$prom_str	.= '<tr><td align="left" width="50%" '.$style_head.'>Promotional Code</td><td align="left" width="50%" '.$style_desc.'>'.$row_prom['code_number'].'</td></tr>';
				if ($row_prom['code_type']!='product') // show only if not of type 'product' if type is product discount will be shown with product listing
				{
					$prom_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Promotional Code Discount</td><td align="left" width="50%" '.$style_desc.'>'.print_price_selected_currency($row_prom['code_lessval'],$row_ords['order_currency_convertionrate'],$row_ords['order_currency_symbol'],true).'</td></tr>';
				}
			}
		}
		$prom_str 		.= 	'</table>';
	}
	return $prom_str;
}
/* Function to get the promotional code type.*/
function get_promotional_type($type)
{
	global $db,$ecom_siteid;
	 if($type=='default')
	 {
	  $str = '% Off on grand total';
	 }
	 elseif($type=='money')
	 {
	   $str = 'Money Off on minimum value of grand total';
	 }
	 elseif($type=='percent')
	 {
	 	 $str = '% Off on minimum value of grand total';
	 }
	 else
	 {
 	  	$str ='Value Off on selected products';
	 }
	 return $str;
}

/* Function to get a uniquie voucher number*/
function get_UniqueVoucherNumber()
{
	global $db,$ecom_siteid,$ecom_hostname;
	$sql = "SELECT voucher_prefix 
				FROM general_settings_sites_common 
					WHERE sites_site_id='".$ecom_siteid."'";
			
	$res = $db->query($sql);
	$row = $db->fetch_array($res);				
	if ($row['voucher_prefix']=='')
		$prefix = '';
	else
		$prefix 	= $row['voucher_prefix'];
	$voucher_num	= $prefix.strtoupper(substr(md5(uniqid()),-16));// take md5 or uniquid() and get the last 16 digits
	return strtoupper($voucher_num);
}
/* Function to send voucher mail */
function save_and_send_VoucherMail($mail_type,$vouch_arr)
{
	global $db,$ecom_siteid,$ecom_hostname;
	// Check the mail type
	switch($mail_type)
	{
		case 'Paid': // Voucher payment success
			// Get the content of email template
			$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
									FROM
										general_settings_site_letter_templates
									WHERE
										sites_site_id = $ecom_siteid
										AND lettertemplate_letter_type = 'VOUCHER_PAYMENT_AUTHORIZE'
									LIMIT
										1";
			$ret_template = $db->query($sql_template);
			if ($db->num_rows($ret_template))
			{
				$row_template 		= $db->fetch_array($ret_template);
				$email_from			= stripslashes($row_template['lettertemplate_from']);
				$email_subject		= stripslashes($row_template['lettertemplate_subject']);
				$email_content		= stripslashes($row_template['lettertemplate_contents']);
				$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);
	
				/* triggering one more query to pick the date and statu of current voucher*/
				$sql_vouchmore = "SELECT voucher_paystatus_manuallychanged,voucher_paystatus_manuallychanged_on,
										 voucher_paystatus_manuallychanged_by,voucher_paystatus,
										 voucher_activatedon,voucher_expireson 
									FROM 
										gift_vouchers 
									WHERE 
										voucher_id = ".$vouch_arr['voucher_id']." 
									LIMIT 
										1";
				$ret_vouchmore = $db->query($sql_vouchmore);
				if ($db->num_rows($ret_vouchmore))
				{
					$row_vouchmore = $db->fetch_array($ret_vouchmore);
				}						
				$note		= $vouch_arr['note'];
				$activate	= $row_vouchmore['voucher_activatedon'];
				$expires	= $row_vouchmore['voucher_expireson'];
				if($activate!='0000-00-00')
				{
					$activated_on = dateFormat($activate);
				}
				else
					$activated_on = '';
				if($expires!='0000-00-00')
				{
					$expires_on = dateFormat($expires);
				}
				else
					$expires_on = '';	
				if ($row_vouchmore['voucher_paystatus_manuallychanged']==1)
				{
					$manually_changed_on = dateFormat($row_vouchmore['voucher_paystatus_manuallychanged_on']);
				}
				else
				{
					$manuall_changed_on = '';
				}
				// Calling function to get the customer details and sendto details of current voucher
				$det_for_mail			= get_VoucherCustomerDetailsForMail($vouch_arr['voucher_id']);
				$bought_details_str		= $det_for_mail['bought_det'];
				$sendto_details_str		= $det_for_mail['send_det'];
				$cname					= $det_for_mail['cust_name'];
				$cemail					= $det_for_mail['cust_email'];
				if($note!='')
				{
					$note_str=   ' <tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top" colspan=2>Note</td>
								 </tr>';
					$note_str .=    '<tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top" colspan=2>'.$note.'</td>
										  </tr>'	;
				}
				$search_arr	= array (
										'[name]',
										'[domain]',
										'[bought_by_details]',
										'[authorize_date]',
										'[voucherdate]',
										'[activated_on]',
										'[expires_on]',
										'[send_to_details]',
										'[voucher_code]',
										'[voucher_value]',
										'[total_usage]',
										'[payment_status]',
										'[note]'
									);
				$replace_arr= array(
										$cname,
										$ecom_hostname,
										$bought_details_str,
										$manually_changed_on,
										dateFormat($vouch_arr['voucher_boughton']),
										$activated_on,
										$expires_on,
										$sendto_details_str,
										$vouch_arr['voucher_number'],
										print_price_selected_currency($vouch_arr['voucher_value'],$vouch_arr['voucher_curr_rate'],$vouch_arr['voucher_curr_symbol'],true),
										$vouch_arr['voucher_max_usage'],
										getpaymentstatus_Name($row_vouchmore['voucher_paystatus']),
										$note_str
									);					
				
				// Do the replacement in email template content
				$email_content = str_replace($search_arr,$replace_arr,$email_content);
				// Building email headers to be used with the mail
				$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
				$email_headers 	.= "MIME-Version: 1.0\n";
				$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";
	
				// Saving the email to gift_voucher_emails table
				$insert_array								= array();
				$insert_array['gift_vouchers_voucher_id']	= $vouch_arr['voucher_id'];
				$insert_array['email_to']					= $cemail;
				$insert_array['email_subject']				= add_slash($email_subject);
				//$insert_array['email_message']				= addslashes($email_content);
				$insert_array['email_headers']				= add_slash($email_headers,false);
				$insert_array['email_type']					= 'VOUCHER_PAYMENT_AUTHORIZE';
				$insert_array['email_sendonce']				= ($email_disabled==0)?1:0;
				$insert_array['email_lastsenddate']			= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
				$db->insert_from_array($insert_array,'gift_voucher_emails');
				$mail_insert_id = $db->insert_id();
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('vouch',$mail_insert_id,(stripslashes($email_content)));
	//echo $email_subject."To-".$cemail."--c--".$email_content;exit;
				if($email_disabled==0)// check whether mail sending is disabled
				{
					mail($cemail, $email_subject,$email_content, $email_headers);
				}
			}
		break;	
		case 'Pay_Failed': // Voucher payment failed
			// Get the content of email template
			$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
									FROM
										general_settings_site_letter_templates
									WHERE
										sites_site_id = $ecom_siteid
										AND lettertemplate_letter_type = 'VOUCHER_PAYMENT_FAILED'
									LIMIT
										1";
			$ret_template = $db->query($sql_template);
			if ($db->num_rows($ret_template))
			{
				$row_template 		= $db->fetch_array($ret_template);
				$email_from			= stripslashes($row_template['lettertemplate_from']);
				$email_subject		= stripslashes($row_template['lettertemplate_subject']);
				$email_content		= stripslashes($row_template['lettertemplate_contents']);
				$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);
	
				/* triggering one more query to pick the date and statu of current voucher*/
				$sql_vouchmore = "SELECT voucher_paystatus_manuallychanged,voucher_paystatus_manuallychanged_on,
										 voucher_paystatus_manuallychanged_by,voucher_paystatus,
										 voucher_activatedon,voucher_expireson 
									FROM 
										gift_vouchers 
									WHERE 
										voucher_id = ".$vouch_arr['voucher_id']." 
									LIMIT 
										1";
				$ret_vouchmore = $db->query($sql_vouchmore);
				if ($db->num_rows($ret_vouchmore))
				{
					$row_vouchmore = $db->fetch_array($ret_vouchmore);
				}						
	
				$note		= $vouch_arr['note'];
				$activated_on = '';
				$expires_on = dateFormat($expires);
				
				if ($row_vouchmore['voucher_paystatus_manuallychanged']==1)
				{
					$manually_changed_on = dateFormat($row_vouchmore['voucher_paystatus_manuallychanged_on']);
				}
				else
				{
					$manuall_changed_on = '';
				}
				// Calling function to get the customer details and sendto details of current voucher
				$det_for_mail			= get_VoucherCustomerDetailsForMail($vouch_arr['voucher_id']);
				$bought_details_str		= $det_for_mail['bought_det'];
				$sendto_details_str		= $det_for_mail['send_det'];
				$cname					= $det_for_mail['cust_name'];
				$cemail					= $det_for_mail['cust_email'];
			    if($note!='')
				{
					$note_str=   ' <tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top" colspan=2>Note</td>
								 </tr>';
					$note_str .=    '<tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top" colspan=2>'.$note.'</td>
										  </tr>'	;
				}
				$search_arr	= array (
										'[name]',
										'[domain]',
										'[bought_by_details]',
										'[failed_date]',
										'[voucherdate]',
										'[activated_on]',
										'[expires_on]',
										'[send_to_details]',
										'[voucher_code]',
										'[voucher_value]',
										'[total_usage]',
										'[payment_status]',
										'[reason]'
									);
				$replace_arr= array(
										$cname,
										$ecom_hostname,
										$bought_details_str,
										$manually_changed_on,
										dateFormat($vouch_arr['voucher_boughton']),
										$activated_on,
										$expires_on,
										$sendto_details_str,
										$vouch_arr['voucher_number'],
										print_price_selected_currency($vouch_arr['voucher_value'],$vouch_arr['voucher_curr_rate'],$vouch_arr['voucher_curr_symbol'],true),
										$vouch_arr['voucher_max_usage'],
										getpaymentstatus_Name($row_vouchmore['voucher_paystatus']),
										$note_str
									);					
				
				// Do the replacement in email template content
				$email_content = str_replace($search_arr,$replace_arr,$email_content);
				// Building email headers to be used with the mail
				$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
				$email_headers 	.= "MIME-Version: 1.0\n";
				$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";
	
				// Saving the email to gift_voucher_emails table
				$insert_array								= array();
				$insert_array['gift_vouchers_voucher_id']	= $vouch_arr['voucher_id'];
				$insert_array['email_to']					= $cemail;
				$insert_array['email_subject']				= add_slash($email_subject);
				//$insert_array['email_message']				= add_slash($email_content,false);
				$insert_array['email_headers']				= add_slash($email_headers,false);
				$insert_array['email_type']					= 'VOUCHER_PAYMENT_FAILED';
				$insert_array['email_sendonce']				= ($email_disabled==0)?1:0;
				$insert_array['email_lastsenddate']			= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
				$db->insert_from_array($insert_array,'gift_voucher_emails');
				$mail_insert_id = $db->insert_id();
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('vouch',$mail_insert_id,(stripslashes($email_content)));
				if($email_disabled==0)// check whether mail sending is disabled
				{
					mail($cemail, $email_subject,$email_content, $email_headers);
				}
			}
		break;
		case 'AUTHORISE': // Voucher authenticate authorise
			// Get the content of email template
			$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
									FROM
										general_settings_site_letter_templates
									WHERE
										sites_site_id = $ecom_siteid
										AND lettertemplate_letter_type = 'VOUCHER_PAY_AUTHORISE'
									LIMIT
										1";
			$ret_template = $db->query($sql_template);
			if ($db->num_rows($ret_template))
			{
				$row_template 		= $db->fetch_array($ret_template);
				$email_from			= stripslashes($row_template['lettertemplate_from']);
				$email_subject		= stripslashes($row_template['lettertemplate_subject']);
				$email_content		= stripslashes($row_template['lettertemplate_contents']);
				$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);
	
				/* triggering one more query to pick the date and statu of current voucher*/
				$sql_vouchmore = "SELECT voucher_paystatus_manuallychanged,voucher_paystatus_manuallychanged_on,
										 voucher_paystatus_manuallychanged_by,voucher_paystatus,
										 voucher_activatedon,voucher_expireson 
									FROM 
										gift_vouchers 
									WHERE 
										voucher_id = ".$vouch_arr['voucher_id']." 
									LIMIT 
										1";
				$ret_vouchmore = $db->query($sql_vouchmore);
				if ($db->num_rows($ret_vouchmore))
				{
					$row_vouchmore = $db->fetch_array($ret_vouchmore);
					if ($row_vouchmore['voucher_paystatus']=='Paid')
					{
						$activate	= $row_vouchmore['voucher_activatedon'];
						$expires	= $row_vouchmore['voucher_expireson'];
						if($activate!='0000-00-00')
						{
							$activated_on = dateFormat($activate);
						}
						else
							$activated_on = '';
						if($expires!='0000-00-00')
						{
							$expires_on = dateFormat($expires);
						}
						else
							$expires_on = '';	
						if ($row_vouchmore['voucher_paystatus_manuallychanged']==1)
						{
							$manually_changed_on = dateFormat($row_vouchmore['voucher_paystatus_manuallychanged_on']);
						}
						else
						{
							$manuall_changed_on = '';
						}
					}
					else 
					{
						$activated_on 		= '-';		
						$expires_on 		= '-';		
						$manuall_changed_on = '-';
					}
				}	
				$note		= $vouch_arr['note'];
				$auth_amt	= $vouch_arr['auth_amt'];
				// Calling function to get the customer details and sendto details of current voucher
				$det_for_mail			= get_VoucherCustomerDetailsForMail($vouch_arr['voucher_id']);
				$bought_details_str		= $det_for_mail['bought_det'];
				$sendto_details_str		= $det_for_mail['send_det'];
				$cname					= $det_for_mail['cust_name'];
				$cemail					= $det_for_mail['cust_email'];
				if($note!='')
				{
					$note_str=   ' <tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top" colspan=2>Note</td>
								 </tr>';
					$note_str .=    '<tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top" colspan=2>'.$note.'</td>
										  </tr>'	;
				}
				$search_arr	= array (
										'[name]',
										'[domain]',
										'[bought_by_details]',
										'[auth_date]',
										'[voucherdate]',
										'[activated_on]',
										'[expires_on]',
										'[send_to_details]',
										'[voucher_code]',
										'[voucher_value]',
										'[total_usage]',
										'[payment_status]',
										'[note]',
										'[auth_amt]'
									);
				$replace_arr= array(
										$cname,
										$ecom_hostname,
										$bought_details_str,
										$manually_changed_on,
										dateFormat($vouch_arr['voucher_boughton']),
										$activated_on,
										$expires_on,
										$sendto_details_str,
										$vouch_arr['voucher_number'],
										print_price_selected_currency($vouch_arr['voucher_value'],$vouch_arr['voucher_curr_rate'],$vouch_arr['voucher_curr_symbol'],true),
										$vouch_arr['voucher_max_usage'],
										getpaymentstatus_Name($row_vouchmore['voucher_paystatus']),
										$note_str,
										print_price_selected_currency($auth_amt,$vouch_arr['voucher_curr_rate'],$vouch_arr['voucher_curr_symbol'],true)								
									);					
				
				// Do the replacement in email template content
				$email_content = str_replace($search_arr,$replace_arr,$email_content);
				// Building email headers to be used with the mail
				$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
				$email_headers 	.= "MIME-Version: 1.0\n";
				$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";
	
				// Saving the email to gift_voucher_emails table
				$insert_array								= array();
				$insert_array['gift_vouchers_voucher_id']	= $vouch_arr['voucher_id'];
				$insert_array['email_to']					= $cemail;
				$insert_array['email_subject']				= add_slash($email_subject);
				//$insert_array['email_message']				= add_slash($email_content,false);
				$insert_array['email_headers']				= add_slash($email_headers,false);
				$insert_array['email_type']					= 'VOUCHER_PAY_AUTHORISE';
				$insert_array['email_sendonce']				= ($email_disabled==0)?1:0;
				$insert_array['email_lastsenddate']			= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
				$db->insert_from_array($insert_array,'gift_voucher_emails');
				$mail_insert_id = $db->insert_id();
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('vouch',$mail_insert_id,(stripslashes($email_content)));
				if($email_disabled==0)// check whether mail sending is disabled
				{
					mail($cemail, $email_subject,$email_content, $email_headers);
				}
			}
		break;
		case 'ABORTED': // Order Deferred aborted
			// Get the content of email template
			$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
									FROM
										general_settings_site_letter_templates
									WHERE
										sites_site_id = $ecom_siteid
										AND lettertemplate_letter_type = 'VOUCHER_PAY_AUTH_ABORT'
									LIMIT
										1";
			$ret_template = $db->query($sql_template);
			if ($db->num_rows($ret_template))
			{
				$row_template 		= $db->fetch_array($ret_template);
				$email_from			= stripslashes($row_template['lettertemplate_from']);
				$email_subject		= stripslashes($row_template['lettertemplate_subject']);
				$email_content		= stripslashes($row_template['lettertemplate_contents']);
				$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);
	
				/* triggering one more query to pick the date and statu of current voucher*/
				$sql_vouchmore = "SELECT voucher_paystatus_manuallychanged,voucher_paystatus_manuallychanged_on,
										 voucher_paystatus_manuallychanged_by,voucher_paystatus,
										 voucher_activatedon,voucher_expireson 
									FROM 
										gift_vouchers 
									WHERE 
										voucher_id = ".$vouch_arr['voucher_id']." 
									LIMIT 
										1";
				$ret_vouchmore = $db->query($sql_vouchmore);
				if ($db->num_rows($ret_vouchmore))
				{
					$row_vouchmore = $db->fetch_array($ret_vouchmore);
					
				}	
				$activated_on = '';
				$expires_on = '';	
				if ($row_vouchmore['voucher_paystatus_manuallychanged']==1)
				{
					$manually_changed_on = dateFormat($row_vouchmore['voucher_paystatus_manuallychanged_on']);
				}
				else
				{
					$manuall_changed_on = '';
				}
				$note		= $vouch_arr['note'];
				// Calling function to get the customer details and sendto details of current voucher
				$det_for_mail			= get_VoucherCustomerDetailsForMail($vouch_arr['voucher_id']);
				$bought_details_str		= $det_for_mail['bought_det'];
				$sendto_details_str		= $det_for_mail['send_det'];
				$cname					= $det_for_mail['cust_name'];
				$cemail					= $det_for_mail['cust_email'];
				if($note!='')
				{
					$note_str=   ' <tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top" colspan=2>Note</td>
								 </tr>';
					$note_str .=    '<tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top" colspan=2>'.$note.'</td>
										  </tr>'	;
				}
				$search_arr	= array (
										'[name]',
										'[domain]',
										'[bought_by_details]',
										'[aborted_on]',
										'[voucherdate]',
										'[activated_on]',
										'[expires_on]',
										'[send_to_details]',
										'[voucher_code]',
										'[voucher_value]',
										'[total_usage]',
										'[payment_status]',
										'[reason]'
									);
				$replace_arr= array(
										$cname,
										$ecom_hostname,
										$bought_details_str,
										$manually_changed_on,
										dateFormat($vouch_arr['voucher_boughton']),
										$activated_on,
										$expires_on,
										$sendto_details_str,
										$vouch_arr['voucher_number'],
										print_price_selected_currency($vouch_arr['voucher_value'],$vouch_arr['voucher_curr_rate'],$vouch_arr['voucher_curr_symbol'],true),
										$vouch_arr['voucher_max_usage'],
										getpaymentstatus_Name($row_vouchmore['voucher_paystatus']),
										$note_str
									);					
				
				// Do the replacement in email template content
				$email_content 	= str_replace($search_arr,$replace_arr,$email_content);
				// Building email headers to be used with the mail
				$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
				$email_headers 	.= "MIME-Version: 1.0\n";
				$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";
	
				// Saving the email to gift_voucher_emails table
				$insert_array								= array();
				$insert_array['gift_vouchers_voucher_id']	= $vouch_arr['voucher_id'];
				$insert_array['email_to']					= $cemail;
				$insert_array['email_subject']				= add_slash($email_subject);
				//$insert_array['email_message']				= add_slash($email_content,false);
				$insert_array['email_headers']				= add_slash($email_headers,false);
				$insert_array['email_type']					= 'VOUCHER_PAY_AUTH_ABORT';
				$insert_array['email_sendonce']				= ($email_disabled==0)?1:0;
				$insert_array['email_lastsenddate']			= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
				$db->insert_from_array($insert_array,'gift_voucher_emails');
				$mail_insert_id = $db->insert_id();
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('vouch',$mail_insert_id,(stripslashes($email_content)));
				if($email_disabled==0)// check whether mail sending is disabled
				{
					mail($cemail, $email_subject,$email_content, $email_headers);
				}
			}
		break;
		case 'CANCELLED': // Order authenticate Cancel
			// Get the content of email template
			$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled 
									FROM
										general_settings_site_letter_templates 
									WHERE
										sites_site_id = $ecom_siteid 
										AND lettertemplate_letter_type = 'VOUCHER_PAY_CANCEL' 
									LIMIT
										1";
			$ret_template = $db->query($sql_template);
			if ($db->num_rows($ret_template))
			{
				$row_template 		= $db->fetch_array($ret_template);
				$email_from			= stripslashes($row_template['lettertemplate_from']);
				$email_subject		= stripslashes($row_template['lettertemplate_subject']);
				$email_content		= stripslashes($row_template['lettertemplate_contents']);
				$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);
	
				/* triggering one more query to pick the date and statu of current voucher*/
				$sql_vouchmore = "SELECT voucher_paystatus_manuallychanged,voucher_paystatus_manuallychanged_on,
										 voucher_paystatus_manuallychanged_by,voucher_paystatus,
										 voucher_activatedon,voucher_expireson 
									FROM 
										gift_vouchers 
									WHERE 
										voucher_id = ".$vouch_arr['voucher_id']." 
									LIMIT 
										1";
				$ret_vouchmore = $db->query($sql_vouchmore);
				if ($db->num_rows($ret_vouchmore))
				{
					$row_vouchmore = $db->fetch_array($ret_vouchmore);
					
				}	
				$activated_on = '';
				$expires_on = '';	
				if ($row_vouchmore['voucher_paystatus_manuallychanged']==1)
				{
					$manually_changed_on = dateFormat($row_vouchmore['voucher_paystatus_manuallychanged_on']);
				}
				else
				{
					$manuall_changed_on = '';
				}
				$note		= $vouch_arr['note'];
				
				// Calling function to get the customer details and sendto details of current voucher
				$det_for_mail			= get_VoucherCustomerDetailsForMail($vouch_arr['voucher_id']);
				$bought_details_str		= $det_for_mail['bought_det'];
				$sendto_details_str		= $det_for_mail['send_det'];
				$cname					= $det_for_mail['cust_name'];
				$cemail					= $det_for_mail['cust_email'];
				if($note!='')
				{
					$note_str=   ' <tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top" colspan=2>Note</td>
								 </tr>';
					$note_str .=    '<tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top" colspan=2>'.$note.'</td>
										  </tr>'	;
				}
				$search_arr	= array (
										'[name]',
										'[domain]',
										'[bought_by_details]',
										'[cancel_date]',
										'[voucherdate]',
										'[activated_on]',
										'[expires_on]',
										'[send_to_details]',
										'[voucher_code]',
										'[voucher_value]',
										'[total_usage]',
										'[payment_status]',
										'[reason]'
									);
				$replace_arr= array(
										$cname,
										$ecom_hostname,
										$bought_details_str,
										$manually_changed_on,
										dateFormat($vouch_arr['voucher_boughton']),
										$activated_on,
										$expires_on,
										$sendto_details_str,
										$vouch_arr['voucher_number'],
										print_price_selected_currency($vouch_arr['voucher_value'],$vouch_arr['voucher_curr_rate'],$vouch_arr['voucher_curr_symbol'],true),
										$vouch_arr['voucher_max_usage'],
										getpaymentstatus_Name($row_vouchmore['voucher_paystatus']),
										$note_str
									);					
				
				// Do the replacement in email template content
				$email_content 	= str_replace($search_arr,$replace_arr,$email_content);
				// Building email headers to be used with the mail
				$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
				$email_headers 	.= "MIME-Version: 1.0\n";
				$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";
	
				// Saving the email to gift_voucher_emails table
				$insert_array								= array();
				$insert_array['gift_vouchers_voucher_id']	= $vouch_arr['voucher_id'];
				$insert_array['email_to']					= $cemail;
				$insert_array['email_subject']				= add_slash($email_subject);
				//$insert_array['email_message']				= add_slash($email_content,false);
				$insert_array['email_headers']				= add_slash($email_headers,false);
				$insert_array['email_type']					= 'VOUCHER_PAY_CANCEL';
				$insert_array['email_sendonce']				= ($email_disabled==0)?1:0;
				$insert_array['email_lastsenddate']			= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
				$db->insert_from_array($insert_array,'gift_voucher_emails');
				$mail_insert_id = $db->insert_id();
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('vouch',$mail_insert_id,(stripslashes($email_content)));
				if($email_disabled==0)// check whether mail sending is disabled
				{
					mail($cemail, $email_subject,$email_content, $email_headers);
				}
			}
		break;
		case 'RELEASE': // Voucher payment release for Deferred
			// Get the content of email template
			$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
									FROM
										general_settings_site_letter_templates
									WHERE
										sites_site_id = $ecom_siteid
										AND lettertemplate_letter_type = 'VOUCHER_PAY_RELEASE'
									LIMIT
										1";
			$ret_template = $db->query($sql_template);
			if ($db->num_rows($ret_template))
			{
				$row_template 		= $db->fetch_array($ret_template);
				$email_from			= stripslashes($row_template['lettertemplate_from']);
				$email_subject		= stripslashes($row_template['lettertemplate_subject']);
				$email_content		= stripslashes($row_template['lettertemplate_contents']);
				$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);
	
				/* triggering one more query to pick the date and statu of current voucher*/
				$sql_vouchmore = "SELECT voucher_paystatus_manuallychanged,voucher_paystatus_manuallychanged_on,
										 voucher_paystatus_manuallychanged_by,voucher_paystatus,
										 voucher_activatedon,voucher_expireson 
									FROM 
										gift_vouchers 
									WHERE 
										voucher_id = ".$vouch_arr['voucher_id']." 
									LIMIT 
										1";
				$ret_vouchmore = $db->query($sql_vouchmore);
				if ($db->num_rows($ret_vouchmore))
				{
					$row_vouchmore = $db->fetch_array($ret_vouchmore);
				}						
	
				$note		= $vouch_arr['note'];
				$activate	= $row_vouchmore['voucher_activatedon'];
				$expires	= $row_vouchmore['voucher_expireson'];
				if($activate!='0000-00-00')
				{
					$activated_on = dateFormat($activate);
				}
				else
					$activated_on = '';
				if($expires!='0000-00-00')
				{
					$expires_on = dateFormat($expires);
				}
				else
					$expires_on = '';	
				if ($row_vouchmore['voucher_paystatus_manuallychanged']==1)
				{
					$manually_changed_on = dateFormat($row_vouchmore['voucher_paystatus_manuallychanged_on']);
				}
				else
				{
					$manuall_changed_on = '';
				}
				
				// Calling function to get the customer details and sendto details of current voucher
				$det_for_mail			= get_VoucherCustomerDetailsForMail($vouch_arr['voucher_id']);
				$bought_details_str		= $det_for_mail['bought_det'];
				$sendto_details_str		= $det_for_mail['send_det'];
				$cname					= $det_for_mail['cust_name'];
				$cemail					= $det_for_mail['cust_email'];
				if($note!='')
				{
					$note_str=   ' <tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top" colspan=2>Note</td>
								 </tr>';
					$note_str .=    '<tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top" colspan=2>'.$note.'</td>
										  </tr>'	;
				}
				$search_arr	= array (
										'[name]',
										'[domain]',
										'[bought_by_details]',
										'[release_date]',
										'[voucherdate]',
										'[activated_on]',
										'[expires_on]',
										'[send_to_details]',
										'[voucher_code]',
										'[voucher_value]',
										'[total_usage]',
										'[payment_status]',
										'[note]'
									);
				$replace_arr= array(
										$cname,
										$ecom_hostname,
										$bought_details_str,
										$manually_changed_on,
										dateFormat($vouch_arr['voucher_boughton']),
										$activated_on,
										$expires_on,
										$sendto_details_str,
										$vouch_arr['voucher_number'],
										print_price_selected_currency($vouch_arr['voucher_value'],$vouch_arr['voucher_curr_rate'],$vouch_arr['voucher_curr_symbol'],true),
										$vouch_arr['voucher_max_usage'],
										getpaymentstatus_Name($row_vouchmore['voucher_paystatus']),
										$note_str
									);					
				
				// Do the replacement in email template content
				$email_content = str_replace($search_arr,$replace_arr,$email_content);
				// Building email headers to be used with the mail
				$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
				$email_headers 	.= "MIME-Version: 1.0\n";
				$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";
	
				// Saving the email to gift_voucher_emails table
				$insert_array								= array();
				$insert_array['gift_vouchers_voucher_id']	= $vouch_arr['voucher_id'];
				$insert_array['email_to']					= $cemail;
				$insert_array['email_subject']				= add_slash($email_subject);
				//$insert_array['email_message']				= add_slash($email_content,false);
				$insert_array['email_headers']				= add_slash($email_headers,false);
				$insert_array['email_type']					= 'VOUCHER_PAY_RELEASE';
				$insert_array['email_sendonce']				= ($email_disabled==0)?1:0;
				$insert_array['email_lastsenddate']			= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
				$db->insert_from_array($insert_array,'gift_voucher_emails');
				$mail_insert_id = $db->insert_id();
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('vouch',$mail_insert_id,(stripslashes($email_content)));
				if($email_disabled==0)// check whether mail sending is disabled
				{
					mail($cemail, $email_subject,$email_content, $email_headers);
				}
	
			}
		break;	
		case 'REPEAT': // Voucher payment repeate for Preauth
			// Get the content of email template
			$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
									FROM
										general_settings_site_letter_templates
									WHERE
										sites_site_id = $ecom_siteid
										AND lettertemplate_letter_type = 'VOUCHER_PAY_REPEAT'
									LIMIT
										1";
			$ret_template = $db->query($sql_template);
			if ($db->num_rows($ret_template))
			{
				$row_template 		= $db->fetch_array($ret_template);
				$email_from			= stripslashes($row_template['lettertemplate_from']);
				$email_subject		= stripslashes($row_template['lettertemplate_subject']);
				$email_content		= stripslashes($row_template['lettertemplate_contents']);
				$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);
	
				/* triggering one more query to pick the date and statu of current voucher*/
				$sql_vouchmore = "SELECT voucher_paystatus_manuallychanged,voucher_paystatus_manuallychanged_on,
										 voucher_paystatus_manuallychanged_by,voucher_paystatus,
										 voucher_activatedon,voucher_expireson 
									FROM 
										gift_vouchers 
									WHERE 
										voucher_id = ".$vouch_arr['voucher_id']." 
									LIMIT 
										1";
				$ret_vouchmore = $db->query($sql_vouchmore);
				if ($db->num_rows($ret_vouchmore))
				{
					$row_vouchmore = $db->fetch_array($ret_vouchmore);
				}						
	
				$note		= $vouch_arr['note'];
				$activate	= $row_vouchmore['voucher_activatedon'];
				$expires	= $row_vouchmore['voucher_expireson'];
				if($activate!='0000-00-00')
				{
					$activated_on = dateFormat($activate);
				}
				else
					$activated_on = '';
				if($expires!='0000-00-00')
				{
					$expires_on = dateFormat($expires);
				}
				else
					$expires_on = '';	
				if ($row_vouchmore['voucher_paystatus_manuallychanged']==1)
				{
					$manually_changed_on = dateFormat($row_vouchmore['voucher_paystatus_manuallychanged_on']);
				}
				else
				{
					$manuall_changed_on = '';
				}
				
				// Calling function to get the customer details and sendto details of current voucher
				$det_for_mail			= get_VoucherCustomerDetailsForMail($vouch_arr['voucher_id']);
				$bought_details_str		= $det_for_mail['bought_det'];
				$sendto_details_str		= $det_for_mail['send_det'];
				$cname					= $det_for_mail['cust_name'];
				$cemail					= $det_for_mail['cust_email'];
				if($note!='')
				{
					$note_str=   '<tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top" colspan=2>Note</td>
								 </tr>';
					$note_str .='<tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top" colspan=2>'.$note.'</td>
								</tr>'	;
				}
				$search_arr	= array (
										'[name]',
										'[domain]',
										'[bought_by_details]',
										'[repeat_date]',
										'[voucherdate]',
										'[activated_on]',
										'[expires_on]',
										'[send_to_details]',
										'[voucher_code]',
										'[voucher_value]',
										'[total_usage]',
										'[payment_status]',
										'[note]'
									);
				$replace_arr= array(
										$cname,
										$ecom_hostname,
										$bought_details_str,
										$manually_changed_on,
										dateFormat($vouch_arr['voucher_boughton']),
										$activated_on,
										$expires_on,
										$sendto_details_str,
										$vouch_arr['voucher_number'],
										print_price_selected_currency($vouch_arr['voucher_value'],$vouch_arr['voucher_curr_rate'],$vouch_arr['voucher_curr_symbol'],true),
										$vouch_arr['voucher_max_usage'],
										getpaymentstatus_Name($row_vouchmore['voucher_paystatus']),
										$note_str
									);					
				
				// Do the replacement in email template content
				$email_content = str_replace($search_arr,$replace_arr,$email_content);
				// Building email headers to be used with the mail
				$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
				$email_headers 	.= "MIME-Version: 1.0\n";
				$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";
	
				// Saving the email to gift_voucher_emails table
				$insert_array								= array();
				$insert_array['gift_vouchers_voucher_id']	= $vouch_arr['voucher_id'];
				$insert_array['email_to']					= $cemail;
				$insert_array['email_subject']				= add_slash($email_subject);
				//$insert_array['email_message']				= add_slash($email_content,false);
				$insert_array['email_headers']				= add_slash($email_headers,false);
				$insert_array['email_type']					= 'VOUCHER_PAY_REPEAT';
				$insert_array['email_sendonce']				= ($email_disabled==0)?1:0;
				$insert_array['email_lastsenddate']			= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
				$db->insert_from_array($insert_array,'gift_voucher_emails');
				$mail_insert_id = $db->insert_id();
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('vouch',$mail_insert_id,(stripslashes($email_content)));
				if($email_disabled==0)// check whether mail sending is disabled
				{
					mail($cemail, $email_subject,$email_content, $email_headers);
				}
			}
		break;	
		case 'REFUND': // Voucher refund
			// Get the content of email template
			$sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
									FROM
										general_settings_site_letter_templates
									WHERE
										sites_site_id = $ecom_siteid
										AND lettertemplate_letter_type = 'VOUCHER_REFUND_NOTIFICATION'
									LIMIT
										1";
			$ret_template = $db->query($sql_template);
			if ($db->num_rows($ret_template))
			{
				$row_template 		= $db->fetch_array($ret_template);
				$email_from			= stripslashes($row_template['lettertemplate_from']);
				$email_subject		= stripslashes($row_template['lettertemplate_subject']);
				$email_content		= stripslashes($row_template['lettertemplate_contents']);
				$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);
	
				/* triggering one more query to pick the date and statu of current voucher*/
				$sql_vouchmore = "SELECT voucher_paystatus_manuallychanged,voucher_paystatus_manuallychanged_on,
										 voucher_paystatus_manuallychanged_by,voucher_paystatus,
										 voucher_activatedon,voucher_expireson 
									FROM 
										gift_vouchers 
									WHERE 
										voucher_id = ".$vouch_arr['voucher_id']." 
									LIMIT 
										1";
				$ret_vouchmore = $db->query($sql_vouchmore);
				if ($db->num_rows($ret_vouchmore))
				{
					$row_vouchmore = $db->fetch_array($ret_vouchmore);
					if ($row_vouchmore['voucher_paystatus']=='Paid')
					{
						$activate	= $row_vouchmore['voucher_activatedon'];
						$expires	= $row_vouchmore['voucher_expireson'];
						if($activate!='0000-00-00')
						{
							$activated_on = dateFormat($activate);
						}
						else
							$activated_on = '';
						if($expires!='0000-00-00')
						{
							$expires_on = dateFormat($expires);
						}
						else
							$expires_on = '';	
						if ($row_vouchmore['voucher_paystatus_manuallychanged']==1)
						{
							$manually_changed_on = dateFormat($row_vouchmore['voucher_paystatus_manuallychanged_on']);
						}
						else
						{
							$manuall_changed_on = '';
						}
					}
					else 
					{
						$activated_on 		= '-';		
						$expires_on 		= '-';		
						$manuall_changed_on = '-';
					}
				}	
				$note		= $vouch_arr['note'];
				$refund_amt	= $vouch_arr['refund_amt'];
				
				// Calling function to get the customer details and sendto details of current voucher
				$det_for_mail			= get_VoucherCustomerDetailsForMail($vouch_arr['voucher_id']);
				$bought_details_str		= $det_for_mail['bought_det'];
				$sendto_details_str		= $det_for_mail['send_det'];
				$cname					= $det_for_mail['cust_name'];
				$cemail					= $det_for_mail['cust_email'];
				if($note!='')
				{
					$note_str=   ' <tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(172, 172, 172);" align="left" valign="top" colspan=2>Note</td>
								 </tr>';
					$note_str .=    '<tr>
												<td style="border-bottom: 1px solid rgb(172, 172, 172); padding: 5px 0pt; font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(91, 91, 91); font-weight: normal;" align="left" valign="top" colspan=2>'.$note.'</td>
										  </tr>'	;
				}
				$search_arr	= array (
										'[name]',
										'[domain]',
										'[bought_by_details]',
										'[refund_date]',
										'[voucherdate]',
										'[activated_on]',
										'[expires_on]',
										'[send_to_details]',
										'[voucher_code]',
										'[voucher_value]',
										'[total_usage]',
										'[payment_status]',
										'[reason]',
										'[refund_amt]'
									);
				$replace_arr= array(
										$cname,
										$ecom_hostname,
										$bought_details_str,
										$manually_changed_on,
										dateFormat($vouch_arr['voucher_boughton']),
										$activated_on,
										$expires_on,
										$sendto_details_str,
										$vouch_arr['voucher_number'],
										print_price_selected_currency($vouch_arr['voucher_value'],$vouch_arr['voucher_curr_rate'],$vouch_arr['voucher_curr_symbol'],true),
										$vouch_arr['voucher_max_usage'],
										getpaymentstatus_Name($row_vouchmore['voucher_paystatus']),
										$note_str,
										print_price_selected_currency($refund_amt,$vouch_arr['voucher_curr_rate'],$vouch_arr['voucher_curr_symbol'],true)								
									);					
				
				// Do the replacement in email template content
				$email_content = str_replace($search_arr,$replace_arr,$email_content);
				// Building email headers to be used with the mail
				$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
				$email_headers 	.= "MIME-Version: 1.0\n";
				$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";
	
				// Saving the email to gift_voucher_emails table
				$insert_array								= array();
				$insert_array['gift_vouchers_voucher_id']	= $vouch_arr['voucher_id'];
				$insert_array['email_to']					= $cemail;
				$insert_array['email_subject']				= add_slash($email_subject);
				//$insert_array['email_message']				= add_slash($email_content,false);
				$insert_array['email_headers']				= add_slash($email_headers,false);
				$insert_array['email_type']					= 'VOUCHER_REFUND_NOTIFICATION';
				$insert_array['email_sendonce']				= ($email_disabled==0)?1:0;
				$insert_array['email_lastsenddate']			= ($email_disabled==0)?'now()':'0000-00-00 00:00:00';
				$db->insert_from_array($insert_array,'gift_voucher_emails');
				$mail_insert_id = $db->insert_id();
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('vouch',$mail_insert_id,(stripslashes($email_content)));
				if($email_disabled==0)// check whether mail sending is disabled
				{
					mail($cemail, $email_subject,$email_content, $email_headers);
				}
			}
		break;
	};
}	
// function to get the bought by whom details for 
function get_VoucherCustomerDetailsForMail($voucher_id)
{
	global $db,$ecom_siteid;
	// Initializing the return array
	$ret_arr	= array ('bought_det'=>'','send_det'=>'','cust_name'=>'');
	// Get the checkout fields from general_settings_sites_checkoutfields table
	$sql_checkout = "SELECT field_key,field_name
						FROM
							general_settings_site_checkoutfields
						WHERE
							sites_site_id = $ecom_siteid
							AND field_type IN ('VOUCHER')
						ORDER BY
							field_order";
	$ret_checkout = $db->query($sql_checkout);
	if($db->num_rows($ret_checkout))
	{
		while ($row_checkout = $db->fetch_array($ret_checkout))
		{
			$chkorder_arr[$row_checkout['field_key']] = stripslashes($row_checkout['field_name']);
		}
	}
	// get the details of customer who bought this voucher and also to whom the voucher is to be send
	$sql_cust = "SELECT voucher_toname,voucher_toemail,voucher_tomessage,voucher_title,voucher_fname,
						voucher_mname,voucher_surname,voucher_buildingno,voucher_street,voucher_city,
						voucher_state,voucher_country,voucher_zip,voucher_phone,voucher_mobile,
						voucher_company,voucher_fax,voucher_email,voucher_note 
					FROM 
						gift_vouchers_customer 
					WHERE 
						voucher_id = $voucher_id 
					LIMIT 
						1";	
	$ret_cust = $db->query($sql_cust);
	if ($db->num_rows($ret_cust))
	{
		$row_cust	 = $db->fetch_array($ret_cust);
		$email_name	 = stripslashes($row_cust['voucher_title']).stripslashes($row_cust['voucher_fname']).' '.stripslashes($row_cust['voucher_mname']).' '.stripslashes($row_cust['voucher_surname']);
		$email_id	 = $row_cust['voucher_email'];
		// ##############################################################################
		$style_head = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:bold;border-bottom:1px solid  #f8f8f8;'";
	$style_desc = "style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;'";
	$style_desc_prodname ="style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#5b5b5b;font-weight:normal;border-bottom:1px solid  #f8f8f8;text-decoration:none;'";

			// Main Billing address details
			// ##############################################################################
			$bought_str		= '<table width="100%" celpadding="0" cellspacing="0" border="0">';
			$bought_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Name</td><td align="left" width="50%"'.$style_desc.'>'.$email_name.'</td></tr>';
			$bought_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_vouchercomp_name'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_company']).'</td></tr>';
			$bought_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_voucherbuilding'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_buildingno']).'</td></tr>';
			$bought_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_voucherstreet'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_street']).'</td></tr>';
			$bought_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_vouchercity'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_city']).'</td></tr>';
			$bought_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_voucherstate'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_state']).'</td></tr>';
			$bought_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_vouchercountry'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_country']).'</td></tr>';
			$bought_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_voucherzipcode'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_zip']).'</td></tr>';
			$bought_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_voucherphone'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_phone']).'</td></tr>';
			$bought_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_vouchermobile'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_mobile']).'</td></tr>';
			$bought_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_voucherfax'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_fax']).'</td></tr>';
			$bought_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_voucheremail'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_email']).'</td></tr>';
			$bought_str		.= '<tr><td align="left" width="50%" '.$style_head.'>'.$chkorder_arr['checkout_vouchernotes'] .'</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_note']).'</td></tr>';
			$bought_str		.= '</table>';

			// ##############################################################################
			// Details of person to whom the voucher details to be send
			// ##############################################################################
			$sendto_str		= '<table width="100%" celpadding="0" cellspacing="0" border="0">';
			$sendto_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Name</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_toname']).'</td></tr>';
			$sendto_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Email Id</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_toemail']).'</td></tr>';
			$sendto_str		.= '<tr><td align="left" width="50%" '.$style_head.'>Message</td><td align="left" width="50%" '.$style_desc.'>'.stripslashes($row_cust['voucher_tomessage']).'</td></tr>';
			$sendto_str		.= '</table>';
			
			$ret_arr['bought_det'] 	= $bought_str;
			$ret_arr['send_det'] 	= $sendto_str;
			$ret_arr['cust_name']	= $email_name;
			$ret_arr['cust_email']	= $email_id;	
	}
	return $ret_arr;
}
// Function which send the email which are not send from client area since the payment status was not paid while purchasing the voucher
function send_RequiredVoucherMails($voucher_id,$pstatus='')
{
	global $db,$ecom_siteid;
	// Get the activation and the expiration date and place it in the main mail contents
	$sql_det = "SELECT date_format(voucher_activatedon,'%d-%b-%Y') activatedon,date_format(voucher_expireson,'%d-%b-%Y') expireson,voucher_paystatus  
					FROM 
						gift_vouchers 
					WHERE 
						voucher_id=$voucher_id 
					LIMIT 
						1";
	$ret_det = $db->query($sql_det);
	if ($db->num_rows($ret_det))
	{
		$row_det = $db->fetch_array($ret_det);
		// Get the mail contents for vouchers to make the modifications which are not yet send
		// Check whether any mails pending for sending for current order and also when email was not disabled
		$sql_email = "SELECT email_id,email_to,email_subject,email_headers,
						email_type
						FROM
							gift_voucher_emails
						WHERE
							gift_vouchers_voucher_id =$voucher_id 
							AND email_sendonce = 0 
							AND email_type IN('VOUCHER_CONFIRMATION_CUST','VOUCHER_NOTIFICATION_ADMIN','VOUCHER_DETAILS_CUST') 
							AND email_was_disabled=0";
		$ret_email = $db->query($sql_email);
		if ($db->num_rows($ret_email))
		{
			while ($row_email = $db->fetch_array($ret_email))
			{
				//$email_content = stripslashes($row_email['email_message']);
				$email_content = read_email_from_file('vouch',$row_email['email_id']);
				if($pstatus=='')
					$pstatus_name = getpaymentstatus_Name($row_det['voucher_paystatus']);
				else 
					$pstatus_name = $pstatus;
				// Making the necessary replacement to the content of the mail
				$email_content 	= preg_replace ("/<paystat>(.*)<\/paystat>/","<paystat>".$pstatus_name."</paystat>", $email_content);
				$email_content 	= preg_replace ("/<activatedon>(.*)<\/activatedon>/","<activatedon>".$row_det['activatedon']."</activatedon>", $email_content);
				$email_content 	= preg_replace ("/<expireson>(.*)<\/expireson>/","<expireson>".$row_det['expireson']."</expireson>", $email_content);
				//Updating the changes to the content of email to gift_voucher_emails table
				/*$sql_update = "UPDATE 
									gift_voucher_emails 
								SET 
									email_message='".addslashes($email_content)."',
									email_sendonce=1,
									email_lastsenddate=now() 
								WHERE 
									email_id=".$row_email['email_id']." 
								LIMIT 
									1";*/
				$sql_update = "UPDATE 
									gift_voucher_emails 
								SET 
									email_sendonce=1,
									email_lastsenddate=now() 
								WHERE 
									email_id=".$row_email['email_id']." 
								LIMIT 
									1";					
				$db->query($sql_update);
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('vouch',$row_email['email_id'],(stripslashes($email_content)));
				if ($row_email['email_type']=='VOUCHER_NOTIFICATION_ADMIN')// if order confirmation to admin
				{
					$to_arr	= explode(",",stripslashes($row_email['email_to']));
					for($i=0;$i<count($to_arr);$i++)
					{
						mail($to_arr[$i],stripslashes($row_email['email_subject']),$email_content,stripslashes($row_email['email_headers']));
					}
				}
				else // sending mail to customer
				{
					mail(stripslashes($row_email['email_to']),stripslashes($row_email['email_subject']),$email_content,stripslashes($row_email['email_headers']));
				}			
			}
		}
	}
}			
// Function which send the email which are not send from client area since the payment status was not paid while placing the order
function send_RequiredOrderMails($order_id,$pstatus='')
{
	global $db,$ecom_siteid,$ecom_site_activate_invoice;
	// Get the activation and the expiration date and place it in the main mail contents
	$sql_det = "SELECT order_paystatus  
					FROM 
						orders  
					WHERE 
						order_id=$order_id 
					LIMIT 
						1";
	$ret_det = $db->query($sql_det);
	if ($db->num_rows($ret_det))
	{
		$row_det = $db->fetch_array($ret_det);
		// Get the mail contents for orders to make the modifications which are not yet send
		// Check whether any mails pending for sending for current order and also when email was not disabled
		$sql_email = "SELECT email_id,email_to,email_subject,email_messagepath,email_headers,
						email_type
						FROM
							order_emails
						WHERE
							orders_order_id =$order_id 
							AND email_sendonce = 0 
							AND email_type IN('ORDER_CONFIRM_CUST','ORDER_CONFIRM_ADMIN') 
							AND email_was_disabled=0";
		$ret_email = $db->query($sql_email);
		if ($db->num_rows($ret_email))
		{
			while ($row_email = $db->fetch_array($ret_email))
			{
				//$email_content = stripslashes($row_email['email_message']);
				$email_content = read_email_from_file('ord',$row_email['email_id']);
				if($pstatus=='')
					$pstatus_name = getpaymentstatus_Name($row_det['order_paystatus']);
				else 
					$pstatus_name = $pstatus;
				// Making the necessary replacement to the content of the mail
				$email_content 	= preg_replace ("/<paystat>(.*)<\/paystat>/","<paystat>".$pstatus_name."</paystat>", $email_content);
				//Updating the changes to the content of email to gift_voucher_emails table
				/*$sql_update = "UPDATE 
									order_emails 
								SET 
									email_message='".addslashes(stripslashes($email_content))."' ,
									email_sendonce=1,
									email_lastsenddate=now() 
								WHERE 
									email_id=".$row_email['email_id']." 
								LIMIT 
									1";*/
				$sql_update = "UPDATE 
									order_emails 
								SET 
									email_sendonce=1,
									email_lastsenddate=now() 
								WHERE 
									email_id=".$row_email['email_id']." 
								LIMIT 
									1";					
				$db->query($sql_update);
				/*10 Nov 2011*/
				$email_content = add_line_break($email_content);
				write_email_as_file('ord',$row_email['email_id'],(stripslashes($email_content)));								
				
				if ($row_email['email_type']=='ORDER_CONFIRM_ADMIN')// if order confirmation to admin
				{
					$to_arr	= explode(",",stripslashes($row_email['email_to']));
					for($i=0;$i<count($to_arr);$i++)
					{
						mail($to_arr[$i],stripslashes($row_email['email_subject']),$email_content,stripslashes($row_email['email_headers']));
					}
				}
				elseif($row_email['email_type']=='ORDER_CONFIRM_CUST')
				{
					$order_invoice_id = 0;
					if($ecom_site_activate_invoice==1)
					{
						$sql_inv = "SELECT invoice_id 
										FROM 
											order_invoice 
										WHERE 
											orders_order_id = $order_id 
										LIMIT 
											1";
						$ret_inv = $db->query($sql_inv);
						if ($db->num_rows($ret_inv))
						{
							$row_inv = $db->fetch_array($ret_inv);
							$order_invoice_id = $row_inv['invoice_id'];
						}					
					}
					$sql_check = "SELECT lettertemplate_disabled  
										FROM 
											general_settings_site_letter_templates  
										WHERE 
											lettertemplate_letter_type='ORDER_CONFIRM_INVOICE' 
											AND sites_site_id = $ecom_siteid 
										LIMIT 
											1";
					$ret_check = $db->query($sql_check);
					if($db->num_rows($ret_check))
					{
						$row_check = $db->fetch_array($ret_check);		
					}
					if($order_invoice_id>0 and $row_check['lettertemplate_disabled']==0) // case if got the invoice id
					{
						sendOrderMailWithAttachment($order_id,$order_invoice_id,stripslashes($row_email['email_to']),stripslashes($row_email['email_subject']),$email_content,1);
					}
					else // case if did not obtained the invoice id
					{
						mail(stripslashes($row_email['email_to']),stripslashes($row_email['email_subject']),$email_content,stripslashes($row_email['email_headers']));
					}
					
				}
				else // sending mail to customer
				{
					
					mail(stripslashes($row_email['email_to']),stripslashes($row_email['email_subject']),$email_content,stripslashes($row_email['email_headers']));
				}
			}
		}
	}
}			

/* Function to load the swf help when clicked on the "Help" link to the top of console area*/
function load_Help_Swf()
{
	global $ecom_hostname;
	$req 		= $_REQUEST['request'];
	$purpose	= $_REQUEST['fpurpose'];
	$file 		= get_help_file($req,'flash');
	if ($file!='')
	{
		$ret_arr['flash_path'] 	= 'http://'.$ecom_hostname.'/console/help_swf/index.php?f='.$file.'&host='.$ecom_hostname;
		return $ret_arr;
	}	
	else 
	{
		$ret_arr['flash_path'] 	= '';
		return  $ret_arr;
	}	
}		

function load_Help_HTML()
{
	global $ecom_hostname;
	$ftype		= '';
	$req 			= $_REQUEST['request'];
	$purpose	= $_REQUEST['fpurpose'];
	$ftype		= trim($_REQUEST['form_type']);
	//if ($ftype!='')
	//	$purpose = $ftype.'_'.$purpose;
	$file = '';
	$file = get_help_file($req,'html');
	$qrystr		= ($purpose)?'#'.$purpose:'#';
	if ($file!='')
	{
		//$ret_arr['html_path']	= 'http://'.$ecom_hostname.'/console/help_html/index.php?f='.$file.$qrystr;
		$ret_arr['html_path']	= $file.$qrystr;
		return $ret_arr;
	}	
	else 
	{
		$ret_arr['html_path']	= '';
		return  $ret_arr;
	}	
}		

function get_help_file($req,$typ)
{
	$flash_file = $html_file = '';
	switch($req)
	{
		case 'account':
			$flash_file = 'account';
			$html_file	= 'account';
		break;
		case 'console_user':
			$flash_file = 'console_user';
			$html_file 	= 'console_user';
		break;
		case 'prod_cat_group':
			$flash_file = 'prod_cat_group';
			$html_file = 'prod_cat_group';
		break;
		case 'prod_cat':
			$flash_file = 'prod_cat';
			$html_file = 'prod_cat';
		break;
		case 'products':
			//$flash_file = 'products';
			$html_file = 'products';
		break;	
		case 'img_gal':
			//$flash_file = 'img_gal';
			$html_file 	= 'img_gal';
		break;
		case 'import_export':
			//$flash_file = 'import_export';
			$html_file 	= 'import_export';
		break;
		case 'giftwrap_bows':
			//$flash_file = 'import_export';
			$html_file 	= 'giftwrap_bows';
		break;
		case 'giftwrap_cards':
			//$flash_file = 'import_export';
			$html_file 	= 'giftwrap_cards';
		break;
		case 'giftwrap_ribbons':
			//$flash_file = 'import_export';
			$html_file 	= 'giftwrap_ribbons';
		break;
		case 'giftwrap_papers':
			//$flash_file = 'import_export';
			$html_file 	= 'giftwrap_papers';
		break;
		case 'sizechart':
			//$flash_file = 'import_export';
			$html_file 	= 'sizechart';
		break;
		case 'customform':
			//$flash_file = 'import_export';
			switch ($_REQUEST['form_type'])
			{
				case 'checkout':
					//$flash_file = 'customform_checkout';
					$html_file 	= 'customform_checkout';
				break;
				case 'enquire':
					//$flash_file = 'customform_enquire';
					$html_file 	= 'customform_enquire';
				break;
				case 'register':
					//$flash_file = 'customform_register';
					$html_file 	= 'customform_register';
				break;
			};
		break;
		case 'stat_group':
			//$flash_file = 'import_export';
			$html_file 	= 'stat_group';
		break;
		case 'stat_page':
			//$flash_file = 'import_export';
			$html_file 	= 'stat_page';
		break;
		case 'comp_pos':
			//$flash_file = 'import_export';
			$html_file 	= 'comp_pos';
		break;
		case 'prod_labels':
			//$flash_file = 'import_export';
			$html_file 	= 'prod_labels';
		break;
		case 'product_stores':
			//$flash_file = 'import_export';
			$html_file 	= 'product_stores';
		break;
		case 'survey':
			//$flash_file = 'import_export';
			$html_file 	= 'survey';
		break;
		case 'product_reviews':
			//$flash_file = 'import_export';
			$html_file 	= 'product_reviews';	
		break;
		case 'site_reviews':
			//$flash_file = 'import_export';
			$html_file 	= 'site_reviews';	
		break;
		case 'combo':
			//$flash_file = 'import_export';
			$html_file 	= 'combo';	
		break;
		case 'site_headers':
			//$flash_file = 'import_export';
			$html_file 	= 'site_headers';	
		break;
		case 'featured':
			//$flash_file = 'import_export';
			$html_file 	= 'featured';	
		break;
		case 'costperclick_urls':
			//$flash_file = 'import_export';
			$html_file 	= 'costperclick_urls';	
		break;
		case 'cpc':
			//$flash_file = 'import_export';
			$html_file 	= 'cpc';	
		break;
		case 'callback':
			//$flash_file = 'import_export';
			$html_file 	= 'callback';	
		break;
		case 'cust_discount_group':
			//$flash_file = 'import_export';
			$html_file 	= 'cust_discount_group';	
		break;
		case 'newsletter_customers':
			//$flash_file = 'import_export';
			$html_file 	= 'newsletter_customers';	
		break;
		case 'faq':
			//$flash_file = 'import_export';
			$html_file 	= 'faq';	
		break;
		case 'help':
			//$flash_file = 'import_export';
			$html_file 	= 'help';	
		break;
		case 'customer_corporation':
			//$flash_file = 'import_export';
			$html_file 	= 'customer_corporation';	
		break;
		case 'customer_search':
			//$flash_file = 'import_export';
			$html_file 	= 'customer_search';	
		break;
		case 'general_settings_Gift_Wrap':
			//$flash_file = 'import_export';
			$html_file 	= 'general_settings_Gift_Wrap';	
		break;
		case 'product_enquire':
			//$flash_file = 'import_export';
			$html_file 	= 'product_enquire';	
		break;
		case 'seo_keyword':
			switch ($_REQUEST['fpurpose'])
			{
				case 'saved_keyword':
					//$flash_file = 'import_export';
					$html_file 	= 'saved_keyword';
				break;
				case 'verification_code':
					//$flash_file = 'import_export';
					$html_file 	= 'verification_code';
				break;
				case 'entire_keywords':
					//$flash_file = 'import_export';
					$html_file 	= 'entire_keywords';
				break;
				default:
					//$flash_file = 'import_export';
					$html_file 	= 'seo_keyword';
				break;	
			};
		break;
		case 'seo_title':
			//$flash_file = 'import_export';
			$html_file 	= 'seo_title';	
		break;
		case 'instock_notify':
			//$flash_file = 'import_export';
			$html_file 	= 'instock_notify';	
		break;
		case 'payonaccount':
			//$flash_file = 'import_export';
			$html_file 	= 'payonaccount';	
		break;
		case 'payonaccount_pending':
			//$flash_file = 'import_export';
			$html_file 	= 'payonaccount_pending';	
		break;
		case 'adverts':
			//$flash_file = 'import_export';
			$html_file 	= 'adverts';	
		break;
		case 'cust_group':
			//$flash_file = 'import_export';
			$html_file 	= 'cust_group';	
		break;
		case 'newsletter':
			//$flash_file = 'import_export';
			$html_file 	= 'newsletter';	
		break;
		case 'newsletter_templates':
			//$flash_file = 'import_export';
			$html_file 	= 'newsletter_templates';	
		break;
		case 'prom_code':
			//$flash_file = 'import_export';
			$html_file 	= 'prom_code';	
		break;
		case 'seo_meta_description':
			//$flash_file = 'import_export'; 
			$html_file 	= 'seo_meta_description';	
		break;
		case 'gift_voucher':
			//$flash_file = 'import_export';
			$html_file 	= 'gift_voucher';	
		break;
		case 'order_enquiries':
			//$flash_file = 'import_export';
			$html_file 	= 'order_enquiries';	
		break;
		case 'orders':
			//$flash_file = 'import_export';
			$html_file 	= 'orders';	
		break;
		case 'shelfs':
			//$flash_file = 'import_export';
			$html_file 	= 'shelfs';	
		break;
		case 'bestseller':
			//$flash_file = 'import_export';
			$html_file 	= 'bestseller';	
		break;
		case 'general_settings_currency':
			//$flash_file = 'import_export';
			$html_file 	= 'general_settings_currency';	
		break;
		case 'delivery_settings':
			//$flash_file = 'import_export';
			$html_file 	= 'delivery_settings';	
		break;
		case 'delivery_settings_more':
			//$flash_file = 'import_export';
			$html_file 	= 'delivery_settings_more';	
		break;
		case 'settings_letter_templates':
			//$flash_file = 'import_export';
			$html_file 	= 'settings_letter_templates';	
		break;
		case 'general_settings':
			switch ($_REQUEST['fpurpose'])
			{
				case 'captions':
				case 'edit_captions':
					//$flash_file = 'import_export';
					$html_file 	= 'captions';
				break;
				case 'orderconfirmemail':
					//$flash_file = 'import_export';
					$html_file 	= 'orderconfirmemail';
				break;
				case 'bonus_rate':
					//$flash_file = 'import_export';
					$html_file 	= 'bonus_rate';
				break;
				case 'settings_default':
					//$flash_file = 'import_export';
					$html_file 	= 'settings_default';
				break;
				case 'list_order':
					//$flash_file = 'import_export';
					$html_file 	= 'list_order';
				break;
				default:
					//$flash_file = 'import_export';
					$html_file 	= 'settings_letter_templates';	
				break;
			}	
		break;
		case 'image_setings':
			//$flash_file = 'import_export';
			$html_file 	= 'image_setings';	
		break;
		case 'database_offline':
			//$flash_file = 'import_export';
			$html_file 	= 'database_offline';	
		break;
		case 'payment_types':
			//$flash_file = 'import_export';
			$html_file 	= 'payment_types';	
		break;
		case 'payment_capture_types':
			//$flash_file = 'import_export';
			$html_file 	= 'payment_capture_types';	
		break;
		case 'prod_vendor':
			//$flash_file = 'import_export';
			$html_file 	= 'prod_vendor';	
		break;
		case 'shopbybrandgroup':
			//$flash_file = 'import_export';
			$html_file 	= 'shopbybrandgroup';	
		break;
		case 'shopbybrand':
			//$flash_file = 'import_export';
			$html_file 	= 'shopbybrand';	
		break;
		case 'general_settings_tax':
		case 'tax_settings':
			//$flash_file = 'import_export';
			$html_file 	= 'general_settings_tax';	
		break;
		case 'general_settings_country':
			//$flash_file = 'import_export';
			$html_file 	= 'general_settings_country';	
		break;
		case 'general_settings_comptype':
			//$flash_file = 'import_export';
			$html_file 	= 'general_settings_comptype';	
		break;
		case 'general_settings_price':
			//$flash_file = 'import_export';
			$html_file 	= 'general_settings_price';	
		break;
		case 'preorder':
			//$flash_file = 'import_export';
			$html_file 	= 'preorder';	
		break;
		case 'general_settings_state':
			//$flash_file = 'import_export';
			$html_file 	= 'general_settings_state';	
		break;
		case 'email_notify':
			//$flash_file = 'import_export';
			$html_file 	= 'email_notify';	
		break;
		case 'settings_static_checkfields':
			//$flash_file = 'import_export';
			$html_file 	= 'settings_static_checkfields';	
		break;
	};
	if($typ=='flash')
		return $flash_file;
	elseif ($typ=='html')
		 return $html_file;	
}

function get_order_status_text_to_number($text)
{
	switch($text)
	{
		case 'ONHOLD':
			return 3;
		break;
		case 'BACK':
			return 4;
		break;
		case 'CANCELLED':
			return 5;
		break;
	};
}
function get_order_status_number_to_text($no)
{
	$cap = '';;
	switch($no)
	{
		case 1:
			$cap =  'Payment Received';
		break;
		case 2:
			$cap = 'Payment Failed';
		break;	
		case 3:
			$cap = 'On Hold';
		break;
		case 4:
			$cap = 'Back order';
		break;
		case 5:
			$cap =  'Cancelled';
		break;
		case 6:
			$cap = 'Despatched';
		break;
		case 7:
			$cap = 'Refunded';
		break;
		case 8:
			$cap = 'Release Deferred';
		break;
		case 9:
			$cap = 'Aborted Deferred';
		break;
		case 10:
			$cap = 'Repeat Preauth';
		break;
		case 11:
			$cap = 'Authorise Authenticate';
		break;
		case 12:
			$cap = 'Cancel Authenticate';
		break;
		case 13:
			$cap = 'Placed on Account';
		break;
		case 14:
			$cap = 'Decline';
		break;
	};
	if($cap!='')
		$cap = '<strong>('.$cap.')</strong>';
	return $cap;
}
function get_voucher_status_number_to_text($no)
{
	$cap = '';;
	switch($no)
	{
		case 1:
			$cap =  'Payment Received';
		break;
		case 2:
			$cap = 'Payment Failed';
		break;	
		case 3:
			$cap = 'Refunded';
		break;
		case 4:
			$cap = 'Release Deferred';
		break;
		case 5:
			$cap = 'Aborted Deferred';
		break;
		case 6:
			$cap = 'Repeat Preauth';
		break;
		case 7:
			$cap = 'Authorise Authenticate';
		break;
		case 8:
			$cap = 'Cancel Authenticate';
		break;
	};
	if($cap!='')
		$cap = '<strong>('.$cap.')</strong>';
	return $cap;
}

function Build_main_delivery_dropdown($opt='',$html='')
{
	if ($opt!='')
	{
	?> 
			<option value="<?php echo $opt?>" selected="selected"><?php echo $opt?></option>		
	<?php	
	}
	echo $html;
}
function Build_main_delivery_dropdown_html()
{
	$var = '';
	$get_Set = get_general_settings('delivery_settings_common_min,delivery_settings_common_max,delivery_settings_common_increment');
	for($i=$get_Set['delivery_settings_common_min'];$i<=$get_Set['delivery_settings_common_max'];$i+=$get_Set['delivery_settings_common_increment'])
	{	
		$var .='<option value="'.$i.'">'.$i.'</option>	';
	}
	return $var;
}
function Build_sub_delivery_dropdown($opt='',$html='')
{
	if ($opt!='')
	{
	?>
		<option value="<?php echo $opt?>" selected="selected"><?php echo $opt?></option>		
	<?php	
	}
	echo $html;		
}
function Build_sub_delivery_dropdown_html()
{
	$get_Set = get_general_settings('delivery_settings_weight_min_limit,delivery_settings_weight_max_limit,delivery_settings_weight_increment');
	for($i=$get_Set['delivery_settings_weight_min_limit'];$i<=$get_Set['delivery_settings_weight_max_limit'];$i+=$get_Set['delivery_settings_weight_increment'])
	{
		if($i<10)  
		{
			$test = "0".$i;
		}
		else
		{
			$test= $i;
		}
		$var .='<option value="'.$i.'">'.$i.'</option>	';
	}					
	return $var;
}
	function newsletter_tabs($curtab,$newsletter_id,$custom='',$newsletter_name='') 
	{
?>        
   <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabmenu_x"> 
	<tr>  
			<td  align="left" onClick="window.location='home.php?request=newsletter&fpurpose=edit&newsletter_id=<?=$newsletter_id?>'" class="<?php if($curtab=='main_tab_td') echo "toptab_sel"; else echo "toptab"?>" id="main_tab_td"><span>Main Info</span></td>
			<td  align="left" onClick="window.location='home.php?request=newsletter&fpurpose=prodnewsletter&newsletter_id=<?=$newsletter_id?>'" class="<?php if($curtab=='prods_tab_td') echo "toptab_sel"; else echo "toptab"?>" id="prods_tab_td"> <span>Assigned Products </span> </td>
			<td  align="left" onClick="window.location='home.php?request=newsletter&fpurpose=preview&newsletter_id=<?=$newsletter_id?>'" class="<?php if($curtab=='preview_tab_td') echo "toptab_sel"; else echo "toptab"?>" id="preview_tab_td"> <span>Preview Newsletter</span> </td>
			<td  align="left" onClick="window.location='home.php?request=newsletter&fpurpose=listnewsgroups&newsletter_id=<?=$newsletter_id?>'" class="<?php if($curtab=='customer_tab_td') echo "toptab_sel"; else echo "toptab"?>" id="customer_tab_td"> <span>List Customer Types</span> </td>			
			<?PHP
			if($custom)
			{
			?>
			<td  align="left" class="<?php if($curtab=='custom_tab_td') echo "toptab_sel"; else echo "toptab"?>" id="custom_tab_td"><span> List Customers to send the news letter: "<b><?=$newsletter_name?></b>"</span> </td>			
			<? } ?>
			<td width="90%" align="left">&nbsp;</td>  
	</tr></table>
<?PHP
	}
function notification_tabs($curtab,$news_id) 
	{
?>        
   <table width="100%" border="0" cellspacing="1" cellpadding="1"> 
	<tr>  
			<td  align="left" onClick="window.location='home.php?request=email_notify&fpurpose=edit&fmode=edit&newsletter_id=<?=$news_id?>'" class="<?php if($curtab=='main_tab_td') echo "toptab_sel"; else echo "toptab"?>" id="main_tab_td"><span>Main Info</span></td>
			<td  align="left" onClick="window.location='home.php?request=email_notify&fpurpose=edit_notify_settings&fmode=edit&newsletter_id=<?=$news_id?>'" class="<?php if($curtab=='settings_tab_td') echo "toptab_sel"; else echo "toptab"?>" id="settings_tab_td"> <span>Notification Settings</span> </td>
			<td  align="left" onClick="window.location='home.php?request=email_notify&fpurpose=preview_email&fmode=edit&newsletter_id=<?=$news_id?>'" class="<?php if($curtab=='preview_tab_td') echo "toptab_sel"; else echo "toptab"?>" id="preview_tab_td"> <span>Preview Notification</span> </td>			
			
			<td width="90%" align="left">&nbsp;</td>  
	</tr></table>
<?PHP
	}
	
	// Function to do the return stock operation in case of refunding
	function do_Refund_Stock_Return($row_check)
	{
		global $db,$ecom_siteid,$prodext_arr;
		// Check whether the product is in fixed or variable stock
		$sql_prodcheck = "SELECT product_variablestock_allowed,product_preorder_allowed,product_total_preorder_allowed 
									FROM
										products
									WHERE
										product_id = ".$row_check['products_product_id']."
									LIMIT
										1";
		$ret_prodcheck = $db->query($sql_prodcheck);
		if ($db->num_rows($ret_prodcheck))
		{
			$row_prodcheck = $db->fetch_array($ret_prodcheck);
			if ($row_prodcheck['product_preorder_allowed']=='N' and $row_check['order_preorder']=='N') // return the stock only if the product was not is preorder at the time of ordering and also at present
			{
				// Check whether direct stock or combination stock
				if ($row_check['order_stock_combination_id']==0)// fixed stock
				{
					// Check whether the product is still in fixed stock
					if($row_prodcheck['product_variablestock_allowed']=='N')
					{
						// Increment the web stock and actual stock field for the current product by qty in order
						// and also making the preorder to N. This is done to handle the case of curreent product is placed in preorder
						$sql_update = "UPDATE products
												SET
													product_webstock 	= product_webstock + ".$row_check['order_qty']." ,
													product_actualstock = product_actualstock + ".$row_check['order_qty']." ,
													product_preorder_allowed = 'N',product_total_preorder_allowed=0,
													product_instock_date='0000-00-00'
												WHERE
													product_id = ".$row_check['products_product_id']."
														LIMIT
													1";
						$db->query($sql_update);
					}
				}
				else // case of variable stock
				{
					// Check whether the product is still in variable stock
					if($row_prodcheck['product_variablestock_allowed']=='Y')
					{
						// Check whether the combination still exists
						$sql_check = 'SELECT comb_id
												FROM
													product_variable_combination_stock
												WHERE
													comb_id = '.$row_check['order_stock_combination_id']."
													AND products_product_id = ".$row_check['products_product_id']."
												LIMIT
													1";
						$ret_check = $db->query($sql_check);
						if ($db->num_rows($ret_check)) // case if combination already exists
						{
							$sql_update = "UPDATE product_variable_combination_stock
													SET
														web_stock = web_stock + ".$row_check['order_qty'].",
														actual_stock = actual_stock + ".$row_check['order_qty']."
													WHERE
														comb_id = ".$row_check['order_stock_combination_id']."
														AND products_product_id = ".$row_check['products_product_id']."
													LIMIT
														1";
							$db->query($sql_update);
	
							//Updating the products table in the fields product_preorder_allowed to make it to 'N'
							$sql_upd = "UPDATE products
												SET
													product_preorder_allowed ='N',product_total_preorder_allowed=0,
													product_instock_date='0000-00-00'
												WHERE
													product_id = ".$row_check['products_product_id']."
												LIMIT
													1";
							$db->query($sql_upd);
						}
					}
				}
			}
			elseif($row_prodcheck['product_preorder_allowed']=='Y' and $row_check['order_preorder']=='Y')// case product was in preorder at the time of ordering also is in preorder at present
			{
				if(!in_array($row_check['products_product_id'],$prodext_arr))// This is done to handle the case to decrement the total preorder value only once even if the product exists in cart more than once
				{
					$update_sql = "UPDATE 
										products 
									SET 
										product_total_preorder_allowed = product_total_preorder_allowed + 1 
									WHERE 
										product_total_preorder_allowed > 0 
										AND product_id = ".$row_check['products_product_id']." 
										AND sites_site_id = $ecom_siteid  
									LIMIT 
										1";
					$db->query($update_sql);
					$prodext_arr[] = $row_check['products_product_id'];
				}	
			}
		}
	}
	
function send_Stock_Notification($product_id)	
{
	global $db,$ecom_siteid,$ecom_hostname;
	
	if(check_IndividualSslActive())
	{
		$http = 'https://';
	}
	else
	{
		$http = 'http://';
	}
	
	// Check whether there exists any entry for current product id in product_stock_update_notification for current site
	$sql_check = "SELECT notify_id,cust_email,product_stock_comb_id,products_product_id  
							FROM 
								product_stock_update_notification 
							WHERE 
								products_product_id=$product_id 
								AND sites_site_id = $ecom_siteid";
	$ret_check = $db->query($sql_check);
	if ($db->num_rows($ret_check))
	{
		// Fetching the template set for instock notification
		$tempsql 	 = "SELECT lettertemplate_contents, lettertemplate_subject, lettertemplate_from 
								FROM 
									general_settings_site_letter_templates
								WHERE 
									lettertemplate_letter_type='CUSTOMER_INSTOCK_NOTIFICATION' 
									AND sites_site_id='".$ecom_siteid."' 
									AND  lettertemplate_disabled='0'
								LIMIT 
									1";
		$tempres 	= $db->query($tempsql);
		if ($db->num_rows($tempres)==0)
		{
			return ; // dont do anything down since email is disabled
		}
		else
		{		
			$temprow					= $db->fetch_array($tempres);
			$tempcontent			= 	$temprow['lettertemplate_contents'];	
			$tempsubject 			= 	$temprow['lettertemplate_subject'];	
			$lettertemplate_from 	= 	$temprow['lettertemplate_from'];	
		}		
		// Building the email header
		$headers 	= "From: Instock Notification <".$temprow['lettertemplate_from'].">\n";
		$headers  	.= "MIME-Version: 1.0\n";
		$headers	.= "Content-type: text/html; charset=iso-8859-1\n";
		
						
		// Set the template to be used for product list	
		 $productTemplate = "
										<table style='width:655px;' border='0' cellspacing='0' cellpadding='0'>
										<tr>
											<td style='padding:2px 8px;' align='left' valign='top' width='114' height='108'>[IMG]</td>
											<td  style='padding:2px 8px;' align='left' valign='top'>
											<div style='padding:5px 0;font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#796348;font-weight:bold'>[TITLE]</div>
											<div style='padding:2px 0;font-family:Arial, Helvetica, sans-serif;font-size:11px;color:#616160;font-weight:normal'>[DESCRIPTION]</div>
											<div style='padding:2px 0;font-family:Arial, Helvetica, sans-serif;font-size:14px;color:#ca5c5a;font-weight:bold'>Retail Price: [PRICE]</div>
											</td>
										</tr>
										</table>";
		while($row_check = $db->fetch_array($ret_check))
		{
			$pid 		= $row_check['products_product_id'];
			$cid		= $row_check['product_stock_comb_id'];
			$nid		= $row_check['notify_id'];
			$email	= stripslashes($row_check['cust_email']);
			$stk		= 0;
			if ($cid>0) // case if combination id exists. so check whether stock exists for current combination
			{
				$sql_stk_chk = "SELECT web_stock  as stk 
											FROM 
												product_variable_combination_stock 
											WHERE 
												comb_id=$cid
												AND web_stock>0 
												AND products_product_id = $pid 
											LIMIT 
												1";
				$ret_stk_chk = $db->query($sql_stk_chk);
				if ($db->num_rows($ret_stk_chk)==0)// if stock does not exists then do go down
				{
					// Check whether current product is still in variable stock
					$sql_chk = "SELECT product_variablestock_allowed 
											FROM 
												products  
											WHERE 
												product_id = $pid 
												AND sites_site_id = $ecom_siteid 
												AND product_variablestock_allowed = 'Y'  
											LIMIT 
												1";
					$ret_chk = $db->query($sql_chk);
					if ($db->num_rows($ret_chk)==0)
					{
						// Delete current notification as it is not valid now
						
						$sql_del = "DELETE FROM 
											product_stock_update_notification_messages 
										WHERE 
											product_stock_update_notification_notify_id = $nid ";
						$db->query($sql_del); 
						
						$sql_del = "DELETE FROM 
											product_stock_update_notification_variables 
										WHERE 
											product_stock_update_notification_notify_id = $nid ";
						$db->query($sql_del); 
						
						
						$sql_del = "DELETE FROM 
											product_stock_update_notification 
										WHERE 
											notify_id = $nid 
										LIMIT 
											1";
						$db->query($sql_del); 
												
					}
					continue ;  // skipping what ever below
				}	
			}
			else // case if combo_id is not set for the current entry in stock notification table, so look for fixed stock
			{
			 	$sql_stk_chk = "SELECT product_webstock  as stk,product_variablestock_allowed as var_allow 
											FROM 
												products  
											WHERE 
												product_id = $pid 
												AND sites_site_id=$ecom_siteid 
											LIMIT 
												1";
				$ret_stk_chk = $db->query($sql_stk_chk);
				if ($db->num_rows($ret_stk_chk))// if stock does not exists then do go down
				{
					$row_stk_chk = $db->fetch_array($ret_stk_chk);
					if($row_stk_chk['var_allow']=='Y') // Check whether this product is currently in variable stock. If yes then delete the notification entry as it is no more valid
					{
						$sql_del = "DELETE FROM 
											product_stock_update_notification_messages 
										WHERE 
											product_stock_update_notification_notify_id = $nid ";
						$db->query($sql_del); 
						
						$sql_del = "DELETE FROM 
											product_stock_update_notification_variables 
										WHERE 
											product_stock_update_notification_notify_id = $nid ";
						$db->query($sql_del); 
						
						
						$sql_del = "DELETE FROM 
											product_stock_update_notification 
										WHERE 
											notify_id = $nid 
										LIMIT 
											1";
						$db->query($sql_del); 
						continue ; // skipping what ever below
					}
					elseif($row_stk_chk['stk']==0) // if not in variable stock but stock in 0 now so skip all section below and continue at the top
					{
						continue ; // skipping what ever below
					}
				}
			}
			// Get the details of first image set for current product, if any		
			$sql_img     = "SELECT  image_thumbpath 
									FROM 
										images a, images_product b 
									WHERE a.image_id=b.images_image_id 
										   AND b.products_product_id = '".$pid."' 
											AND a.sites_site_id = '".$ecom_siteid."'
									ORDER BY 
										b.image_order ASC 
									LIMIT 
										1";
			$res_img      = $db->query($sql_img);
			if ($db->num_rows($res_img))
			{
				$imagrow     = $db->fetch_array($res_img);
				$images       = $imagrow['image_thumbpath'];
			}	
			// Get the details of current product from products table 
			$prodnamesql 	= "SELECT product_name, product_shortdesc, product_webprice, product_discount, 
												product_discount_enteredasval, product_bulkdiscount_allowed 
										FROM 
											products 
										WHERE 
											product_id='".$pid."' 
										LIMIT 
											1";
			$prodnameres 	= $db->query($prodnamesql);
			if ($db->num_rows($prodnameres))
				$prodnamerow	 = $db->fetch_array($prodnameres);
			
			// Calcilating the discount value	
			if($prodnamerow['product_discount']>0)
			{
				switch($prodnamerow['product_discount_enteredasval']) 
				{
					case '0' :
						$rate =  $prodnamerow['product_webprice'] - ($prodnamerow['product_webprice']*$prodnamerow['product_discount']/100);
					break;
					case '1' :
						$rate =  $prodnamerow['product_webprice'] - $prodnamerow['product_discount'];
					break;
					case '2' :
						$rate =  $prodnamerow['product_discount'];		
					break;
					default :
						$rate = $prodnamerow['product_webprice'];
				}
			}
			else
			$rate = $prodnamerow['product_webprice'];	
			// Set the image  tag to be replaced in the product list template		
			if(trim($images))  // case if images assigned for current product
			{
				$imgname = "<a href=\"".$http.$ecom_hostname."/".strip_url($prodnamerow['product_name'])."-p".$pid.".html\"><img src='".$http.$ecom_hostname."/images/".$ecom_hostname."/".$images."' border='0'/></a>";					 
			}
			else // case if image is not assigned for current product
			{ 
				$imgname = "<a href=\"".$http.$ecom_hostname."/".strip_url($prodnamerow['product_name'])."-p".$pid.".html\"><img src='".$http.$ecom_hostname."/images/".$ecom_hostname."/site_images/no_small_image.gif' border='0'/></a>";
			}
			
			$productTemplate 	=	str_replace('[IMG]',$imgname,$productTemplate);
			$prodname 	   		=    $prodnamerow['product_name'];
			$prodshortdesc 		=    $prodnamerow['product_shortdesc'];
			$rate 	   	   			=    display_price($rate);
			
			// Get the variables messages linked with current notification, if any
			$var_message 		= "SELECT message_caption, message_value 
												FROM 
													product_stock_update_notification_messages 
												WHERE 
													product_stock_update_notification_notify_id=".$nid;	
			$var_messag_res 	= $db->query($var_message);	
			while($var_msg_row = $db->fetch_array($var_messag_res))
			{
				if(trim($var_msg_row['message_caption']) && trim($var_msg_row['message_value']))
				{
					$messagdetail .= $var_msg_row['message_caption']." : ".$var_msg_row['message_value']."<br/>";
				}
			}
			// Get the variable messages linked with current notification, if any 	
			$var_variable = "SELECT var_name, var_value 
										FROM 
											product_stock_update_notification_variables 
										WHERE 
											product_stock_update_notification_notify_id=".$nid	;
			$var_variable_res = $db->query($var_variable);	
			while($var_variable_row = $db->fetch_array($var_variable_res)) 
			{
				if(trim($var_variable_row['var_name']) && trim($var_variable_row['var_value'])) 
				{
					$variabledetail .= $var_variable_row['var_name']." : ".$var_variable_row['var_value']."<br/>";
				}
			}		
										
			$prodname = $prodname."<br/>".$variabledetail.$messagdetail;
			$productTemplate 		=	str_replace('[TITLE]',$prodname,$productTemplate);
			$productTemplate 		=	str_replace('[DESCRIPTION]',$prodshortdesc,$productTemplate);
			$productTemplate 		=	str_replace('[PRICE]',$rate,$productTemplate);
			$productlayoutdesign  	=  $productTemplate;
		
			$tempcontent 			=	str_replace('[product_list]',$productlayoutdesign,$tempcontent);
			$tempcontent 			=	str_replace('[domain]',$ecom_hostname,$tempcontent);
			$tempcontent 			=	str_replace('[date]',date("d-M-Y"),$tempcontent);
			
			$tempsubject = $tempsubject;
			//echo "To".$email."<br><br>".$tempcontent;
			mail($email,$tempsubject,$tempcontent,$headers);
			// Delete the current notification entry from product_stock_update_notification table
			
			$sql_del = "DELETE FROM 
									product_stock_update_notification_messages 
								WHERE 
									product_stock_update_notification_notify_id = $nid ";
			$db->query($sql_del); 
			
			$sql_del = "DELETE FROM 
								product_stock_update_notification_variables 
							WHERE 
								product_stock_update_notification_notify_id = $nid ";
			$db->query($sql_del); 
			$sql_del = "DELETE FROM 
								product_stock_update_notification 
							WHERE 
								notify_id = $nid 
							LIMIT 
								1";
			$db->query($sql_del);
		}
	}							
}
// Function to send the payonaccont payment approval mail while pending transactions have been marked as approved.
function send_PayonAccountApproval($pay_id)
{
	global $db,$ecom_siteid,$ecom_hostname;
	// Get the content of email template
	 $sql_template = "SELECT lettertemplate_from,lettertemplate_subject,lettertemplate_contents,lettertemplate_disabled
							FROM
								general_settings_site_letter_templates
							WHERE
								sites_site_id = $ecom_siteid
								AND lettertemplate_letter_type = 'PAY_ON_ACCOUNT_PAYMENT_APPROVAL'
							LIMIT
								1";
	$ret_template = $db->query($sql_template);
	if ($db->num_rows($ret_template))
	{
		$row_template 		= $db->fetch_array($ret_template);
		$email_from			= stripslashes($row_template['lettertemplate_from']);
		$email_subject		= stripslashes($row_template['lettertemplate_subject']);
		$email_content		= stripslashes($row_template['lettertemplate_contents']);
		$email_disabled		= stripslashes($row_template['lettertemplate_disabled']);
		if($email_disabled==0)// check whether mail sending is disabled
		{
			// Get the payment details from order_payonaccount_details 
			$sql_pay = "SELECT DATE_FORMAT(pay_date,'%d-%b-%Y') pdate,pay_amount,pay_paystatus,pay_paymenttype,pay_paymentmethod ,pay_curr_rate, pay_curr_symbol,customers_customer_id  
									FROM 
										order_payonaccount_details 
									WHERE 
										pay_id = $pay_id 
										AND sites_site_id = $ecom_siteid 
									LIMIT 
										1";
			$ret_pay = $db->query($sql_pay);
			if ($db->num_rows($ret_pay))
			{
				$row_pay = $db->fetch_array($ret_pay);
				// Get the details of customer
				$sql_cust = "SELECT customer_title,customer_fname,customer_surname,customer_email_7503  
										FROM 
											customers 
										WHERE 
											customer_id = ".$row_pay['customers_customer_id']." 
											AND sites_site_id = $ecom_siteid 
										LIMIT 
											1";
				$ret_cust = $db->query($sql_cust);
				if ($db->num_rows($ret_cust))
				{
					$row_cust = $db->fetch_array($ret_cust);
				}	
			}
			else // case if invalid details
				return false;
		
			$cname					= stripslashes($row_cust['customer_title']).stripslashes($row_cust['customer_fname'])." ".stripslashes($row_cust['customer_surname']);
			// Check whether currency rate and currency symbol is there with current transactions
			if ($row_pay['pay_curr_rate']!='' and $row_pay['pay_curr_symbol']!='')
			{
				$price = print_price_selected_currency($row_pay['pay_amount'],$row_pay['pay_curr_rate'],$row_pay['pay_curr_symbol'],true);
			}
			else
			{
				// get the default currency symbol 
				$sql_curr = "SELECT currency_id,curr_sign_char 
									FROM 
										general_settings_site_currency 
									WHERE 
										sites_site_id=$ecom_siteid 
										AND curr_default=1 ";
				$res_curr = $db->query($sql_curr);
				if ($db->num_rows($res_curr))
				{
					$row_curr 	= $db->fetch_array($res_curr);
					$price = print_price_selected_currency($row_pay['pay_amount'],1,$row_curr['curr_sign_char'],true);
				}
			}
			$search_arr	= array (
										'[name]',
										'[domain]',
										'[date]',
										'[amount]'
								);
			$replace_arr= array(
										$cname,
										$ecom_hostname,
										$row_pay['pdate'],
										$price
								);
			// Do the replacement in email template content
			$email_content = str_replace($search_arr,$replace_arr,$email_content);
			// Building email headers to be used with the mail
			$email_headers 	= "From: $ecom_hostname	<$email_from>\n";
			$email_headers 	.= "MIME-Version: 1.0\n";
			$email_headers 	.= "Content-type: text/html; charset=iso-8859-1\n";
			mail($row_cust['customer_email_7503'], $email_subject,$email_content, $email_headers); 
		}
	}
}

function server_check($tablename, $wherecondition) 
{
	global $db,$ecom_siteid,$ecom_hostname;
	$sql = "SELECT count(*) AS cnt FROM ".$tablename." WHERE ".$wherecondition." AND sites_site_id='".$ecom_siteid."'"; 
	$res = $db->query($sql);
	$row = $db->fetch_array($res);
	if($row['cnt']==0) return 0;
	else return 1;
}

/* Function to create cache settings files when ever a setting is saved in general settings section in console*/
function create_GeneralSettings_CacheFile()
{
	global $db,$ecom_siteid,$image_path;
	$file_path = $image_path .'/settings_cache';
	if(!file_exists($file_path))
		mkdir($file_path);
	$file_name = $file_path.'/general_settings.php';
	// Open the file in write mod 
	$fp = fopen($file_name,'w');
	fwrite($fp,'<?php'."\n");
	// get the details from general_settings_sites_common
	$sql_common = "SELECT * 
								FROM 
									general_settings_sites_common 
								WHERE 
									sites_site_id=$ecom_siteid 
								LIMIT 
									1";
	$ret_common = $db->query($sql_common);
	if ($db->num_rows($ret_common))
	{
		$row_common = $db->fetch_assoc($ret_common);
		$check_array = array('voucher_buy_text','voucher_spend_text','pricepromise_topcontent','pricepromise_bottomcontent',
								'product_freedelivery_content','bonus_point_details_content','payon_account_details_content',
								'general_download_topcontent','general_shopsall_topcontent','general_shopsall_bottomcontent',
								'general_comboall_topcontent','general_comboall_bottomcontent','general_savedsearch_topcontent',
								'general_savedsearch_bottomcontent','general_pricepromise_addtocart'
							);
		foreach ($row_common as $k=>$v)
		{
			if ($k!=='listing_id' and $k!=='sites_site_id' and !in_array($k,$check_array))
				fwrite($fp,'$Settings_arr["'.$k.'"] = "'. addslashes(stripslashes($v)).'";'."\n");
		}	
	}
	
	// get the details from general_settings_sites_common_onoff
	$sql_common = "SELECT * 
							FROM 
								general_settings_sites_common_onoff 
							WHERE 
								sites_site_id=$ecom_siteid 
							LIMIT 
								1";
	$ret_common = $db->query($sql_common);
	if ($db->num_rows($ret_common))
	{
		$row_common = $db->fetch_assoc($ret_common);
		foreach ($row_common as $k=>$v)
		{
			if ($k!=='listing_id' and $k!=='sites_site_id')
				fwrite($fp,'$Settings_arr["'.$k.'"] = "'. addslashes(stripslashes($v)).'";'."\n");
		}	
	}
	
	// get the details from general_settings_sites_listorders
	$sql_common = "SELECT * 
								FROM 
									general_settings_sites_listorders 
								WHERE 
									sites_site_id=$ecom_siteid 
								LIMIT 
									1";
			$ret_common = $db->query($sql_common);
			if ($db->num_rows($ret_common))
			{
				$row_common = $db->fetch_assoc($ret_common);
				foreach ($row_common as $k=>$v)
				{
					if ($k!=='listing_id' and $k!=='sites_site_id')
						fwrite($fp,'$Settings_arr["'.$k.'"] = "'. addslashes(stripslashes($v)).'";'."\n");
				}	
			}
	fwrite($fp,'?>');		
	fclose($fp);
}

/*  Function to create the price displaly settings when ever a price display settings is changed from console area */
function create_PriceDisplaySettings_CacheFile()
{

	global $db,$ecom_siteid,$image_path;
	$file_path = $image_path .'/settings_cache';
	if(!file_exists($file_path))
		mkdir($file_path);
	$file_name = $file_path.'/price_display_settings.php';
	// Open the file in write mod 
	$fp = fopen($file_name,'w');
	fwrite($fp,'<?php'."\n");
	// get the details from general_settings_site_pricedisplay
	$sql_price = "SELECT * 
							FROM 
								general_settings_site_pricedisplay 
							WHERE 
								sites_site_id = $ecom_siteid 
							LIMIT 
								1";
	$ret_price = $db->query($sql_price);
	if ($db->num_rows($ret_price))
	{
		$row_price = $db->fetch_assoc($ret_price);
		foreach($row_price as $k=>$v)
		{	
			if ($k!=='price_id' and $k!=='sites_site_id')
				fwrite($fp,'$PriceSettings_arr["'.$k.'"] = "'. addslashes(stripslashes($v)).'";'."\n");
		}	
	}
	fwrite($fp,'?>');		
	fclose($fp);	
}

/*  Function to create the price displaly settings when ever a price display settings is changed from console area */
function create_Captions_CacheFile($section_id)
{
	global $db,$ecom_siteid,$image_path;
	
	// Get the name of section 
	$sql_section = "SELECT section_code 
							FROM 
								general_settings_section 
							WHERE 
								section_id = $section_id 
							LIMIT 
								1";
	$ret_section = $db->query($sql_section);
	if($db->num_rows($ret_section))
	{
		$row_section 		= $db->fetch_array($ret_section);
		$section_code	=  strtolower($row_section['section_code']);	
	}
	if(!file_exists($image_path .'/settings_cache'))
		mkdir($image_path .'/settings_cache');
	$file_path = $image_path .'/settings_cache/settings_captions';
	if(!file_exists($file_path))
		mkdir($file_path);
	$file_name = $file_path.'/'.$section_code.'.php';
	// Open the file in write mod 
	$fp = fopen($file_name,'w');
	fwrite($fp,'<?php'."\n");
	// get the details from general_settings_site_pricedisplay
	$sql_cap = "SELECT general_key,general_text 
							FROM 
								general_settings_site_captions  
							WHERE 
								sites_site_id = $ecom_siteid 
								AND general_settings_section_section_id = $section_id 
							";
	$ret_cap = $db->query($sql_cap);
	if ($db->num_rows($ret_cap))
	{
		while($row_cap = $db->fetch_array($ret_cap))
		{	
			fwrite($fp,'$Cache_captions_arr["'.$row_cap['general_key'].'"] = "'. addslashes(stripslashes($row_cap['general_text'])).'";'."\n");
		}	
	}
	fwrite($fp,'?>');		
	fclose($fp);	
}
/*  Function to create the price displaly settings when ever a price display settings is changed from console area for "ALL Sections" */
function create_Captions_CacheFile_All()
{
	global $db,$ecom_siteid,$image_path;
	
	// Get the name of section 
	$sql_section = "SELECT section_id,section_code 
							FROM 
								general_settings_section ";
	$ret_section = $db->query($sql_section);
	if($db->num_rows($ret_section))
	{
		while($row_section 	= $db->fetch_array($ret_section))
		{
			$section_code	=  strtolower($row_section['section_code']);	
			$section_id			= $row_section['section_id'];
			if(!file_exists($image_path .'/settings_cache'))
				mkdir($image_path .'/settings_cache');
			$file_path = $image_path .'/settings_cache/settings_captions';
			if(!file_exists($file_path))
				mkdir($file_path);
			$file_name = $file_path.'/'.$section_code.'.php';
			// Open the file in write mod 
			$fp = fopen($file_name,'w');
			fwrite($fp,'<?php'."\n");
			// get the details from general_settings_site_pricedisplay
			$sql_cap = "SELECT general_key,general_text 
									FROM 
										general_settings_site_captions  
									WHERE 
										sites_site_id = $ecom_siteid 
										AND general_settings_section_section_id = $section_id 
									";
			$ret_cap = $db->query($sql_cap);
			if ($db->num_rows($ret_cap))
			{
				while($row_cap = $db->fetch_array($ret_cap))
				{	
					fwrite($fp,'$Cache_captions_arr["'.$row_cap['general_key'].'"] = "'. addslashes(stripslashes($row_cap['general_text'])).'";'."\n");
				}	
			}
			fwrite($fp,'?>');		
			fclose($fp);	
		}
	}		
}

/*  Function to create the site menu and mod menu cache */
function create_Currency_CacheFile()
{

	global $db,$ecom_siteid,$image_path;
	$file_path = $image_path .'/settings_cache';
	if(!file_exists($file_path))
		mkdir($file_path);
		
	// Case of currencies
	$file_name = $file_path.'/currency.php';
	// Open the file in write mod 
	$fp = fopen($file_name,'w');
	fwrite($fp,'<?php'."\n");
	// get the details from general_settings_site_currency
	$sql_curr = "SELECT currency_id, curr_name, curr_sign, curr_sign_char, curr_code, curr_rate, curr_margin, curr_default, curr_numeric_code 
					FROM 
						general_settings_site_currency  
					WHERE 
						sites_site_id = $ecom_siteid 
					ORDER BY 
						curr_default DESC";
	$ret_curr = $db->query($sql_curr);
	if($db->num_rows($ret_curr))
	{
		while ($row_curr = $db->fetch_array($ret_curr))
		{
			if($row_curr['curr_default']==1)
			{
				fwrite($fp,'$default_curr[\'currency_id\'] = "'. addslashes(stripslashes($row_curr['currency_id'])).'";'."\n");
				fwrite($fp,'$default_curr[\'curr_name\'] = "'. addslashes(stripslashes($row_curr['curr_name'])).'";'."\n");
				fwrite($fp,'$default_curr[\'curr_sign\'] = "'. addslashes(stripslashes($row_curr['curr_sign'])).'";'."\n");
				fwrite($fp,'$default_curr[\'curr_sign_char\'] = "'. addslashes(stripslashes($row_curr['curr_sign_char'])).'";'."\n");
				fwrite($fp,'$default_curr[\'curr_code\'] = "'. addslashes(stripslashes($row_curr['curr_code'])).'";'."\n");
				fwrite($fp,'$default_curr[\'curr_rate\'] = "'. addslashes(stripslashes($row_curr['curr_rate'])).'";'."\n");
				fwrite($fp,'$default_curr[\'curr_margin\'] = "'. addslashes(stripslashes($row_curr['curr_margin'])).'";'."\n");
				fwrite($fp,'$default_curr[\'curr_default\'] = "'. addslashes(stripslashes($row_curr['curr_default'])).'";'."\n");
				fwrite($fp,'$default_curr[\'curr_numeric_code\'] = "'. addslashes(stripslashes($row_curr['curr_numeric_code'])).'";'."\n");
			}
				fwrite($fp,'$sel_curr['.$row_curr['currency_id'].'][\'currency_id\'] = "'. addslashes(stripslashes($row_curr['currency_id'])).'";'."\n");
				fwrite($fp,'$sel_curr['.$row_curr['currency_id'].'][\'curr_name\'] = "'. addslashes(stripslashes($row_curr['curr_name'])).'";'."\n");
				fwrite($fp,'$sel_curr['.$row_curr['currency_id'].'][\'curr_sign\'] = "'. addslashes(stripslashes($row_curr['curr_sign'])).'";'."\n");
				fwrite($fp,'$sel_curr['.$row_curr['currency_id'].'][\'curr_sign_char\'] = "'. addslashes(stripslashes($row_curr['curr_sign_char'])).'";'."\n");
				fwrite($fp,'$sel_curr['.$row_curr['currency_id'].'][\'curr_code\'] = "'. addslashes(stripslashes($row_curr['curr_code'])).'";'."\n");
				fwrite($fp,'$sel_curr['.$row_curr['currency_id'].'][\'curr_rate\'] = "'. addslashes(stripslashes($row_curr['curr_rate'])).'";'."\n");
				fwrite($fp,'$sel_curr['.$row_curr['currency_id'].'][\'curr_margin\'] = "'. addslashes(stripslashes($row_curr['curr_margin'])).'";'."\n");
				fwrite($fp,'$sel_curr['.$row_curr['currency_id'].'][\'curr_default\'] = "'. addslashes(stripslashes($row_curr['curr_default'])).'";'."\n");
				fwrite($fp,'$sel_curr['.$row_curr['currency_id'].'][\'curr_numeric_code\'] = "'. addslashes(stripslashes($row_curr['curr_numeric_code'])).'";'."\n");
		}
	}
	fwrite($fp,'?>');		
	fclose($fp);	
}

/*  Function to create the price displaly settings when ever a price display settings is changed from console area */
function create_Tax_Delivery_Paytype_Paymethod_CacheFile()
{
	global $db,$ecom_siteid,$image_path;
	$file_path = $image_path .'/settings_cache';
	if(!file_exists($file_path))
		mkdir($file_path);
	$file_name = $file_path.'/common_settings.php';
	// Open the file in write mod 
	$fp = fopen($file_name,'w');
	fwrite($fp,'<?php'."\n");
	// get the details from general_settings_site_pricedisplay
	$tax_vals = 0;
	$tax_name	= array();
	$sql_tax = "SELECT tax_name,tax_val
					FROM
						general_settings_site_tax
					WHERE
						sites_site_id = $ecom_siteid
						AND tax_active = 1";
	$ret_tax = $db->query($sql_tax);
	if ($db->num_rows($ret_tax))
	{
		while ($row_tax = $db->fetch_array($ret_tax))
		{
			$tax_vals += $row_tax['tax_val'];
			$tax_name[] = stripslashes($row_tax['tax_name']);
		}
	}

	fwrite($fp,'$ret_tax["tax_val"] 		= "'. $tax_vals.'";'."\n");
	for ($i=0;$i<count($tax_name);$i++)
	{
		fwrite($fp,'$ret_tax["tax_name"][] 	= "'. $tax_name[$i].'";'."\n");
	}	
	
	$sql_del = "SELECT a.deliverymethod_id,a.deliverymethod_text,a.deliverymethod_location_required,deliverymethod_name 
						FROM
							delivery_methods a,general_settings_site_delivery b
						WHERE
							b.sites_site_id = $ecom_siteid
							AND a.deliverymethod_id=b.delivery_methods_delivery_id 
						LIMIT
							1";
	$ret_del = $db->query($sql_del);
	if ($db->num_rows($ret_del))
	{
		$row_del = $db->fetch_array($ret_del);
		fwrite($fp,'$ret_delivery["deliverymethod_id"] 		= "'. $row_del['deliverymethod_id'].'";'."\n");
		fwrite($fp,'$ret_delivery["deliverymethod_name"] 	= "'. $row_del['deliverymethod_name'].'";'."\n");
		fwrite($fp,'$ret_delivery["deliverymethod_text"] 		= "'. $row_del['deliverymethod_text'].'";'."\n");
		fwrite($fp,'$ret_delivery["deliverymethod_location_required"] 	= "'. $row_del['deliverymethod_location_required'].'";'."\n");
	}
	
	
	$tot_cnt =  0;
	$tot_paymethod_cnt  = $paymethod_without_google_cnt = 0;
	
	// Get all the payment types set for the site
	$sql_paytypes = "SELECT a.paytype_id, a.paytype_name, a.paytype_code, a.paytype_order, a.paytype_showinvoucher, a.paytype_logintouse, a.paytype_showinpayoncredit,b.paytype_caption 
								FROM 
									payment_types a,payment_types_forsites b 
								WHERE 
									b.sites_site_id = $ecom_siteid 
									AND a.paytype_id = b.	paytype_id 
									AND b.paytype_forsites_active=1 
									AND b.paytype_forsites_userdisabled=0";
	$ret_paytypes = $db->query($sql_paytypes);
	if ($db->num_rows($ret_paytypes))
	{
		$tot_cnt = $without_google_cnt = 0;
		while ($row_paytypes = $db->fetch_array($ret_paytypes))
		{
			$tot_cnt++;
			// Payment type Array based on paytype id
			fwrite($fp,'$ret_paytypeId["'.$row_paytypes['paytype_id'].'"] ["paytype_id"]	= "'. $row_paytypes['paytype_id'].'";'."\n");
			fwrite($fp,'$ret_paytypeId["'.$row_paytypes['paytype_id'].'"] ["paytype_name"]	 = "'. $row_paytypes['paytype_caption'].'";'."\n");
			fwrite($fp,'$ret_paytypeId["'.$row_paytypes['paytype_id'].'"] ["paytype_code"]	= "'. $row_paytypes['paytype_code'].'";'."\n");
			fwrite($fp,'$ret_paytypeId["'.$row_paytypes['paytype_id'].'"] ["paytype_order"]	 = "'. $row_paytypes['paytype_order'].'";'."\n");
			fwrite($fp,'$ret_paytypeId["'.$row_paytypes['paytype_id'].'"] ["paytype_showinvoucher"] = "'. $row_paytypes['paytype_showinvoucher'].'";'."\n");
			fwrite($fp,'$ret_paytypeId["'.$row_paytypes['paytype_id'].'"] ["paytype_logintouse"] = "'. $row_paytypes['paytype_logintouse'].'";'."\n");
			fwrite($fp,'$ret_paytypeId["'.$row_paytypes['paytype_id'].'"] ["paytype_showinpayoncredit"] = "'. $row_paytypes['paytype_showinpayoncredit'].'";'."\n");
			
			//  Payment type Array based on paytype code
			fwrite($fp,'$ret_paytypeCode["'.$row_paytypes['paytype_code'].'"] ["paytype_id"]	 = "'. $row_paytypes['paytype_id'].'";'."\n");
			fwrite($fp,'$ret_paytypeCode["'.$row_paytypes['paytype_code'].'"] ["paytype_name"] = "'. $row_paytypes['paytype_caption'].'";'."\n");
			fwrite($fp,'$ret_paytypeCode["'.$row_paytypes['paytype_code'].'"] ["paytype_code"]	 = "'. $row_paytypes['paytype_code'].'";'."\n");
			fwrite($fp,'$ret_paytypeCode["'.$row_paytypes['paytype_code'].'"] ["paytype_order"] = "'. $row_paytypes['paytype_order'].'";'."\n");
			fwrite($fp,'$ret_paytypeCode["'.$row_paytypes['paytype_code'].'"] ["paytype_showinvoucher"] = "'. $row_paytypes['paytype_showinvoucher'].'";'."\n");
			fwrite($fp,'$ret_paytypeCode["'.$row_paytypes['paytype_code'].'"] ["paytype_logintouse"] = "'. $row_paytypes['paytype_logintouse'].'";'."\n");
			fwrite($fp,'$ret_paytypeCode["'.$row_paytypes['paytype_code'].'"] ["paytype_showinpayoncredit"] = "'. $row_paytypes['paytype_showinpayoncredit'].'";'."\n");
			
		}
	}
		fwrite($fp,'$total_paytype_cnts	 = "'. $tot_cnt.'";'."\n");							
	// Get all the payment methods set for the current site
	$sql_paymethod = "SELECT a.paymethod_id, a.paymethod_name, a.paymethod_key, a.paymethod_takecarddetails, a.paymethod_ssl_imagelink, a.paymethod_showinvoucher,
											a.paymethod_secured_req, a.paymethod_showinpayoncredit,payment_method_sites_active,b.payment_method_google_recommended,
											b.payment_method_preview_req,b.payment_method_sites_caption   
								FROM 
									payment_methods a, payment_methods_forsites b 
								WHERE 
									b.sites_site_id = $ecom_siteid 
									AND a.paymethod_id = b.	payment_methods_paymethod_id 
									AND payment_method_sites_active =1
									AND payment_hide = 0 ";
	$ret_paymethod = $db->query($sql_paymethod);
	if ($db->num_rows($ret_paymethod))
	{
		while ($row_paymethod = $db->fetch_array($ret_paymethod))
		{
			$tot_paymethod_cnt++;
			if ($row_paymethod['paymethod_key'] != 'GOOGLE_CHECKOUT')
				$paymethod_without_google_cnt++;
			// Payment method Array based on paymethod_id id
			fwrite($fp,'$ret_paymethodId["'.$row_paymethod['paymethod_id'].'"] ["paymethod_id"] = "'. $row_paymethod['paymethod_id'].'";'."\n");
			fwrite($fp,'$ret_paymethodId["'.$row_paymethod['paymethod_id'].'"] ["paymethod_name"] = "'. $row_paymethod['payment_method_sites_caption'].'";'."\n");
			fwrite($fp,'$ret_paymethodId["'.$row_paymethod['paymethod_id'].'"] ["paymethod_key"] = "'. $row_paymethod['paymethod_key'].'";'."\n");
			fwrite($fp,'$ret_paymethodId["'.$row_paymethod['paymethod_id'].'"] ["paymethod_takecarddetails"] = "'. $row_paymethod['paymethod_takecarddetails'].'";'."\n");
			fwrite($fp,'$ret_paymethodId["'.$row_paymethod['paymethod_id'].'"] ["paymethod_ssl_imagelink"] = "'. $row_paymethod['paymethod_ssl_imagelink'].'";'."\n");
			fwrite($fp,'$ret_paymethodId["'.$row_paymethod['paymethod_id'].'"] ["paymethod_showinvoucher"] = "'. $row_paymethod['paymethod_showinvoucher'].'";'."\n");
			fwrite($fp,'$ret_paymethodId["'.$row_paymethod['paymethod_id'].'"] ["paymethod_secured_req"] = "'. $row_paymethod['paymethod_secured_req'].'";'."\n");
			fwrite($fp,'$ret_paymethodId["'.$row_paymethod['paymethod_id'].'"] ["paymethod_showinpayoncredit"] = "'. $row_paymethod['paymethod_showinpayoncredit'].'";'."\n");
			fwrite($fp,'$ret_paymethodId["'.$row_paymethod['paymethod_id'].'"] ["payment_method_preview_req"] = "'. $row_paymethod['payment_method_preview_req'].'";'."\n");
			fwrite($fp,'$ret_paymethodId["'.$row_paymethod['paymethod_id'].'"] ["payment_method_google_recommended"] = "'. $row_paymethod['payment_method_google_recommended'].'";'."\n");

			// Payment method Array based on paymethod_key id
			fwrite($fp,'$ret_paymethodKey["'.$row_paymethod['paymethod_key'].'"] ["paymethod_id"] = "'. $row_paymethod['paymethod_id'].'";'."\n");
			fwrite($fp,'$ret_paymethodKey["'.$row_paymethod['paymethod_key'].'"] ["paymethod_name"] = "'. $row_paymethod['payment_method_sites_caption'].'";'."\n");
			fwrite($fp,'$ret_paymethodKey["'.$row_paymethod['paymethod_key'].'"] ["paymethod_key"] = "'. $row_paymethod['paymethod_key'].'";'."\n");
			fwrite($fp,'$ret_paymethodKey["'.$row_paymethod['paymethod_key'].'"] ["paymethod_takecarddetails"]	 = "'. $row_paymethod['paymethod_takecarddetails'].'";'."\n");
			fwrite($fp,'$ret_paymethodKey["'.$row_paymethod['paymethod_key'].'"] ["paymethod_ssl_imagelink"] = "'. $row_paymethod['paymethod_ssl_imagelink'].'";'."\n");
			fwrite($fp,'$ret_paymethodKey["'.$row_paymethod['paymethod_key'].'"] ["paymethod_showinvoucher"]	 = "'. $row_paymethod['paymethod_showinvoucher'].'";'."\n");
			fwrite($fp,'$ret_paymethodKey["'.$row_paymethod['paymethod_key'].'"] ["paymethod_secured_req"] = "'. $row_paymethod['paymethod_secured_req'].'";'."\n");
			fwrite($fp,'$ret_paymethodKey["'.$row_paymethod['paymethod_key'].'"] ["paymethod_showinpayoncredit"] = "'. $row_paymethod['paymethod_showinpayoncredit'].'";'."\n");
			fwrite($fp,'$ret_paymethodKey["'.$row_paymethod['paymethod_key'].'"] ["payment_method_preview_req"] = "'. $row_paymethod['payment_method_preview_req'].'";'."\n");
			fwrite($fp,'$ret_paymethodKey["'.$row_paymethod['paymethod_key'].'"] ["payment_method_google_recommended"] = "'. $row_paymethod['payment_method_google_recommended'].'";'."\n");
		}
	}
	fwrite($fp,'$total_paymethods_cnt = "'. $tot_paymethod_cnt.'";'."\n");			
	fwrite($fp,'$total_paymethods_without_google_cnt	= "'. $paymethod_without_google_cnt.'";'."\n");								
	fwrite($fp,'?>');		
	fclose($fp);	
}

// Function to check whether errors or warning to be displayed in console home page
function Check_for_errors_or_warnings()
{
	global $db,$ecom_siteid,$ecom_site_hide_console_error_msgs;
	if($ecom_site_hide_console_error_msgs==0)
	{
		$err_arr = $warn_arr = array();
		$cat_group_exists 		= false;
		$category_exists		= false; 
		// ###################################################################################################
		// Errors
		// ###################################################################################################
		// Check whether category groups exists in current site
		$sql_catmenu = "SELECT catgroup_id 
							FROM 
								product_categorygroup 
							WHERE 
								sites_site_id = $ecom_siteid 
							LIMIT 
								1";
		$ret_catmenu = $db->query($sql_catmenu);
		if ($db->num_rows($ret_catmenu)==0)
		{
			$err_arr[] = 'No Category menus exists in your site';
		}
		else
			$cat_group_exists = true;
			
		// Check whether categories exists in current site
		$sql_cat = "SELECT category_id 
						FROM 
							product_categories 
						WHERE 
							sites_site_id = $ecom_siteid 
						LIMIT 
							1";
		$ret_cat = $db->query($sql_cat);
		if ($db->num_rows($ret_cat)==0)
		{
			$err_arr[] = 'No Categories exists in your site';			
		}
		else // case if categories exists
		{
			$category_exists = true;
			if($cat_group_exists) // if cat group exists. check whether there exists any category group with no categories mapped to it
			{
				$cat_arr = array();
				// Get distinct category group id from category group - category mapping table
				$sql_cat = "SELECT distinct a.catgroup_id 
								FROM 
									product_categorygroup_category a, product_categorygroup b 
								WHERE 
									a.catgroup_id = b.catgroup_id 
									AND b.sites_site_id = $ecom_siteid";
				$ret_cat = $db->query($sql_cat);
				if($db->num_rows($ret_cat))
				{
					while ($row_cat =$db->fetch_array($ret_cat))
					{
						$cat_arr[] = $row_cat['catgroup_id'];
					}
				}
				if (count($cat_arr)==0)
				{
					$warn_arr[] = 'Category menus are not mapped with any of the categories in your site. <a href="home.php?request=prod_cat_group" class="edittextlink">Click here</a> to go to category menu listing page';
				}
				else
				{
					// Check whether there exists any category groups to which no categories have been assigned yet
					$cat_str = implode(',',$cat_arr);
					$sql_catgroup = "SELECT catgroup_id 
										FROM 
											product_categorygroup 
										WHERE 
											sites_site_id = $ecom_siteid 
											AND catgroup_id NOT IN ($cat_str) 
										LIMIT 
											1";
					$ret_catgroup = $db->query($sql_catgroup);
					if($db->num_rows($ret_catgroup))
					{
						$warn_arr[] = 'Few Category menus are not mapped with categories in your website. <a href="home.php?request=prod_cat_group" class="edittextlink">Click here</a> to go to category menu listing page';
					}
				}
			
				// Check whether there exists any parent categories which are not yet assigned to any of the category groups
				$cat_arr = array(-1);
				// Get distinct category  id from category group - category mapping table
				$sql_cat = "SELECT distinct a.category_id 
								FROM 
									product_categorygroup_category a, product_categories b 
								WHERE 
									a.category_id = b.category_id 
									AND b.sites_site_id = $ecom_siteid";
				$ret_cat = $db->query($sql_cat);
				if($db->num_rows($ret_cat))
				{
					while ($row_cat =$db->fetch_array($ret_cat))
					{
						$cat_arr[] = $row_cat['category_id'];
					}
				}
				$cat_str = implode(',',$cat_arr);
				$sql_cat = "SELECT count(category_id) 
								FROM 
									product_categories 
								WHERE 
									parent_id = 0 
									AND sites_site_id = $ecom_siteid 
									AND category_id NOT IN ($cat_str) ";
				$ret_cat = $db->query($sql_cat);
				list($cat_cnt) = $db->fetch_array($ret_cat);
				if($cat_cnt)
				{
					if ($cat_cnt>1)
					{
						$cat_cnts  	= "Few Catogories";
						$is_ar		= 'are';	 
					}	
					elseif($cat_cnt==1)
					{
						$cat_cnts = 'One Category';
						$is_ar		= 'is';
						$cat_cnt	= '';
					}	
					$warn_arr[] = "$cat_cnt $cat_cnts at root level in your website $is_ar not mapped to any of the category menus. <a href='home.php?request=prod_cat' class='edittextlink'>Click here</a> to go to category listing page";
				}	
			}	
		}
		
		// Check products exists in current site
		$sql_prod = "SELECT product_id 
						FROM 
							products 
						WHERE 
							sites_site_id = $ecom_siteid 
						LIMIT 
							1";
		$ret_prod = $db->query($sql_prod);
		if ($db->num_rows($ret_prod)==0)
		{
			$err_arr[] = 'No products exists in your website';
		}
		else
		{
			if($category_exists)
			{
				$sql_prods = "SELECT distinct products_product_id 
								FROM 
									product_category_map a, products b
								WHERE 
									b.sites_site_id = $ecom_siteid 
									AND a.products_product_id = b.product_id ";
				$ret_prods = $db->query($sql_prods);
				$prod_arr  = array();
				if($db->num_rows($ret_prods))
				{
					while ($row_prods = $db->fetch_array($ret_prods))
					{
						$prod_arr[] = $row_prods['products_product_id'];
					}	
					$prod_str = implode(',',$prod_arr);
					$sql_prod = "SELECT count(product_id) 
									FROM 
										products 
									WHERE 
										sites_site_id = $ecom_siteid 
										AND product_id NOT IN ($prod_str)";	
					$ret_prod = $db->query($sql_prod);
					list($prod_cnt) = $db->fetch_array($ret_prod);
					if ($prod_cnt==1)
					{
						$prod_str 	= 'One Product';
						$is_are		= 'is';
					}
					elseif($prod_cnt>1)
					{
						$prod_str 	= 'Few Products';
						$is_are		= 'are';
					}
					if ($prod_cnt)
						$warn_arr[] = "$prod_str in your website $is_ar not mapped to any of the categories. <a href='home.php?request=products' class='edittextlink'>Click here</a> to go to product listing page";
				}
				else
				{
					$err_arr[] = 'None of the products in your website are mapped with any of the categories. <a href="home.php?request=products" class="edittextlink">Click here</a> to go to product listing page';
				}
			}	
		}
		
		$staticpage_group_exists = false;
		// Check whether static groups exists in current site
		$sql_catmenu = "SELECT group_id 
							FROM 
								static_pagegroup 
							WHERE 
								sites_site_id = $ecom_siteid 
							LIMIT 
								1";
		$ret_catmenu = $db->query($sql_catmenu);
		if ($db->num_rows($ret_catmenu)==0)
		{
			$err_arr[] = 'No Static Page menus exists in your site';
		}
		else
			$staticpage_group_exists = true;
			
		// Check whether static pages exists in current site
		$sql_cat = "SELECT  page_id 
						FROM 
							static_pages  
						WHERE 
							sites_site_id = $ecom_siteid 
						LIMIT 
							1";
		$ret_cat = $db->query($sql_cat);
		if ($db->num_rows($ret_cat)==0)
		{
			$warn_arr[] = 'No Static Pages exists in your site. <a href="home.php?request=stat_page" class="edittextlink">Click here</a> to go to static page listing page';			
		}
		else // case if static pages exists
		{
			if($staticpage_group_exists) // if static page group exists. check whether there exists any static page group with no pages mapped to it
			{
				$stat_arr = array();
				// Get distinct category group id from category group - category mapping table
				$sql_stat = "SELECT distinct a.static_pagegroup_group_id  
								FROM 
									static_pagegroup_static_page_map a, static_pagegroup b 
								WHERE 
									a.static_pagegroup_group_id = b.group_id 
									AND b.sites_site_id = $ecom_siteid";
				$ret_stat = $db->query($sql_stat);
				if($db->num_rows($ret_stat))
				{
					while ($row_stat =$db->fetch_array($ret_stat))
					{
						$stat_arr[] = $row_stat['static_pagegroup_group_id'];
					}
				}
				if (count($stat_arr)==0)
				{
					$warn_arr[] = 'Static page menus are not mapped with static pages in your site. <a href="home.php?request=stat_page" class="edittextlink">Click here</a> to go to static page listing page';
				}
				else
				{
					// Check whether there exists any static groups to which no pages have been assigned yet
					$stat_str = implode(',',$stat_arr);
					$sql_catgroup = "SELECT group_id 
										FROM 
											static_pagegroup  
										WHERE 
											sites_site_id = $ecom_siteid 
											AND group_id NOT IN ($stat_str) 
										LIMIT 
											1";
					$ret_catgroup = $db->query($sql_catgroup);
					if($db->num_rows($ret_catgroup))
					{
						$warn_arr[] = 'Few Static page Menus are not mapped with static pages in your site. <a href="home.php?request=stat_group" class="edittextlink">Click here</a> to go to static page menu listing page';
					}
				}
			
			
				// Check whether there exists any pages which are not yet assigned to any of the page groups
				$stat_arr = array(-1);
				// Get distinct page id from static page group - pages mapping table
				$sql_stat = "SELECT distinct a.static_pages_page_id  
								FROM 
									static_pagegroup_static_page_map a, static_pages b 
								WHERE 
									a.static_pages_page_id = b.page_id  
									AND b.sites_site_id = $ecom_siteid";
				$ret_stat = $db->query($sql_stat);
				if($db->num_rows($ret_stat))
				{
					while ($row_stat =$db->fetch_array($ret_stat))
					{
						$stat_arr[] = $row_stat['static_pages_page_id'];
					}
				}
				$stat_str = implode(',',$stat_arr);
				$sql_stat = "SELECT count(page_id) 
								FROM 
									static_pages 
								WHERE 
									sites_site_id = $ecom_siteid 
									AND page_id NOT IN ($stat_str) ";
				$ret_stat = $db->query($sql_stat);
				list($stat_cnt) = $db->fetch_array($ret_stat);
				if($stat_cnt)
				{
					if ($stat_cnt>1)
					{
						$stat_cnts  	= "Few Static Pages";
						$is_ar		= 'are';	 
					}	
					elseif($cat_cnt==0)
					{
						$stat_cnts = 'Static Page';
						$is_ar		= 'is';
					}	
					$warn_arr[] = "$stat_cnt $stat_cnts in your website $is_ar not mapped to any of the static page menus. <a href='home.php?request=stat_page' class='edittextlink'>Click here</a> to go to static page listing page";
				}	
			}	
		}
		// Check whether the rate of any of the currency is set to 0, if yes then report it
		$sql_curr = "SELECT currency_id,curr_name 
						FROM 
							general_settings_site_currency 
						WHERE 
							sites_site_id = $ecom_siteid 
							AND curr_rate = 0 
						LIMIT 
							2";
		$ret_curr = $db->query($sql_curr);
		$curr_cnt = $db->num_rows($ret_curr);	
		if($db->num_rows($ret_curr))
		{
			
			$err_arr[] = 'Rates for some of the currencies in your website is set to 0. Currency rates should not be zero. <a href="home.php?request=general_settings_currency" class="edittextlink">Click here</a> to go to currency listing page';
		}
		// Check delivery method.
		$sql_del = "SELECT deliverymethod_id,deliverymethod_text, deliverymethod_location_required 
						FROM 
							delivery_methods a, general_settings_site_delivery b
						WHERE 
							a.deliverymethod_id = b.delivery_methods_delivery_id 
							AND b.sites_site_id = $ecom_siteid 
						LIMIT 
							1";
		$ret_del = $db->query($sql_del);
		if ($db->num_rows($ret_del))
		{
			$row_del = $db->fetch_array($ret_del);
			if($row_del['deliverymethod_text']!='None')
			{
				// check whether current delivery method requires location
				if ($row_del['deliverymethod_location_required']==1)
				{
					$sql_loc = "SELECT location_id 
									FROM 
										delivery_site_location 
									WHERE 
										sites_site_id = $ecom_siteid 
										AND delivery_methods_deliverymethod_id = ".$row_del['deliverymethod_id'];
					$ret_loc = $db->query($sql_loc);
					
					if($db->num_rows($ret_loc)==0)
					{
						$err_arr[] = "Delivery method is related to location but no location details added to your website. <a href='home.php?request=delivery_settings' class='edittextlink'>Click here</a> to go to delivery settings page";
					}
				}
				// Check whether any active delivery method group exists for current site
				$sql_delgrp = "SELECT delivery_group_id 
								FROM 
									general_settings_site_delivery_group 
								WHERE 
									sites_site_id = $ecom_siteid 
									AND delivery_group_hidden = 0";
				$ret_delgrp = $db->query($sql_delgrp);
				if ($db->num_rows($ret_delgrp))
				{
					$found_grp = false;
					while ($row_delgrp = $db->fetch_array($ret_delgrp))
					{
						if ($row_del['deliverymethod_location_required']==1)
						{
							if ($db->num_rows($ret_loc))
							{
								$found = false;
								while ($row_loc = $db->fetch_array($ret_loc))
								{
									// Chech whether there exists an entry for current location for current delivery method for current site
									$sql_opt = "SELECT delopt_det_id 
													FROM 
														delivery_site_option_details 
													WHERE 
														delivery_methods_deliverymethod_id = ".$row_del['deliverymethod_id']." 
														AND delivery_site_location_location_id=".$row_loc['location_id']." 
														AND delivery_group_id=".$row_delgrp['delivery_group_id']." 
														AND sites_site_id = $ecom_siteid 
													LIMIT 
														1";
									$ret_opt = $db->query($sql_opt);
									if($db->num_rows($ret_opt)==0 and $found==false)
									{
										$err_arr[] 	= 'Delivery details not added completly for all locations for all delivery groups. <a href="home.php?request=delivery_settings" class="edittextlink">Click here</a> to go to delivery settings page';	
										$found 		= true;
										$found_grp 	= true;
									}					
								}
							}
						}
						else // delivery method not related to location and no group exists
						{
							// Chech whether there exists an entry for current delivery method for current site
							$sql_opt = "SELECT delopt_det_id 
											FROM 
												delivery_site_option_details 
											WHERE 
												delivery_methods_deliverymethod_id = ".$row_del['deliverymethod_id']." 
												AND delivery_group_id=".$row_delgrp['delivery_group_id']."
												AND sites_site_id = $ecom_siteid 
											LIMIT 
												1";
							$ret_opt = $db->query($sql_opt);
							if($db->num_rows($ret_opt)==0 and $found_grp==false)
							{
								$err_arr[]  = 'Delivery details not added completly for all delivery groups in your website. <a href="home.php?request=delivery_settings" class="edittextlink">Click here</a> to go to delivery settings page';	
								$found_grp 	= true;
							}	
						}
					}	
				}
				else // no active delivery method group exists
				{
					if ($row_del['deliverymethod_location_required']==1)
					{
						if ($db->num_rows($ret_loc))
						{
							$found = false;
							while ($row_loc = $db->fetch_array($ret_loc))
							{
								// Chech whether there exists an entry for current location for current delivery method for current site
								$sql_opt = "SELECT delopt_det_id 
												FROM 
													delivery_site_option_details 
												WHERE 
													delivery_methods_deliverymethod_id = ".$row_del['deliverymethod_id']." 
													AND delivery_site_location_location_id=".$row_loc['location_id']." 
													AND sites_site_id = $ecom_siteid 
												LIMIT 
													1";
								$ret_opt = $db->query($sql_opt);
								if($db->num_rows($ret_opt)==0 and $found==false)
								{
									$err_arr[] = 'Delivery details not added completly for all locations. <a href="home.php?request=delivery_settings" class="edittextlink">Click here</a> to go to delivery settings page';	
									$found = true;
								}					
							}
						}
					}
					else // delivery method not related to location and no group exists
					{
						// Chech whether there exists an entry for current delivery method for current site
						$sql_opt = "SELECT delopt_det_id 
										FROM 
											delivery_site_option_details 
										WHERE 
											delivery_methods_deliverymethod_id = ".$row_del['deliverymethod_id']." 
											AND sites_site_id = $ecom_siteid 
										LIMIT 
											1";
						$ret_opt = $db->query($sql_opt);
						if($db->num_rows($ret_opt)==0)
						{
							$err_arr[] = 'Delivery details not added completly for your website. <a href="home.php?request=delivery_settings" class="edittextlink">Click here</a> to go to delivery settings page';	
						}	
					}
				}
			}
		}	
		else
			$err_arr[] = 'Delivery method not selected for your website. <a href="home.php?request=delivery_settings" class="edittextlink">Click here</a> to go to delivery settings page';					
		
		// Check whether the number of email templates in current site is not equal to that set from super admin
		$sql_email = "SELECT count(template_id) 
						FROM 
							common_emailtemplates ";
		$ret_email = $db->query($sql_email);
		
		$sql_emails = "SELECT count(lettertemplate_id) 
							FROM 
								general_settings_site_letter_templates 
							WHERE 
								sites_site_id = $ecom_siteid";
		$ret_emails = $db->query($sql_emails);
		if ($db->num_rows($ret_email) != $db->num_rows($ret_emails))
		{
			$err_arr[] = 'Mismatch in the number of email templates in your website. <a href="home.php?request=settings_letter_templates" class="edittextlink">Click here</a> to go to email templates listing page';
		}
		// From Address for all email templates are set or not
		$sql_email = "SELECT lettertemplate_id 
						FROM 
							general_settings_site_letter_templates 
						WHERE 
							sites_site_id = $ecom_siteid 
							AND lettertemplate_from = '' 
						LIMIT 
							1";
		$ret_email = $db->query($sql_email);
		if ($db->num_rows($ret_email))
		{
			$err_arr[] = 'The From Email Id is not set for some email templates in your website. <a href="home.php?request=settings_letter_templates" class="edittextlink">Click here</a> to go to email templates listing page';		
		}
		
		// Check whether company types exists
		$sql_types = "SELECT comptype_id 
						FROM 
							general_settings_sites_customer_company_types 
						WHERE 
							sites_site_id = $ecom_siteid 
							AND comptype_hide = 0 
						LIMIT 
							1";
		$ret_types = $db->query($sql_types);
		if($db->num_rows($ret_types)==0)
		{
			$err_arr[] = 'Customer Company types not set. <a href="home.php?request=general_settings_comptype" class="edittextlink">Click here</a> to go to company types listing page';	
		}
		
		// Check whether country exists
		$sql_country = "SELECT country_id  
						FROM 
							general_settings_site_country  
						WHERE 
							sites_site_id = $ecom_siteid 
							AND country_hide = 1  
						LIMIT 
							1";
		$ret_country = $db->query($sql_country);
		if($db->num_rows($ret_country)==0)
		{
			$err_arr[] = 'Countries not set for current website. <a href="home.php?request=general_settings_country" class="edittextlink">Click here</a> to go to country listing page';	
		}
		
		// Check whether order confirmation emails are specified
		$sql_common = "SELECT order_confirmationmail, forcecustomer_login_checkout, delivery_settings_weight_min_limit,
							delivery_settings_weight_max_limit, delivery_settings_weight_increment, delivery_settings_common_min,
							delivery_settings_common_max, delivery_settings_common_increment, unit_of_weight,bonuspoint_rate,best_seller_picktype   
						FROM 
							general_settings_sites_common 
						WHERE 
							sites_site_id = $ecom_siteid 
						LIMIT 
							1";
		$ret_common = $db->query($sql_common);
		if($db->num_rows($ret_common))
		{
			$row_common = $db->fetch_array($ret_common);
		}
		if(trim($row_common['order_confirmationmail'])=='')
		{
			$warn_arr[] = 'Order confirmation email id not set in general settings. <a href="home.php?request=general_settings&fpurpose=orderconfirmemail" class="edittextlink">Click here</a> to go to order confirmation settings page';
		}
		if(trim($row_common['bonuspoint_rate'])==0)
		{
			$warn_arr[] = 'Bonus points rate not set in general settings. <a href="home.php?request=general_settings&fpurpose=bonus_rate" class="edittextlink">Click here</a> to go to bonus point rate settings page';
		}
		
		if ($row_common['delivery_settings_common_min']<0 or  $row_common['delivery_settings_common_max']<=0 or  $row_common['delivery_settings_common_increment'] <=0
		or $row_common['delivery_settings_weight_min_limit']<0 or  $row_common['delivery_settings_common_max']<=0 or  $row_common['delivery_settings_common_increment']<=0)
		{
			$err_arr[] = 'Delivery settings for drop down boxes not correctly set. <a href="home.php?request=delivery_settings_more" class="edittextlink">Click here</a> to go to delivery settings for dropdowns sections';
		}
		// Check whether atleast one downloadable product exist in current website. So customer should be forced to login while checkout
		$sql_prod = "SELECT product_id 
						FROM 
							products 
						WHERE 
							sites_site_id = $ecom_siteid 
							AND product_downloadable_allowed = 'Y' 
							AND product_hide = 0 
						LIMIT 
							1";
		$ret_prod = $db->query($sql_prod);
		if ($db->num_rows($ret_prod))
		{
			if($row_common['forcecustomer_login_checkout']==0)
			{
				$err_arr[] = 'Force customer to login option should be activated from general settings section as downloadable products exists in your website. <a href="home.php?request=general_settings&fpurpose=settings_default&curtab=administration_tab_td" class="edittextlink">Click here</a> to go to main shop settings section.';
			}
		}
		
		// Check whether tax is set in site
		$sql_tax = "SELECT tax_id 
						FROM 
							general_settings_site_tax 
						WHERE 
							sites_site_id = $ecom_siteid 
						LIMIT 
							1";
		$ret_tax = $db->query($sql_tax);
		if($db->num_rows($ret_tax)==0)
		{
			$warn_arr[] = 'No tax details set in your website. <a href="home.php?request=general_settings_tax" class="edittextlink">Click here</a> to go to tax settings page.';
		}
		
		// Get details from list order settings
		$sql_list = "SELECT product_maxbestseller_in_component, enquiry_maxcntperpage, orders_maxcntperpage, orders_maxcntperpage_enquiry,
							orders_maxcntperpage_enqposts, product_preorder_in_component, productreview_maxcntperpage, 
							sitereview_maxcntperpage, product_maxcnt_fav_category, product_maxcnt_recent_purchased, 
							payon_maxcntperpage_statements, product_limit_homepage_favcat_recent ,product_maxshelfprod_in_component
						FROM 
							general_settings_sites_listorders 
						WHERE 
							sites_site_id = $ecom_siteid 
						LIMIT 
							1";
		$ret_list = $db->query($sql_list);
		if($db->num_rows($ret_list))
		{
			$row_list = $db->fetch_array($ret_list);
		}
		
		// ###################################################################################################		
		// Warnings
		// ###################################################################################################
		if($row_common['best_seller_picktype']==1) // If best sellers is to be picked manually
		{
			$sql_best = "SELECT bestsel_id 
							FROM 
								general_settings_site_bestseller 
							WHERE 
								sites_site_id = $ecom_siteid 
							LIMIT 
								1";
			$ret_best = $db->query($sql_best);
			if ($db->num_rows($ret_best)==0)
			{
					$warn_arr[] = 'Best Sellers are selected to pick manually, but not selected the products to be displayed as best sellers.  <a href="home.php?request=bestseller&curtab=prods_tab_td" class="edittextlink">Click here</a> to go to best sellers settings page.';
			}
		}
		if($row_list['product_maxbestseller_in_component']==0)
		{
			$warn_arr[] = 'Maximum number of bestseller products in left / right menu is not set. <a href="home.php?request=general_settings&fpurpose=list_order" class="edittextlink">Click here</a> to go to list order settings page.';
		}
		if($row_list['product_maxshelfprod_in_component']==0)
		{
			$warn_arr[] = 'Maximum number of products in left / right menu shelves listing is not set. <a href="home.php?request=general_settings&fpurpose=list_order" class="edittextlink">Click here</a> to go to list order settings page.';
		}
		if($row_list['product_preorder_in_component']==0)
		{
			$warn_arr[] = 'Maximum number of products in left / right menu preorder listing is not set. <a href="home.php?request=general_settings&fpurpose=list_order" class="edittextlink">Click here</a> to go to list order settings page.';
		}
		if($row_list['enquiry_maxcntperpage']==0)
		{
			$warn_arr[] = 'Number of enquiries to be shown per page for registered customers is not set. <a href="home.php?request=general_settings&fpurpose=list_order&curtab=regcustomers_tab_td" class="edittextlink">Click here</a> to go to list order settings page.';
		}
		if($row_list['orders_maxcntperpage']==0)
		{
			$warn_arr[] = 'Number of orders to be shown per page for registered customers is not set. <a href="home.php?request=general_settings&fpurpose=list_order&curtab=regcustomers_tab_td" class="edittextlink">Click here</a> to go to list order settings page.';
		}
		if($row_list['orders_maxcntperpage_enquiry']==0)
		{
			$warn_arr[] = 'Number of orders queries to be shown per page for registered customers is not set. <a href="home.php?request=general_settings&fpurpose=list_order&curtab=regcustomers_tab_td" class="edittextlink">Click here</a> to go to list order settings page.';
		}
		if($row_list['orders_maxcntperpage_enqposts']==0)
		{
			$warn_arr[] = 'Number of posts of orders queries to be shown per page for registered customers is not set. <a href="home.php?request=general_settings&fpurpose=list_order&curtab=regcustomers_tab_td" class="edittextlink">Click here</a> to go to list order settings page.';
		}
		if($row_list['payon_maxcntperpage_statements']==0)
		{
			$warn_arr[] = 'Number of payonaccount statements to be shown per page for registered customers is not set. <a href="home.php?request=general_settings&fpurpose=list_order&curtab=regcustomers_tab_td" class="edittextlink">Click here</a> to go to list order settings page.';
		}
		if($row_list['product_limit_homepage_favcat_recent']==0)
		{
			$warn_arr[] = 'Limit of products in favourite categories to be shown in the home page of registered customers is not set. <a href="home.php?request=general_settings&fpurpose=list_order&curtab=regcustomers_tab_td" class="edittextlink">Click here</a> to go to list order settings page.';
		}
		if($row_list['product_maxcnt_fav_category']==0)
		{
			$warn_arr[] = 'Number of products to be shown per page in show all of favourite categories of registered customers is not set. <a href="home.php?request=general_settings&fpurpose=list_order&curtab=regcustomers_tab_td" class="edittextlink">Click here</a> to go to list order settings page.';
		}
		if($row_list['product_maxcnt_recent_purchased']==0)
		{
			$warn_arr[] = 'Limit of recently viewed products in left/right menu in website is not set. <a href="home.php?request=general_settings&fpurpose=list_order&curtab=regcustomers_tab_td" class="edittextlink">Click here</a> to go to list order settings page.';
		}
		if($row_list['productreview_maxcntperpage']==0)
		{
			$warn_arr[] = 'Number of product reviews to be shown per page in website is not set. <a href="home.php?request=general_settings&fpurpose=list_order&curtab=others_tab_td" class="edittextlink">Click here</a> to go to list order settings page.';
		}
		if($row_list['sitereview_maxcntperpage']==0)
		{
			$warn_arr[] = 'Number of site reviews to be shown per page in website is not set. <a href="home.php?request=general_settings&fpurpose=list_order&curtab=others_tab_td" class="edittextlink">Click here</a> to go to list order settings page.';
		}
		
		// Get the google settings from sites table
		$sql_sites = "SELECT is_meta_verificationcode, meta_verificationcode, is_google_urchinwebtracker_code, is_google_webtracker_code,
							is_google_adword_checkout, google_webtracker_code, google_webtracker_urchin_code, google_adword_conversion_id,
							google_adword_conversion_language, google_adword_conversion_format, google_adword_conversion_color, google_adword_conversion_label 
						FROM 
							sites 
						WHERE 
							site_id = $ecom_siteid
						LIMIT 
							1";
		$ret_sites = $db->query($sql_sites);
		if($db->num_rows($ret_sites))
		{
			$row_sites = $db->fetch_array($ret_sites);
			
			if($row_sites['is_meta_verificationcode']==1)
			{
				if (trim($row_sites['meta_verificationcode'])=='')
					$warn_arr[] = 'Google meta verification code not set for your website. <a href="home.php?request=seo_keyword&fpurpose=verification_code" class="edittextlink">Click here</a> to go to google details settings page.';
			}
			if($row_sites['is_google_webtracker_code']==1)
			{
				if (trim($row_sites['google_webtracker_code'])=='')
					$warn_arr[] = 'Google Webtracker Code code not set for your website. <a href="home.php?request=seo_keyword&fpurpose=verification_code" class="edittextlink">Click here</a> to go to google details settings page.';
			}
			if($row_sites['is_google_urchinwebtracker_code']==1)
			{
				if (trim($row_sites['google_webtracker_urchin_code'])=='')
					$warn_arr[] = 'Google Urchin Webtracker Code not set for your website. <a href="home.php?request=seo_keyword&fpurpose=verification_code" class="edittextlink">Click here</a> to go to google details settings page.';
			}
			if($row_sites['is_google_adword_checkout']==1)
			{
				if (trim($row_sites['google_adword_conversion_id'])=='')
					$warn_arr[] = 'Google adword Conversion Id not set for your website. <a href="home.php?request=seo_keyword&fpurpose=verification_code" class="edittextlink">Click here</a> to go to google details settings page.';
				if (trim($row_sites['google_adword_conversion_language'])=='')
					$warn_arr[] = 'Google adword Conversion Language not set for your website. <a href="home.php?request=seo_keyword&fpurpose=verification_code" class="edittextlink">Click here</a> to go to google details settings page.';
				if (trim($row_sites['google_adword_conversion_format'])=='')
					$warn_arr[] = 'Google adword Conversion Format not set for your website. <a href="home.php?request=seo_keyword&fpurpose=verification_code" class="edittextlink">Click here</a> to go to google details settings page.';
				if (trim($row_sites['google_adword_conversion_format'])=='')
					$warn_arr[] = 'Google adword Conversion Color not set for your website. <a href="home.php?request=seo_keyword&fpurpose=verification_code" class="edittextlink">Click here</a> to go to google details settings page.';		
				if (trim($row_sites['google_adword_conversion_label'])=='')
					$warn_arr[] = 'Google adword Conversion Label not set for your website. <a href="home.php?request=seo_keyword&fpurpose=verification_code" class="edittextlink">Click here</a> to go to google details settings page.';			
			}
		}
		if(count($err_arr)>0 or count($warn_arr)>0) // if error or working exists
		{
			$ret_arr['mod'] = false;
			$ret_arr['err']	= $err_arr;
			$ret_arr['wrn']	= $warn_arr;
		}	
		else // case if no warning or error exists
			$ret_arr['mod'] = true;
	}
	else
		$ret_arr['mod'] = true;
	
	return $ret_arr;	
	
}
function Display_Main_Help_msg($help_arr,$help_msg,$bypass_help_icon=0)
{
	global $db,$ecom_hostname;
	if ($help_arr['html_path']!='')
	{
		//$help_link = "javascript:showPopup('".$ecom_hostname."','".$help_arr['html_path']."',800,750)";
		$help_link = "javascript:alert('Coming soon...')";
	}
	else 
	{
		$help_link = "javascript:alert('Coming soon...')";
	}
	?>	
	  
	  <div class="helpmsg_divotr">
      <div class="helpmsg_divotr_in">
      <div class="helpmsgtd-header_inner" align="left"><?php if(trim($help_msg)!=''){?>What is this feature?<?php } else '&nbsp;'; ?></div>
	  <div class="helpmsg_div">
	  <?=$help_msg?>
	 </div> </div>
<?php if ($bypass_help_icon==0){?>
<div class="helpmsgtd-bottom" align="right"><a href="<?php echo $help_link?>" class="helpmsgtdlink"><img src="images/help_inner.gif" border="0" /></a></div><?php }?></div>
      
<?php	 
}
/*
	Function to decrement the stock, usage count for gift vouchers and customer discount updations
*/
function do_PostOrderSuccessOperations($order_id)
{
	global $db,$ecom_siteid,$ecom_hostname;
	// product_decrementstock from 
	$sql_gen = "SELECT product_decrementstock 
					FROM 
						general_settings_sites_common 
					WHERE 
						sites_site_id = $ecom_siteid 
					LIMIT 
						1";
	$ret_gen = $db->query($sql_gen);
	if($db->num_rows($ret_gen))
	{
		$Settings_arr = $db->fetch_array($ret_gen);
	}
	/* Donate bonus Start */
	// Get relevant details from orders table 
	$sql_ord = "SELECT order_date,customers_customer_id,promotional_code_code_id,gift_vouchers_voucher_id,order_bonuspoints_used,
						order_bonuspoint_inorder,order_paymenttype,order_totalprice,order_deposit_amt,
						order_bonuspoints_donated    
					FROM 
						orders 
					WHERE 
						order_id = $order_id 
					LIMIT 
						1";
	/* Donate bonus End */					
	$ret_ord = $db->query($sql_ord);
	if ($db->num_rows($ret_ord)==0) // done to handle the case if order with specified id does not exists
	{
		return ;
	}
	$row_ord = $db->fetch_array($ret_ord);
	// Get the order details for current order id
	$sql_det = "SELECT orderdet_id,products_product_id,order_orgqty,order_preorder,order_stock_combination_id 
					FROM 
						order_details 
					WHERE 
						orders_order_id = $order_id";
	$ret_det = $db->query($sql_det);
	if ($db->num_rows($ret_det))
	{
		// Handle the case of decrementing the stock for products in order
		$prodext_arr= array(-1);
		while ($row_det = $db->fetch_array($ret_det))
		{
			// Check whether the product is currently in preorder or not
			$sql_prod = "SELECT product_preorder_allowed,product_variablestock_allowed,product_alloworder_notinstock  
							FROM 
								products 
							WHERE 
								product_id=".$row_det['products_product_id']." 
							LIMIT 
								1";
			$ret_prod = $db->query($sql_prod);
			if ($db->num_rows($ret_prod))
			{
				$row_prod = $db->fetch_array($ret_prod);
			}
				if($row_prod['product_preorder_allowed']=='N') // Proceed to stock decrement section only if the product in not in preorder
				{
					if($Settings_arr['product_decrementstock']) // Set from console to decrement the stock when order is successfull
					{
						if($row_prod['product_variablestock_allowed']=='Y' and $row_det['order_stock_combination_id']) // If variable stock maintained
						{
							// Get the current stock for the current combination of current product
							$sql_stock = "SELECT web_stock
											FROM
												product_variable_combination_stock
											WHERE
												products_product_id = ".$row_det['products_product_id']. "
												AND comb_id = ".$row_det['order_stock_combination_id']."
											LIMIT
												1";
							$ret_stock = $db->query($sql_stock);
							if ($db->num_rows($ret_stock))
							{
								$row_stock = $db->fetch_array($ret_stock);
								if($row_stock['web_stock']>$row_det['order_orgqty']) // case if stock in web is > req qty
									$new_webstock = $row_stock['web_stock'] - $row_det['order_orgqty'];
								else
									$new_webstock = 0;
								// Updating the stock for current combination of current product
								$update_array				= array();
								$update_array['web_stock']	= $new_webstock;
								$db->update_from_array($update_array,'product_variable_combination_stock',array('comb_id'=>$row_det['order_stock_combination_id'],'products_product_id'=>$row_det['products_product_id']));
							}
						}
						else // Case if fixed stock is maintained
						{
							// Get the current stock for the product
							$sql_stock = "SELECT product_webstock
											FROM
												products
											WHERE
												product_id = ".$row_det['products_product_id']. "
											LIMIT
												1";
							$ret_stock = $db->query($sql_stock);
							if ($db->num_rows($ret_stock))
							{
								$row_stock = $db->fetch_array($ret_stock);
								if($row_stock['product_webstock']>$row_det['order_orgqty']) // case if stock in web is > req qty
									$new_webstock = $row_stock['product_webstock'] - $row_det['order_orgqty'];
								else
									$new_webstock = 0;
								// Updating the stock for current product
								$update_array						= array();
								$update_array['product_webstock']	= $new_webstock;
								$db->update_from_array($update_array,'products',array('product_id'=>$row_det['products_product_id']));
							}
						}
						// Calling function to recalculate the actual stock for the product
						recalculate_actual_stock($row_det['products_product_id']);
					}
				}
				else 
				{
				// If product was in preorder then decrement the max preorder allowed count for the product by one
					if($row_prod['product_preorder_allowed']=='Y')
					{
						if(!in_array($row_det['products_product_id'],$prodext_arr))// This is done to handle the case to decrement the total preorder value only once even if the product exists in cart more than once
						{
							$update_sql = "UPDATE 
												products 
											SET 
												product_total_preorder_allowed = product_total_preorder_allowed - 1 
											WHERE 
												product_total_preorder_allowed > 0 
												AND product_id = ".$row_det['products_product_id']." 
												AND sites_site_id = $ecom_siteid  
											LIMIT 
												1";
							$db->query($update_sql);
							$prodext_arr[] = $row_det['products_product_id'];
						}
					}	
				}
		}
	
		// If any gift voucher is used in current order, then increment the value of voucher_usage by 1
		if ($row_ord['gift_vouchers_voucher_id'])
		{
			$update_sql = "UPDATE gift_vouchers
							 SET
								voucher_usage = voucher_usage+1
							WHERE
								voucher_id = ".$row_ord['gift_vouchers_voucher_id']."
								AND sites_site_id = $ecom_siteid
								AND voucher_max_usage>voucher_usage";
			$db->query($update_sql);
		}
	
		// If any promotional code is used in current order and its usage is limited , then increment the value of code_usedlimit by 1
		if ($row_ord['promotional_code_code_id'])
		{
				// Increment the userlimit field value by  1
					$update_sql = "UPDATE promotional_code
											 SET
												code_usedlimit = code_usedlimit+1
											WHERE
												code_id = ".$row_ord['promotional_code_code_id']."
												AND sites_site_id = $ecom_siteid 
												AND code_unlimit_check=0 
												AND code_limit > code_usedlimit 
											LIMIT 
												1";
					$db->query($update_sql);
					if($row_ord['customers_customer_id'])
					{
						$update_sql = "UPDATE promotional_code
											 SET
												code_customer_usedlimit = code_customer_usedlimit+1
											WHERE
												code_id = ".$row_ord['promotional_code_code_id']." 
												AND code_login_to_use = 1 
												AND sites_site_id = $ecom_siteid 
												AND code_customer_unlimit_check=0 
												AND code_customer_limit > code_customer_usedlimit  
											LIMIT 
												1";
						$db->query($update_sql);
					}
		}		
		if($row_ord["customers_customer_id"])
		{
			// Section which updates the bonus points for customers
			// case if customer if logged in<br>
			// get the bonuspoints available for current customer
			$sql_cust = "SELECT customer_bonus 
							FROM 
								customers 
							WHERE 
								customer_id=".$row_ord['customers_customer_id']." 
							LIMIT 
								1";
			$ret_cust = $db->query($sql_cust);
			if($db->num_rows($ret_cust))
			{
				$row_cust 							= $db->fetch_array($ret_cust);
				$total_cust_bonuspoints				= $row_cust['customer_bonus'];
				$points_earned						= $row_ord['order_bonuspoint_inorder'];
				$points_used						= $row_ord['order_bonuspoints_used'];
				/* Donate bonus Start */
				$points_donated						= $row_ord['order_bonuspoints_donated'];
				/* Donate bonus End */
				
				/* Donate bonus Start */
				$final_bonuspoints					= $total_cust_bonuspoints + $points_earned - ($points_used+$points_donated);
				/* Donate bonus End */
				if($final_bonuspoints<0) // done to handle the case of bonus points becoming -ive 
					$final_bonuspoints = 0;
				$update_array						= array();
				$update_array['customer_bonus']		= $final_bonuspoints;
				$db->update_from_array($update_array,'customers',array('customer_id'=>$row_ord['customers_customer_id']));
			}
			
			//  Handling the case of downloadble products (if any) in current order. If exists and if active date range is applicable, then set the active start and end date based on number of days field
			$sql_download = "UPDATE order_product_downloadable_products 
								SET 
									proddown_days_active_start = now(),
									proddown_days_active_end = DATE_ADD(now(),INTERVAL proddown_days DAY) 
								WHERE 
									orders_order_id=$order_id 
									AND sites_site_id = $ecom_siteid 
									AND proddown_days_active=1 ";
			$db->query($sql_download);
			
			// Check whether payment type is pay_on_account
			if($row_ord['order_paymenttype']=='pay_on_account')
			{
				// If any exists for current order id in this table delete it 
				$sql_del = "DELETE FROM 
									order_payonaccount_details 
								WHERE 
									orders_order_id = ".$order_id." 
								LIMIT 
									1";
				$db->query($sql_del);
				// Making entry to order_payonaccount_details
				$insert_array											= array();
				$insert_array['pay_date']								= $row_ord['order_date'];
				$insert_array['orders_order_id']						= $order_id;
				$insert_array['sites_site_id']							= $ecom_siteid;
				$insert_array['customers_customer_id']					= $row_ord["customers_customer_id"];
				$insert_array['pay_amount']								= $row_ord['order_totalprice'];
				$insert_array['pay_transaction_type']					= 'D';	
				$insert_array['pay_details']							= 'Order Id '.$order_id;
				$insert_array['pay_paystatus']							= $row_ord['order_paymenttype'];
				$insert_array['pay_paymenttype']						= $row_ord['order_paymenttype'];
				$insert_array['pay_paymentmethod']						= '';
				$db->insert_from_array($insert_array,'order_payonaccount_details');
				
				// Decrement the pay on account limit for current customer
				$update_sql = "UPDATE customers 
								SET 
									customer_payonaccount_usedlimit = customer_payonaccount_usedlimit + ".$row_ord['order_totalprice'] ." 
								WHERE 
									sites_site_id = $ecom_siteid 
									AND customer_id = ".$row_ord['customers_customer_id']." 
								LIMIT 
									1";
				$db->query($update_sql);
			}									
		}
	}
}
function seo_revenue_report($row_ord,$order_amount) {
	global $db,$ecom_siteid;
	/*$db->query("UPDATE seo_revenue_total SET click_to_sale=click_to_sale+1,click_total=click_total+$order_amount WHERE site_id=$ecom_siteid AND click_id=".$_SESSION['cpc_click_id']);
	$db->query("UPDATE seo_revenue_month SET click_to_sale=click_to_sale+1,click_total=click_total+$order_amount WHERE click_pm_id=".$_SESSION['cpc_click_pm_id']);
	*/
	$db->query("UPDATE seo_revenue_total SET click_to_sale=click_to_sale+1,click_total=click_total+$order_amount WHERE site_id=$ecom_siteid AND click_id=".$row_ord['order_cpc_click_id']);
	$db->query("UPDATE seo_revenue_month SET click_to_sale=click_to_sale+1,click_total=click_total+$order_amount WHERE click_pm_id=".$row_ord['order_cpc_click_pm_id']);
}
function  cost_per_click($id_arr,$total)
{
	global $db,$ecom_siteid;
	$id_arr 		= explode('_',$id_arr);
	$url_id 		= $id_arr[0];
	$month_id		= $id_arr[1];
	// Update both the table costperclick_adverturl and costperclick_month 
	$sql_update = "UPDATE 
							costperclick_adverturl 
						SET 
							url_total_sale_clicks 	= url_total_sale_clicks + 1,
							url_total_sale_amount = url_total_sale_amount + $total 
						WHERE 
							url_id = $url_id 
							AND sites_site_id = $ecom_siteid 
						LIMIT 
							1";
	$db->query($sql_update);
	
	// Update both the table costperclick_adverturl and costperclick_month 
	$sql_update = "UPDATE 
								costperclick_month 
							SET 
								month_total_sale_clicks = month_total_sale_clicks + 1,
								month_total_sale_amount = month_total_sale_amount + $total 
							WHERE 
								month_id = $month_id 
								AND sites_site_id = $ecom_siteid 
							LIMIT 
								1";
	$db->query($sql_update);
}	

function get_combination_id($prodid,$var_arr=array())
{
	global $db,$ecom_siteid;
	$sql_prod = "SELECT product_variablestock_allowed,product_webstock,product_preorder_allowed,
						product_total_preorder_allowed,product_instock_date,product_variablecomboprice_allowed,product_variablecombocommon_image_allowed 
					FROM
						products
					WHERE
						product_id=$prodid
						AND sites_site_id = $ecom_siteid
					LIMIT
						1";
	$ret_prod = $db->query($sql_prod);
	if ($db->num_rows($ret_prod))
	{
		$row_prod = $db->fetch_array($ret_prod);
		
		if (count($var_arr)==0 and ($row_prod['product_variablestock_allowed'] == 'Y' or $row_prod['product_variablecomboprice_allowed']=='Y' or $row_prod['product_variablecombocommon_image_allowed']=='Y')) // case if variable combination price is allowed and also if var arr is null
		{
			$sql_var = "SELECT var_id,var_name  
							FROM 
								product_variables 
							WHERE 
								products_product_id = ".$prodid." 
								AND var_hide= 0 
								AND var_value_exists = 1 
							ORDER BY 
								var_order";
			$ret_var = $db->query($sql_var);
			if($db->num_rows($ret_var))
			{
				while ($row_var = $db->fetch_array($ret_var))
				{
					$curvar_id= $row_var['var_id'];
					// Get the value id of first value for this variable
					$sql_datas = "SELECT var_value_id 
											FROM 
												product_variable_data 
											WHERE 
												product_variables_var_id = ".$curvar_id." 
											ORDER BY var_order  
											LIMIT 
												1";
					$ret_datas = $db->query($sql_datas);
					if ($db->num_rows($ret_datas))
					{
						$row_data = $db->fetch_array($ret_datas);
					}							
					$var_arr[$curvar_id] = $row_data['var_value_id'];
				}
			}
		}
		foreach ($var_arr as $k=>$v)
		{
			// Check whether the variable is a check box or a drop down box
			$sql_check = "SELECT var_id
							FROM
								product_variables
							WHERE
								var_id=$k
								AND var_value_exists = 1
							LIMIT
								1";
			$ret_check = $db->query($sql_check);
			if ($db->num_rows($ret_check))
			{
				$row_check 	= $db->fetch_array($ret_check);
				$varids[] 	= $k; // populate only the id's of variables which have values to the array
			}
		}
		if (count($varids))
		{
			if ($row_prod['product_variablestock_allowed'] == 'Y' or $row_prod['product_variablecomboprice_allowed']=='Y' or $row_prod['product_variablecombocommon_image_allowed']=='Y') // Case if variable stock is maintained
			{
					// Find the various combinations available for current product
					$sql_comb = "SELECT comb_id 
									FROM
										product_variable_combination_stock
									WHERE
										products_product_id = $prodid";
					$ret_comb = $db->query($sql_comb);
					if ($db->num_rows($ret_comb))
					{
						while ($row_comb = $db->fetch_array($ret_comb))
						{
							$comb_found_cnt = 0;
							// Get the combination details for current combination
							$sql_combdet = "SELECT comb_id,product_variables_var_id,product_variable_data_var_value_id
												FROM
													product_variable_combination_stock_details
												WHERE
													comb_id = ".$row_comb['comb_id']."
													AND products_product_id=$prodid";
							$ret_combdet = $db->query($sql_combdet);
							if ($db->num_rows($ret_combdet))
							{
								if ($db->num_rows($ret_combdet)==count($varids))// check whether count in table is same as that of count in array
								{
									while ($row_combdet = $db->fetch_array($ret_combdet))
									{
										if (in_array($row_combdet['product_variables_var_id'],$varids))
										{
											if ($var_arr[$row_combdet['product_variables_var_id']]==$row_combdet['product_variable_data_var_value_id'])
											{
												$comb_found_cnt++;
											}
										}
									}
								}
							}
							if (($comb_found_cnt and count($varids)) and $comb_found_cnt==count($varids))
							{
								$ret_data['combid']	= $row_comb['comb_id'];
								return $ret_data; // return from function as soon as the combination found
							}
						}
					}
				}
				else // case if variable stock is not maintained
				{
					$ret_data['combid']	= 0;
					return $ret_data; // return from function as soon as the combination found
				}
			}	
		}
	$ret_data['combid']			= 0;
	return $ret_data; // return from function as soon as the combination found
}
function handle_default_comp_price_and_id($prodid)
{
	global $db,$ecom_siteid;
	// Check whether product_variablecomboprice_allowed option is active for current product
		$sql_check = "SELECT product_variablestock_allowed, product_variablecomboprice_allowed, product_variablecombocommon_image_allowed  
								FROM 
									products 
								WHERE 
									product_id = ".$prodid." 
								LIMIT 
									1";
		$ret_check = $db->query($sql_check);
		if ($db->num_rows($ret_check))
		{
			$row_check = $db->fetch_array($ret_check);
		}									
		if($row_check['product_variablestock_allowed']=='Y' or $row_check['product_variablecomboprice_allowed']=='Y' or $row_check['product_variablecombocommon_image_allowed']=='Y')  // do the following only if product_variablecomboprice_allowed is set to Y
		{
			// Get the combination id of first combination for thie product
			$comb_arr = get_combination_id($prodid);
			if($comb_arr['combid'])
			{
				$add_condition = '';
				if($row_check['product_variablecomboprice_allowed'] =='Y')
				{
					// Get the price for combination from product_variable_combination_stock
					$sql_price = "SELECT comb_price 
											FROM 
												product_variable_combination_stock 
											WHERE 
												comb_id = ".$comb_arr['combid']." 
												AND products_product_id =".$prodid." 
											LIMIT 
												1";
					$ret_price = $db->query($sql_price);
					if ($db->num_rows($ret_price))
					{
						$row_price = $db->fetch_array($ret_price);
					}	
					$set_price  = (!$row_price['comb_price'])?0:$row_price['comb_price'];
					$add_condition .= " product_webprice = ".$set_price;
				}
				if($add_condition!='')
					$add_condition .=",";
				$add_condition .= " default_comb_id = ".$comb_arr['combid'];
				
				if($add_condition!='')
				{
					// Updating the product_webprice field in products table with this value
					$update_sql = "UPDATE products 
												SET 
													$add_condition 
												WHERE 
													product_id = ".$prodid."
													AND sites_site_id =$ecom_siteid 
												LIMIT 
													1";
					$db->query($update_sql);									
				}	
			}
			else
			{
				$update_sql = "UPDATE products 
											SET 
												default_comb_id = 0 
											WHERE 
												product_id = $prodid 
												AND sites_site_id = $ecom_siteid 
											LIMIT 
												1";
				$db->query($update_sql);						
			}
		}
}
function headed_email($from,$to, $subject, $content, $attachments = array())
{
	$m = new Mime($from,	// From
				  $to,											// To
				  "",		// CC
				  $subject, 									// Subject
				  "multipart/mixed");
	
	$m->start_multipart("related");
	$m->insert_text("html", $content);
	//m->insert_image("emails/b1st-logo.jpg", $b1st_logo_id);
	//$m->insert_image("emails/$logo", $bshop_logo_id);
	$m->end_multipart();

	foreach($attachments as $attach) $m->insert_attachment($attach["type"], $attach["filename"]);
	$m->send();
}	
function sendOrderMailWithAttachment($email_orderid,$order_invoice_id,$email_to,$email_subject,$email_content,$pick_paystatus=0)
{
	global $db,$ecom_siteid,$image_path;
	// Get the name of file to be attached
	$sql_attach = "SELECT invoice_filename 
						FROM 
							order_invoice 
						WHERE 
							invoice_id = $order_invoice_id 
						LIMIT 
							1";
	$ret_attach = $db->query($sql_attach);
	if($db->num_rows($ret_attach))
	{
		$row_attach 		= $db->fetch_array($ret_attach);
		$full_filename		= $image_path.'/invoices/'.$row_attach['invoice_filename'];
		if($pick_paystatus==1) // if payment status is to be picked from the order table and update it in the invoice file before attaching it with confirmation mail
		{
			$sql_stat = "SELECT order_paystatus 
							FROM 
								orders 
							WHERE 
								order_id = $email_orderid 
							LIMIT 
								1";
			$ret_stat = $db->query($sql_stat);
			if ($db->num_rows($ret_stat))
			{
				$row_stat = $db->fetch_array($ret_stat);
				$cur_stat = getpaymentstatus_Name($row_stat['order_paystatus']);
				// Read the conten of invoice file
				$fp 			= fopen($full_filename,'r');
				$file_content 	= fread($fp,filesize($full_filename));
				fclose($fp);
				$file_content 	= preg_replace ("/<paystat>(.*)<\/paystat>/", "<paystat>".$cur_stat."</paystat>", $file_content);
				$fp				= fopen($full_filename,'w');
				fwrite($fp,$file_content);
				fclose($fp);
			}					
		}
		// Get the from address for the email
		$sql_temp = "SELECT lettertemplate_from 
						FROM 
							general_settings_site_letter_templates 
						WHERE 
							sites_site_id = $ecom_siteid 
							AND lettertemplate_letter_type = 'ORDER_CONFIRM_CUST' 
						LIMIT 
							1";
		$ret_temp = $db->query($sql_temp);
		if ($db->num_rows($ret_temp))
		{
			$row_temp 	= $db->fetch_array($ret_temp);
			$email_from = stripslashes($row_temp['lettertemplate_from']);
		}					
		$file_type			= 'text/html';
		headed_email($email_from,$email_to, $email_subject, $email_content,array(array("filename" => $full_filename, "type" => $file_type)));
	}						
}	
function check_repetation_in_combo_deal_products($combo_id,$deactivate_combo=false)
{
	global $db,$ecom_siteid;
	$different = false;
	$alert = '';
	// get the list of products linked with current combo from combo_products
	$sql_combo = "SELECT comboprod_id,products_product_id  
						FROM 
							combo_products 
						WHERE 
							combo_combo_id = $combo_id";
	$ret_combo = $db->query($sql_combo);
	if($db->num_rows($ret_combo))
	{
		while ($row_combo = $db->fetch_array($ret_combo))
		{
			$curprod_map = $row_combo['comboprod_id'];
			$curprod_org = $row_combo['products_product_id'];
			$cur_var_arr = get_combo_deal_product_var($curprod_map);
			// Check whether there exists other mapping to the same product in current combo
			$sql_sameprod = "SELECT comboprod_id 
								FROM 
									combo_products 
								WHERE 
									combo_combo_id = $combo_id 
									AND products_product_id = $curprod_org 
									AND comboprod_id <> $curprod_map";
			$ret_sameprod = $db->query($sql_sameprod);
			if ($db->num_rows($ret_sameprod))
			{
				while ($row_sameprod = $db->fetch_array($ret_sameprod))
				{
					$cur_temp_var_arr = get_combo_deal_product_var($row_sameprod['comboprod_id']);
					if(count($cur_var_arr) != count($cur_temp_var_arr)) // for same product the number of variables should be same, if not, show error 
					{
						$alert = 'Error with the product ';
					}
					else // case if variable count are same, then check whether there exists atleast one difference in variable values
					{
						if(count($cur_var_arr)==0 and count($cur_temp_var_arr)==0)
						{
							$alert = 'Duplication in product';
						}
						if(count($cur_var_arr)==0 or count($cur_temp_var_arr)==0)
						{
							$alert = 'Error with product ';
						}
						else
						{
							foreach ($cur_var_arr as $k=>$v)
							{
								if($v != $cur_temp_var_arr[$k])
								{
									$different = true;
								}
							}
						}	
					}
				}
			}
			else
				$different = true;
		}	
	}	
	if($alert!='')
	{
		if($deactivate_combo==true)
		{
			$update_sql = "UPDATE 
								combo 
							SET 
								combo_active = 0  
							WHERE 
								combo_id = $combo_id 
								AND sites_site_id = $ecom_siteid 
							LIMIT 
								1";
			$db->query($update_sql);			
			$alert .=' <br>Combo Deal Deactivated';		
			$err = 1;
		}
		return "<br>".$alert;
	}
	elseif ($different==false)
	{
		$alert = 'Duplicate products exists';	
		if($deactivate_combo==true)
		{
			$update_sql = "UPDATE 
								combo 
							SET 
								combo_active = 0  
							WHERE 
								combo_id = $combo_id 
								AND sites_site_id = $ecom_siteid 
							LIMIT 
								1";
			$db->query($update_sql);		
			$err = 1;	
			$alert .=' <br>Combo Deal Deactivated';		
		}
		$ret_arr['err'] 	= $err;
		$ret_arr['alert'] 	= $alert;
	}	
}

function set_combo_bundle_price($combo_id)
{
	global $db,$ecom_siteid;
	$sql_combo = "SELECT sum(combo_discount) as totamt 
					FROM 
						combo_products 
					WHERE 
						combo_combo_id = $combo_id";
	$ret_combo = $db->query($sql_combo);
	list($bundle_price) = $db->fetch_array($ret_combo);
	if (!$bundle_price)
		$bundle_price = 0;
	$sql_update = "UPDATE 
						combo 
					SET 
						combo_bundleprice = $bundle_price 
					WHERE 
						combo_id = $combo_id 
					LIMIT 
						1";
	$db->query($sql_update);
}

/* Function to check whether any combo exists with same set of products other than the give combo */
function check_same_combo_exist($combo_id)
{
	global $db,$ecom_siteid;
	$already_exists = false; 	// variable to decide whether combo already exists or not
	// Get the list of all products assigned to current combo
	$sql_curprod = "SELECT comboprod_id, products_product_id 
						FROM 
							combo_products 
						WHERE 
							combo_combo_id = $combo_id";
	$ret_curprod = $db->query($sql_curprod);
	if($db->num_rows($ret_curprod))
	{
		while ($row_curprod = $db->fetch_array($ret_curprod))
		{
			$combdet_arr = array();
			// Get all combination of current mappings
			$sql_comb = "SELECT comb_id 
							FROM 
								combo_products_variable_combination 
							WHERE 
								combo_products_comboprod_id = ".$row_curprod['comboprod_id'];
			$ret_comb = $db->query($sql_comb);
			$combdet_arr = array();
			if($db->num_rows($ret_comb))
			{
				while ($row_comb = $db->fetch_array($ret_comb))
				{
					$combdet_arr[$row_curprod['products_product_id']][] = $row_comb['comb_id'];
				}
			}					
			$curprod_arr[] = array(0=>$row_curprod['comboprod_id'].'~'.$row_curprod['products_product_id'],1=>$combdet_arr);
		}
	}			
	// Get the list of all combos other than this
	$sql_combo = "SELECT combo_id 
					FROM 
						combo 
					WHERE 
						sites_site_id = $ecom_siteid 
						AND combo_id <> $combo_id ";
	$ret_combo = $db->query($sql_combo);
	if ($db->num_rows($ret_combo))
	{
		while ($row_combo = $db->fetch_array($ret_combo))
		{
			$same_comb_cnt = 0;
			// get all products in current combo
			$sql_curcombprod = "SELECT comboprod_id, products_product_id 
								FROM 
									combo_products 
								WHERE 
									combo_combo_id = ".$row_combo['combo_id'];
			$ret_curcombprod = $db->query($sql_curcombprod);
			if($db->num_rows($ret_curcombprod)==count($curprod_arr)) // proceed only if the number of products in both the combo are same
			{
				while ($row_curcombprod = $db->fetch_array($ret_curcombprod))
				{
					// Check whether each of the products in combo1 exists in current combo
					for($i=0;$i<count($curprod_arr);$i++)
					{
						$temp_arr	 			= explode('~',$curprod_arr[$i][0]);
						$curprodmapid 			= $temp_arr[0];
						$curprodid 				= $temp_arr[1];
						$atleastonesame_comb 	= false;
						if($row_curcombprod['products_product_id'] == $curprodid) // case if matching products found
						{

							// Get the combinations available for current product map
							$sql_map = "SELECT comb_id 
											FROM 
												combo_products_variable_combination  
											WHERE 
												combo_products_comboprod_id = ".$row_curcombprod['comboprod_id'];
							$ret_map = $db->query($sql_map);
							if($db->num_rows($ret_map))
							{
								while ($row_map = $db->fetch_array($ret_map))
								{
									if(count($curprod_arr[$i][1][$curprodid]))
									{
										for($ii=0;$ii<count($curprod_arr[$i][1][$curprodid]);$ii++)
										{
											if($curprod_arr[$i][1][$curprodid][$ii])
											{
												if(is_combination_same($row_map['comb_id'],$curprod_arr[$i][1][$curprodid][$ii]))
													$atleastonesame_comb = true;
											}		
										}
									}
								}
							}
							else // to handle the case of no combination exists
								$atleastonesame_comb = true;
							if($atleastonesame_comb)
								$same_comb_cnt++;					
						}	
					}
				}
			}	
			if($same_comb_cnt==count($curprod_arr)) // if number of matches found is equal to the number of products in current combo
				return true;
		}
	}
	return $already_exists;
}
/* Function to check whether 2 combinations match */
function is_combination_same($comb1_id,$comb2_id)
{	
	global $db,$ecom_siteid;
	$same = true;
	$var1_arr = $var2_arr = array();
	$sql_combo1_det = "SELECT var_id, var_value_id 
							FROM 
								combo_products_variable_combination_map 
							WHERE 
								combo_products_variable_combination_comb_id = $comb1_id";
	$ret_combo1_det = $db->query($sql_combo1_det);
	if($db->num_rows($ret_combo1_det))
	{
		while($row_combo1_det = $db->fetch_array($ret_combo1_det))
		{
			$var1_arr[$row_combo1_det['var_id']] = $row_combo1_det['var_value_id'];
		}
	}
	$sql_combo2_det = "SELECT var_id, var_value_id 
							FROM 
								combo_products_variable_combination_map 
							WHERE 
								combo_products_variable_combination_comb_id = $comb2_id";
	$ret_combo2_det = $db->query($sql_combo2_det);
	if($db->num_rows($ret_combo2_det))
	{
		while($row_combo2_det = $db->fetch_array($ret_combo2_det))
		{
			$var2_arr[$row_combo2_det['var_id']] = $row_combo2_det['var_value_id'];
		}
	}	
	if(count($var1_arr)!=count($var2_arr))
		$same = false;
	else
	{
		$same_cnt = 0;
		foreach ($var1_arr as $k=>$v)
		{
			foreach ($var2_arr as $kk=>$vv)
			{
				if ($k==$kk) // if variables ids are same
				{
					if ($v==$vv)
						$same_cnt++;
				}
			}
		}
		if($same_cnt!=count($var1_arr))
			$same = false;
	}
	return $same;
} 
/* Function to deactivate combo .. when var id is given */
function deactivate_Combo ($var_id)
{
	global $db,$ecom_siteid;
	
	// Get the product id to which the current variable id is related 
	$sql_prod = "SELECT products_product_id 
					FROM 
						product_variables 
					WHERE 
						var_id = $var_id 
					LIMIT 
						1";
	$ret_prod = $db->query($sql_prod);
	if($db->num_rows($ret_prod))
	{
		$row_prod = $db->fetch_array($ret_prod);
		$sql_combo = "SELECT combo_combo_id 
						FROM 
							combo_products 
						WHERE 
							products_product_id = ".$row_prod['products_product_id'];
		$ret_combo = $db->query($sql_combo);
		if($db->num_rows($ret_combo))
		{
			while ($row_combo = $db->fetch_array($ret_combo))
			{
				$update_sql = "UPDATE combo 
										SET 
											combo_active = 0 
										WHERE 
											combo_id = ".$row_combo['combo_combo_id']." 
										LIMIT 
											1";
				$db->query($update_sql);
			}
		}
	}
						
}
/* Function to deactivate combo .. when var id is given */
function deactivate_Promotional_code ($var_id)
{
	global $db,$ecom_siteid;
	
	// Get the product id to which the current variable id is related 
	$sql_prod = "SELECT products_product_id 
					FROM 
						product_variables 
					WHERE 
						var_id = $var_id 
					LIMIT 
						1";
	$ret_prod = $db->query($sql_prod);
	if($db->num_rows($ret_prod))
	{
		$row_prod = $db->fetch_array($ret_prod);
		$sql_code = "SELECT promotional_code_code_id  
						FROM 
							promotional_code_product  
						WHERE 
							products_product_id = ".$row_prod['products_product_id'];
		$ret_code = $db->query($sql_code);
		if($db->num_rows($ret_code))
		{
			while ($row_code = $db->fetch_array($ret_code))
			{
				$update_sql = "UPDATE promotional_code 
										SET 
											code_hidden = 1 
										WHERE 
											code_id = ".$row_code['promotional_code_code_id']." 
										LIMIT 
											1";
				$db->query($update_sql);
			}
		}
	}
						
}
function Change_Combo_Active_status($comboid,$status)
{
	global $db,$ecom_siteid;
	$update_sql = "UPDATE combo 
						SET 
							combo_active = $status  
						WHERE 
							combo_id = $comboid 
						LIMIT 
							1";
	$db->query($update_sql);
}
function Change_Promotional_Active_status($codeid,$status)
{
	global $db,$ecom_siteid;
	$update_sql = "UPDATE promotional_code 
						SET 
							code_hidden = $status  
						WHERE 
							code_id = $codeid 
						LIMIT 
							1";
	$db->query($update_sql);
}	
function combination_already_exists($comb_id,$pass_var_arr,$rearrange_pass_var_arr=true)
{
	global $db,$ecom_siteid;
	$match_cnt = 0;
	$comb_arr = $var_arr = array();
	
	// Get the details of variables and values related to current variable combination
	$sql_combo = "SELECT var_id,var_value_id 
					FROM 
						combo_products_variable_combination_map 
					WHERE 
						combo_products_variable_combination_comb_id = ".$comb_id;
	$ret_combo = $db->query($sql_combo);
	if($db->num_rows($ret_combo))
	{
		$combo_cnt = $db->num_rows($ret_combo);
		while ($row_combo = $db->fetch_array($ret_combo))
		{
			$comb_arr[$row_combo['var_id']] = $row_combo['var_value_id'];	
		}
		
	}
	if(count($pass_var_arr) and $rearrange_pass_var_arr==true)
	{
		for($i=0;$i<count($pass_var_arr);$i++)
		{
			if(count($pass_var_arr[$i]))
			{
				foreach ($pass_var_arr[$i] as $k=>$v)
				{
					$var_arr[$k] = $v;
				}		
			}	
		}	
	}	
	if($rearrange_pass_var_arr==false)
		$var_arr = $pass_var_arr;
	if(count($comb_arr)==count($var_arr))
	{
		foreach ($comb_arr as $k=>$v)
		{
			foreach ($var_arr as $kk=>$vv)
			{
				if($k==$kk)
				{
					if($v==$vv)
						$match_cnt++;
				}
			}
		}
	}
	else
	{
		return false;
	}	
	if($match_cnt==count($comb_arr))
		return true;
	else
		return false;	

}
function promotional_combination_already_exists($comb_id,$pass_var_arr,$rearrange_pass_var_arr=true)
{
	global $db,$ecom_siteid;
	$match_cnt = 0;
	$comb_arr = $var_arr = array();
	
	// Get the details of variables and values related to current variable combination
	$sql_combo = "SELECT var_id,var_value_id 
					FROM 
						promotional_code_products_variable_combination_map 
					WHERE 
						promotional_code_products_variable_combination_comb_id = ".$comb_id;
	$ret_combo = $db->query($sql_combo);
	if($db->num_rows($ret_combo))
	{
		$combo_cnt = $db->num_rows($ret_combo);
		while ($row_combo = $db->fetch_array($ret_combo))
		{
			$comb_arr[$row_combo['var_id']] = $row_combo['var_value_id'];	
		}
		
	}
	if(count($pass_var_arr) and $rearrange_pass_var_arr==true)
	{
		for($i=0;$i<count($pass_var_arr);$i++)
		{
			if(count($pass_var_arr[$i]))
			{
				foreach ($pass_var_arr[$i] as $k=>$v)
				{
					$var_arr[$k] = $v;
				}		
			}	
		}	
	}	
	if($rearrange_pass_var_arr==false)
		$var_arr = $pass_var_arr;
	if(count($comb_arr)==count($var_arr))
	{
		foreach ($comb_arr as $k=>$v)
		{
			foreach ($var_arr as $kk=>$vv)
			{
				if($k==$kk)
				{
					if($v==$vv)
						$match_cnt++;
				}
			}
		}
	}
	else
	{
		return false;
	}	
	if($match_cnt==count($comb_arr))
		return true;
	else
		return false;	

}
function check_atleast_one_combination($combo_id)
{
	global $db,$ecom_siteid;
	$alert = '';
	// get the list of products in current combo deail
	$sql_prod = "SELECT a.product_variables_exists,b.comboprod_id  
					FROM 
						products a, combo_products b 
					WHERE 
						b.combo_combo_id=$combo_id 
						AND a.product_id = b.products_product_id 
						AND a.product_hide='N'";
	$ret_prod = $db->query($sql_prod);
	if($db->num_rows($ret_prod))
	{
		while ($row_prod = $db->fetch_array($ret_prod))
		{
			if($row_prod['product_variables_exists']=='Y')
			{
				// Check whether atleast one combination exists for current product in current assignment
				$sql_check = "SELECT comb_id 
								FROM 
									combo_products_variable_combination 
								WHERE 
									combo_products_comboprod_id = ".$row_prod['comboprod_id']." 
								LIMIT 
									1";
				$ret_check = $db->query($sql_check);
				if ($db->num_rows($ret_check)==0)
					$alert = 'Please select variable combinations for all products with variables in current combo deal.';
			}
		}
	}	
	else
		$alert = 'Sorry.. No active products exists in current combo deal.'	;
	return 	$alert;		
}
function check_atleast_one_promotionalcombination($code_id)
{
	global $db,$ecom_siteid;
	$alert = '';
	// get the list of products in current combo deail
	$sql_prod = "SELECT a.product_variables_exists,b.pcode_det_id   
					FROM 
						products a, promotional_code_product  b 
					WHERE 
						b.promotional_code_code_id=$code_id 
						AND a.product_id = b.products_product_id 
						AND a.product_hide='N'";
	$ret_prod = $db->query($sql_prod);
	if($db->num_rows($ret_prod))
	{
		while ($row_prod = $db->fetch_array($ret_prod))
		{
			if($row_prod['product_variables_exists']=='Y')
			{
				// Check whether atleast one combination exists for current product in current assignment
				$sql_check = "SELECT comb_id 
								FROM 
									promotional_code_products_variable_combination  
								WHERE 
									promotional_code_product_pcode_det_id = ".$row_prod['pcode_det_id']." 
								LIMIT 
									1";
				$ret_check = $db->query($sql_check);
				if ($db->num_rows($ret_check)==0)
					$alert = 'Please select variable combinations for all products with variables in current promotional code.'; 
			}
		}
	}
	else
		$alert = 'Sorry... No active products exists in current Promotional code.'	;		
	return 	$alert;		
}
function check_combo_integrity()
{
	global $db,$ecom_siteid;
	// Get all combo deals existing in current website
	$sql_combo = "SELECT combo_id 
					FROM 
						combo 
					WHERE 
						sites_site_id = $ecom_siteid";
	$ret_combo = $db->query($sql_combo);
	if($db->num_rows($ret_combo))
	{
		while ($row_combo = $db->fetch_array($ret_combo))
		{
			$deactivate = false;
			$comboid = $row_combo['combo_id'];
			$curr_comb_arr = $comb_delete_arr = array();
			// Check whether all combination mappings for current combo is valid
			$sql_sel = "SELECT comboprod_id,products_product_id  
							FROM 
								combo_products 
							WHERE 
							 	combo_combo_id = $comboid";
			$ret_sel = $db->query($sql_sel);
			if($db->num_rows($ret_sel))
			{
				while ($row_sel = $db->fetch_array($ret_sel))
				{
					// Get all combination id 
					$sql_comb = "SELECT comb_id 
									FROM 
										combo_products_variable_combination 
									WHERE 
										combo_products_comboprod_id = ".$row_sel['comboprod_id'];
					$ret_comb = $db->query($sql_comb);
					if($db->num_rows($ret_comb))
					{
						while ($row_comb = $db->fetch_array($ret_comb))
						{
							// Get the var_id and var_value_id related
							$sql_combdet = "SELECT var_id,var_value_id,products_product_id  
												FROM 
													combo_products_variable_combination_map 
												WHERE 
													combo_products_variable_combination_comb_id = ".$row_comb['comb_id'];
							$ret_combdet = $db->query($sql_combdet);
							if($db->num_rows($ret_combdet))
							{
								while ($row_combdet = $db->fetch_array($ret_combdet))
								{
									$val_exists 		= 0;
									$delete_required 	= false;
									// Get the details of current variable from product_variables table
									$sql_variable_check = "SELECT var_id, var_value_exists 
																FROM 
																	product_variables 
																WHERE 
																	var_id = ".$row_combdet['var_id']." 
																	AND var_hide = 0 
																	AND products_product_id = ".$row_sel['products_product_id']." 
																LIMIT 
																	1";
									$ret_variable_check = $db->query($sql_variable_check);
									if($db->num_rows($ret_variable_check)==0)
									{
										$delete_required = true;
									}
									else
									{
										$row_variable_check = $db->fetch_array($ret_variable_check);
										$val_exists 		= $row_variable_check['var_value_exists']; 
									}
									if($val_exists==1) // case if values exists for current variable
									{
										// Check whether current value id is valid
										$sql_varcheck = "SELECT var_value_id 
															FROM 
																product_variable_data 
															WHERE 
																product_variables_var_id = ".$row_combdet['var_id']." 
																AND var_value_id = ".$row_combdet['var_value_id']." 
															LIMIT 
																1";
										$ret_varcheck = $db->query($sql_varcheck);
										if($db->num_rows($ret_varcheck)==0)
										{
											$delete_required = true;
										}
									}
									if($delete_required==true)
									{
										// delete from combo_products_variable_combination_map table
										$sql_del = "DELETE 
														FROM 
															combo_products_variable_combination_map 
														WHERE 
															var_id = ".$row_combdet['var_id']." 
															AND var_value_id=".$row_combdet['var_value_id']." 
															AND combo_products_variable_combination_comb_id=".$row_comb['comb_id'];
										$ret_del = $db->query($sql_del);
										// Check whether atleast one entry exists against current combination id in combo_products_variable_combination_map after deletion
										$sql_cnts = "SELECT combo_products_variable_combination_comb_id 
														FROM 
															combo_products_variable_combination_map 
														WHERE 
															combo_products_variable_combination_comb_id = ".$row_comb['comb_id'];
										$ret_cnts = $db->query($sql_cnts);
										if($db->num_rows($ret_cnts)==0)
										{
											// Delete the respective combination details from combo_products_variable_combination table
											$comb_delete_arr[] = $row_comb['comb_id'];
										} 
									}
								}
							}
						}
					}
				}
			}		
			//Deleting the floating combination details which had occured after above deletion (if required)	
			if(count($comb_delete_arr))
			{
				$del_str = implode(",",$comb_delete_arr);
				$sql_del = "DELETE FROM 
								combo_products_variable_combination 
							WHERE 
								comb_id IN ($del_str)";
				$db->query($sql_del);
			}			
			// Check whether there exists same combinations in current combo deal
			check_and_delete_repeated_combinations($comboid);
			
			// Check whether there exists other combo with the same details
			if(check_same_combo_exist($comboid))
			{
				$deactivate = true;				
			}
			// Check whether atleast one combination exists for products in current combo
			$err = check_atleast_one_combination($comboid);
			if($err!='')
				$deactivate = true;
			if($deactivate==true)
			{
				// deactivate current combo
				Change_Combo_Active_status($comboid,0);
			}
			set_combo_bundle_price($comboid);
		}
	}
}
function check_and_delete_repeated_combinations($combo_id)
{	
	global $ecom_siteid,$db,$ecom_hostname;
	// Get all the products assigned to current combo
	$sql_combprod = "SELECT comboprod_id, products_product_id  
						FROM 
							combo_products 
						WHERE 
							combo_combo_id = ".$combo_id;
	$ret_combprod = $db->query($sql_combprod);
	if($db->num_rows($ret_combprod))
	{
		while ($row_combprod = $db->fetch_array($ret_combprod))
		{
			// Get the combinations available for the mappings
			$sql_comb = "SELECT comb_id 
							FROM 
								combo_products_variable_combination 
							WHERE 
								combo_products_comboprod_id = ".$row_combprod['comboprod_id'];
			$ret_comb = $db->query($sql_comb);
			if($db->num_rows($ret_comb))
			{
				while ($row_comb = $db->fetch_array($ret_comb))
				{
					$var_arr = get_combo_variable_arr($row_comb['comb_id']);
					// get all combinations other than the current one
					$sql_other_comb = "SELECT comb_id 
										FROM 
											combo_products_variable_combination 
										WHERE 
											comb_id <> ".$row_comb['comb_id']." 
											AND combo_products_comboprod_id = ".$row_combprod['comboprod_id'];
					$ret_other_comb = $db->query($sql_other_comb);
					if($db->num_rows($ret_other_comb))
					{
						while ($row_other_comb = $db->fetch_array($ret_other_comb))
						{
							$comb_already_exists = combination_already_exists($row_other_comb['comb_id'],$var_arr,false);
							if($comb_already_exists)
							{
								// Delete current combination and all its details
								$sql_del = "DELETE FROM 
												combo_products_variable_combination_map 
											WHERE 
												combo_products_variable_combination_comb_id = ".$row_other_comb['comb_id'];
								$db->query($sql_del);
								$sql_del = "DELETE FROM 
												combo_products_variable_combination 
											WHERE 
												comb_id = ".$row_other_comb['comb_id'];
								$db->query($sql_del);				
							}
						}	
					}	
				}
			}
		}
	}
}
function get_combo_variable_arr($combmap_combid)
{
	global 	$ecom_siteid,$db,$ecom_hostname;
	$var_arr = array();
	$sql_combo = "SELECT var_id, var_value_id 
					FROM 
						combo_products_variable_combination_map 
					WHERE 
						combo_products_variable_combination_comb_id = $combmap_combid ";
	$ret_combo = $db->query($sql_combo);
	if($db->num_rows($ret_combo))
	{
		while ($row_combo = $db->fetch_array($ret_combo))
		{
			$var_arr[$row_combo['var_id']] = $row_combo['var_value_id'];
		}
	}
	return $var_arr;
}
function get_promotional_variable_arr($combmap_combid)
{
	global 	$ecom_siteid,$db,$ecom_hostname;
	$var_arr = array();
	$sql_combo = "SELECT var_id, var_value_id 
					FROM 
						promotional_code_products_variable_combination_map  
					WHERE 
						promotional_code_products_variable_combination_comb_id = $combmap_combid ";
	$ret_combo = $db->query($sql_combo);
	if($db->num_rows($ret_combo))
	{
		while ($row_combo = $db->fetch_array($ret_combo))
		{
			$var_arr[$row_combo['var_id']] = $row_combo['var_value_id'];
		}
	}
	return $var_arr;
}
function check_and_deactivate_combo_deal_by_productid($product_id)
{
	global $db,$ecom_siteid;
	// Get the combo_id to which the current products are related
	$sql_comb = "SELECT combo_combo_id 
					FROM 
						combo_products 
					WHERE 
						products_product_id = $product_id";
	$ret_comb = $db->query($sql_comb);
	if($db->num_rows($ret_comb))
	{
		while ($row_comb = $db->fetch_array($ret_comb))
		{
			Change_Combo_Active_status($row_comb['combo_combo_id'],0);
		}
	}
}
function check_and_deactivate_promotional_code_by_productid($product_id)
{
	global $db,$ecom_siteid;
	// Get the promotional_code_code_id to which the current products are related
	$sql_comb = "SELECT promotional_code_code_id 
					FROM 
						promotional_code_product  
					WHERE 
						products_product_id = $product_id";
	$ret_comb = $db->query($sql_comb);
	if($db->num_rows($ret_comb))
	{
		while ($row_comb = $db->fetch_array($ret_comb))
		{
			Change_Promotional_Active_status($row_comb['promotional_code_code_id'],1);
		}
	}
}
// Check whether the number of variables with values equal the number of var with values in each of the combinations in a given combo deal
function check_count_of_var_with_value_in_combo($combo_id,$deactivate=false)
{
	global $db,$ecom_siteid;
	$alert = '';
	// Get the total number of variables
	$sql_combo = "SELECT comboprod_id,products_product_id,combo_combo_id   
					FROM 
						combo_products 
					WHERE 
						combo_combo_id = $combo_id ";
	$ret_combo = $db->query($sql_combo);
	if($db->num_rows($ret_combo))
	{
		while ($row_combo = $db->fetch_array($ret_combo))
		{
			// Get all comboinations of current product map
			$sql_combination = "SELECT comb_id, combo_products_comboprod_id, products_product_id 
									FROM 
										combo_products_variable_combination 
									WHERE 
										combo_products_comboprod_id = ".$row_combo['comboprod_id'];
			$ret_combination = $db->query($sql_combination);
			while ($row_combination = $db->fetch_array($ret_combination))
			{
				$curprodid = $row_combination['products_product_id'];
				$var_arr = array();
				// Get the variable details for current product
				$sql_curvar = "SELECT var_id 
									FROM 
										product_variables 
									WHERE 
										products_product_id = $curprodid 
										AND var_hide=0 
										AND var_value_exists = 1";
				$ret_curvar = $db->query($sql_curvar);
				if($db->num_rows($ret_curvar))
				{
					while ($row_curvar = $db->fetch_array($ret_curvar))
					{
						$var_arr[] = $row_curvar['var_id'];
					}
				}
				if(count($var_arr))
				{
					// Check whether each of the variables with values exists are there in all combination of current mapping
					foreach ($var_arr as $k=>$v)
					{
						$sql_check = "SELECT combo_products_variable_combination_comb_id 
										FROM 
											combo_products_variable_combination_map 
										WHERE 
										  	combo_products_variable_combination_comb_id = ".$row_combination['comb_id']." 
											AND var_id = $v 
										LIMIT 
											1";
						$ret_check = $db->query($sql_check);
						if($db->num_rows($ret_check)==0)
						{
							$alert = "Some of the variables with values are not selected for certain combinations in 'Selected Variable Combinations' section";
							if($deactivate==true)// Check whether combo is to be deactivated 
							{
								Change_Combo_Active_status($combo_id,0);
							}
							return $alert;
						}
					}
					
				}
			}
		}
	}
	return $alert;
}
// Check whether the number of variables with values equal the number of var with values in each of the combinations in a given promotional code 
function check_count_of_var_with_value_in_promotionalcode($code_id,$deactivate=false)
{
	global $db,$ecom_siteid;
	$alert = '';
	// Get the total number of variables
	$sql_code = "SELECT pcode_det_id,products_product_id,promotional_code_code_id    
					FROM 
						promotional_code_product  
					WHERE 
						promotional_code_code_id = $code_id ";
	$ret_code = $db->query($sql_code);
	if($db->num_rows($ret_code))
	{
		while ($row_code = $db->fetch_array($ret_code))
		{
			// Get all comboinations of current product map
			$sql_combination = "SELECT comb_id, promotional_code_product_pcode_det_id, products_product_id 
									FROM 
										promotional_code_products_variable_combination  
									WHERE 
										promotional_code_product_pcode_det_id = ".$row_code['pcode_det_id'];
			$ret_combination = $db->query($sql_combination);
			while ($row_combination = $db->fetch_array($ret_combination))
			{
				$curprodid = $row_combination['products_product_id'];
				$var_arr = array();
				// Get the variable details for current product
				$sql_curvar = "SELECT var_id 
									FROM 
										product_variables 
									WHERE 
										products_product_id = $curprodid 
										AND var_hide=0 
										AND var_value_exists = 1";
				$ret_curvar = $db->query($sql_curvar);
				if($db->num_rows($ret_curvar))
				{
					while ($row_curvar = $db->fetch_array($ret_curvar))
					{
						$var_arr[] = $row_curvar['var_id'];
					}
				}
				if(count($var_arr))
				{
					// Check whether each of the variables with values exists are there in all combination of current mapping
					foreach ($var_arr as $k=>$v)
					{
						$sql_check = "SELECT promotional_code_products_variable_combination_comb_id  
										FROM 
											promotional_code_products_variable_combination_map  
										WHERE 
										  	promotional_code_products_variable_combination_comb_id = ".$row_combination['comb_id']." 
											AND var_id = $v 
										LIMIT 
											1";
						$ret_check = $db->query($sql_check);
						if($db->num_rows($ret_check)==0)
						{
							$alert = "Some of the variables with values are not selected for certain combinations in 'Selected Variable Combinations' section";
							if($deactivate==true)// Check whether combo is to be deactivated 
							{
								Change_Promotional_Active_status($code_id,1);
							}
							return $alert;
						}
					}
					
				}
			}
		}
	}
	return $alert;
}
function check_promotionalcode_integrity()
{
	global $db,$ecom_siteid;
	// Get all promotional codes existing in current website which are of product type
	$sql_code = "SELECT code_id  
					FROM 
						promotional_code   
					WHERE 
						sites_site_id = $ecom_siteid 
						AND code_type='product'";
	$ret_code = $db->query($sql_code);
	if($db->num_rows($ret_code))
	{
		while ($row_code = $db->fetch_array($ret_code))
		{
			$deactivate = false;
			$codeid = $row_code['code_id'];
			$curr_comb_arr = $comb_delete_arr = array();
			// Check whether all combination mappings for current promotional code is value is valid
			$sql_sel = "SELECT pcode_det_id, products_product_id  
							FROM 
								promotional_code_product  
							WHERE 
							 	promotional_code_code_id = $codeid";
			$ret_sel = $db->query($sql_sel);
			if($db->num_rows($ret_sel))
			{
				while ($row_sel = $db->fetch_array($ret_sel))
				{
					// Get all combination id 
					$sql_comb = "SELECT comb_id 
									FROM 
										promotional_code_products_variable_combination  
									WHERE 
										promotional_code_product_pcode_det_id = ".$row_sel['pcode_det_id'];
					$ret_comb = $db->query($sql_comb);
					if($db->num_rows($ret_comb))
					{
						while ($row_comb = $db->fetch_array($ret_comb))
						{
							// Get the var_id and var_value_id related
							$sql_combdet = "SELECT var_id,var_value_id,products_product_id  
												FROM 
													promotional_code_products_variable_combination_map  
												WHERE 
													promotional_code_products_variable_combination_comb_id = ".$row_comb['comb_id'];
							$ret_combdet = $db->query($sql_combdet);
							if($db->num_rows($ret_combdet))
							{
								while ($row_combdet = $db->fetch_array($ret_combdet))
								{
									$val_exists 		= 0;
									$delete_required 	= false;
									// Get the details of current variable from product_variables table
									$sql_variable_check = "SELECT var_id, var_value_exists 
																FROM 
																	product_variables 
																WHERE 
																	var_id = ".$row_combdet['var_id']." 
																	AND var_hide = 0 
																	AND products_product_id = ".$row_sel['products_product_id']." 
																LIMIT 
																	1";
									$ret_variable_check = $db->query($sql_variable_check);
									if($db->num_rows($ret_variable_check)==0)
									{
										$delete_required = true;
									}
									else
									{
										$row_variable_check = $db->fetch_array($ret_variable_check);
										$val_exists 		= $row_variable_check['var_value_exists']; 
									}
									if($val_exists==1) // case if values exists for current variable
									{
										// Check whether current value id is valid
										$sql_varcheck = "SELECT var_value_id 
															FROM 
																product_variable_data 
															WHERE 
																product_variables_var_id = ".$row_combdet['var_id']." 
																AND var_value_id = ".$row_combdet['var_value_id']." 
															LIMIT 
																1";
										$ret_varcheck = $db->query($sql_varcheck);
										if($db->num_rows($ret_varcheck)==0)
										{
											$delete_required = true;
										}
									}
									if($delete_required==true)
									{
										// delete from combo_products_variable_combination_map table
										$sql_del = "DELETE 
														FROM 
															promotional_code_products_variable_combination_map 
														WHERE 
															var_id = ".$row_combdet['var_id']." 
															AND var_value_id=".$row_combdet['var_value_id']." 
															AND promotional_code_products_variable_combination_comb_id=".$row_comb['comb_id'];
										$ret_del = $db->query($sql_del);
										// Check whether atleast one entry exists against current combination id in promotional_code_products_variable_combination_map after deletion
										$sql_cnts = "SELECT promotional_code_products_variable_combination_comb_id 
														FROM 
															promotional_code_products_variable_combination_map  
														WHERE 
															promotional_code_products_variable_combination_comb_id = ".$row_comb['comb_id'];
										$ret_cnts = $db->query($sql_cnts);
										if($db->num_rows($ret_cnts)==0)
										{
											// Delete the respective combination details from romotional_code_products_variable_combination table
											$comb_delete_arr[] = $row_comb['comb_id'];
										} 
									}
								}
							}
						}
					}
				}
			}		
			//Deleting the floating combination details which had occured after above deletion (if required)	
			if(count($comb_delete_arr))
			{
				$del_str = implode(",",$comb_delete_arr);
				$sql_del = "DELETE FROM 
								promotional_code_products_variable_combination  
							WHERE 
								comb_id IN ($del_str)";
				$db->query($sql_del);
			}			
			// Check whether there exists same combinations in current promotional code
			check_and_delete_repeated_promotionalcode_combinations($codeid);
			
			// Check whether there exists other combo with the same details
			/*if(check_same_combo_exist($codeid))
			{
				$deactivate = true;				
			}*/
			// Check whether atleast one combination exists for products in current combo
			$err = check_atleast_one_promotionalcombination($codeid);
			if($err!='')
				$deactivate = true;
			if($deactivate==true)
			{
				// deactivate current combo
				Change_Promotional_Active_status($codeid,1);
			}
		}
	}
}
function check_and_delete_repeated_promotionalcode_combinations($code_id)
{	
	global $ecom_siteid,$db,$ecom_hostname;
	// Get all the products assigned to current promotional code
	$sql_codeprod = "SELECT pcode_det_id, products_product_id
						FROM 
							promotional_code_product  
						WHERE 
							promotional_code_code_id = ".$code_id;
	$ret_codeprod = $db->query($sql_codeprod);
	if($db->num_rows($ret_codeprod))
	{
		while ($row_codeprod = $db->fetch_array($ret_codeprod))
		{
			// Get the combinations available for the mappings
			$sql_comb = "SELECT comb_id 
							FROM 
								promotional_code_products_variable_combination  
							WHERE 
								promotional_code_product_pcode_det_id = ".$row_codeprod['pcode_det_id'];
			$ret_comb = $db->query($sql_comb);
			if($db->num_rows($ret_comb))
			{
				while ($row_comb = $db->fetch_array($ret_comb))
				{
					$var_arr = get_promotional_variable_arr($row_comb['comb_id']);
					// get all combinations other than the current one
					$sql_other_comb = "SELECT comb_id 
										FROM 
											promotional_code_products_variable_combination 
										WHERE 
											comb_id <> ".$row_comb['comb_id']." 
											AND promotional_code_product_pcode_det_id = ".$row_codeprod['pcode_det_id'];
					$ret_other_comb = $db->query($sql_other_comb);
					if($db->num_rows($ret_other_comb))
					{
						while ($row_other_comb = $db->fetch_array($ret_other_comb))
						{
							$comb_already_exists = promotional_combination_already_exists($row_other_comb['comb_id'],$var_arr,false);
							if($comb_already_exists)
							{
								// Delete current combination and all its details
								$sql_del = "DELETE FROM 
												promotional_code_products_variable_combination_map  
											WHERE 
												promotional_code_products_variable_combination_comb_id = ".$row_other_comb['comb_id'];
								$db->query($sql_del);
								$sql_del = "DELETE FROM 
												promotional_code_products_variable_combination  
											WHERE 
												comb_id = ".$row_other_comb['comb_id'];
								$db->query($sql_del);				
							}
						}	
					}	
				}
			}
		}
	}
}
function check_productIntegrity($prod_id)
{
	global $db, $ecom_siteid,$ecom_hostname;
	$addprice_condition = '';
	// Check whether still exists variables for this product which are not hidden
	$sql_check = "SELECT var_id 
						FROM 
							product_variables 
						WHERE 
							products_product_id = ".$prod_id." 
							AND var_hide = 0 
						LIMIT 
							1";
	$ret_check = $db->query($sql_check);
	if ($db->num_rows($ret_check)==0)
	{
		$addprice_condition = " product_variables_exists = 'N' ";
	}	
	else
		$addprice_condition = " product_variables_exists = 'Y' ";			
	
	// Check whether still exists variables for this product which are not hidden
	$sql_check = "SELECT var_id 
						FROM 
							product_variables 
						WHERE 
							products_product_id = ".$prod_id." 
							AND var_hide = 0 
							AND var_value_exists=1 
						LIMIT 
							1";
	$ret_check = $db->query($sql_check);
	if ($db->num_rows($ret_check)==0)
	{
		if($addprice_condition!='')
			$addprice_condition.= ',';
		$addprice_condition .= " product_variablecombocommon_image_allowed='N',
									product_variablecomboprice_allowed='N',
									product_variablestock_allowed='N',
									product_variableweight_allowed='N' "; 
	}
	// Check whether there exists atleast one variable for this product with additional price set
	$sql_price = "SELECT a.var_id 
						FROM 
							product_variables a LEFT JOIN product_variable_data b  
							ON (a.var_id=b.product_variables_var_id)
						WHERE 
							a.products_product_id = ".$prod_id."  
							AND a.var_hide=0 
							AND (b.var_addprice>0  OR a.var_price>0)
						LIMIT 1";
	$ret_price = $db->query($sql_price);
	if($db->num_rows($ret_price))
	{
		if($addprice_condition!='')
			$addprice_condition .= ",product_variablesaddonprice_exists ='Y' ";
		else
			$addprice_condition = " product_variablesaddonprice_exists ='Y' ";
	}	
	else
	{
		if($addprice_condition!='')
			$addprice_condition .= ",product_variablesaddonprice_exists ='N' ";
		else
			$addprice_condition = " product_variablesaddonprice_exists ='N' ";
	}
	if($addprice_condition!='')
	{
		$update_sql = "UPDATE 
							products 
						SET 
							$addprice_condition 
						WHERE 
							product_id = $prod_id 
						LIMIT 
							1";
		$db->query($update_sql);
	}	
}
function check_productIntegrity_including_varstock($prod_id)
{
	global $db, $ecom_siteid,$ecom_hostname;
	$addprice_condition = '';
	// Check whether still exists variables for this product which are not hidden
	$sql_check = "SELECT var_id 
						FROM 
							product_variables 
						WHERE 
							products_product_id = ".$prod_id." 
							AND var_hide = 0 
						LIMIT 
							1";
	$ret_check = $db->query($sql_check);
	if ($db->num_rows($ret_check)==0)
	{
		$addprice_condition = " product_variables_exists = 'N' ";
	}	
	else
		$addprice_condition = " product_variables_exists = 'Y' ";			
	
	// Check whether still exists variables for this product which are not hidden
	$sql_check = "SELECT var_id 
						FROM 
							product_variables 
						WHERE 
							products_product_id = ".$prod_id." 
							AND var_hide = 0 
							AND var_value_exists=1 
						LIMIT 
							1";
	$ret_check = $db->query($sql_check);
	if ($db->num_rows($ret_check)==0)
	{
		if($addprice_condition!='')
			$addprice_condition.= ',';
		$addprice_condition .= " product_variablecombocommon_image_allowed='N',
								 product_variablecomboprice_allowed='N',
								 product_variablestock_allowed='N' "; 
		// Calling function to delete the variable combination stock  (if any exists)
		delete_var_stock($prod_id);
	}
	// Check whether there exists atleast one variable for this product with additional price set
	$sql_price = "SELECT a.var_id 
						FROM 
							product_variables a LEFT JOIN product_variable_data b  
							ON (a.var_id=b.product_variables_var_id)
						WHERE 
							a.products_product_id = ".$prod_id."  
							AND a.var_hide=0 
							AND (b.var_addprice>0  OR a.var_price>0)
						LIMIT 1";
	$ret_price = $db->query($sql_price);
	if($db->num_rows($ret_price))
	{
		if($addprice_condition!='')
			$addprice_condition .= ",product_variablesaddonprice_exists ='Y' ";
		else
			$addprice_condition = " product_variablesaddonprice_exists ='Y' ";
	}	
	else
	{
		if($addprice_condition!='')
			$addprice_condition .= ",product_variablesaddonprice_exists ='N' ";
		else
			$addprice_condition = " product_variablesaddonprice_exists ='N' ";
	}
	if($addprice_condition!='')
	{
		$update_sql = "UPDATE 
							products 
						SET 
							$addprice_condition 
						WHERE 
							product_id = $prod_id 
						LIMIT 
							1";
		$db->query($update_sql);
	}	
}
function delete_var_stock($prod_id,$update_var_details=false)
{
	global $db, $ecom_siteid,$ecom_hostname;
	// 	Get all the combinations if any for current product
	$sql_comb = "SELECT comb_id 
					FROM 
						product_variable_combination_stock 
					WHERE 
						products_product_id = $prod_id";
	$ret_comb = $db->query($sql_comb);
	if($db->num_rows($ret_comb))
	{
		while ($row_comb = $db->fetch_array($ret_comb))
		{
			// deleting from images_variable_combination table against current combid
			$sql_del = "DELETE FROM 
								images_variable_combination 
							WHERE 
								comb_id=".$row_comb['comb_id'];
			$db->query($sql_del); 
			
			// deleting from product_bulkdiscount table against current combid
			$sql_del = "DELETE FROM 
								product_bulkdiscount 
							WHERE 
								comb_id=".$row_comb['comb_id']." 
								AND products_product_id = $prod_id";
			$db->query($sql_del);
			
			// deleting from product_shop_variable_combination_stock table against current combid
			$sql_del = "DELETE FROM 
								product_shop_variable_combination_stock  
							WHERE 
								comb_id=".$row_comb['comb_id']." 
								AND products_product_id = $prod_id";
			$db->query($sql_del); 
		}
	}
	// deleting from product_variable_combination_stock_details table against product id
	$sql_del = "DELETE FROM 
						product_variable_combination_stock_details  
					WHERE 
						products_product_id = $prod_id";
	$db->query($sql_del);
	// deleting from product_variable_combination_stock  table against product id
	$sql_del = "DELETE FROM 
						product_variable_combination_stock   
					WHERE 
						products_product_id = $prod_id";
	$db->query($sql_del);
	if($update_var_details==true)
	{
		$update_sql = "UPDATE products 
						SET 
							product_variablecombocommon_image_allowed='N',
							product_variablecomboprice_allowed='N',
							product_variablestock_allowed='N'  
						WHERE 
							product_id=$prod_id 
							AND sites_site_id = $ecom_siteid 
						LIMIT 
							1";
		$db->query($update_sql);
	}
}
function check_productvariable_consistancy($product_id)
{
	global $db, $ecom_siteid,$ecom_hostname;
	$del_arr = array();
	// Get all variables with values for current product
	$sql_var = "SELECT var_id 
					FROM 
						product_variables 
					WHERE 
						products_product_id = $product_id 
						AND var_value_exists=1";
	$ret_var = $db->query($sql_var);
	if($db->num_rows($ret_var))
	{
		while ($row_var = $db->fetch_array($ret_var))
		{
			// Check atleast one value exist for current variable
			$sql_valcheck = "SELECT var_value_id 
								FROM 
									product_variable_data 
								WHERE 
									product_variables_var_id=".$row_var['var_id']." 
								LIMIT 
									1";
			$ret_valcheck = $db->query($sql_valcheck);
			if($db->num_rows($ret_valcheck)==0) // case if values does not exists
			{
				// store the var id in delete array to delete it
				$del_arr[] = $row_var['var_id'];
			}
		}
		//Physically Deleting the variables which are set to have values but at present no values exists
		for($i=0;$i<count($del_arr);$i++)
		{
			$sql_del = "DELETE FROM 
							product_shop_variables 
						WHERE 
							var_id = ".$del_arr[$i]." 
							AND products_product_id = $product_id";
			$db->query($sql_del);
			$sql_del = "DELETE FROM 
							product_variables  
						WHERE 
							var_id = ".$del_arr[$i]." 
							AND products_product_id = $product_id";
			$db->query($sql_del);
		}
	} 
}
function ChangeStatus_customer_discount_group_global($group_id,$stat=0)
{
        global $db,$ecom_siteid;
        $sql_update = "UPDATE customer_discount_group 
                                        SET 
                                                cust_disc_grp_active = $stat 
                                        WHERE
                                                cust_disc_grp_id = $group_id 
                                                AND sites_site_id = $ecom_siteid 
                                        LIMIT 
                                                1";
        $db->query($sql_update);
}
  function check_customer_discountgroup_integrity()
  {
     global $db,$ecom_siteid;
         
     // Get the list of all customer discount groups
     $sql_cust = "SELECT cust_disc_grp_id 
                    FROM 
                        customer_discount_group  
                    WHERE 
                        sites_site_id = $ecom_siteid";
                        
     $ret_cust = $db->query($sql_cust);
     if($db->num_rows($ret_cust))
     {
        while ($row_cust = $db->fetch_array($ret_cust))
        {
                
                $group_id       = $row_cust['cust_disc_grp_id'];
                $deactivate     = false;
                // Check whether there exists atleast one customer in current customer discount group
                $sql_map = "SELECT map_id  
                              FROM 
                                customer_discount_customers_map  
                              WHERE 
                                customer_discount_group_cust_disc_grp_id = $group_id  
                              LIMIT 
                                1";
                $ret_map = $db->query($sql_map);
                if($db->num_rows($ret_map)==0) // case if no customers
                {
                  $deactivate = true;
                }
                // Check whether there exists atleast one product in current customer discount group
                $sql_prod = "SELECT map_id  
                              FROM 
                                customer_discount_group_products_map   
                              WHERE 
                                customer_discount_group_cust_disc_grp_id = $group_id   
                              LIMIT 
                                1";
                $ret_prod = $db->query($sql_prod);
                if($db->num_rows($ret_prod)==0) // case if no products  
                {
                        $deactivate = true;
                }
                if($deactivate==true)
                {
                   ChangeStatus_customer_discount_group_global($group_id,0);      
                }
                
        }
     }
  }
  /*function check_product_label_integrity()
  {
	global $db,$ecom_siteid;
	// Get all the labels existing in current website
	$sql_label = "SELECT label_id 
					FROM 
						product_site_labels  
					WHERE 
						sites_site_id = $ecom_siteid";
	$ret_label = $db->query($sql_label);
	if($db->num_rowS($ret_label))
	{
		while ($row_label = $db->fetch_array($ret_label))
		{
			
		}
	}						
  }*/
  function price_promise_status($stat)
  {
  	switch ($stat)
	{
		case 'Accept':
			$msg = 'Accepted';
		break;
		case 'Reject':
			$msg = 'Rejected';
		break;
		default:
			$msg = $stat;
		break;
	};
	return $msg;
  }
  
function write_email_as_file($mod,$id,$content)
{
	global $image_path,$db;
	$fname 	= $id.'.txt';
	$folder = '';
	switch($mod)
	{
		case 'ord':
			if(!is_dir($image_path.'/email_messages'))
			{
			 	mkdir($image_path.'/email_messages',0777);
			} 
			if(!is_dir($image_path.'/email_messages/order_emails'))
			{
			 	mkdir($image_path.'/email_messages/order_emails',0777);
			} 
			$folder	= 'email_messages/order_emails';
			$sql_update = "UPDATE order_emails 
							SET 
								email_messagepath='".$folder.'/'.$fname."' 
							WHERE 
								email_id = $id 
							LIMIT 
								1";
			$db->query($sql_update);
		break;
		case 'vouch':
			if(!is_dir($image_path.'/email_messages'))
			{
			 	mkdir($image_path.'/email_messages',0777);
			} 
			if(!is_dir($image_path.'/email_messages/gift_voucher_emails'))
			{
			 	mkdir($image_path.'/email_messages/gift_voucher_emails',0777);
			} 
			$folder	= 'email_messages/gift_voucher_emails';
			$sql_update = "UPDATE gift_voucher_emails  
							SET 
								email_messagepath='".$folder.'/'.$fname."' 
							WHERE 
								email_id = $id 
							LIMIT 
								1";
			$db->query($sql_update);
		break;
	};
	if($folder)
	{
		$fp = fopen($image_path.'/'.$folder.'/'.$fname,'w');
		fwrite($fp,$content);
		fclose($fp);
	}
}
function read_email_from_file($mod,$id)
{
	global $image_path,$db;
	switch($mod)
	{
		case 'ord':
			$sql_sel = "SELECT email_messagepath 
							FROM 
								order_emails 
							WHERE 
								email_id = $id 
							LIMIT 
								1";
			$ret_sel = $db->query($sql_sel);
			if($db->num_rows($ret_sel))
			{
				$row_sel = $db->fetch_array($ret_sel);
				$file_path = $row_sel['email_messagepath'];
			}
		break;
		case 'vouch':
			$sql_sel = "SELECT email_messagepath 
							FROM 
								gift_voucher_emails  
							WHERE 
								email_id = $id 
							LIMIT 
								1";
			$ret_sel = $db->query($sql_sel);
			if($db->num_rows($ret_sel))
			{
				$row_sel = $db->fetch_array($ret_sel);
				$file_path = $row_sel['email_messagepath'];
			}
		break;
	};	
	// read the contents of file
	$full_file_path = $image_path.'/'.$file_path;
	$fp = fopen($full_file_path,'r');
	$content = fread($fp,filesize($full_file_path));
	fclose($fp);
	return $content;
}

function handle_recalculate_specialtax_calculation($order_id)
{
	global $db,$ecom_siteid,$ecom_allpricewithtax;
	// do the following only if special tax calculation is required
	if($ecom_allpricewithtax==1)
	{
		// Get the list of items in order_details table for current order
		$sql_det = "SELECT * FROM 
					order_details 
				WHERE 
					orders_order_id = $order_id 
				ORDER BY 
					orderdet_id";
		$ret_det = $db->query($sql_det);
		while ($row_det = $db->fetch_array($ret_det))
		{
			$org_qty = $row_det['order_orgqty'];
			$rem_qty = $row_det['order_qty'];
			$valid_qty = 0;
			$backqty = 0;
			$despatch_return_qty = 0;
			$refund_qty = 0;
			if($rem_qty<>$org_qty) // case if orginal qty and remaining qty are different. So need to find what hapnd to the remaining qty
			{
				// Check whether remaining qty is there in the back order for current orderdet_id
				$sql_back = "SELECT sum(backorder_qty) as backqty  
						FROM 
							order_details_backorder 
						WHERE 
							orderdet_id = ".$row_det['orderdet_id'];
				$ret_back = $db->query($sql_back);
				if($db->num_rows($ret_back))
				{
					$row_back = $db->fetch_array($ret_back);
					$backqty = $row_back['backqty'];
				}
				
				// Check whether qty is there in the despatched table for current orderdet_id
				$sql_back = "SELECT (despatched_qty-despatched_returned_qty) as despatch_return_qty   
						FROM 
							order_details_despatched  
						WHERE 
							orderdet_id = ".$row_det['orderdet_id'];
				$ret_back = $db->query($sql_back);
				if($db->num_rows($ret_back))
				{
					$row_back = $db->fetch_array($ret_back);
					$despatch_return_qty = $row_back['despatch_return_qty'];
				}
			
				$valid_qty = $rem_qty + $backqty + $despatch_return_qty;
				
				if($rem_qty>0)
				{
					$total_product_amount_tax 	 =  ($row_det['orderdet_specialtax_orgproductamt']/$org_qty)*$valid_qty;
					$total_product_extrashipping_tax =  ($row_det['orderdet_specialtax_orgextrashippingamt']/$org_qty)*$valid_qty;
				}
				else
				{
					$total_product_amount_tax 	 = 0;
					$total_product_extrashipping_tax = 0;
				}
				// update the order_taxcalc_qty field in order_details table with this qty value
				$update_det = "UPDATE order_details 
						SET 
							order_taxcalc_qty = $valid_qty,
							orderdet_specialtax_productamt = $total_product_amount_tax,
							orderdet_specialtax_extrashippingamt = $total_product_extrashipping_tax  
						WHERE 
							orderdet_id =".$row_det['orderdet_id']." 
						LIMIT 
							1";
				$db->query($update_det);
				
				
			}
		}
		// sql to get the totals of various tax totals
		$sql_dettot = "SELECT sum(orderdet_specialtax_productamt) prodamt_tax,
					sum(orderdet_specialtax_extrashippingamt) extra_tax 
				FROM 
					order_details 
				WHERE 
					orders_order_id = $order_id";
		$ret_dettot = $db->query($sql_dettot);
		if($db->num_rows($ret_dettot))
		{
			$row_dettot = $db->fetch_array($ret_dettot);
			$prodamt_tax = $row_dettot['prodamt_tax'];
			$extra_tax = $row_dettot['extra_tax'];			
		}
		// Updating the tax related pages in orders table for current order id
		$sql_ord = "UPDATE orders 
				SET 
					order_specialtax_productamt = $prodamt_tax,
					order_specialtax_extrashippingamt =$extra_tax
				WHERE 
					order_id = $order_id 
				LIMIT 
					1";
		$db->query($sql_ord);
			
		// Update the total fields in order table for current order
		$sql_update = "UPDATE orders 
				SET 
					order_specialtax_totalamt = order_specialtax_productamt + order_specialtax_deliveryamt + order_specialtax_extrashippingamt
				WHERE 
					order_id = $order_id 
				LIMIT 
					1";	
		$db->query($sql_update);					
	}	
}




function copy_attachment($filename,$img_id)
{
	global $image_path;
	$attach_path = "$image_path/attachments";
	if(!file_exists($attach_path)) mkdir($attach_path, 0777);
	$ext_arr = explode(".",$_FILES[$filename]['name']);
	$len = count($ext_arr)-1;
	$fname = $img_id.".".$ext_arr[$len];
	$res = move_uploaded_file($_FILES[$filename]['tmp_name'],$attach_path.'/'.$fname);
	if (!$res)
	{
		$ret_arr['alert'] 		= 'Upload Failed';
	}
	else
	{
		$ret_arr['alert'] 		= '';
		$ret_arr['extension'] 	= $ext_arr[$len];
		$ret_arr['filename'] 	= $fname;
	}
	return $ret_arr;
}
function copy_attachment_icon($filename,$img_id)
{
	global $image_path;
	$attach_path = "$image_path/attachments/icons";
	if(!file_exists($attach_path)) mkdir($attach_path, 0777);
	$ext_arr = explode(".",$_FILES[$filename]['name']);
	$len = count($ext_arr)-1;
	$fname = $img_id.".".$ext_arr[$len];
	$res = move_uploaded_file($_FILES[$filename]['tmp_name'],$attach_path.'/'.$fname);
	if (!$res)
	{
		$ret_arr['alert'] 		= 'Icon Upload Failed';
	}
	else
	{
		$ret_arr['alert'] 		= '';
		$ret_arr['extension'] 	= $ext_arr[$len];
		$ret_arr['filename'] 		= $fname;
	}
	return $ret_arr;
}
function generateselectboxoption($name,$option_values,$selected,$moboption_values,$mobselected,$onblur='',$onchange='',$multiple=0) {
	global $catSET_WIDTH;
	if(!is_array($option_values)) return ;
	$return_value = "<select name='$name' id='$name'";
	if ($multiple > 0) $return_value .= " multiple='multiple' size='$multiple'";
	if($onblur) {
		$return_value .= " onblur='$onblur'";
	}
	if($onchange) {
		$return_value .= " onchange='$onchange'";
	}
	if($onclick) {
		$return_value .= " onclick='$onclick'";
	}
	if($catSET_WIDTH!='')
		$return_value .=' style="width:'.$catSET_WIDTH.'"';
	$return_value .= ">";
	if(count($option_values)>0)
	{
		$return_value .= " <optgroup label='Website Layout'>";
	}
	
	foreach($option_values as $k => $v) {		
		if(is_array($selected)) {
			if(in_array($k,$selected)) {
				$return_value .= "<option value='$k' selected>$v</option>";
			} else {
				$return_value .= "<option value='$k'>$v</option>";
			}
		} else {
			if($selected == $k) {
				$return_value .= "<option value='$k' selected>$v</option>";
			} else {
				$return_value .= "<option value='$k'>$v</option>";
			}
		}
	}
	if(count($option_values)>0)
	{
		$return_value .= " </optgroup>";
	}	
	if(count($moboption_values)>0)
	{
		$return_value .= " <optgroup label='Mobile Layout'>";
    }
	foreach($moboption_values as $k => $v) {
		if(is_array($mobselected)) {
			if(in_array($k,$mobselected)) {
				$return_value .= "<option value='$k' selected>$v</option>";
			} else {
				$return_value .= "<option value='$k'>$v</option>";
			}
		} else {
			if($mobselected == $k) {
				$return_value .= "<option value='$k' selected>$v</option>";
			} else {
				$return_value .= "<option value='$k'>$v</option>";
			}
		}
	}
	if(count($moboption_values)>0)
	{
			$return_value .= " </optgroup>";
	}		

	$return_value .= "</select>";
	return $return_value;
}

function is_product_variable_weight_active()
{
	global $db,$ecom_siteid;
	$enable = false;
	$sql_set = "SELECT enable_variable_weight FROM general_settings_sites_common_onoff WHERE sites_site_id = $ecom_siteid LIMIT 1";
	$ret_set = $db->query($sql_set);
	if($db->num_rows($ret_set))
	{
		$row_set = $db->fetch_array($ret_set);
		if($row_set['enable_variable_weight']==1)
		{
			$enable = true;
		}
	}	
	return $enable;
}


function is_delivery_group_free_delivery_active()
{
	global $db,$ecom_siteid;
	$enable = false;
	$sql_set = "SELECT enable_delivery_group_free_delivery FROM general_settings_sites_common_onoff WHERE sites_site_id = $ecom_siteid LIMIT 1";
	$ret_set = $db->query($sql_set);
	if($db->num_rows($ret_set))
	{
		$row_set = $db->fetch_array($ret_set);
		if($row_set['enable_delivery_group_free_delivery']==1)
		{
			$enable = true;
		}
	}	
	return $enable;
}

function check_show_special_no_auth_msg()
{
	global $db,$ecom_siteid;
	$usr = $_SESSION['console_id'];
	$show_noauth = false;
	$restrict_array = array(
							'customer_search',
							'newsletter_customers',
							'customer_corporation',
							'cust_discount_group',
							'newsletter'							
						);
	if($ecom_siteid ==72) // for disocunt-mobility website
	{ 
		$sql_usr = "SELECT user_email_9568 FROM sites_users_7584 WHERE  sites_site_id=$ecom_siteid AND	user_id = $usr LIMIT 1";
		$ret_usr = $db->query($sql_usr);
		if ($db->num_rows($ret_usr))
		{
			$row_usr = $db->fetch_array($ret_usr);
			if($row_usr['user_email_9568']!='admin@admin.com')
			{
				if(in_array($_REQUEST['request'],$restrict_array))
					$show_noauth = true;
			}
		}
	}
	return $show_noauth;
}
function grid_enablecheck($edit_id)
	{
	global $db,$ecom_siteid,$ecom_gridenable;  //grid display	
	$sql_prod = "SELECT product_variable_display_type,product_variable_in_newrow  
						FROM 
							products 
						WHERE 
							product_id = $edit_id 
						LIMIT 
							1";
		$ret_prod = $db->query($sql_prod);
		if ($db->num_rows($ret_prod))
			$row_prod = $db->fetch_array($ret_prod);
		$grid_proceed = false;
		if($ecom_gridenable ==1)
		{
		   $sql_cat = "SELECT category_id,enable_grid_display FROM  product_categories a,products b where b.product_default_category_id = a.category_id AND b.product_id = $edit_id ";
		   $ret_cat = $db->query($sql_cat);
		   $row_cat = $db->fetch_array($ret_cat);
		   if($row_cat['enable_grid_display']==1)
		   {
		      $grid_proceed = true;
		   }
	    }
	    return $grid_proceed;	
}
/* Function to decide whether grid display is supported for a given category */
	function is_grid_display_enabled($catid)
	{
		global $db,$ecom_siteid,$ecom_gridenable;
		$grid_display_valid = false; // variable which decides whether grid display is enabled or not
		if($ecom_gridenable==1)
			{ 
		// Check whether grid display is active for this category and also variable group is assigned to this category
		$sql_cat = "SELECT product_variables_group_id,enable_grid_display,category_name 
						FROM 
							product_categories 
						WHERE 
							category_id = $catid 
							AND sites_site_id = $ecom_siteid 
							AND category_hide = 0
						LIMIT 
							1";
		$ret_cat = $db->query($sql_cat);
		if($db->num_rows($ret_cat))
		{
			 $row_cat = $db->fetch_array($ret_cat);
			 $def_cat_name = $row_cat['category_name'];

			if($row_cat['enable_grid_display']==1)
			{
				if($row_cat['product_variables_group_id']!=0)
				{
					// Check whether the group is hidden or not
					$sql_vargrp = "SELECT var_group_hide 
									FROM 
										product_variables_group 
									WHERE 
										var_group_id = ".$row_cat['product_variables_group_id']." 
										AND sites_site_id = $ecom_siteid 
										AND var_group_hide = 0 
									LIMIT 
										1";
					$ret_vargrp = $db->query($sql_vargrp);
					if($db->num_rows($ret_vargrp))
					{
						$groupid = $row_cat['product_variables_group_id'];
						// Check whether preset variables assigned to this group is not hidden
						$sql_preset = "SELECT prd_var_group_var_mapid 
										FROM 
											product_variables_group_variables_map a,product_preset_variables b
										WHERE 
											a.sites_site_id = $ecom_siteid 
											AND a.product_variables_id = b.var_id 
											AND a.product_variables_group_id = ".$groupid ."
											AND b.var_hide = 0 
										LIMIT 1";
						$ret_preset = $db->query($sql_preset);
						if($db->num_rows($ret_preset))
						{
						
							// check whether a horizontal variable is set in this group and is not hidden
							$sql_hori = "SELECT product_variables_id,var_name 
										FROM 
											product_variables_group_variables_map a,product_preset_variables b
										WHERE 
											a.sites_site_id = $ecom_siteid 
											AND a.product_variables_id = b.var_id 
											AND a.product_variables_group_id = ".$groupid ."
											AND b.var_hide = 0 
											AND a.prd_var_group_var_horizontal = 1 
										LIMIT 1";
							$ret_hori = $db->query($sql_hori);
							if($db->num_rows($ret_hori))
							{
								$grid_display_valid = true;
								$row_hori 			= $db->fetch_array($ret_hori);
								$hori_varid 		= $row_hori['product_variables_id'];
								$hori_varname 		= $row_hori['var_name'];
								// get the name of horizontal variable
							}	
						}	
					}
				}
			}
		}
		$ret_arr['enabled'] 		= $grid_display_valid;
		$ret_arr['groupid'] 		= $groupid;
		$ret_arr['hori_varid'] 		= $hori_varid;
		$ret_arr['hori_varname'] 	= $hori_varname;
		$ret_arr['def_catid'] 		= $catid;
		$ret_arr['category_name'] 	= $def_cat_name;
		return $ret_arr;
		}
	}
	/* Function to decide whether grid display is supported for a given category */
	function is_grid_display_enabled_prod($prod_id)
	{
		global $db,$ecom_siteid,$ecom_gridenable;
		    $grid_display_valid = false; // variable which decides whether grid display is enabled or not
			if($ecom_gridenable==1)
			{ 
				if($prod_id>0)
				{
				$sql_prod = "SELECT product_name,product_default_category_id FROM products WHERE product_id=".$prod_id." AND sites_site_id=".$ecom_siteid." LIMIT 1";
				$ret_prod = $db->query($sql_prod);
				if ($db->num_rows($ret_prod))
				{ 
				$row_prod    = $db->fetch_array($ret_prod);
				$def_cat_id  = $row_prod['product_default_category_id'];
					if($def_cat_id>0)
					{
						$check_arr   = is_grid_display_enabled($def_cat_id);
					    return $check_arr;
					}
				}				
			}
		}
	}
	function save_product_preset_map($prod_id=0)
	{
			global $db,$ecom_siteid,$ecom_gridenable;			
			//print_r($val_arr);
			$proceed = false;
			$hori_arra = $vert_arra = array();
			if($ecom_gridenable==1)
			{				
				if($prod_id>0)
				{
					$del_sql = "DELETE FROM product_preset_variable_grid_map WHERE products_product_id=$prod_id";
					$db->query($del_sql);
					 $sql_check_var  = "SELECT var_id,preset_variable_id  
					                                FROM 
														product_variables 
													WHERE 
														products_product_id = ".$prod_id." AND preset_variable_id > 0";
					$ret_check_var = $db->query($sql_check_var)	;
					if($db->num_rows($ret_check_var)>0)					
					{						
							while($row_check_var = $db->fetch_array($ret_check_var))
							{
							    $sql_horcheck = "SELECT prd_var_group_var_mapid FROM product_variables_group_variables_map WHERE product_variables_id = ".$row_check_var['preset_variable_id']." AND prd_var_group_var_horizontal=1 LIMIT 1";   
							    $ret_horcheck = $db->query($sql_horcheck);
							    if($db->num_rows($ret_horcheck)>0)
							    {
								  	$proceed = true;
								  	
								}
								else
								{
								    $proceed = false;
								}
								   $sql_check_var_val  = "SELECT 
																var_value_id,preset_variable_value_id 
														  FROM 
																product_variable_data  
														  WHERE 
																product_variables_var_id = ".$row_check_var['var_id']."
														  AND 
																preset_variable_value_id >0";
								 $ret_check_var_val = $db->query($sql_check_var_val);
								 while($row_check_var_val=$db->fetch_array($ret_check_var_val))
								 {
								    if($proceed==true)
								    { 
										$hori_arra[] = $row_check_var_val['preset_variable_value_id'];
									}
									else
									{
										$vert_arra[] = $row_check_var_val['preset_variable_value_id'];
									}
								 }
							}
					}	
					//if($proceed==true)
					{
						$str ='';
						if(is_array($hori_arra) && is_array($vert_arra))
						{
							if(count($hori_arra) and count($vert_arra))
							{
								foreach($hori_arra as $k=>$v)
								{								
										foreach($vert_arra as $ii=>$jj)
										{
											
											   // Making a new entry to the product_variables table 	
												$insert_array										= array();
												$insert_array['products_product_id']				= $prod_id;
												$insert_array['horizontal_preset_var_value_id']		= $v;
												$insert_array['vertical_preset_var_value_id']  	 	= $jj;						
	
												$db->insert_from_array($insert_array,'product_preset_variable_grid_map');
												$cur_var_id = $db->insert_id();						
											   //$str .= $vv."-".$jj[$i]."<br>";									
										}								
								 }
							 }
						//echo $str;
						}
					}					
				}
			}	

	}
	function handle_auto_301($newtitle,$pgid=0,$prodid=0,$catid=0)
	{
		global $db,$ecom_siteid,$ecom_advancedseo;
		//if($ecom_siteid != 102) // done to avoid kqf from the condition.
		//if($ecom_siteid == 44)// only for bshop4.co.uk
		
		if($ecom_siteid != 102) // done to avoid kqf from the condition.
		{
			if($pgid)
			{
				include_once ('functions/console_urls.php');
				// Get the current page title
				$sql_get = "SELECT title FROM static_pages WHERE page_id = $pgid and sites_site_id=$ecom_siteid";
				$ret_get = $db->query($sql_get);
				if($db->num_rows($ret_get))
				{
					$row_get = $db->fetch_array($ret_get);
					$curtitle = stripslashes($row_get['title']);
					if(strtolower($curtitle)!=strtolower($newtitle))
					{
						$oldurl_arr = url_static_page($pgid,$curtitle,1);
						$oldurl = $oldurl_arr[1];
						$newurl_arr = url_static_page($pgid,$newtitle,1);
						$newurl = $newurl_arr[1]; 
						write_to_301_table($oldurl,$newurl);
					}	
				}
			}
			elseif($prodid)
			{
				// Get the current product title
				$sql_get = "SELECT product_name FROM products WHERE product_id = $prodid and sites_site_id=$ecom_siteid";
				$ret_get = $db->query($sql_get);
				if($db->num_rows($ret_get))
				{
					$row_get = $db->fetch_array($ret_get);
					$curtitle = stripslashes($row_get['product_name']);
					if(strtolower($curtitle)!=strtolower($newtitle))
					{
						$oldurl = url_product($prodid,$curtitle,$ecom_advancedseo,1);
						$newurl = url_product($prodid,$newtitle,$ecom_advancedseo,1);
						write_to_301_table($oldurl,$newurl);
					}	
				}
			}
			elseif($catid)
			{
				include_once ('functions/console_urls.php');
				// Get the current category title
				$sql_get = "SELECT category_name FROM product_categories WHERE category_id = $catid and sites_site_id=$ecom_siteid";
				$ret_get = $db->query($sql_get);
				if($db->num_rows($ret_get))
				{
					$row_get = $db->fetch_array($ret_get);
					$curtitle = stripslashes($row_get['category_name']);
					if(strtolower($curtitle)!=strtolower($newtitle))
					{
						$oldurl = url_category($catid,$curtitle,1);
						$newurl = url_category($catid,$newtitle,1);
						write_to_301_table($oldurl,$newurl);
					}	
				}
			}
		}	
		$sql_del = "DELETE FROM seo_redirect WHERE redirect_old_url=redirect_new_url AND sites_site_id=$ecom_siteid";
		$ret_del = $db->query($sql_del);
	}
	function write_to_301_table($oldurl,$newurl)
	{
		global $db,$ecom_siteid;
		
		$insert_redirect = true;
		// Check whether the same entry already exists
		$sql_chk = "SELECT redirect_id FROM seo_redirect WHERE sites_site_id = $ecom_siteid AND (redirect_old_url='".$oldurl."' AND redirect_new_url='".$newurl."') LIMIT 1";
		$ret_chk = $db->query($sql_chk);
		if($db->num_rows($ret_chk))
		{
			$insert_redirect = false;
		}
		
		if($insert_redirect==true)
		{
			// Check whether a reciprocal entry exists
			$sql_chk = "SELECT redirect_id FROM seo_redirect WHERE sites_site_id = $ecom_siteid AND (redirect_old_url='".$newurl."' AND redirect_new_url='".$oldurl."') LIMIT 1";
			$ret_chk = $db->query($sql_chk);
			if($db->num_rows($ret_chk))
			{
				$row_chk = $db->fetch_array($ret_chk);
				$del_chk = "DELETE FROM seo_redirect WHERE sites_site_id = $ecom_siteid AND redirect_id = ".$row_chk['redirect_id']." LIMIT 1";
				$db->query($del_chk);
			}
			
			$redirect_loop = true;
			$loop_arr = array();
			$loop_arr[] = $newurl;
			$loop_arr[] = $oldurl;
			$cur_chkurl = $oldurl;
			while ($redirect_loop)
			{
				// Check an entry exists with the new url as current old url
				$sql_chk = "SELECT redirect_id,redirect_old_url,redirect_new_url 
								FROM 
									seo_redirect 
								WHERE 
									sites_site_id = $ecom_siteid 
									AND redirect_new_url='".$cur_chkurl."' 
								LIMIT 
									1";
				$ret_chk = $db->query($sql_chk);
				if($db->num_rows($ret_chk))
				{
					$row_chk 	= $db->fetch_array($ret_chk);
					if (in_array($row_chk['redirect_old_url'],$loop_arr))
					{
						$del_id = $row_chk['redirect_id'];
						$sql_del = "DELETE FROM seo_redirect WHERE sites_site_id=$ecom_siteid AND redirect_id = ".$row_chk['redirect_id']." LIMIT 1";
						$db->query($sql_del);
						$redirect_loop = false;
					}
					else
					{
						$loop_arr[] = $row_chk['redirect_old_url'];
						$cur_chkurl = $row_chk['redirect_old_url'];
					}
				}	
				else
					$redirect_loop = false;
			}
			//print_r($loop_arr);
		}
		
		
		
		
		
		if($insert_redirect)
		{
			$insert_array 								= array();
			$insert_array['sites_site_id'] 				= $ecom_siteid;
			$insert_array['redirect_old_url'] 			= $oldurl;
			$insert_array['redirect_new_url'] 			= $newurl;
			$insert_array['redirect_last_access_date'] 	= '0000-00-00 00:00:00';
			$insert_array['redirect_autoentry'] 		= 1;
			$db->insert_from_array($insert_array,'seo_redirect');
		}	
		
	}
	function write_to_301_table_old($oldurl,$newurl)
	{
		global $db,$ecom_siteid;
		
		// Check whether the entry already exists
		$sql_chk = "SELECT redirect_id FROM seo_redirect WHERE sites_site_id = $ecom_siteid AND redirect_old_url='".$oldurl."' LIMIT 1";
		$ret_chk = $db->query($sql_chk);
		if($db->num_rows($ret_chk))
		{
			$row_chk = $db->fetch_array($ret_chk);
			$sql_del = "DELETE FROM seo_redirect WHERE redirect_id = ".$row_chk['redirect_id']." and sites_site_id =$ecom_siteid LIMIT 1";
			$db->query($sql_del);
		}
		// Check whether the entry already exists
		$sql_chk = "SELECT redirect_id FROM seo_redirect WHERE sites_site_id = $ecom_siteid AND redirect_new_url='".$oldurl."' LIMIT 1";
		$ret_chk = $db->query($sql_chk);
		if($db->num_rows($ret_chk))
		{
			$row_chk = $db->fetch_array($ret_chk);
			$sql_del = "DELETE FROM seo_redirect WHERE redirect_id = ".$row_chk['redirect_id']." and sites_site_id =$ecom_siteid LIMIT 1";
			$db->query($sql_del);
		}
		
		
	}
	function check_IndividualSslActive() // Check whether ssl is activated for individual domains
	{
		global $db, $ecom_siteid, $ecom_hostname,$ecom_advancedseo,$ecom_selfssl_active;
		if($ecom_selfssl_active==1)
		{
			return true;
		}
	}
	function mask_emails($str)
	{
		global $ecom_siteid;
		if($ecom_siteid==104 or $ecom_siteid==105)
		{
			$em   = explode("@",$str);
			if(strlen($em[0])==1)
			{
				return   '*'.'@'.$em[1];
			}
			$name = implode(array_slice($em, 0, count($em)-1), '@');
			$len  = floor(strlen($name)/2);
			return substr($name,0, $len) . str_repeat('*', $len) . "@" . end($em);
		}
		else
		{
			return $str;
		}
	}
	function mask_emails_orddetails($str)
	{
		global $ecom_siteid;
		if($ecom_siteid==104)
		{
			$em   = explode("@",$str);
			if(strlen($em[0])==1)
			{
				return   '*'.'@'.$em[1];
			}
			$name = implode(array_slice($em, 0, count($em)-1), '@');
			$len  = floor(strlen($name)/2);
			return substr($name,0, $len) . str_repeat('*', $len) . "@" . end($em);
		}
		else
		{
			return $str;
		}
	}
?>
