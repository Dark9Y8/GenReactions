<?php
/**
 * Voting Box Template Part
 *
 * @package whats-your-reaction
 * @subpackage Functions
 */

$gen_reactions 	= gen_get_reactions();
?>

<?php if ( ! empty( $gen_reactions ) ) : ?>
<aside class="gen-reactions">
	<h2 class="g1-beta g1-beta-2nd" style="text-align:center"><?php esc_html_e( 'How do you feel about it?', 'gen' ) ?></h2>

	<?php
	// Common for all reactions.
	$gen_votes 		= gen_get_post_votes();
	$gen_post 		= get_post();
	$gen_author_id	= get_current_user_id();
	$gen_nonce 		= wp_create_nonce( 'gen-vote-post' );
	?>
	<ul class="gen-reaction-items">
	<?php foreach ( $gen_reactions as $gen_reaction ) : ?>
		<?php
		// Reaction id.
		$gen_reaction_id = $gen_reaction->slug;

		// Reaction CSS classes.
		$gen_reaction_classes = array(
			'gen-reaction',
			'gen-reaction-' . $gen_reaction_id,
		);

		if ( gen_user_voted( $gen_reaction_id ) ) {
			$gen_reaction_classes[] = 'gen-reaction-voted';
		}

		$gen_reaction_value 	 = isset( $gen_votes[ $gen_reaction_id ] ) ? $gen_votes[ $gen_reaction_id ]['count'] : 0;
		$gen_reaction_percentage = isset( $gen_votes[ $gen_reaction_id ] ) ? $gen_votes[ $gen_reaction_id ]['percentage'] : 0;
		?>
		<li class="gen-reaction-item">
			<a class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $gen_reaction_classes ) ); ?>" data-gen-nonce="<?php echo esc_attr( $gen_nonce ); ?>" data-gen-post-id="<?php echo absint( $gen_post->ID ); ?>" data-gen-author-id="<?php echo absint( $gen_author_id ); ?>" data-gen-reaction="<?php echo esc_attr( $gen_reaction_id ); ?>">
				<?php gen_render_reaction_icon( $gen_reaction->term_id, array( 'size' => 50 ) ); ?>

				<div class="gen-reaction-track">
					<div class="gen-reaction-value"><?php echo absint( $gen_reaction_value ); ?></div>
					<div class="gen-reaction-bar" style="height: <?php echo absint( $gen_reaction_percentage ); ?>%;">
					</div>
				</div>
				<div class="gen-reaction-button"><strong class="gen-reaction-label"><?php echo esc_html( $gen_reaction->name ); ?></strong></div>
			</a>
		</li>
	<?php endforeach; ?>
	</ul>
</aside>
<?php endif; ?>
