<?php
/**
 * Fake Votes Metabox
 *
 * @package gen
 * @subpackage Metaboxes
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Register metabox
 *
 * @param string  $post_type    Post type.
 * @param WP_Post $post         Post object.
 */
function gen_add_fake_reactions_metabox( $post_type, $post ) {
	add_meta_box(
		'gen_fake_reactions',
		__( 'Fake Reactions', 'gen' ),
		'gen_fake_reactions_metabox',
		$post_type,
		'normal'
	);

	do_action( 'gen_register_fake_reactions_metabox' );
}

/**
 * Render metabox
 *
 * @param WP_Post $post         Post object.
 */
function gen_fake_reactions_metabox( $post ) {
	// Secure the form with nonce field.
	wp_nonce_field(
		'gen_fake_reactions',
		'gen_fake_reactions_nonce'
	);

	$value = get_post_meta( $post->ID, '_gen_fake_reaction_count', true );
	?>
	<div id="gen-metabox">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label for="_gen_fake_reaction_count">
						<?php esc_html_e( 'Fake reaction count', 'gen' ); ?>
					</label>
				</th>
				<td>
					<input type="number" id="_gen_fake_reaction_count" name="_gen_fake_reaction_count" value="<?php echo esc_attr( $value ) ?>" size="5" />
					<span class="description"><?php esc_html_e( 'Leave empty to use global settings.', 'gen' ); ?></span>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
<?php
}

/**
 * Save metabox data
 *
 * @param int $post_id      Post id.
 *
 * @return mixed
 */
function gen_save_fake_reactions_metabox( $post_id ) {
	// Nonce sent?
	$nonce = filter_input( INPUT_POST, 'gen_fake_reactions_nonce', FILTER_SANITIZE_STRING );

	if ( ! $nonce ) {
		return $post_id;
	}

	// Don't save data automatically via autosave feature.
	if ( gen_is_doing_autosave() ) {
		return $post_id;
	}

	// Don't save data when doing preview.
	if ( gen_is_doing_preview() ) {
		return $post_id;
	}

	// Don't save data when using Quick Edit.
	if ( gen_is_inline_edit() ) {
		return $post_id;
	}

	$post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_STRING );

	// Check permissions.
	$post_type_obj = get_post_type_object( $post_type );

	if ( ! current_user_can( $post_type_obj->cap->edit_post, $post_id ) ) {
		return $post_id;
	}

	// Verify nonce.
	if ( ! check_admin_referer( 'gen_fake_reactions', 'gen_fake_reactions_nonce' ) ) {
		wp_die( esc_html__( 'Nonce incorrect!', 'gen' ) );
	}

	$reaction_count = filter_input( INPUT_POST, '_gen_fake_reaction_count', FILTER_SANITIZE_STRING );

	// Sanitize if not empty.
	if ( ! empty( $reaction_count ) ) {
		$reaction_count = absint( $reaction_count );
	}

	update_post_meta( $post_id, '_gen_fake_reaction_count', $reaction_count );

	do_action( 'gen_save_list_post_metabox', $post_id );

	return $post_id;
}
