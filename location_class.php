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

class TravelBloggerLocation {
	
	var $id = 0;
	var $name = '';
	var $coords = array(0,0); //Longitude, Latitude
	var $post_count = 0; //Longitude, Latitude
	
	function TravelBloggerLocation($id, $name, $coords, $post_count) {
		$this->__construct($id, $name, $coords, $post_count);
	}
	
	function __construct($id, $name, $coords, $post_count){
		$this->id = $id;
		$this->name = $name;
		$this->coords = $coords;
		$this->post_count = $post_count;
	}
	
	static function getLocations($sort = 'name', $sortOrder='asc') {
		global $wpdb;
		
		$sortOrder = ($sortOrder == 'desc') ? $sortOrder : 'asc';
		
		$sort = (in_array($sort,array('name', 'id', 'coords', 'post_count'))) ? $sort : 'name';
		$sort = ($sort == 'coords') ? 'lng ' . $sortOrder . ' lat ' . $sortOrder : $sort . ' ' . $sortOrder;
		$sql = 'SELECT loc.id, loc.name, loc.lng, loc.lat, count(post.post_id) AS post_count FROM ' . $wpdb->prefix . 'travelling_blogger_location AS loc
			LEFT JOIN ' . $wpdb->prefix . 'travelling_blogger_post AS post ON loc.id = post.location_id 
			GROUP BY loc.id, loc.name, loc.lng, loc.lat
			ORDER BY ' . $sort;
		
		$data = $wpdb->get_results($sql);
		
		$locs = array();
		
		foreach ($data as $row) {
			$locs[] = new TravelBloggerLocation($row->id, $row->name, array($row->lng, $row->lat), $row->post_count);
		}
		
		return $locs;
	}
	
	static function getLocation($id) {
		global $wpdb;
		
		$id = intval($id);
		
		$sql = $wpdb->prepare('SELECT loc.id, loc.name, loc.lng, loc.lat, count(post.post_id) AS post_count FROM ' . 
			$wpdb->prefix . 'travelling_blogger_location AS loc
			LEFT JOIN ' . $wpdb->prefix . 'travelling_blogger_post AS post ON loc.id = post.location_id 
			WHERE loc.id = %d GROUP BY loc.id, loc.name, loc.lng, loc.lat', $id);
			
		$data = $wpdb->get_results($sql);
		
		if ($data) {
			$row = $data[0];
			return new TravelBloggerLocation($row->id, $row->name, array($row->lng, $row->lat), $row->post_count);
		} else {
			return false;
		}
	}
	
	static function newLocation($name) {
		if (!current_user_can('manage_categories')) {
			die('You\'re not allowed to add location to the system.');
		}
		global $wpdb;
		require_once('geocoder.php');
		$coords = travelling_blogger_geocode($name);
		if (!$coords) {
			return false;
		} else {
			$wpdb->insert(
				$wpdb->prefix ."travelling_blogger_location", 
				array(
					'name'=>$name,
					'lng'=>$coords[0],
					'lat'=>$coords[1]
				), array('%s','%f','%f')
			);
			$id = $wpdb->insert_id;
			return new TravelBloggerLocation($id, $name, $coords, 0);
		}
	}
	
	static function setPostLocation($postId, $locationId) {
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}
		global $wpdb;
		
		$sqlDel = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'travelling_blogger_post WHERE post_id = %d', $postId);
		
		$wpdb->query($sqlDel);
		
