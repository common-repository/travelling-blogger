<?php
/*
Plugin Name: Custom List Table Example
Plugin URI: http://www.mattvanandel.com/
Description: A highly documented plugin that demonstrates how to create custom List Tables using official WordPress APIs.
Version: 1.1
Author: Matt Van Andel
Author URI: http://www.mattvanandel.com
License: GPL2
*/
/*  Copyright 2011  Matthew Van Andel  (email : matt@mattvanandel.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



/* == NOTICE ===================================================================
 * Please do not alter this file. Instead: make a copy of the entire plugin, 
 * rename it, and work inside the copy. If you modify this plugin directly and 
 * an update is released, your changes will be lost!
 * ========================================================================== */



/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary.
 */
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}




class Location_List_Table extends WP_List_Table {
	
	private $message = false;

	function __construct(){
		global $status, $page;
		//Set parent defaults
		parent::__construct( array(
			'singular'  => __('location',TRAVELLING_BLOGGER),	 //singular name of the listed records
			'plural'	=> __('locations',TRAVELLING_BLOGGER),	//plural name of the listed records
			'ajax'	  => false		//does this table support ajax?
		) );
		
	}
	
	
	function column_default($item, $column_name){
		switch($column_name){
			default:
				return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}

	function column_coords($item) {
		return $item->coords[0] . ',' . $item->coords[1];
	}
	
	function column_post_count($item) {
		return $item->post_count;
	}
	
	function column_name($item){
		
		//Build row actions
		$actions = array(
			'edit' => sprintf('<a href="?page=%s&action=%s&location=%s">Edit</a>',$_REQUEST['page'],'edit',$item->id),
			'delete' => sprintf(
				'<a href="?page=%s&action=%s&location=%s&_wp_nonce=%s&paged=%d">Delete</a>',
				$_REQUEST['page'],
				'delete',
				$item->id, 
				wp_create_nonce('travelling_blogger_delete_location'), 
				$this->get_pagenum()
			),
		);
		
		//Return the title contents
		return sprintf('<strong><a href="?page=%1$s&action=edit&location=%2$d">%3$s</a></strong> %4$s',
			/*$1%s*/ $_REQUEST['page'],
			/*$2%s*/ $item->id,
			/*$3%s*/ $item->name,
			/*$4%s*/ $this->row_actions($actions)
		);
	}
	
	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ 'location',
			/*$2%s*/ $item->id
		);
	}

	function get_columns(){
		$columns = array(
			'cb'		=> '<input type="checkbox" />', //Render a checkbox instead of text
			'name'		=> __('Locations',TRAVELLING_BLOGGER),
			'coords'	=> __('Coordinates (Longitude,Latitudes)',TRAVELLING_BLOGGER),
			'post_count'=> __('Posts')
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'name'	 => array('name',true),
			'coords'	 => array('coords',true),
			'post_count'	 => array('post_count',true)
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'delete'	=> __('Delete',TRAVELLING_BLOGGER)
		);
		return $actions;
	}

	function process_bulk_action() {
		
		if (!current_user_can('manage_categories'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}		
		
		//Detect when a bulk action is being triggered...
		switch ($this->current_action()) {
			case 'delete':
				if (!array_key_exists('location', $_REQUEST)) {
					return;
				}
				
				if (is_array($_REQUEST['location'])) {
					if ( !wp_verify_nonce( $_REQUEST['_wp_nonce'], 'travelling_blogger_bulkaction') ) {
						die('Wrong nonce');
					}
					$ids2del = $_REQUEST['location'];
				} else {
					if ( !wp_verify_nonce( $_REQUEST['_wp_nonce'], 'travelling_blogger_delete_location' ) ) {
						die('Wrong nonce');
					}
					$ids2del = array($_REQUEST['location']);
				}
				
				if (sizeof($ids2del) == 0) {return;}
				
				foreach ($ids2del as $id) {
					TravelBloggerLocation::delete($id);
				}
				
				
				$this->message = (sizeof($ids2del) > 1) ?
					sprintf(__('%d locations deleted.', TRAVELLING_BLOGGER), sizeof($ids2del)) :
					__('1 location deleted.', TRAVELLING_BLOGGER);
				
				break;
			case 'editlocation':
				$location = TravelBloggerLocation::getLocation($_REQUEST['location']);
				
				if ( !wp_verify_nonce( $_REQUEST['_wp_nonce'], 'travelling_blogger_editlocation') ) {
					die('Wrong nonce');
				}
				
				if (array_key_exists('name', $_REQUEST)) {
					$location->name = $_REQUEST['name'];
				}
				if (array_key_exists('lat', $_REQUEST) && array_key_exists('lat', $_REQUEST)) {				
					$location->coords = array($_REQUEST['lng'],$_REQUEST['lat']);
				}
				
				if ($location->update()) {
					$this->message = __('Location updated successful.',TRAVELLING_BLOGGER);
				} else {
					$this->message = __('Location updated failure.',TRAVELLING_BLOGGER);
				}
				break; 
			case 'update-location-page':
				wp_verify_nonce( 'travelling_blogger_update_location_page', '_wp_nonce' );
				$page_id = intval($_REQUEST['location-page']);
				if (get_page( $page_id ) != null) {
					update_option('travelling_blogger_location_page_id', $page_id);
				} else {
					update_option('travelling_blogger_location_page_id', '');
				}
				$this->message = __('Location page updated.',TRAVELLING_BLOGGER);
				break;
		}
	}

	function prepare_items() {

		//Process action
		$this->process_bulk_action();

		//Set number of items per page
		$per_page = 10;

		//Set header row
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		//Get Locations
		$possibleSortOrder = array('name', 'coords', 'id', 'post_count');
		$orderby = (!empty($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], $possibleSortOrder)) ? $_REQUEST['orderby'] : 'location'; //If no sort, default to title
		$order = (!empty($_REQUEST['order']) && $_REQUEST['order'] == 'desc') ? 'desc' : 'asc'; //If no order, default to asc
		$data = TravelBloggerLocation::getLocations($orderby, $order);

		$total_items = count($data);

		//Page
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'	=> $per_page,
			'total_pages' => ceil($total_items/$per_page)
		));
		
		$current_page = $this->get_pagenum();
		$data = array_slice($data, ($current_page-1)*$per_page, $per_page);

		$this->items = $data;

		
	}
	
	function getMessage() {
		return $this->message;
	}
	
}

