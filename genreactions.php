<?php
/**
Plugin Name:    GenReactions
Plugin URI: https://www.genupdates.com/genreactions
Description:    GenReactions is an innovative plugin of its kind. It allows your visitors to react on each and every post. They can express their feelings just then "like" or "comment". I'm noob guy but if you like my work then you can support me. I'll hire some proffesional developers to make this plugin better by your help.
Author:         Rahul
Version:        1.0
Author URI:     https://www.facebook.com/bloggerstrange
Text Domain:    gen
Domain Path:    /languages/
Donate link: https://www.paypal.me/bloggerstrange
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.

@package whats-your-reaction
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Return the plugin directory base path
 *
 * @return string
 */
function gen_get_plugin_dir() {
	return plugin_dir_path( __FILE__ );
}

/**
 * Return the plugin directory url
 *
 * @return string
 */
function gen_get_plugin_url() {
	return trailingslashit( plugin_dir_url( __FILE__ ) );
}

/**
 * Return the plugin basename
 *
 * @return string
 */
function gen_get_plugin_basename() {
	return plugin_basename( __FILE__ );
}

/**
 * Return the plugin version
 *
 * @return string
 */
function gen_get_plugin_version() {
	$version = false;
	$data = get_plugin_data( __FILE__ );

	if ( ! empty( $data['Version'] ) ) {
		$version = $data['Version'];
	}

	return $version;
}

require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/functions.php' );
require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/ajax.php' );
require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/hooks.php' );
require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/shortcodes.php' );

if ( is_admin() ) {
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/admin/functions.php' );
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/admin/hooks.php' );
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/admin/metaboxes/fake-reactions-metabox.php' );
}

// Init.
register_activation_hook( plugin_basename( __FILE__ ), 'gen_activate' );
register_deactivation_hook( plugin_basename( __FILE__ ), 'gen_deactivate' );
register_uninstall_hook( plugin_basename( __FILE__ ), 'gen_uninstall' );