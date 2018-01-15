<?php


// setting global menu items
global $menu_items;
$menu_items = array(
	'Dashboard' => array(
		'item'=>'index.php',
		'subitems'=> array(
	  		array('Home', 'index.php', 'index.php' ),
	  		array('Updates', 'index.php', 'my-sites.php' )
	  	)
	),
	'Posts' => array(
		'item'=>'edit.php',
		'subitems'=> array(
		  	array('All Posts', 'edit.php','edit.php'),
		  	array('Add New', 'edit.php','post-new.php'),
		  	array('Categories', 'edit.php','edit-tags.php?taxonomy=category'),
		  	array('Tags', 'edit.php','edit-tags.php?taxonomy=post_tag')
	  	)
	),
	'Media' => array(
		'item'=>'upload.php',
		'subitems'=> array(
			array('Library', 'upload.php', 'upload.php' ),
			array('Add New', 'upload.php', 'media-new.php' )
	  	)
	),
	'Pages' => array(
		'item'=>'edit.php?post_type=page',
		'subitems'=> array(
			array('All Pages', 'edit.php?post_type=page', 'edit.php?post_type=page' ),
			array('Add New', 'edit.php?post_type=page', 'post-new.php?post_type=page' )
	  	)
	),
	'Comments' => array(
		'item'=>'edit-comments.php',
		'subitems'=> array(
			
	  	)
	),
	'Appearance' => array(
		'item'=>'themes.php',
		'subitems'=> array(
			array('Themes', 'themes.php', 'themes.php' ),
			array('Customize', 'themes.php', 'customize.php' ),
			array('Widgets', 'themes.php', 'widgets.php' ),
			array('Menus', 'themes.php', 'nav-menus.php' )
	  	)
	),
	'Plugins' => array(
		'item'=>'plugins.php',
		'subitems'=> array(
			
	  	)
	),
	'Users' => array(
		'item'=>'users.php',
		'subitems'=> array(
			
	  	)
	),
	'Tools' => array(
		'item'=>'tools.php',
		'subitems'=> array(
			array('Available Tools', 'tools.php', 'tools.php'),
			array('Import', 'tools.php', 'import.php'),
			array('Export', 'tools.php', 'export.php')	
	  	)
	),
	'Settings' => array(
		'item'=>'options-general.php',
		'subitems'=> array(
			array('General', 'options-general.php', 'options-general.php'),
			array('Writing', 'options-general.php', 'options-writing.php'),
			array('Reading', 'options-general.php', 'options-reading.php'),
			array('Discussion', 'options-general.php', 'options-discussion.php'),
			array('Media', 'options-general.php', 'options-media.php'),
			array('Permalinks', 'options-general.php', 'options-permalink.php')	
	  	)
	)
);

function admin_menu_settings() {
    global $menu_items;

	// save and apply our settings to the menu
	foreach($menu_items as $item => $val){
		$sanitized_val = sanitize_title('main_'.$val['item']);
		register_setting( 'lu-settings-menu-options', $sanitized_val );

		if($_GET['page']=='lu-admin-settings/lu-settings-menu-options.php' && $_POST['submit']) {
			update_option($sanitized_val, $_POST[$sanitized_val]);
		}	
		if(get_option($sanitized_val)){
	  		// remove_menu_page(get_option($sanitized_val));
		} else {
			// handle submenu items
			foreach($val['subitems'] as $subitem){
				$sanitized_subval = sanitize_title('sub_'.$subitem[2]);
				register_setting( 'lu-settings-menu-options', $sanitized_val );

				if($_GET['page']=='lu-settings/lu-settings-menu-options.php' && $_POST['submit']) {
					update_option($sanitized_subval, $_POST[$sanitized_subval]);
				}	
				if(get_option($sanitized_subval)){
			  		// remove_submenu_page($subitem[1],$subitem[2]);
				}
			}	
		}
	}
}
add_action( 'admin_init', 'admin_menu_settings' );



