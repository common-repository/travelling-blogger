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


/**
 * TravellingBloggerWidget Class
 */
class TravellingBloggerWidget extends WP_Widget {
	
	private $mapTypes; 
	
	function TravellingBloggerWidget() {
		TravellingBloggerWidget::__construct();
	}
	
	function __construct() {
		parent::WP_Widget(
			false, 
			$name = 'Travel Blogger Map', 
			$widget_options = array('description' => __("Display a map with the locations of your posts.", TRAVELLING_BLOGGER))
		);
		$this->mapTypes = array(
			'ROADMAP' => __('Road map', TRAVELLING_BLOGGER),
			'SATELLITE' => __('Satellite images', TRAVELLING_BLOGGER),
			'HYBRID' => __('Satellite/Road hybrid', TRAVELLING_BLOGGER),
			'TERRAIN' => __('Terrain and vegetation', TRAVELLING_BLOGGER)
		);
	}

	/** @see WP_Widget::widget */
	function widget($args, $instance) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		?>
			  <?php echo $before_widget; ?>
				  <?php if ( $title )
						echo $before_title . $title . $after_title; ?>
					<div class="travelling_blogger_map" style="height: <?php 
						$height = intval($instance['height']);
						echo ($height>0) ? $height : 100;
					?>px">
						<div class="travelling_blogger_map_cat" ><?php echo ($instance['category'])?$instance['category']:''; ?></div>
						<div class="travelling_blogger_map_marker" ><?php echo $instance['marker'] ?></div>
						<div class="travelling_blogger_map_type" ><?php echo (array_key_exists($instance['mapType'], $this->mapTypes)) ? $instance['mapType'] : 'ROADMAP'; ?></div>
					</div>
				  <?php echo $after_widget; ?>
		<?php
	}

	/** @see WP_Widget::update */
	function update($new_instance, $old_instance) {
		$instance = wp_parse_args(
			array(
				'category' => intval($new_instance['category']), 
				'title' => strip_tags($new_instance['title']),
				'marker' => strip_tags($new_instance['marker']),
				'mapType' => (array_key_exists($new_instance['mapType'], $this->mapTypes)) ? $new_instance['mapType'] : 'ROADMAP',
				'height' => intval($new_instance['height']),
			), 
			(array) $old_instance
		);
		
		return $instance;
		//return $new_instance;
	}

	/** @see WP_Widget::form */
	function form($instance) {
		$title = esc_attr($instance['title']);
		
		$cats = get_terms( 'category');
		?>
		 <p>
		  <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', TRAVELLING_BLOGGER); ?></label> 
		  <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Select Category', TRAVELLING_BLOGGER); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
		<option value=""><?php _e('All Categories', TRAVELLING_BLOGGER); ?></option>
		<?php
		foreach ( $cats as $cat ) {
			echo '<option value="' . intval($cat->term_id) . '"'
				. ( $cat->term_id == $instance['category'] ? ' selected="selected"' : '' )
				. '>' . $cat->name . "</option>\n";
		}
		?>
		</select></p>
		<label for="<?php echo $this->get_field_id('mapType'); ?>"><?php _e('Select Map Type', TRAVELLING_BLOGGER); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id('mapType'); ?>" name="<?php echo $this->get_field_name('mapType'); ?>">
		<?php
		foreach ( $this->mapTypes as $key => $label ) {
			echo '<option value="' . $key . '"'
				. ( $key == $instance['mapType'] ? ' selected="selected"' : '' )
				. '>' . $label . "</option>\n";
		}
		?>
		</select></p>
		<p>
		  <label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Map Height (px)', TRAVELLING_BLOGGER); ?></label> 
		  <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="number" value="<?php 
			$height = intval($instance['height']);
			echo ($height>0) ? $height : 100; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('Marker'); ?>" ><?php _e('Select Marker image', TRAVELLING_BLOGGER); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id('marker'); ?>" name="<?php echo $this->get_field_name('marker'); ?>">
		<option value=""><?php _e('Default Google Marker', TRAVELLING_BLOGGER); ?></option>
		<?php
			
		if ($handle = opendir(ABSPATH . 'wp-content/plugins/travelling-blogger/markers/')) {
			while (false !== ($file = readdir($handle))) {
				if (preg_match('/^(.*)\.png$/', $file)) {
					$file = str_replace('.png', '', $file);
					echo '<option value="' . $file . '"'
						. ( $file == $instance['marker'] ? ' selected="selected"' : '' )
						. '>' . $file . "</option>\n";
				}
			}

			closedir($handle);
		}
		?>
		</select>
		<?php _e('To use your own marker, save it in PNG format and put it in the "markers" folder.', TRAVELLING_BLOGGER); ?>
		</p>
		<?php
	}

}

add_action('widgets_init', create_function('', 'return register_widget("TravellingBloggerWidget");'));