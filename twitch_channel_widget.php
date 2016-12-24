<?php
class twitch_channel_widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'twitch_channel_widget',
			'description' => __( 'Show one Random Twitch Stream that is currently online.' ),
		);
		parent::__construct( 'twitch_channel_widget', 'Twitch Channel Widget', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$args = array(
			'posts_per_page'   => -1,
			'meta_key'         => 'online_status',
			'meta_value'       => 'online',
			'post_type'        => 'twitch_channel',
			'post_status'      => 'publish',
			'orderby' 		   => 'rand',
		);
		$twitch_channels = get_posts( $args );
		$channel_meta = get_post_meta( $twitch_channels[0]->ID);
		
		$online_streams = count($twitch_channels);

		// outputs the content of the widget
		echo '
		<section class="widget twitch_channel_widget">
			<h2 class="widget-title">' . __( 'Streams' )  . '</h2>
			<div class="twitch_channel_item">
				<img class="twitch_thumbnail" src="'.$channel_meta['medium_thumbnail'][0].'" />
				<div class="channel_title">' . $twitch_channels[0]->post_name . '</div>
				<div class="stream_title">' . $channel_meta['stream_title'][0] . '</div>
				<div class="game_title"><b>Game: </b>' . $channel_meta['game'][0] . '</div>
				<div class="viewers"><b>Viewers: </b>' . $channel_meta['viewers'][0] . '</div>
			</div>
			<div class="online_streams">
				<b>' . $online_streams . ' Streams are currently online</b>
			</div>
		</section>
		';
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
	}
}
?>