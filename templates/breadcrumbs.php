<?php
/**
 * Breadcrumbs generator 
 * 
 * Generates the breadcrumbs to pages for the twitch_database plugin
 * @package twitch_database
 * 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
 
// get the plugin options to determine the id of the page where all streams are listed
$options = get_option( 'twitch_database_plugin_options' );

echo '<a href="' . home_url() . '">' . __( 'Home' ) . '</a> > ';
// if the all streams page isnt set, simply dont display the breadcrumb link to it
if( isset( $options['channels_overview_page_id'] ) && $options['channels_overview_page_id'] !== '' && $options['channels_overview_page_id'] !== 0 ){
	echo '<a href="' . get_permalink( $options['channels_overview_page_id'] ) . '">Twitch Channels</a> > '; 
}
echo $post->post_title;
?>