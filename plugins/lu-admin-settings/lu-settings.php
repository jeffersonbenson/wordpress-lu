<?php
/**
 * @package LU_Settings
 * @version 1.6
 */
/*
Plugin Name: LU Settings
Description: Contains all settings, styling and setup for admin and theme support.
Author: Bradley Moore
Version: 1
*/

require_once(plugin_dir_path( __FILE__ ).'/lu-settings-options.php');
require_once(plugin_dir_path( __FILE__ ).'/includes/lu-settings-debug.php');
// require_once(plugin_dir_path( __FILE__ ).'/lu-settings-user-permissions.php');
require_once(plugin_dir_path( __FILE__ ).'/lu-settings-menu-options.php');

// Remove elements from admin side navigation
	function lu_settings_menu_modify() { 
		add_menu_page('LU Admin Settings', 'LU Admin Settings', 'remove_users', __FILE__, 'admin_lu_options_page' );
		add_options_page(__FILE__, 'Settings', 'Settings', 'remove_users', __FILE__, 'admin_lu_options_page' );
		add_submenu_page(__FILE__, 'Settings', 'Settings', 'remove_users', __FILE__, 'admin_lu_options_page' );
		add_submenu_page(__FILE__, 'Permissions', 'Permissions', 'remove_users', __FILE__.'/lu-admin-menu-settings', 'admin_menu_settings_page' );
	}
	add_action('admin_menu', 'lu_settings_menu_modify');


// setting globals
function set_lu_globals(){
	global $myluusername;
	global $mylupassword;
	global $myluenv;
	global $luuser;
	$user_id = get_current_user_id();
	// mylu api access
	$myluusername = 'global_header_ws_user';
	$mylupassword = 'GhUp4M@tW$!!!!';

	// environment vars
	$env = 'www';
	$myluenv = 'mylu';
	if(is_numeric(strpos($_SERVER['HTTP_HOST'], 'dev')) || strpos($_SERVER['HTTP_HOST'], '.university.liberty.edu') || is_numeric(strpos($_SERVER['HTTP_HOST'], 'localhost'))){
		$env = 'dev';
		$myluenv = 'myludev';
	} elseif (is_numeric(strpos($_SERVER['HTTP_HOST'], 'test'))){
		$env = 'test';
		$myluenv = 'mylutest';
	}

	// user information
	if(is_user_logged_in()){
		$luuser = maybe_unserialize(get_user_meta($user_id, 'lu_data', true));
		if($luuser && $luuser['data']){
			$expires = $luuser['expires'];
			$luuser = $luuser['data'];
		}
		if(!$luuser || $luuser=='' || date('Y-m-d H:i:s')>=$expires){
		}
		refresh_lu_user_data();
	}

}
add_action('init', 'set_lu_globals');


/*
	function used in set_lu_globals
	used to store mylu user information 
	into the current user for local use
*/
function refresh_lu_user_data(){
	global $luuser;
	global $myluenv;
	global $myluusername;
	global $mylupassword;
	$user_id = get_current_user_id();
	$context = stream_context_create(array(
	    'http' => array(
	        'header'  => "Authorization: Basic " . base64_encode("$myluusername:$mylupassword")
	    )
	));
	$user = wp_get_current_user();
	$username = $user->user_login;
	if(!$user->user_email){
		update_user_meta($user_id, 'user_email', $username.'@liberty.edu');
	}
	$luuser_data = array();
	try {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch,CURLOPT_URL,'https://'.$myluenv.'.liberty.edu/myluAppTool/user/rest/entities/'.$username.'/null/null/null/ALL');
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($ch, CURLOPT_USERPWD, "$myluusername:$mylupassword");
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		$data = curl_exec($ch);
		if($data){
			$luuser_data['data'] = json_decode($data);
			$luuser_data['expires'] = date("Y-m-d H:i:s", strtotime('+2 hours'));
			$luuser = $luuser_data['data'];
		}
	} catch (Exception $e) {
	}
	if($luuser_data['data']){
		update_user_meta($user_id, 'lu_data', maybe_serialize($luuser_data));
	} else {
		update_user_meta($user_id, 'lu_data', false);
	}
}
	

