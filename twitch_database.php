<?php
/*
Plugin Name: Twitch Database
Description: Maintain a database of Twitch Channels and their online status
Author:      freakpants - Christian Nyffenegger
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// include the options panel for the backend
require( 'plugin-options.php' );
require( 'update_stream_status.php' );

function add_css() {
	wp_register_style('twitch_style', plugins_url('style.css',__FILE__ ));
	wp_enqueue_style('twitch_style');
}
add_action( 'admin_init','add_css' );

add_action( 'init', 'create_post_type' );
function create_post_type() {
	register_post_type( 'twitch_channel',
    array(
		'labels' => array(
        'name' => __( 'Twitch Channels' ),
        'singular_name' => __( 'Twitch Channel' ),
    ),
	'menu_icon' => plugin_dir_url(__FILE__) . 'assets/images/twitch.png' ,
    'public' => true,
    'has_archive' => true,
	));
}
add_action( 'init', 'create_taxonomies', 0 );
function create_taxonomies(){
	// Add game taxonomy
	register_taxonomy('game','twitch_channel',array(
			'label' => __( 'Game' ),
			'rewrite' => array( 'slug' => 'game' ),
			'hierarchical' => false,
			'show_in_menu' => false,
		)
	);
}

function filter_content($content) {
	global $post;
	if( $post->post_type === 'twitch_channel' ) {
		require("templates/single-twitch_channel.php");
	} else {
		return $content;
	}
}
add_filter( 'the_content', 'filter_content' ); 

add_action( 'save_post_twitch_channel', 'twitch_channel_save', 10 , 3 );
function twitch_channel_save( $post_id, $post, $update ) {
    if( $update ){	
	} else {
		// if new twitch channel, set meta values to default values
		add_post_meta($post_id, 'online_status', 'offline', true);
		add_post_meta($post_id, 'viewers', 0, true);
	}
}

// add online status to backend list
add_filter( 'manage_twitch_channel_posts_columns' , 'add_online_status' );
function add_online_status( $columns ) {
	$columns = array(
	"cb" => "", 
	"medium_thumbnail" => '',
	"title" => __('Channel'), 
	"display_name" => __('Display Name'),
	"stream_title" => __('Stream Title'),
	'viewers' => __('Viewers'),
	'online_status' => __('Status'),  
	'game' => __('Game'),  
	"date" => __('Date')
	);
	return $columns;
}

// output custom columns for the twitch_channel post type
add_action( 'manage_posts_custom_column' , 'custom_columns', 10, 2 );
function custom_columns( $column, $post_id ) {
	switch ( $column ) {
		case 'online_status':
			if ( get_post_meta( $post_id, $column , true ) === 'online' ){
				echo '
				<a class="online_status online" href="/wp-admin/edit.php?post_type=twitch_channel&online_status=online">
					online
				</a>';
			} else {
				echo '
				<a class="online_status offline" href="/wp-admin/edit.php?post_type=twitch_channel&online_status=offline">
					offline
				</a>';
			}
		break;
		case 'viewers':
		case 'display_name':
		case 'stream_title':
			echo get_post_meta( $post_id, $column , true ); 
		break;
		case 'medium_thumbnail':
			$title = strtolower( get_the_title( $post_id ) );
			$url = wp_upload_dir()['baseurl'].'/channel_thumbs/'.$title.'_medium_thumb.jpg';
			$path = wp_upload_dir()['basedir'].'/channel_thumbs/'.$title.'_medium_thumb.jpg';
			$twitch_404 = plugin_dir_url(__FILE__).'assets/images/twitch_404.jpg';
			// check if the thumbnail exists, and default to the default twitch channel thumb if missing
			if ( is_file($path) ){
				echo '<img width="100%" src="'.$url.'"></img>';
			} else {
				echo '<img style="opacity:0.6" width="100%" src="'.$twitch_404.'"></img>';
			}
		break;
		case 'game':
			$games = wp_get_object_terms( $post_id, 'game');
			if( $games ){
				foreach ( $games as $game ){
					echo 
					'<a href="/wp-admin/edit.php?post_type=twitch_channel&game='.$game->slug.'">'.$game->name.'</a>';
				}
			} 
		break;
	}
}

// enable filtering in backend
add_action('restrict_manage_posts','my_restrict_manage_posts');
function my_restrict_manage_posts() {
	global $typenow;
	$selected = '';
	if ( isset( $_GET['game'] ) ){
		$selected = $_GET['game'];
	}   
	if ( $typenow === 'twitch_channel' ){
		$args = array(
        'show_option_all' => __( "Show All Games" ),
        'taxonomy' => 'game',
        'name' => 'game',
		'value_field' => 'slug',
		'selected' => $selected,);
		wp_dropdown_categories($args);
	}
}


// flush permalinks on plugin activation/deactivation so the custom post type slug can be accessed 
register_deactivation_hook( __FILE__, 'flush_permalinks' );
register_activation_hook( __FILE__, 'flush_permalinks' );
function flush_permalinks() {
	create_post_type();
	flush_rewrite_rules();
}

// create a directory for our stream thumbs in wp_uploads so we have a consistent location for them
register_activation_hook( __FILE__, 'create_thumbnail_directory' );
function create_thumbnail_directory(){
	$file_path = wp_upload_dir()['basedir'] . '/channel_thumbs/';
	wp_mkdir_p( $file_path  );
}

function my_cron_schedules($schedules){
    if(!isset($schedules["1min"])){
        $schedules["1min"] = array(
            'interval' => 1*60,
            'display' => __('Once every minute'));
    }
    return $schedules;
}
add_filter('cron_schedules','my_cron_schedules');

add_action('cron_event', 'update_stream_status');
wp_schedule_event(time(), '1min', 'cron_event');

add_action( 'pre_get_posts', 'posts_query' );
function posts_query( $query ){
	global $typenow;
	if ( isset( $_GET['online_status'] ) && $typenow === 'twitch_channel' && is_admin() ){
		$query->set( 'meta_key' , 'online_status' );
		if( $_GET['online_status'] === 'online' ){
			$query->set( 'meta_value' , 'online' );
		} else {
			$query->set( 'meta_value' , 'offline' );
		}
	}
}

// shortcode for outputting a single channel
function display_twitch_channel( $atts ) {
    $attributes = shortcode_atts( array(
        'channel_name' => 'something',
    ), $atts );
	var_dump($attributes);
}
add_shortcode( 'twitch_channel', 'display_twitch_channel' );

// override the page template for our twitch channel custom post type
function get_custom_post_type_template($single_template) {
    global $post;

    if ($post->post_type == 'twitch_channel' ) {
		// check if a page template exists in the theme directory
		if ( file_exists (get_template_directory() . '/twitch_channel/single-twitch_channel.php' ) ){
			$single_template = get_template_directory() . '/twitch_channel/single-twitch_channel.php';
		} else {
			// if there is no page template in the theme directory, use our default plugin template
			$single_template = dirname( __FILE__ ) . '/templates/single-twitch_channel.php';
		}
    }
    return $single_template;
}
add_filter( 'single_template', 'get_custom_post_type_template' );

// returns the path of a template relative to the plugin directory (for use in theme templates that override the plugin templates)
function twitch_channel_template( $template_name ){
	return dirname( __FILE__ ) . '/templates/' .  $template_name ;
}

?>