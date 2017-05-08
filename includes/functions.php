<?php
/**
 * Common Functions
 *
 * @package whats-your-reaction
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Plugin acitvation
 */
function gen_activate() {
	gen_install_votes_schema();
}

/**
 * Plugin deacitvation
 */
function gen_deactivate() {}

/**
 * Plugin uninstallation
 */
function gen_uninstall() {}

/**
 * Install table 'gen_votes'
 */
function gen_install_votes_schema() {
	global $wpdb;

	$current_ver    = '1.0';
	$installed_ver  = get_option( 'gen_votes_table_version' );

	// Create table only if needed.
	if ( $installed_ver !== $current_ver ) {
		$table_name      = $wpdb->prefix . gen_get_votes_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		vote_id bigint(20) unsigned NOT NULL auto_increment,
		post_id bigint(20) NOT NULL ,
		vote varchar(20) NOT NULL,
		author_id bigint(20) NOT NULL default '0',
  		author_ip varchar(100) NOT NULL default '',
		author_host varchar(200) NOT NULL,
		date datetime NOT NULL default '0000-00-00 00:00:00',
  		date_gmt datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (vote_id),
		KEY post_id (post_id)
	) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( 'gen_votes_table_version', $current_ver );
	}
}

/**
 * Load stylesheets.
 */
function gen_enqueue_styles() {
	$ver = gen_get_plugin_version();
	$url = trailingslashit( gen_get_plugin_url() ) . 'css/';

	wp_enqueue_style( 'gen-main', $url . 'main.min.css', array(), $ver );
	wp_style_add_data( 'gen-main', 'rtl', 'replace' );
}

/**
 * Load javascripts.
 */
function gen_enqueue_scripts() {
	$ver = gen_get_plugin_version();

	wp_enqueue_script( 'gen-front', gen_get_plugin_url() . 'js/front.js', array( 'jquery' ), $ver, true );

	$front_config = array(
		'ajax_url'          => admin_url( 'admin-ajax.php' ),
	);

	wp_localize_script( 'gen-front', 'gen_front_config', wp_json_encode( $front_config ) );
}

/**
 * Return unique taxonomy name
 *
 * @return string
 */
function gen_get_taxonomy_name() {
	return 'reaction';
}

/**
 * Register "Reactions" taxonomy
 */
function gen_register_taxonomy() {
	$labels = array(
		'name' 				=> _x( 'Reactions', 'taxonomy general name', 'gen' ),
		'singular_name' 	=> _x( 'Reaction', 'taxonomy singular name', 'gen' ),
		'search_items' 		=> __( 'Search Reactions', 'gen' ),
		'all_items' 		=> __( 'All Reactions', 'gen' ),
		'parent_item' 		=> __( 'Parent Reaction', 'gen' ),
		'parent_item_colon' => __( 'Parent Reaction:', 'gen' ),
		'edit_item' 		=> __( 'Edit Reaction', 'gen' ),
		'update_item' 		=> __( 'Update Reaction', 'gen' ),
		'add_new_item' 		=> __( 'Add New Reaction', 'gen' ),
		'new_item_name' 	=> __( 'New Reaction Name', 'gen' ),
		'menu_name' 		=> __( 'Reactions', 'gen' ),
		'view_item'         => __( 'View Reaction', 'gen' ),
		'not_found'         => __( 'No Reactions Found', 'wy' ),
	);

	$args = array(
		'hierarchical' 		=> false,
		'labels' 			=> $labels,
		'show_ui' 			=> true,
		'show_admin_column' => true,
		'query_var' 		=> true,
		'orderby' 			=> 'slug',
		'rewrite' 			=> array(
			'slug' => 'reaction',
		),
	);

	$taxonomy_name = gen_get_taxonomy_name();

	register_taxonomy( $taxonomy_name, array( 'post' ), $args );
}

/**
 * Return list of ordered reactions
 *
 * @return array		List of term objects.
 */
