<?php
if($_REQUEST['fpurpose']=='')
{
	
	$ajax_return_function = 'ajax_return_contents';
	include "ajax/ajax.php";
	include("includes/adverts/list_adverts.php");
}
elseif($_REQUEST['fpurpose']=='change_hide')
	{
		include_once("../functions/functions.php");
		include_once('../session.php');
		include_once("../config.php");	
		$advert_ids_arr 		= explode('~',$_REQUEST['advert_ids']);
		$new_status		= $_REQUEST['ch_status'];
		for($i=0;$i<count($advert_ids_arr);$i++)
		{
			$update_array					= array();
			$update_array['advert_hide']	= $new_status;
			$advert_id 						= $advert_ids_arr[$i];	
			$db->update_from_array($update_array,'adverts',array('advert_id'=>$advert_id));
			// Clearing the cache
			delete_advert_cache($advert_id);
		}
		delete_body_cache();
		$alert = 'Status changed successfully.';
		include ('../includes/adverts/list_adverts.php');
		
}
else if($_REQUEST['fpurpose']=='delete')
{
	
		include_once("../functions/functions.php");
		include_once('../session.php');
		include_once("../config.php");
		if ($_REQUEST['del_ids'] == '')
		{
			$alert = 'Sorry Banner not selected';
		}
		else
		{
		// Find the feature details for module mod_adverts from features table
					$sql_feat = "SELECT feature_id,feature_name FROM features WHERE feature_modulename ='mod_adverts'";
					$ret_feat = $db->query($sql_feat);
					if ($db->num_rows($ret_feat))
					{
						$row_feat 	= $db->fetch_array($ret_feat);
						$cur_featid	= $row_feat['feature_id'];
					}
			$del_arr = explode("~",$_REQUEST['del_ids']);
			for($i=0;$i<count($del_arr);$i++)
			{
				if(trim($del_arr[$i]))
				{
                                    // Get the details of deleting rotate image details 
                                    $sql_det = "SELECT rotate_image 
                                                    FROM 
                                                        advert_rotate 
                                                    WHERE 
                                                        adverts_advert_id=".$del_arr[$i];
                                    $ret_det = $db->query($sql_det);
                                    if($db->num_rows($ret_det))
                                    {
                                        while ($row_det = $db->fetch_array($ret_det))
                                        {
                                            if(file_exists("$image_path/adverts/rotate/".$row_det['rotate_image']))
                                                @unlink ("$image_path/adverts/rotate/".$row_det['rotate_image']);
                                        }        
                                    }
                                          $sql_del = "DELETE FROM advert_rotate WHERE adverts_advert_id=".$del_arr[$i];
                                          $db->query($sql_del);
					  $sql_del = "DELETE FROM adverts WHERE advert_id=".$del_arr[$i];
					  $db->query($sql_del);
						
					 // if($alert) $alert .="<br />";
						//$alert .= " Advert with ID -".$del_arr[$i]." Deleted ";
					
				// to delete from the display settings table
				  $sql_display_del = "DELETE FROM display_settings WHERE display_component_id=".$del_arr[$i]." AND features_feature_id =".$cur_featid." AND sites_site_id = $ecom_siteid ";
					 $db->query($sql_display_del);
					// Clearing the cache
					delete_advert_cache($del_arr[$i]);
				}	
			}
			recreate_entire_websitelayout_cache();
			delete_body_cache();
			$alert = "Banners deleted Sucessfully";
			
		}
		include ('../includes/adverts/list_adverts.php');
	

}
else if($_REQUEST['fpurpose']=='add')
{
	
	//include_once("classes/fckeditor.php");
	include("includes/adverts/add_adverts.php");
}
else if($_REQUEST['fpurpose']=='edit')
{	

		$ajax_return_function = 'ajax_return_contents';
		include "ajax/ajax.php";
		include ('includes/adverts/ajax/adverts_ajax_functions.php');
		//include_once("classes/fckeditor.php");
		//($_REQUEST['checkbox'][0]>0)?$edit_id=$_REQUEST['checkbox'][0]:$edit_id=$_REQUEST['advert_id'];
		($_REQUEST['checkbox'][0]>0)?$edit_id=$_REQUEST['checkbox'][0]:$edit_id=$_REQUEST['advert_id'];
		
	include("includes/adverts/edit_adverts.php");
	
}
else if($_REQUEST['fpurpose']=='insert')
{
	if($_REQUEST['Submit'])
	{

	//Function to validate forms
	validate_forms();
	if($_REQUEST['cbo_type']=='IMG')
	{
	if($_FILES['file_advert']['type']=='image/jpeg' || $_FILES['file_advert']['type']=='image/gif' || $_FILES['file_advert']['type']=='image/pjpeg')
		{
						$valid_image_type = true;
		}
		else
		{
			$alert= "<span ><strong>Error:</strong> Invalid Image type. Enter jpeg or gif image.</span><br>";
		}
	}
	if($_REQUEST['cbo_type']== 'SWF')
	{
		if($_FILES['file_advert']['type']=='application/x-shockwave-flash')
		{ 
		 $valid_flash_type = true;
		}
		else
		{
			$alert= "<span ><strong>Error:</strong> Invalid FLash type. Enter SWF File.</span><br>";
	
		}
	}		
	if (!$alert && ($_REQUEST['cbo_type']=='IMG' || $_REQUEST['cbo_type']=='SWF'))
	{ 
		//calling function to save the file
		$ret = save_image();
		$alert = $ret['alert'];
		$add_filename = $ret['filename'];
	}
	
	if(!$alert)
	{
		if ($_REQUEST['advert_activateperiodchange'])
		{
				if(!is_valid_date($_REQUEST['advert_displaystartdate'],'normal','-') or !is_valid_date($_REQUEST['advert_displayenddate'],'normal','-'))
					$alert = 'Sorry!! Start or End Date is Invalid';
		}		
	}
	if($alert)
	{
	
	//include_once("classes/fckeditor.php");
		
	?>
		<!--<br><font color="red"><b>Error!!&nbsp;&nbsp;</b> <?php // echo $alert?></font><br>-->
	<?php
	
		include("includes/adverts/add_adverts.php");	
	}
	else
	{
                                                                     
		//Calling the function to save the property
		$advert_id = Save_Advert($add_filename);
		$insert_id = $db->insert_id();
                if ($advert_id and $_REQUEST['cbo_type']=='ROTATE')
                {
                    // section to handle the case of rotate images
                    foreach ($_FILES as $k=>$v)
                    {
                        if(substr($k,0,10)=='rotate_img')
                        {
                            if($_FILES[$k]['tmp_name']!='')
                            {
                                $id_arr = explode('_',$k);
                                $curid = $id_arr[2];
                                $resize  = $_REQUEST['rotate_resize_'.$curid];
                                $ret_arr = save_rotate_image($k,$resize,0);
                                if($ret_arr['alert']=='') // case of no error
                                {
                                    $insert_array                     = array();
                                    $insert_array['adverts_advert_id']= $advert_id;
                                    $insert_array['rotate_image']     = addslashes($ret_arr['filename']);
                                    $insert_array['rotate_link']      = addslashes($_REQUEST['rotate_link_'.$curid]);
									$insert_array['rotate_alttext']      = addslashes($_REQUEST['rotate_alttext_'.$curid]);
                                    $insert_array['rotate_order']     = addslashes($_REQUEST['rotate_order_'.$curid]);
                                    $db->insert_from_array($insert_array,'advert_rotate');
                                }
                                else
                                    $rotate_error .= '<br>'.$ret_arr['alert'];
                            }
                        }
                    }
                }
		// Section to make entry to display_settings table
				if(count($_REQUEST['display_id']))
				{
				// Find the feature details for module mod_adverts from features table
					$sql_feat = "SELECT feature_id,feature_name FROM features WHERE feature_modulename ='mod_adverts'";
					$ret_feat = $db->query($sql_feat);
					if ($db->num_rows($ret_feat))
					{
						$row_feat 	= $db->fetch_array($ret_feat);
						$cur_featid	= $row_feat['feature_id'];
					}
		// checking wheter the feature is added in the site menu table // BY ANU 
					$sql_chk_menu = "SELECT menu_id FROM site_menu WHERE features_feature_id = ".$cur_featid;
					$res_chk_menu = $db->query($sql_chk_menu);
					$if_menu_exists = $db->num_rows($res_chk_menu);
					if(!$if_menu_exists){
					$sql_insert_menu = "INSERT INTO site_menu (sites_site_id,features_feature_id,menu_title) VALUES ($ecom_siteid,$cur_featid,'".$row_feat['feature_name']."')";
					$db->query($sql_insert_menu);
					}
					//end checking site menu
			
					for($i=0;$i<count($_REQUEST['display_id']);$i++)
					{
						 $cur_arr 		= explode("_",$_REQUEST['display_id'][$i]);
						 	$layoutid	= $cur_arr[0];
							$layoutcode	= $cur_arr[1];
							$position	= $cur_arr[2];
							$insert_array										= array();
							$insert_array['sites_site_id']						= $ecom_siteid;
							$insert_array['features_feature_id']				= $cur_featid;
							$insert_array['display_position']					= $position;
							$insert_array['themes_layouts_layout_id']			= $layoutid;
							$insert_array['layout_code']						= add_slash($layoutcode);
							$insert_array['display_title']						= add_slash(trim($_REQUEST['advert_title']));
							$insert_array['display_order']						= 0;
							$insert_array['display_component_id']				= $insert_id;
							$db->insert_from_array($insert_array,'display_settings');
							
						
					}
			}
		// Clearing the cache of body
		delete_body_cache();
		recreate_entire_websitelayout_cache();
	?>
		<br><font color="red"><b>Banner Added Successfully</b></font><br>
                <?php echo $rotate_error?><br>
		<br /><a class="smalllink" href="home.php?request=adverts&search_name=<?=$_REQUEST['pass_search_name']?>&sort_by=<?=$_REQUEST['pass_sort_by']?>&sort_order=<?=$_REQUEST['pass_sort_order']?>&records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pg=<?=$_REQUEST['pass_pg']?>">Go Back to the Listing page</a><br /><br />
		<a class="smalllink" href="home.php?request=adverts&fpurpose=edit&advert_id=<?php echo $advert_id?>&pass_search_name=<?=$_REQUEST['pass_search_name']?>&pass_sort_by=<?=$_REQUEST['pass_sort_by']?>&pass_sort_order=<?=$_REQUEST['pass_sort_order']?>&pass_records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pass_pg=<?=$_REQUEST['pass_pg']?>&advert_title=<?=$_REQUEST['advert_title']?>">Go Back to the Edit Page</a><br /><br />
	    <a class="smalllink" href="home.php?request=adverts&fpurpose=add&pass_search_name=<?=$_REQUEST['pass_search_name']?>&pass_sort_by=<?=$_REQUEST['pass_sort_by']?>&pass_sort_order=<?=$_REQUEST['pass_sort_order']?>&pass_records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pass_pg=<?=$_REQUEST['pass_pg']?>">Go Back to the Advert Add Page</a><br /><br />
	<?php
	}
	
	}
}
else if($_REQUEST['fpurpose'] == 'update_advert') {  // for updating the advert
   
	if($_REQUEST['advert_id'])
	{
		//Function to validate forms
		validate_forms();
		if($_FILES['file_advert']['name'])
		{
			if($_REQUEST['cbo_type']=='IMG')
			{
			
				if($_FILES['file_advert']['type']=='image/jpeg' || $_FILES['file_advert']['type']=='image/gif' || $_FILES['file_advert']['type']=='image/pjpeg')
				{
								$valid_image_type = true;
				}
				else
				{
					$alert= "<span ><strong>Error:</strong> Invalid Image type. Enter jpeg or gif image.</span><br>";
				}
			}
			if($_REQUEST['cbo_type']== 'SWF')
			{
				if($_FILES['file_advert']['type']=='application/x-shockwave-flash')
				{ 
				 $valid_flash_type = true;
				}
				else
				{
					$alert= "<span ><strong>Error:</strong> Invalid FLash type. Enter SWF File.</span><br>";
			
				}
			}
		}	
		if(!$alert)
			{
				if ($_REQUEST['advert_activateperiodchange'])
				{
						if(!is_valid_date($_REQUEST['advert_displaystartdate'],'normal','-') or !is_valid_date($_REQUEST['advert_displayenddate'],'normal','-'))
							$alert = 'Sorry!! Start or End Date is Invalid';
				}		
			}
		if (!$alert)
		{
		
			if($_FILES['file_advert']['name'])
			{ 
			
				$ret 			= save_image();
				$alert 			= $ret['alert'];
				$add_filename 	= $ret['filename'];
			}
			//Calling the function to save the property
			$advert_id = Save_Advert($add_filename);
                        $advert_id = $_REQUEST['advert_id']; 
                        if ($_REQUEST['cbo_type']=='ROTATE')
                        {
                            // section to handle the case of rotate images
                            foreach ($_FILES as $k=>$v)
                            {
                                if(substr($k,0,10)=='rotate_img') // case of additional images
                                {
                                    if($_FILES[$k]['tmp_name']!='')
                                    {
                                        $id_arr = explode('_',$k);
                                        $curid = $id_arr[2];
                                        $resize  = $_REQUEST['rotate_resize_'.$curid];
                                        $ret_arr = save_rotate_image($k,$resize,0);
                                        if($ret_arr['alert']=='') // case of no error
                                        {
                                            $insert_array                     = array();
                                            $insert_array['adverts_advert_id']= $advert_id;
                                            $insert_array['rotate_image']     = addslashes($ret_arr['filename']);
                                            $insert_array['rotate_link']      = addslashes($_REQUEST['rotate_link_'.$curid]);
											$insert_array['rotate_alttext']      = addslashes($_REQUEST['rotate_alttext_'.$curid]);
                                            $insert_array['rotate_order']     = addslashes($_REQUEST['rotate_order_'.$curid]);
                                            $db->insert_from_array($insert_array,'advert_rotate');
                                        }
                                        else
                                            $rotate_error .= '<br>'.$ret_arr['alert'];
                                    }
                                }
                            }
                            // section to handle the case of rotate images
                            foreach ($_REQUEST as $k=>$v)
                            {
                                if (substr($k,0,15)=='ext_rotate_link') // case if details related to an existing image is to be updated
                                {
                                    $id_arr = explode('_',$k);
                                    $curid = $id_arr[3];
                                    $resize  = $_REQUEST['ext_rotate_resize_'.$curid];
                                    $ret_arr = array('alert'=>'');
                                    if($_FILES['ext_rotate_img_'.$curid]['tmp_name']!='')// update the image only if new image selected
                                    {
                                        $ret_arr = save_rotate_image('ext_rotate_img_'.$curid,$resize,$curid);
                                    }    
                                    if($ret_arr['alert']=='') // case of no error
                                    {
                                        $update_array                           = array();
                                        $update_array['adverts_advert_id']      = $advert_id;
                                        if ($ret_arr['filename']!='')
                                            $update_array['rotate_image']       = addslashes($ret_arr['filename']);
                                        $update_array['rotate_link']            = addslashes($_REQUEST['ext_rotate_link_'.$curid]);
                                        $update_array['rotate_alttext']           = addslashes($_REQUEST['ext_rotate_alttext_'.$curid]);
                                        $update_array['rotate_order']           = addslashes($_REQUEST['ext_rotate_order_'.$curid]);
                                        $db->update_from_array($update_array,'advert_rotate',array('rotate_id'=>$curid));
                                    }
                                    else
                                        $rotate_error .= '<br>'.$ret_arr['alert'];
                                }
                            }
                        }
			// Section to make entry to display_settings table
				if(count($_REQUEST['display_id']))
				{
					// Find the feature details for module mod_adverts from features table
					$sql_feat = "SELECT feature_id,feature_name FROM features WHERE feature_modulename ='mod_adverts'";
					$ret_feat = $db->query($sql_feat);
					if ($db->num_rows($ret_feat))
					{
						$row_feat 	= $db->fetch_array($ret_feat);
						$cur_featid	= $row_feat['feature_id'];
					}
					
					// checking wheter the feature is added in the site menu table // BY ANU 
					$sql_chk_menu = "SELECT menu_id FROM site_menu WHERE features_feature_id = ".$cur_featid;
					$res_chk_menu = $db->query($sql_chk_menu);
					$if_menu_exists = $db->num_rows($res_chk_menu);
					if(!$if_menu_exists){
					$sql_insert_menu = "INSERT INTO site_menu (sites_site_id,features_feature_id,menu_title) VALUES ($ecom_siteid,$cur_featid,'".$row_feat['feature_name']."')";
					$db->query($sql_insert_menu);
					}
					//end checking site menu
					$sel_dispid	= array();
					for($i=0;$i<count($_REQUEST['display_id']);$i++)
					{
						$cur_arr 		= explode("_",$_REQUEST['display_id'][$i]);
						$dispid			= $cur_arr[0];
						$sel_dispid[] 	= $dispid;
						// Check whether this disp id is already selected for this category group
						 $sql_check = "SELECT display_id FROM display_settings WHERE display_id=$dispid AND 
										sites_site_id=$ecom_siteid AND features_feature_id = $cur_featid AND 
										display_component_id=".$_REQUEST['advert_id']."";
						$ret_check = $db->query($sql_check);
						if ($db->num_rows($ret_check)==0 or $dispid==0)
						{
							$layoutid		= $cur_arr[1];
							$layoutcode		= $cur_arr[2];
							$position		= $cur_arr[3];
							$insert_array										= array();
							$insert_array['sites_site_id']						= $ecom_siteid;
							$insert_array['features_feature_id']				= $cur_featid;
							$insert_array['display_position']					= $position;
							$insert_array['themes_layouts_layout_id']			= $layoutid;
							$insert_array['layout_code']						= add_slash($layoutcode);
							$insert_array['display_title']						= add_slash(trim($_REQUEST['advert_title']));
							$insert_array['display_order']						= 0;
							$insert_array['display_component_id']				= $_REQUEST['advert_id'];
							$db->insert_from_array($insert_array,'display_settings');
							$insertid 		= $db->insert_id();
							$sel_dispid[] 	= $insertid;
						}	
						// commented not to change the display title while updating the component title 
						//else
						//{
						//	$update_array						= array();
						//	$update_array['display_title']		= add_slash(trim($_REQUEST['adevrt_title']));
						//	$db->update_from_array($update_array,'display_settings',array('display_id'=>$dispid));
						//}
					}
					// Delete all those entries from display setting corresponding to current category group which where there
					// previously but not existing now
					$sql_del = "DELETE FROM display_settings WHERE sites_site_id=$ecom_siteid AND 
								features_feature_id=$cur_featid AND display_component_id=".$_REQUEST['advert_id']." AND 
								display_id NOT IN (".implode(",",$sel_dispid).")";
					$ret_del = $db->query($sql_del);
				}
				// Clearing the cache
				//delete_body_cache();
				//delete_advert_cache($_REQUEST['advert_id']);
                                // case if update the title in display settings is to be done for current advert
                                if($_REQUEST['advert_updatewebsitelayout']) 
                                {
                                    // Get the feature id of mod_adverts from features table
                                    $sql_feat = "SELECT feature_id 
                                                    FROM 
                                                        features 
                                                    WHERE 
                                                        feature_modulename ='mod_adverts' 
                                                    LIMIT 
                                                        1";
                                    $ret_feat = $db->query($sql_feat);
                                    if ($db->num_rows($ret_feat))
                                    {
                                            $row_feat       = $db->fetch_array($ret_feat);
                                            $cur_featid     = $row_feat['feature_id'];
                                    }
                                    $sql_update = "UPDATE 
                                                        display_settings 
                                                    SET 
                                                        display_title='".trim(add_slash($_REQUEST['advert_title']))."' 
                                                    WHERE 
                                                        sites_site_id = $ecom_siteid 
                                                        AND features_feature_id = $cur_featid 
                                                        AND display_component_id = ".$_REQUEST['advert_id'];
                                    $db->query($sql_update);
                                }
                                
                                /*if($_REQUEST['advert_updatewebsitelayout']) 
                                {
                                    clear_all_cache();// Clearing all cache
                                }
                                else
                                {*/
                                    delete_body_cache();
                                    delete_advert_cache($_REQUEST['advert_id']);  
                                /*}*/
								recreate_entire_websitelayout_cache();
			?>
			<br><font color="red"><b>Banner Updated Successfully</b></font><br>
			<br><a class="smalllink" href="home.php?request=adverts&search_name=<?=$_REQUEST['pass_search_name']?>&sort_by=<?=$_REQUEST['pass_sort_by']?>&sort_order=<?=$_REQUEST['pass_sort_order']?>&records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pg=<?=$_REQUEST['pass_pg']?>">Go Back to the Banner Listing page</a><br /><br />
			<a class="smalllink" href="home.php?request=adverts&fpurpose=edit&advert_id=<?=$_REQUEST['advert_id']?>&pass_search_name=<?=$_REQUEST['pass_search_name']?>&pass_sort_by=<?=$_REQUEST['pass_sort_by']?>&pass_sort_order=<?=$_REQUEST['pass_sort_order']?>&pass_records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pass_pg=<?=$_REQUEST['pass_pg']?>&advert_title=<?=$_REQUEST['advert_title']?>">Go Back to the Banner Edit Page</a><br /><br />
			<a class="smalllink" href="home.php?request=adverts&fpurpose=add&pass_search_name=<?=$_REQUEST['pass_search_name']?>&pass_sort_by=<?=$_REQUEST['pass_sort_by']?>&pass_sort_order=<?=$_REQUEST['pass_sort_order']?>&pass_records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pass_pg=<?=$_REQUEST['pass_pg']?>">Go Back to the Banner Add Page</a><br /><br />			
			<?php
		}
		else
		{ 
			$ajax_return_function = 'ajax_return_contents';
			include "ajax/ajax.php";
			include ('includes/adverts/ajax/adverts_ajax_functions.php');
			//include_once("classes/fckeditor.php");
			($_REQUEST['checkbox'][0]>0)?$edit_id=$_REQUEST['checkbox'][0]:$edit_id=$_REQUEST['advert_id'];
		?>
			<!--<br><font color="red"><b>Error!!&nbsp;&nbsp;</b> <?php // echo $alert?></font><br>-->
	<?php
			include("includes/adverts/edit_adverts.php");
		}
	}
	else
	{ 
		$ajax_return_function = 'ajax_return_contents';
			include "ajax/ajax.php";
			include ('includes/adverts/ajax/adverts_ajax_functions.php');
			//include_once("classes/fckeditor.php");
			($_REQUEST['checkbox'][0]>0)?$edit_id=$_REQUEST['checkbox'][0]:$edit_id=$_REQUEST['advert_id'];
		?>
			<br><font color="red"><strong>Error!</strong> Invalid Banner Id</font><br />
			<br /><a class="smalllink" href="home.php?request=property&cbo_ptype=<?php echo $_REQUEST['cbo_ptype']?>&cbo_ptype=<?php echo $_REQUEST['cbo_ptype']?>&cbo_approvestatus=<?php echo $_REQUEST['cbo_approvestatus']?>&cbo_cat=<?php echo $_REQUEST['cbo_cat']?>&search_cname=<?=$_REQUEST['search_cname']?>&sort_by=<?=$_REQUEST['sort_by']?>&sort_order=<?=$_REQUEST['sort_order']?>&records_per_page=<?=$_REQUEST['records_per_page']?>&pg=<?=$_REQUEST['pg']?>">Go Back to the Listing page</a><br /><br />
			
		<?php	
		include("includes/adverts/edit_adverts.php");
	} //// updating adverts ends
}
elseif($_REQUEST['fpurpose']=='delete_rotate')
{
    if($_REQUEST['d_id'] and $_REQUEST['advert_id'])
    {
         //Check whether d_id and advert id belongs to current website
         $sql_del = "SELECT advert_id 
                        FROM 
                            adverts 
                        WHERE 
                            advert_id=".$_REQUEST['advert_id']." 
                            AND sites_site_id = $ecom_siteid 
                        LIMIT 
                            1";
         $ret_del = $db->query($sql_del);
         if($db->num_rows($ret_del))
         {
            // Get the details of deleting rotate image details 
            $sql_det = "SELECT rotate_image 
                            FROM 
                                advert_rotate 
                            WHERE 
                                adverts_advert_id=".$_REQUEST['advert_id']." 
                                AND rotate_id =".$_REQUEST['d_id']." 
                            LIMIT 
                                1";
            $ret_det = $db->query($sql_det);
            if($db->num_rows($ret_det))
            {
                $row_det = $db->fetch_array($ret_det);
                if(file_exists("$image_path/adverts/rotate/".$row_det['rotate_image']))
                   @unlink ("$image_path/adverts/rotate/".$row_det['rotate_image']);
                $sql_del = "DELETE FROM 
                                advert_rotate 
                            WHERE 
                                rotate_id=".$_REQUEST['d_id']." 
                            LIMIT 
                                1";
                $db->query($sql_del);
                $alert = "Rotate image deleted successfully";
            }
            else
                $alert = "Sorry!! unable to delete .. Details not valid";
         }
    }
    ($_REQUEST['checkbox'][0]>0)?$edit_id=$_REQUEST['checkbox'][0]:$edit_id=$_REQUEST['advert_id'];
    $ajax_return_function = 'ajax_return_contents';
    include "ajax/ajax.php";
    include ('includes/adverts/ajax/adverts_ajax_functions.php');
    include("includes/adverts/edit_adverts.php");  
}
elseif($_REQUEST['fpurpose'] =='list_adverts_maininfo')// Case of listing main info for category groups
{
	/*include_once("../functions/functions.php");
	include_once('../session.php');
	include_once("../config.php");	
	include ('../includes/adverts/ajax/adverts_ajax_functions.php');
	//include_once("../classes/fckeditor.php");
	show_adverts_maininfo($_REQUEST['group_id']);*/
	($_REQUEST['checkbox'][0]>0)?$edit_id=$_REQUEST['checkbox'][0]:$edit_id=$_REQUEST['advert_id'];
	$ajax_return_function = 'ajax_return_contents';
	include "ajax/ajax.php";
	include ('includes/adverts/ajax/adverts_ajax_functions.php');
	include("includes/adverts/edit_adverts.php");
}
/*To list categories assigned in the Adverts using AJAX*/
elseif($_REQUEST['fpurpose'] == 'list_categoriesInAdverts_ajax'){
		include_once("../functions/functions.php");
		include_once('../session.php');
		include_once("../config.php");	
		include ('../includes/adverts/ajax/adverts_ajax_functions.php');
		show_category_list($_REQUEST['group_id']); //$_REQUEST['cur_advertid']
}elseif($_REQUEST['fpurpose'] == 'changestat_category_ajax'){ // To Change the status of the selected category in the Adverts
		
		include_once("../functions/functions.php");
		include_once('../session.php');
		include_once("../config.php");			
		include ('../includes/adverts/ajax/adverts_ajax_functions.php');
		if ($_REQUEST['ch_ids'] == '')
		{
			$alert = 'Sorry Categories not not selected';
		}
		else
		{
			$chstat_arr = explode("~",$_REQUEST['ch_ids']);
			for($i=0;$i<count($chstat_arr);$i++)
			{
				if(trim($chstat_arr[$i]))
				{
					// Changing status of the catgories in the Advert
				 $sql_chstat = "UPDATE advert_display_category SET advert_display_category_hide = ".$_REQUEST['chstat']." WHERE id=".$chstat_arr[$i];
					$db->query($sql_chstat);
				}	
			}
			$alert = 'Successfully Changed the status for  the selected Categories'; 
		}	
		show_category_list($_REQUEST['cur_advert_id'],$alert);
}
else if($_REQUEST['fpurpose'] == 'list_assign_categories'){// to list the categories to be assigned to the Advert
	$advert_id = $_REQUEST['checkbox'][0];
	include ('includes/adverts/list_assign_categories.php');						
	
}
else if($_REQUEST['fpurpose'] == 'assign_categories'){// to asign the categories to the Adverts

	
	 $advert_id = $_REQUEST['advert_id'];
	{
		
		if ($_REQUEST['category_ids'] == '')
		{
			$alert = 'Sorry Category not not selected';
		}
		else
		{ 
		
	 $sql_assigned_categories = "SELECT product_categories_category_id FROM advert_display_category WHERE adverts_advert_id =".$_REQUEST['advert_id'];
$res_assigned_categories = $db->query($sql_assigned_categories);
$assigned_categories_arr = array();
while($assigned_categories = $db->fetch_array($res_assigned_categories)){
$assigned_categories_arr[]= $assigned_categories['product_categories_category_id'];
}
			$categories_arr = explode("~",$_REQUEST['category_ids']);
			for($i=0;$i<count($categories_arr);$i++)
			{
				if(trim($categories_arr[$i]) && !in_array($categories_arr[$i],$assigned_categories_arr))
				{
					$insert_array = array();
					$insert_array['sites_site_id']=$ecom_siteid;
					$insert_array['adverts_advert_id']=$_REQUEST['advert_id'];
					$insert_array['product_categories_category_id']=$categories_arr[$i];
					$db->insert_from_array($insert_array, 'advert_display_category');
				}	
			}
			$alert = 'Banner(s) Successfully assigned to categories'; 
		}						
	
	}
	$alert = '<center><font color="red"><b>'.$alert;
			$alert .= '</b></font></center>';
			echo $alert;
			?>
	<br /><a class="smalllink" href="home.php?request=adverts&search_name=<?=$_REQUEST['pass_search_name']?>&sort_by=<?=$_REQUEST['pass_sort_by']?>&sort_order=<?=$_REQUEST['pass_sort_order']?>&records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pg=<?=$_REQUEST['pass_pg']?>&start=<?=$_REQUEST['pass_start']?>&advert_id=<?=$_REQUEST['advert_id']?>" onclick="show_processing()">Go Back to the Banners Listing page</a><br /><br />
			<a class="smalllink" href="home.php?request=adverts&fpurpose=edit&advert_id=<?=$_REQUEST['advert_id']?>&pass_search_name=<?=$_REQUEST['pass_search_name']?>&pass_sort_by=<?=$_REQUEST['pass_sort_by']?>&pass_sort_order=<?=$_REQUEST['pass_sort_order']?>&pass_records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pass_pg=<?=$_REQUEST['pass_pg']?>&pass_start=<?=$_REQUEST['pass_start']?>&curtab=category_tab_td&advert_title=<?=$_REQUEST['advert_title']?>" onclick="show_processing()">Go Back to the Edit  this Banner</a><br /><br />
			<a class="smalllink" href="home.php?request=adverts&fpurpose=add&pass_search_name=<?=$_REQUEST['pass_search_name']?>&pass_sort_by=<?=$_REQUEST['pass_sort_by']?>&pass_sort_order=<?=$_REQUEST['pass_sort_order']?>&pass_records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pass_pg=<?=$_REQUEST['pass_pg']?>">Go Back to the Banner Add Page</a><br /><br />		
			<?
	
}
elseif($_REQUEST['fpurpose']=='delete_category_ajax') // section used for delete of Category using ajax
	{
		include_once("../functions/functions.php");
		include_once('../session.php');
		include_once("../config.php");
		include ('../includes/adverts/ajax/adverts_ajax_functions.php');
		if ($_REQUEST['del_ids'] == '')
		{
			$alert = 'Sorry categories not not selected';
		}
		else
		{
			$del_arr = explode("~",$_REQUEST['del_ids']);
			for($i=0;$i<count($del_arr);$i++)
			{
				if(trim($del_arr[$i]))
				{
					// Deleting pages from page advert
					$sql_del = "DELETE FROM advert_display_category WHERE id=".$del_arr[$i];
					$db->query($sql_del);
				}	
			}
			$alert = 'Categories Successfully Removed from the Banner'; 
		}	
show_category_list($_REQUEST['cur_advert_id'],$alert);
}
elseif($_REQUEST['fpurpose'] == 'list_products_ajax'){ // for listing the products assiged to the adverts to be displyed 
	
		include_once("../functions/functions.php");
		include_once('../session.php');
		include_once("../config.php");	
		include ('../includes/adverts/ajax/adverts_ajax_functions.php');
		show_product_list($_REQUEST['group_id'],$alert);  //$_REQUEST['cur_advertid']
}
else if($_REQUEST['fpurpose'] == 'list_assign_products'){// to list the products to be assigned to the Adverts
	$advert_id = $_REQUEST['checkbox'][0];
	include ('includes/adverts/list_assign_products.php');						
	
}
else if($_REQUEST['fpurpose'] == 'assign_products'){// to asign the categories to the Advert

	
	$advert_id = $_REQUEST['advert_id'];
	{
		
		if ($_REQUEST['product_ids'] == '')
		{
			$alert = 'Sorry Products not not selected';
		}
		else
		{ 
		
		$sql_assigned_products = "SELECT products_product_id FROM advert_display_product WHERE adverts_advert_id =".$_REQUEST['advert_id']." AND sites_site_id=".$ecom_siteid;
$res_assigned_products = $db->query($sql_assigned_products);
$assigned_products_arr = array();
while($assigned_products = $db->fetch_array($res_assigned_products)){
$assigned_products_arr[]= $assigned_products['products_product_id'];
}
			$products_arr = explode("~",$_REQUEST['product_ids']);
			for($i=0;$i<count($products_arr);$i++)
			{
				if(trim($products_arr[$i]) && !in_array($products_arr[$i],$assigned_products_arr))
				{
					$insert_array = array();
					$insert_array['sites_site_id']=$ecom_siteid;
					$insert_array['adverts_advert_id']=$_REQUEST['advert_id'];
					$insert_array['products_product_id']=$products_arr[$i];
					$db->insert_from_array($insert_array, 'advert_display_product');
				}	
			}
			$alert = 'Products Successfully assigned  to Banner(s)'; 
		}						
	
	}
	$alert = '<center><font color="red"><b>'.$alert;
			$alert .= '</b></font></center>';
			echo $alert;
			?>
	<br /><a class="smalllink" href="home.php?request=adverts&search_name=<?=$_REQUEST['pass_search_name']?>&sort_by=<?=$_REQUEST['pass_sort_by']?>&sort_order=<?=$_REQUEST['pass_sort_order']?>&records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pg=<?=$_REQUEST['pass_pg']?>&start=<?=$_REQUEST['pass_start']?>&advert_id=<?=$_REQUEST['advert_id']?>" onclick="show_processing()">Go Back to the Banners Listing page</a><br /><br />
			<a class="smalllink" href="home.php?request=adverts&fpurpose=edit&advert_id=<?=$_REQUEST['advert_id']?>&pass_search_name=<?=$_REQUEST['pass_search_name']?>&pass_sort_by=<?=$_REQUEST['pass_sort_by']?>&pass_sort_order=<?=$_REQUEST['pass_sort_order']?>&pass_records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pass_pg=<?=$_REQUEST['pass_pg']?>&pass_start=<?=$_REQUEST['pass_start']?>&curtab=products_tab_td&advert_title=<?=$_REQUEST['advert_title']?>" onclick="show_processing()">Go Back to the Edit  this Banner</a><br /><br />
			<a class="smalllink" href="home.php?request=adverts&fpurpose=add&pass_search_name=<?=$_REQUEST['pass_search_name']?>&pass_sort_by=<?=$_REQUEST['pass_sort_by']?>&pass_sort_order=<?=$_REQUEST['pass_sort_order']?>&pass_records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pass_pg=<?=$_REQUEST['pass_pg']?>">Go Back to the Banner Add Page</a><br /><br />		
			<?
	
}elseif($_REQUEST['fpurpose'] == 'changestat_product_ajax'){ // To Change the status of the selected Product assigned to the Adevrt
		include_once("../functions/functions.php");
		include_once('../session.php');
		include_once("../config.php");			
		include ('../includes/adverts/ajax/adverts_ajax_functions.php');
		if ($_REQUEST['ch_ids'] == '')
		{
			$alert = 'Sorry Products not not selected';
		}
		else
		{
			$chstat_arr = explode("~",$_REQUEST['ch_ids']);
			for($i=0;$i<count($chstat_arr);$i++)
			{
				if(trim($chstat_arr[$i]))
				{
					// Changing status of the catgories in the Advert
				$sql_chstat = "UPDATE advert_display_product SET advert_display_product_hide = ".$_REQUEST['chstat']." WHERE id=".$chstat_arr[$i];
					$db->query($sql_chstat);
				}	
			}
			$alert = 'Successfully Changed the status for  the selected Product(s) assigned to the Banner'; 
		}	
		show_product_list($_REQUEST['cur_advert_id'],$alert);
}elseif($_REQUEST['fpurpose']=='delete_product_ajax') // section used for delete of Product assigned to Adverts using ajax
	{
		include_once("../functions/functions.php");
		include_once('../session.php');
		include_once("../config.php");
		include ('../includes/adverts/ajax/adverts_ajax_functions.php');
		if ($_REQUEST['del_ids'] == '')
		{
			$alert = 'Sorry Products  not selected';
		}
		else
		{
			$del_arr = explode("~",$_REQUEST['del_ids']);
			for($i=0;$i<count($del_arr);$i++)
			{
				if(trim($del_arr[$i]))
				{
					// Deleting product from Advert
					$sql_del = "DELETE FROM advert_display_product WHERE id=".$del_arr[$i];
					$db->query($sql_del);
				}	
			}
			$alert = 'Products Successfully Removed from the Banner'; 
		}	
show_product_list($_REQUEST['cur_advert_id'],$alert);
}
/* FOR the assigned STATIC PAGES PART*/
elseif($_REQUEST['fpurpose'] == 'list_assign_pages_ajax'){
		 
		include_once("../functions/functions.php");
		include_once('../session.php');
		include_once("../config.php");	
		include ('../includes/adverts/ajax/adverts_ajax_functions.php');
		show_assign_pages_list($_REQUEST['group_id'],$alert); //$_REQUEST['cur_advertid']
}
elseif($_REQUEST['fpurpose'] == 'changestat_assign_pages_ajax'){ // To Change the status of the selected Page assigned to the Advert
		 
		include_once("../functions/functions.php");
		include_once('../session.php');
		include_once("../config.php");			
		include ('../includes/adverts/ajax/adverts_ajax_functions.php');
		if ($_REQUEST['ch_ids'] == '')
		{
			$alert = 'Sorry Pages not not selected';
		}
		else
		{
			$chstat_arr = explode("~",$_REQUEST['ch_ids']);
			for($i=0;$i<count($chstat_arr);$i++)
			{
				if(trim($chstat_arr[$i]))
				{
					// Changing status of the static pages assigned to the Adverts
		$sql_chstat = "UPDATE advert_display_static SET advert_display_static_hide = ".$_REQUEST['chstat']." WHERE id=".$chstat_arr[$i];
					$db->query($sql_chstat);
				}	
			}
			$alert = 'Successfully Changed the status for  the selected Page(s) assigned to the Banner'; 
		}	
		show_assign_pages_list($_REQUEST['cur_advert_id'],$alert);
}
else if($_REQUEST['fpurpose'] == 'list_assign_pages'){// to list the products to be assigned to the Advert
$advert_id = $_REQUEST['checkbox'][0];
	include ('includes/adverts/list_assign_pages.php');						
	
}
else if($_REQUEST['fpurpose'] == 'assign_pages_to_Advert'){// to asign the Static Pages to the Advert
	
	$advert_id = $_REQUEST['advert_id'];
	{
		
		if ($_REQUEST['page_ids'] == '')
		{
			$alert = 'Sorry Pages not not selected';
		}
		else
		{ 
		
		$sql_assigned_pages = "SELECT static_pages_page_id FROM advert_display_static WHERE adverts_advert_id =".$_REQUEST['advert_id']." AND sites_site_id=".$ecom_siteid;
$res_assigned_pages = $db->query($sql_assigned_pages);
$assigned_pages_arr = array();
while($assigned_pages = $db->fetch_array($res_assigned_pages)){
$assigned_pages_arr[]= $assigned_pages['static_pages_page_id'];
}
			$pages_arr = explode("~",$_REQUEST['page_ids']);
			for($i=0;$i<count($pages_arr);$i++)
			{
				if(trim($pages_arr[$i]) && !in_array($pages_arr[$i],$assigned_pages_arr))
				{
					$insert_array = array();
					$insert_array['sites_site_id']=$ecom_siteid;
					$insert_array['adverts_advert_id']=$_REQUEST['advert_id'];
					$insert_array['static_pages_page_id']=$pages_arr[$i];
					$db->insert_from_array($insert_array, 'advert_display_static');
				}	
			}
			delete_advert_cache($_REQUEST['advert_id']);
			delete_body_cache();
			$alert = 'Static Page(s) Successfully assigned to Banners'; 
		}						
	
	}
	$alert = '<center><font color="red"><b>'.$alert;
			$alert .= '</b></font></center>';
			echo $alert;
			?>
	<br /><a class="smalllink" href="home.php?request=adverts&search_name=<?=$_REQUEST['pass_search_name']?>&sort_by=<?=$_REQUEST['pass_sort_by']?>&sort_order=<?=$_REQUEST['pass_sort_order']?>&records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pg=<?=$_REQUEST['pass_pg']?>&start=<?=$_REQUEST['pass_start']?>&advert_id=<?=$_REQUEST['advert_id']?>" onclick="show_processing()">Go Back to the Banners Listing page</a><br /><br />
			<a class="smalllink" href="home.php?request=adverts&fpurpose=edit&advert_id=<?=$_REQUEST['advert_id']?>&pass_search_name=<?=$_REQUEST['pass_search_name']?>&pass_sort_by=<?=$_REQUEST['pass_sort_by']?>&pass_sort_order=<?=$_REQUEST['pass_sort_order']?>&pass_records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pass_pg=<?=$_REQUEST['pass_pg']?>&pass_start=<?=$_REQUEST['pass_start']?>&curtab=static_tab_td&advert_title=<?=$_REQUEST['advert_title']?>" onclick="show_processing()">Go Back to the Edit  this Banner</a><br /><br />
			<a class="smalllink" href="home.php?request=adverts&fpurpose=add&pass_search_name=<?=$_REQUEST['pass_search_name']?>&pass_sort_by=<?=$_REQUEST['pass_sort_by']?>&pass_sort_order=<?=$_REQUEST['pass_sort_order']?>&pass_records_per_page=<?=$_REQUEST['pass_records_per_page']?>&pass_pg=<?=$_REQUEST['pass_pg']?>">Go Back to the Banner Add Page</a><br /><br />		
			<?
	
}elseif($_REQUEST['fpurpose']=='delete_assign_pages') // section used for delete of Static Pages assigned to Adverts using ajax
	{ 
		include_once("../functions/functions.php");
		include_once('../session.php');
		include_once("../config.php");
		include ('../includes/adverts/ajax/adverts_ajax_functions.php');
		if ($_REQUEST['del_ids'] == '')
		{
			$alert = 'Sorry Pages  not selected';
		}
		else
		{
			$del_arr = explode("~",$_REQUEST['del_ids']);
			for($i=0;$i<count($del_arr);$i++)
			{
				if(trim($del_arr[$i]))
				{
					// Deleting product from Adverts
					$sql_del = "DELETE FROM advert_display_static WHERE id=".$del_arr[$i];
					$db->query($sql_del);
				}	
			}
			delete_advert_cache($_REQUEST['cur_advert_id']);
			delete_body_cache();
			$alert = 'Pages Successfully Removed from the Banner'; 
		}	
show_assign_pages_list($_REQUEST['cur_advert_id'],$alert);
}
// ===============================================================================
// 						FUNCTIONS USED IN THIS PAGE
// ===============================================================================	
function validate_forms()
{
	global $alert,$db;
	if($_REQUEST['dont_save']!=1)
	{
		//Validations
		$alert = '';
		$fieldRequired 		= array($_REQUEST['advert_title']);
		$fieldDescription 	= array('Banner Title');
		$fieldEmail 		= array();
		$fieldConfirm 		= array();
		$fieldConfirmDesc 	= array();
		$fieldNumeric 		= array();
		$fieldNumericDesc 	= array();
		
		if (($cbo_type=='IMG' || $cbo_type=='SWF'  ) && !$_REQUEST['advert_id'])//do only in case of insert
		{
				$fieldRequired[] 	= $_FILES['file_advert']['name'];
				$fieldDescription[] = array('Select File');
		}	
		serverside_validation($fieldRequired, $fieldDescription, $fieldEmail, $fieldConfirm, $fieldConfirmDesc, $fieldNumeric, $fieldNumericDesc);
	}
}
// =================================================================================
//					The common function to save the Advert
// =================================================================================
function Save_Advert($fname)
{
	global $db,$ecom_siteid;
	if($_REQUEST['dont_save']!=1) // case of handle the unwanted submission while coming to pages by clicking the links in action pages
	{
		if($_REQUEST['advert_id']) //If advert id is there then update else update
		{
			// ===============================================================================
			// 						Edit Section Starts over here
			// ===============================================================================
			
			//Updating the adverts table
			if(!$_REQUEST['advert_order'] || !is_numeric($_REQUEST['advert_order']))
				$order = 0;
			else
				$order = trim($_REQUEST['advert_order']);
				
			$update_array						= array();
			$update_array['sites_site_id'] 		= $ecom_siteid;
			//$update_array['advert_position'] 	= addslashes($_REQUEST['cbo_position']);
			$update_array['advert_order'] 		= $_REQUEST['advert_order'];
			$update_array['advert_hide']		= $_REQUEST['advert_hide'];
			$update_array['advert_showinall']	= ($_REQUEST['advert_showinall'])?1:0;
			$update_array['advert_showinhome']	= ($_REQUEST['advert_showinhome'])?1:0;
			$update_array['advert_type']		= $_REQUEST['cbo_type'];
			$update_array['advert_target']		= $_REQUEST['advert_target'];
			$update_array['advert_title'] 		= addslashes($_REQUEST['advert_title']);
			$update_array['advert_activateperiodchange']=$_REQUEST['advert_activateperiodchange'];
			$exp_advert_displaystartdate=explode("-",$_REQUEST['advert_displaystartdate']);
			$val_advert_displaystartdate=$exp_advert_displaystartdate[2]."-".$exp_advert_displaystartdate[1]."-".$exp_advert_displaystartdate[0];
			$exp_advert_displayenddate=explode("-",$_REQUEST['advert_displayenddate']);
			$val_advert_displayenddate=$exp_advert_displayenddate[2]."-".$exp_advert_displayenddate[1]."-".$exp_advert_displayenddate[0];
			if($_REQUEST['advert_activateperiodchange']==0){
			$val_advert_displaystartdate ="0000-00-00";
			$val_advert_displayenddate ="0000-00-00";
			}
			$val_advert_displaystartdatetime    =  $val_advert_displaystartdate." ".$_REQUEST['advert_starttime_hr'].":".$_REQUEST['advert_starttime_mn'].":".$_REQUEST['advert_starttime_ss'];
			$val_advert_displayenddatetime  	=  $val_advert_displayenddate." ".$_REQUEST['advert_endtime_hr'].":".$_REQUEST['advert_endtime_mn'].":".$_REQUEST['advert_endtime_ss'];
			$update_array['advert_displaystartdate']	=$val_advert_displaystartdatetime;
			$update_array['advert_displayenddate']		=$val_advert_displayenddatetime;
			if(trim($_REQUEST['txt_link']))
			{
				$update_array['advert_link'] 	= addslashes($_REQUEST['txt_link']);
			}
			
			if($_REQUEST['cbo_type']=='IMG' && $fname) //If type is IMG and if img is selected
			{
				$update_array['advert_source'] 	= $fname;
			}
			elseif ($_REQUEST['cbo_type']=='PATH')
			{
				$update_array['advert_source'] 	= addslashes($_REQUEST['txt_imgloc']);
			}
			elseif ($_REQUEST['cbo_type']=='TXT')
			{
				$update_array['advert_source'] 	= addslashes($_REQUEST['txt_text']);
			}
			elseif($_REQUEST['cbo_type']=='SWF' && $fname) //If type is IMG and if img is selected
			{
				$update_array['advert_source'] 	= $fname;
				$update_array['advert_link'] 	= '';
			}
			elseif ($_REQUEST['cbo_type']=='ROTATE')
			{
				$update_array['advert_rotate_height'] 	= trim($_REQUEST['rotate_height']);
				$update_array['advert_rotate_speed'] 	= trim($_REQUEST['rotate_speed']);
			}
			$db->update_from_array($update_array, 'adverts', array('advert_id' => $_REQUEST['advert_id'] , 'sites_site_id' => $ecom_siteid));
		}else {
				
			// ===============================================================================
			// 						Insert Section Starts over here
			// ===============================================================================
			// Inserting to adverts Table
			if(!$_REQUEST['txt_order'] || !is_numeric($_REQUEST['txt_order']))
				$order = 0;
			else
				$order = trim($_REQUEST['txt_order']);
				
			$insert_array						= array();
			$insert_array['sites_site_id'] 			= $ecom_siteid;
			//$insert_array['advert_position'] 	= addslashes($_REQUEST['cbo_position']);
			$insert_array['advert_hide']		= $_REQUEST['advert_hide'];
			$insert_array['advert_showinall']	= ($_REQUEST['advert_showinall'])?1:0;
			$insert_array['advert_showinhome']	= ($_REQUEST['advert_showinhome'])?1:0;
			$insert_array['advert_type']		= $_REQUEST['cbo_type'];
			$insert_array['advert_title'] 		= addslashes($_REQUEST['advert_title']);
			$insert_array['advert_target'] 		= addslashes($_REQUEST['advert_target']);
			$insert_array['advert_activateperiodchange']=$_REQUEST['advert_activateperiodchange'];
			$exp_advert_displaystartdate=explode("-",$_REQUEST['advert_displaystartdate']);
			$val_advert_displaystartdate=$exp_advert_displaystartdate[2]."-".$exp_advert_displaystartdate[1]."-".$exp_advert_displaystartdate[0];
			$exp_advert_displayenddate=explode("-",$_REQUEST['advert_displayenddate']);
			$val_advert_displayenddate=$exp_advert_displayenddate[2]."-".$exp_advert_displayenddate[1]."-".$exp_advert_displayenddate[0];
			
			$val_advert_displaystartdatetime    =  $val_advert_displaystartdate." ".$_REQUEST['advert_starttime_hr'].":".$_REQUEST['advert_starttime_mn'].":".$_REQUEST['advert_starttime_ss'];
			$val_advert_displayenddatetime  	=  $val_advert_displayenddate." ".$_REQUEST['advert_endtime_hr'].":".$_REQUEST['advert_endtime_mn'].":".$_REQUEST['advert_endtime_ss'];
			$insert_array['advert_displaystartdate']	=$val_advert_displaystartdatetime;
			$insert_array['advert_displayenddate']		=$val_advert_displayenddatetime;
			
			//$insert_array['advert_displaystartdate']=$val_advert_displaystartdate;
			//$insert_array['advert_displayenddate']=$val_advert_displayenddate;
			if(trim($_REQUEST['txt_link']))
			{
				$insert_array['advert_link'] 	= addslashes($_REQUEST['txt_link']);
			}
			if($_REQUEST['cbo_type']=='IMG')
			{
				$insert_array['advert_source'] 	= $fname;
			}
			elseif ($_REQUEST['cbo_type']=='PATH')
			{
				$insert_array['advert_source'] 	= addslashes($_REQUEST['txt_imgloc']);
			}
			elseif ($_REQUEST['cbo_type']=='TXT')
			{
				$insert_array['advert_source'] 	= addslashes($_REQUEST['txt_text']);
			}
			elseif($_REQUEST['cbo_type']=='SWF' && $fname) //If type is IMG and if img is selected
			{
				$insert_array['advert_source'] 	= $fname;
				$insert_array['advert_link'] 	= '';
			}
			elseif ($_REQUEST['cbo_type']=='ROTATE')
			{
				$insert_array['advert_rotate_height'] 	= trim($_REQUEST['rotate_height']);
				$insert_array['advert_rotate_speed'] 	= trim($_REQUEST['rotate_speed']);
			}
			
			$db->insert_from_array($insert_array, 'adverts');
			$insert_id = $db->insert_id();		
			
			//for ading to site_menu
			/*$chk_siteMenu_sql = "SELECT menu_id FROM site_menu WHERE module_name = 'mod_advert' AND menu_position = '".$_REQUEST['cbo_position']."'  AND site_id= ".$ecom_siteid ;
			$chk_siteMenu_qry = $db->query($chk_siteMenu_sql);
			$siteMenu_chk =	$db->num_rows($chk_siteMenu_qry);
				if(!$siteMenu_chk) {
					$sql_features = "SELECT feature_id,feature_title FROM features WHERE module_name = 'mod_advert'";
					$res_features = $db->query($sql_features);
					$row_check = $db->fetch_array($res_features);
					$insertmenu_array['menu_title'] = $row_check['feature_title'];
					$insertmenu_array['menu_position'] = $_REQUEST['cbo_position'];
					$insertmenu_array['feature_id'] = $row_check['feature_id'];
					$insertmenu_array['site_id'] = $ecom_siteid;
					$insertmenu_array['module_name'] = 'mod_advert';
					$insertmenu_array['show_component'] = 'Y';
					$insertmenu_array['ordering'] = $_REQUEST['txt_order'];
					$db->insert_from_array($insertmenu_array, 'site_menu');	
				}
			*/	
			// Calling function to add entries to display_settings table base of selected position
			//save_to_displaysettings($_REQUEST['cbo_position'],'mod_advert','advert_position','adverts',$_REQUEST['txt_order']);	
			//end adding to site_menu
			
			return $insert_id;	
			
			// ===============================================================================
			// 						Insert Section Ends over here
			// ===============================================================================	
		
		
		}
	}
}

