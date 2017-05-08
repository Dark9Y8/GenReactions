<?php
/**
 * Shortcodes Functions
 *
 * @package whats-your-reaction
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_shortcode( 'gen_voting_box', 'gen_voting_box_shortcode' );

/**
 * Voting box shortcode
 *
 * @return string
 */
function gen_voting_box_shortcode() {
	ob_start();
	gen_get_template_part( 'voting-box' );
	$out = ob_get_clean();

	return $out;
}