function gen_get_reactions() {
	$terms = get_terms( array(
		'taxonomy'		=> gen_get_taxonomy_name(),
		'hide_empty'	=> false,
		'meta_key' 	=> 'order',
		'orderby'	=> 'meta_value_num',
		'order'		=> 'ASC',
	) );

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	// @todo - use WP_Meta_Query
	foreach ( $terms as $id => $term ) {
		if ( 'standard' === get_term_meta( $term->term_id, 'disabled', true ) ) {
			unset( $terms[ $id ] );
		}
	}

	return $terms;
}

/**
 * Return reaction term object by name
 *
 * @param string $name		Reaction name.
 *
 * @return bool|WP_Term
 */
function gen_get_reaction( $name ) {
	$terms = get_terms( array(
		'taxonomy'		=> gen_get_taxonomy_name(),
		'hide_empty'	=> false,
		'slug'			=> $name,
	) );

	if ( empty( $terms ) ) {
		return false;
	}

	return $terms[0];
}

/**
 * Return list of ordered post reactions
 *
 * @return array		List of term objects.
 */
function gen_get_post_reactions( $post = null ) {
	$post = get_post( $post );

	$terms = wp_get_post_terms(
		$post->ID,
		gen_get_taxonomy_name(),
		array(
			'meta_key' 	=> 'order',
			'orderby'	=> 'meta_value_num',
			'order'		=> 'ASC',
		)
	);

	// @todo - use WP_Meta_Query
	foreach ( $terms as $id => $term ) {
		if ( 'standard' === get_term_meta( $term->term_id, 'disabled', true ) ) {
			unset( $terms[ $id ] );
		}
	}

	return $terms;
}

/**
 * Check whether the reaction exists
 *
 * @param string $name		Type of reaction.
 *
 * @return bool
 */
function gen_is_valid_reaction( $name ) {
	$terms = get_terms( array(
		'taxonomy'		=> gen_get_taxonomy_name(),
		'hide_empty'	=> false,
		'slug'			=> $name,
	) );

	return ! empty( $terms );
}

/**
 * Hook into post content
 *
 * @param string $content		Post content.
 *
 * @return string
 */
function gen_load_post_voting_box( $content ) {
	if ( ! apply_filters( 'gen_load_post_voting_box', is_single() ) ) {
		return $content;
	}

	$content .= gen_get_voting_box();

	return $content;
}

/**
 * Return voting box HTML container
 *
 * @return string
 */
function gen_get_voting_box() {
	return do_shortcode( '[gen_voting_box]' );
}

/**
 * Render voting box HTML container
 *
 * @return string
 */
function gen_render_voting_box() {
	echo gen_get_voting_box();
}

/**
 * Load a template part into a template
 *
 * @param string $slug The slug name for the generic template.
 * @param string $name The name of the specialised template.
 */
function gen_get_template_part( $slug, $name = null ) {
	// Trim off any slashes from the slug.
	$slug = ltrim( $slug, '/' );

	if ( empty( $slug ) ) {
		return;
	}

	$parent_dir_path = trailingslashit( get_template_directory() );
	$child_dir_path  = trailingslashit( get_stylesheet_directory() );

	$files = array(
		$child_dir_path . 'whats-your-reaction/' . $slug . '.php',
		$parent_dir_path . 'whats-your-reaction/' . $slug . '.php',
		gen_get_plugin_dir() . 'templates/' . $slug . '.php',
	);

	if ( ! empty( $name ) ) {
		array_unshift(
			$files,
			$child_dir_path . 'whats-your-reaction/' . $slug . '-' . $name . '.php',
			$parent_dir_path . 'whats-your-reaction/' . $slug . '-' . $name . '.php',
			gen_get_plugin_dir() . 'templates/' . $slug . '-' . $name . '.php'
		);
	}

	$located = '';

	foreach ( $files as $file ) {
		if ( empty( $file ) ) {
			continue;
		}

		if ( file_exists( $file ) ) {
			$located = $file;
			break;
		}
	}

	if ( strlen( $located ) ) {
		load_template( $located, false );
	}
}

/**
 * Check whether user has already voted for a post
 *
 * @param string $type 			Vote type.
 * @param int    $post_id 		Post id.
 * @param int    $user_id 		User id.
 *
 * @return mixed		Vote type or false if not exists
 */