// add user activation menu to network admin users submenu
	add_action('network_admin_menu', 'ds_uak_admin_page');
	function ds_uak_admin_page() {
	        add_submenu_page('users.php', 'User Activations', 'User Activations', 'edit_users', 'act_keys', 'ds_delete_stale');
	}
	function ds_delete_stale(){
		global $wpdb;
		echo '<div class="wrap">';
		if($_GET['activate']){
			$signup = $wpdb->get_row('SELECT * FROM wp_signups WHERE activation_key = "'.$_GET['activate'].'" ORDER BY user_login ASC');
			$activated = wpmu_activate_signup($_GET['activate']);
			if($activated){
				echo '<div id="message" class="updated notice is-dismissible"><p>'.$signup->user_login.' has been activated</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			}
		}
		$signups = $wpdb->get_results('SELECT * FROM wp_signups WHERE active <> 1 ORDER BY user_login ASC');
		echo '<table class="wp-list-table widefat fixed striped users-network"><thead><tr>';
		echo '<td>Username</td>';
		echo '<td>Email</td>';
		echo '<td>Registered</td>';
		echo '<td></td>';
		echo '</tr></thead><tbody id="the-list">';
		if(count($signups)>0){
			foreach($signups as $signup){
				echo '<tr>';
				echo '<td>'.$signup->user_login.'</td>';
				echo '<td>'.$signup->user_email.'</td>';
				echo '<td>'.$signup->registered.'</td>';
				echo '<td><a href="users.php?page=act_keys&activate='.$signup->activation_key.'">Activate</a></td>';
				echo '</tr>';
			}
		} else {
			echo '<tr><td colspan="4" align="center">No inactive users</td></tr>';
		}
		echo '</tbody></table>';
		echo '</div>';
		// wpmu_activate_signup($activation_key);
	}

function admin_menu_settings_page() {
    global $menu_items;
    settings_fields( 'admin-menu-settings' );
    do_settings_sections( 'admin-menu-settings' );?>

	<div class="wrap">
		<h1>Admin Menu Settings</h1>

		<form method="post" action="">
			<table>
			    <?php 
				foreach($menu_items as $item => $val){ 
					$sanitized_val = sanitize_title('main_'.$val['item']);?>
			        <tr>
				        <th>
				        	<label class="checkbox<?php if(get_option($sanitized_val) == $val['item']){ echo ' removed'; } ?>">
				        	<?php echo $item; ?> <input type="checkbox" name="<?php echo $sanitized_val; ?>" <?php if(get_option($sanitized_val) == $val['item']){ echo 'checked="checked"'; } ?> value="<?php echo $val['item']; ?>" />
				        	</label>
				        </th>
				        <td>
					        <table>
								<?php foreach($val['subitems'] as $subitem){ 
									$sanitized_subval = sanitize_title('sub_'.$subitem[2]);?>
							        <tr>
							        	<td>
								        	<label class="checkbox<?php if(get_option($sanitized_subval) == $subitem[2]){ echo ' removed'; } ?>">
								        		<?php echo $subitem[0]; ?> <input type="checkbox" name="<?php echo $sanitized_subval; ?>" <?php if(get_option($sanitized_subval) == $subitem[2]){ echo 'checked="checked"'; } ?> value="<?php echo $subitem[2]; ?>" />
								        	</label>
								       	</td>
							       	</tr>
							    <?php } ?>
							</table>
				        </td>
			        </tr>
				<?php } ?>
			</table>
		    <?php submit_button(); ?>
		</form>
	</div>
	<script>
		jQuery('label.checkbox input').change(function(){
			if(jQuery(this).is(':checked')){
				jQuery(this).closest('label.checkbox').addClass('removed');
			} else {
				jQuery(this).closest('label.checkbox').removeClass('removed');
			}
		});
	</script>
	<style>
		tr {
		}
		th {
			text-align:left;
			padding:2px 20px 2px 0;
			/*border-bottom:1px solid #ccc;*/
			vertical-align: top;
		}
		td {
			/*border-bottom:1px solid #ccc;*/
			vertical-align: top;
			padding:2px 20px 2px 0;
		}
		input[type="checkbox"]{
			position: absolute;
			left:-99999px;
			opacity: 0;
			visibility: hidden;
		}
		label.checkbox {
			padding:5px 10px;
			display:inline-block;
		}
		label.removed {
			text-decoration: line-through;
			background:#d00;
			color:#fff;
		}
	</style>
<?php }