/*
	function restricts access to the site if checked in the settings
	it also restricts based on roles if settings are set
*/
function lu_site_access(){
	global $post;
	global $myluenv;
	global $luuser;

	$restrict_to_roles = maybe_unserialize(get_option('restrict_to_roles'));
	$redirect_user_to = get_option('redirect_user_to');

	// lu_debug('site_access', $restrict_to_roles);
	// lu_debug('site_access', $luuser);
	// lu_debug('site_access', '(get_option(\'lu_force_login\')) &raquo; '.(get_option('lu_force_login')));
	// lu_debug('site_access', '(!is_admin()) &raquo; '.(!is_admin()));
	// lu_debug('site_access', '($GLOBALS[\'pagenow\'] != \'wp-login.php\') &raquo; '.($GLOBALS['pagenow'] != 'wp-login.php'));
	// lu_debug('site_access', '(!is_user_logged_in()) &raquo; '.(!is_user_logged_in()));
	// lu_debug('site_access', '(!$luuser->QROLES) &raquo; '.(!$luuser->QROLES));
	// lu_debug('site_access', '($luuser->QROLES) &raquo; '.($luuser->QROLES));
	// lu_debug('site_access', '(count(array_intersect($restrict_to_roles, $luuser->QROLES))) &raquo; '.(count(array_intersect($restrict_to_roles, $luuser->QROLES))));
	// lu_debug('site_access', '($post->ID != $redirect_user_to) &raquo; '.($post->ID != $redirect_user_to));
	// lu_debug('site_access', 'main if redirect &raquo; '.($redirect_user_to && (!$luuser->QROLES || ($luuser->QROLES && count(array_intersect($restrict_to_roles, $luuser->QROLES))<1)) &&$post->ID != $redirect_user_to));
	
	if(get_option('lu_force_login')){
		if(!is_admin() && $GLOBALS['pagenow'] != 'wp-login.php'){
			if ( !is_user_logged_in() ) {
			   	auth_redirect();
			}
			if(
				$redirect_user_to && 
				(
					!$luuser->QROLES || 
					($luuser->QROLES && 
						is_array($restrict_to_roles) &&
						count($restrict_to_roles) > 0 &&
						count(array_intersect($restrict_to_roles, $luuser->QROLES))<1)
				) &&
				$post->ID != $redirect_user_to){
				wp_safe_redirect(get_permalink($redirect_user_to));
				die();
			}
		}
	}

}
add_action('template_redirect', 'lu_site_access');


// replace admin url
/*add_filter('site_url',  'wpadmin_filter', 10, 3);  
add_filter('network_admin_url',  'wpadmin_filter', 10, 3);  
function wpadmin_filter( $url, $path ) {  
    $old  = array( "/(wp-admin)/");  
    $admin_dir = WP_ADMIN_DIR;  
    $new  = array($admin_dir);  
    return preg_replace( $old, $new, $url, 1);  
}
if(strstr($_SERVER[REQUEST_URI], 'wp-admin')){
	header('Location: '.get_bloginfo('url').'/404');
	die();
}*/

/* GOOGLE TAG MANAGER ADDITION */
add_action('wp_head', 'lu_add_google_tag');
function lu_add_google_tag(){
	global $post;
	$google_tags = maybe_unserialize(get_option('lu_google_tags'));
	$current_post_type = get_post_type($post->ID);
	if($google_tags[$current_post_type]){
		echo stripslashes($google_tags[$current_post_type]);
	} elseif ($google_tags['Default']){
		echo stripslashes($google_tags['Default']);
	}
}


/********************* WP ADMIN CUSTOMIZATION *************************/
// remove unwanted code from the header
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'feed_links', 2);
	remove_action('wp_head', 'index_rel_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'feed_links_extra', 3);
	remove_action('wp_head', 'start_post_rel_link', 10, 0);
	remove_action('wp_head', 'parent_post_rel_link', 10, 0);
	remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	remove_action('template_redirect', 'rest_output_link_header', 11, 0);
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action('rest_api_init', 'wp_oembed_register_route');
	remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);

// add backend-style.css to admin
	add_action( 'admin_enqueue_scripts', 'load_admin_styles' );
	function load_admin_styles() {
		wp_enqueue_style( 'admin_styles', plugins_url( 'backend-style.css', __FILE__ ), false, '1.0.0' );
	}  


