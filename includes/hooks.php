<?php
/**
 * Hooks
 *
 * @package whats-your-reaction
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

// Init.
add_action( 'init', 'gen_register_taxonomy', 0 );

// Post.
add_filter( 'the_content', 'gen_load_post_voting_box' );

// Ajax.
add_action( 'wp_ajax_gen_vote_post',        'gen_ajax_vote_post' );
add_action( 'wp_ajax_nopriv_gen_vote_post',	'gen_ajax_vote_post' );

// Assets.
add_action( 'wp_enqueue_scripts', 'gen_enqueue_styles' );
add_action( 'wp_enqueue_scripts', 'gen_enqueue_scripts' );

// Fake reactions.
//add_filter( 'gen_post_votes', 'gen_fake_reaction_count', 11, 2 );
