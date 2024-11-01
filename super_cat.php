<?php
/*
Plugin Name: Super Category Plugin
Plugin URI: http://anthologyoi.com/wordpress/plugins/super-category-multi-blog-plugin-v-08.html
Description: A plugin to make "Super Categories" that allow you to use a single admin system across multiple blogs and websites using parked domains.
Author: Aaron Harun
Version: 0.8
Author URI: http://anthologyoi.com/
*/

/*
Installation:
ONE: Upload all files to your plugin directory.
TWO: Activate.
THREE: Bask in the glory of the most basic options.
FOUR: Read the instructions. 

*/
/*
Changes:
 Pages -- done
 posts -- done
 themes -- done
 categories -- done
 internal links -- done
 bloginfo (urls) -- done
 header info -- done
 external links -- done
 install -- done
 admin panel for theme select and header info -- done
 get permalinks to work -- done
 pages to work without parents.-- done
 remove bad category links in posts -- done
 remove all theme edits -- done
 rss feeds -- done
 Arrays rather than single options -- done
 Added functionality to allow other plugins to integrate themselves into Super Categories -- done

*/
/*
Would love to add
Support for subfolders as different sites.
Support for selectable super category names.



*/




/*
NOTES:
You must have the same folder across all sites. If your default install is http://example.com/wordpress/ you cannot have a site that goes to http://otherexample.com/blog/ it must end in /wordpresss

wp_list_categories doesn't work.
*/

/*
Plugins that seem to work without modification:
Any Plugin that works in wordpress 2.1 and only displays posts, categories, or links using the normal APIs.
Ultimate Tag Warrior
Widgets - the plugin itself works.
Inap -- of course
Landing sites
Admin Drop Down Menus
Askimet
runPHP
WordStats
DupPrevent
Google Analyticator

Plugins that work with modification.
Dagon Design Sitemap Generator:
	Add:
		 if (function_exists('sprcat_filter_categories')){
			$c_items = sprcat_filter_categories($c_items);
		}
	Before: $cat_count = 0;


*/

// The actual url we need
$url = str_replace(array('www.','./'),'',$_SERVER['HTTP_HOST']);

//the category name
$clean_url = str_replace('.','',$url);
$cur_sprcat = $wpdb->get_var("SELECT cat_ID FROM $wpdb->categories WHERE category_parent = 0 && cat_name = '$clean_url'");
if($cur_sprcat){
	if($permalinks = get_option('permalink_structure')){
		$cur_page = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE post_status = 'publish' && post_type='page' && post_parent = 0 && post_title = '$clean_url'");
		$good_pages = $wpdb->get_results("SELECT `id` FROM $wpdb->posts WHERE post_status = 'publish' && post_type='page' && (post_parent = '$cur_page->ID' OR (post_parent = 0 && post_title != '$clean_url'))", ARRAY_A);
	}
	$good_cats = explode(',',get_category_children($cur_sprcat,","));
	if(empty($good_cats[0])){unset($good_cats[0]);}
// sets our options
$sprcatall = get_option("sprcat");
$sprcat = $sprcatall[$clean_url];

}

/*
**********************
Filters
***********************/


function sprcat_filter_categories ($results){
global $cur_sprcat, $good_cats;
	$filtered_results = array();
	foreach($results as $result){
		if((in_array($result->cat_ID,$good_cats,true) && $result->cat_ID !='')  || (in_array($result->ID,$good_cats,true) && $result->ID !='' )){
			if($cur_sprcat == $result->category_parent){
				$result->category_parent = 0;
			}
			array_push($filtered_results, $result);
		}
	}
	return $filtered_results;
}

function sprcat_filter_pages($results){
global $cur_sprcat, $good_pages;
	$filtered_results = array();
	foreach ($results as $result){
		foreach ($good_pages as $good_page){
			if($result->ID == $good_page['id']){
				$result->post_parent = 0;
						array_push($filtered_results, $result);
				}
			}
	}
return $filtered_results;
}


function sprcat_posts_where($where) {
global $cur_sprcat;
	$cat_includes = "(post_type = 'page' AND post_status = 'publish') ".get_category_children($cur_sprcat," OR `category_id` = ");
	$where .= " AND ($cat_includes)";
return $where;
}

