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

function travelling_blogger_set_coord($post_id, $meta_key, $meta_value) {
	//If updating some other meta value, do nothing
	if ($meta_key != LOCATION_META) return;	

	//Don't update Lat/Long if the location hasn't change
	if (
		$meta_value == get_post_meta($post_id, $meta_key, true)
		&& get_post_meta($post_id, COORDS_META, true)) {
		return;
	}

	//Get the LngLat for the location
	$coords = travelling_blogger_geocode($meta_value);
	if ($coords) {
		update_post_meta($post_id, COORDS_META, $coords[0] . ',' .$coords[1]);
	} else {
		update_post_meta($post_id, COORDS_META, GEOCODE_ERROR);
	}
}


/**
  * This function uses Google Geocoding service to match the location variable to a specific Latitude and Longitude.
  * It's based on an article from Tom Manshreck of the Google Geo Team: http://code.google.com/apis/maps/articles/phpsqlgeocode.html
  */
function travelling_blogger_geocode($location) {

	// Initialize delay in geocode speed
	$delay = 0;
	$base_url = 'http://maps.google.com/maps/geo?output=xml'; //Key is no longuer required

	// Iterate through the rows, geocoding each address
	$geocode_pending = true;

	while ($geocode_pending) {
		$id = $row["id"];
		$request_url = $base_url . "&q=" . urlencode($location);
		$xml = simplexml_load_file($request_url) or die("url not loading");

		$status = $xml->Response->Status->code;
		if (strcmp($status, "200") == 0) {
			// Successful geocode
			$geocode_pending = false;
			$coordinates = $xml->Response->Placemark->Point->coordinates;
			$coordinatesSplit = split(",", $coordinates);
			// Format: Longitude, Latitude, Altitude
			return $coordinatesSplit;
		} else if (strcmp($status, "620") == 0) {
			// sent geocodes too fast
			$delay += 100000;
		} else {
			// failure to geocode
			return false;
		}
		usleep($delay);
	}
}