function travelling_blogger_display_location_table() {
	//Create an instance of our package class...
	$locationListTable = new Location_List_Table();
	//Fetch, prepare, sort, and filter our data...
	$locationListTable->prepare_items();
?>
<div class="wrap nosubsub">
	<div id="icon-edit" class="icon32"><br/></div><h2><?php _e('Locations',TRAVELLING_BLOGGER); ?></h2>
	
	<?php if ($locationListTable->getMessage()) { ?>	
	<div id="message" class="updated below-h2">
		<p><?php echo $locationListTable->getMessage(); ?></p>
	</div>
	<?php } ?>
	
	<div id="col-container">
		<div id="col-right">
			<div class="col-wrap">
				<form id="location-filter" method="POST">
					<?php wp_nonce_field( 'travelling_blogger_bulkaction', '_wp_nonce' ); ?>
					<!-- For plugins, we also need to ensure that the form posts back to our current page -->
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
					<!-- Now we can render the completed list table -->
					<?php $locationListTable->display() ?>
				</form>
			</div>
		</div>
		<div id="col-left">
			<div class="col-wrap">
				<div class="form-wrap">
					<h3><?php _e('Add New Location',TRAVELLING_BLOGGER); ?></h3>
					<form id="addlocation" method="POST" class="validate" action="edit.php?page=travelling-blogger">
						<input type="hidden" name="action" value="add-location">
						<?php wp_nonce_field( 'travelling_blogger_add_location', '_wp_nonce' ); ?>
						<div class="form-field form-required">
							<label for="location-name"><?php _e('Location',TRAVELLING_BLOGGER); ?></label>
							<input name="location-name" id="location-name" type="text" value="" size="40" aria-required="true">
							<span style="display:none" class="error" id="no_location_provided"><?php _e('No location was provided',TRAVELLING_BLOGGER) ?></span>
							<span style="display:none" class="error" id="no_geocode"><?php _e('Location could not be geocoded',TRAVELLING_BLOGGER) ?></span>
						</div>
						<p class="submit">
							<input type="submit" name="submit" id="submit" class="button" value="<?php _e('Add New Location',TRAVELLING_BLOGGER); ?>">
						</p>
					</form>
					
					<?php if (current_user_can('activate_plugins')) { ?>
					
					<h3><?php _e('Location Page',TRAVELLING_BLOGGER); ?></h3>
					<form id="update_location_page" method="POST" class="validate" action="edit.php?page=travelling-blogger">
						<input type="hidden" name="action" value="update-location-page">
						<?php wp_nonce_field( 'travelling_blogger_update_location_page', 'addlocation_wp_nonce' ); ?>
						<div class="form-field form-required">
							<label for="location-page"><?php _e('Location Page',TRAVELLING_BLOGGER); ?></label>
							<select id="location-page" name="location-page">
								<option>(<?php _e('Deactivate',TRAVELLING_BLOGGER); ?>)</option>
								<?php

$curr_page = get_option('travelling_blogger_location_page_id');

$pages = get_pages();
$parents = array(0);
foreach ($pages as $page) {
	while ($page->post_parent != end($parents)) {
		array_pop($parents);
	}
	echo '<option value="' . $page->ID.'"';
	if ($curr_page == $page->ID) {echo ' selected="selected" ';}
	echo ' >' . str_repeat('-- ', sizeof($parents)-1) . $page->post_title . '</option>';
	$parents[] = $page->ID;
}
								?>
							</select>
						</div>
						<p><?php _e('Specify which page you want to use to display posts by location. Make sur to include the following shortcode in the page\'s content.',TRAVELLING_BLOGGER); ?></p>
						<p style="font-family: monospace;">[location_page]</p>
						<p><?php _e('You can use the "displayTitle" attribute to specify if a title should be displayed. You can choose which tag will be use to display the title so it integrates gracefully with your template.',TRAVELLING_BLOGGER); ?></p>
						<p><span style="font-family: monospace;">[location_page titleDisplay="none"]</span><br/><?php _e('No title will be displayed. (Default)',TRAVELLING_BLOGGER); ?></p>
						<p><span style="font-family: monospace;">[location_page titleDisplay="p"]</span><br/><?php _e('The title will be displayed in a P tag.',TRAVELLING_BLOGGER); ?></p>
						<p><span style="font-family: monospace;">[location_page titleDisplay="h1"]</span><br/><?php _e('The title will be displayed in a H1 tag.',TRAVELLING_BLOGGER); ?></p>
						<p><span style="font-family: monospace;">[location_page titleDisplay="h2"]</span><br/><?php _e('The title will be displayed in a H2 tag.',TRAVELLING_BLOGGER); ?></p>
						<p><span style="font-family: monospace;">[location_page titleDisplay="h3"]</span><br/><?php _e('The title will be displayed in a H3 tag.',TRAVELLING_BLOGGER); ?></p>
						<p class="submit">
							<input type="submit" name="submit" id="submit" class="button" value="<?php _e('Update Location Page',TRAVELLING_BLOGGER); ?>" />
						</p>
					</form>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
}