function sprcat_posts_join($where) {
global $cur_sprcat,$wpdb, $wp_query;
        if (strpos($where," LEFT JOIN $wpdb->post2cat ON ($wpdb->posts.ID = $wpdb->post2cat.post_id) ") === false ) {
         $where .= " JOIN $wpdb->post2cat ON ($wpdb->posts.ID = $wpdb->post2cat.post_id) ";
        }

return $where;
}

function sprcat_prev_next_post_where($where) {
global $cur_sprcat;
	$cat_includes = get_category_children($cur_sprcat," OR `category_id` = ");
	$where .= ' AND ('.trim($cat_includes,' OR').')';
return $where;
}


/*
**********************
Other Stuff, sets options
***********************/

function sprcat_set_title ($title){
global $sprcat;
 	if($sprcat['title']){
 		$title = $sprcat['title'];
 	}
return $title;
}

function sprcat_set_description ($description){
global $sprcat;
 	if($sprcat['description']){
 		$description = $sprcat['description'];
 	}

return $description;
}
function sprcat_set_themes($theme){
global $sprcat;
 	if($sprcat){
 		$theme = $sprcat['theme'];
 	}
return $theme;
}

function sprcat_set_stylesheet($style){
global $sprcat;
 	if($sprcat){
 		$style = $sprcat['theme'];
 	}
return $style;
}
/*
**********************
Fix's for all the links.
***********************/
//This rewrites all urls to use the url we want it to be rather than what the admin panel claims it to be.
function sprcat_fix_links ($link){
global $url;

	$link = preg_replace('/(http.*?\:\/\/w*\.*)(.*?)(\/.*)/','${1}'.$url.'${3}',$link);
return $link;
}
//reqrite home and site url incase there is no / in them
function sprcat_fix_link ($link){
global $url;
	$link2 = preg_replace('/(http.*?\:\/\/w*\.?)(.*?)$/','${1}'.$url,$link);
	// there must be so send it on
	if($link == $link2){
		$link2 = sprcat_fix_links($link);
	}
return $link2;
}

//This makes it so we can have pages without their parents
function sprcat_fix_page_uris ($uris){
global $clean_url;
	while (list($key, $value) = each($uris)) {
		$key = str_replace($clean_url.'/','',$key);
		$fixed_uris[$key] = $value;
	}
return $fixed_uris;

}

//This removes the parents that match the url from the link in html lists.
function sprcat_fix_links_remove_parent($links){
global $url,$clean_url;
	$links = preg_replace('/(http.*?\:\/\/w*\.*)('.$url.'[^:.]*?\/.*?)('.$clean_url.'\/)(.*?)"/','${1}$${2}${4}"',$links);
return $links;

}
//Same as above, but for signle urls
function sprcat_fix_links_remove_parent2($links){
global $url,$clean_url;
	$links = preg_replace('/(http.*?\:\/\/w*\.*)('.$url.'[^:.]*?\/.*?)('.$clean_url.'\/)(.*?)/','${1}${2}${4}',$links);

return $links;
}

// Unfortunately the only way to do remove a bad category with low ids is to do the permalink all over again.
// This is a little slower than I would have liked (.00025s on a new install).
// But  I wasn't given a better choice. Thanks to a lack of filters on get_the_category
function sprcat_fix_bad_categories($links){
global $post,$permalinks,$good_cats;
		if(strpos($permalinks, '%category%') == true){
			foreach($good_cats as $good_cat){
				if(strpos($links, get_cat_name($good_cat['id'])) === true){
					//If $x goes up we don't have to fix it.
					$x++;
				}
			}	
				
				if($x == 0){
		
					//Just incase a category name is in the title. Would hate to have Dog taken out.
					$links = str_replace($post->post_name,'-:',$links);
					$cats = get_the_category($post->ID);
			
				foreach($cats as $cat){
	
					// We want to go through this as fast as possible and only get the first results
	
					if($bad_category == '' ){
	
						if(strpos($links, $cat->category_nicename) == true){
						//Argh bad cat found
							if ( $parent=$cat->category_parent ){
							$bad_category = get_category_parents($parent, FALSE, '/', TRUE);
							}
							$bad_category .= $cat->category_nicename;
						}
					}
	
					if($good_category == '' ){
	
						if(in_array($cat->cat_ID,$good_cats,true)){
						//first good cat found
							if ( $parent=$cat->category_parent ){
							$good_category = get_category_parents($parent, FALSE, '/', TRUE);
							}
							$good_category .= $cat->category_nicename;
						}
					}
				if($good_category && $bad_category){
					$links = str_replace($bad_category,$good_category,$links);
					$links = str_replace('-:',$post->post_name,$links);
				}
			
	
			}
	
	
		}
	
	}
return $links;
}