// set favicon 
	function LU_favicon() {
	  echo '<link rel="shortcut icon" type="image/x-icon" href="https://www.liberty.edu/favicon.ico" />';
	}
	add_action('wp_head', 'LU_favicon');
	add_action('admin_head', 'LU_favicon');


// custom page title
	add_filter('admin_title', 'my_admin_title', 10, 2);
	function my_admin_title($admin_title, $title) {
	    return get_bloginfo('name').' &raquo; '.$title;
	}


// change admin footer text to say Webmanager 7.0
	function change_footer() {
	    // add_filter( 'admin_footer_text', 'luadmin_edit_text', 11 );
	}
	function luadmin_edit_text($content) {
	    return "Webmanager 7.0";
	}
	add_action( 'admin_init', 'change_footer' );


// change admin bar user greeting
	add_action( 'admin_bar_menu', 'custom_greeting', 11 );
	function custom_greeting( $wp_admin_bar ) {
		$user_id = get_current_user_id();
		$current_user = wp_get_current_user();
		$profile_url = get_edit_profile_url( $user_id );
		if ( 0 != $user_id ) {
			/* Add the "My Account" menu */
			$avatar = get_avatar( $user_id, 28 );
			$greeting = 'Good Morning';
			if(date('H', time())>=12 && date('H', time()) < 17){
				$greeting = 'Good Afternoon';
			} else if (date('H', time()) >= 17){
				$greeting = 'Good Evening';
			}
			$howdy = sprintf( __($greeting.', %1$s'), $current_user->display_name );
			$class = empty( $avatar ) ? '' : 'with-avatar';

			$wp_admin_bar->add_menu( array(
				'id' => 'my-account',
				'parent' => 'top-secondary',
				'title' => $howdy . $avatar,
				'href' => $profile_url,
				'meta' => array(
					'class' => $class,
				),
			) );
		}
	}


// REMOVE DASHBOARD WIDGETS
	// remove the welcome panel from admin dashboard
	remove_action( 'welcome_panel', 'wp_welcome_panel' );
	function remove_dashboard_meta() {
	        remove_meta_box( 'wpseo-dashboard-overview', 'dashboard', 'normal' );
	        remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
	        remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
	        remove_meta_box( 'dashboard_primary', 'dashboard', 'normal' );
	        remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
	        remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
	        remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	        remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
	        remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	        remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
	        remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
	}
	add_action( 'admin_init', 'remove_dashboard_meta' );


// category id in body and post class
	function category_id_class($classes) {
		global $post;
		foreach((get_the_category($post->ID)) as $category)
			$classes [] = 'cat-' . $category->cat_ID . '-id';
			return $classes;
	}
	add_filter('post_class', 'category_id_class');
	add_filter('body_class', 'category_id_class');
	// add_filter( 'wpseo_metabox_prio', function() { return 'low';});


	/* Function to sort multidimensional arrays 
	Used on: Events Page for sorting post meta date fields */
	function subval_sort($a,$subkey) {
		foreach($a as $k=>$v) {
			$b[$k] = strtolower($v[$subkey]);
		}
		asort($b);
		foreach($b as $key=>$val) {
			$c[] = $a[$key];
		}
		return $c;
	}

// FORCE TOGGLE PARENT CATEGORIES
	function toggle_child_categories() {
		echo '
			<script>
			jQuery(".selectit input").change(function(){
				var $chk = jQuery(this);
				var ischecked = $chk.is(":checked");
				$chk.parent().parent().siblings().children("label").children("input").each(function(){
				var b = this.checked;
				ischecked = ischecked || b;
				})
				checkParentNodes(ischecked, $chk);
			});
			function checkParentNodes(b, $obj)
			{
				$prt = findParentObj($obj);
				if ($prt.length != 0)
				{
				 $prt[0].checked = b;
				 checkParentNodes(b, $prt);
				}
			}
			function findParentObj($obj)
			{
				return $obj.parent().parent().parent().prev().children("input");
			}
			</script>
			';
	}
	add_action('admin_footer', 'toggle_child_categories');

// REMOVE ADMIN BAR
	// add_filter( 'show_admin_bar', '__return_false' );

/********************* END WP ADMIN CUSTOMIZATION *************************/


