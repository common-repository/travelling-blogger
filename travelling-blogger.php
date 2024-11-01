<?php
/*
Plugin Name: Travelling Blogger
Plugin URI: http://maximerainville.com/
Description: Travelling Blogger allows you to mark the location of your posts and display them on a Google map.
Version: 1.0
Author: Maxime Rainville
Author URI: http://maximerainville.com/
License: GPLv2 or later
*/

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

define('LOCATION_META', 'location');
define('TRAVELLING_BLOGGER', 'travelling-blogger');
define('COORDS_META', 'lng_lat');
define('GEOCODE_ERROR', 'Could not geocode location.');
define('MARKER_IMG', 'marker');

require_once('plugin_init.php');
require_once('location_class.php');
require_once('widget.php');
require_once('display.php');
require_once('geocoder.php');
require_once('kml.php');
require_once('location_page.php');
require_once('ajax.php');
require_once('admin_panel.php');
require_once('post_edit.php');
require_once('feed.php');
require_once('miniMap.php');


register_activation_hook(__FILE__,'travelling_blogger_install_handler');