function gen_user_voted( $type, $post_id = 0, $user_id = 0 ) {
	$post = get_post( $post_id );

	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	// User not logged in, guest voting disabled.
	if ( 0 === $user_id && ! gen_guest_voting_is_enabled() ) {
		return false;
	}

	// User not logged in, guest voting enabled.
	if ( 0 === $user_id && gen_guest_voting_is_enabled() ) {
		$vote_cookie = filter_input( INPUT_COOKIE, 'gen_vote_' . $type . '_' . $post->ID, FILTER_SANITIZE_STRING );

		return (bool) $vote_cookie;
	}

	// User logged in.
	global $wpdb;
	$votes_table_name = $wpdb->prefix . gen_get_votes_table_name();

	$vote = $wpdb->get_var(
		$wpdb->prepare(
			"
			SELECT vote
			FROM $votes_table_name
			WHERE post_id = %d AND author_id = %d AND vote = %s
			ORDER BY vote_id DESC
			LIMIT 1",
			$post->ID,
			$user_id,
			$type
		)
	);

	return $vote;
}

/**
 * Check whether guest user can vote
 *
 * @return bool
 */
function gen_guest_voting_is_enabled() {
	return apply_filters( 'gen_guest_voting_is_enabled', true );
}

/**
 * Get the table name of the votes table
 *
 * @return string
 */
function gen_get_votes_table_name() {
	return 'gen_votes';
}

/**
 * Return votes summary
 *
 * @param int|WP_Post $post_id 			Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return int
 */
function gen_get_post_votes( $post_id = 0 ) {
	$post = get_post( $post_id );

	$votes = get_post_meta( $post->ID, '_gen_votes', true );

	return apply_filters( 'gen_post_votes', $votes, $post );
}

/**
 * Register new vote for a post
 *
 * @param array $vote_arr Vote config.
 *
 * @return bool|WP_Error
 */
function gen_vote_post( $vote_arr ) {
	$defaults = array(
		'post_id'   => get_the_ID(),
		'author_id' => get_current_user_id(),
		'type'      => '',
	);

	$vote_arr = wp_parse_args( $vote_arr, $defaults );

	global $wpdb;
	$table_name = $wpdb->prefix . gen_get_votes_table_name();

	$post_date  = current_time( 'mysql' );
	$ip_address = gen_get_ip_address();
	$host = gethostbyaddr( $ip_address );

	$affected_rows = $wpdb->insert(
		$table_name,
		array(
			'post_id'     => $vote_arr['post_id'],
			'vote'        => $vote_arr['type'],
			'author_id'   => $vote_arr['author_id'],
			'author_ip'   => $ip_address ? $ip_address : '',
			'author_host' => $host ? $host : '',
			'date'        => $post_date,
			'date_gmt'    => get_gmt_from_date( $post_date ),
		),
		array(
			'%d',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
		)
	);

	if ( false === $affected_rows ) {
		return new WP_Error( 'gen_insert_vote_failed', esc_html__( 'Could not insert new vote into the database!', 'gen' ) );
	}

	$meta = gen_update_votes_metadata( $vote_arr['post_id'] );

	// Assign post to reaction term if reached threshold.
	$reaction_threshold = apply_filters( 'gen_reaction_threshold', 3 );
	$reaction_type 		= $vote_arr['type'];

	if ( $meta[ $reaction_type ]['count'] >= $reaction_threshold ) {
		$reaction_term = gen_get_reaction( $reaction_type );

		wp_set_post_terms( $vote_arr['post_id'], array( $reaction_term->term_id ), gen_get_taxonomy_name(), true );
	}

	do_action( 'gen_vote_added', $vote_arr, $meta );

	return $meta;
}

/**
 * Return vistor IP address
 *
 * @return string
 */
function gen_get_ip_address() {
	$http_x_forwarder_for = filter_input( INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_SANITIZE_STRING );
	$remote_addr          = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING );

	if ( empty( $http_x_forwarder_for ) ) {
		$ip_address = $remote_addr;
	} else {
		$ip_address = $http_x_forwarder_for;
	}

	if ( false !== strpos( $ip_address, ',' ) ) {
		$ip_address = explode( ',', $ip_address );
		$ip_address = $ip_address[0];
	}

	return $ip_address;
}