function validate_image($mod='')
{
	global $alert;
	if($_FILES['file_advert']['name'])
	{
		$ext_arr = explode(".",$_FILES['file_advert']['name']);
		$len = count($ext_arr)-1;
		$valid_arr = array('jpg','gif');
		if(!in_array(strtolower($ext_arr[$len]),$valid_arr))
		{
			$alert = 'Invalid Image Format';
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

function save_image()
{
	global $image_path,$db,$ecom_siteid,$Img_Resize;
	$img_id = uniqid('');
	if($_REQUEST['advert_id'])
	{
		//Get the id part table
		$sql_adv = "SELECT advert_source,advert_type FROM adverts WHERE advert_id=".$_REQUEST['advert_id'];
		$ret_adv = $db->query($sql_adv);
		if ($db->num_rows($ret_adv))
		{
			$row_adv = $db->fetch_array($ret_adv);
			if ($row_adv['advert_type']=='IMG' || $row_adv['advert_type']=='SWF')
				@unlink ("$image_path/adverts/".$row_adv['advert_source']);
		}
	}
	$advert_path = "$image_path/adverts";
	if(!file_exists($advert_path)) mkdir($advert_path, 0777);
	$ext_arr = explode(".",$_FILES['file_advert']['name']);
	$len = count($ext_arr)-1;
	$filename = $img_id.".".$ext_arr[$len];
	if($_FILES['file_advert']['type'] != 'application/x-shockwave-flash' || $ext_arr[1] != 'swf'){ // the following part is not applicable for flas/swf files
	//Find the geometry for adverts- for images
		$sql_geo = "SELECT advertimage_geometry FROM themes a, sites b WHERE b.site_id=$ecom_siteid AND a.theme_id=b.themes_theme_id";
		$ret_geo = $db->query($sql_geo);
		if ($db->num_rows($ret_geo))
		{
			$res_geo = $db->fetch_array($ret_geo);
			$geometry['advert'] = $res_geo['advertimage_geometry'];
		}
		if ($_REQUEST['chk_advertresize']==1)
			$Resizeme = 1;
		else
			$Resizeme = 2;	
		$advertmage_name = resize_advertimage($_FILES['file_advert']["tmp_name"], $filename, $geometry["advert"], $_FILES['file_advert']["type"],$Resizeme); // last parameter changed from 1 to 2 for working in local
	}else{
		$advertmage_name = resize_advertimage($_FILES['file_advert']["tmp_name"], $filename, '', $_FILES['file_advert']["type"],0); // for flash files which do not need resizing
	}
	if(!$advertmage_name)
	{
		$ret_arr['alert'] 		= 'Upload Failed';
	}
	else
	{
			$ret_arr['alert'] 		= '';
			$ret_arr['extension'] 	= $ext_arr[$len];
			$ret_arr['filename'] 	= $advertmage_name;
	}
	return $ret_arr;
}
function save_rotate_image($pass_file='',$resize='',$rotate_id=0)
{
        global $image_path,$db,$ecom_siteid,$Img_Resize;
        $img_id = uniqid('');
         
        if($rotate_id)
        {
                //Get the id part table
                $sql_adv = "SELECT rotate_image 
                                FROM 
                                    advert_rotate 
                                WHERE 
                                    rotate_id=".$rotate_id." 
                                LIMIT 
                                    1";
                $ret_adv = $db->query($sql_adv);
                if ($db->num_rows($ret_adv))
                {
                        $row_adv = $db->fetch_array($ret_adv);
                        unlink ("$image_path/adverts/rotate/".$row_adv['rotate_image']);
                }
        }
        $advert_main = "$image_path/adverts/";
        if(!file_exists($advert_main)) mkdir($advert_main, 0777);
        $advert_path = "$image_path/adverts/rotate/";
        if(!file_exists($advert_path)) mkdir($advert_path, 0777);
        $ext_arr = explode(".",$_FILES[$pass_file]['name']);
        $len = count($ext_arr)-1;
        $filename = $img_id.".".$ext_arr[$len];
        //Find the geometry for adverts- for images
        $sql_geo = "SELECT advertimage_geometry FROM themes a, sites b WHERE b.site_id=$ecom_siteid AND a.theme_id=b.themes_theme_id";
        $ret_geo = $db->query($sql_geo);
        if ($db->num_rows($ret_geo))
        {
                $res_geo = $db->fetch_array($ret_geo);
                $geometry['advert'] = $res_geo['advertimage_geometry'];
        }
        if ($resize==1)
                $Resizeme = 1;
        else
                $Resizeme = 2;  
        $advertmage_name = resize_advertrotateimage($_FILES[$pass_file]["tmp_name"], $filename, $geometry["advert"], $_FILES[$pass_file]["type"],$Resizeme); // last parameter changed from 1 to 2 for working in local
        if(!$advertmage_name)
        {
                $ret_arr['alert']               = 'Upload Failed rotate';
        }
        else
        {
                        $ret_arr['alert']               = '';
                        $ret_arr['extension']   = $ext_arr[$len];
                        $ret_arr['filename']    = $advertmage_name;
        }
        return $ret_arr;
}
//Function to resize and copy the uploaded files to required location
function resize_advertimage($old, $new, $geometry, $exten,$resize_me = 1)
	{
		global $image_path;
		$convert_path = CONVERT_PATH;
		
		// Probably not necessary, but might as well
		if(!is_uploaded_file($old)) return FALSE;

		if ($resize_me==1)
		{
			$command = $convert_path."/convert \"$old\" -geometry \"$geometry\" -interlace Line \"$image_path/adverts/$new\" 2>&1";
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
			$new_path = "$image_path/adverts/$new";
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
 function resize_advertrotateimage($old, $new, $geometry, $exten,$resize_me = 1)
        {
                global $image_path;
                $convert_path = CONVERT_PATH;
                 // Probably not necessary, but might as well
                if(!is_uploaded_file($old)) return FALSE;
                if ($resize_me==1)
                {
                        $command = $convert_path."/convert \"$old\" -geometry \"$geometry\" -interlace Line \"$image_path/adverts/rotate/$new\" 2>&1";
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
                        $new_path = "$image_path/adverts/rotate/$new";
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
?>
