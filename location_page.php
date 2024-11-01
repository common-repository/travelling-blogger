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

function travelling_blogger_location_page_permalink($locationId = false) {
	$id = get_option('travelling_blogger_location_page_id');
	if (!$id || get_page( $page_id ) == null) {
		return false;
	} else {
		$permalink = get_permalink( $id );
		
		$query = array();
		if ($locationId) {$query[] = "locationId=$locationId";}
		if ($year) {$query[] = "year=$year";}
		if ($tag) {$query[] = "tag=$tag";}
		
		if (!preg_match('/\?/',$permalink)) {
			$permalink .= '?';
		} else {
			$permalink .= '&';
		}
		
		$permalink .= implode('&', $query);
		
		return $permalink;
	}
}

add_shortcode( 'location_page', 'travelling_blogger_location_page_short_code_handler' );

function travelling_blogger_location_page_short_code_handler( $atts ) {
	echo '<div class="travelling_blogger_location_page">';

	$locationId = intval($_GET['locationId']);
	$titleDisplay = ($atts['titledisplay']) ? $atts['titledisplay'] : 'none';
	
	if ($locationId) {
		echo '<div class="travelling_blogger_location_display">';
		global $post;
		
		$loc = TravelBloggerLocation::getLocation($locationId);
		
		$title = sprintf(__('Location: %s',TRAVELLING_BLOGGER), $loc->name);
		switch ($titleDisplay) {
			case 'p': 
				echo '<p>' . $title . '</p>';
				break;
			case 'h1':
				echo '<h1>' . $title . '</h1>';
				break;
			case 'h2':
				echo '<h2>' . $title . '</h2>';
				break;
			case 'h3':
				echo '<h3>' . $title . '</h3>';
				break;
		}
		
		echo '<p>';
		echo '<a href="' . travelling_blogger_location_page_permalink() .'">' . __('Return to location list',TRAVELLING_BLOGGER) . '</a>';
		echo '</p>';
		
		$posts = $loc->getPosts();
		
		// My own mini loop
		$orginalPost = $post;
		foreach ($posts as $p) {
			$post = $p;
			setup_postdata($post);
			get_template_part( 'content', get_post_format() );
		}
		$post = $orginalPost;
		setup_postdata($post);
		echo '</div>';
	} else {
		echo '<div class="travelling_blogger_location_list">';
		
		$title = __('Location list',TRAVELLING_BLOGGER);
		switch ($titleDisplay) {
			case 'p': 
				echo '<p>' . $title . '</p>';
				break;
			case 'h1':
				echo '<h1>' . $title . '</h1>';
				break;
			case 'h2':
				echo '<h2>' . $title . '</h2>';
				break;
			case 'h3':
				echo '<h3>' . $title . '</h3>';
				break;
		}
		
		$locs = TravelBloggerLocation::getLocations();

		$lis = '';
		foreach ($locs as $loc) {
			if ($loc->post_count) {
				$lis .= '<li><a href="' . travelling_blogger_location_page_permalink($loc->id) . '">' . $loc->name . '</a> (' . $loc->post_count . ' posts)</li>';
			}
		}
		
		if (!$lis) {
			_e('They are currently no location available.',TRAVELLING_BLOGGER);
		} else {
			echo '<ul>';
			echo $lis;
			echo '</ul>';
		}
		echo '</div>';
	}
	
	echo '</div>';
}