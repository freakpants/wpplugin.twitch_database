<?php
/**
 * The Page Template for displaying a singe Twitch Channel
 * 
 * This Template can be overridden in your theme by copying it to yourtheme/twitch_database/single-twitch_channel.php
 * @package twitch_database 
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		
		<?php
		// include breadcrumbs
		require( twitch_channel_template( '/breadcrumbs.php' ) ); ?>
		
		<header class="entry-header">
			<h1 class="entry-title">Twitch Channel - <?= $post->post_title ?></h1>
		</header>
		<?php
		// Start the loop.
		while ( have_posts() ) : the_post();

			// Include the page content template.
			if ( file_exists (get_template_directory() . '/twitch_database/content-single-twitch_channel.php' ) ){
				$content_template = get_template_directory() . '/twitch_database/content-single-twitch_channel.php';
			} else {
				// if there is no page template in the theme directory, use our default plugin template
				$content_template = twitch_channel_template( '/content-single-twitch_channel.php' );
			}
			require( $content_template );

			// End of the loop.
		endwhile;
		?>
	</main><!-- .site-main -->

	<?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
