<?php
/*
Plugin Name: wpadminmenuaccess
Description: Manage WordPress Admin Menus Access
Version: 1.1
Author: Kedar
Plugin URI:
Author URI: 
License:
*/

//////////////////////////////////////////////////////////////////////////////////////
/////////////////// ADMIN MENU Functions & Code //////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////
add_action("admin_menu", "register_wpadminmenuaccess");
function register_wpadminmenuaccess()
{
	add_menu_page("WP Admin Menu Access", "WP Admin Menu Access", "", "wp_admin_menu_access", "wama_admin_menu_access_fun",  plugins_url("/wpadminmenuaccess/icon/dbcoss_icon_g.png"));
	add_submenu_page( "wp_admin_menu_access", "Manage Access","Manage Access", "manage_options", "manage_access_sub", "wama_manage_access_sub_fun"  );
}

function wama_admin_menu_access_fun()
{
	echo "Code for: WP Admin Menu Access ";
}

function wama_manage_access_sub_fun()
{
	if(!current_user_can('administrator'))
	{
		echo "You are not allowed to access this page";
		exit;
	}
	wp_enqueue_style( 'wpadminmenuaccess', plugins_url("css/wpadminmenuaccess.css", __FILE__) );
	$allowed_opts = array(
						'allowed_menus',
						'allowed_menus_roles',
						'allowed_menus_users'
					);
	
	if (isset($_POST['save'])) 
	{ 
		if(check_admin_referer('wama'))
		{
			$allowed_menus 			= isset( $_POST['allowed_menus'] ) ? (array) $_POST['allowed_menus'] : array();
			$allowed_menus_roles 	= isset( $_POST['roles'] ) ? (array) $_POST['roles'] : array();
			$allowed_menus_users 	= isset( $_POST['users'] ) ? (array) $_POST['users'] : array();
			
			/***** START - Sanitizing post data *************/
			$allowed_menus 			= array_map( 'sanitize_text_field', $allowed_menus );
			$allowed_menus_roles 	= array_map( 'wama_sanitize_text_field', $allowed_menus_roles);
			$allowed_menus_users 	= array_map( 'wama_sanitize_number_field', $allowed_menus_users);
			/***** END - Sanitizing post data *************/
			
			foreach($allowed_opts as $aopts)
			{
				if(get_option($aopts, false) === false)
				{
					add_option($aopts, json_encode($$aopts));
				}
				else
				{
					update_option($aopts, json_encode($$aopts));
				}
			}
		}
	}
    $allowed_menus 			= get_option('allowed_menus', false);
	$allowed_menus 			= ($allowed_menus !== false)?(array)json_decode($allowed_menus):array();
	$allowed_menus_roles 	= get_option('allowed_menus_roles', false);
	$allowed_menus_roles 	= ($allowed_menus_roles !== false)?(array)json_decode($allowed_menus_roles):array();
	$allowed_menus_users 	= get_option('allowed_menus_users', false);
	$allowed_menus_users 	= ($allowed_menus_users !== false)?(array)json_decode($allowed_menus_users):array();
	
	global $submenu, $menu, $pagenow;
	global $wp_roles;
	$menu_wama 		= $menu;
	$submenu_wama 	= $submenu;
	$all_users 		= get_users([ 'role__in' => [ 'administrator', 'editor', 'author', 'contributor' ] ] );

	?>
	<div id="wama">
		<h1>Allow Admin Menus Access</h1>
		<hr>
		<form method="post" >
			<?php wp_nonce_field( 'wama' );?>
			<table celspacing="0">
				<tr>
					<th class="w20">Left Side Menu</th>
					<th class="w10">Allow Menus Access</th>
					<th class="w30">Select Role</th>
					<th class="w30">Select User</th>
				</tr>
			<?php
			foreach ($menu_wama as $index=>$menu_row) 
			{ 
				if(isset($menu_row['6']) && $menu_row['6']!='')
				{
					//$submenu_row 	= isset($submenu_wama[$menu_row['2']]) ? (array)$submenu_wama[$menu_row['2']] : array() ;
					?>
					<tr>
						<td><span class="m_name"><?php echo $menu_row['0']; ?></span></td>
						<td><input type="checkbox" name="allowed_menus[]" value="<?php echo $menu_row['5']; ?>" <?php echo (in_array($menu_row['5'], $allowed_menus)?'checked="checked"':'');?>/></td>
						<td>
							<?php 
							$checked_roles = false;
							if(isset($allowed_menus_roles[$menu_row['5']]) && count($allowed_menus_roles[$menu_row['5']]) >0)
							{
								$checked_roles = true;
							}
							?>
							<input type="radio" <?php echo ($checked_roles?'checked="checked"':'')?> onclick="document.getElementById('roles_<?php echo $menu_row['5']; ?>').style.display='inline-block';" /> Select Roles to Allow
							<select class="select_bx <?php echo ($checked_roles?'':'dnone')?>" id="roles_<?php echo $menu_row['5']; ?>" name="roles[<?php echo $menu_row['5']; ?>][]" multiple class="access_options">
								<?php 
								foreach ( $wp_roles->roles as $key=>$value )
								{
									if($key == 'administrator') continue;
									?>
									<option value="<?php echo $key; ?>" <?php echo (isset($allowed_menus_roles[$menu_row['5']]) && in_array($key, $allowed_menus_roles[$menu_row['5']])?'selected="selected"':'');?>><?php echo $value['name']; ?></option>
									<?php 
								}
								?>
							</select>
						</td>
						<td>
							<?php 
							$checked_user = false;
							if(isset($allowed_menus_users[$menu_row['5']]) && count($allowed_menus_users[$menu_row['5']]) >0)
							{
								$checked_user = true;
							}
							?>
							<input type="radio" <?php echo ($checked_user?'checked="checked"':'')?> onclick="document.getElementById('users_<?php echo $menu_row['5']; ?>').style.display='inline-block';" /> Select Users to Allow
							<select class="select_bx <?php echo ($checked_user?'':'dnone')?>" id="users_<?php echo $menu_row['5']; ?>" name="users[<?php echo esc_attr($menu_row['5']); ?>][]" multiple class="access_options">
								<?php 
								foreach ($all_users as $u )
								{
									if($u->ID == '1') continue;
									?>
									<option value="<?php echo $u->ID; ?>" <?php echo (in_array($u->ID, $allowed_menus_users[$menu_row['5']])?'selected="selected"':'');?>><?php echo $u->user_email; ?></option>
									<?php 
								}
								?>
							</select>
						</td>
					</tr>
					<?php
				}
			}
			?>
			</table>
			<input type="submit" class="save_btn" name="save" value="Save" />
		</form>
	</div>
	<?php
}

