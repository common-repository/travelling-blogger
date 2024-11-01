<?php
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


add_action('admin_menu', 'travelling_blogger_admin_handler');
function travelling_blogger_admin_handler() {
	add_posts_page( __('Locations',TRAVELLING_BLOGGER), __('Locations',TRAVELLING_BLOGGER), 'manage_categories', 'travelling-blogger', 'travelling_blogger_admin');
}

add_action('delete_post', 'travelling_blogger_delete_post');

function travelling_blogger_delete_post($post_id) {
	TravelBloggerLocation::setPostLocation($post_id, false);
}

if (is_admin()) {
	wp_enqueue_script( 'travelling_blogger_admin', plugin_dir_url( __FILE__ ) . 'admin.js', array( 'jquery' ) );
}

function travelling_blogger_admin() {

	if (!current_user_can('manage_categories'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	if (array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'edit') {
		require_once('admin_edit_location.php');
		travelling_blogger_edit_location();
	} else {
		require_once('list-table-location.php');
		travelling_blogger_display_location_table();
	}
}