// REGISTER SIDEBARS
$text_domain = (isset($text_domain))?$text_domain:'';
	if ( function_exists('register_sidebar') )
	register_sidebar(array(
		'id'          => 'sidebar-widget',
		'name'        => __( 'Sidebar Widget', $text_domain ),
		'description' => __( 'This widget is located in the sidebar.', $text_domain ),
	));

// DISALLOW ALL FILE EDITING IN THE BACKEND OF THE SITE (THEME AND PLUGIN EDITORS)
	define('DISALLOW_FILE_EDIT', true);

// add featured image functionality
	add_theme_support( 'post-thumbnails' );


/******************* TINYMCE ADVANCED FUNCTIONS *******************/
// SHOW KITCHENSINK BY DEFAULT - TINYMCE
	function unhide_kitchensink( $args ) {
		$args['wordpress_adv_hidden'] = false;
		return $args;
	}
	add_filter( 'tiny_mce_before_init', 'unhide_kitchensink' );

// Customize tinymce styles and formats
	if( !function_exists('base_custom_mce_format') ){
		function base_custom_mce_format($init) {
			// Add block format elements you want to show in dropdown
			$init['theme_advanced_blockformats'] = 'p,h1,h2,h3,h4,h5,h6';
			// PARSING THE STYLE.CSS FILE
		    $css = file_get_contents(get_bloginfo('stylesheet_url'));
		    preg_match_all( '/(?ims)([a-z0-9\s\.\:#_\-@,]+)\{([^\}]*)\}/', $css, $arr);
		    $result = array();
		    foreach ($arr[0] as $i => $x){
		    	$elem = explode('{', $x);
				$elemitem = trim($elem[0]);
				$style = trim($elem[1]);
				if($elemitem=='#contentWrapper'){
					//$x='#content body {'.$style;
				}
				if(strstr($x, '#content ')||(!strstr($elem[0],'.')&&!strstr($elem[0],'#'))){
					if(strstr($x, '#content ')){
						$styles = explode('#content ', $x);
						$allStyles .= end($styles)."\n";
					} else {
						$allStyles .= $x."\n";
					}
				}
		        $selector = trim($arr[1][$i]);
		        $rules = explode(';', trim($arr[2][$i]));
		        $rules_arr = array();
		        foreach ($rules as $strRule){
		            if (!empty($strRule)){
		                $rule = explode(":", $strRule);
		                $rules_arr[trim($rule[0])] = trim($rule[1]);
		            }
		        }
		        $selectors = explode(',', trim($selector));
		        foreach ($selectors as $strSel){
		            $result[$strSel] = $rules_arr;
		        }
		    }
		    $allStyles .= 'body {padding:20px;}';
		    //print_r($result);
		    // AFTER PARSING, FIND ALL THE CLASSES AND THEIR STYLES
		    $allClassArray = array();
			foreach($result as $key=>$style) {
				if(strstr($key, '#content ')||(!strstr($key,'.')&&!strstr($key,'#'))){
					$key = str_replace('#content ', '', $key);
					foreach($style as $stylekey=>$styleVal){
						//$allStyles .= $key." {".$stylekey.":".$styleVal."}<br>";
						// STRIP OUT ALL UNNECESSARY ITEMS
						$classExplode = explode('.', $key);
						$spaceExplode = explode(' ', $classExplode[1]);
						$commaexplode = explode(',', $spaceExplode[0]);
						$slashexplode = explode('/', $spaceExplode[0]);
						$colonexplode = explode(':', $slashexplode[0]);
						if($colonexplode[0]){
							array_push($allClassArray, $colonexplode[0]);
						}
					}
				}
			}
			$directory = explode('.com', get_bloginfo('template_directory'));
			// ADDING ALL #CONTENT STYLES TO EDITOR-STYLE STYLESHEET
			// UNCOMMENT THE 3 LINES BELOW TO IMPORT ALL STYLES INSIDE YOUR CONTENT DIV 
			// INTO THE EDITOR-STYLE.CSS STYLESHEET

			// $editorcss = fopen('..'.end($directory).'/editor-style.css', 'w') or die("can't open file");
			// fwrite($editorcss, $allStyles);
			// fclose($editorcss);

			// ADD ALL CLASSES TO STYLES DROPDOWN LIST IN TINYMCE EDITOR
			// NOTE: THE CLASSES HAVE TO HAVE STYLES SET IN THE MAIN STYLE.CSS
			// FILE TO SHOW UP IN THIS LIST, OTHERWISE THERE'S NO REASON TO HAVE
			// THEM ON THE LIST, THERE'S NO STYLE... :)
			$allClassArray = array_unique($allClassArray);
			foreach($allClassArray as $class){
				$allClasses .= $class.',';
			}
			$init['theme_advanced_styles'] = $allClasses;
			return $init;
		}
		add_filter('tiny_mce_before_init', 'base_custom_mce_format' );
	}