/**
 * Update voting stats
 *
 * @param int   $post_id            Post id.
 * @param array $meta               Current meta value.
 *
 * @return bool
 */
function gen_update_votes_metadata( $post_id = 0, $meta = array() ) {
	$post = get_post( $post_id );

	if ( empty( $meta ) ) {
		$meta = gen_generate_votes_metadata( $post );
	}

	if ( empty( $meta ) ) {
		return false;
	}

	foreach ( $meta as $type => $data ) {
		update_post_meta( $post->ID, '_gen_' . $type . '_count', $data['count'] );
		update_post_meta( $post->ID, '_gen_' . $type . '_percentage', $data['percentage'] );
	}

	update_post_meta( $post->ID, '_gen_votes', $meta );

	return $meta;
}

/**
 * Generate voting stats
 *
 * @param int $post_id          Post id.
 *
 * @return array
 */
function gen_generate_votes_metadata( $post_id = 0 ) {
	$post = get_post( $post_id );

	global $wpdb;
	$votes_table_name = $wpdb->prefix . gen_get_votes_table_name();

	$votes = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT vote, count(vote) AS cnt
			FROM $votes_table_name
			WHERE post_id = %d
			GROUP BY vote",
			$post->ID
		)
	);

	$total_votes = 0;
	$meta = array();

	foreach ( $votes as $group_data ) {
		$type = $group_data->vote;
		$cnt  = $group_data->cnt;

		$meta[ $type ] = array(
			'count' => $cnt,
		);

		$total_votes += $cnt;
	}

	// Calculate percentages.
	foreach ( $meta as $type => $data ) {
		$percentage = round( ( 100 * $data['count'] ) / $total_votes );

		$meta[ $type ]['percentage'] = $percentage;
	}

	return apply_filters( 'gen_votes_metadata', $meta, $post->ID );
}

function gen_render_reaction_icon( $term_id, $args = array() ) {
	echo gen_capture_reaction_icon( $term_id, $args );
}

function gen_capture_reaction_icon( $term_id, $args = array() ) {
	$term = get_term( $term_id, gen_get_taxonomy_name() );

	if ( ! is_wp_error( $term ) ) {
		$term_args = array(
			'type'             => get_term_meta( $term->term_id, 'icon_type', true ),
			'set'              => get_term_meta( $term->term_id, 'icon_set', true ),
			'icon'             => get_term_meta( $term->term_id, 'icon', true ),
			'text'             => $term->name,
			'color'            => get_term_meta( $term->term_id, 'icon_color', true ),
			'background_color' => get_term_meta( $term->term_id, 'icon_background_color', true ),
		);

		$defaults = array(
			'size'              => 50,
		);

		$args = wp_parse_args( $args, $term_args );
		$args = wp_parse_args( $args, $defaults );
	} else {
		$defaults = array(
			'size'              => 50,
			'type'              => 'visual',
			'color'             => '',
			'background_color'  => '',
		);


		$args = wp_parse_args( $args, $defaults );
	}

	// Normalize.
	$args['set'] = empty ( $args['set'] ) ? 'emoji' : $args['set'];

	// Compose CSS.
	$css_style = '';
	$css_style .= strlen( $args['color'] ) ? 'color: ' . sanitize_hex_color( $args['color'] ) . ';' : '';
	$css_style .= strlen( $args['background_color'] ) ? 'background-color: ' . sanitize_hex_color( $args['background_color'] ) . ';' : '';
	$css_style = strlen( $css_style ) ? 'style="' . $css_style . '"' : '';


	$out = '';

	$class = array(
		'gen-reaction-icon',
		'gen-reaction-icon-' . $args['icon'],
		'gen-reaction-icon-with-' . $args['type'],
	);

	$out .= '<span class="'. implode( ' ', array_map( 'sanitize_html_class', $class ) ) . '" '. $css_style . '>';
		$out .= '<img width="' . absint( $args['size'] ) .  '" height="' . absint( $args['size'] ) . '" src="' . esc_url( gen_get_plugin_url() . 'images/'. $args['set'] . '/' . $args['icon'] . '.gif' ) . '" alt="" />';

		$out .= '<span class="gen-reaction-icon-text">' . esc_html( $args['text'] ) . '</span>';
	$out .= '</span>';

	return apply_filters( 'gen_capture_reaction_icon', $out, $term_id, $args );
}

