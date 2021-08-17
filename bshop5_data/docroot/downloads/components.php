<?php
	/*#################################################################
	# Script Name 		: components.php
	# Description 		: Page which holds the html script for various components of the site
	# Coded by 			: Sny
	# Created on		: 19-Jun-2009
	# Modified by		: 
	# Modified On		: 
	#################################################################*/
	class components
	{
		// ####################################################################################################
		// Function which holds the display logic for static pages
		// ####################################################################################################
		function mod_staticgroup($grp_array,$title)
		{
			global $position,$ecom_siteid,$db,$ecom_hostname,$Settings_arr,$Captions_arr;
			$Captions_arr['STATIC_PAGES'] 	= getCaptions('STATIC_PAGES');
			$add_statcondition 				= " AND a.pname <> 'Home'";
			$cache_type = '';
			// section to be used with caching
			switch($position)
			{
				case 'top':
					$cache_type		= 'comp_topstatgroup';	
				break;
				case 'bottom':
					$cache_type		= 'comp_bottomstatgroup';	
				break;
				case 'left':
					$cache_type		= 'comp_leftstatgroup';	
				break;
				case 'right':
					$cache_type		= 'comp_rightstatgroup';	
				break;
			}// Cache checking section
				$cache_exists 	= false;
				$cache_required	= false;
				if ($Settings_arr['enable_caching_in_site']==1 and $cache_type != '')
				{
					$cache_required = true;
					if (exists_Cache($cache_type,$grp_array[0]['group_id']))
					{
						$content_cache = getcontent_Cache($cache_type,$grp_array[0]['group_id']);
						if ($content_cache) // if cache exists show it
						{
							echo $content_cache;
							$cache_exists = true;
						}
					}
				} 
				// Do the following only if caching is not enabled or cache does not exists
				if ($cache_exists==false)
				{
					if($cache_required)// if caching is required start recording the output
					{
						ob_start();
					}	
					// ############## Top ##############
					if ($position == 'top') // Case if value of position is top;
					{
						?>
							<div class="topmenu">
						   <ul class="staticlink"> 
					<?php
						if(count($grp_array))
						{
							$disp_done = false;
							//Iterating through the group array to fetch the pages to be shown.
							foreach ($grp_array as $k=>$grpData)
							{
								$sql_pg = "SELECT a.page_id,a.title,a.content,a.page_type,a.page_link,page_link_newwindow    
											FROM 
												static_pages a,static_pagegroup_static_page_map b 
											WHERE
												a.sites_site_id = $ecom_siteid 
												AND b.static_pagegroup_group_id=".$grpData['group_id']." 
												AND a.page_id=b.static_pages_page_id 
												AND static_pages_hide = 0 
												AND hide = 0 
												$add_statcondition 
											ORDER BY 
												static_pages_order DESC";
								
								$ret_pg = $db->query($sql_pg);
								$cnt = $db->num_rows($ret_pg);
								if ($grpData['group_showhomelink']==1)
								{
						?>
									<li class="<?php echo (!$disp_done)?'lnk-fst':'lnk-nor'?>"><h2> <a href="<?php url_link('')?>" class="static" title="<?php echo $Captions_arr['STATIC_PAGES']['STAT_HOME']?>"><?php echo $Captions_arr['STATIC_PAGES']['STAT_HOME']?></a></h2></li>
						<?php
									$disp_done = true;
								}
								while ($row_pg = $db->fetch_array($ret_pg))
								{
									$target = ($row_pg['page_link_newwindow']==1 and $row_pg['page_type']!='Page')?'target="_blank"':'';
								?>
										<li class="<?php echo (!$disp_done)?'lnk-fst':'lnk-nor'?>"><h2><a href="<?php if ($row_pg['page_type']=='Page') url_static_page($row_pg['page_id'],$row_pg['title']); else echo $row_pg['page_link'];?>" class="static" title="<?php echo stripslashes($row_pg['title'])?>" <?php echo $target?>><?php echo stripslashes($row_pg['title']);?></a></h2></li>
								<?php
									$disp_done = true;
								}
								if ($grpData['group_showsitemaplink']==1 )
								{
					?>			<li class="<?php echo (!$disp_done)?'lnk-fst':'lnk-nor'?>"><h2> <a href="<?php url_link('sitemap.html')?>" class="static" title="<?php echo $Captions_arr['STATIC_PAGES']['STAT_SITEMAP']?>"><?php echo $Captions_arr['STATIC_PAGES']['STAT_SITEMAP']?></a></h2></li>
					<?php		
									$disp_done = true;
								}
								if ($grpData['group_showxmlsitemaplink']==1 )
								{
								?>
										<li class="<?php echo (!$disp_done)?'lnk-fst':'lnk-nor'?>"><h2> <a href="<?php url_link('sitemap.xml')?>" class="static" title="<?php echo $Captions_arr['STATIC_PAGES']['STAT_XMLSITEMAP']?>"><?php echo $Captions_arr['STATIC_PAGES']['STAT_XMLSITEMAP']?></a></h2></li>
								<?php	
									$disp_done = true;
								}
								if ($grpData['group_showsavedsearchlink']==1)
								{
					?>				
									<li class="<?php echo (!$disp_done)?'lnk-fst':'lnk-nor'?>"><h2> <a href="<?php url_link('saved-search.html')?>" class="static" title="<?php echo $Captions_arr['STATIC_PAGES']['STAT_SAVEDSEARCH']?>"><?php echo $Captions_arr['STATIC_PAGES']['STAT_SAVEDSEARCH']?></a></h2></li>
					<?php	
									$disp_done = true;	
								}
								if ($grpData['group_showfaqlink']==1 )
								{
					?>		
									<li class="<?php echo (!$disp_done)?'lnk-fst':'lnk-nor'?>"><h2> <a href="<?php url_link('faq.html')?>" class="static" title="<?php echo $Captions_arr['STATIC_PAGES']['STAT_FAQ']?>"><?php echo $Captions_arr['STATIC_PAGES']['STAT_FAQ']?></a></h2></li>
					<?php
									$disp_done = true;
								}
								if ($grpData['group_showhelplink']==1 )
								{
					?>		
									<li class="<?php echo (!$disp_done)?'lnk-fst':'lnk-nor'?>"><h2><a href="<?php url_link('help.html')?>" class="static" title="<?php echo $Captions_arr['STATIC_PAGES']['STAT_HELP']?>"><?php echo $Captions_arr['STATIC_PAGES']['STAT_HELP']?></a></h2></li>
					<?php
									$disp_done = true;
								}
							}
								
						}		
						?>			
						<li class="lnk-lst"></li>
								</ul>  
								</div>
						<?php
					}// End of top
					if ($position == 'bottom') // Case if value of position is bottom;
					{
						if(count($grp_array))
						{
						?>
							<ul class="bottomlinks">
								
						<?php
							//Iterating through the group array to fetch the pages to be shown.
							$i=0;
							$show_top = $shop_bottom = 0;
							foreach ($grp_array as $k=>$grpData)
							{
								$sql_pg = "SELECT a.page_id,a.title,a.content,a.page_type,a.page_link,page_link_newwindow  
											FROM 
												static_pages a,static_pagegroup_static_page_map b 
											WHERE
												a.sites_site_id = $ecom_siteid 
												AND b.static_pagegroup_group_id=".$grpData['group_id']." 
												AND a.page_id=b.static_pages_page_id 
												AND static_pages_hide = 0 
												AND hide = 0 
												$add_statcondition 
											ORDER BY 
												static_pages_order";
								$ret_pg = $db->query($sql_pg);
								if($show_top==0)
								{
									if ($grpData['group_showhomelink']==1)
									{
						?>
										<li><h3><a href="<?php url_link('')?>" class="bottomlink" title="<?php echo $Captions_arr['STATIC_PAGES']['STAT_HOME']?>"><?php echo $Captions_arr['STATIC_PAGES']['STAT_HOME']?></a></h3></li>
						<?php			
									}
										$show_top = 0;
								}	
								
								while ($row_pg = $db->fetch_array($ret_pg))
								{
									$target = ($row_pg['page_link_newwindow']==1 and $row_pg['page_type']!='Page')?'target="_blank"':'';
								?>
										<li><h3><a href="<?php if ($row_pg['page_type']=='Page') url_static_page($row_pg['page_id'],$row_pg['title']); else echo $row_pg['page_link'];?>" class="bottomlink" title="<?php echo stripslashes($row_pg['title'])?>" <?php echo $target?>><?php echo stripslashes($row_pg['title']);?></a></h3></li>
								<?php
								}
							}
								
									if ($grpData['group_showsitemaplink']==1)
									{
						?>
										<li><h3><a href="<?php url_link('sitemap.html')?>" class="bottomlink" title="<?php echo $Captions_arr['STATIC_PAGES']['STAT_SITEMAP']?>"><?php echo $Captions_arr['STATIC_PAGES']['STAT_SITEMAP']?></a></h3></li>
						<?php			
									}
									if ($grpData['group_showxmlsitemaplink']==1 )
									{			
									?>
										<li><h3><a href="<?php url_link('sitemap.xml')?>" class="bottomlink" title="<?php echo $Captions_arr['STATIC_PAGES']['STAT_XMLSITEMAP']?>"><?php echo $Captions_arr['STATIC_PAGES']['STAT_XMLSITEMAP']?></a></h3></li>
									<?php									
									}
									if ($grpData['group_showfaqlink']==1)
									{
						?>
										<li><h3><a href="<?php url_link('faq.html')?>" class="bottomlink" title="<?php echo $Captions_arr['STATIC_PAGES']['STAT_FAQ']?>"><?php echo $Captions_arr['STATIC_PAGES']['STAT_FAQ']?></a></h3></li>
						<?php			
									}
									if ($grpData['group_showhelplink']==1)
									{
						?>
										<li><h3><a href="<?php url_link('help.html')?>" class="bottomlink" title="<?php echo $Captions_arr['STATIC_PAGES']['STAT_HELP']?>"><?php echo $Captions_arr['STATIC_PAGES']['STAT_HELP']?></a></h3></li>
						<?php			
									}
									if ($grpData['group_showsavedsearchlink']==1)
									{
						?>
										<li><h3><a href="<?php url_link('saved-search.html')?>" class="bottomlink" title="<?php echo $Captions_arr['STATIC_PAGES']['STAT_SAVEDSEARCH']?>"><?php echo $Captions_arr['STATIC_PAGES']['STAT_SAVEDSEARCH']?></a></h3></li>
						<?php			
									}
						?>			
								</ul>
						<?php	
						}
					}// End of bottom
					
					if($cache_required)// If caching was required then stop the recording and save the cache and display the cached content
					{
						$content = ob_get_contents();
						ob_end_clean();
						save_Cache($cache_type,$grp_array[0]['group_id'],$content);
						echo $content;
					}
				}	
		}
		// ####################################################################################################
		// Function which holds the display logic for product category groups
		// ####################################################################################################
		function mod_productcatgroup($grp_array,$title)
		{
			global $position,$ecom_siteid,$db,$ecom_hostname,$Settings_arr,$Captions_arr,$ecom_themeid;
			
			// Getting the required sort by field and sort order
				$sort_by 	=  $Settings_arr['category_orderfield'];
				if($sort_by=='cname') $sort_by = 'category_name';
				$sort_by 	=  ($sort_by=='custom')?'b.category_order':$sort_by;
				$sort_order =  $Settings_arr['category_orderby'];
				$sort_str	= " $sort_by $sort_order ";
				// Get the parent of selected category
				$req_cat = $_REQUEST['category_id'];
				if ($req_cat)
				{
					$sql_parent = "SELECT parent_id 
									FROM 
										product_categories 
									WHERE 
										category_id=$req_cat 
									LIMIT 
										1";
					$ret_parent = $db->query($sql_parent);
					if ($db->num_rows($ret_parent))
					{
						list($parent) = $db->fetch_array($ret_parent);
					}
				}	
			// section to be used with caching
			switch($position)
			{
				case 'top':
					$cache_type		= 'comp_topcatgroup';	
				break;
				case 'bottom':
					$cache_type		= 'comp_bottomcatgroup';	
				break;
				case 'left':
					$cache_type		= 'comp_leftcatgroup';	
				break;
				case 'right':
					$cache_type		= 'comp_rightcatgroup';	
				break;
			}// Cache checking section
				$cache_exists 	= false;
				$cache_required	= false;
				if ($Settings_arr['enable_caching_in_site']==1 and !$_REQUEST['category_id'])
				{
					$cache_required = true;
					if (exists_Cache($cache_type,$grp_array[0]['catgroup_id']))
					{
						$content_cache = getcontent_Cache($cache_type,$grp_array[0]['catgroup_id']);
						if ($content_cache) // if cache exists show it
						{
							echo $content_cache;
							$cache_exists = true;
						}
					}
				}
				// Do the following only if caching is not enabled or cache does not exists
				if ($cache_exists==false)
				{
					if($cache_required)// if caching is required start recording the output
					{
						ob_start();
					}	
						if ($position == 'bottom') // Case if value of position is bottom;
						{
							if(count($grp_array))
							{
								//Iterating through the group array to fetch the pages to be shown.
								foreach ($grp_array as $k=>$grpData)
								{
									$sql_cat = "SELECT a.category_id,a.category_name,parent_id 
												FROM 
													product_categories a,product_categorygroup_category b 
												WHERE
													a.sites_site_id = $ecom_siteid 
													AND b.catgroup_id=".$grpData['catgroup_id']." 
													AND a.category_id=b.category_id 
													AND a.category_hide = 0 
												ORDER BY 
													$sort_str";
									$ret_cat = $db->query($sql_cat);
									if ($db->num_rows($ret_cat))
									{
										// Check the listing type for categories in category group
										if ($grpData['catgroup_listtype']== 'Menu')// case if categories are to be shown in list menu
										{
										?>
											<ul class="cat_bottomlinks">
										<?php
											while ($row_cat = $db->fetch_array($ret_cat))
											{
										?>
												<li><h3><a href="<?php url_category($row_cat['category_id'],$row_cat['category_name'],-1)?>" class="cat_bottomlink" title="<?php echo stripslashes($row_cat['category_name'])?>"><?php echo stripslashes($row_cat['category_name']);?></a></h3></li>
										<?php
											}
										?>
										</ul>
										<?php		
										}
									}
								}
							}
						
						}// End of bottom
						elseif ($position=='left' and $_REQUEST['req']=='') // ############## Left  and home ##############
						{
							$prev_grp = 0;
							if(count($grp_array))
							{
								//Iterating through the group array to fetch the pages to be shown.
								foreach ($grp_array as $k=>$grpData)
								{
								$sql_cat = "SELECT a.category_id,a.category_name,parent_id,b.category_displaytype,b.category_islink,a.category_subcatlisttype   
												FROM 
													product_categories a,product_categorygroup_category b 
												WHERE
													a.sites_site_id = $ecom_siteid 
													AND b.catgroup_id=".$grpData['catgroup_id']." 
													AND a.category_id=b.category_id 
													AND a.category_hide = 0 
												ORDER BY 
													$sort_str";
									$ret_cat = $db->query($sql_cat);
									if ($db->num_rows($ret_cat))
									{
									?>
											<div class="lf_catgry">
											<div class="lf_catgry_top"></div>
											<div class="lf_catgry_middle">
									<?php
										// Check the listing type for categories in category group
										if ($grpData['catgroup_listtype']== 'Menu')// case if categories are to be shown in list menu
										{
										?>
											<ul class="category">
										<?php
											while ($row_cat = $db->fetch_array($ret_cat))
											{
												if($prev_grp != $grpData['catgroup_id'])
												{
													if ($grpData['catgroup_hidename']==0) // Decide whether or not to show the groupname
													{
											?>
														<li class="categoryheader"><?php echo stripslashes($title)?></li>
											<?php
													}
														$prev_grp = $grpData['catgroup_id'];
												}
												// Start:- to check for whether the categories under the group is displayed as a heading with/without a link
											if($row_cat['category_displaytype']=='Normal' && $row_cat['category_islink'])
											{
											?>
												 <li><h2><a href="<?php url_category($row_cat['category_id'],$row_cat['category_name'],-1)?>" class="catelink" title="<?php echo stripslashes($row_cat['category_name'])?>"><?php echo stripslashes($row_cat['category_name']);?></a></h2></li>
											<?php
											}
											elseif($row_cat['category_displaytype']=='Normal' && !$row_cat['category_islink'])
											{
											?>
												 <li><h2><?php echo stripslashes($row_cat['category_name']);?></h2></li>
											<?											
											}
											elseif($row_cat['category_displaytype']=='Heading' && $row_cat['category_islink'])
											{
											?>
												 <li class="subcategoryheader"><a href="<?php url_category($row_cat['category_id'],$row_cat['category_name'],-1)?>" class="subcategoryheaderlink" title="<?php echo stripslashes($row_cat['category_name'])?>"><?php echo stripslashes($row_cat['category_name']);?></a></li>
											<?
											}
											elseif($row_cat['category_displaytype']=='Heading' && !$row_cat['category_islink'])
											{
											?>
												 <li class="subcategoryheader"><?php echo stripslashes($row_cat['category_name']);?></span></li>
											<?
											}
											// End:- to check for whether the categories under the gorup is displayed as a heading with/without a link
												if ($row_cat['category_subcatlisttype']=='List' or $row_cat['category_subcatlisttype']=='Both')
												{
													if (($row_cat['category_id']==$_REQUEST['category_id']) or ($row_cat['category_id']==$parent))
													{	
														if ($parent == $row_cat['category_id'])
															$comp_catid = $parent;
														else	
															$comp_catid = $row_cat['category_id'];
														// Check whether any child exists for current category
														$sql_child = "SELECT category_id,category_name,default_catgroup_id
																		FROM 
																			product_categories 
																		WHERE 
																			parent_id=".$comp_catid." 
																			AND sites_site_id=$ecom_siteid 
																			AND category_hide = 0 
																		ORDER BY category_order";
														$ret_child = $db->query($sql_child);
														if ($db->num_rows($ret_child))
														{
															while ($row_child = $db->fetch_array($ret_child))
															{
														?>
																	<li><h2><a href="<?php url_category($row_child['category_id'],$row_child['category_name'],-1)?>" title="<?php echo stripslashes($row_child['category_name'])?>" class="subcategoryheaderlink"><?php echo stripslashes($row_child['category_name']);?></a></h2></li>
														<?php		
															}
														}
													}
												}	
											}
										?>
										</ul>
										<?php		
										}
										else
										{
										?>
											<ul class="category">
										<?php
											if ($grpData['catgroup_hidename']==0) // Decide whether or not to show the groupname
											{
												echo '<li class="categoryheader">'.$title.'</li>';
											}
										?>
											<li>
											<select name="prodcatgroup_<?php echo $grpData['catgroup_id']?>" onchange="handle_dropdownval_sel(this.value)">
											<option value="">-- <?php echo $Captions_arr['COMMON']['SELECT']?> -- </option>
										<?php
												// Case if categories are to be shown in dropdown box
												while ($row_cat = $db->fetch_array($ret_cat))
												{
										?>
													<option value="<?php url_category($row_cat['category_id'],$row_cat['category_name'],-1)?>" <?php echo ($row_cat['category_id']==$_REQUEST['category_id'])?'selected="selected"':''?>><?php echo stripslashes($row_cat['category_name'])?></option>
										<?php		
												}
										?>
											</select>
											</li>
											</ul>
										<?php	
										}
										?>
										</div>
										<div class="lf_catgry_bottom"></div>
										</div>
										<?php
									}	
								}
							}
						}
						elseif ($position=='right' or ($position=='left' and $_REQUEST['req']!='')) // ############## Right or left of inner pages ##############
						{
							$prev_grp = 0;
							if(count($grp_array))
							{
								//Iterating through the group array to fetch the pages to be shown.
								foreach ($grp_array as $k=>$grpData)
								{
									$sql_cat = "SELECT a.category_id,a.category_name,parent_id,b.category_displaytype,
													b.category_islink,a.category_subcatlisttype,a.category_showimageofproduct   
												FROM 
													product_categories a,product_categorygroup_category b 
												WHERE
													a.sites_site_id = $ecom_siteid 
													AND b.catgroup_id=".$grpData['catgroup_id']." 
													AND a.category_id=b.category_id 
													AND a.category_hide = 0 
												ORDER BY 
													$sort_str";
									$ret_cat = $db->query($sql_cat);
									if ($db->num_rows($ret_cat))
									{
										// Check the listing type for categories in category group
										?>
											<div class="rt_ctgry">
											 <div class="rt_ctgry_top"></div>
											 <div class="rt_ctgry_middle">
										<?php
												$cnt =0;
													while ($row_cat = $db->fetch_array($ret_cat))
													{ 
													$cnt ++;
													if($cnt%2==0)
													$cls_cat = 'catgry_list_right';
													else
													$cls_cat = 'catgry_list_left';
													?>
													  <div class="<?php echo  $cls_cat?>">
															  <div class="catgry_img">
															  <?
															  $pass_type = 'image_thumbpath';	
											if ($row_cat['category_showimageofproduct']==0) // Case to check for images directly assigned to category
											{
												// Calling the function to get the image to be shown
												$catimg_arr = get_imagelist('prodcat',$row_cat['category_id'],$pass_type,0,0,1);
											}
											else // Case of check for the first available image of any of the products under this category
											{
												// Calling the function to get the id of products under current category with image assigned to it
												$cur_prodid = find_AnyProductWithImageUnderCategory($row_cat['category_id']);
												if ($cur_prodid)// case if any product with image assigned to it under current category exists
												{
													// Calling the function to get the type of image to shown for current 
													//$pass_type = get_default_imagetype('category');	
													// Calling the function to get the image to be shown
													$catimg_arr = get_imagelist('prod',$cur_prodid,$pass_type,0,0,1);
												}	
											}		
											if(count($catimg_arr))
											{
												$exclude_catid 	= $catimg_arr[0]['image_id']; // exclude id in case of multi images for category
											?>
												<a href="<?php url_category($row_cat['category_id'],$row_cat['category_name'],-1)?>" title="<?php echo $row_cat['category_name']?>">
											<?php		
												show_image(url_root_image($catimg_arr[0][$pass_type],1),$row_cat['category_name'],$row_cat['category_name'],'imgwraptext');
											?>
												</a>
											<?php		
											}
										?>
											</div>
											<div class="catgry_name"><a href="<?php url_category($row_cat['category_id'],$row_cat['category_name'],-1)?>" title="<?php echo $row_cat['category_name']?>"><?php echo $row_cat['category_name']?></a></div>
											</div>
												<?php
											}
										?>
										</div>
										<div class="rt_ctgry_bottom"></div>
										</div>										<?php		
									}
								}
							}	
						}// End of right
						else if($position=='topband')
						{
						 
							if(count($grp_array))
							{
								// Check whether catgroup drop down is supported
								$sql_style	= "SELECT theme_top_cat_dropdownmenu_support FROM themes WHERE theme_id=".$ecom_themeid." LIMIT 1";
								$ret_style 	= $db->query($sql_style);
								if ($db->num_rows($ret_style))
								{
									$row_style	= $db->fetch_array($ret_style);
									$subcatdropdownsupport = $row_style['theme_top_cat_dropdownmenu_support'];
								}
								//Iterating through the group array to fetch the pages to be shown.
								foreach ($grp_array as $k=>$grpData)
								{ 
									$sql_cat = "SELECT a.category_id,a.category_name,parent_id,b.category_subcat_width,a.category_showimageofproduct,a.category_subcatlisttype  
												FROM 
													product_categories a,product_categorygroup_category b 
												WHERE
													a.sites_site_id = $ecom_siteid 
													AND b.catgroup_id=".$grpData['catgroup_id']." 
													AND a.category_id=b.category_id 
													AND a.category_hide = 0 
													$more_conditionsa  
												ORDER BY 
													$sort_str";
									$ret_cat = $db->query($sql_cat);
									if ($db->num_rows($ret_cat))
									{ 
										$grpData['catgroup_show_subcat_indropdown']= 1; // newly added by sony as the value for this was not getting in cart page
										// Check the listing type for categories in category group
										//if ($grpData['catgroup_listtype']== 'Menu')// case if categories are to be shown in list menu
										//{
										if ($grpData['catgroup_show_subcat_indropdown']== 1 and $subcatdropdownsupport)// case if categories are to be shown in list menu
										{ 
											$grpData['catgroup_show_subcat_indropdown_subcount'] = 2;
											if ($grpData['catgroup_show_subcat_indropdown_subcount']== 2)// case if 2 levels of subcategories to be displayed
											{  
										?>
											<div class="category_con">
												<div class="category_left"></div>
													<div class="category_mid">
                                                    <?php 
														/*if($ecom_siteid=='76')
														{
														?>
														 <div class="category_home_button">
                                                 			<a href="<?php url_link('')?>" title="<?php echo stripslash_normal($Captions_arr['STATIC_PAGES']['STAT_HOME'])?>" ><?php echo stripslash_normal($Captions_arr['STATIC_PAGES']['STAT_HOME'])?></a>
														 </div>
														 <?php 
														 }*/
														 ?>
														<ul  id="main_navigation"> 
															<?php
																$center_point =3;
																$cursubcat_cnt = 0;
																$pass_type = 'image_thumbcategorypath';
																?>
																<li><h1> <a href="<?php url_link('')?>" class="category_home" title="<?php echo $Captions_arr['STATIC_PAGES']['STAT_HOME']?>"><img src="<?php url_site_image('home-icn.png')?>"/></a> </h1></li>
																<?php
																while ($row_cat = $db->fetch_array($ret_cat))
																{
																		$img_url = '';
																		if ($row_cat['category_showimageofproduct']==0) // Case to check for images directly assigned to category
																		{
																			// Calling the function to get the type of image to shown for current 
																			//$pass_type = get_default_imagetype('subcategory');	
																			// Calling the function to get the image to be shown
																			$img_arr = get_imagelist('prodcat',$row_cat['category_id'],$pass_type,0,0,1);
																			if(count($img_arr))
																			{
																				$img_url = url_root_image($img_arr[0][$pass_type],1);
																			}
																		}
																		else // Case of check for the first available image of any of the products under this category
																		{
																			// Calling the function to get the id of products under current category with image assigned to it
																			$cur_prodid = find_AnyProductWithImageUnderCategory($row_cat['category_id']);
																			if ($cur_prodid)// case if any product with image assigned to it under current category exists
																			{
																				// Calling the function to get the type of image to shown for current 
																				//$pass_type = get_default_imagetype('subcategory');
																				// Calling the function to get the image to be shown
																				$img_arr = get_imagelist('prod',$cur_prodid,$pass_type,0,0,1);
																				
																				if(count($img_arr))
																				{
																					$img_url = url_root_image($img_arr[0][$pass_type],1);
																				}
																			}
																		}
																		if($img_url!='')
																			$img_str = "background: url(".$img_url.")  center no-repeat;";
																		else
																			$img_str = '';
																?>
																	
								
																	<li>
																	<h2><a href="<?php url_category($row_cat['category_id'],$row_cat['category_name'],-1)?>" class="category_menu" title="<?php echo stripslash_normal($row_cat['category_name'])?>"><span><?php echo ucwords(stripslash_normal($row_cat['category_name']));?></span></a></h2>
																<?php
																if ($row_cat['category_subcatlisttype']=='List' or $row_cat['category_subcatlisttype']=='Both')
																{
																	// Check whether there exists first level subcategories for current category
																	$sql_subcat = "SELECT category_id,category_name 
																					FROM 
																						product_categories 
																					WHERE 
																						parent_id =".$row_cat['category_id']." 
																						AND sites_site_id = $ecom_siteid 
																						AND category_hide = 0 
																						$more_conditions 
																					ORDER BY 
																						category_order";
																	$ret_subcat = $db->query($sql_subcat);
																	$num_rows	= $db->num_rows($ret_subcat);
																	if($num_rows)
																	{
																		if($num_rows>=4)
																		{
																			$nav_cls 	= 'nav-c4';
																		}
																		else
																		{
																			$nav_cls 	= 'nav-c4'; //$nav_cls 	= 'nav-c'.$num_rows;
																		}
																		
																		if($cursubcat_cnt==4)
																		{
																			$align_cls	= 'align-center4';
																		}
																		else
																		{
																			if($cursubcat_cnt<=$center_point)
																			{
																				$align_cls	= 'align-left';
																			}	
																		}	
																		if(($cursubcat_cnt==$center_point))// or ($cursubcat_cnt==($center_point+1)))
																		{
																			if($num_rows>=4)
																				$align_cls	= 'align-center4';
																			else
																				$align_cls	= 'align-center4';;//$align_cls	= 'align-center3';
																		}
																		elseif($cursubcat_cnt>=$center_point)
																			$align_cls	= 'align-right';
																		$cursubcat_cnt++;
																?>
																		<div class="dropdown <?php echo $align_cls.' '.$nav_cls?>">
																		<div class="dropdown_inner_cls">
																																		
																		<div class="menulogo_div">
																		<div class='mainimage_cls' style=" z-index:9999; float:left;<?php echo $img_str?> width:100%; height:100%"></div>
																		<?php $this->Show_shop_image_forcat($row_cat['category_id']); /*?>Shop Logos div<?php */?></div>
																		
																		<?php
																		$subcat_cnt 	= 0;
																		$subcat_maxcnt 	= 3;				
																		while($row_subcat = $db->fetch_array($ret_subcat))
																		{
																			if($subcat_cnt==0)
																				echo '<div class="topsubcat_cont">';
																		?>
																			<ul>
																			<li class="cat_subcat"><a href="<?php url_category($row_subcat['category_id'],$row_subcat['category_name'],-1)?>"><?php echo stripslashes($row_subcat['category_name'])?></a></li>
																			<?php
																			/*
																				// Check whether first level subcategories exists for current subcategory
																				$sql_subsubcat = "SELECT category_id,category_name 
																					FROM 
																						product_categories 
																					WHERE 
																						parent_id =".$row_subcat['category_id']." 
																						AND sites_site_id = $ecom_siteid 
																						AND category_hide = 0 
																						$more_conditions 
																					ORDER BY 
																						category_order";
																				$ret_subsubcat = $db->query($sql_subsubcat);
																				$num_subrows	= $db->num_rows($ret_subsubcat);
																				if($num_subrows)
																				{
																					while ($row_subsubcat = $db->fetch_array($ret_subsubcat))
																					{
																			?>			
																						<li><a href="<?php url_category($row_subsubcat['category_id'],$row_subsubcat['category_name'],-1)?>"><?php echo stripslashes($row_subsubcat['category_name'])?></a></li>
																			<?php
																					}
																				}
																				*/
																			?>
																			</ul>
																<?php	
																			$subcat_cnt++;
																			if($subcat_cnt>=$subcat_maxcnt)
																			{
																				echo '</div>';
																				$subcat_cnt = 0;
																			}
																		}
																		if ($subcat_cnt>0 and $subcat_cnt<$subcat_maxcnt)
																			echo '</div>';
																?>
																		</div>
																		</div>	
																<?php		
																	}
																}		
																?>	
																	</li>
																<?
																}
															?>
														</ul>
													</div>
												<div class="category_right"></div>
											</div>
										<?php	
											}	
										}
										else
										{
											
										?>
											<div class="category_con">
												<div class="category_left"></div>
													<div class="category_mid">
														<ul  id="main_navigation"> 
															<?php
																$center_point = 3;
																$cursubcat_cnt = 1;
																while ($row_cat = $db->fetch_array($ret_cat))
																{
																	?>
																	<li>
																	<h2><a href="<?php url_category($row_cat['category_id'],$row_cat['category_name'],-1)?>" class="category_menu" title="<?php echo stripslash_normal($row_cat['category_name'])?>"><span><?php echo ucwords(stripslash_normal($row_cat['category_name']));?></span></a></h2>
																	</li>
																<?
																}
															?>
														</ul>
													</div>
												<div class="category_right"></div>
											</div> 
										<?php		
										
										}
										//}
									}
								}
							}
						}
						else if($position=="middlebottom")
						{

                               $prev_grp = 0;
							if(count($grp_array))
							{
								//Iterating through the group array to fetch the pages to be shown.
								foreach ($grp_array as $k=>$grpData)
								{
									$sql_cat = "SELECT a.category_id,a.category_name,parent_id,b.category_displaytype,
													b.category_islink,a.category_subcatlisttype,a.category_showimageofproduct,a.category_shortdescription    
												FROM 
													product_categories a,product_categorygroup_category b 
												WHERE
													a.sites_site_id = $ecom_siteid 
													AND b.catgroup_id=".$grpData['catgroup_id']." 
													AND a.category_id=b.category_id 
													AND a.category_hide = 0 
												ORDER BY 
													$sort_str LIMIT 0,3";
									$ret_cat = $db->query($sql_cat);
									if ($db->num_rows($ret_cat))
									{
									?>
                               <div class="contentwrap">
								<?php
								if($title) // check whether title exists
								{
								?>

								<h3><?php echo $title?></h3>
								<?php
								}
								$pass_type = 'image_thumbpath';
								?>
								<?php
								$Captions_arr['COMMON'] = getCaptions('COMMON');							
								$cnts = $db->num_rows($ret_cat);
								$cnt = 0;
								$cls = '';
								$cnt =0;
								while ($row_cat = $db->fetch_array($ret_cat))
								{  
								    $cnt++; 
									if($cnt==3)
									{
										$cls = "saleboxwrap_right";
								    }
								    else
								    {
										$cls = "saleboxwrap";
									}
								?>
								<div class="<?php echo $cls;?>"><div class="saleboxwrap_top"></div>
								<div class="saleboxwrap_bg">
								<div class="saleboxwrap_content">
								<div class="imgBox"><a href="<?php url_category($row_cat['category_id'],$row_cat['category_name'],-1)?>" title="<?php echo stripslash_normal($row_cat['category_name'])?>">
								<?php
								// Calling the function to get the image to be shown
								$img_arr = get_imagelist('prodcat',$row_cat['category_id'],$pass_type,0,0,1);
								if(count($img_arr))
								{
								show_image(url_root_image($img_arr[0][$pass_type],1),$row_cat['category_name'],$row_cat['category_name']);
								}
								else
								{
								// calling the function to get the default image
								$no_img = get_noimage('prodcat',$pass_type); 
								if ($no_img)
								{
								show_image($no_img,$row_cat['category_name'],$row_cat['category_name']);
								}	
								}	
								?>
								</a></div>
								<?php
								 //echo $row_prod['product_shortdesc']."test1";
								 if($row_cat['category_shortdescription']!='')
								 {
								 ?>
								<div class="splContent_bottom">

								<?php echo stripslashes($row_cat['category_shortdescription'])?>
								</div>
								<?php
								}
								else
								{
									?>
									<div class="splContent_bottom">

								<?php echo stripslashes($row_cat['category_name'])?>
								</div>
									<?php
								
								}
								?>
								</div>

								</div>
								<div class="saleboxwrap_bottom"></div></div>
								<?php 
								}
								?>
								</div>
								<?php
								}
                              }
						   }
						}
					if($cache_required)// If caching was required then stop the recording and save the cache and display the cached content
					{
						$content = ob_get_contents();
						ob_end_clean();
						save_Cache($cache_type,$grp_array[0]['catgroup_id'],$content);
						echo $content;
					}
				}
		}
		// ####################################################################################################
		// Function which holds the display logic for quick search section
		// ####################################################################################################
		function mod_quicksearch($title)
		{
			global $Captions_arr,$position,$ecom_hostname,$protectedUrl;
			if ($position=='topband') // show only if position value is left and home page
			{
		?>
		<div class="topwrap_two">
			<div class="top_phone_container">
				<div class="top_phone_txt"><div style="display:block;float:left;padding-left:398px;width:auto;font-size:24px;font-weight: bold;">0174 881 1991<div>
			</div>
		 </div>
		</div>	<div class="search_inner">
					<form method="post" name="frm_quicksearch" class="frm_cls" action="<?php url_link('search.html')?>">
    	    <div class="search_input"> 
<input name="quick_search" type="text" class="srh_input" id="quick_search"  value=""/>            </div>
    		<div class="search_btn">
            <input name="button_submit_search" type="submit" class="srh_btn" id="button3" value=""  />
            </div>
            <input type="hidden" name="search_submit" value="search_submit" />
            </form>
   			</div>
					
			<?php	
			}			
		}
		// ####################################################################################################
		// Function which holds the display logic for customer login
		// ####################################################################################################
		function mod_customerlogin($title)
		{
			global $ecom_siteid,$db,$ecom_hostname,$Captions_arr,$Settings_arr,$image_path,$position;
			if($Settings_arr['showcustomerlogin_as_banner']==1) // Decide whether customer login is to be displayed as banner or in normal style
			{
				if ($position=='left' and $_REQUEST['req']=='') // case of home page
				{
			?>
						<div class="login_con">
						   <div class="login_banner">
							 <div class="signup_btn"><a href="<?php url_link('registration.html')?>" title="<?php echo stripslash_normal($Captions_arr['CUST_LOGIN']['NEW_USER'])?>"><img src="<?php url_site_image('signupbtn.gif')?>" /></a></div>
							 <div class="login_btn"><a href="<?php url_link('custlogin.html')?>"><img src="<?php url_site_image('loginbtn.gif')?>" /></a></div>
						   </div>
						</div>
			<?php
				}
				elseif ($position=='right' or ($position=='left' and $_REQUEST['req']!=''))
				{
			?>
					<div class="login_con_rt">
						   <div class="login_banner_rt">
							 <div class="signup_btn_rt"><a href="<?php url_link('registration.html')?>" title="<?php echo stripslash_normal($Captions_arr['CUST_LOGIN']['NEW_USER'])?>"><img src="<?php url_site_image('signupbtn.gif')?>" /></a></div>
							 <div class="login_btn_rt"><a href="<?php url_link('custlogin.html')?>"><img src="<?php url_site_image('loginbtn.gif')?>" /></a></div>
						   </div>
						</div>
			<?php		
				}
			}
		}
		function Show_shop_image_forcat($cat_id)
		{
		 global $db,$ecom_siteid,$sitesel_curr;
         if($cat_id)
         {
              $sql_shop = "SELECT a.shopbrand_id,a.shopbrand_name,a.shopbrand_hide,b.shop_order  
					FROM product_shopbybrand a,category_shop_map b 
					WHERE a.sites_site_id=$ecom_siteid AND 
					b.shopbybrand_category_id = $cat_id 
					AND a.shopbrand_hide = 0 
					AND a.shopbrand_id = b.shopbybrand_shopbybrand_id 	  
					ORDER BY b.shop_order";
					$ret_shop = $db->query($sql_shop);
									
										$HTML_Content = '';
										$pass_type = 'image_bigpath';
										//$pass_type = 'image_gallerythumbpath';

										$cnts = 0;
										while ($row_shop = $db->fetch_array($ret_shop))
										{
											$show_noimage = false;
											$HTML_image = '';
											//if ($row_shop['shopbrand_showimageofproduct']==0) // Case to check for images directly assigned to shop
											{
												
												// Calling the function to get the image to be shown
												$shopimg_arr = get_imagelist('prodshop',$row_shop['shopbrand_id'],$pass_type,0,0,1); 
												if(count($shopimg_arr))
												{
													$exclude_catid 	= $shopimg_arr[0]['image_id']; // exclude id in case of multi images for category
													$HTML_image 	= show_image(url_root_image($shopimg_arr[0][$pass_type],1),$row_shop['shopbrand_name'],$row_shop['shopbrand_name'],'','',1);
													$show_noimage 	= false;
												}
												else
													$show_noimage = true;
											}
											/*
											else // Case of check for the first available image of any of the products under this category
											{
												// Calling the function to get the id of products under current category with image assigned to it
												$cur_prodid = find_AnyProductWithImageUnderShop($row_shop['shopbrand_id']);
												if ($cur_prodid)// case if any product with image assigned to it under current category exists
												{
													// Calling the function to get the image to be shown
													$img_arr = get_imagelist('prod',$cur_prodid,$pass_type,0,0,1);
													if(count($img_arr))
													{
														$HTML_image = show_image(url_root_image($img_arr[0][$pass_type],1),$row_shop['shopbrand_name'],$row_shop['shopbrand_name'],'','',1);
														$show_noimage = false;
													}
													else// case if no products exists under current shop with image assigned to it
													$show_noimage = true;
												}
												else// case if no products exists under current shop with image assigned to it
													$show_noimage = true;
											}
											*/ 
											if($show_noimage==false)
											{
												$HTML_Content .= '
																	<a href="'.url_shops($row_shop['shopbrand_id'],$row_shop['shopbrand_name'],'').'" title="'.stripslashes($row_shop['shopbrand_name']).'">
																		'.$HTML_image.'
																	</a>
																';
												$cnts++;
											}				
										}
										$divid = uniqid('shopbrand');	
										?>									
										<?=$HTML_Content?>	
										<?php
			}									
		}
		// ####################################################################################################
		// Function which holds the display logic for call back request 
		// ####################################################################################################
		function mod_callback($title)
		{
		   		
			global $ecom_siteid,$db,$ecom_hostname,$position,$Captions_arr,$Settings_arr;
			if ($position=='left' and $_REQUEST['req']=='')
			{
			?>
				<div class="call-class" align="left"><a href="<?php url_link('callback.html')?>"><img src="<?php url_site_image('callback.gif')?>" alt="Call Back" title="Call back request" border="0" /></a></div>
			<?php
			}
			if ($position=='right' or ($position=='left' and $_REQUEST['req']!=''))
			{ ?>
				  <div class="rt_call-class"><a href="<?php url_link('callback.html')?>"><img src="<?php url_site_image('rt-call-back.gif')?>" alt="Call Back" title="Call back request" border="0" /></a></div>
              <?php
			}
		}
		// ####################################################################################################
		// Function which holds the display logic for best sellers
		// ####################################################################################################
		function mod_bestsellers($ret_main,$title,$display_id)
		{
			global $ecom_siteid,$db,$ecom_hostname,$position,$Captions_arr,$Settings_arr;
			if ($db->num_rows($ret_main))
			{
				if ($position=='left' and $_REQUEST['req']=='') // Home left panel
				{	
					?>
				<div class="lf_bestsellr">
				<?php
					if($title) // check whether title exists
					{
				?>
				<div class="lf_bestsellr_top"><?php echo $title?></div>
				<?php } ?>
				<div class="lf_bestsellr_middle">
				<?
				while ($row_main = $db->fetch_array($ret_main))
					{
				?>	
				<div class="lf_bestsellr_pdt">
				<div class="lf_bestsellr_image"><a href="<?php url_product($row_main['product_id'],$row_main['product_name'],-1)?>" title="<?php echo stripslashes($row_main['product_name'])?>">
						<?php
							// Calling the function to get the type of image to shown for current 
							$pass_type = get_default_imagetype('combshelf');
							// Calling the function to get the image to be shown
							$img_arr = get_imagelist('prod',$row_main['product_id'],$pass_type,0,0,1);
							if(count($img_arr))
							{
								show_image(url_root_image($img_arr[0][$pass_type],1),$row_main['product_name'],$row_main['product_name']);
							}
							else
							{
								// calling the function to get the default image
								$no_img = get_noimage('prod',$pass_type); 
								if ($no_img)
								{
									show_image($no_img,$row_main['product_name'],$row_main['product_name']);
								}	
							}	
						?>
						
						</a></div>
				<div class="lf_bestsellr_sep"></div>
				<div class="lf_bestsellr_name"><a href="<?php url_product($row_main['product_id'],$row_main['product_name'],-1)?>" title="<?php echo stripslashes($row_main['product_name'])?>" class="lf_bestsellr_name_link"><?php echo stripslashes($row_main['product_name'])?></a></div>
				</div>
				<? }?>
				
				
				
				<div class="lf_best_all_new"><a href="<? url_link('bestsellers'.$display_id.'.html')?>"  title="<?php echo $Captions_arr['COMMON']['SHOW_ALL']?>" class="pre-bestseller-showall_lf"><?php echo $Captions_arr['COMMON']['SHOW_ALL']?></a></div>

</div><div class="lf_bestsellr_bottom"></div>
				</div>							
					<?php	
				}	
				elseif ($position=='right' or ($position=='left' and $_REQUEST['req']!='')) // right side of inner pages
				{
				?>
					<div class="rt_bestseller">
					<?php
					if($title) // check whether title exists
					{
					?>
						<div class="rt_bestseller_top" ><?php echo $title?></div>
					<?php
					}
					?>
					<div class="rt_bestseller_middle">
					 <table width="100%" border="0" cellpadding="0" cellspacing="0" class="rt_bestseller_table">
					<?php			
						while ($row_main = $db->fetch_array($ret_main))
						{
					?>	
						<tr>
						<td width="28" align="left" valign="middle" class="rt_bestseller_table_tdA">
						<a href="<?php url_product($row_main['product_id'],$row_main['product_name'],-1)?>" title="<?php echo stripslashes($row_main['product_name'])?>">
												<?php
													// Calling the function to get the type of image to shown for current 
													//$pass_type = get_default_imagetype('combshelf');
													$pass_type = 'image_iconpath';
													// Calling the function to get the image to be shown
													$img_arr = get_imagelist('prod',$row_main['product_id'],$pass_type,0,0,1);
													if(count($img_arr))
													{
														show_image(url_root_image($img_arr[0][$pass_type],1),$row_main['product_name'],$row_main['product_name']);
													}
													else
													{
														// calling the function to get the default image
														$no_img = get_noimage('prod',$pass_type); 
														if ($no_img)
														{
															show_image($no_img,$row_main['product_name'],$row_main['product_name']);
														}	
													}	
												?>
												
												</a>
						</td>
						<td width="253" align="left" valign="middle" class="rt_bestseller_table_tdA">
						<a href="<?php url_product($row_main['product_id'],$row_main['product_name'],-1)?>" title="<?php echo stripslashes($row_main['product_name'])?>" class="best_rt_prodlink"><?php echo stripslashes($row_main['product_name'])?></a>
						</td>
						</tr>
					<?php
						}		
					?>
					</table>
					</div>
								<div class="rt_bestseller_bottom"><a href="<? url_link('bestsellers'.$display_id.'.html')?>"  title="<?php echo $Captions_arr['COMMON']['SHOW_ALL']?>" class="pre-bestseller-showall"><?php echo $Captions_arr['COMMON']['SHOW_ALL']?></a></div>
								</div>
				<?php									
				}
			}
		}
		// ####################################################################################################
		// Function which holds the display logic for best sellers
		// ####################################################################################################
		function mod_compare_products($ret_compare_pdts,$title='Compare PrOducts')
		{
			global $ecom_siteid,$db,$ecom_hostname,$position,$Captions_arr,$Settings_arr;
			
			if ($db->num_rows($ret_compare_pdts))
			{
				if ($Settings_arr['product_compare_enable']==1)
				{
				
				?>
					<form name="compare_list" id="compare_list" action="" method="post">	
					<input type="hidden" name="remove_compareid"  value="" />
				<?php 
					if($_REQUEST['disp_id'])
					{
				?>
					<input type="hidden" name="disp_id" id="disp_id" value="<?=$_REQUEST['disp_id']?>">
				
				<? 
					}
				if ($position=='left'  and $_REQUEST['req']=='') // Best sellers is allowed in left or right panels
				{	
				
				?>
				
				<div class="compare_div">
					<div class="compare_top"></div>
						<div class="compare_middle">
							<table width="100%" border="0" cellpadding="0" cellspacing="0" class="compare_table">
							<?php
							if($title) // check whether title exists
							{
							?>
								<tr>
									<td colspan="2" class="compare_table_header"><?php echo $title?></td>
								</tr>
							<? }
							while ($row_compare_pdts = $db->fetch_array($ret_compare_pdts))
							{
							?>	
								<tr>
									<td class="compare_table_td"><img src="<?php url_site_image('comp-icn.gif')?>" onclick="document.common_compare_list.remove_compareid.value=<?=$row_compare_pdts['product_id']?>; if(confirm('Are You sure You want to remove the product from the compare list')){ document.common_compare_list.submit()};" alt="Remove" title="Remove" /></td>
									<td class="compare_table_td"><a href="<?php url_product($row_compare_pdts['product_id'],$row_compare_pdts['product_name'],-1)?>" class="comparelink" title="<?php echo stripslashes($row_compare_pdts['product_name'])?>"><?php echo stripslashes($row_compare_pdts['product_name'])?></a></td>
								</tr>
							<?php
							}
							if(count($_SESSION['compare_products'])>1)
							{
							?>
								<tr>
									<td colspan="2" align="right" valign="top" class="compare_table_td"><a href="<?php url_link('compare_products.html')?>" class="compare_showall" title="<?php echo $Captions_arr['COMMON']['COMPARE_PRODUCTS']?>" target="_blank"><?php echo $Captions_arr['COMMON']['COMPARE_PRODUCTS']?></a></td>
								</tr>
							<? }?>
							</table>
						</div>
					<div class="compare_bottom"></div>															
				</div>
				<?php 
				}
				else if (($position=='left'  and $_REQUEST['req']!='') or $position=='right') // Best sellers is allowed in left or right panels
				{	
				?>
				<div class="rt_compare">
					<div class="rt_compare_top"></div>
					<div class="rt_compare_middle">
						<table width="100%" border="0" cellpadding="0" cellspacing="0" class="compare_table_rt">
							<?php
							if($title) // check whether title exists
							{
							?>
								<tr>
									<td colspan="2" align="left" valign="middle" class="compare_table_header_rt"><?php echo $title ?></td>
								</tr>
							<?php
							 }
							while ($row_compare_pdts = $db->fetch_array($ret_compare_pdts))
							{
							?>
								<tr>
									<td width="26" align="left" valign="middle" class="compare_table_td_rt"><img src="<?php url_site_image('comp-icn.gif')?>" onclick="document.common_compare_list.remove_compareid.value=<?=$row_compare_pdts['product_id']?>; if(confirm('Are You sure You want to remove the product from the compare list')){ document.common_compare_list.submit()};" alt="Remove" title="Remove" /></td>
									<td width="277" align="left" valign="middle" class="compare_table_td_rt"><a href="<?php url_product($row_compare_pdts['product_id'],$row_compare_pdts['product_name'],-1)?>" class="comparelink" title="<?php echo stripslashes($row_compare_pdts['product_name'])?>"><?php echo stripslashes($row_compare_pdts['product_name'])?></a></td>
								</tr>
							<?php 
							}
							if(count($_SESSION['compare_products'])>1)
							{
							?>
								<tr>
									<td colspan="2" align="right" valign="middle" class="compare_table_td_rt"><a href="<?php url_link('compare_products.html')?>" class="compare_showall" title="<?php echo $Captions_arr['COMMON']['COMPARE_PRODUCTS']?>" target="_blank"><?php echo $Captions_arr['COMMON']['COMPARE_PRODUCTS']?></a></td>
								</tr>
							<?
							}
							?>
						</table>
					</div>
					<div class="rt_compare_bottom"></div>
				</div>
				<?php
				}
				?>	
				</form>
				<?php	
				}		
			}
}
		// ####################################################################################################
		// Function which holds the display logic for combo deals 
		// ####################################################################################################
		function mod_combo($comb_arr,$title)
		{
			global $db,$ecom_siteid,$ecom_hostname,$Settings_arr,$Captions_arr,$position,$Settings_arr;
			if ($position=='left' or $position =='right')// Combo deal is allowed only in left or right panel
			{
				// Getting the settings for combo deals from settings table
				// Deciding the sort by field
				$combosort_by			= $Settings_arr['product_orderfield_combo'];
				switch ($combosort_by)
				{
					case 'custom': // case of custom ordering
						$combosort_by		= 'b.comboprod_order';
					break;
					case 'product_name': // case of order by product name
						$combosort_by		= 'a.product_name';
					break;
					case 'price': // case of order by price
						$combosort_by		= 'a.product_webprice';
					break;
					default: // by default order be product name
						$combosort_by		= 'a.product_name';
					break;
				};
				$combosort_order		= $Settings_arr['product_orderby_combo'];
				//Iterating through the combo array to fetch the product to be shown.
				foreach ($comb_arr as $k=>$combData)
				{	
						// Check whether combo_activateperiodchange is set to 1
						$active 	= $combData['combo_activateperiodchange'];
						if($active==1)
						{
							$proceed	= validate_component_dates($combData['combo_displaystartdate'],$combData['combo_displayenddate']);
						}
						else
							$proceed	= true;	
						if ($proceed)
						{
							switch($position)
							{
								case 'left':
									$cache_type		= 'comp_leftcombo';	
								break;
								case 'right':
									$cache_type		= 'comp_rightcombo';	
								break;
							}	
							$cache_exists 	= false;
							$cache_required	= false;
							if ($Settings_arr['enable_caching_in_site']==1)
							{
								$cache_required = true;
								if (exists_Cache($cache_type,$combData['combo_id']))
								{
									$content_cache = getcontent_Cache($cache_type,$combData['combo_id']);
									if ($content_cache) // if cache exists show it
									{
										echo $content_cache;
										$cache_exists = true;
									}
								}
							}
						
							// Do the following only if caching is not enabled or cache does not exists
							if ($cache_exists==false)
							{
								if($cache_required)// if caching is required start recording the output
								{
									ob_start();
								}
								// Get the list of products to be shown in current combo deal
								$sql_prod = "SELECT a.product_id,a.product_name,a.product_default_category_id 
											FROM 
												products a,combo_products b 
											WHERE 
												b.combo_combo_id = ".$combData['combo_id']." 
												AND a.product_id = b.products_product_id 
												AND a.product_hide='N' 
											ORDER BY 
												$combosort_by $combosort_order";
								$ret_prod = $db->query($sql_prod);
								 $totcnt = $db->num_rows($ret_prod);
								if ($db->num_rows($ret_prod))
								{
									if($position =='left' and $_REQUEST['req'] == '') // home page and left
										{
										?>
										<div class="lf_combodeal">
										<?php
											if ($title and $combData['combo_hidename']==0)
											{
										?>			
												<div class="lf_combodeal_top" ><?php echo $title?></div>
										<?php
											}
											?>
										<div class="lf_combodeal_middle">
										<?php
										$cnt =0; 
										$maxcnt = 2;
										$tot_prods = $db->num_rows($ret_prod);
										if($tot_prods<$maxcnt)
											$maxcnt = $tot_prods;
										while ($row_prod = $db->fetch_array($ret_prod))
										{
											$cnt ++;
											?>
											<div class="lf_combodeal_img"><a href="<?php url_combo($combData['combo_id'],$combData['combo_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>">
											<?php
												// Calling the function to get the type of image to shown for current 
												$pass_type = 'image_iconpath';
												// Calling the function to get the image to be shown
												$img_arr = get_imagelist('prod',$row_prod['product_id'],$pass_type,0,0,1);
												if(count($img_arr))
												{
													show_image(url_root_image($img_arr[0][$pass_type],1),$row_prod['product_name'],$row_prod['product_name']);
												}
												else
												{
													// calling the function to get the default image
													$no_img = get_noimage('prod',$pass_type); 
													if ($no_img)
													{
														show_image($no_img,$row_prod['product_name'],$row_prod['product_name']);
													}	
												}	
											?>
											</a>
											</div>
											<?php if($cnt>0 && $cnt%$maxcnt!=0 && $cnt!=$totcnt)
										   { 
										 	 ?>
											<div class="lf_combodeal_plus"></div>
											<?php 
											}
										}?>
										</div>
										<div class="lf_combodeal_bottom"><a href="<?php url_combo($combData['combo_id'],$combData['combo_name'],-1)?>"  title="<?php echo $Captions_arr['COMMON']['SHOW_DEAL']?>" class="lf-combodeal-showall"><?php echo $Captions_arr['COMMON']['SHOW_DEAL']?></a></div>
										</div>
										<? 
										}
										elseif($position=='right' or ($position=='left' and $_REQUEST['req']!=''))
										{
										?>
											<div class="rt_combodeal">
										<?php
										if ($title and $combData['combo_hidename']==0)
										{
										?>	
											<div class="rt_combodeal_top"><?php echo $title?></div>
										<?
										}
										?>
										<div class="rt_combodeal_middle">
										<?
										$cnt =0;
										$maxcnt =4; 
										$tot_prods = $db->num_rows($ret_prod);
										if($tot_prods<$maxcnt)
											$maxcnt = $tot_prods;
										while ($row_prod = $db->fetch_array($ret_prod))
										{
										  $cnt ++;
										  ?>
											<div class="rt_combodeal_img">
											<a href="<?php url_combo($combData['combo_id'],$combData['combo_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>">
											<?php
												// Calling the function to get the type of image to shown for current 
												$pass_type = 'image_iconpath';
												// Calling the function to get the image to be shown
												$img_arr = get_imagelist('prod',$row_prod['product_id'],$pass_type,0,0,1);
												if(count($img_arr))
												{
													show_image(url_root_image($img_arr[0][$pass_type],1),$row_prod['product_name'],$row_prod['product_name']);
												}
												else
												{
													// calling the function to get the default image
													$no_img = get_noimage('prod',$pass_type); 
													if ($no_img)
													{
														show_image($no_img,$row_prod['product_name'],$row_prod['product_name']);
													}	
												}	
											?>
											</a>
											</div>
											<?php 
											if($cnt!=$totcnt && $cnt%$maxcnt!=0 && $cnt>0)
											{
											?>
												<div class="rt_combodeal_plus"></div>
											<?php 
											}
										 }
										 ?>
										</div>
										<div class="rt_combodeal_bottom">
										<a href="<?php url_combo($combData['combo_id'],$combData['combo_name'],-1)?>"  title="<?php echo $Captions_arr['COMMON']['SHOW_DEAL']?>" class="pre-combodeal-showall"><?php echo $Captions_arr['COMMON']['SHOW_DEAL']?></a>
										</div>
										</div>
										<?php	
										}
								}
						
								if($cache_required)// If caching was required then stop the recording and save the cache and display the cached content
								{
									$content = ob_get_contents();
									ob_end_clean();
									save_Cache($cache_type,$combData['combo_id'],$content);
									echo $content;
								}
							}	
					}
				}	
			}	
		}
		// ####################################################################################################
		// Function which holds the display logic for shelves 
		// ####################################################################################################
		function mod_shelf($shelf_arr,$title)
		{
			global $db,$ecom_siteid,$ecom_hostname,$Settings_arr,$Captions_arr,$position;
				
				
				// Getting the settings for shelves from settings table
				// Deciding the sort by field
				$shelfsort_by			= $Settings_arr['product_orderfield_shelf'];
				
				// Getting the limit of products to be shown in left of right components for the shelf
				$limit					= 10;
				switch ($shelfsort_by)
				{
					case 'custom': // case of order by customer fiekd
					$shelfsort_by		= 'b.product_order';
					break;
					case 'product_name': // case of order by product name
					$shelfsort_by		= 'a.product_name';
					break;
					case 'price': // case of order by price
					$shelfsort_by		= 'a.product_webprice';
					break;
					default: // by default order by product name
					$shelfsort_by		= 'a.product_name';
					break;
				};
				$shelfsort_order	= $Settings_arr['product_orderby_shelf'];
				//Iterating through the shelf array to fetch the product to be shown.
				foreach ($shelf_arr as $k=>$shelfData)
				{
					// Check whether shelf_activateperiodchange is set to 1
					$active 	= $shelfData['shelf_activateperiodchange'];
					if($active==1)
					{
						$proceed	= validate_component_dates($shelfData['shelf_displaystartdate'],$shelfData['shelf_displayenddate']);
					}
					else
						$proceed	= true;	
					if ($proceed)
					{
						if($position =='middleright')
						{
						$limit =1  ;
						}
						if($position=='middlebottom')
						{
						$limit = 3;
						}
						
						// Get the list of products to be shown in current shelf
						$sql_prod = "SELECT a.product_id,a.product_name,a.product_default_category_id,a.product_webprice,
											a.product_discount,a.product_discount_enteredasval,a.product_applytax,a.product_bonuspoints,a.product_variables_exists,a.product_variablesaddonprice_exists,a.product_shortdesc       									  
									FROM 
										products a,product_shelf_product b 
									WHERE 
										b.product_shelf_shelf_id = ".$shelfData['shelf_id']." 
										AND a.product_id = b.products_product_id 
										AND a.product_hide = 'N' 
										AND a.sites_site_id =$ecom_siteid 
									ORDER BY 
										$shelfsort_by $shelfsort_order 
									LIMIT 
										$limit";
						$ret_prod = $db->query($sql_prod);
						$totcnt = $db->num_rows($ret_prod );
						if ($db->num_rows($ret_prod))
						{
							if(($position =='middleleft' || $position =='middleright')and $_REQUEST['req'] == '') // home page shelf
							{
								if($position =='middleleft')
							{

								
							if($position == 'middleleft')
							{
							$scrollup_id	=	"scroll_up_left";
							$scrolldown_id	=	"scroll_down_left";
							$scrollbox_id	=	"scroll_box_left";
							$scroll_id		=	"div_scroll1_left";
							}	
							if($position == 'right')
							{
							$scrollup_id	=	"scroll_up_right";
							$scrolldown_id	=	"scroll_down_right";
							$scrollbox_id	=	"scroll_box_right";
							$scroll_id		=	"div_scroll1_right";
							}
							if ($db->num_rows($ret_prod))
							{
							//$pass_type = get_default_imagetype('combshelf');								
							$pass_type = 'image_thumbpath';

							$width_one_set 	= 170;
							$total_cnt		= $db->num_rows($ret_prod);

							$min_number_req	= $total_cnt;
							$min_width_req 	= $width_one_set * $min_number_req;
							$calc_width		= $total_cnt * $width_one_set;
							if($calc_width < $min_width_req)
							$div_width = $min_width_req;
							else
							$div_width = $calc_width; 
							if($title) // check whether title exists
							{
							?>
							<div class="shlf_slr_table_hdrA_left"><h2><?php echo $title?></h2></div>
							<?php
							}
							?>
							<div class="scrolling_wrap_left">
							<div class="link_pdt_outr_left">
							<div class="link_pdt_top_left"></div>
							<div class="link_pdt_conts_left">
							<div class="det_link_pdt_con_left">
							<div class="det_link_nav_left"><a href="#null" onmouseover="scrollDivRight('containerD')" onmouseout="stopMe()"><img src="<?php url_site_image('arrow_left.png')?>"></a></div>
							<div id="containerD" class="det_link_pdt_inner_left">
							<div id="scroller" style="width:<?php echo $div_width?>px">
							<?php
							$cnts = $db->num_rows($ret_prod);
							while($row_prod = $db->fetch_array($ret_prod))
							{ 
							?>
							<div class="scrollimg"><ul>
							<li>
							<?php
							//if($shelfData['shelf_showimage']==1) // whether image is to be displayed
							{   
							?>
							<div class="shelf_thump_wrap">
							<a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslash_normal($row_prod['product_name'])?>">
							<?php
							// Calling the function to get the image to be shown
							$img_arr = get_imagelist('prod',$row_prod['product_id'],$pass_type,0,0,1);
							if(count($img_arr))
							{
							show_image(url_root_image($img_arr[0][$pass_type],1),$row_prod['product_name'],$row_prod['product_name']);
							}
							else
							{
							// calling the function to get the default image
							$no_img = get_noimage('prod',$pass_type); 
							if ($no_img)
							{
							show_image($no_img,$row_prod['product_name'],$row_prod['product_name']);
							}	
							}	
							?>
							</a>
							</div>
							<?php 
							}
							?>
							<div class="thump_details">
							<div class="productname_left"><a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslash_normal($row_prod['product_name'])?>"><?php echo stripslash_normal($row_prod['product_name'])?></a></div>
							<?php
							//if ($shelfData['shelf_showprice']==1)// Check whether description is to be displayed
											{
										?>
										<?php
												$price_class_arr['ul_class'] 		= 'price';
												$price_class_arr['normal_class'] 	= 'productprice';
												$price_class_arr['strike_class'] 	= 'retailprice';
												$price_class_arr['yousave_class'] 	= 'productprice';
												$price_class_arr['discount_class'] 	= 'discountprice';
												echo show_Price($row_prod,$price_class_arr,'cat_detail_1');
												//show_excluding_vat_msg($row_prod,'vat_div');// show excluding VAT msg
										?>
										<?php
											}	
											
										?>
										<?php 
											//if ($shelfData['shelf_showdescription']==1)// Check whether description is to be displayed
											{
										?>
											<!--<div class="prod_list_des"><?php echo stripslashes($row_prod['product_shortdesc'])?><?php show_moreinfo($row_prod,'list_more')?></div>-->
										<?php
											}
											
											
											/*
										?>
											
											<div class="moreinfo">
											<a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>">More Info</a>
											</div>
											*/?> 
											<div class="addtocartWrap">
												<div class="prod_list_buy_left">
												<?php 
												$frm_name = uniqid('catdet_');
												?>
												<form method="post" action="<?php url_link('manage_products.html')?>" name='<?php echo $frm_name?>' id="<?php echo $frm_name?>" class="frm_cls" onsubmit="return product_enterkey(this,<?php echo $row_prod['product_id']?>)">
												<input type="hidden" name="fpurpose" value="" />
												<input type="hidden" name="fproduct_id" value="" />
												<input type="hidden" name="pass_url" value="<?php echo $_SERVER['REQUEST_URI']?>" />
												<input type="hidden" name="fproduct_url" value="<?php url_product($row_prod['product_id'],$row_prod['product_name'])?>" />
												<?php
												$class_arr['ADD_TO_CART']       = '';
												$class_arr['PREORDER']          = '';
												$class_arr['ENQUIRE']           = '';
												$class_arr['QTY_DIV']           = 'normal_shlfA_pdt_input';
												$class_arr['QTY']               = ' ';
												$class_td['QTY']				= 'prod_list_buy_a';
												$class_td['TXT']				= 'prod_list_buy_b';
												$class_td['BTN']				= 'prod_list_buy_c';
												echo show_addtocart_v5($row_prod,$class_arr,$frm_name,false,'','',true,$class_td);
												?>
												</form>
												</div>
											</div>
							</div>

							</li>
							</ul></div>
							<?php
							}
							?>
							</div>
							</div>
							<div class="det_link_nav_left"> <a href="#null" onmouseover="scrollDivLeft('containerD','<?php echo $div_width?>')" onmouseout="stopMe()"><img src="<?php url_site_image('arrow_right.png')?>" /></a></div>
							</div>
							</div>

							<div class="link_pdt_bottom_left"></div>
							</div>
							</div>
							<?php
							}
							}
							else if($position == 'middleright')
							{
								$Captions_arr['COMMON'] = getCaptions('COMMON');							
								$cnts = $db->num_rows($ret_prod);
								while($row_prod = $db->fetch_array($ret_prod))
								{ 
								?>
								<div class="special_offers">
								<?php
								if($title) // check whether title exists
								{
								?>
								<h2><?php echo $title?></h2>			<?php
								}
								$pass_type = 'image_thumbpath';
								?>
									
								<div class="boxContent">
								<div class="splImgWrap">
									<a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslash_normal($row_prod['product_name'])?>">
								<?php
								// Calling the function to get the image to be shown
								$img_arr = get_imagelist('prod',$row_prod['product_id'],$pass_type,0,0,1);
								if(count($img_arr))
								{
								show_image(url_root_image($img_arr[0][$pass_type],1),$row_prod['product_name'],$row_prod['product_name']);
								}
								else
								{
								// calling the function to get the default image
								$no_img = get_noimage('prod',$pass_type); 
								if ($no_img)
								{
								show_image($no_img,$row_prod['product_name'],$row_prod['product_name']);
								}	
								}	
								?>
								</a>
									</div>
                                 <?php
                                 //echo $row_prod['product_shortdesc']."test1";
                                 if($row_prod['product_shortdesc']!='')
                                 {
                                 ?>
								<div class="splContent">
								<?php echo stripslashes($row_prod['product_shortdesc'])?>
								</div>
								<?php
							    }
								?>
								</div>
								 <div class="lf_shlef_all_home"><a href="<?php  url_shelf_all($shelfData['shelf_id'],$shelfData['shelf_name'],-1)?>"  title="<?php echo $Captions_arr['COMMON']['SHOW_ALL']?>" class="newshelf-lf-showall"><?php echo $Captions_arr['COMMON']['SHOW_ALL']?></a></div>  

								</div>
							<?php
								}	
							
							}
								
							}
							else if($position=='middlebottom')
							{
								?>
								<div class="contentwrap">
								<?php
								if($title) // check whether title exists
								{
								?>

								<h3><?php echo $title?></h3>
								<?php
								}
								$pass_type = 'image_thumbpath';
								?>
								<?php
								$Captions_arr['COMMON'] = getCaptions('COMMON');							
								$cnts = $db->num_rows($ret_prod);
								$cnt = 0;
								$cls = '';
								while($row_prod = $db->fetch_array($ret_prod))
								{ 
								    $cnt++; 
									if($cnt==3)
									{
										$cls = "saleboxwrap_right";
								    }
								    else
								    {
										$cls = "saleboxwrap";
									}
								?>
								<div class="<?php echo $cls;?>"><div class="saleboxwrap_top"></div>
								<div class="saleboxwrap_bg">
								<div class="saleboxwrap_content">
								<div class="imgBox"><a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslash_normal($row_prod['product_name'])?>">
								<?php
								// Calling the function to get the image to be shown
								$img_arr = get_imagelist('prod',$row_prod['product_id'],$pass_type,0,0,1);
								if(count($img_arr))
								{
								show_image(url_root_image($img_arr[0][$pass_type],1),$row_prod['product_name'],$row_prod['product_name']);
								}
								else
								{
								// calling the function to get the default image
								$no_img = get_noimage('prod',$pass_type); 
								if ($no_img)
								{
								show_image($no_img,$row_prod['product_name'],$row_prod['product_name']);
								}	
								}	
								?>
								</a></div>
								<?php
								 //echo $row_prod['product_shortdesc']."test1";
								 if($row_prod['product_shortdesc']!='')
								 {
								 ?>
								<div class="splContent_bottom">

								<?php echo stripslashes($row_prod['product_shortdesc'])?>
								</div>
								<?php
								}
								?>


								</div>

								</div>
								<div class="saleboxwrap_bottom"></div></div>
								<?php 
								}
								?>
								</div>
<?php
							}
							else if($position =='left' and $_REQUEST['req'] == '') // home page shelf
							{
								if($shelfData['shelf_currentstyle']=='nor') // case of normal design layout
								{
									?>
									<div class="lf_shlef">
									<div class="lf_shlef_top"></div>
									<div class="lf_shlef_middle">
									<?php
									switch($shelfData['shelf_displaytype'])
									{
										case '1row': // case of one in a row
										case '2row': // case of one in a row
										case '3row': // case of one in a row
												if ($title)
												{
												?>	
													<div class="lf_shlef_name" ><?php echo $title?></div>
												<?php
												}
												while($row_prod = $db->fetch_array($ret_prod))
												{
													if($shelfData['shelf_showtitle']==1)// whether title is to be displayed
													{
													?>				  
														<div class="lf_shlef_link"><h2><a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>"><?php echo stripslashes($row_prod['product_name'])?></a></h2></div>
													<?php
													}
													if($shelfData['shelf_showimage']==1) // whether image is to be displayed
													{
													?>
														<div class="lf_shlef_image">
																<a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>">
																<?php
																	// Calling the function to get the type of image to shown for current 
																	$pass_type = get_default_imagetype('combshelf');
																	// Calling the function to get the image to be shown
																	$img_arr = get_imagelist('prod',$row_prod['product_id'],$pass_type,0,0,1);
																	if(count($img_arr))
																	{
																		show_image(url_root_image($img_arr[0][$pass_type],1),$row_prod['product_name'],$row_prod['product_name']);
																	}
																	else
																	{
																		// calling the function to get the default image
																		$no_img = get_noimage('prod',$pass_type); 
																		if ($no_img)
																		{
																			show_image($no_img,$row_prod['product_name'],$row_prod['product_name']);
																		}	
																	}	
																?>
																</a>
															</div>
													<?php
													}
													if($shelfData['shelf_showprice']==1) // whether price is to be displayed
													{
													?>
														<div class="lf_shlef_price">
														<?php
															$price_class_arr['ul_class'] 		= 'shelf_lf_ul';
															$price_class_arr['normal_class'] 	= 'shelf_lf_normalprice';
															$price_class_arr['strike_class'] 	= 'shelf_lf_strikeprice';
															$price_class_arr['yousave_class'] 	= 'shelf_lf_yousaveprice';
															$price_class_arr['discount_class'] 	= 'shelf_lf_discountprice';
															echo show_Price($row_prod,$price_class_arr,'compshelf');
															show_excluding_vat_msg($row_prod,'vat_div');// show excluding VAT msg
															show_bonus_points_msg($row_prod,'bonus_point'); // Show bonus points
														?>	
														</div>
													<?php
													}
													?>
													<div class="lf_shlef_info"><a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>"  title="<?php echo $Captions_arr['COMMON']['MORE_INFO']?>" class="lf_shlef_info_more"><?php echo $Captions_arr['COMMON']['MORE_INFO']?></a></div>  
													<?php
													}
												?>		
													 <div class="lf_shlef_all"><a href="<?php  url_shelf_all($shelfData['shelf_id'],$shelfData['shelf_name'],-1)?>"  title="<?php echo $Captions_arr['COMMON']['SHOW_ALL']?>" class="shelf-lf-showall"><?php echo $Captions_arr['COMMON']['SHOW_ALL']?></a></div>  
										<?php	
										break;
										case 'dropdown': // case of show shelf as drop down box
											$uniq = uniqid('');
												if($title)
												{
												?>	
													<div class="lf_shlef_name" ><?php echo $title?></div>
												<?php
												}
												?> 
												<div class="lf_shlef_link">
														<select name="cbo_shelf_<?php echo $uniq."_".$shelfData['shelf_id']?>" id="cbo_shelf_<?php echo $uniq."_".$shelfData['shelf_id']?>" onchange="handle_dropdownval_sel(this.value)">
														<option value="">-- <?php echo $Captions_arr['COMMON']['SELECT']?> --</option>
														<?php
														while($row_prod = $db->fetch_array($ret_prod))
														{
														?>
															<option value="<?php echo url_product($row_prod['product_id'],$row_prod['product_name'],1)?>" <?php echo ($row_prod['product_id']==$_REQUEST['product_id'])?'selected':''?>><?php echo stripslashes($row_prod['product_name'])?></option>
														<?php
														}
														?>	
															<option value="<?php echo url_shelf_all($shelfData['shelf_id'],$shelfData['shelf_name'],1)?>">-- <?php echo $Captions_arr['COMMON']['SHOW_ALL']?> --</option>
														</select>
													</div>
										<?php		
										break;
									};
									?>
									</div>
									<div class="lf_shlef_bottom"></div>
									</div>
									<?php	
								}	
								elseif($shelfData['shelf_currentstyle']=='christ') // case of christmas layout
								{
									?>
									<div class="lf_shelfD">
									<?php
									if ($title)
												{
												?>	
									<div class="lf_shelfD_top"><? echo $title?></div><div class="lf_shelfD_middle"><table  class="lf_shelf-spclD_table" border="0" cellpadding="0" cellspacing="0">
									<?php }
									switch($shelfData['shelf_displaytype'])
									{
										case '1row': // case of one in a row
										case '2row': // case of one in a row
										case '3row': // case of one in a row
												
									
									
									while($row_prod = $db->fetch_array($ret_prod))
									{
									?>
									<tr>
									<td><div class="lf_shelf-spclD_img">
									<?php 
									if($shelfData['shelf_showimage']==1) // whether image is to be displayed
									{
									?>
												<a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>">
													<?php
														// Calling the function to get the type of image to shown for current 
														//$pass_type = get_default_imagetype('combshelf');
														$pass_type = 'image_iconpath';
														// Calling the function to get the image to be shown
														$img_arr = get_imagelist('prod',$row_prod['product_id'],$pass_type,0,0,1);
														if(count($img_arr))
														{
															show_image(url_root_image($img_arr[0][$pass_type],1),$row_prod['product_name'],$row_prod['product_name']);
														}
														else
														{
															// calling the function to get the default image
															$no_img = get_noimage('prod',$pass_type); 
															if ($no_img)
															{
																show_image($no_img,$row_prod['product_name'],$row_prod['product_name']);
															}	
														}	
													?>
													</a>
									<?php
									}
									if($shelfData['shelf_showprice']==1) // whether price is to be displayed
									{
										$price_class_arr['ul_class'] 			= 'lf_shelf-spclD_ul';
										$price_class_arr['normal_class'] 		= 'lf_shelf-spclD_normalprice';
										$price_class_arr['strike_class'] 		= 'lf_shelf-spclD_strikeprice';
										$price_class_arr['yousave_class'] 	= 'lf_shelf-spclD_yousaveprice';
										$price_class_arr['discount_class'] 	= 'lf_shelf-spclD_discountprice';
										echo show_Price($row_prod,$price_class_arr,'compshelf');
										show_excluding_vat_msg($row_prod,'vat_div');// show excluding VAT msg
										show_bonus_points_msg($row_prod,'bonus_point'); // Show bonus points
									}
									?>
									</div>
									</td>
									</tr>
									<? }?>
									<tr>
									<td align="right" valign="middle"><a href="<?php  url_shelf_all($shelfData['shelf_id'],$shelfData['shelf_name'],-1)?>"  title="<?php echo $Captions_arr['COMMON']['SHOW_ALL']?>" class="lf_shelf-spclD-showall"><?php echo $Captions_arr['COMMON']['SHOW_ALL']?></a></td>
									</tr>
									<? break;
									case 'dropdown': // case of show shelf as drop down box
										$uniq = uniqid('');
									?> 
										<div class="lf_shlef_link_christ">
												<select name="cbo_shelf_<?php echo $uniq."_".$shelfData['shelf_id']?>" id="cbo_shelf_<?php echo $uniq."_".$shelfData['shelf_id']?>" onchange="handle_dropdownval_sel(this.value)">
												<option value="">-- <?php echo $Captions_arr['COMMON']['SELECT']?> --</option>
												<?php
												while($row_prod = $db->fetch_array($ret_prod))
												{
												?>
													<option value="<?php echo url_product($row_prod['product_id'],$row_prod['product_name'],1)?>" <?php echo ($row_prod['product_id']==$_REQUEST['product_id'])?'selected':''?>><?php echo stripslashes($row_prod['product_name'])?></option>
												<?php
												}
												?>	
													<option value="<?php echo url_shelf_all($shelfData['shelf_id'],$shelfData['shelf_name'],1)?>">-- <?php echo $Captions_arr['COMMON']['SHOW_ALL']?> --</option>
												</select>
											</div>
									<?php		
										break;
									};
										?>
									</table> </div>
									<div class="lf_shelfD_bottom"></div>
									</div>
									<?php	
								}
								elseif($shelfData['shelf_currentstyle']=='new') // case of new year layout
								{
									?>
									<div class="lf_shlef_new">
									<div class="lf_shlef_top_new"></div>
									<div class="lf_shlef_middle_new">
									<?php
									switch($shelfData['shelf_displaytype'])
									{
										case '1row': // case of one in a row
										case '2row': // case of one in a row
										case '3row': // case of one in a row
												if ($title)
												{
												?>	
													<div class="lf_shlef_name_new" ><?php echo $title?></div>
												<?php
												}
												while($row_prod = $db->fetch_array($ret_prod))
												{
													if($shelfData['shelf_showtitle']==1)// whether title is to be displayed
													{
													?>				  
														<div class="lf_shlef_link_new"><h2><a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>"><?php echo stripslashes($row_prod['product_name'])?></a></h2></div>
													<?php
													}
													if($shelfData['shelf_showimage']==1) // whether image is to be displayed
													{
													?>
														<div class="lf_shlef_image_new">
																<a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>">
																<?php
																	// Calling the function to get the type of image to shown for current 
																	$pass_type = get_default_imagetype('combshelf');
																	// Calling the function to get the image to be shown
																	$img_arr = get_imagelist('prod',$row_prod['product_id'],$pass_type,0,0,1);
																	if(count($img_arr))
																	{
																		show_image(url_root_image($img_arr[0][$pass_type],1),$row_prod['product_name'],$row_prod['product_name']);
																	}
																	else
																	{
																		// calling the function to get the default image
																		$no_img = get_noimage('prod',$pass_type); 
																		if ($no_img)
																		{
																			show_image($no_img,$row_prod['product_name'],$row_prod['product_name']);
																		}	
																	}	
																?>
																</a>
															</div>
													<?php
													}
													if($shelfData['shelf_showprice']==1) // whether price is to be displayed
													{
													?>
														 <div class="lf_shlef_price_new">
															<?php
																$price_class_arr['ul_class'] 			= 'shelf_lf_ul_new';
																$price_class_arr['normal_class'] 		= 'shelf_lf_normalprice_new';
																$price_class_arr['strike_class'] 		= 'shelf_lf_strikeprice_new';
																$price_class_arr['yousave_class'] 	= 'shelf_lf_yousaveprice_new';
																$price_class_arr['discount_class'] 	= 'shelf_lf_discountprice_new';
																echo show_Price($row_prod,$price_class_arr,'compshelf');
																show_excluding_vat_msg($row_prod,'vat_div');// show excluding VAT msg
																show_bonus_points_msg($row_prod,'bonus_point'); // Show bonus points
															?>	
															</div>
													<?php
													}
													?>
													<div class="lf_shlef_info_new"><a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>"  title="<?php echo $Captions_arr['COMMON']['MORE_INFO']?>"><?php echo $Captions_arr['COMMON']['MORE_INFO']?></a></div>  
													<?php
													}
												?>		
													 <div class="lf_shlef_all_new"><a href="<?php  url_shelf_all($shelfData['shelf_id'],$shelfData['shelf_name'],-1)?>"  title="<?php echo $Captions_arr['COMMON']['SHOW_ALL']?>" class="newshelf-lf-showall"><?php echo $Captions_arr['COMMON']['SHOW_ALL']?></a></div>  
										<?php	
										break;
										case 'dropdown': // case of show shelf as drop down box
											$uniq = uniqid('');
												if($title)
												{
												?>	
													<div class="lf_shlef_name_new" ><?php echo $title?></div>
												<?php
												}
												?> 
												<div class="lf_shlef_link_new">
														<select name="cbo_shelf_<?php echo $uniq."_".$shelfData['shelf_id']?>" id="cbo_shelf_<?php echo $uniq."_".$shelfData['shelf_id']?>" onchange="handle_dropdownval_sel(this.value)">
														<option value="">-- <?php echo $Captions_arr['COMMON']['SELECT']?> --</option>
														<?php
														while($row_prod = $db->fetch_array($ret_prod))
														{
														?>
															<option value="<?php echo url_product($row_prod['product_id'],$row_prod['product_name'],1)?>" <?php echo ($row_prod['product_id']==$_REQUEST['product_id'])?'selected':''?>><?php echo stripslashes($row_prod['product_name'])?></option>
														<?php
														}
														?>	
															<option value="<?php echo url_shelf_all($shelfData['shelf_id'],$shelfData['shelf_name'],1)?>">-- <?php echo $Captions_arr['COMMON']['SHOW_ALL']?> --</option>
														</select>
													</div>
										<?php		
										break;
									};
									?>
									</div>
									<div class="lf_shlef_bottom_new"></div>
									</div>
									<?php	
								}
							}
							elseif($position=='right' or ($position=='left' and $_REQUEST['req']!=''))
							{
								if($shelfData['shelf_currentstyle']=='nor') // case of normal design layout
								{
									?>
									<div class="rt_shlf">
									<div class="rt_shlf_top"></div>
									<?php
									switch($shelfData['shelf_displaytype'])
									{
										case '1row': // case of one in a row
										case '2row': // case of one in a row
										case '3row': // case of one in a row
												if ($title)
												{
												?>	
													<div class="rt_shlf_name" ><?php echo $title?></div>
												<?php
												}
												?>
												<div class="rt_shlf_middle">
												<?php
												while($row_prod = $db->fetch_array($ret_prod))
												{
												?>
													 <div class="rt_shelf_product">
													<?php
													if($shelfData['shelf_showtitle']==1)// whether title is to be displayed
													{
													?>				  
														<div class="rt_shelf_name"><h2><a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>" class="newyr_shelf_prdlink"><?php echo stripslashes($row_prod['product_name'])?></a></h2></div>
													<?php
													}
													if($shelfData['shelf_showimage']==1) // whether image is to be displayed
													{
													?>
														<div class="rt_shelfimg">
																<a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>">
																<?php
																	// Calling the function to get the type of image to shown for current 
																	$pass_type = get_default_imagetype('combshelf');
																	// Calling the function to get the image to be shown
																	$img_arr = get_imagelist('prod',$row_prod['product_id'],$pass_type,0,0,1);
																	if(count($img_arr))
																	{
																		show_image(url_root_image($img_arr[0][$pass_type],1),$row_prod['product_name'],$row_prod['product_name']);
																	}
																	else
																	{
																		// calling the function to get the default image
																		$no_img = get_noimage('prod',$pass_type); 
																		if ($no_img)
																		{
																			show_image($no_img,$row_prod['product_name'],$row_prod['product_name']);
																		}	
																	}	
																?>
																</a>
															</div>
													<?php
													}
													?>
													<div class="rt_shelf_cnts">
													<?php
													if($shelfData['shelf_showprice']==1) // whether price is to be displayed
													{
													?>
														 
															<?php
																$price_class_arr['ul_class'] 			= 'rt_shelful';
																$price_class_arr['normal_class'] 		= 'rt_shelfnormalprice';
																$price_class_arr['strike_class'] 		= 'rt_shelfstrikeprice';
																$price_class_arr['yousave_class'] 	= 'rt_shelfyousaveprice';
																$price_class_arr['discount_class'] 	= 'rt_shelfdiscountprice';
																echo show_Price($row_prod,$price_class_arr,'compshelf');
																show_excluding_vat_msg($row_prod,'vat_div');// show excluding VAT msg
																show_bonus_points_msg($row_prod,'bonus_point'); // Show bonus points
															?>	
													<?php
													}
													?>
													<div class="rt_shlef_info"><a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>"  title="<?php echo $Captions_arr['COMMON']['MORE_INFO']?>"><?php echo $Captions_arr['COMMON']['MORE_INFO']?></a></div>  
													</div>
													</div>
													<?php
													}
												?>		
													 <div class="rt_shlef_all"><a href="<?php  url_shelf_all($shelfData['shelf_id'],$shelfData['shelf_name'],-1)?>"  title="<?php echo $Captions_arr['COMMON']['SHOW_ALL']?>" class="shelf-rt-showall"><?php echo $Captions_arr['COMMON']['SHOW_ALL']?></a></div>  
													 </div>
										<?php	
										break;
										case 'dropdown': // case of show shelf as drop down box
											$uniq = uniqid('');
												if($title)
												{
												?>	
													<div class="rt_shlf_name" ><?php echo $title?></div>
												<?php
												}
												?> 
												<div class="rt_shlf_middle">
												<div class="rt_shelf_name">
														<select name="cbo_shelf_<?php echo $uniq."_".$shelfData['shelf_id']?>" id="cbo_shelf_<?php echo $uniq."_".$shelfData['shelf_id']?>" onchange="handle_dropdownval_sel(this.value)">
														<option value="">-- <?php echo $Captions_arr['COMMON']['SELECT']?> --</option>
														<?php
														while($row_prod = $db->fetch_array($ret_prod))
														{
														?>
															<option value="<?php echo url_product($row_prod['product_id'],$row_prod['product_name'],1)?>" <?php echo ($row_prod['product_id']==$_REQUEST['product_id'])?'selected':''?>><?php echo stripslashes($row_prod['product_name'])?></option>
														<?php
														}
														?>	
															<option value="<?php echo url_shelf_all($shelfData['shelf_id'],$shelfData['shelf_name'],1)?>">-- <?php echo $Captions_arr['COMMON']['SHOW_ALL']?> --</option>
														</select>
													</div>
													</div>
										<?php		
										break;
									};
									?>
									<div class="rt_shlf_bottom"></div>
									</div>
									<?php	
								}	
								elseif($shelfData['shelf_currentstyle']=='christ') // case of christmas layout
								{
									?>
									  <div class="rt_shelfD">
									<?php
									
									switch($shelfData['shelf_displaytype'])
									{
										case '1row': // case of one in a row
										case '2row': // case of one in a row
										case '3row': // case of one in a row
										
									if ($title)
									{
									?>	<div class="rt_shelfD_top"><? echo $title?></div>
									<? }?>
									<div class="rt_shelfD_middle">
									 <table  class="shelf-spclD_table" border="0" cellpadding="0" cellspacing="0">
									 <tr>
									<?php
									$cnt=0;
									while($row_prod = $db->fetch_array($ret_prod))
									{
									$cnt ++;
									?>
									<td><div class="shelf-spclD_img">
									<?php
									if($shelfData['shelf_showimage']==1) // whether image is to be displayed
									{
									?>
									<a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>">
											<?php
												// Calling the function to get the type of image to shown for current 
												//$pass_type = get_default_imagetype('combshelf');
												$pass_type = 'image_iconpath';
												// Calling the function to get the image to be shown
												$img_arr = get_imagelist('prod',$row_prod['product_id'],$pass_type,0,0,1);
												if(count($img_arr))
												{
													show_image(url_root_image($img_arr[0][$pass_type],1),$row_prod['product_name'],$row_prod['product_name']);
												}
												else
												{
													// calling the function to get the default image
													$no_img = get_noimage('prod',$pass_type); 
													if ($no_img)
													{
														show_image($no_img,$row_prod['product_name'],$row_prod['product_name']);
													}	
												}	
											?>
									</a>
									<? }?>
									
									<?php
									if($shelfData['shelf_showprice']==1) // whether price is to be displayed
									{
									?>
									<?php
											$price_class_arr['ul_class'] 			= 'shelf-spclD_ul';
											$price_class_arr['normal_class'] 		= 'shelf-spclD_normalprice';
											$price_class_arr['strike_class'] 		= 'shelf-spclD_strikeprice';
											$price_class_arr['yousave_class'] 	= 'shelf-spclD_yousaveprice';
											$price_class_arr['discount_class'] 	= 'shelf-spclD_discountprice';
											echo show_Price($row_prod,$price_class_arr,'compshelf');
											
										?>	
									<? }?>
									</div>	</td>
									<?
									if($cnt==$totcnt)
									{
									  echo "</tr>";
									}
									else
									{
									if($cnt%2==0)
										{
										echo "</tr><tr>";
										}
									}
									}
									?> 
								  <tr>
									<td colspan="2" align="right" valign="middle"><a href="<?php  url_shelf_all($shelfData['shelf_id'],$shelfData['shelf_name'],-1)?>"  title="<?php echo $Captions_arr['COMMON']['SHOW_ALL']?>" class="shelf-spclD-showall"><?php echo $Captions_arr['COMMON']['SHOW_ALL']?></a></td>
									</tr>
								 </table>
							
									</div>
		
										<?php	
										break;
										case 'dropdown': // case of show shelf as drop down box
											$uniq = uniqid('');
												if($title)
												{
												?>	
													<div class="rt_shlf_name_christ" ><?php echo $title?></div>
												<?php
												}
												?> 
												<div class="rt_shlf_middle_christ">
												<div class="rt_shelf_name_christ">
														<select name="cbo_shelf_<?php echo $uniq."_".$shelfData['shelf_id']?>" id="cbo_shelf_<?php echo $uniq."_".$shelfData['shelf_id']?>" onchange="handle_dropdownval_sel(this.value)">
														<option value="">-- <?php echo $Captions_arr['COMMON']['SELECT']?> --</option>
														<?php
														while($row_prod = $db->fetch_array($ret_prod))
														{
														?>
															<option value="<?php echo url_product($row_prod['product_id'],$row_prod['product_name'],1)?>" <?php echo ($row_prod['product_id']==$_REQUEST['product_id'])?'selected':''?>><?php echo stripslashes($row_prod['product_name'])?></option>
														<?php
														}
														?>	
															<option value="<?php echo url_shelf_all($shelfData['shelf_id'],$shelfData['shelf_name'],1)?>">-- <?php echo $Captions_arr['COMMON']['SHOW_ALL']?> --</option>
														</select>
													</div>
													</div>
										<?php		
										break;
									};
									?>
								<div class="rt_shelfD_bottom"></div>
      </div>
									<?php	
								}
								elseif($shelfData['shelf_currentstyle']=='new') // case of new year layout
								{
									?>
									<div class="rt_shelfC">
									<?php
									switch($shelfData['shelf_displaytype'])
									{
											case '1row': // case of one in a row
											case '2row': // case of one in a row
											case '3row': // case of one in a row
	?>
										
										<?php if ($title)
										{
										?>	
										<div class="rt_shelfC_top"><?php echo $title?></div>
										<? 
										}
										?>
										<div class="rt_shelfC_middle">
										<table width="100%" border="0" cellspacing="0" cellpadding="0"  class="shelf-spclC_outr">
										<?php
										while($row_prod = $db->fetch_array($ret_prod))
										{
											?>
											<tr>
											<td align="left" valign="middle">
											<table  class="shelf-spclC_table" border="0" cellpadding="0" cellspacing="0">
											<tr>
											<td align="left" valign="middle" class="shelf-spclC_td">
											<?php
											if($shelfData['shelf_showimage']==1) // whether image is to be displayed
											{
												?>
												<a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>">
												<?php
												// Calling the function to get the type of image to shown for current 
												//$pass_type = get_default_imagetype('combshelf');
												$pass_type = 'image_iconpath';
												// Calling the function to get the image to be shown
												$img_arr = get_imagelist('prod',$row_prod['product_id'],$pass_type,0,0,1);
												if(count($img_arr))
												{
												show_image(url_root_image($img_arr[0][$pass_type],1),$row_prod['product_name'],$row_prod['product_name']);
												}
												else
												{
													// calling the function to get the default image
													$no_img = get_noimage('prod',$pass_type); 
													if ($no_img)
													{
														show_image($no_img,$row_prod['product_name'],$row_prod['product_name']);
													}	
												}	
												?>
												</a>
												<?php
											}
											if($shelfData['shelf_showtitle']==1)// whether title is to be displayed
											{
											?>	 <br /><a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>"><?php echo stripslashes($row_prod['product_name'])?></a>
											<?
											}
											?>
											</td>
											<td width="132" align="left" valign="middle" class="shelf-spclC_td">
											<?php
											if($shelfData['shelf_showprice']==1) // whether price is to be displayed
											{
												?>
												<?php
												$price_class_arr['ul_class'] 			= 'shelf-spclC_ul';
												$price_class_arr['normal_class'] 		= 'shelf-spclC_normalprice';
												$price_class_arr['strike_class'] 		= 'shelf-spclC_strikeprice';
												$price_class_arr['yousave_class'] 	= 'shelf-spclC_yousaveprice';
												$price_class_arr['discount_class'] 	= 'shelf-spclC_discountprice';
												echo show_Price($row_prod,$price_class_arr,'compshelf');
												
											}	
										  ?>	
											</td>
											</tr>
											</table></td>
											</tr>
											<? 
										}
										?>
										<tr>
										<td colspan="2" align="right" valign="middle" ><a href="<?php  url_shelf_all($shelfData['shelf_id'],$shelfData['shelf_name'],-1)?>"  title="<?php echo $Captions_arr['COMMON']['SHOW_ALL']?>" class="shelf-spclC-showall"><?php echo $Captions_arr['COMMON']['SHOW_ALL']?> </a></td>
										</tr>
										</table>
										</div>
										
	<?php
										break;
										case 'dropdown': // case of show shelf as drop down box
											$uniq = uniqid('');
												if($title)
												{
												?>	
													<div class="rt_shelfC_top"><?php echo $title?></div>
												<?php
												}
												?> 
												<div class="rt_shelfC_middle">
												<div class="rt_shelf_nam_newe">
														<select name="cbo_shelf_<?php echo $uniq."_".$shelfData['shelf_id']?>" id="cbo_shelf_<?php echo $uniq."_".$shelfData['shelf_id']?>" onchange="handle_dropdownval_sel(this.value)">
														<option value="">-- <?php echo $Captions_arr['COMMON']['SELECT']?> --</option>
														<?php
														while($row_prod = $db->fetch_array($ret_prod))
														{
														?>
															<option value="<?php echo url_product($row_prod['product_id'],$row_prod['product_name'],1)?>" <?php echo ($row_prod['product_id']==$_REQUEST['product_id'])?'selected':''?>><?php echo stripslashes($row_prod['product_name'])?></option>
														<?php
														}
														?>	
															<option value="<?php echo url_shelf_all($shelfData['shelf_id'],$shelfData['shelf_name'],1)?>">-- <?php echo $Captions_arr['COMMON']['SHOW_ALL']?> --</option>
														</select>
													</div>
													</div>
										<?php		
										break;
									};
									?>
									<div class="rt_shelfC_bottom"></div>
										</div>
									<?php	
								}
							}
						}
					}
				}	
		}
		
		// ####################################################################################################
		// Function which holds the display logic for newsletter subscription
		// ####################################################################################################
		function mod_newsletter($title)
		{
			global $Captions_arr,$ecom_hostname,$vImage,$db,$ecom_siteid,$Settings_arr,$vImage,$position;
			$vimgfield = (!$Settings_arr['imageverification_req_newsletter'])?'':'newsletter_Vimg';
			$Captions_arr['NEWS_LETTER'] = getCaptions('NEWS_LETTER');
			if($position =='left' and $_REQUEST['req'] == '') // home page shelf
			{
				if($Settings_arr['shownewsletter_as_banner']==1) // Decide whether customer login is to be displayed as banner or in normal style
				{
				?>
					<div class="nws_div_left">
					<a href="<?php url_link('newsletter.html')?>"><img src="<?php url_site_image('news-left.gif')?>" border="0" /></a>
					</div>
					<?php
				}
				else
				{
		?>
					<div class="nws_div">
			        <div class="nws_top"></div>
			        <div class="nws_middle">
								<form name="frm_newsletter" method="post" action="" class="frm_cls" onsubmit="return newsletter_validation(this)">
			
			        <table class="rt_newslettertable" border="0" cellpadding="0" cellspacing="0">
			            <tbody>
						<?php
						if ($title)
						{
						?>	
			              <tr>
			                <td colspan="2" class="rt_newsletterheader"><?php echo $title ?></td>
			              </tr>
						 <?
						 }
							if($Settings_arr['newsletter_title_req']==1)
						{
						?>	
						<tr>
							<td class="newslettertd"><?php echo $Captions_arr['NEWS_LETTER']['TITLE']?></td>
							<td align="left" valign="top" class="newslettertd">
							<select name="newsletter_title" class="regiinput" id="newsletter_title" >
							<option value="">Select</option>
							<option value="Mr.">Mr.</option>
							<option value="Mrs.">Mrs.</option> 
							<option value="Miss.">Miss.</option> 
							<option value="M/S.">M/S.</option>
							</select>
							</td>
						</tr>	
						<?php
						}
						if($Settings_arr['newsletter_name_req']==1)
						{
						?>
						<tr>
							<td class="newslettertd"><?php echo $Captions_arr['NEWS_LETTER']['NAME']?></td>
							<td align="left" valign="top" class="newslettertd">
							<input name="newsletter_name" type="text" class="rt_newsletterinput" id="newsletter_name" size="12" />				</td>
						</tr>	
						<?php
						}
						?>		 
						<tr>
							<td class="newslettertd"><?php echo $Captions_arr['NEWS_LETTER']['EMAIL']?></td>
							<td align="left" valign="top" class="newslettertd">
							<input name="newsletter_email" type="text" class="rt_newsletterinput" id="newsletter_email" size="12" />				</td>
						</tr>
						<?php
						if($Settings_arr['newsletter_phone_req']==1)
						{
						?>
						<tr>
							<td class="newslettertd"><?php echo $Captions_arr['NEWS_LETTER']['PHONE']?></td>
							<td align="left" valign="top" class="newslettertd">
							<input name="newsletter_phone" type="text" class="rt_newsletterinput" id="newsletter_phone" size="12" />				</td>
						</tr>
						<?php
						}
						if($Settings_arr['newsletter_group_req']==1)
						{
							// Check whether any customer groups exists
							$sql_groups = "SELECT custgroup_id,custgroup_name 
											FROM 
												customer_newsletter_group 
											WHERE 
												sites_site_id = $ecom_siteid AND custgroup_active='1'
											ORDER BY custgroup_name ";
							$ret_groups = $db->query($sql_groups);
							if ($db->num_rows($ret_groups))
							{			
							$cust_group_arr = array();
							while ($row_groups = $db->fetch_array($ret_groups))
							{
								$cst_id 					= $row_groups['custgroup_id'];
								$cust_group_arr[$cst_id]	= stripslashes($row_groups['custgroup_name']);
							}						
						?>
							<tr>
								<td colspan="2" valign="top" class="newslettertd"><?php echo $Captions_arr['NEWS_LETTER']['GROUP']?></td>
							</tr>
							<tr>
								<td colspan="2" align="left" valign="top" class="newslettertd">
							<?php
							if (count($cust_group_arr))
							{ 
								echo generateselectbox('newsletter_group[]',$cust_group_arr,0,'','',5,'',false,'sel_newsletter_group');
							}	
							?>				</td>
							</tr>
						<?php
							}	
						}
					
						?>
						<?php 
							if($Settings_arr['imageverification_req_newsletter']) // if image verification is required
							{
						?>
						  <tr>
					    	<td align="left" colspan="2" class="newslettertd"><?=$Captions_arr['NEWS_LETTER']['ENTER_CODE']?>&nbsp;<span class="redtext">*</span></td>
						  </tr>
						  <tr>
						     <td align="left" valign="middle" colspan="2" class="newslettertd"><img src="<?php url_verification_image('includes/vimg.php?size=4&pass_vname=newsletter_Vimg')?>" border="0" alt="Image Verification"/></td>
						  </tr>
						  <tr>
						    <td  colspan="2" align="left" class="newslettertd">
						      <?php 
								// showing the textbox to enter the image verification code
								$vImage->showCodBox(1,'newsletter_Vimg','class="newsletterinput"'); 
							?>	</td>
						 
										  </tr>
					  <?php
							}
						?>
						<tr>
						<td class="newslettertd" align="left" colspan="2" >
							<input name="newsletter_Submit" type="submit" class="nws_btn" id="newsletter_Submit" value="<?php echo $Captions_arr['NEWS_LETTER']['SUBSCRIBE']?>" />				</td>
			
						</tr>
						<tr>
							<td colspan="2" align="right">&nbsp;</td>
						</tr>
			            </tbody>
			          </table>
					  			</form>
			
			        </div>
			        <div class="nws_bottom"></div>
			      </div>
		<?php
				}
			}
			elseif($position=='right' or ($position=='left' and $_REQUEST['req']!=''))
			{
				if($Settings_arr['shownewsletter_as_banner']==1) // Decide whether customer login is to be displayed as banner or in normal style
				{
				?>
					<div class="nws_div_right">
					<a href="<?php url_link('newsletter.html')?>"><img src="<?php url_site_image('news-right.gif')?>" border="0" /></a>
					</div>
				<?php
				}
				else
				{
		?>
				<div class="rt_nws">
				<div class="rt_nws_top"></div>
				<div class="rt_nws_middle">
				<form name="frm_newsletter" method="post" action="" class="frm_cls" onsubmit="return newsletter_validation(this)">
				<table border="0" cellpadding="0" cellspacing="0" class="newslettertable">
				<?php
				if ($title)
				{
				?>		
					<tr>
					<td colspan="2" class="newsletterheader"><?php echo $title?></td>
					</tr>
				<?php
				}
				if($Settings_arr['newsletter_title_req']==1)
				{
				?>	
				<tr>
					<td class="newslettertd"><?php echo $Captions_arr['NEWS_LETTER']['TITLE']?></td>
					<td align="left" valign="top" class="newslettertd">
					<select name="newsletter_title" class="regiinput" id="newsletter_title" >
					<option value="">Select</option>
					<option value="Mr.">Mr.</option>
					<option value="Mrs.">Mrs.</option>
					<option value="Miss.">Miss.</option> 
					<option value="M/S.">M/S.</option>
					</select>
					</td>
				</tr>	
				<?php
				}
				if($Settings_arr['newsletter_name_req']==1)
				{
				?>
				<tr>
					<td class="newslettertd"><?php echo $Captions_arr['NEWS_LETTER']['NAME']?></td>
					<td align="left" valign="top" class="newslettertd">
					<input name="newsletter_name" type="text" class="newsletterinput" id="newsletter_name" size="15" />				</td>
				</tr>	
				<?php
				}
				?>		 
				<tr>
					<td class="newslettertd"><?php echo $Captions_arr['NEWS_LETTER']['EMAIL']?></td>
					<td align="left" valign="top" class="newslettertd">
					<input name="newsletter_email" type="text" class="newsletterinput" id="newsletter_email" size="15" />				</td>
				</tr>
				<?php
				if($Settings_arr['newsletter_phone_req']==1)
				{
				?>
				<tr>
					<td class="newslettertd"><?php echo $Captions_arr['NEWS_LETTER']['PHONE']?></td>
					<td align="left" valign="top" class="newslettertd">
					<input name="newsletter_phone" type="text" class="newsletterinput" id="newsletter_phone" size="15" />				</td>
				</tr>
				<?php
				}
				if($Settings_arr['newsletter_group_req']==1)
				{
					// Check whether any customer groups exists
					$sql_groups = "SELECT custgroup_id,custgroup_name 
									FROM 
										customer_newsletter_group 
									WHERE 
										sites_site_id = $ecom_siteid AND custgroup_active='1'
									ORDER BY custgroup_name ";
					$ret_groups = $db->query($sql_groups);
					if ($db->num_rows($ret_groups))
					{			
					$cust_group_arr = array();
					while ($row_groups = $db->fetch_array($ret_groups))
					{
						$cst_id 					= $row_groups['custgroup_id'];
						$cust_group_arr[$cst_id]	= stripslashes($row_groups['custgroup_name']);
					}						
				?>
					<tr>
						<td colspan="2" valign="top" class="newslettertd"><?php echo $Captions_arr['NEWS_LETTER']['GROUP']?></td>
					</tr>
					<tr>
						<td colspan="2" align="left" valign="top" class="newslettertd">
					<?php
					if (count($cust_group_arr))
					{ 
						echo generateselectbox('newsletter_group[]',$cust_group_arr,0,'','',5,'',false,'sel_newsletter_group');
					}	
					?>				</td>
					</tr>
				<?php
					}	
				}
			
				?>
				<?php 
					if($Settings_arr['imageverification_req_newsletter']) // if image verification is required
					{
				?>
				  <tr>
			    	<td align="left" colspan="2" class="newslettertd"><?=$Captions_arr['NEWS_LETTER']['ENTER_CODE']?>&nbsp;<span class="redtext">*</span></td>
				  </tr>
				  <tr>
				     <td align="left" valign="middle" colspan="2" class="newslettertd"><img src="<?php url_verification_image('includes/vimg.php?size=4&pass_vname=newsletter_Vimg')?>" border="0" alt="Image Verification"/></td>
				  </tr>
				  <tr>
				    <td  colspan="2" align="left" class="newslettertd">
					<span class="newsletterinput">
				      <?php 
						// showing the textbox to enter the image verification code
						$vImage->showCodBox(1,'newsletter_Vimg','class="newsletterinput"'); 
					?>
					</span>
					</td>
				</tr>
			  <?php
					}
				?>
				<tr>
				<td class="newslettertd" align="left" colspan="2" >
					<input name="newsletter_Submit" type="submit" class="nws_btn" id="newsletter_Submit" value="<?php echo $Captions_arr['NEWS_LETTER']['SUBSCRIBE']?>" />				</td>
	
				</tr>
				<tr>
					<td colspan="2" align="right">&nbsp;</td>
				</tr>
				</table>
				</form>
				</div>
				<div class="rt_nws_bottom"></div>
				</div>
		<?php
				}		
			}
		}
		// ####################################################################################################
		// Function which holds the display logic for Gift Vouchers
		// ####################################################################################################
		function mod_giftvoucher($title)
		{
			global $Captions_arr,$ecom_hostname,$db,$ecom_siteid,$Settings_arr,$vImage,$position;
			$Captions_arr['GIFT_VOUCHER'] = getCaptions('GIFT_VOUCHER');
			$vimgfield = (!$Settings_arr['imageverification_req_voucher'])?'':'buycompgiftvoucher_Vimg';
		    if($position=='left' and $_REQUEST['req']=='')
			{
			?>
			<div class="left_gift_buy"><a href="<?php echo get_buyGiftVoucherURL()?>" class="buygiftvoucherheader" title="<?php echo $Captions_arr['GIFT_VOUCHER']['BUY_VOUCHER']?>"><img src=" <?php url_site_image('buy-gift-left.gif') ?>" /></a></div>
			<?php
		  	}
			else if($position=='right' || ($position=='left' and $_REQUEST['req']!=''))
			{
			 ?>
			     <div class="right_gift_buy">
					 <a href="<?php echo get_buyGiftVoucherURL()?>" class="buygiftvoucherheader" title="<?php echo $Captions_arr['GIFT_VOUCHER']['BUY_VOUCHER']?>">
					  <img src="<?php url_site_image('buy-gift-right.gif') ?>" />
					  </a>
				</div>
			 <?php
			}	
		}
		// ####################################################################################################
		// Function which holds the display logic for spending giftvoucher or promotional code
		// ####################################################################################################
		function mod_spendvoucher($title)
		{
			global $Captions_arr,$ecom_hostname,$db,$ecom_siteid,$Settings_arr,$vImage,$position;
			if(check_IndividualSslActive())
			{
				$ecom_selfhttp = "https://";
			}
			else
			{
				$ecom_selfhttp = "http://";
			}
			$Captions_arr['GIFT_VOUCHER'] = getCaptions('GIFT_VOUCHER');
		    if($position=='left' and $_REQUEST['req']=='')
			{
			?>
		      <div class="left_gift_use"> 
			  <a href="<?php echo $ecom_selfhttp.$ecom_hostname?>/spend_voucher.html" class="buygiftvoucherheader" title="<?php echo $Captions_arr['GIFT_VOUCHER']['SPEND_VOUCHER_CLICK_HERE']?>"><img src="<?php url_site_image('use-gift-left.gif')?>" /></a></div>
			  <?
			  }
			  else if($position=='right' || ($position=='left' and $_REQUEST['req']!=''))
			  {
			 ?>
			  <div class="right_gift_use"> 
			  <a href="<?php echo $ecom_selfhttp.$ecom_hostname?>/spend_voucher.html" class="buygiftvoucherheader" title="<?php echo $Captions_arr['GIFT_VOUCHER']['SPEND_VOUCHER_CLICK_HERE']?>"><img src="<?php url_site_image('use-gift-right.gif')?>" /></a></div>
			  <?php
			  }
			  ?>
		<?php	
		}
		// ####################################################################################################
		// Function which holds the display logic for Survey
		// ####################################################################################################
		function mod_survey($survey_array,$title)
		{
			global $Captions_arr,$ecom_hostname,$db,$ecom_siteid,$position;
			$Captions_arr['SURVEY'] = getCaptions('SURVEY');
		?>
			
			 <?php
				foreach ($survey_array as $k=>$surveyData)
				{
					// Check whether survay_activateperiodchange is set to 1
					$active 	= $shelfData['survay_activateperiodchange'];
					if($active==1)
					{
						$proceed	= validate_component_dates($surveyData['survay_displaystartdate'],$surveyData['survay_displayenddate']);
					}
					else
						$proceed	= true;	
					
					if($proceed)
					{
				?>
						<form name="survey_frm" action="" method="post" onsubmit="return validate_survey(this)">
				<?php
					if($position =='left' and $_REQUEST['req'] == '') // home page shelf
					{
				?>	
						<div class="lf_sry">
				        <div class="lf_sry_top"></div>
        				<div class="lf_sry_middle">	
						<table width="100%" border="0" cellpadding="2" cellspacing="0" class="surveytable">
				<?php	
						if($title)
						{
				?>
						  <tr>
							<td colspan="2" align="left" valign="top" class="surveytableheader"><?php echo $title?></td>
						  </tr>
			 	<?php
			 			}
				?>		
						  <tr>
							<td colspan="2" align="left" valign="top" class="surveytablequst"><?php echo stripslashes($surveyData['survey_question'])?></td>
						  </tr>
				  <?php
						// Get the options for the survey
						$sql_surveyopt = "SELECT option_id,option_text 
											FROM 
												survey_option 
											WHERE 
												survey_id = ".$surveyData['survey_id']. " 
											ORDER BY option_order ";
						$ret_surveyopt = $db->query($sql_surveyopt);
						if ($db->num_rows($ret_surveyopt))
						{
							while ($row_surveyopt = $db->fetch_array($ret_surveyopt))
							{
				  ?>
							  <tr>
								<td width="26%" height="20" align="right" valign="middle" class="surveytabletd"><input name="survey_opt" type="radio" value="<?php echo $row_surveyopt['option_id']?>" /></td>
								<td align="left" valign="middle" class="surveytabletd"><?php echo stripslashes($row_surveyopt['option_text']);?></td>
							  </tr>
			  <?php
							}
						}
			  ?>
				  <tr>
					<td align="right" valign="middle" class="surveytabletd">&nbsp;</td>
					<td align="left" valign="middle" class="surveytabletdbottom">
					<input type="hidden" name="survey_comp_id" value="<?php echo $surveyData['survey_id']?>" />
					<input name="survey_Submit" type="submit" class="survey_buttongray" value="<?php echo $Captions_arr['SURVEY']['VOTE']?>" /></td>
				  </tr>
				   </table>
				   </div>
			       <div class="lf_sry_bottom"></div>
	      			</div>
			<?php
			}
			elseif($position=='right' or ($position=='left' and $_REQUEST['req']!=''))
			{
			?>
			 <div class="rt_sry">
			<div class="rt_sry_top"></div>
			<div class="rt_sry_middle">
			<table width="100%" border="0" cellpadding="2" cellspacing="0" class="surveytable">
				<?php	
						if($title)
						{
				?>
						  <tr>
							<td colspan="2" align="left" valign="top" class="surveytableheader"><?php echo $title?></td>
						  </tr>
			 	<?php
			 			}
				?>		
						  <tr>
							<td colspan="2" align="left" valign="top" class="surveytablequst"><?php echo stripslashes($surveyData['survey_question'])?></td>
						  </tr>
				  <?php
						// Get the options for the survey
						$sql_surveyopt = "SELECT option_id,option_text 
											FROM 
												survey_option 
											WHERE 
												survey_id = ".$surveyData['survey_id']. " 
											ORDER BY option_order ";
						$ret_surveyopt = $db->query($sql_surveyopt);
						if ($db->num_rows($ret_surveyopt))
						{
							while ($row_surveyopt = $db->fetch_array($ret_surveyopt))
							{
				  ?>
							  <tr>
								<td width="26%" height="20" align="right" valign="middle" class="surveytabletd"><input name="survey_opt" type="radio" value="<?php echo $row_surveyopt['option_id']?>" /></td>
								<td align="left" valign="middle" class="surveytabletd"><?php echo stripslashes($row_surveyopt['option_text']);?></td>
							  </tr>
			  <?php
							}
						}
			  ?>
				  <tr>
					<td align="right" valign="middle" class="surveytabletd">&nbsp;</td>
					<td align="left" valign="middle" class="surveytabletdbottom">
					<input type="hidden" name="survey_comp_id" value="<?php echo $surveyData['survey_id']?>" />
					<input name="survey_Submit" type="submit" class="survey_buttongray" value="<?php echo $Captions_arr['SURVEY']['VOTE']?>" /></td>
				  </tr>
				   </table>
			</div>
			<div class="rt_sry_bottom"></div>
		  	</div>
			<?php
			}
			?>	   
				   </form>
			 <?php
			 		}
					else
					{
						removefrom_Display_Settings($surveyData['survey_id'],'mod_survey');
					}
				}
			 ?> 
		 
		<?php	
		}
		// ####################################################################################################
		// Function which holds the display logic for product shop by brand
		// ####################################################################################################
		function mod_shopbybrandgroup($grp_array,$title)
		{
			global $position,$ecom_siteid,$db,$ecom_hostname,$Settings_arr,$Captions_arr;
			
			// Getting the required sort by field and sort order
			$sort_by 	=  $Settings_arr['shopbybrand_shops_orderfield'];
			$sort_by 	=  ($sort_by=='custom')?'b.shop_order':$sort_by;
			$sort_order =  $Settings_arr['shopbybrand_shops_orderby'];
			$sort_str	= " $sort_by $sort_order ";
			$req_shop = $_REQUEST['shop_id'];
			if ($req_shop)
			{
				$sql_parent = "SELECT shopbrand_parent_id 
								FROM 
									product_shopbybrand 
								WHERE 
									shopbrand_id=$req_shop 
								LIMIT 
									1";
				$ret_parent = $db->query($sql_parent);
				if ($db->num_rows($ret_parent))
				{
					list($parent) = $db->fetch_array($ret_parent);
				}
			}	
			
			$prev_grp = 0;
			if(count($grp_array))
			{
				//Iterating through the array to fetch the product shops to be shown.
				foreach ($grp_array as $k=>$groupData)
				{
					// Check whether shelf_activateperiodchange is set to 1
					$active 	= $groupData['shopbrandgroup_activateperiodchange'];
					if($active==1)
					{
						$proceed	= validate_component_dates($groupData['shopbrandgroup_displaystartdate'],$groupData['shopbrandgroup_displayenddate']);
					}
					else
						$proceed	= true;	
					if ($proceed)
					{
						if ($position == 'left' or $position == 'right') // ############## Left ##############
						{
							if ($groupData['shopbrandgroup_listtype'] == 'Menu')// case if shops are to be shown in list menu
							{
								if($position=='left')
									$cache_type		= 'shop_left_menu';	
								else
									$cache_type		= 'shop_right_menu';	
								$cache_exists 	= false;
								$cache_required	= false;
								
							}
							elseif($groupData['shopbrandgroup_listtype'] =='Dropdown')
							{
								if($position=='left')
									$cache_type		= 'shop_left_dropdown';	
								else
									$cache_type		= 'shop_right_dropdown';
								$cache_exists 	= false;
								$cache_required	= false;	
							}
							elseif($groupData['shopbrandgroup_listtype'] =='Header')
							{
								if($position=='left')
									$cache_type		= 'shop_left_header';	
								else
									$cache_type		= 'shop_right_header';
								$cache_exists 	= false;
								$cache_required	= false;
							}
						}
						elseif($position == 'top')
						{
							$cache_type		= 'shop_top_menu';
							
							$cache_exists 	= false;
							$cache_required	= false;
						}
						elseif($position == 'bottom')
						{
							$cache_type		= 'shop_bottom_menu';
							$cache_exists 	= false;
							$cache_required	= false;
						}
						
						if ($Settings_arr['enable_caching_in_site']==1 and !$_REQUEST['shop_id']) // dont pick from cache in case if clicked on any of the shops
						{
							$cache_required = true;
							if (exists_Cache($cache_type,$groupData['shopbrandgroup_id']))
							{
								$content_cache = getcontent_Cache($cache_type,$groupData['shopbrandgroup_id']);
								if ($content_cache) // if cache exists show it
								{
									echo $content_cache;
									$cache_exists = true;
								}
							}
						}
						if($cache_exists!=true)
						{
							$sql_shop = "SELECT a.shopbrand_id,a.shopbrand_name,shopbrand_parent_id,
												shopbrand_default_shopbrandgroup_id ,shopbrand_subshoplisttype
											FROM 
												product_shopbybrand a,product_shopbybrand_group_shop_map b 
											WHERE
												a.sites_site_id = $ecom_siteid 
												AND b.product_shopbybrand_shopbrandgroup_id = ".$groupData['shopbrandgroup_id']." 
												AND a.shopbrand_id = b.product_shopbybrand_shopbrand_id 
												AND a.shopbrand_hide = 0 
											ORDER BY 
												$sort_str";
							$ret_shop = $db->query($sql_shop);
							if ($db->num_rows($ret_shop))
							{
								if ($position == 'left' and $_REQUEST['req']=='') // ############## Left ##############
								{
								// Check the listing type for shop in product shop group
								if ($groupData['shopbrandgroup_listtype'] == 'Menu')// case if shops are to be shown in list menu
								{
									$cache_type		= 'shop_left_menu';	
									$cache_exists 	= false;
									$cache_required	= false;
									if ($Settings_arr['enable_caching_in_site']==1 and !$_REQUEST['shop_id']) // dont pick from cache in case if clicked on any of the shops
									{
										$cache_required = true;
										if (exists_Cache($cache_type,$groupData['shopbrandgroup_id']))
										{
											$content_cache = getcontent_Cache($cache_type,$groupData['shopbrandgroup_id']);
											if ($content_cache) // if cache exists show it
											{
												echo $content_cache;
												$cache_exists = true;
											}
										}
									}
									// Do the following only if caching is not enabled or cache does not exists
									if ($cache_exists==false)
									{
										if($cache_required)// if caching is required start recording the output
										{
											ob_start();
										}
								?>
								<div class="lf_shpbybrnd">
									<div class="lf_shpbybrnd_top">
									<?php
									if ($groupData['shopbrandgroup_hidename']==0) // Decide whether or not to show the groupname
									{
								?>
										<?php echo stripslashes($title)?>
								<?php
									}
									?>					
									</div>
										<div class="lf_shpbybrnd_middle">
											<ul class="shopleft">
											<?php
											while ($row_shop = $db->fetch_array($ret_shop))
											{
											$showimg_arr[$row_shop['shopbrand_id']] = $row_shop;
											?>
												 <li><h1><a href="<?php url_shops($row_shop['shopbrand_id'],$row_shop['shopbrand_name'],-1)?>" class="shopleftlink" title="<?php echo stripslashes($row_shop['shopbrand_name'])?>"><?php echo stripslashes($row_shop['shopbrand_name']);?></a></h1></li>
											<?php
												if ($row_shop['shopbrand_subshoplisttype']=='List')
												{
													if (($row_shop['shopbrand_id']==$_REQUEST['shop_id']) or ($row_shop['shopbrand_id']==$parent))
													{
													  if($row_shop['shopbrand_id']==$parent)
													  {
													   $comp_shop = $parent;
													  }
													  else
													  $comp_shop = $row_shop['shopbrand_id']; 
													  
														// Check whether any child exists for current shop
														$sql_child = "SELECT shopbrand_id,shopbrand_name,shopbrand_default_shopbrandgroup_id 
																		FROM 
																			product_shopbybrand  
																		WHERE 
																			shopbrand_parent_id=".$comp_shop." 
																			AND sites_site_id=$ecom_siteid 
																		ORDER BY shopbrand_order";
														$ret_child = $db->query($sql_child);
														if ($db->num_rows($ret_child))
														{
														?>
															<li>
															<ul class="subshopleft">
														<?php	
															while ($row_child = $db->fetch_array($ret_child))
															{
														?>
																	<li><h1><a href="<?php url_shops($row_child['shopbrand_id'],$row_child['shopbrand_name'],-1)?>" title="<?php echo stripslashes($row_child['shopbrand_name'])?>" class="shopleftlink"><?php echo stripslashes($row_child['shopbrand_name']);?></a></h1></li>
														<?php		
															}
														?>
															</ul>
															</li>
														<?php	
														}
													}
												}	
											}
											?>
											</ul>
										<?php
										if($groupData['shopbrandgroup_display_rotator']==1)
										{
											$HTML_Content = '';
											$HTML_Content = $this->show_shop_rotator($showimg_arr);
											if($HTML_Content != '')
											{
											?><div class="shp_brnd_scroll">
												<div class="shp_brnd_scroll_con">
													<div class="shp_brnd_scroll_inner">
										<?php
												echo $HTML_Content;
												?>
													</div>
												</div>
											</div>
												<?php
											}
										}	
										?>
										</div>
									<div class="lf_shpbybrnd_bottom"></div>
								</div>
								<?php
										if($cache_required)// If caching was required then stop the recording and save the cache and display the cached content
										{
											$content = ob_get_contents();
											ob_end_clean();
											save_Cache($cache_type,$groupData['shopbrandgroup_id'],$content);
											echo $content;
										}												
									}
										
								}
								elseif($groupData['shopbrandgroup_listtype'] =='Dropdown')
								{
									$cache_type		= 'shop_left_dropdown';	
									$cache_exists 	= false;
									$cache_required	= false;
									if ($Settings_arr['enable_caching_in_site']==1)
									{
										$cache_required = true;
										if (exists_Cache($cache_type,$groupData['shopbrandgroup_id']))
										{
											$content_cache = getcontent_Cache($cache_type,$groupData['shopbrandgroup_id']);
											if ($content_cache) // if cache exists show it
											{
												echo $content_cache;
												$cache_exists = true;
											}
										}
									}
									// Do the following only if caching is not enabled or cache does not exists
									if ($cache_exists==false)
									{
										if($cache_required)// if caching is required start recording the output
										{
											ob_start();
										}
								?>
									<div class="lf_shpbybrnd">
									<div class="lf_shpbybrnd_top">
									<?php
									if ($groupData['shopbrandgroup_hidename']==0) // Decide whether or not to show the groupname
									{
								?>
										<?php echo stripslashes($title)?>
								<?php
									}
									?>					
									</div>
										<div class="lf_shpbybrnd_middle">
											<ul class="shopleft">
											<label>
											<select name="prodshopgroup_<?php echo $groupData['shopbrandgroup_id']?>" onchange="handle_dropdownval_sel(this.value)">
												<option value="">-- <?php echo $Captions_arr['COMMON']['SELECT']?> -- </option>
								<?php
												// Case if categories are to be shown in dropdown box
												while ($row_shop = $db->fetch_array($ret_shop))
												{
												$showimg_arr[$row_shop['shopbrand_id']] = $row_shop;
								?>
													<option value="<?php url_shops($row_shop['shopbrand_id'],$row_shop['shopbrand_name'],-1,$groupData['shopbrandgroup_id'])?>" <?php echo (($row_shop['shopbrand_id']==$_REQUEST['shop_id']) /*and ($groupData['shopbrandgroup_id']==$_REQUEST['shopgroup_id'])*/)?'selected="selected"':''?>><?php echo stripslashes($row_shop['shopbrand_name'])?></option>
								<?php		
												}
								?>
											</select>
										  </label>
											</ul>
										<?php
										if($groupData['shopbrandgroup_display_rotator']==1)
										{
											$HTML_Content = '';
											$HTML_Content = $this->show_shop_rotator($showimg_arr);
											if($HTML_Content != '')
											{
											?><div class="shp_brnd_scroll">
												<div class="shp_brnd_scroll_con">
													<div class="shp_brnd_scroll_inner">
										<?php
												echo $HTML_Content;
												?>
													</div>
												</div>
											</div>
												<?php
											}
										}	
										?>
										</div>
										
									<div class="lf_shpbybrnd_bottom"></div>
								</div>
								<?php
								
										if($cache_required)// If caching was required then stop the recording and save the cache and display the cached content
										{
											$content = ob_get_contents();
											ob_end_clean();
											save_Cache($cache_type,$groupData['shopbrandgroup_id'],$content);
											echo $content;
										}
									}
								}
								elseif($groupData['shopbrandgroup_listtype'] =='Header')
								{
								?>
									<a href="<?php url_link('shopbybrand.html')?>"><img src="<?php url_site_image('shop-left.gif')?>" border="0" title="Click Here" /></a>
				<?php
										if($groupData['shopbrandgroup_display_rotator']==1)
										{
											while ($row_shop = $db->fetch_array($ret_shop))
											{
												$showimg_arr[$row_shop['shopbrand_id']] = $row_shop;
											}	
											$HTML_Content = '';
											$HTML_Content = $this->show_shop_rotator($showimg_arr);
											if($HTML_Content != '')
											{
											?><div class="shp_brnd_scrollC">
												<div class="shp_brnd_scroll_conC">
													<div class="shp_brnd_scroll_innerC">
												<?php
												echo $HTML_Content;
												?>
													</div>
												</div>
											</div>
												<?php
											}
										}
								   }
								} //pos
								elseif ($position=='right' or ($position=='left' and $_REQUEST['req']!='')) // ############## Right ##############
								{
									// Check the listing type for shop in product shop group
									if ($groupData['shopbrandgroup_listtype']== 'Menu')// case if shops are to be shown in list menu
									{
										$cache_type		= 'shop_right_menu';	
										$cache_exists 	= false;
										$cache_required	= false;
										if ($Settings_arr['enable_caching_in_site']==1 and !$_REQUEST['shop_id'])// dont pick from cache in case if clicked on any of the shops
										{
											$cache_required = true;
											if (exists_Cache($cache_type,$groupData['shopbrandgroup_id']))
											{
												$content_cache = getcontent_Cache($cache_type,$groupData['shopbrandgroup_id']);
												if ($content_cache) // if cache exists show it
												{
													echo $content_cache;
													$cache_exists = true;
												}
											}
										}
										// Do the following only if caching is not enabled or cache does not exists
										if ($cache_exists==false)
										{
											if($cache_required)// if caching is required start recording the output
											{
												ob_start();
											}
									?>
									<div class="rt_shpbybrnd">
										<div class="rt_shpbybrnd_top">
										<?php
										if ($groupData['shopbrandgroup_hidename']==0) // Decide whether or not to show the groupname
										{
									?>
											<?php echo stripslashes($title)?>
									<?php
										}
										?>				
										</div>
											<div class="rt_shpbybrnd_middle">
											<ul class="shopright">
											<?php
												while ($row_shop = $db->fetch_array($ret_shop))
												{	
												$showimg_arr[$row_shop['shopbrand_id']] = $row_shop;
												?>
													 <li><h1><a href="<?php url_shops($row_shop['shopbrand_id'],$row_shop['shopbrand_name'],-1,$groupData['shopbrandgroup_id'])?>" class="shoplinkright" title="<?php echo stripslashes($row_shop['shopbrand_name'])?>"><?php echo stripslashes($row_shop['shopbrand_name']);?></a></h1></li>
												<?php
													if ($row_shop['shopbrand_subshoplisttype']=='List')
													{
														if (($row_shop['shopbrand_id']==$_REQUEST['shop_id']) or ($row_shop['shopbrand_id']==$parent))
														{
															if(($row_shop['shopbrand_id']==$parent))
															{
															$comp_id = $parent;
															}
															else
															{
															$comp_id =$row_shop['shopbrand_id'];
															}
															// Check whether any child exists for current shop
															$sql_child = "SELECT shopbrand_id,shopbrand_name,shopbrand_default_shopbrandgroup_id 
																			FROM 
																				product_shopbybrand  
																			WHERE 
																				shopbrand_parent_id=".$comp_id." 
																				AND sites_site_id=$ecom_siteid 
																			ORDER BY shopbrand_order";
															$ret_child = $db->query($sql_child);
									
															if ($db->num_rows($ret_child))
															{
															?>
																<li>
																<ul class="subshopright">
															<?php	
																while ($row_child = $db->fetch_array($ret_child))
																{
															?>
																		<li><h1><a href="<?php url_shops($row_child['shopbrand_id'],$row_child['shopbrand_name'],-1,$groupData['shopbrandgroup_id'],$row_shop['shopbrand_id'])?>" title="<?php echo stripslashes($row_child['shopbrand_name'])?>" class="shoplinkright"><?php echo stripslashes($row_child['shopbrand_name']);?></a></h1></li>
															<?php		
																}
															?>
																</ul>
																</li>
															<?php	
															}
														}
													}	
												}
											?>
											</ul>
											<?php
												if($groupData['shopbrandgroup_display_rotator']==1)
												{
													$HTML_Content = '';
													$HTML_Content = $this->show_shop_rotator($showimg_arr);
													if($HTML_Content != '')
													{
													?><div class="shp_brnd_scrollA">
														<div class="shp_brnd_scroll_conA">
															<div class="shp_brnd_scroll_innerA">
												<?php
														echo $HTML_Content;
														?>
															</div>
														</div>
													</div>
														<?php
													}
												}	
												?>
											</div>
										<div class="rt_shpbybrnd_bottom"></div>
									</div>
									<?php
											if($cache_required)// If caching was required then stop the recording and save the cache and display the cached content
											{
												
												$content = ob_get_contents();
												ob_end_clean();
												save_Cache($cache_type,$groupData['shopbrandgroup_id'],$content);
												echo $content;
											}	
										}	
									}
									elseif($groupData['shopbrandgroup_listtype'] =='Dropdown')
									{
										$cache_type		= 'shop_right_dropdown';	
										$cache_exists 	= false;
										$cache_required	= false;
										if ($Settings_arr['enable_caching_in_site']==1)
										{
											$cache_required = true;
											if (exists_Cache($cache_type,$groupData['shopbrandgroup_id']))
											{
												$content_cache = getcontent_Cache($cache_type,$groupData['shopbrandgroup_id']);
												if ($content_cache) // if cache exists show it
												{
													echo $content_cache;
													$cache_exists = true;
												}
											}
										}
										// Do the following only if caching is not enabled or cache does not exists
										if ($cache_exists==false)
										{
											if($cache_required)// if caching is required start recording the output
											{
												ob_start();
											}
									?>
											<div class="rt_shpbybrnd">
										<div class="rt_shpbybrnd_top">
										<?php
										if ($groupData['shopbrandgroup_hidename']==0) // Decide whether or not to show the groupname
										{
									?>
											<?php echo stripslashes($title)?>
									<?php
										}
										?>				
										</div>
											<div class="rt_shpbybrnd_middle">
											<ul class="shopright">
											<label>
												<select name="prodshopgroup_<?php echo $groupData['shopbrandgroup_id']?>" onchange="handle_dropdownval_sel(this.value)">
													<option value="">-- <?php echo $Captions_arr['COMMON']['SELECT']?> -- </option>
									<?php
													// Case if categories are to be shown in dropdown box
													while ($row_shop = $db->fetch_array($ret_shop))
													{
														$showimg_arr[$row_shop['shopbrand_id']] = $row_shop;
									
									?>
														<option value="<?php url_shops($row_shop['shopbrand_id'],$row_shop['shopbrand_name'],-1,$groupData['shopbrandgroup_id'])?>" <?php echo (($row_shop['shopbrand_id']==$_REQUEST['shop_id']) /*and ($groupData['shopbrandgroup_id']==$_REQUEST['shopgroup_id'])*/)?'selected="selected"':''?>><?php echo stripslashes($row_shop['shopbrand_name'])?></option>
									<?php		
													}
									?>
												</select>
											  </label>
											</ul>
											<?php
											if($groupData['shopbrandgroup_display_rotator']==1)
												{
													$HTML_Content = '';
													$HTML_Content = $this->show_shop_rotator($showimg_arr);
													if($HTML_Content != '')
													{
													?><div class="shp_brnd_scrollA">
														<div class="shp_brnd_scroll_conA">
															<div class="shp_brnd_scroll_innerA">
												<?php
														echo $HTML_Content;
														?>
															</div>
														</div>
													</div>
														<?php
													}
												}	
												?>
											</div>
										<div class="rt_shpbybrnd_bottom"></div>
									</div>
									<?php	
											if($cache_required)// If caching was required then stop the recording and save the cache and display the cached content
											{
												$content = ob_get_contents();
												ob_end_clean();
												save_Cache($cache_type,$groupData['shopbrandgroup_id'],$content);
												echo $content;
											}	
										}
									}
									elseif($groupData['shopbrandgroup_listtype'] =='Header')
									{
									?>
										<a href="<?php url_link('shopbybrand.html')?>"><img src="<?php url_site_image('shop-right.gif')?>" border="0" title="Click Here" /></a>
									<?php
											if($groupData['shopbrandgroup_display_rotator']==1)
											{	
												while ($row_shop = $db->fetch_array($ret_shop))
												{
													$showimg_arr[$row_shop['shopbrand_id']] = $row_shop;
												}	
												$HTML_Content = '';
												$HTML_Content = $this->show_shop_rotator($showimg_arr);
												if($HTML_Content != '')
												{
												?><div class="shp_brnd_scrollD">
													<div class="shp_brnd_scroll_conD">
														<div class="shp_brnd_scroll_innerD">
											<?php
													echo $HTML_Content;
													?>
														</div>
													</div>
												</div>
													<?php
												}
											}
									
									}
								}// End of right
								elseif ($position=='top')
								{
								return;
								$HTML_Content = '';
								$pass_type = 'image_gallerythumbpath';
								$cnts = 0;
								$width_one_set 	= 99;
								$min_number_req	= 10;
								$min_width_req 	= $width_one_set * $min_number_req;
								$total_cnt		= $db->num_rows($ret_shop);
								$calc_width		= $total_cnt * $width_one_set;
								if($calc_width < $min_width_req)
									$div_width = $min_width_req;
								else
									$div_width = $calc_width; 
								while ($row_shop = $db->fetch_array($ret_shop))
								{
									$show_noimage = false;
									$HTML_image = '';
									if ($row_shop['shopbrand_showimageofproduct']==0) // Case to check for images directly assigned to shop
									{
										
										// Calling the function to get the image to be shown
										$shopimg_arr = get_imagelist('prodshop',$row_shop['shopbrand_id'],$pass_type,0,0,1); 
										if(count($shopimg_arr))
										{
											$exclude_catid 	= $shopimg_arr[0]['image_id']; // exclude id in case of multi images for category
											$HTML_image 	= show_image(url_root_image($shopimg_arr[0][$pass_type],1),$row_shop['shopbrand_name'],$row_shop['shopbrand_name'],'','',1);
											$show_noimage 	= false;
										}
										else
											$show_noimage = true;
									}
									else // Case of check for the first available image of any of the products under this category
									{
										// Calling the function to get the id of products under current category with image assigned to it
										$cur_prodid = find_AnyProductWithImageUnderShop($row_shop['shopbrand_id']);
										if ($cur_prodid)// case if any product with image assigned to it under current category exists
										{
											// Calling the function to get the image to be shown
											$img_arr = get_imagelist('prod',$cur_prodid,$pass_type,0,0,1);
											if(count($img_arr))
											{
												$HTML_image = show_image(url_root_image($img_arr[0][$pass_type],1),$row_shop['shopbrand_name'],$row_shop['shopbrand_name'],'','',1);
												$show_noimage = false;
											}
											else// case if no products exists under current shop with image assigned to it
											$show_noimage = true;
										}
										else// case if no products exists under current shop with image assigned to it
											$show_noimage = true;
									}
									if($show_noimage==false)
									{
										$HTML_Content .= '
															<div class="shp_brnd_thumbimg_pdt">
																<div class="shp_brnd_thumbimg_image">
																<a href="'.url_shops($row_shop['shopbrand_id'],$row_shop['shopbrand_name'],'').'" title="'.stripslashes($row_shop['shopbrand_name']).'">
																	'.$HTML_image.'
																</a>
																</div>
															</div>';
										$cnts++;
									}				
								}
								$divid = uniqid('shopbrand');	
								?>
								<div class="shp_brnd_con">
								<div class="shp_brnd_left"></div>
								<div class="shp_brnd_mid">
								<div class="shp_brnd_thumbimg_con">
								<div class="shp_brnd_nav"><a href="<?php url_link('shopbybrand.html')?>" onmouseover="scrollDivRight('<?=$divid?>')" onmouseout="stopMe()"><img src="<?php url_site_image('top-shop-arrow-left.gif')?>"></a></div>
								<div id="<?=$divid?>" class="shp_brnd_thumbimg_inner">
								<div id="shp_brnd_thumb" style="width:<?php echo $div_width?>px">
								  <?=$HTML_Content?>
								</div>
								</div>
								<div class="shp_brnd_nav"><a href="<?php url_link('shopbybrand.html')?>" onmouseover="scrollDivLeft('<?=$divid?>',<?php echo ($cnts*90)?>)" onmouseout="stopMe()" o><img src="<?php url_site_image('top-shop-arrow-right.gif')?>"></a></div>
								</div>
								</div>
								<div class="shp_brnd_right"></div>
								</div>
								<?php
								}
								elseif($position == 'bottom')
								{
								$HTML_Content = '';
									$pass_type = 'image_thumbpath';
									$cnts = 0;
									while ($row_shop = $db->fetch_array($ret_shop))
									{
										$show_noimage = false;
										$HTML_image = '';
										if ($row_shop['shopbrand_showimageofproduct']==0) // Case to check for images directly assigned to shop
										{
											
											// Calling the function to get the image to be shown
											$shopimg_arr = get_imagelist('prodshop',$row_shop['shopbrand_id'],$pass_type,0,0,1); 
											if(count($shopimg_arr))
											{
												$exclude_catid 	= $shopimg_arr[0]['image_id']; // exclude id in case of multi images for category
												$HTML_image 	= show_image(url_root_image($shopimg_arr[0][$pass_type],1),$row_shop['shopbrand_name'],$row_shop['shopbrand_name'],'','',1);
												$show_noimage 	= false;
											}
											else
												$show_noimage = true;
										}
										else // Case of check for the first available image of any of the products under this category
										{
											// Calling the function to get the id of products under current category with image assigned to it
											$cur_prodid = find_AnyProductWithImageUnderShop($row_shop['shopbrand_id']);
											if ($cur_prodid)// case if any product with image assigned to it under current category exists
											{
												// Calling the function to get the image to be shown
												$img_arr = get_imagelist('prod',$cur_prodid,$pass_type,0,0,1);
												if(count($img_arr))
												{
													$HTML_image = show_image(url_root_image($img_arr[0][$pass_type],1),$row_shop['shopbrand_name'],$row_shop['shopbrand_name'],'','',1);
													$show_noimage = false;
												}
												else// case if no products exists under current shop with image assigned to it
												$show_noimage = true;
											}
											else// case if no products exists under current shop with image assigned to it
												$show_noimage = true;
										}
										if($show_noimage==false)
										{
											$HTML_Content .= '
																	<a href="'.url_shops($row_shop['shopbrand_id'],$row_shop['shopbrand_name'],'').'" title="'.stripslashes($row_shop['shopbrand_name']).'">
																		'.$HTML_image.'
																	</a>
															';
											$cnts++;
										}				
									}
									$divid = uniqid('shopbrand');	
								?>
									<div class="footerBrandsB" >
									  <?=$HTML_Content?>
									</div>
								<?php
								
								}
							}	
						}
					}
				}
			}
		}
		// Function to support the image rotate in shop by brand menu
		function show_shop_rotator($showimg_arr)
		{
			$shop_ul_id = uniqid('shopmenu_');
			// get the list of rotating images
			$HTML_Content .= '<script type="text/javascript">
								jQuery.noConflict();
								var $j = jQuery;
								$j(document).ready(
									function(){
									$j(\'ul#'.$shop_ul_id.'\').innerfade({ 
										speed: 1000,
										timeout: '.(4*1000).',
										type: \'sequence\',
										containerheight: \'60px\'
									});
								});
								</script>';
			$HTML_Content .= '<ul id="'.$shop_ul_id.'" >';
			$pass_type = 'image_gallerythumbpath';	
			foreach ($showimg_arr as $k=>$row_shop)
			{
				$show_noimage = false;
				if ($row_shop['shopbrand_showimageofproduct']==0) // Case to check for images directly assigned to shop
				{
					// Calling the function to get the image to be shown
					$shopimg_arr = get_imagelist('prodshop',$row_shop['shopbrand_id'],$pass_type,0,0,1); 
					if(count($shopimg_arr))
					{
						$exclude_catid 	= $shopimg_arr[0]['image_id']; // exclude id in case of multi images for category
						$HTML_image 	= show_image(url_root_image($shopimg_arr[0][$pass_type],1),$row_shop['shopbrand_name'],$row_shop['shopbrand_name'],'','',1);
						$show_noimage 	= false;
					}
					else
						$show_noimage = true;
				}
				else // Case of check for the first available image of any of the products under this category
				{
					// Calling the function to get the id of products under current category with image assigned to it
					$cur_prodid = find_AnyProductWithImageUnderShop($row_shop['shopbrand_id']);
					if ($cur_prodid)// case if any product with image assigned to it under current category exists
					{
						// Calling the function to get the image to be shown
						$img_arr = get_imagelist('prod',$cur_prodid,$pass_type,0,0,1);
						if(count($img_arr))
						{
							$HTML_image = show_image(url_root_image($img_arr[0][$pass_type],1),$row_shop['shopbrand_name'],$row_shop['shopbrand_name'],'','',1);
							$show_noimage = false;
						}
						else// case if no products exists under current shop with image assigned to it
						$show_noimage = true;
					}
					else// case if no products exists under current shop with image assigned to it
						$show_noimage = true;
				}
				if($show_noimage==false)
				{
					$link = url_shops($row_shop['shopbrand_id'],$row_shop['shopbrand_name'],'');
					$link_start = $link_end = '';
					if($link!='')
					{
						$link_start     = '<a href="'.$link.'" title="'.$title.'">';
						$link_end       = '</a>';
					}
					$HTML_Content 	.= '<li>'.$link_start.$HTML_image.$link_end.'</li>';
				}						
			}
			$HTML_Content .='</ul>';
			return $HTML_Content;
		}
		// ####################################################################################################
		// Function which holds the display logic for Recently Viewed Products
		// ####################################################################################################
		function mod_recentlyviewedproduct($cookval,$title)
		{
			global $Captions_arr,$ecom_siteid,$db,$Settings_arr,$position;
		   $frm_name = uniqid('recent_');
		?>
				<form name="<? echo $frm_name?>" id="<? echo $frm_name?>" action="" method="post">
				<input type="hidden" name="remove_recent" id="remove_recent" value="remove_recent" />	
		<?php
			if ($position=='left' and $_REQUEST['req']=='') // Home left panel
			{	
			?>
				<div class="recent_view">
						<?php
						if($title)
						{
						?>
						<div class="recent_view_top"><?php echo $title ?></div>
						<? 
						}
						?>
						<div class="recent_view_middle">
						<?php
						$cookarray 	= explode(",",$cookval);
								foreach ($cookarray as $k=>$v)
								{
									$sql_prod = "SELECT product_id, product_name,product_default_category_id  
												FROM 
													products 
												WHERE 
													sites_site_id = $ecom_siteid 
													AND product_hide ='N' 
													AND product_id =".$v;
									$ret_prod = $db->query($sql_prod);
									if ($db->num_rows($ret_prod))
									{
										while ($row_prod = $db->fetch_array($ret_prod))
										{
										?>
										<?php 
													if (!$Settings_arr['recentlyviewed_hide_image'])
													{
												?>	
						<div class="recent_view_img">
											<a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>">
												<?php
													// Calling the function to get the type of image to shown for current 
													$pass_type = get_default_imagetype('recent');
													// Calling the function to get the image to be shown
													$img_arr = get_imagelist('prod',$row_prod['product_id'],$pass_type,0,0,1);
													if(count($img_arr))
													{
														show_image(url_root_image($img_arr[0][$pass_type],1),$row_prod['product_name'],$row_prod['product_name'],'recent_img');
													}
													else
													{
														// calling the function to get the default image
														$no_img = get_noimage('prod',$pass_type); 
														if ($no_img)
														{
															show_image($no_img,$row_prod['product_name'],$row_prod['product_name'],'recent_img');
														}	
													}	
												?>
												</a>
						</div>
						<? }
						}
						}
						}	
						?>
						 </div>
						<div class="recent_view_bottom"><a href="#"  title="<?php echo $Captions_arr['COMMON']['COMON_RECENT']?>"  onclick="if(confirm('Are You sure You want to clear the recent product list')){ document.<?=$frm_name?>.submit()};" class="recent_view-showall"><?php echo $Captions_arr['COMMON']['COMON_RECENT']?></a></div>
					  </div>	
			<?php
			}
			elseif ($position=='right' or ($position=='left' and $_REQUEST['req']!='')) // right side of inner pages
			{
			?>
					<div class="rt_shlf_recent">
					<?php
					if($title) // check whether title exists
					{
					?>
						<div class="rt_shlf_top_recent"><?php echo $title?></div>
					<?php
					}
					?>
					<div class="rt_shlf_middle_recent">
			<?php
					$cookarray 	= explode(",",$cookval);
					foreach ($cookarray as $k=>$v)
					{
						$sql_prod = "SELECT product_id, product_name,product_default_category_id  
									FROM 
										products 
									WHERE 
										sites_site_id = $ecom_siteid 
										AND product_hide ='N' 
										AND product_id =".$v;
						$ret_prod = $db->query($sql_prod);
						if ($db->num_rows($ret_prod))
						{
							while ($row_prod = $db->fetch_array($ret_prod))
							{
							?>
								
								<?php 
									if (!$Settings_arr['recentlyviewed_hide_image'])
									{
								?>	
								<div class="recent_view_img">
								<a href="<?php url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" title="<?php echo stripslashes($row_prod['product_name'])?>">
								<?php
									// Calling the function to get the type of image to shown for current 
									$pass_type = get_default_imagetype('recent');
									// Calling the function to get the image to be shown
									$img_arr = get_imagelist('prod',$row_prod['product_id'],$pass_type,0,0,1);
									if(count($img_arr))
									{
										show_image(url_root_image($img_arr[0][$pass_type],1),$row_prod['product_name'],$row_prod['product_name'],'recent_img');
									}
									else
									{
										// calling the function to get the default image
										$no_img = get_noimage('prod',$pass_type); 
										if ($no_img)
										{
											show_image($no_img,$row_prod['product_name'],$row_prod['product_name'],'recent_img');
										}	
									}	
								?>
								
								</a></div>
								<?php
								}
								?>
							<?php
							}
						}	
					}
			?>
				</div>
				<div class="rt_shlf_bottom_recent"><a href="#"  title="<?php echo $Captions_arr['COMMON']['COMON_RECENT']?>"  onclick="if(confirm('Are You sure You want to clear the recent product list')){ document.<?=$frm_name?>.submit()};" class="recent_view-showall"><?php echo $Captions_arr['COMMON']['COMON_RECENT']?></a></div>
				</div>
			<?php			
			}
		?>	
			</form>	
				
			
		<?php		
		}
		// ####################################################################################################
		// Function which holds the display logic for adverts
		// ####################################################################################################
		function mod_adverts($advert_arr,$title)
		{
			global $ecom_siteid,$db,$position,$Settings_arr;
			if(check_IndividualSslActive())
			{
				$ecom_selfhttp = "https://";
			}
			else
			{
				$ecom_selfhttp = "http://";
			}
			if (count($advert_arr))
			{
				if ($position=='left' and $_REQUEST['req']=='') // Home left panel
				{
					?>
					<div class="lf_ad_new">
					<?php	
					foreach ($advert_arr as $d=>$k)
					{
						switch($position)
						{
							case 'left':
								$cache_type		= 'comp_leftadvert';	
							break;
							case 'right':
								$cache_type		= 'comp_rightadvert';	
							break;
						}	
						$cache_exists 	= false;
						$cache_required	= false;
						if ($Settings_arr['enable_caching_in_site']==1) // dont pick from cache in case if clicked on any of the shops
						{
							$cache_required = true;
							if (exists_Cache($cache_type,$k['advert_id']))
							{
								$content_cache = getcontent_Cache($cache_type,$k['advert_id']);
								if ($content_cache) // if cache exists show it
								{
									echo $content_cache;
									$cache_exists = true;
								}
							}
						}
						// Do the following only if caching is not enabled or cache does not exists
						if ($cache_exists==false)
						{
							if($cache_required)// if caching is required start recording the output
							{
								ob_start();
							}
						switch ($k['advert_type'])
						{
							case 'IMG':
								$path = url_root_image('nor/img/'.$k['advert_source'],1);
								$link = $k['advert_link'];
								if ($link!='')
								{
						?>
									<a href="<?php echo $link?>" title="<?php echo stripslashes($k['advert_title'])?>" target="<?=$k['advert_target']?>">	
						<?php
								}
						?>
									<img src="<?php echo $path?>" alt="<?php echo stripslashes($k['advert_title'])?>" title="<?php echo $title?>" border="0" />
						<?php		
								if ($link!='')
								{
						?>
									</a>
						<?php		
								}
							break;
							case 'PATH':
								$path = $k['advert_source'];
								$link = $k['advert_link'];
								if ($link!='')
								{
						?>
									<a href="<?php echo $link?>" title="<?php echo stripslashes($k['advert_title'])?>" target="<?=$k['advert_target']?>">	
						<?php
								}
						?>
									<img src="<?php echo $path?>" alt="<?php echo stripslashes($k['advert_title'])?>" title="<?php echo $title?>" border="0" />
						<?php		
								if ($link!='')
								{
						?>
									</a>
						<?php		
								}
							break;
							case 'TXT':
								$path = $k['advert_source'];
							?>
								<div class="lf_ad_new_top"></div>
								<div class="lf_ad_new_middle">
							<?php	
								echo stripslashes($path);
							?>
								</div>
								<div class="lf_ad_new_bottom"></div>
							<?php	
							break;
							
							case 'SWF'://for  flash file
							$path = url_root_image('nor/img/'.$k['advert_source'],1);
							$link = $k['advert_link'];
							$flash_path =  '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="'.$ecom_selfhttp.'download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0" width="200" height="95">
							<param name="movie" value='.$path.'  >
    						<param name="quality" value="high" >
							<param name="BGCOLOR" value="#D6D8CB"><embed src='.$path.' type=application/x-shockwave-flash width = 200 height=95> </object>';
							$img_link=  '';
							echo  $flash_path ;
							
						break;
						case 'ROTATE':   // case if ad rotate images are set
							?>
								<div class="advert_rotate_left">
								<?php
								$advert_ul_id = uniqid('advert_');
								// get the list of rotating images
								$sql_rotate = "SELECT rotate_image,rotate_link, rotate_alttext  
													FROM 
														advert_rotate 
													WHERE 
														adverts_advert_id = ".$k['advert_id']." 
													ORDER BY 
														rotate_order ASC";
								$ret_rotate = $db->query($sql_rotate);
								if($db->num_rows($ret_rotate))
								{
								   $HTML_Content .= '<script type="text/javascript">
													jQuery.noConflict();
													var $j = jQuery;
													$j(document).ready(
														function(){
														$j(\'ul#'.$advert_ul_id.'\').innerfade({ 
															speed: 1000,
															timeout: '.($k['advert_rotate_speed']*1000).',
															type: \'sequence\',
															runningclass: \'innerfade_left\',
															containerheight: \''.$k['advert_rotate_height'].'px\'
														});
													});
													</script>';
									$HTML_Content .= '<ul id="'.$advert_ul_id.'">';
									while ($row_rotate = $db->fetch_array($ret_rotate))
									{   
									    if($row_rotate['rotate_alttext']!='')
										{
										  $alt_text = $row_rotate['rotate_alttext']; 
										}
										else
										{
										   $alt_text = $k['advert_title']; 
										}
										$link = trim($row_rotate['rotate_link']);
										$link_start = $link_end = '';
										if($link!='')
										{
											$link_start     = '<a href="'.$link.'" title="'.$title.'">';
											$link_end       = '</a>';
										}
										$HTML_Content .= '
															<li>'.$link_start.'
															<img src="'.url_root_image('rot/img/'.$row_rotate['rotate_image'],1).'" alt="'.stripslashes($alt_text).'" title="'.stripslashes($alt_text).'"/>'.
															$link_end.'</li>';
									}
									$HTML_Content .='</ul>';
									echo $HTML_Content;
									}
								?>
								</div>
							<?php	
							break;
						};
						?>
						</div>
						<?php
							if($cache_required)// If caching was required then stop the recording and save the cache and display the cached content
							{
								$content = ob_get_contents();
								ob_end_clean();
								save_Cache($cache_type,$k['advert_id'],$content);
								echo $content;
							}	
						}
					}	
				}
				elseif ($position=='right' or ($position=='left' and $_REQUEST['req']!='')) // right side of inner pages
				{
					?>
					<div class="rt_ad_new">
					<?php	
						foreach ($advert_arr as $d=>$k)
						{
							switch($position)
							{
								case 'left':
									$cache_type		= 'comp_leftadvert';	
								break;
								case 'right':
									$cache_type		= 'comp_rightadvert';	
								break;
							}	;
							$cache_exists 	= false;
							$cache_required	= false;
							if ($Settings_arr['enable_caching_in_site']==1) // dont pick from cache in case if clicked on any of the shops
							{
								$cache_required = true;
								if (exists_Cache($cache_type,$k['advert_id']))
								{
									$content_cache = getcontent_Cache($cache_type,$k['advert_id']);
									if ($content_cache) // if cache exists show it
									{
										echo $content_cache;
										$cache_exists = true;
									}
								}
							}
							// Do the following only if caching is not enabled or cache does not exists
							if ($cache_exists==false)
							{
								if($cache_required)// if caching is required start recording the output
								{
									ob_start();
								}
								switch ($k['advert_type'])
								{
									case 'IMG':
										$path = url_root_image('nor/img/'.$k['advert_source'],1);
										$link = $k['advert_link'];
										if ($link!='')
										{
								?>
											<a href="<?php echo $link?>" title="<?php echo stripslashes($k['advert_title'])?>" target="<?=$k['advert_target']?>">	
								<?php
										}
								?>
											<img src="<?php echo $path?>" alt="<?php echo stripslashes($k['advert_title'])?>" title="<?php echo stripslashes($k['advert_title'])?>" border="0" />
								<?php		
										if ($link!='')
										{
								?>
											</a>
								<?php		
										}
									break;
									case 'PATH':
										$path = $k['advert_source'];
										$link = $k['advert_link'];
										if ($link!='')
										{
								?>
											<a href="<?php echo $link?>" title="<?php echo stripslashes($k['advert_title'])?>" target="<?=$k['advert_target']?>">	
								<?php
										}
								?>
											<img src="<?php echo $path?>" alt="<?php echo stripslashes($k['advert_title'])?>" title="<?php echo stripslashes($k['advert_title'])?>" border="0" />
								<?php		
										if ($link!='')
										{
								?>
											</a>
								<?php		
										}
									break;
									case 'TXT':
										$path = $k['advert_source'];
										?>
											<div class="rt_ad_new_top"></div>
											<div class="rt_ad_new_middle">
										<?php	
											echo stripslashes($path);
										?>
											</div>
											<div class="rt_ad_new_bottom"></div>
										<?php	
									break;
									
									case 'SWF'://for  flash file
									$path = url_root_image('nor/img/'.$k['advert_source'],1);
									$link = $k['advert_link'];
									$flash_path =  '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="'.$ecom_selfhttp.'download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0" width="200" height="95">
									<param name="movie" value='.$path.'  >
									<param name="quality" value="high" >
									<param name="BGCOLOR" value="#D6D8CB"><embed src='.$path.' type=application/x-shockwave-flash width = 200 height=95> </object>';
									$img_link=  '';
									echo  $flash_path ;
									
									break;
									case 'ROTATE':   // case if ad rotate images are set
							?>
										<div class="advert_rotate_right">
										<?php
										$advert_ul_id = uniqid('advert_');
										// get the list of rotating images
										$sql_rotate = "SELECT rotate_image,rotate_link 
															FROM 
																advert_rotate 
															WHERE 
																adverts_advert_id = ".$k['advert_id']." 
															ORDER BY 
																rotate_order ASC";
										$ret_rotate = $db->query($sql_rotate);
										if($db->num_rows($ret_rotate))
										{
										   $HTML_Content .= '<script type="text/javascript">
															jQuery.noConflict();
															var $j = jQuery;
															$j(document).ready(
																function(){
																$j(\'ul#'.$advert_ul_id.'\').innerfade({ 
																	speed: 1000,
																	timeout: '.($k['advert_rotate_speed']*1000).',
																	type: \'sequence\',
																	runningclass: \'innerfade_right\',
																	containerheight: \''.$k['advert_rotate_height'].'px\'
																});
															});
															</script>';
											$HTML_Content .= '<ul id="'.$advert_ul_id.'">';
											while ($row_rotate = $db->fetch_array($ret_rotate))
											{
												 if($row_rotate['rotate_alttext']!='')
												{
												  $alt_text = $row_rotate['rotate_alttext']; 
												}
												else
												{
												   $alt_text = $k['advert_title']; 
												}
												$link = trim($row_rotate['rotate_link']);
												$link_start = $link_end = '';
												if($link!='')
												{
													$link_start     = '<a href="'.$link.'" title="'.stripslashes($k['advert_title']).'">';
													$link_end       = '</a>';
												}
												$HTML_Content .= '
																	<li>'.$link_start.'
																	<img src="'.url_root_image('rot/img/'.$row_rotate['rotate_image'],1).'" alt="'.stripslashes($alt_text).'" title="'.stripslashes($alt_text).'" />'.
																	$link_end.'</li>';
											}
											$HTML_Content .='</ul>';
											echo $HTML_Content;
											}
										?>
										</div>
									<?php	
									break;
								};
							?>
							</div>
							<?php
								if($cache_required)// If caching was required then stop the recording and save the cache and display the cached content
								{
									$content = ob_get_contents();
									ob_end_clean();
									save_Cache($cache_type,$k['advert_id'],$content);
									echo $content;
								}	
							}
						}	
					}
					elseif ($position=='middlebottom' and $_REQUEST['req']=='') // right side of inner pages
					{
						foreach ($advert_arr as $d=>$k)
						{
							switch($position)
							{
								case 'left':
									$cache_type		= 'comp_leftadvert';	
								break;
								case 'right':
									$cache_type		= 'comp_rightadvert';	
								break;
							}	;
							$cache_exists 	= false;
							$cache_required	= false;
							if ($Settings_arr['enable_caching_in_site']==1) // dont pick from cache in case if clicked on any of the shops
							{
								$cache_required = true;
								if (exists_Cache($cache_type,$k['advert_id']))
								{
									$content_cache = getcontent_Cache($cache_type,$k['advert_id']);
									if ($content_cache) // if cache exists show it
									{
										echo $content_cache;
										$cache_exists = true;
									}
								}
							}
							// Do the following only if caching is not enabled or cache does not exists
							if ($cache_exists==false)
							{
								if($cache_required)// if caching is required start recording the output
								{
									ob_start();
								}
								switch ($k['advert_type'])
								{
									case 'IMG':
										$path = url_root_image('nor/img/'.$k['advert_source'],1);
										$link = $k['advert_link'];
										if ($link!='')
										{
								?>
											<a href="<?php echo $link?>" title="<?php echo stripslashes($k['advert_title'])?>" target="<?=$k['advert_target']?>">	
								<?php
										}
								?>
											<img src="<?php echo $path?>" alt="<?php echo stripslashes($k['advert_title'])?>" title="<?php echo stripslashes($k['advert_title'])?>" border="0" />
								<?php		
										if ($link!='')
										{
								?>
											</a>
								<?php		
										}
									break;									
									case 'TXT':
										$path = $k['advert_source'];
										?>
											<div class="contentwrap">

										<?php	
											echo stripslashes($path);
										?>
											</div>
										<?php	
									break;							
									
								};
							?>
							</div>
							<?php
								if($cache_required)// If caching was required then stop the recording and save the cache and display the cached content
								{
									$content = ob_get_contents();
									ob_end_clean();
									save_Cache($cache_type,$k['advert_id'],$content);
									echo $content;
								}	
							}
						}
					}		
				}
		}
		
		// ####################################################################################################
		// Function which holds the display logic for sitereviews
		// ####################################################################################################
		function mod_sitereviews($title)
		{
			global $ecom_siteid,$db,$position,$Settings_arr;
			if ($position=='left' and $_REQUEST['req']=='') // Home left panel
			{
		?>
				<div class="lf_rew">
				<div class="lf_rew_top"></div>
				<div class="lf_rew_middle"><input class="sitereviewleft" value="Site Reviews" onclick="window.location='<?php url_link('sitereview.html');?>'" type="button"></div>
				<div class="lf_rew_bottom"></div>
				</div>
		<?php	
			}
			elseif ($position=='right' or ($position=='left' and $_REQUEST['req']!='')) // right side of inner pages
			{
		?>	
			<div class="rt_rew">
			<div class="rt_rew_top"></div>
			<div class="rt_rew_middle"><input class="sitereviewright" value="Site Reviews" onclick="window.location='<?php url_link('sitereview.html');?>'" type="button"></div>
			<div class="rt_rew_bottom"></div>
			</div>
		<?php	
			}
		}
		
		// ####################################################################################################
		// Function which holds the display logic for Recently Viewed Products
		// ####################################################################################################
		function mod_productlist($ret_prod,$title)
		{
			global $Captions_arr,$ecom_siteid,$db,$Settings_arr,$position;
			if ($position=='left' and $_REQUEST['req']=='') // show only if position value is left and home page
			{
		?>
				<div class="lf_plist">
				<div class="lf_plist_top"></div>
				<div class="lf_plist_middle">
				<ul class="plist">
			<?php
				if($title)
				{
			?>
				<li class="plistheader"><?php echo stripslashes($title)?></li>
			<?php
				}
				while ($row_prod = $db->fetch_array($ret_prod))
				{
				?>
						<li><h2><a href="<?php echo url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" class="plistlink" title="<?php echo stripslashes($row_prod['product_name'])?>"><?php echo stripslashes($row_prod['product_name'])?></a></h2></li>
				<?php
				}
		?>
				</div>
				<div class="lf_plist_bottom"></div>
				</div>
		<?php		
			}
			elseif($position=='right' or ($position=='left' and $_REQUEST['req']!=''))// right or middle page other than home page
			{
			?>
				<div class="lf_plist_right">
				<div class="lf_plist_top_right"></div>
				<div class="lf_plist_middle_right">
				<ul class="plist_right">
			<?php
				if($title)
				{
			?>
				<li class="plistheader_right"><?php echo stripslashes($title)?></li>
			<?php
				}
				while ($row_prod = $db->fetch_array($ret_prod))
				{
				?>
						<li><h2><a href="<?php echo url_product($row_prod['product_id'],$row_prod['product_name'],-1)?>" class="plistlink_right" title="<?php echo stripslashes($row_prod['product_name'])?>"><?php echo stripslashes($row_prod['product_name'])?></a></h2></li>
				<?php
				}
		?>
				</div>
				<div class="lf_plist_bottom_right"></div>
				</div>
			<?php	
			}
		}
		function mod_statistics($title,$stat_query)
		{
			global $position,$ecom_siteid,$db,$ecom_hostname,$Settings_arr,$Captions_arr,$position;
			if($db->num_rows($stat_query))
				$row_query = $db->fetch_array($stat_query);
			
				if ($position=='left' and $_REQUEST['req']=='') // show only if position value is left and home page
				{
		?>
					<div class="lf_stcs">
					<div class="lf_stcs_top"></div>
					<div class="lf_stcs_middle">
					<?php
					if($title)
					{
					?>
						<div class="stcs_txtA"><?php echo $title?></div>
					<?php
					}
					?>
					 <div  class="stcs_txtB"><?=$row_query['site_hits']?> <?php echo $Captions_arr['COMMON']['WEB_STATISTICS']?></div>
					</div>
					<div class="lf_stcs_bottom"></div>
				  	</div>
	  <?php
	 		 }
			 elseif($position=='right' or ($position=='left' and $_REQUEST['req']!=''))// right or middle page other than home page
			 {
	  ?>
	  				<div class="rt_stcs">
					<div class="rt_stcs_top"></div>
					<div class="rt_stcs_middle">
					<?php
					if($title)
					{
					?>
					<div class="stcs_txtA" ><?php echo $title?></div>
					<?php
					}
					?>
					 <div  class="stcs_txtB"><?=$row_query['site_hits']?> <?php echo $Captions_arr['COMMON']['WEB_STATISTICS']?></div>
					</div>
					<div class="rt_stcs_bottom"></div>
				  </div>
	  <?php
	  		}
		}
		function mod_ssl($title)
		{
			global $position,$ecom_siteid,$db,$ecom_hostname,$Settings_arr,$Captions_arr,$image_path;
			if ($position=='left' and $_REQUEST['req']=='') // show only if position value is left and home page
			{
		?>
				<div class="lf_ssl">
				<div class="lf_ssl_top"></div>
				<div class="lf_ssl_middle">
                                <?php
                                if(file_exists("$image_path/site_images/ssl_main_image_left.gif"))
                                {
                                ?>
                                    <img src="<? url_site_image('ssl_main_image_left.gif')?>" alt="Secure Payment" title="<?php echo $title?>" border="0" />
                                <?
                                }
                                ?>		
				</div>
				<div class="lf_ssl_bottom"></div>
				</div>  
			<?php
				}
				 elseif($position=='right' or ($position=='left' and $_REQUEST['req']!=''))// right or middle page other than home page
				 {
			 ?>
					<div class="rt_ssl">
					<div class="rt_ssl_top"></div>
					<div class="rt_ssl_middle">
                                        <?php
                                        if(file_exists("$image_path/site_images/ssl_main_image_right.gif"))
                                        {
                                        ?>
                                            <img src="<? url_site_image('ssl_main_image_right.gif')?>" alt="Secure Payment" title="<?php echo $title?>" border="0" />
                                        <?
                                        }
                                        ?>
					</div>
					<div class="rt_ssl_bottom"></div>
					</div>
			 <?php
			 	}
				elseif($position=='bottom')
				{
					if(file_exists("$image_path/site_images/ssl_main_image_bottom.gif"))
                    {
				?>
					<div align="center" style="padding-bottom:5px">
					<img src="<? url_site_image('ssl_main_image_bottom.gif')?>" alt="Secure Payment" title="<?php echo $title?>" border="0" />
					</div>				
				<?php
					}
				}
		}
		// Function to show the top menu item
		function mod_topmenu($title)
		{ //LOGIN_TOPMENU;
			global $db,$ecom_siteid,$ecom_hostname,$inlineSiteComponents,$Captions_arr;
			 $Captions_arr['LOGIN_MENU'] 	= getCaptions('LOGIN_MENU');
			 $cust_id 					= get_session_var("ecom_login_customer");
		?>
			<tr>
				<td colspan="3" class="userloginmenuytop" >
				<ul class="userloginmenuytopul"> 
				<li><h1><a href="<?php url_link('logout.html')?>" class="userloginmenuytoplink"><?=$Captions_arr['LOGIN_MENU']['LOGOUT']?></a></h1></li>
				<li><h1><a href="<?php url_link('mypricepromise.html')?>" class="userloginmenuytoplink"><?=$Captions_arr['LOGIN_MENU']['MY_PRICE_PROMISE']?></a></h1></li>
				<?php
				// check whether payon account link is to be displayed for current user
				$sql_usr = "SELECT customer_payonaccount_status 
									FROM 
										customers 
									WHERE 
										customer_id = $cust_id 
										AND sites_site_id =$ecom_siteid 
										AND customer_payonaccount_status IN ('ACTIVE','INACTIVE')
									LIMIT 
										1";
				$ret_usr = $db->query($sql_usr);
				if ($db->num_rows($ret_usr))
				{
				?>
 					<li><h1><a href="<?php url_link('mypayonaccountpayment.html')?>" class="userloginmenuytoplink"><?=$Captions_arr['LOGIN_MENU']['MY_PAYONACCDETAILS']?></a></h1></li>
				<?php
				}
					$sql_download	= "SELECT ord_down_id 
													FROM 
														order_product_downloadable_products  
													WHERE
														sites_site_id = $ecom_siteid 
														AND customers_customer_id = $cust_id 
													LIMIT 
														1";
					$ret_download	= $db->query($sql_download);
					if ($db->num_rows($ret_download))
					{
				?>	
					<li><h1><a href="<?php url_link('mydownloads.html')?>" class="userloginmenuytoplink"><?=$Captions_arr['LOGIN_MENU']['MY_DOWNLOADS']?></a></h1></li>
				<?php
					}
					
				?>
				<li><h1><a href="<?php url_link('myorders.html')?>" class="userloginmenuytoplink"><?=$Captions_arr['LOGIN_MENU']['MY_ORDERS']?></a></h1></li>
				<? $myaddr_module = 'mod_myaddressbook';
					if(in_array($myaddr_module,$inlineSiteComponents))
					{
				?>
					<li><h1><a href="<?php url_link('myaddressbook.html')?>" class="userloginmenuytoplink"><?=$Captions_arr['LOGIN_MENU']['MY_ADDRESSBOOK']?></a></h1></li>
				<?php
					}
				?>
				<?php	
					$myfav_module = 'mod_myfavorites';
					if(in_array($myfav_module,$inlineSiteComponents))
					{
				?>
					<li><h1><a href="<?php url_link('myfavorites.html')?>" class="userloginmenuytoplink"><?=$Captions_arr['LOGIN_MENU']['MY_FAVOURITE']?></a></h1></li>
				<?php
					}
					?>
				<li><h1><a href="<?php url_link('wishlist.html')?>" class="userloginmenuytoplink"><?=$Captions_arr['LOGIN_MENU']['MY_WISHLIST']?></a></h1></li>
				<li><h1><a href="<?php url_link('myenquiries.html')?>" class="userloginmenuytoplink"><?=$Captions_arr['LOGIN_MENU']['MY_ENQUIRIES']?></a></h1></li>
				<li><h1><a href="<?php url_link('myprofile.html')?>" class="userloginmenuytoplink"><?=$Captions_arr['LOGIN_MENU']['MY_PROFILE']?></a></h1></li>
				 <?php /* FB login script */
					//if($cust_id == 190741 || $cust_id == 67300 || $cust_id ==189235)
					{
				?>
				<li><a href="<?php url_link('gdproptin.html')?>" class="userloginmenuytoplink">GDPR Opt-In Form</a></li>
				<?php
					}
				?>
				<li><h1><a href="<?php url_link('login_home.html')?>" class="userloginmenuytoplink"><?=$Captions_arr['LOGIN_MENU']['MY_HOME']?></a></h1></li>
				</ul>          
				</td>
			</tr>
		<?php
		}
		
		/* Function to show the currency selector */
		function mod_currencyselector($title)
		{
			global $db,$ecom_siteid,$sitesel_curr,$position;
		  	// get the list of currencies to be used with the site
			$curr_arr = get_currency_list();
			 $comp_uniqid = uniqid('');
	  	?>
	  		<form method="post" name="frm_maincurrency_<?=$comp_uniqid?>" enctype="multipart/form-data" class="frm_cls" action="">
			<?php
			if ($position=='left' and $_REQUEST['req']=='') // show only if position value is left and home page
			{
			?>
				<div class="lf_curr">
				<div class="lf_curr_top"></div>
				<div class="lf_curr_middle">
			<?php
			if($title)
			{
			?>
				<div class="if_curr_txtA" ><?php echo $title?></div>
				<?php
			}
		?>	
				<div  class="lf_curr_txtB"> <?php
							//showing the currency selection drop down
							echo generateselectbox('cbo_selcurrency',$curr_arr,$sitesel_curr,'','document.frm_maincurrency_'.$comp_uniqid.'.submit()',0,'currencyselectordropdown');
					?></div>
				</div>
				<div class="lf_curr_bottom"></div>
				</div>
			<?php
			}
			 elseif($position=='right' or ($position=='left' and $_REQUEST['req']!=''))// right or middle page other than home page
			{
			?>
				<div class="rt_curr">
				<div class="rt_curr_top"></div>
				<div class="rt_curr_middle">
		<?php
			if($title)
			{
			?>
	    		   <div class="rt_curr_txtA" ><?php echo $title?></div>
		<?php
			}
		?>
				 <div  class="rt_curr_txtB">
				 <?php
							//showing the currency selection drop down
							echo generateselectbox('cbo_selcurrency',$curr_arr,$sitesel_curr,'','document.frm_maincurrency_'.$comp_uniqid.'.submit()',0,'currencyselectordropdown');
					?>
				</div>
				</div>
				<div class="rt_curr_bottom"></div>
				</div>
		<?php
		}
		?>	
		</form>	
		<?php
		}
	
	
		/* Function to show the currency selector */
		function mod_header($header_arr)
		{
			global $db,$ecom_siteid,$sitesel_curr;
			$ret_array  = array();
			if (count($header_arr)) 
			{
				$random = array_rand($header_arr);
				$header_image = trim($header_arr[$random]['header_filename']);
				if($header_image=='')
					$header_image = url_site_image('main_header.jpg',1);
				else
					$header_image = url_root_image($header_image,1);
				$header_text = trim($header_arr[$random]['header_caption']);
				$ret_array['img']  = $header_image;
				$ret_array['text']  = $header_text;
				//$header_image = "<img src=\"$header_image\" alt=\"\" width=\"244\" height=\"45\" border=\"0\" />";
					return $ret_array;
			} else {
				$header_image = url_site_image('main_header.jpg',1);
				$ret_array['img']  = $header_image;
				$ret_array['text']  = '';
				//$header_image = "<img src=\"$header_image\" alt=\"\" width=\"244\" height=\"45\" border=\"0\" />";
					return $ret_array;
			}		
		}
		
		// ####################################################################################################
		// Function which holds the display logic for best sellers
		// ####################################################################################################
		function mod_preorder($ret_main,$title,$display_id)
		{
			global $ecom_siteid,$db,$ecom_hostname,$position,$Captions_arr,$Settings_arr;
			if ($position=='left' or $position=='right') // Best sellers is allowed in left or right panels
			{	
				if ($db->num_rows($ret_main))
				{
						if ($position=='left'  and $_REQUEST['req']=='') // display login for left hand side
						{
				?>
						<div class="lf_preodr">
						<div class="lf_preodr_top">
						<?php
						if($title) // check whether title exists
						{
							echo $title;
						}
						?>
						</div>
							<div class="lf_preodr_middle">
								<ul class="preorder">
								<?php
								while ($row_main = $db->fetch_array($ret_main))
								{
								?>	
									<li><h1><a href="<?php url_product($row_main['product_id'],$row_main['product_name'],-1)?>" class="preorderlink" title="<?php echo stripslashes($row_main['product_name'])?>"><?php echo stripslashes($row_main['product_name'])?></a></h1></li>
								
								<?php
								
								}		
								?>
								<li><h1 align="right"> <a href="<? url_link('preorder'.$display_id.'.html')?>" class="pre-odr-showall" title="<?php echo $Captions_arr['COMMON']['SHOW_ALL']?>" <?php /*onclick="document.bestseller_component_left<?=$display_id?>.submit();"*/?>><?php echo $Captions_arr['COMMON']['SHOW_ALL']?></a> </h1> </li>
								</ul>
							</div>
						<div class="lf_preodr_bottom"></div>
						</div>
							<?php /*<form name="bestseller_component_left<?=$display_id?>" action="<? url_link('bestsellers.html')?>" method="post"><input type="hidden" name="disp_id" value="<?php echo $display_id?>"/></form>*/?>
				<?php	
						}
						elseif($position == 'right' or ($position == 'left' and $_REQUEST['req']!='')) // display logic for right hand side
						{
				?>
							<div class="rt_preodr">
								<div class="rt_preodr_top">
								<?php
								if($title) // check whether title exists
								{
					?>
									<?php echo $title?>
					<?php
								}
								?>					
								</div>
								<div class="rt_preodr_middle">
								 <ul class="preorder">  
										<?php
													while ($row_main = $db->fetch_array($ret_main))
													{
										?>	
														<li><h1><a href="<?php url_product($row_main['product_id'],$row_main['product_name'],-1)?>" class="preorderlink" title="<?php echo stripslashes($row_main['product_name'])?>"><?php echo stripslashes($row_main['product_name'])?></a></h1></li>
										
										<?php
													}		
										?>
														<li><h1 align="right"><a href="<? url_link('preorder'.$display_id.'.html')?>" class="pre-odr-showall" title="<?php echo $Captions_arr['COMMON']['SHOW_ALL']?>" <?php /*onclick="document.bestseller_component_right<?=$display_id?>.submit();"*/?>><?php echo $Captions_arr['COMMON']['SHOW_ALL']?></a> </h1> </li>
												</ul>
								
								
								
								
								
								
								
								</div>
								<div class="rt_preodr_bottom"></div>
							  </div>
							<?php /*<form name="bestseller_component_right<?=$display_id?>" action="<? url_link('bestsellers.html')?>" method="post"> <input type="hidden" name="disp_id" value="<?php echo $display_id?>"/></form>*/?>
				<?php	
						}
				}	
			}	
		}
		  function mod_payonaccount($title)   // Function to show the payonaccount banner
             {
                 global $db,$ecom_siteid,$sitesel_curr;
            ?>
              <div class="payonaccountcon" align="left"><a href="<?php url_link('payonaccount.html')?>"><img src="<?php url_site_image('payonaccount.gif')?>" alt="PayonAccount" title="PayonAccount" border="0" /></a></div>
            <?
			}
	};
?>