// Adds editor-style.css to tinymce advanced
	add_action( 'admin_init', 'add_my_editor_style' );
	function add_my_editor_style() {
		add_editor_style();
	}

/******************* END TINYMCE ADVANCED FUNCTIONS *******************/


// Shows link description in navigation items 
add_filter('walker_nav_menu_start_el', 'description_in_nav_el', 10, 4);
function description_in_nav_el($item_output, $item, $depth, $args){
    $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
    $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
    $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
    $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
    $return = '<a'.$attributes.'>' . $item->title;
    if($item->post_content){
    	$return .= '<span class="nav-desc">'.$item->post_content.'</span>';
    }
    $return .= '</a>';
    return $return;
}


/* EASILY ADD CUSTOM POST TYPES WITH NEW COLUMNS */
 // USAGE ** don't uncomment here, use in your theme functions.php file
/*custom_register_cpt(array(
	'post_type'=>'news',
	'rewrite_slug'=>'news',
	'singular_name'=>'News',
	'plural_name'=>'News',
	'has_archive'=>true,
	'hierarchical'=>false,
	'capability_type'=>'post',
	'supports'=>array( 'title', 'revisions', 'editor' )
));*/
function custom_register_cpt($atts){
	$post_type='';
	$rewrite_slug='';
	$singular_name='';
	$plural_name='';
	$has_archive=true;
	$hierarchical=false;
	$capability_type='post';
	$columns = array();
	$show_in_rest = false;
	$supports=array('title', 'editor', 'thumbnail');
	extract($atts);
	register_post_type( $post_type, array(
	    'labels' => 
	    	array(
			    'name' => $plural_name,
			    'singular_name' => $plural_name,
			    'add_new' => 'Add New',
			    'add_new_item' => 'Add New '.$single_name,
			    'edit_item' => 'Edit '.$single_name,
			    'new_item' => 'New '.$single_name,
			    'all_items' => 'All '.$single_name,
			    'view_item' => 'View '.$single_name,
			    'search_items' => 'Search '.$plural_name,
			    'not_found' =>  'No '.$plural_name.' found',
			    'not_found_in_trash' => 'No '.$plural_name.' found in Trash', 
			    'parent_item_colon' => '',
			    'menu_name' => $plural_name
			),
	    'public' => true,
	    'publicly_queryable' => true,
	    'show_ui' => true, 
	    'show_in_menu' => true, 
	    'query_var' => true,
		'show_in_rest'=>$show_in_rest,
	    'rewrite' => array( 'slug' => $rewrite_slug ),
	    'capability_type' => $capability_type,
	    'has_archive' => $has_archive, 
	    'hierarchical' => $hierarchical,
	    'menu_position' => null,
	    'supports' => $supports
		)
	);
}


// Custom Meta Box Function ---------------------------- BEGIN --------------
// if we ever need to get rid of ACF, repeaters and wysiwygs would be difficult
 
// ------ USAGE -----
/*$meta_boxes = 
	array(
		array(
			'box_title'=>'Page Options',// Title
			'id'=>'department',
			'post_type'=>'page',// Post Type
			// 'callback'=>'unique_callback_name',
			'fields' => array(
				array(
					'label'=>'Department',
					'desc'=>'',
					'id'=>'department',
					'type'=>'select', // supports text, textarea, checkbox, and select (must have 'options' array for select)
					'options'=> array(
						array(
							'value'=>'', 
							'label'=>'No Department',
						),
						array(
							'value'=>'Creative Media', 
							'label'=>'Creative Media',
						),
						array(
							'value'=>'IT Development', 
							'label'=>'IT Development',
						),
						array(
							'value'=>'Human Resources', 
							'label'=>'Human Resources',
						)
					),
					'show_column'=>1 // for the post type page
				)
			),
			'position' => 'side', // side, normal, or advanced
			'priority' => 'core' // high, core, default, or low
		)
	);
create_custom_meta_box($meta_boxes);*/