function gen_get_reaction_icons() {
	$icons = array(
		'emoji' => array(
			'angry'     => array( 'label' => __( 'Angry', 'gen' ) ),
			'cry'       => array( 'label' => __( 'Sad', 'gen' ) ),
			'lol'       => array( 'label' => __( 'Haha', 'gen' ) ),
			'love'      => array( 'label' => __( 'Love', 'gen' ) ),
			'omg'       => array( 'label' => __( 'Wow', 'gen' ) ),
			'win'       => array( 'label' => __( 'Like', 'gen' ) ),
		),
	);

	return apply_filters( 'gen_reaction_icons', $icons );
}

function gen_fake_reaction_count( $votes, $post ) {
	if ( empty( $post ) ) {
		return $votes;
	}

	// Get value defined for that single post.
	$fake_count = (int) get_post_meta( $post->ID, '_gen_fake_reaction_count', true );

	// If user not defined the count, calculate it.
	if ( ! $fake_count ) {
		$fake_base = (int) get_option( 'gen_fake_reaction_count_base' );

		// Only if fake base is set, we can apply fake count.
		if ( $fake_base > 0 ) {
			$fake_factor = gen_get_fake_factor( $post->post_date );

			$fake_count = round( $fake_base * $fake_factor );
		}
	}

	$fake_count = apply_filters( 'gen_fake_reaction_count', $fake_count, $post->ID );

	if ( $fake_count <= 0 ) {
		return $votes;
	}

	$fake_votes     = array();
	$reation_terms  = gen_get_reactions();

	// Fill fake votes array with $votes data (if any) to keep order.
	foreach ( $reation_terms as $reation_term ) {
		$id = $reation_term->slug;

		// If condig exists, use it.
		if ( isset( $votes[ $id ] ) ) {
			$fake_votes[ $id ] = $votes[ $id ];
		} else {
			$fake_votes[ $id ] = array(
				'count' 	 => 0,
				'percentage' => 0,
			);
		}
	}

	$total_votes 		= 0;
	$fake_count_diff 	= round( $fake_count * 17 / 100 ); // 17%.
	$fake_count_partial = ( count( $fake_votes ) - 1 ) * $fake_count_diff; // Start with the top value.

	// Add fakes.
	foreach ( $fake_votes as $id => $data ) {
		$type_fake_count = $fake_count + $fake_count_partial;

		$fake_votes[ $id ]['count'] += $type_fake_count;

		$total_votes += $fake_votes[ $id ]['count'];

		$fake_count_partial -= $fake_count_diff; // And decrease it.
	}

	// Recalculate percentages.
	foreach ( $fake_votes as $id => $data ) {
		$percentage = round( ( 100 * $data['count'] ) / $total_votes );

		$fake_votes[ $id ]['percentage'] = $percentage;
	}

	return $fake_votes;
}

/**
 * Return fake factor based on post creation date
 *
 * @param string $date			Post creation date.
 *
 * @return float
 */
function gen_get_fake_factor( $date ) {
	$current_time = time();
	$date_time 	  = strtotime( $date );

	$day_in_seconds = 24 * 60 * 60;

	$days_diff = round( abs( $current_time - $date_time ) / $day_in_seconds );

	$t = $days_diff;	// Current time.
	$b = 0.1;			// Start value.
	$c = 0.9;			// Change in value.
	$d = 30;			// Duration.

	// Factor function doesn't return value equal to 1 after $d time.
	// Which is normal, as it's sinus, but we want to have 1 value after $d duration.
	if ( $days_diff > $d ) {
		return 1;
	}

	// EaseOutSine.
	$factor = $c * sin( $t / $d * (pi() / 2 ) ) + $b;

	return $factor;
}
