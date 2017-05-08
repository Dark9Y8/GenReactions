<?php
/**
 * Vote Ajax Functions
 *
 * @package whats-your-reaction
 * @subpackage Ajax
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Vote ajax handler
 */
function gen_ajax_vote_post() {
	check_ajax_referer( 'gen-vote-post', 'security' );

	// Sanitize post id.
	$post_id = (int) filter_input( INPUT_POST, 'gen_post_id', FILTER_SANITIZE_NUMBER_INT ); // Removes all illegal characters from a number.

	if ( 0 === $post_id ) {
		gen_ajax_response_error( _x( 'Post id not set!', 'ajax internal message', 'gen' ) );
		exit;
	}

	// Sanitize author id.
	$author_id = (int) filter_input( INPUT_POST, 'gen_author_id', FILTER_SANITIZE_NUMBER_INT );

	// Sanitize type.
	$reaction_type = filter_input( INPUT_POST, 'gen_vote_type', FILTER_SANITIZE_STRING );

	if ( ! gen_is_valid_reaction( $reaction_type ) ) {
		gen_ajax_response_error( _x( 'Invalid reaction type!', 'ajax internal message', 'gen' ) );
		exit;
	}

	// User can add only one vote (per reaction type).
	if ( gen_user_voted( $reaction_type, $post_id, $author_id ) ) {
		gen_ajax_response_error( _x( 'User has already voted for that reaction type!', 'ajax internal message', 'gen' ) );
		exit;
	}

	$new_vote = array(
		'post_id'   => $post_id,
		'author_id' => $author_id,
		'type'		=> $reaction_type,
	);

	$post_voting_state = gen_vote_post( $new_vote );

	if ( is_wp_error( $post_voting_state ) ) {
		gen_ajax_response_error( sprintf( _x( 'Failed to vote for post with id %d', 'ajax internal message', 'gen' ), $post_id ), array(
			'error_code'    => esc_html( $post_voting_state->get_error_code() ),
			'error_message' => esc_html( $post_voting_state->get_error_message() ),
		) );
		exit;
	}

	gen_ajax_response_success( _x( 'Vote added successfully.', 'ajax internal message', 'gen' ), array( 'state' => gen_get_post_votes( $post_id ) ) );
	exit;
}

/**
 * Prints ajax response, json encoded
 *
 * @param string $status    Status of the response (success|error).
 * @param string $message   Text message describing response status code.
 * @param array  $args      Response extra arguments.
 *
 * @return void
 */
function gen_ajax_response( $status, $message, $args ) {
	$res = array(
		'status'  => $status,
		'message' => $message,
		'args'    => $args,
	);

	echo wp_json_encode( $res );
}

/**
 * Prints ajax success response, json encoded
 *
 * @param string $message       Text message describing response status code.
 * @param array  $args          Response extra arguments.
 *
 * @return void
 */
function gen_ajax_response_success( $message, $args = array() ) {
	gen_ajax_response( 'success', $message, $args );
}

/**
 * Prints ajax error response, json encoded
 *
 * @param string $message       Text message describing response status code.
 * @param array  $args          Response extra arguments.
 *
 * @return void
 */
function gen_ajax_response_error( $message, $args = array() ) {
	gen_ajax_response( 'error', $message, $args );
}