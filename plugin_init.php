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

global $travelling_blogger_version;
$travelling_blogger_version = "1.0";

function travelling_blogger_install_handler() {

	global $wpdb;
	global $travelling_blogger_version;

	$table_location_name = $wpdb->prefix . "travelling_blogger_location";
	$table_post_name = $wpdb->prefix . "travelling_blogger_post";
		
	$sqlLocation = "CREATE TABLE " . $table_location_name . " (
		id INT NOT NULL AUTO_INCREMENT,
		name VARCHAR(255) NOT NULL,
		lng DOUBLE,
		lat DOUBLE,
		PRIMARY KEY (id)
	);";
	
	$sqlPost .= "CREATE TABLE " . $table_post_name . " (
		post_id INT NOT NULL,
		location_id INT NOT NULL,
		PRIMARY KEY (post_id)
	);";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sqlLocation);
	dbDelta($sqlPost);

	add_option("travelling_blogger_version", $travelling_blogger_version);
}