<?php
/**
 * The template for displaying a single Twitch Channel
 *
 * @package Twitch Database
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php
		// Start the loop.
		while ( have_posts() ) : the_post();
			?>
				<iframe id="stream_player" width="100%" src="http://player.twitch.tv/?channel=<?= $post->post_title ?>" allowfullscreen></iframe>
				<iframe src="http://twitch.tv/<?= $post->post_title ?>/chat?popout=" width="100%" height="450px" frameborder="0" scrolling="no">
				</iframe>
				<script type="text/javascript">
					function resize_stream(){
						var height = jQuery('#stream_player').width() / 16 * 9;
						jQuery('#stream_player').height(height);
					}
					jQuery( document ).ready(function() {
						resize_stream();
					});
					window.onresize = resize_stream;
				</script>
			<?php

			// End of the loop.
		endwhile;
		?>

	</main><!-- .site-main -->

	<?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>