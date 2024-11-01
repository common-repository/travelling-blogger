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

add_action('wp_ajax_nopriv_travelling_blogger_kml', 'travelling_blogger_kml');
add_action('wp_ajax_travelling_blogger_kml', 'travelling_blogger_kml');

//http://display-kml.appspot.com/
//http://codex.wordpress.org/AJAX_in_Plugins

function travelling_blogger_kml() {
	global $wpdb;
	
	if ($_GET['debug']) 
		header('Content-Type: text/plain');
	else 
		header('Content-Type: application/kml+xml');
	
	echo '<?xml version="1.0" encoding="UTF-8"?>'
?>
<kml xmlns="http://www.opengis.net/kml/2.2">
<Document>
	<Style id="normalPlacemark">
<?php 
	travelling_blogger_placemark_style();
?>
	</Style>
	
<?php 
	travelling_blogger_placemark(); 
?>
</Document>
</kml>
<?php
	die();
}

function travelling_blogger_placemark() {

	if (isset($_GET['cat'])) {
		$cat = intval($_GET['cat']);
	} else {
		$cat = -1;
	}
	
	if (isset($_GET['tag'])) {
		$tag = intval($_GET['tag']);
	} else {
		$tag = -1;
	}
	
	if (isset($_GET['year'])) {
		$year = intval($_GET['year']);
	} else {
		$year = -1;
	}
	
	$locs = TravelBloggerLocation::getLocations();
	
	foreach ($locs as $loc) {
		$posts = $loc->getPosts($cat, $tag, $year);
		if (sizeof($posts)>0) {
			travelling_blogger_placemark_start($loc);
			foreach ($posts as $post) {
				echo '<li>';
				echo '<a href="' . get_permalink( $post->ID ) . '" target="_self">' . $post->post_title . '</a>';
				echo '</li>';
			}
			travelling_blogger_placemark_end($loc);
		}
	}
}

function travelling_blogger_placemark_start($loc) {
	echo "\t<Placemark id='$loc->id'>\n";
	echo "\t\t<name>$loc->name</name>\n";
	echo "\t\t<styleUrl>#normalPlacemark</styleUrl>\n";
	echo "\t\t<description><![CDATA[\n";
	echo '<ul>';
}

function travelling_blogger_placemark_end($loc) {
	echo '</ul>';
	echo "\n]]>\n\t\t</description>\n";
	echo "\t\t<Point>\n";
	echo "\t\t\t<coordinates>" . $loc->coords[0] . ',' . $loc->coords[1] . "</coordinates>\n";
	echo "\t\t</Point>\n";
	echo "\t</Placemark>\n";
}

function travelling_blogger_placemark_style() {
	if (array_key_exists(MARKER_IMG, $_GET) && $_GET[MARKER_IMG]) { ?>
		<IconStyle>
			<Icon>
				<href><?php echo plugins_url( 'travelling-blogger/markers/' . $_GET[MARKER_IMG] . '.png' , '' ) ?></href>
			</Icon>
		</IconStyle>
<?php }
}
