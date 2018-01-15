<?php

function update_lu_options() {
	register_setting( 'lu-options', 'lu_load_unminified_css' );
	register_setting( 'lu-options', 'lu_load_unminified_js' );
	register_setting( 'lu-options', 'lu_force_login' );
	register_setting( 'lu-options', 'restrict_to_roles' );
	register_setting( 'lu-options', 'redirect_user_to' );
	register_setting( 'lu-options', 'lu_image_quality' );
	register_setting( 'lu-options', 'lu_image_dimensions_width' );
	register_setting( 'lu-options', 'lu_image_dimensions_height' );
	register_setting( 'lu-options', 'image_sizes' );
	register_setting( 'lu-options', 'lu_google_tags' );

	if($_GET['page']=='lu-admin-settings/lu-settings.php' && $_POST['submit']) {
		update_option('lu_load_unminified_css', $_POST['lu_load_unminified_css']);
		update_option('lu_load_unminified_js', $_POST['lu_load_unminified_js']);
		update_option('lu_force_login', $_POST['lu_force_login']);
		update_option('restrict_to_roles', maybe_serialize($_POST['restrict_to_roles']));
		update_option('redirect_user_to', $_POST['redirect_user_to']);
		update_option('lu_image_quality', $_POST['lu_image_quality']);
		update_option('lu_image_dimensions_width', $_POST['lu_image_dimensions_width']);
		update_option('lu_image_dimensions_height', $_POST['lu_image_dimensions_height']);
		update_option('lu_google_tags', maybe_serialize($_POST['gtm']));
		if($_POST['image_sizes']){
			foreach($_POST['image_sizes'] as $i=>$imageSize){
				$_POST['image_sizes'][$i]['slug'] = sanitize_title($imageSize['name']);
			}
		}
		update_option('image_sizes', maybe_serialize($_POST['image_sizes']));
	}	
}
add_action( 'admin_init', 'update_lu_options' );

