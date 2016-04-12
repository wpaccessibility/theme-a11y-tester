<?php
/*
Plugin Name: Theme A11y Tester
Plugin URI: http://themetest.joedolson.com
Description: Upload & automatically run accessibility tests on WordPress themes.
Author: WP Accessibility Team
Author URI: http://make.wordpress.org/accessibility/
Version: 0.1.0
*/
/*  
	This program is open source software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


register_activation_hook( __FILE__, 'di_activation' );
/**
 * On plug-in activation create upload page if necessary and set option with page ID.
 */
function di_activation() {
	$upload = get_option( 'tat_upload_page' );
	if ( !$upload ) {
		$page = tat_setup_page( 'Upload Theme' );
		update_option( 'tat_upload_page', $page );
	}
	
	flush_rewrite_rules();	
}

/**
 * Setup search pages on activation if pages of that name don't already exist.
 *
 * @param $slug
 *
 * @return int|WP_Error
 */
function di_setup_page( $slug ) {
	global $current_user;
	$current_user = wp_get_current_user();
	if ( ! is_page( $slug ) ) {
		$page      = array(
			'post_title'  => ucfirst( $slug ),
			'post_status' => 'publish',
			'post_type'   => 'page',
			'post_author' => $current_user->ID,
			'ping_status' => 'closed'
		);
		$post_ID   = wp_insert_post( $page );
		$post_slug = wp_unique_post_slug( $slug, $post_ID, 'publish', 'page', 0 );
		wp_update_post( array( 'ID' => $post_ID, 'post_name' => $post_slug ) );
	} else {
		$post    = get_page_by_path( $slug );
		$post_ID = $post->ID;
	}

	return $post_ID;
}

/**
 * Document this once it's more developed. 
 */
add_filter( 'the_content', 'tat_upload_form' )
function tat_upload_form( $content ) {
	// can I use install_themes_upload() here?
	return '<form><label for="tat_theme">' . __( 'Upload Theme', 'theme-a11y-tester' ) . '</label> <input type="file" id="tat_theme" name="tat_theme" /></form>';
}


/** 
 * Notes for future development:
 *
 * Core functions:
 * 	- install_blog( int $blod_id, string $blog_title = '' )
 *  - install_themes_upload()
 *  - Theme_Upgrade::install( string $package, array $args = array() ) (accepts local path or URI)