/*
**********************
THREE irritating and messing 
functions because 
the_categories doesn't
have a real filter....
Technically filters but
I'm leaving them here in the hopes
I can get rid of them later
***********************/
function sprcat_force_fix_categorys($cats){
global $good_cats;
	//we can't directly remove them, but we can sabatoge.
	if (!in_array($cats->cat_ID,$good_cats,true)){
		$cats->cat_name = 'ISHOULDHAVEPUTAHOOKHERE';	
	}
return $cats;
}
function sprcat_force_fix_categories($cats){
$cats = explode (',',$cats);
$filtered_cats = array();
	foreach($cats as $cat){
		//hmm smells like sabotage!!!
		if(strpos($cat,'ISHOULDHAVEPUTAHOOKHERE') == false){
		array_push($filtered_cats, $cat);
		}
	}
		return implode(',',$filtered_cats);
}

function sprcat_force_fix_rss_categories($cats){
$cats = explode ('<category>',$cats);
$filtered_cats = array();
	foreach($cats as $cat){
		//hmm smells like sabotage!!!
		if(strpos($cat,'ISHOULDHAVEPUTAHOOKHERE') == false){
		array_push($filtered_cats, $cat);
		}
	}
		return implode('<category>',$filtered_cats);
}

// *******************************
// The Filters themselves
// *******************************
//Obviously if there is no Super Category then no point in all this.
if($cur_sprcat){
	//We don't need it on the admin, so just incase we hide them
	if (!strpos($_SERVER['PHP_SELF'], 'wp-admin')){
		add_filter('posts_join', 'sprcat_posts_join');
		add_filter('posts_where', 'sprcat_posts_where');
		add_filter('get_next_post_join', 'sprcat_posts_join');
		add_filter('get_previous_post_join', 'sprcat_posts_join');
		add_filter('get_next_post_where', 'sprcat_prev_next_post_where');
		add_filter('get_previous_post_where', 'sprcat_prev_next_post_where');
		add_filter('get_categories', 'sprcat_filter_categories');
		add_filter('get_pages','sprcat_filter_pages');
		add_filter('get_category', 'sprcat_force_fix_categorys',9);
		add_filter('the_category', 'sprcat_force_fix_categories',9);
		add_filter('the_category_rss', 'sprcat_force_fix_rss_categories',9);
		add_filter('posts_groupby', 'sprcat_force_groupby');
		
		//We don't want to wring out things that don't need to be wrung;
		if(get_option('permalink_structure')){
			add_filter('option_page_uris', 'sprcat_fix_page_uris',1);
			add_filter('list_cats', 'sprcat_fix_links_remove_parent');
			add_filter('wp_list_pages', 'sprcat_fix_links_remove_parent');
			add_filter('wp_list_categories', 'sprcat_fix_links_remove_parent');
			add_filter('the_category', 'sprcat_fix_links_remove_parent');
			add_filter('category_link', 'sprcat_fix_links_remove_parent2');
			add_filter('post_link', 'sprcat_fix_links_remove_parent2');
			add_filter('post_link', 'sprcat_fix_bad_categories');
		}

	}


	//We need these everywhere,
	add_filter('option_home', 'sprcat_fix_link',1);
	add_filter('option_siteurl', 'sprcat_fix_link',1);
	add_filter('template', 'sprcat_set_themes', 1);
	add_filter('stylesheet', 'sprcat_set_stylesheet',1);
	add_filter('option_blogname', 'sprcat_set_title',1);
	add_filter('option_blogdescription', 'sprcat_set_description',1);

	
}

// *********************
// Special cases
// *********************
function sprcat_force_groupby($groupby){
	if ($groupby == ''){
	
		return ' `ID`';
	}else{
	return $groupby;
	}
}