function admin_lu_options_page() {
    global $menu_items;
    settings_fields( 'lu-options' );
    do_settings_sections( 'lu-options' );?>

	<style>
		#restrict_to_roles {
			display:none;
		}
		.label {
			width:150px;
			display:inline-block;
		}
		label {
			margin-bottom:5px;
			display:inline-block;
		}
		table {
			border-collapse: collapse;
			border-spacing: 5px;
		}
		th {
			text-align:left;
			border-bottom:1px solid #ccc;
		}
		td, th {
			padding:5px 10px;
			border-right:1px solid #ccc;
		}
		td:last-child, th:last-child {
			border-right:none;
		}
	</style>

	<div class="wrap">

		<form method="post" action="">
			
		<div class="postbox">
			<div class="inside">
				<h2>Debugging Settings</h2>

				<label class="switcher checkbox">
	        		Load Unminified CSS <input type="checkbox" name="lu_load_unminified_css" <?php if(get_option('lu_load_unminified_css') == true){ echo 'checked="checked"'; } ?> value="1" />
	        		<div class="switch">
	        			<div class="slider"></div>
	        		</div>
	        	</label>
	        	<br/>
				<label class="switcher checkbox">
	        		Load Unminified JS <input type="checkbox" name="lu_load_unminified_js" <?php if(get_option('lu_load_unminified_js') == true){ echo 'checked="checked"'; } ?> value="1" />
	        		<div class="switch">
	        			<div class="slider"></div>
	        		</div>
	        	</label>
			</div>			
		</div>

		<div class="postbox">
			<div class="inside">

	        	<h3>Image Options</h3>
				<label class="number">
	        		<span class="label">Image Quality</span> <input type="number" style="width:50px;" min="0" max="100" name="lu_image_quality" value="<?=(get_option('lu_image_quality')?get_option('lu_image_quality'):100)?>" />
	        	</label>
	        	<br/>

				<label class="text">
					<span class="label">Max Width</span> <input type="number" style="width:75px" name="lu_image_dimensions_width" value="<?=get_option('lu_image_dimensions_width')?>" />px
	        	</label>
	        	<br/>
				<label class="text">
					<span class="label">Max Height</span> <input type="number" style="width:75px" name="lu_image_dimensions_height" value="<?=get_option('lu_image_dimensions_height')?>" />px
	        	</label>

	        	<h4>Image Sizes</h4>
	        	<template class="image_size_template">
	        		<tr>
						<td>
							<label class="text">
								<input type="text" style="width:200px" name="image_sizes[TEMPLATE][name]" />
				        	</label>
				        </td>
						<td>
							<label class="text">
								<input type="text" style="width:75px" name="image_sizes[TEMPLATE][width]" />px
				        	</label>
				        </td>
				        <td>
							<label class="text">
								<input type="text" style="width:75px" name="image_sizes[TEMPLATE][height]" />px
				        	</label>
				        </td>
				        <td>
							<label class="switcher checkbox">
								<input type="checkbox" name="image_sizes[TEMPLATE][crop]" value="1" />
								<div class="switch">
				        			<div class="slider"></div>
				        		</div>
				        	</label>
				        </td>
						<td><a href="#" class="remove_image_size"><span class="dashicons dashicons-trash" style="color:#900; text-decoration:none;"></span></a></td>
				    </tr>
			    </template>
	        	<div class="image_sizes">
					<table>
						<tr><th>Name</th><th>Width</th><th>Height</th><th>Crop</th><th></th></tr>
						<?php 
							$imageSizes = maybe_unserialize(get_option('image_sizes'));
							if($imageSizes){
								foreach($imageSizes as $i=>$imageSize){?>
					        		<tr>
										<td>
											<label class="text">
												<input type="text" style="width:200px" name="image_sizes[<?=$i?>][name]" value="<?=stripslashes($imageSize['name'])?>" />
								        	</label>
								        </td>
										<td>
											<label class="text">
												<input type="text" style="width:75px" name="image_sizes[<?=$i?>][width]" value="<?=$imageSize['width'];?>" />px
								        	</label>
								        </td>
								        <td>
											<label class="text">
												<input type="text" style="width:75px" name="image_sizes[<?=$i?>][height]" value="<?=$imageSize['height'];?>"/>px
								        	</label>
								        </td>
								        <td>
											<label class="switcher checkbox">
												<input type="checkbox" name="image_sizes[<?=$i?>][crop]" <?=$imageSize['crop']!=0?'checked="checked"':''?> value="1" />
												<div class="switch">
								        			<div class="slider"></div>
								        		</div>
								        	</label>
								        </td>
								        <td><a href="#" class="remove_image_size"><span class="dashicons dashicons-trash" style="color:#900; text-decoration:none;"></span></a></td>
								    </tr>
								<?php }
							}
						?>
					</table>
	        	</div>
	        	<a class="add_image_size button">Add Image Size</a>
	        	<script>
	        		jQuery('.add_image_size').click(function(){
	        			var imageSizeTemplate = jQuery('.image_size_template')[0].innerHTML;
						imageSizeTemplate = imageSizeTemplate.replace(/TEMPLATE/gi, jQuery('.image_sizes table tr').length-1);
	        			jQuery('.image_sizes table').append(imageSizeTemplate);
	        			set_image_size_remove();
	        			return false;
	        		});
	        		set_image_size_remove();
	        		function set_image_size_remove(){
		        		jQuery('.remove_image_size').unbind().click(function(){
		        			jQuery(this).closest('tr').remove();
		        			return false;
		        		});
		        	}
	        	</script>
	        </div>
	    </div>
			
		<div class="postbox">
			<div class="inside">
				<h2>Options</h2>
				
				<label class="switcher checkbox">
		    		Force Login <input type="checkbox" name="lu_force_login" <?php if(get_option('lu_force_login') == true){ echo 'checked="checked"'; } ?> value="1" />
		    		<div class="switch">
		    			<div class="slider"></div>
		    		</div>
		    	</label>
		    	<div id="restrict_to_roles" <?php if(get_option('lu_force_login') == true){ echo 'style="display:block"'; } ?>>
		        	<br/>
		        	<?php 
		        	$restrict_to_roles = maybe_unserialize(get_option('restrict_to_roles')); ?>
					<h3>Restrict to Roles</h3>
					<?php $qroles = array(
						'Administrator',
						'Admission',
						'AdmissionLUCOM ',
						'AdmissionLUO',
						'AdmissionResident',
						'AlumniLUCOM ',
						'AlumniSOL ',
						'Alumnus',
						'Demonstrator ',
						'Employee',
						'EmployeeFaculty',
						'EmployeeStaff',
						'EmployeeStudent',
						'FacultyStaffLUCOM  ',
						'FacultyStaffSOL ',
						'Friend',
						'Student',
						'StudentAcademy',
						'StudentLUCOM',
						'StudentLUO',
						'StudentResident',
						'StudentSOL');
		    		?>
					<?php foreach($qroles as $qrole){?>
						<label class="switcher checkbox">
							<span style="width:100px;display:inline-block;"><?php echo $qrole; ?></span>
							<input type="checkbox" name="restrict_to_roles[]" <?php if(is_array($restrict_to_roles) && in_array($qrole, $restrict_to_roles)){ ?>checked="checked" <?php } ?> value="<?php echo $qrole; ?>">
			        		<div class="switch">
			        			<div class="slider"></div>
			        		</div>
						</label>
						<br/>
					<?php } ?>
					<label>
						Redirect users to page:
						<select name="redirect_user_to">
							<?php $redirect_user_to = get_option('redirect_user_to'); ?>
							<?php foreach(get_pages() as $page){ ?>
								<option value="<?php echo $page->ID; ?>" <?php if($redirect_user_to == $page->ID){ echo 'selected="selected"'; } ?>><?php echo $page->post_title; ?></option>
							<?php } ?>
						</select>
					</label>
		        </div>
		    </div>
		</div>
			
		<div class="postbox">
			<div class="inside">
				<h2>Google Tags</h2>
				<?php $google_tags = maybe_unserialize(get_option('lu_google_tags'));?>
				<label style="width:auto;">
	        		Default:<br/><textarea rows="10" style="width:600px;" name="gtm[Default]"><?php if($google_tags['Default']){ echo stripslashes($google_tags['Default']); }?></textarea>
	        	</label>
	        	<br/>
				<?php foreach(get_post_types() as $post_type){
					if($post_type != 'attachment' && $post_type != 'revision' && $post_type != 'nav_menu_item' && $post_type != 'custom_css' && $post_type != 'acf-field-group' && $post_type != 'acf-field' && $post_type != 'gb_reusable_block' && $post_type != 'mgmlp_media_folder' && $post_type != 'customize_changeset'){ ?>
					<label style="width:auto;">
		        		<?=$post_type;?><br/><textarea rows="10" style="width:600px;" name="gtm[<?=$post_type;?>]"><?php if($google_tags[$post_type]){ echo stripslashes($google_tags[$post_type]); }?></textarea>
		        	</label>
		        	<br/>
					<?php } 
				}?>
			</div>			
		</div>
	    <?php submit_button(); ?>
	</form>
</div>
<script>
	jQuery('[name="lu_force_login"]').change(function(){
		if(jQuery(this).is(":checked")){
			jQuery('#restrict_to_roles').show();
		} else {
			jQuery('#restrict_to_roles').hide();
		}
	});
</script>
<?php }