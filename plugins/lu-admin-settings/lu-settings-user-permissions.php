<?php 
add_action( 'show_user_profile', 'lu_additional_profile_fields' );
add_action( 'edit_user_profile', 'lu_additional_profile_fields' );
function lu_additional_profile_fields( $user ) {
  $superuser_caps = array(
    'edit_themes',
    'activate_plugins',
    'edit_plugins',
    'edit_users',
    'edit_files',
    'delete_users',
    'create_users',
    'unfiltered_upload',
    'update_plugins',
    'delete_plugins',
    'install_plugins',
    'update_themes',
    'install_themes',
    'update_core',
    'delete_themes',
    'manage_links',
    'unfiltered_html'
  );
  $default_roles = array(
    'subscriber'=>true,
    'contributor'=>true,
    'author'=>true,
    'editor'=>true,
    'administrator'=>true
  );
  if(current_user_can('manage_users')){
    $original_site = get_current_blog_id();
    $current_user = get_current_user_id();
    $active=false;
    wp_set_current_user($user->ID);
    $sites = [];
    if(is_network_admin()){
      foreach(get_sites() as $site){
        array_push($sites, $site);
      }
    } else {
      $active='nav-tab-active ';
      array_push($sites, get_sites(array('ID'=>get_current_blog_id()))[0]);
    }
    $user = get_user_by('id', $user->ID);
    // print_r($user->roles);

    // print_r(maybe_unserialize(get_the_author_meta($siteid.'additional_capabilities', $user->ID)));
    if(get_the_author_meta($siteid.'additional_capabilities', $user->ID)){
      $capabilities = maybe_unserialize(get_the_author_meta($siteid.'additional_capabilities', $user->ID));
    }
    ?>

    <h2>Site Permissions</h2>
    <span><em>Overwritten by User Role</em></span><br/>
    <?php if(is_network_admin()){?>
      <h2 class="<?php echo $active; ?>nav-tab-wrapper vertical sites">
      <?php foreach($sites as $site){
        $siteid = 'site_'.$site->blog_id.'_';?>
        <a href="#" class="nav-tab site" data-site="<?php echo $site->blog_id; ?>"><?php echo $site->blogname; ?></a>
      <?php } ?>
      </h2>
    <?php } ?>

    <?php foreach($sites as $site){
      if($original_site != $site->blog_id){
        switch_to_blog($site->blog_id);
      }
      global $wp_roles;

      $roles = $wp_roles->roles;
      $roles = array_reverse($roles);
      if(count(array_diff_key($roles, $default_roles))>0){
        $roles = array_intersect_key($roles, $default_roles);
        $roles = array_merge($roles, array_diff_key($wp_roles->roles, $roles));
      }

      $cpts = get_posts(array(
        'post_type'=>'lu_custom_post_types',
        'posts_per_page'=>-1,
        'order'=>'desc'
      ));

      $siteid = 'site_'.$site->blog_id.'_';?>
      <h2 <?php if($active){echo 'style="display:block;" '; } ?>class="nav-tab-wrapper vertical roles" data-site="<?php echo $site->blog_id; ?>" id="<?php echo $siteid; ?>roles">
        <?php foreach($roles as $rolename => $role){?>
          <a href="#" data-site="<?php echo $site->blog_id; ?>" data-role="<?php echo $siteid.$rolename; ?>" class="role nav-tab"><?php echo $role['name']; ?> Capabilities</a>
        <?php } ?>
        <?php foreach($cpts as $cpt){ 
          $post_type = get_post_meta($cpt->ID, 'post_type_slug', true); ?>
          <a href="#" data-site="<?php echo $site->blog_id; ?>" data-role="<?php echo $siteid.$post_type.'_editor'; ?>" class="role nav-tab"><?php echo $cpt->post_title; ?> Capabilities</a>
        <?php } ?>
      </h2>

      <?php foreach($cpts as $cpt){ 
        $post_type = get_post_meta($cpt->ID, 'post_type_slug', true);
        ?>
        <div class="tab-item capabilities" data-role="<?php echo $siteid.$post_type.'_editor'; ?>" id="<?php echo $siteid.$rolename; ?>">
          <label><input type="checkbox" class="toggleAll">Toggle All</label><br/>
          <?php foreach(get_cpt_capabilities($post_type) as $cpt_cap){ ?>
            <label><input type="checkbox"<?php if(current_user_can_for_blog($site->blog_id, $cpt_cap) || current_user_can_for_blog($site->blog_id, $post_type.'_editor')) {echo 'checked="checked"';}; ?> name="<?php echo $siteid; ?>additional_capabilities[<?php echo $cpt_cap; ?>]" value="<?php echo $cpt_cap; ?>"><?php echo ucwords(str_replace('_', ' ', $cpt_cap)); ?></label><br/>
          <?php } ?>
        </div>
      <?php } ?>

      <?php $processedCaps = [];
      foreach($roles as $rolename => $role){?>
        <div class="tab-item capabilities" data-role="<?php echo $siteid.$rolename; ?>" id="<?php echo $siteid.$rolename; ?>">
          <label><input type="checkbox" class="toggleAll">Toggle All</label><br/>
          <?php foreach($role['capabilities'] as $cap=>$value){ 
            if(!in_array($cap, $superuser_caps)){?>
              <label><input <?php if($processedCaps[$cap]){ echo 'disabled="disabled" ';} ?>type="checkbox"<?php if(current_user_can_for_blog($site->blog_id, $cap) || current_user_can_for_blog($site->blog_id, $rolename)) {echo 'checked="checked"';}; ?> <?php if(!$processedCaps[$cap]){ ?>name="<?php echo $siteid; ?>additional_capabilities[<?php echo $cap; ?>]"  value="<?php echo $cap; ?>"<?php } ?>><?php echo ucwords(str_replace('_', ' ', $cap)); ?><?php if($processedCaps[$cap]){ echo ' (set in '.$processedCaps[$cap].') ';} ?></label><br/>
              
              <?php if(
                !$processedCaps[$cap] &&
                (
                  $rolename=='subscriber'||
                  $rolename=='contributor'||
                  $rolename=='author'||
                  $rolename=='editor'||
                  $rolename=='administrator'
                  // $rolename=='shop_manager'|| // woocommerce role
                  // $rolename=='customer' // woocommerce role
                )
                ){
                $processedCaps[$cap]=$role['name'];
              }
            } ?>
          <?php }?>
        </div>
      <?php } ?>
    <?php
    }
  ?>
    <style>
      .nav-tab-wrapper.vertical {
        padding-top: 0!important;
        box-sizing:border-box;
        /*clear:both;*/
        float:left;
        width:33%;
        border:none!important;
      }
      .vertical a.nav-tab {
        clear: left;
        margin-left:0;
        width:100%;
        box-sizing:border-box;
      }
      .vertical a.nav-tab-active {
        border-bottom-color:#ccc;
      }
      .roles {
        float:left;
        clear:none;
        display:none;
        width:66%;
      }
      .tab-item.capabilities {
        border: 1px solid #ccc;
        display:none;
        padding:5px 20px;
        float:left;
        width:33%;
        box-sizing: border-box;
      }
    </style>
    <script type="text/javascript">
      jQuery(document).ready(function(){
        jQuery('.site').click(function(){
          jQuery('.site').removeClass('nav-tab-active');
          jQuery('.roles').hide();
          jQuery('.role').removeClass('nav-tab-active');
          jQuery('.capabilities').hide();
          jQuery(this).addClass('nav-tab-active');
          jQuery('[data-site="'+jQuery(this).attr('data-site')+'"]').show();
          return false;
        });
        jQuery('.role').click(function(){
          jQuery('.role').removeClass('nav-tab-active');
          jQuery('.capabilities').hide();
          jQuery(this).addClass('nav-tab-active');
          jQuery('[data-role="'+jQuery(this).attr('data-role')+'"]').show();
          return false;
        });
        jQuery('.toggleAll').change(function(){
          if(jQuery(this).is(':checked')){
            jQuery(this).closest('.tab-item').find('[type="checkbox"]').attr('checked', true);
          } else {
            jQuery(this).closest('.tab-item').find('[type="checkbox"]').attr('checked', false);
          }
        });
      });
    </script>
    <br/>
    <div style="clear:both;"></div>
    <?php
    if($original_site != get_current_blog_id()){
      switch_to_blog($original_site);
    }
    wp_set_current_user($current_user->ID);
  }
}