//if the user has INAP also.
function sprcat_inap_integration($args = ''){
global $sprcat;
 $args = '';
if($sprcat['link_show_text']){
	$r['link_show_text'] = $sprcat['link_show_text'];
}
if($sprcat['link_hide_text']){
	$r['link_hide_text'] = $sprcat['link_hide_text'];
}
if($sprcat['split_mode']){
	$r['split_mode'] = $sprcat['split_mode'];
}
if($sprcat['show_html']){
	$r['show_html']= $sprcat['show_html'];
}
if($sprcat['default_behavior']){
	$r['default_behavior'] = $sprcat['default_behavior'];
}
if($sprcat['word_limit']){
	$r['word_limit'] = $sprcat['word_limit'];
}
if($sprcat['line_break']){
	$r['line_break'] =$sprcat['line_break'];
}
return $args;
}



// *********************
// All the admin stuff
// *********************


function sprcat_install() {
global $url, $clean_url, $wpdb;
	if(!$wpdb->query("SELECT `cat_name` FROM `$wpdb->categories`WHERE cat_name = '$clean_url'")){
	$success = $wpdb->query("INSERT INTO `$wpdb->categories` (`cat_name`,`category_nicename`) VALUES ('$clean_url','$clean_url')");
	if($success){
		$wpdb->query("UPDATE `$wpdb->categories` SET category_parent = LAST_INSERT_ID() WHERE category_parent = 0 && cat_ID <> 1 && cat_name <> '$clean_url' ");
		
		add_option('sprcat',array("$clean_url"=> array("theme"=>get_option('template'), "title"=>get_option('blogname'), "description"=>get_option('blogdescription'))));
		}
	}
}


//This next section is just a big huge mess. It works, but it isn't pretty.
//Actually it hasn't changed very much since version .1 that required hacks.
//Yah that is old.

function sprcat_admin_manage(){
	global $wpdb, $super_cat,$sprcatall;
  if ($_POST["action"] == "saveconfiguration") {

  	if($_REQUEST['sprcat_new']['cat'] != ''){
  	$check = $wpdb->get_row("SELECT cat_ID FROM $wpdb->categories WHERE cat_name = '$_REQUEST[sprcat_add]' ", ARRAY_A);
		if(!$check){
			if($_REQUEST['sprcat_new']['theme'] != '' && $_REQUEST['sprcat_new']['description'] != '' && $_REQUEST['sprcat_new']['title'] != '' ){
				$cat = str_replace('.','',$_REQUEST['sprcat_new']['cat']);
 			$wpdb->query("INSERT INTO `$wpdb->categories` (`cat_name`,`category_nicename`) VALUES ('$cat','$cat')");
				sprcat_update_options($cat, $_REQUEST['sprcat_new']);
				update_option('sprcat',$sprcatall);
			$message .= 'Added Super Category '. $cat.'<br/>';
			}else{
				$message .= 'You must fill in all of the fields to add a new super category. Please hit back on your browser and fill in any empty fields.<br/>';
			}
		}else{
			$message .= 'There is already a category with this name. Please hit back on your browser and enter a new name or edit the current record.<br/>';
		}

  	}

  	if(count($_REQUEST['edit_sprcat']) > 0){
  		foreach ($_REQUEST['edit_sprcat'] as $cat){
			sprcat_update_options($cat, $_REQUEST['sprcat'][$cat]);
			update_option('sprcat',$sprcatall);
  			$message .= 'Super Category  '.$cat.' updated.<br/>';
  		}
  	}




    echo '<div class="updated"><p><strong> Updated <br/> '.$message;
    echo '</strong></p></div>';
	}
	echo '<fieldset class="manage" style="width:100%; text-align:center;"><legend>' . __('Management', 'sprcat_manage') . '</legend>';
	echo '<form method="post">';
	echo '<table width="80%">';
	echo sprcat_get_options('add');
	echo '<tr><td colspan="2"><br/>Edit Super Categories:</td></tr>';
	while (list($spr_cat, $sprcat) = each($sprcatall)) {
		echo sprcat_get_options($mode,$spr_cat,$sprcat);
	}
	echo'
	</table>
			<input type="hidden" name="action" value="saveconfiguration">
			<input type="submit" value="Save">
		</form>
	</fieldset>';


}



