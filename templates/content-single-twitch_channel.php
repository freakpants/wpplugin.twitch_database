<?php
/**
 * The Content Template for displaying a singe Twitch Channel
 * 
 * This Template can be overridden in your theme by copying it to yourtheme/twitch_database/content-single-twitch_channel.php
 * @package twitch_database
 * 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
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