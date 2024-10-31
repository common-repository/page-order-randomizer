<?php
/**
 * @package page_order_randomizer
 * @version 1.0
 */
/*
Plugin Name: Page Order Randomizer
Plugin URI: http://wordpress.org/extend/plugins/page-order-randomizer/
Description: Randomizes the order pages/posts are shown.
Author: Rodrigo Pinto
Version: 1.0
Author URI: http://fasw.ws/
*/
/*  Copyright 2011  Rodrigo Pinto  (email : rodrigo@fasw.ws)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
$table_name = get_table_name();

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/admin.php';


function get_table_name()
{
	global $wpdb;
	return '`' . $wpdb->prefix . 'page_order_randomizer`';
}	
	
////ACTIVATION

register_activation_hook( __FILE__, 'page_order_randomizer_activate' );  
register_deactivation_hook( __FILE__, 'page_order_randomizer_deactivate' );  
  
function page_order_randomizer_activate() 
{  
    global $wpdb, $table_name;
    $table_name = get_table_name();
    $sql = "
            CREATE TABLE $table_name 
            (
  				`post_parent_id` bigint(20) NOT NULL,
  				`refresh_time` int(11) NOT NULL,
  				`last_refresh_date` datetime NOT NULL,
  				PRIMARY KEY (`post_parent_id`) USING BTREE
			) 
    ";  
  
    $wpdb->query($sql);  
}  
  
function page_order_randomizer_deactivate() 
{  
    global $wpdb, $table_name;  
    $sql = "DROP TABLE $table_name";  
    $wpdb->query($sql);  
}  	
	
////END ACTIVATION
	
	
add_action( 'wp_loaded', 'verifyrandom' );

function verifyrandom()
{
	global $wpdb, $table_name;
	$dnow = date('Y-m-d H:i:s'); 
	$rn_posts = $wpdb->get_results( 
		"
		SELECT ID as post_parent_id FROM $wpdb->posts WHERE post_parent IN 
		(
			SELECT post_parent_id
			FROM $table_name
			WHERE (TIMESTAMPDIFF(MINUTE, last_refresh_date, '$dnow') - refresh_time)>=0
		)
		"
	);
	foreach ($rn_posts as $rn_post) 
	{
		$wpdb->query("update $wpdb->posts set menu_order=" . rand(1, 100) . " where ID=$rn_post->post_parent_id");
		$wpdb->query("update $table_name set last_refresh_date='$dnow' where post_parent_id=$rn_post->post_parent_id");
	}
}


////ADMIN ACTIONS

add_action('admin_menu', 'my_plugin_menu');

function my_plugin_menu() {
	add_options_page('Page Randomizer Options', 'Page Order Randomizer', 'manage_options', 'page_order_randomizer_main_menu', 'page_order_randomizer_options');
}

function page_order_randomizer_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	include_once('admin.php');
	page_order_randomizer_admin();
}


function plugin_action($links, $file) {
	if ($file == plugin_basename(dirname(__FILE__).'/page_order_randomizer.php'))
		$links[] = "<a href='options-general.php?page=page_order_randomizer_main_menu'>" . __('Settings', 'page_order_randomizer', 'page_order_randomizer_main_menu') . "</a>";
	return $links;
}
//Add Plugin Actions to WordPress

add_filter('plugin_action_links', 'plugin_action', -10, 2);

////END ADMIN ACTIONS

?>