		if ($locationId && $locationId != -1) {
			$wpdb->insert(
				$wpdb->prefix ."travelling_blogger_post", 
				array(
					'post_id'=>$postId,
					'location_id'=>$locationId
				), array('%d','%d')
			);
		}
	}

	static function getLocationByPost($postId=false) {
		global $wpdb;
		if (!$postId) {
			$postId=get_the_ID();
		}
		$sql = $wpdb->prepare(
			'SELECT * FROM ' . 
			$wpdb->prefix . 'travelling_blogger_location JOIN ' . $wpdb->prefix . 'travelling_blogger_post 
			ON location_id = id  WHERE post_id=%d',$postId
		);
		
		$data = $wpdb->get_results($sql);
		
		if ($data) {
			return new TravelBloggerLocation($data[0]->id, $data[0]->name, array($data[0]->lng, $data[0]->lat), 0);
		} else {
			return false;
		}
	}

	function getPosts($catId=-1, $tag=-1, $year=-1) {
		global $wpdb;
		$sql = (!$catId || $catId == -1 )
			? $wpdb->prepare('SELECT post_id FROM ' . $wpdb->prefix . 'travelling_blogger_post WHERE location_id=%d',$this->id)
			: $wpdb->prepare(
				'SELECT post_id FROM ' . $wpdb->prefix . 'travelling_blogger_post 
				JOIN ' . $wpdb->term_relationships . ' ON object_id = post_id 
				WHERE location_id=%d AND term_taxonomy_id=%d', 
				$this->id, $catId
			);
		
		$data = $wpdb->get_results($sql);
		
		$postIDs = array();
		foreach ($data as $row) {
			$postIDs[] = $row->post_id;
		}
		
		if (sizeof($postIDs)  == 0) {
			return array();
		}
		
		$allPosts = get_posts(array('include' => $postIDs));
		$postArr = array();
		foreach ($allPosts as $post) {
			if ($tag != -1 && !has_tag($tag, $post)) {
				continue;
			}
			if ($year != -1 && get_the_time('Y',$post) != $year) {
				continue;
			}
			$postArr[] = $post;
		}
		
		return $postArr;
	}

	static function delete($id) {
		if (!current_user_can('manage_categories')) {
			die('You\'re not allowed to add location to the system.');
		}
		global $wpdb;
		$sql = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix .'travelling_blogger_location WHERE id = %d;', $id);
		$wpdb->query($sql);
		$sql = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix .'travelling_blogger_post WHERE location_id = %d;', $id);
		$wpdb->query($sql);
	}
	
	function getLngLat() {
		return $this->coords[0] . ',' . $this->coords[1];
	}
	
	function getLng() {
		return $this->coords[0];
	}
	
	function getLat() {
		return $this->coords[1];
	}
	
	function getLatLng() {
		return $this->coords[1] . ',' . $this->coords[0];
	}
	
	function update() {
		global $wpdb;
		if (trim($this->name) && is_numeric($this->coords[0]) && is_numeric($this->coords[1])) {
			return $wpdb->update(
				$wpdb->prefix .'travelling_blogger_location',
				array(
					'name' => stripcslashes($this->name),
					'lng' => $this->coords[0],
					'lat' => $this->coords[1],
				),
				array('id' => $this->id),
				array('%s','%f','%f')
			);
		} else {
			return false;
		}
	}
	
	static function getTags() {
		global $wpdb;
		$sql = "
SELECT 
	terms.term_id AS id, name, slug, COUNT(term_relationships.term_taxonomy_id) AS `count`
FROM 
	" . $wpdb->prefix . "terms AS terms
	JOIN " . $wpdb->prefix . "term_taxonomy AS term_taxonomy 
		ON terms.term_id = term_taxonomy.term_id
	JOIN " . $wpdb->prefix . "term_relationships AS term_relationships
		ON term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id
	JOIN " . $wpdb->prefix . "travelling_blogger_post AS travelling_blogger_post
		ON travelling_blogger_post.post_id = term_relationships.object_id
	JOIN " . $wpdb->prefix . "posts AS posts
		ON posts.id = travelling_blogger_post.post_id
WHERE 
	taxonomy = 'post_tag' AND post_status = 'publish'
GROUP BY
	terms.term_id, name, slug
ORDER BY COUNT(term_relationships.term_taxonomy_id) desc
LIMIT 50";
		$tags = $wpdb->get_results($sql);
		return $tags;
	}
	
	static function getYears() {
		global $wpdb;
		$sql = "
SELECT 
	YEAR(post_date) AS `year`
FROM 
	" . $wpdb->prefix . "travelling_blogger_post AS travelling_blogger_post
	JOIN " . $wpdb->prefix . "posts AS posts
		ON posts.id = travelling_blogger_post.post_id
WHERE 
	post_status = 'publish'
GROUP BY
	`year`
ORDER BY `year` DESC
LIMIT 10";
		$years = array();
		foreach ($wpdb->get_results($sql) as $data) {
			$years[] = intval($data->year);
		}
		return $years;
	}

	function permalink() {
		$id = get_option('travelling_blogger_location_page_id');
		if (!$id || get_page( $page_id ) == null) {
			return false;
		} else {
			$permalink = get_permalink( $id );
		
			$query = array();
			if ($locationId) {$query[] = "locationId=$this->id";}
		
			if (!preg_match('/\?/',$permalink)) {
				$permalink .= '?';
			} else {
				$permalink .= '&';
			}
		
			$permalink .= implode('&', $query);
		
			return $permalink;
		}
	}

	function miniMap($width = 200, $height = 200, $type='ROADMAP') {
		echo $this->get_miniMap($width, $height, $type);
	}

	function get_miniMap($width = 200, $height = 200, $type='ROADMAP') {
		$miniMap = '<div class="travelling_blogger_minimap" style="height: ' . $height .'px; width: ' . $width . 'px;">';
		$miniMap .= '<div class="travelling_blogger_map_type" >';
		$miniMap .= strtoupper($type);
		$miniMap .= '</div>';
		$miniMap .= '<div class="travelling_blogger_lng" >';
		$miniMap .= $this->getLng();
		$miniMap .= '</div>';
		$miniMap .= '<div class="travelling_blogger_lat" >';
		$miniMap .= $this->getLat();
		$miniMap .= '</div>';
		$miniMap .= '</div>';
		return $miniMap;
	}

}