function create_custom_meta_box($CFArray){
	foreach($CFArray as $meta_box_options){
		add_action('add_meta_boxes', function() use( &$meta_box_options){
		    add_meta_box(
				$meta_box_options['id'], // $id
				$meta_box_options['box_title'], // $title 
				function() use( &$meta_box_options){
					global $post;
					// Use nonce for verification
					echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
						
					// Begin the field table and loop
					echo '<table class="form-table">';
					foreach ($meta_box_options['fields'] as $field) {
						// get value of this field if it exists for this post
						$meta = get_post_meta($post->ID, $field['id'], true);
						// begin a table row with
						echo '<tr>
								<th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
								<td>';
								switch($field['type']) {
									// text
									case 'text':
										echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" />
											<br /><span class="description">'.$field['desc'].'</span>';
									break;
									// textarea
									case 'textarea':
										echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="4">'.$meta.'</textarea>
											<br /><span class="description">'.$field['desc'].'</span>';
									break;
									// checkbox
									case 'checkbox':
										echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/>
											<label for="'.$field['id'].'">'.$field['desc'].'</label>';
									break;
									// select
									case 'select':
										echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
										foreach ($field['options'] as $option) {
											echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';
										}
										echo '</select><br /><span class="description">'.$field['desc'].'</span>';
									break;
									// case items will go here
								} //end switch
						echo '</td></tr>';
					} // end foreach
					echo '</table>'; // end table
				},
				$meta_box_options['post_type'], // $page
				$meta_box_options['position'], // $context
				$meta_box_options['priority']); // $priority
		});
		//Save the Data
		add_action('save_post', function() use( &$meta_box_options){
			global $post_id;
			
			// verify nonce
			if (!wp_verify_nonce($_POST['custom_meta_box_nonce'], basename(__FILE__))) 
				return $post_id;
			// check autosave
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
				return $post_id;
			// check permissions
			if ('page' == $_POST['post_type']) {
				if (!current_user_can('edit_page', $post_id))
					return $post_id;
				} elseif (!current_user_can('edit_post', $post_id)) {
					return $post_id;
			}
			
			// loop through fields and save the data
			foreach ($meta_box_options['fields'] as $field) {
				$old = get_post_meta($post_id, $field['id'], true);
				$new = $_POST[$field['id']];
				if ($new && $new != $old) {
					update_post_meta($post_id, $field['id'], $new);
				} elseif ('' == $new && $old) {
					delete_post_meta($post_id, $field['id'], $old);
				}
			} // end foreach
		});  

		add_filter('manage_edit-'.$meta_box_options['post_type'].'_columns', 
			function() use( &$meta_box_options) {
				global $columns;
				$columns['cb'] = '<input type="checkbox" />';
				$columns['title'] = _x('Title', 'title');
				foreach ($meta_box_options['fields'] as $field) {
					if($field['show_column']){
						$columns[$field['id']] = __($field['label']);
					}
		 		}
				$columns['date'] = _x('Date Added', 'date_added');
				return $columns;
			}
		);
		add_action('manage_'.$meta_box_options['post_type'].'_posts_custom_column',
			function($column_name, $id) use( &$meta_box_options){
				global $wpdb;
				foreach ($meta_box_options['fields'] as $field) {
					if($column_name==$field['id']&&$field['show_column']){
						$custom_field = get_post_meta($id, $field['id'], true);
						if(isset($custom_field)){
							echo $custom_field;
						} else {
							echo 'N/A';
						}
					}
				}
			},
		10, 6);
		add_filter( 'manage_edit-'.$meta_box_options['post_type'].'_sortable_columns', 
			function() use( &$meta_box_options){
				$columns['title'] = 'title';
				foreach ($meta_box_options['fields'] as $field) {
					if($field['show_column']){
						$columns[$field['id']] = $field['id'];
					}
				}
				$columns['date'] = 'date_added';
				return $columns;
			}
		);
	}
}

