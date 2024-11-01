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


/* Handle display of Maps */
wp_enqueue_script( 'google_map_api_v3', 'http://maps.googleapis.com/maps/api/js?sensor=false' );
wp_enqueue_script( 'travelling_blogger', plugin_dir_url( __FILE__ ) . 'showMap.js', array( 'jquery' ) );
wp_localize_script( 'travelling_blogger', 'travelling_blogger', array( 
	'kmlurl' => admin_url( 'admin-ajax.php' ),
	'nzkml' => plugin_dir_url( __FILE__ ) . 'nz.kml',
	'nzimg' => plugin_dir_url( __FILE__ ),
	'edit_label' => __('Edit'),
	'delete_label' => __('Delete'),
) );
wp_enqueue_style( 'travelling_blogger_css', plugin_dir_url( __FILE__ ) . 'style.css' );