// *********************
// All the Integration stuff
// *********************


function sprcat_get_options($mode = '',$spr_cat = '',$sprcat = ''){
$themes = sprcat_get_themes('list');
	if($mode == 'add'){
$all_options=<<<block
	<tr>
		<td>Add Super Category (url minus http:// and www. eg: anthologyoi.com):</td>
		<td><input type="text" value="" name="sprcat_new[cat]"></td>
	</tr>
	<tr>
		<td>New Super Cat Header (blog title):</td>
		<td><input type="text" value="" name="sprcat_new[title]"></td></tr>
	<tr>
		<td>New Super Cat Description (blog description):</td>
		<td><input type="text" value="" name="sprcat_new[description]"></td></tr>
	<tr>
		<td>New Super Cat theme (see theme page for previews):</td>
		<td><select  name="sprcat_new[theme]">$themes</select></td>
	</tr>
block;
	}else{
	$all_options=<<<block
	<tr>
		<td>Edit Super Category</td>
		<td>$spr_cat</td>
	</tr>
	<tr>
		<td>Edit Super Cat Header (blog title):</td>
		<td><input type="text" value="$sprcat[title]" name="sprcat[$spr_cat][title]"></td>
	</tr>
	<tr>
		<td>Edit Super Cat Description (blog description):</td>
		<td><input type="text" value="$sprcat[description]" name="sprcat[$spr_cat][description]"></td>
	</tr>
	<tr>
		<td>Edit Super Cat theme (see theme page for previews):</td>
		<td><select  name="sprcat[$spr_cat][theme]"><option value="$sprcat[theme]">$sprcat[theme]</option>$themes</select></td>
	</tr>
	<tr>
		<td>Check to update Super Category <b>$spr_cat</b><br/><br/><br/><br/></td>
		<td>Check to update: <input type="checkbox" name="edit_sprcat[]" value="$spr_cat"></td>
	</tr>
block;
	
	}

$all_options .= apply_filters('sprcat_get_options',$mode,$sprcat,$spr_cat);
return $all_options;
}


function sprcat_option_filters($option,$function = ''){
if($option != ''){

		if($function == ''){
		$function = 'sprcat_set_'.$option;
			if(!function_exists($function)){}
				eval('function '.$function.' ($option){
						global $sprcat;
 						if($sprcat['.$option.']){
 							$option = $sprcat['.$option.'];
 						}
						return $option;
					}');

		}
		eval("add_filter('option_$option', '$function',1);");
	}
}


function sprcat_update_options($supercat,$options){
global $sprcatall;

	while (list($option, $value) = each($options)) {
		$sprcatall[$supercat][$option] =  $value;
	}

return $sprcatall;
}

function sprcat_add_pages(){
add_management_page('Super Categories Managment', 'Super Categories', 10, __file__, 'sprcat_admin_manage');
}

// modified get_themes() so we can use a simple drop down.
function sprcat_get_themes($format = 'Array'){
	global $wp_themes;
	if ( isset($wp_themes) )
		return $wp_themes;

	$theme_root = get_theme_root();

	// Files in wp-content/themes directory
	$themes_dir = @ dir($theme_root);
	if ( $themes_dir ) {
		while(($theme_dir = $themes_dir->read()) !== false) {
			if ( is_dir($theme_root . '/' . $theme_dir) && is_readable($theme_root . '/' . $theme_dir) ) {
				if ( $theme_dir{0} == '.' || $theme_dir == '..' || $theme_dir == 'CVS' ) {
					continue;
				}
				$stylish_dir = @ dir($theme_root . '/' . $theme_dir);
				$found_stylesheet = false;
				while (($theme_file = $stylish_dir->read()) !== false) {
					if ( $theme_file == 'style.css' ) {
						$theme_files[] = $theme_dir;
						$found_stylesheet = true;
						break;
					}
				}
			}
		}
	}
	if($format == 'Array'){
	return $theme_files;
	}else{
		foreach($theme_files as $name){
		$themes .= '<option>'.$name.'</option>';
		}
	return $themes;
	}
}

add_action('admin_menu', 'sprcat_add_pages');


    if (isset($_GET['activate']) && $_GET['activate'] == 'true')
       add_action('init', 'sprcat_install');

?>