add_action( 'personal_options_update', 'lu_save_additional_profile_fields' );
add_action( 'edit_user_profile_update', 'lu_save_additional_profile_fields' );
function lu_save_additional_profile_fields( $user_id ) {
  $superuser_caps = array(
    'edit_themes',
    'activate_plugins',
    'edit_plugins',
    'edit_users',
    'edit_files',
    'delete_users',
    'create_users',
    'unfiltered_upload',
    'update_plugins',
    'delete_plugins',
    'install_plugins',
    'update_themes',
    'install_themes',
    'update_core',
    'delete_themes',
    'manage_links',
    'unfiltered_html'
  );
  $default_roles = array(
    'subscriber'=>true,
    'contributor'=>true,
    'author'=>true,
    'editor'=>true,
    'administrator'=>true
  );
  if ( ! current_user_can( 'edit_user', $user_id ) ) {
   return false;
  }

  $original_site = get_current_blog_id();
  $sites = [];
  if(is_network_admin()){
    foreach(get_sites() as $site){
      array_push($sites, $site);
    }
  } else {
    array_push($sites, get_sites(array('ID'=>get_current_blog_id()))[0]);
  }

  foreach($sites as $site){
    if($original_site != $site->blog_id){
      switch_to_blog($site->blog_id);
    }
    global $wp_roles;
    $curruser = new \WP_User( $user_id );
    // remove_user_from_blog( $user_id, $site->blog_id );
    $siteid = 'site_'.$site->blog_id.'_';

    update_usermeta( $user_id, $siteid.'additional_capabilities', maybe_serialize($_POST[$siteid.'additional_capabilities']));

    $roles = $wp_roles->roles;
    $roles = array_reverse($roles);
    if(count(array_diff_key($roles, $default_roles))>0){
      $roles = array_intersect_key($roles, $default_roles);
      $roles = array_merge($roles, array_diff_key($wp_roles->roles, $roles));
    }

    foreach($roles as $rolename=>$role){
      if($default_roles[$rolename]){
        $hasRoleCaps=true;
      } else {
        $hasRoleCaps=false;
      }
      foreach($role['capabilities'] as $cap=>$value){
        if(!in_array($cap, $superuser_caps)){
          $curruser->remove_cap($cap);
          if(is_array($_POST[$siteid.'additional_capabilities']) && in_array($cap, $_POST[$siteid.'additional_capabilities'])){
            $curruser->add_cap( $cap );
            // add_user_to_blog( $site->blog_id, $user_id, $cap );
          } else {
            $hasRoleCaps=false;
          }
        }
      }

      // trying to update user role if capabilities equal role capabilites
      if($hasRoleCaps){
        remove_user_from_blog( $user_id, $site->blog_id );
        $_POST['role']=$rolename;
      }
    }
    
    $cpts = get_posts(array(
      'post_type'=>'lu_custom_post_types',
      'posts_per_page'=>-1,
      'order'=>'desc'
    ));
    foreach($cpts as $cpt){ 
      $post_type = get_post_meta($cpt->ID, 'post_type_slug', true);
      $post_type_caps = array(
        'read_'.$post_type,
        'create_'.$post_type,
        'edit_'.$post_type,
        'publish_'.$post_type.'s',
        'edit_'.$post_type.'s',
        'delete_'.$post_type,
        'edit_others_'.$post_type.'s',
        'read_private_'.$post_type.'s',
        'delete_'.$post_type.'s',
        'delete_private_'.$post_type.'s',
        'delete_published_'.$post_type.'s',
        'delete_others_'.$post_type.'s',
        'edit_private_'.$post_type.'s',
        'edit_published_'.$post_type.'s'
      );
      foreach($post_type_caps as $cpt_cap){
        $curruser->remove_cap($cpt_cap);
        if(is_array($_POST[$siteid.'additional_capabilities']) && in_array($cpt_cap,$_POST[$siteid.'additional_capabilities'])){
          $curruser->add_cap( $cpt_cap );
        }
      }
    }
  }
  if($original_site != get_current_blog_id()){
    switch_to_blog($original_site);
  }
  // print_r($_POST['role']);
  // print_r($curruser->roles);
  // exit;
}


add_filter('additional_capabilities_display', 'remove_additional_capabilities_func');
function remove_additional_capabilities_func() {
  if ( current_user_can( 'edit_user', $user_id ) ) return true;
}

add_action( 'admin_init', 'clean_unwanted_caps' );
function clean_unwanted_caps(){
  global $user_id;
  if ( ! current_user_can( 'edit_user', $user_id ) ) {
   return false;
  }
  $cpts = get_posts(array(
    'post_type'=>'lu_custom_post_types',
    'posts_per_page'=>-1,
    'order'=>'desc'
  ));
  $activeCpts=[];
  foreach($cpts as $cpt){
    array_push($activeCpts, get_post_meta($cpt->ID, 'post_type_slug', true));
  }
  global $wp_roles;
  foreach ($wp_roles->roles as $rolename=>$caps) {
    if(
      $rolename!='subscriber'&&
      $rolename!='contributor'&&
      $rolename!='author'&&
      $rolename!='editor'&&
      $rolename!='administrator'&&
      $rolename!='shop_manager'&& // woocommerce role
      $rolename!='customer'&& // woocommerce role
      !in_array($rolename, $activeCpts)
    ){
      remove_role($rolename);
    }
  }
}