function wama_sanitize_text_field( &$array ) {
	foreach ($array as &$value) 
	{	
		if( !is_array($value) )
		{
			$value = sanitize_text_field( $value );
		}
		else
		{
			wama_sanitize_text_field($value);
		}
	}
	return $array;
} 

function wama_sanitize_number_field( &$array ) {
	foreach ($array as &$value) 
	{	
		if( !is_array($value) )
		{
			$value = intval( $value );
		}
		else
		{
			wama_sanitize_number_field($value);
		}
	}
	return $array;
} 


function wpadminmenuaccess_remove_menu_if_allowed() 
{
	global $menu;
	global $current_user;
	$menu_wama 		= $menu;
	$curr_user_roles = $current_user->roles;
	$curr_user_id 	= $current_user->roles;
	
	$allowed_menus 			= get_option('allowed_menus', false);
	$allowed_menus 			= ($allowed_menus !== false)?(array)json_decode($allowed_menus):array();
	$allowed_menus_roles 	= get_option('allowed_menus_roles', false);
	$allowed_menus_roles 	= ($allowed_menus_roles !== false)?(array)json_decode($allowed_menus_roles):array();
	$allowed_menus_users 	= get_option('allowed_menus_users', false);
	$allowed_menus_users 	= ($allowed_menus_users !== false)?(array)json_decode($allowed_menus_users):array();
	
	$menu_slug_page_mapping = array();
	foreach ($menu_wama as $index=>$menu_row) 
	{
		if(isset($menu_row[5]))
		{
			$menu_slug_page_mapping[$menu_row[5]] = $menu_row[2];
		}
	}
			
	foreach($allowed_menus as $menu_slug)
	{
		if(!(in_array($curr_user_id, $allowed_menus_users[$menu_slug])
			|| array_intersect($curr_user_roles, $allowed_menus_roles[$menu_slug])
			|| current_user_can('administrator')))
		{
			remove_menu_page($menu_slug_page_mapping[$menu_slug]);
		}
	}
}
add_action( 'admin_menu', 'wpadminmenuaccess_remove_menu_if_allowed' ,'99999');