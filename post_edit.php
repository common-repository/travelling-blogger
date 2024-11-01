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

// Display Meta box
add_action( 'add_meta_boxes', 'travelling_blogger_add_meta_boxes_handler' );

function travelling_blogger_add_meta_boxes_handler() {
	wp_enqueue_script( 'travelling_blogger_metabox', plugin_dir_url( __FILE__ ) . '/metabox.js', array( 'jquery' ) );
	add_meta_box( 'travelling_blogger_meta_box', __('Location', TRAVELLING_BLOGGER), 'travelling_blogger_meta_box_handler', 'post', 'side', 'default', null );
}

function travelling_blogger_meta_box_handler($post) {
	wp_nonce_field( plugin_basename(dirname(__FILE__)), 'travelling_blogger_savepost' );
	
	$locs = TravelBloggerLocation::getLocations();
	$curr_loc = TravelBloggerLocation::getLocationByPost($post->ID);
	$curr_loc_id = ($curr_loc) ? $curr_loc->id : -1 ;
	
	?>
	<label for="travelling_blogger_cur_location"><?php _e('Location', TRAVELLING_BLOGGER) ?></label>
	<select name="travelling_blogger_cur_location" id="travelling_blogger_cur_location"><option value="-1"></option>
<?php
	foreach ($locs as $loc) {
		$selected = ($loc->id == $curr_loc_id) ? ' selected="selected"' : '' ;
		echo "<option value='$loc->id' $selected>$loc->name</option>";
	}
	?>
	</select>
	<?php
	
	if (current_user_can('manage_categories'))  {
		?>
		<div class="wp-hidden-children">
		<h4><a id="location-add-toggle" href="#location-add" class="hide-if-no-js" tabindex="3">+ <?php _e('Add New Location',TRAVELLING_BLOGGER) ?></a></h4>
		<p id="location-add" class="location-add wp-hidden-child">
			<?php wp_nonce_field( 'travelling_blogger_add_location', 'travelling_blogger_wp_nonce' ); ?>
			<label class="screen-reader-text" for="newlocation"><?php _e('Add New Location',TRAVELLING_BLOGGER) ?></label>
			<input type="text" name="newlocation" id="newlocation" class="form-required" value="" tabindex="3" aria-required="true" style="width:94%">
			<br /><input type="button" id="location-add-submit" class="add:locationchecklist:location-add button location-add-sumbit" value="<?php _e('Add New Location',TRAVELLING_BLOGGER) ?>" tabindex="3">
			<span style="display:none" class="error" id="no_location_provided"><?php _e('No location was provided',TRAVELLING_BLOGGER) ?></span>
			<span style="display:none" class="error" id="no_geocode"><?php _e('Location could not be geocoded',TRAVELLING_BLOGGER) ?></span>
			
		</p>
		</div>
		<?php
	}
	
}

add_action( 'save_post', 'travelling_blogger_save_postdata' );
/* When the post is saved, saves our custom data */
function travelling_blogger_save_postdata( $rev_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	return;

	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times

	if ( !wp_verify_nonce( $_POST['travelling_blogger_savepost'], plugin_basename(dirname(__FILE__)) ) )
		return;

  
	// Check permissions
	if ( !current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// OK, we're authenticated: we need to find and save the data
	$locationId = intval($_POST['travelling_blogger_cur_location']);
	$postId = wp_is_post_revision( $rev_id );
	$postId = ($postId) ? $postId : $rev_id;
	TravelBloggerLocation::setPostLocation($postId, $locationId);
}
