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


add_shortcode( 'location_mini_map', 'travelling_blogger_location_mini_map_short_code_handler' );

function travelling_blogger_location_mini_map_short_code_handler($atts) {
	$atts = (is_array($atts)) ? $atts: array();
	$location = TravelBloggerLocation::getLocationByPost();
	$width = (array_key_exists('width',$atts)) ? intval($atts['width']) : 200;
	$height = (array_key_exists('height',$atts)) ? intval($atts['height']) : 200;
	$type = (array_key_exists('type',$atts)) ? $atts['type'] : 'ROADMAP';
	$location->miniMap($width, $height, $type);
}