// Custom Meta Box Function ---------------------------- END --------------


/* CUSTOM USER ROLE */
// remove_role('non_publisher');
/*function add_roles_on_init() {
	add_role(
		'non_publisher', __( 'Non Publisher' ),
		array(
			// 'activate_plugins' => true,
			// 'delete_others_pages' => true,
			// 'delete_others_posts' => true,
			// 'delete_pages' => true,
			// 'delete_posts' => true,
			// 'delete_private_pages' => true,
			// 'delete_private_posts' => true,
			// 'delete_published_pages' => true,
			// 'delete_published_posts' => true,
			// 'edit_dashboard' => true,
			'edit_others_pages' => true,
			'edit_others_posts' => true,
			'edit_pages' => true,
			'edit_posts' => true,
			// 'edit_private_pages' => true,
			// 'edit_private_posts' => true,
			'edit_published_pages' => true,
			'edit_published_posts' => true,
			// 'edit_theme_options' => true,
			// 'export' => true,
			// 'import' => true,
			// 'list_users' => true,
			// 'manage_categories' => true,
			// 'manage_links' => true,
			// 'manage_options' => true,
			// 'moderate_comments' => true,
			// 'promote_users' => true,
			// 'publish_pages' => true,
			// 'publish_posts' => true,
			// 'read_private_pages' => true,
			// 'read_private_posts' => true,
			'read' => true,
			// 'remove_users' => true,
			// 'switch_themes' => true,
			'upload_files' => true
		)
	);
}
add_action('init', 'add_roles_on_init');*/


/* 
	POST PUBLISHING
*/
/* 
	We can hook into the submission actions, we can also hook into save_post.
	In the future, we can set up our own approval process by adding a "pending" post type 
	that we throw anything that users without admin or publisher (or whatever) create/edit
	that can be approved by an admin or publisher. Possibly could use a custom "post_status"
	value that we pull in the admin for approvals?
*/

/*function edit_post_submitbox_minor_actions(){
	global $post;
	$user = new WP_User( get_current_user_id() );
	if (in_array('editor', $user->roles)) {
		// if editor, we force pending post status so they can never publish changes.
		$post->post_status = 'draft';
	}
	return $post;
}
add_action( 'post_submitbox_minor_actions', 'edit_post_submitbox_minor_actions' );

function postPending($post_ID)
 { 
	$user = new WP_User( get_current_user_id() );
	if (in_array('editor', $user->roles)) {
        //Unhook this function
        remove_action('post_updated', 'postPending', 10, 3);

        return wp_update_post(array('ID' => $post_ID, 'post_status' => 'draft'));

        // re-hook this function
        add_action( 'post_updated', 'postPending', 10, 3 );
     }
 }
add_action('post_updated', 'postPending', 10, 3);

function add_capability() {
    // gets the author role
    $role = get_role( 'editor' );
    // This only works, because it accesses the class instance.
    $role->add_cap( 'edit_pages' ); 
    $role->add_cap( 'edit_published_pages' ); 
    $role->add_cap( 'edit_others_pages' ); 
    $role->remove_cap( 'publish_pages' ); 
}
add_action( 'admin_init', 'add_capability');*/

// function force_pending_for_pages($post){
// 	if (!current_user_can('update_'.$postarr['post_type']) && ! in_array( $postarr['post_status'], array( 'draft', 'pending', 'auto-draft' ) ) ) {
// 		$postarr['post_status'] = 'pending';
// 	}
// 	return $postarr;
// }
// add_action('wp_update_post', 'force_pending_for_pages');

/*
	END POST PUBLISHING
*/



