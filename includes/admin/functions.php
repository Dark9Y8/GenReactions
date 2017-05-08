<?php
/**
 * Admin Functions
 *
 * @package whats-your-reaction
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Load stylesheets.
 */
function gen_admin_enqueue_styles() {
	$ver = gen_get_plugin_version();
	$url = trailingslashit( gen_get_plugin_url() ) . 'includes/admin/css/';

	wp_enqueue_style( 'gen-admin-main', $url . 'main.css', array(), $ver );
}

/**
 * Load javascripts.
 */
function gen_admin_enqueue_scripts( $hook ) {
	if ( 'term.php' !== $hook ) {
		return;
	}

	$ver = gen_get_plugin_version();
	$url = trailingslashit( gen_get_plugin_url() ) . 'includes/admin/js/';

	wp_enqueue_script( 'gen-admin', $url . 'admin.js', array( 'jquery' ), $ver, true );
}

/**
 * Add custom fields to "Add New Reaction" screen
 */
function gen_taxonomy_add_form_fields() {
	?>
	<div class="form-field term-icon-wrap">
		<label for="reaction-icon"><?php esc_html_e( 'Icon', 'gen' ); ?></label>

		<?php
			$sets = gen_get_reaction_icons();
			$index = 0;
		?>

		<?php foreach ( $sets as $set_id => $icons ) : ?>
			<ul class="gen-icon-items">
				<?php foreach ( $icons as $icon_id => $icon_args ) : ?>
					<li class="gen-icon-item">
						<label>
							<?php
							gen_render_reaction_icon( null, array(
								'size' => 40,
								'set'  => $set_id,
								'icon' => $icon_id,
								'text' => $icon_args['label'],
							) );
							?>
							<span class="gen-reaction-label"><?php echo esc_html( $icon_args['label'] ); ?></span>
							<input type="radio" name="icon" value="<?php echo esc_attr( $icon_id ); ?>" <?php checked( ! $index ); ?>  autocomplete="off" />
						</label>
					</li>
					<?php $index++; ?>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>

	</div>
	<div class="form-field term-order-wrap">
		<label for="reaction-order"><?php echo esc_html_x( 'Order', 'label', 'gen' ); ?></label>

		<input type="number" name="order" value="0" size="10" />
	</div>
	<?php
}

/**
 * Add custom fields to "Edit Reaction" screen
 *
 * @param WP_Term $term			Term object.
 */
function gen_taxonomy_edit_form_fields( $term ) {
	$term_id	= $term->term_id;

	$icon_type              = get_term_meta( $term_id, 'icon_type', true );
	$icon_type              = $icon_type === 'text' ? 'text' : 'visual';

	$icon_set = get_term_meta( $term_id, 'icon_set', true );
	// Normalize.
	$icon_set = empty( $icon_set ) ? 'emoji' : $icon_set;

	$icon 		            = get_term_meta( $term_id, 'icon', true );

	$icon_color             = get_term_meta( $term_id, 'icon_color', true );
	$icon_background_color  = get_term_meta( $term_id, 'icon_background_color', true );

	$sets 		= gen_get_reaction_icons();
	$order		= absint( get_term_meta( $term_id, 'order', true ) );
	$disabled   = get_term_meta( $term_id, 'disabled', true );
	?>
	<tr class="form-field">
		<th scope="row">
			<label for="icon_type"><?php echo esc_html_x( 'Icon type', 'term field label', 'gen' ); ?></label>
		</th>
		<td>
			<label><input type="radio" name="icon_type" value="text"  <?php checked( $icon_type, 'text' ); ?> /><?php echo esc_html_x( 'Text', 'icon type', 'gen' ); ?></label>
			<label><input type="radio" name="icon_type" value="visual"  <?php checked( $icon_type, 'visual' ); ?> /><?php echo esc_html_x( 'Visual', 'icon type', 'gen' ); ?></label>
		</td>
	</tr>

	<tr class="form-field term-icon-wrap">
		<th scope="row">
			<label for="icon"><?php echo esc_html_x( 'Icon', 'term field label', 'gen' ); ?></label>
		</th>
		<td>
			<div class="gen-reaction-icon-sets">
				<?php foreach ( $sets as $set_id => $icons ) : ?>
					<ul class="gen-icon-items">
						<?php foreach ( $icons as $icon_id => $icon_args ) : ?>

							<li class="gen-icon-item">
								<label>
									<?php
									gen_render_reaction_icon( null, array(
										'size' => 40,
										'set'  => $set_id,
										'icon' => $icon_id,
										'text' => $icon_args['label'],
									) );
									?>
									<span class="gen-reaction-label"><?php echo esc_html( $icon_args['label'] ); ?></span>
									<input type="radio" name="icon" value="<?php echo esc_attr( $set_id . ':' . $icon_id ); ?>" <?php checked( $set_id . ':' .$icon_id, $icon_set . ':' . $icon ); ?>  autocomplete="off" />
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endforeach; ?>
			</div>

			<div class="gen-reaction-icon-preview">
				<?php gen_render_reaction_icon( $term->term_id, array( 'size' => 40 ) ); ?>
			</div>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row">
			<label for="icon_color"><?php echo esc_html_x( 'Icon text color', 'term field label', 'gen' ); ?></label>
		</th>
		<td>
			<input class="gen-color-picker" type="text" name="icon_color" value="<?php echo esc_attr( $icon_color ); ?>">
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row">
			<label for="icon_background_color"><?php echo esc_html_x( 'Icon background color', 'term field label', 'gen' ); ?></label>
		</th>
		<td>
			<input class="gen-color-picker" type="text" name="icon_background_color" value="<?php echo esc_attr( $icon_background_color ); ?>">
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row">
			<label for="order"><?php echo esc_html_x( 'Order', 'term field label', 'gen' ); ?></label>
		</th>
		<td>
			<input type="text" name="order" value="<?php echo esc_attr( $order ); ?>">
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row">
			<label for="active"><?php echo esc_html_x( 'Disabled', 'term field label', 'gen' ); ?></label>
		</th>
		<td>
			<input type="checkbox" name="disabled" value="standard"<?php checked( $disabled, 'standard' ); ?>>
		</td>
	</tr>
	<?php
}

