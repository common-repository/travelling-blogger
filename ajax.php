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

add_action('wp_ajax_travelling_blogger_new_location', 'travelling_blogger_new_location_handler');

function travelling_blogger_new_location_handler() {
	header('Content: text/xml');
	$new_location = trim($_POST['new_location']);
	
	if ( !wp_verify_nonce( $_POST['_wp_nonce'], 'travelling_blogger_add_location' ) ) {
		?>
<new_location>
	<error>wrong_nonce</error>
</new_location
		<?php
	
	} elseif ($new_location == '') {
		?>
<new_location>
	<error>no_location_provided</error>
</new_location
		<?php
	} else {
		$loc = TravelBloggerLocation::newLocation($new_location);
		if ($loc) {
		?>
<new_location>
	<id><?php echo $loc->id; ?></id>
	<name><?php echo $loc->name; ?></name>
	<coords><?php echo $loc->coords[0] . ',' . $loc->coords[1]; ?></coords>
	<nonce><?php echo wp_create_nonce('travelling_blogger_delete_location'); ?></nonce>
</new_location>
		<?php
		} else {
		?>
<new_location>
	<error>no_geocode</error>
</new_location
		<?php
		}
	}
	die();
}