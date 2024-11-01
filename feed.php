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

function travelling_blogger_georss_handler() {
	$loc = TravelBloggerLocation::getLocationByPost(get_the_ID());
	if ($loc) {
		echo "<georss:point>" . $loc->getLat() . ' ' . $loc->getLng() . "</georss:point>";
	}
}
add_action('rss2_item', 'travelling_blogger_georss_handler');
add_action('atom_entry', 'travelling_blogger_georss_handler');

function travelling_blogger_georss_ns_handler() {
	echo ' xmlns:georss="http://www.georss.org/georss" ';
}
add_action('rss2_ns', 'travelling_blogger_georss_ns_handler');
add_action('atom_ns', 'travelling_blogger_georss_ns_handler');