function gen_taxonomy_save_custom_form_fields( $term_id ) {
	$icon_type 	            = filter_input( INPUT_POST, 'icon_type', FILTER_SANITIZE_STRING );
	$icon_color 	        = filter_input( INPUT_POST, 'icon_color', FILTER_SANITIZE_STRING );
	$icon_background_color 	= filter_input( INPUT_POST, 'icon_background_color', FILTER_SANITIZE_STRING );
	$order 		            = filter_input( INPUT_POST, 'order', FILTER_SANITIZE_NUMBER_INT );
	$disabled	            = filter_input( INPUT_POST, 'disabled', FILTER_SANITIZE_STRING );


	// 'set:icon' or just 'icon'
	$icon = filter_input( INPUT_POST, 'icon', FILTER_SANITIZE_STRING );
	$icon_set = 'emoji';
	$icon = explode( ':', $icon );
	if ( 2 === count( $icon ) ) {
		$icon_set = $icon[0];
		$icon = $icon[1];
	} else {
		$icon = $icon[0];
	}

	if ( $icon_type ) {
		update_term_meta( $term_id, 'icon_type', $icon_type );
	}

	if ( $icon ) {
		update_term_meta( $term_id, 'icon', $icon );
	}

	if ( $icon_set ) {
		update_term_meta( $term_id, 'icon_set', $icon_set );
	}

	update_term_meta( $term_id, 'icon_color', $icon_color );

	update_term_meta( $term_id, 'icon_background_color', $icon_background_color );

	if ( ! $order ) {
		$order = count( gen_get_reactions() ) + 1;
	}

	update_term_meta( $term_id, 'order', $order );
	update_term_meta( $term_id, 'disabled', $disabled ? $disabled : '' );
}

/**
 * Register new columns
 *
 * @param array $columns		List of columns.
 *
 * @return array
 */
function gen_taxonomy_add_columns( $columns ) {
	$new_columns = array(
		'order' => _x( 'Order', 'taxonomy column name', 'gen' ),
		'icon' 	=> _x( 'Icon', 'taxonomy column name', 'gen' ),
	);

	$columns = array_merge( $new_columns, $columns );

	$columns['active'] = _x( 'Active?', 'taxonomy column name', 'gen' );

	return $columns;
}

/**
 * Display custom columns content
 *
 * @param string $content			Column content.
 * @param string $column_name		Column name.
 * @param int    $term_id			Term id.
 *
 * @return string
 */
function gen_taxonomy_display_custom_columns_content( $content, $column_name, $term_id ) {
	switch ( $column_name ) {
		case 'order':
			$content = get_term_meta( $term_id, 'order', true );
			break;

		case 'icon':
			$content = gen_capture_reaction_icon( $term_id, array( 'size' => 40 ) );
			break;

		case 'active':
			$content = 'standard' === get_term_meta( $term_id, 'disabled', true ) ? 'no' : 'yes';
			break;
	}

	return $content;
}

/**
 * Chamge terms query args
 *
 * @param array $args			Query args.
 * @param array $taxonomies		Taxonomies.
 *
 * @return array
 */
function gen_taxonomy_change_term_list_order( $term_query ) {
	// Skip if not admin request.
	if ( ! is_admin() ) {
		return;
	}

	$taxonomy   = gen_get_taxonomy_name();
	$taxonomies = isset( $term_query->query_vars['taxonomy'] ) ? $term_query->query_vars['taxonomy'] : array();

	// Skip if not gen taxonomy.
	if ( ! in_array( $taxonomy, $taxonomies, true ) ) {
		return;
	}

	$term_query->query_vars['meta_key']	= 'order';
	$term_query->query_vars['orderby'] 	= 'meta_value_num';
	$term_query->query_vars['order'] 	= 'ASC';
}

/**
 * Check whether we are in preview mode
 *
 * @return bool
 */
function gen_is_doing_preview() {
	$preview = filter_input( INPUT_POST, 'wp-preview' );

	return ! empty( $preview );
}

/**
 * Check whether we are in autosave state
 *
 * @return bool
 */
function gen_is_doing_autosave() {
	return defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ? true : false;
}

/**
 * Check whether we are during inline edition
 *
 * @return bool
 */
function gen_is_inline_edit() {
	return ! is_null( filter_input( INPUT_POST, '_inline_edit' ) );
}