/*
lu_register_script checks for minified file specified, if it does not exist, it goes to the regular path specified and loads that in instead. Also, if there are concatenated files specified, it loads those in separately if the contactenated/minified file does not exist
*/
$pageInfoScript = false;
function lu_register_script($script, $min_path, $reg_path, $concat_files = false, $deps = array( 'jquery' ), $ver = 1, $in_footer = true){
	if(file_exists(get_template_directory().$min_path.$script) && !get_option('lu_load_unminified_js')){
		wp_register_script( 'site-'.$script,  get_bloginfo('template_directory', 'display') . $min_path . $script, $deps, $ver, $in_footer);
		wp_enqueue_script('site-'.$script);
		if(!$pageInfoScript){
			wp_localize_script('site-'.$script, 'pageinfo', array('ID'=>get_the_id()));
			$pageInfoScript = true;
		}
	} else {
		if($concat_files){
			foreach($concat_files as $file){
				wp_register_script( 'site-'.$file,  get_bloginfo('template_directory', 'display') . $reg_path . $file, $deps, $ver, $in_footer);
				wp_enqueue_script('site-'.$file);
				if(!$pageInfoScript){
					wp_localize_script('site-'.$file, 'pageinfo', array('ID'=>get_the_id()));
					$pageInfoScript = true;
				}
			}
		} else {
			wp_register_script( 'site-'.$script,  get_bloginfo('template_directory', 'display') . $reg_path . $script, $deps, $ver, $in_footer);
			wp_enqueue_script('site-'.$script);
			if(!$pageInfoScript){
				wp_localize_script('site-'.$script, 'pageinfo', array('ID'=>get_the_id()));
				$pageInfoScript = true;
			}
		}
	}
}
function lu_register_style($style, $min_path, $reg_path, $concat_files = false, $deps = false, $ver = false, $media = 'all'){
	if(file_exists(get_template_directory().$min_path.$style) && !get_option('lu_load_unminified_css')){
		wp_register_style( 'site-'.$style,  get_bloginfo('template_directory', 'display') . $min_path . $style, $deps, $ver, $media);
		wp_enqueue_style('site-'.$style);
	} else {
		if($concat_files){
			foreach($concat_files as $file){
				wp_register_style( 'site-'.$file,  get_bloginfo('template_directory', 'display') . $reg_path . $file, $depts, $ver, $media);
				wp_enqueue_style('site-'.$file);
			}
		} else {
			wp_register_style( 'site-'.$style,  get_bloginfo('template_directory', 'display') . $reg_path . $style, $depts, $ver, $media);
			wp_enqueue_style('site-'.$style);
		}
	}
}


/*
	IMAGE COMPRESSION OPTIONS
*/
add_filter('jpeg_quality', function($arg){return (get_option('lu_image_quality')?get_option('lu_image_quality'):100);});
add_action('wp_handle_upload', 'lu_upload_resize');
function lu_upload_resize($image_data){
	try {
		$image_editor = wp_get_image_editor($image_data['file']);
		if(count($image_editor->errors)==0){
			$image_editor->resize(get_option('lu_image_dimensions_width'), get_option('lu_image_dimensions_height'), false);
			$saved_image = $image_editor->save($image_data['file']);
		}
	} catch(Exception $e) {

	}
	return $image_data;
}

/*
	Set Image Sizes
*/

add_action( 'init', 'lu_register_image_sizes' );
function lu_register_image_sizes(){
	$imageSizes = maybe_unserialize(get_option('image_sizes'));
	if($imageSizes){
		foreach($imageSizes as $i=>$imageSize){
			add_image_size($imageSize['slug'], $imageSize['width'], $imageSize['height'], $imageSize['crop']);
		}
	}
}
function lu_register_image_size_names($sizes) {
	$imageSizes = maybe_unserialize(get_option('image_sizes'));
	if($imageSizes){
		foreach($imageSizes as $i=>$imageSize){
	    	$sizes[$imageSize['slug']] = __( stripslashes($imageSize['name']), 'LU' );
		}
	}
    return $sizes;
}
add_filter('image_size_names_choose', 'lu_register_image_size_names');


function svg_mime_type($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'svg_mime_type');

/* get rid of annoying jqmigrate console.log */
add_action( 'wp_default_scripts', function( $scripts ) {
    if ( ! empty( $scripts->registered['jquery'] ) ) {
        $scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
    }
} );


/*

Network Settings

*/

// attempting to add plugin restrictions to hide/show plugins on specific sites, unfinished
/*add_filter( 'network_edit_site_nav_links', 'site_plugins_tab');
function site_plugins_tab($links){
	$links['site-plugins'] = array( 'label' => __( 'Plugins' ),     'url' => '../../..'.wp_make_link_relative(plugins_url( 'network-site-plugins.php', __FILE__ )),     'cap' => 'manage_sites' );
	return $links;
}*/