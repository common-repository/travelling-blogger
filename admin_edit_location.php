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

function travelling_blogger_edit_location() {
	$location = TravelBloggerLocation::getLocation($_REQUEST['location']);
	?>
	
<div class="wrap nosubsub">
	<div id="icon-edit" class="icon32"><br/></div><h2><?php _e('Edit Location',TRAVELLING_BLOGGER); ?></h2>
	
	<form name="editlocation" id="editlocation" method="post" action="<?php echo admin_url('edit.php?page=travelling-blogger'); ?>" class="validate">
		<input type="hidden" name="action" value="editlocation" />
		<input type="hidden" name="location" value="<?php echo $location->id; ?>" />
		<input type="hidden" name="page" value="travelling-blogger" />
		<input type="hidden" id="lng" name="lng" value="<?php echo $location->getLng(); ?>" />
		<input type="hidden" id="lat" name="lat" value="<?php echo $location->getLat(); ?>" />
		<?php wp_nonce_field( 'travelling_blogger_editlocation', '_wp_nonce' ); ?>
		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row" valign="top">
						<label for="name"><?php _e('Location Name', TRAVELLING_BLOGGER); ?></label>
					</th>
					<td>
						<input name="name" id="name" type="text" value="<?php echo $location->name; ?>" size="40" aria-required="true" />						
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label><?php _e('Coordinates', TRAVELLING_BLOGGER); ?></label>
					</th>
					<td>
						<div id="mapLocationEdit" style="height: 400px"></div>
						<button id="geocode" class="button"><?php _e('GeoCode Location Name', TRAVELLING_BLOGGER); ?></button>
						<span class="error" style="display:none;"><?php _e('Location could not be geocoded.', TRAVELLING_BLOGGER); ?></span>
					</td>
				</tr>				
			</tbody>
		</table>

	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button-primary" value="Update" />
	</p>
	
	</form>
	
</div>
	
	
	<?php
}