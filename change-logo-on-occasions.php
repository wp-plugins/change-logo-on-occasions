<?php
/**
 * Plugin Name: Change Logo On Occations
 * Plugin URI: https://profiles.wordpress.org/melangercz/
 * Description: Change logos on certain occasions (e.g. Christmas, Halloween) just like Google does.
 * Version: 1.0
 * Author: melangercz
 * Author URI: http://melanger.cz/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: change-logo-on-occasions
 * Domain Path: /languages
 */
/*  Copyright 2014  Mélanger.cz  (email : plugins@melanger.cz)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('plugins_loaded', 'doodle_logos_lang');
function doodle_logos_lang(){
load_plugin_textdomain('change-logo-on-occasions', false, dirname(plugin_basename( __FILE__ )).'/languages/');
}

  if ( ! function_exists('custom_post_type_logo') ) {

// Register Custom Post Type
function custom_post_type_logo() {

	$labels = array(
		'name'                => __('Logos', 'change-logo-on-occasions'),
		'singular_name'       => __('Logo', 'change-logo-on-occasions'),
		'menu_name'           => __('Logos', 'change-logo-on-occasions')
	);
	$args = array(
		'label'               => 'doodle_logo',
		'description'         => __('Logos', 'change-logo-on-occasions'),
		'labels'              => $labels,
		'supports'            => array( 'title', 'thumbnail', 'excerpt' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => false,
		'menu_position'       => 5,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'post',
	);
	register_post_type( 'doodle_logo', $args );

}

// Hook into the 'init' action
add_action( 'init', 'custom_post_type_logo');

}

add_action( 'admin_notices', 'logo_admin_notice' );
function logo_admin_notice() {
	$expired_logos = count(get_posts(array('post_type'=>'doodle_logo','posts_per_page'=>-1,'post_status'=>'private')));
	if($expired_logos>0):
    ?>
    <div class="updated">
        <p>
		<?php echo sprintf(__("Some logos are outdated (%d).", 'change-logo-on-occasions'), $expired_logos)." <a href='".admin_url("edit.php?post_status=private&amp;post_type=doodle_logo")."'>".sprintf(__("Edit expired logos",'change-logo-on-occasions'))."</a>";?>
		</p>
    </div>
    <?php
	endif;
}

$change_logo_recommended_plugins = array('post-expirator'=>"Post Expirator");

add_action('admin_notices', 'change_logo_dependencies_notice');
function change_logo_dependencies_notice() {
	global $current_user ;
        $user_id = $current_user->ID;
	global $change_logo_recommended_plugins;
	$plugin_found = false;
	foreach($change_logo_recommended_plugins as $required_plugin=>$required_plugin_label):
	if (is_plugin_active( $required_plugin.'/'.$required_plugin.'.php' ) ) {
		$plugin_found = true;
		break;
	}
	endforeach;
	list($required_plugin) = array_keys($change_logo_recommended_plugins);
	$required_plugin_label = $change_logo_recommended_plugins[$required_plugin];
	if(!$plugin_found && !get_user_meta($user_id, 'change_logo_nag_ignore')){
		?>
		<div class="updated">
			<p>
			<?php echo sprintf(__('For <strong>Change logo on occasions</strong> to work it is recommended to install a post expiration plugin, e.g. %s. | <a href="%s">Hide Notice</a>','change-logo-on-occasions'), "<a href='https://wordpress.org/plugins/".$required_plugin."/'>".esc_html($required_plugin_label)."</a>", '?change_logo_nag_ignore=0');?>
			</p>
		</div>
		<?php
	}
}

add_action('admin_init', 'change_logo_nag_ignore');

function change_logo_nag_ignore() {
	global $current_user;
        $user_id = $current_user->ID;
        /* If user clicks to ignore the notice, add that to their user meta */
        if ( isset($_GET['change_logo_nag_ignore']) && '0' == $_GET['change_logo_nag_ignore'] ) {
             add_user_meta($user_id, 'change_logo_nag_ignore', 'true', true);
	}
}

function change_logo_delete_ignore_user_meta(){
	$users = get_users('fields=ID');
	foreach($users as $user_id){
		delete_user_meta($user_id, 'change_logo_nag_ignore');
	}
}
register_activation_hook( __FILE__, 'change_logo_delete_ignore_user_meta' );
register_deactivation_hook( __FILE__, 'change_logo_delete_ignore_user_meta' );

function show_current_doodle_logo(){
	$logos = get_posts(array('post_type'=>'doodle_logo','orderby'=>'post_date','order'=>'DESC','posts_per_page'=>1,'post_status'=>'publish'));
	if(count($logos)>0){
		$logo = $logos[0];
		$link = $logo->post_excerpt;
		if(!$link) $link = '#';
		$img = get_the_post_thumbnail($logo->ID, 'full', array('class'=>'logo_kure','alt'=>get_bloginfo('name')));
		echo '<a href="'.$link.'">'.$img.'</a>';
	}
}