<?php
/*
** update.php
** @description: updates all twitch channels and their online status in our db*
** @package: twitch_database
**
*/
function update_stream_status(){
	$time_start = microtime(true); 
	// include wp_load so we can access wpdb
	require_once( explode( "wp-content" , __FILE__ )[0] . "wp-load.php" );
	
	// retrieve twitch client id from the plugin options in the backend
	$options = get_option('twitch_database_plugin_options');
	$client_id = $options['twitch_client_id'];

	// urls per api call
	$urls_per_call = 110;

	global $wpdb;
	$channels = $wpdb->get_results( "SELECT ID, post_title FROM wp_posts WHERE post_type = 'twitch_channel' AND post_status = 'publish'");

	$post_ids = array();

	if( ! ini_get('allow_url_fopen')){
		// we can't access the twitch api if allow_url_fopen is disabled
		die( 'The twitch API can not be accessed if allow_url_fopen is Off. Please enable it in your php.ini');
	}

	$channel_amount = count( $channels );
	if ( $channel_amount > 0){
		// create the url
		$url = 'https://api.twitch.tv/kraken/streams/?channel=';
				
		// determine amount of twitch requests
		$url_count = ceil( $channel_amount / $urls_per_call );
				
		$urls = array();
		for($i = 1; $i <= $url_count; $i++){
			$urls[$i] = 'https://api.twitch.tv/kraken/streams/?channel=';
		}
		$counter = 0;
		
		foreach($channels as $channel){
			
			
			$counter++;
			$channel_name = strtolower( $channel->post_title );
			
			$post_ids[$channel_name] = $channel->ID;
			
			$url_number = ceil( $counter / $urls_per_call );
			$urls[$url_number] .= $channel_name.',';
			
			if( $counter % $urls_per_call === 0 || $counter === $channel_amount ){
				$urls[$url_number] .= '&client_id='.$client_id;
			}
							
			// create an offline array object for every streamer - this is then replaced by the online status if the streamer is found in the response
			$channels_array[$channel_name] = array('name' => $channel_name, 'status' => 'offline');
			
		}
		foreach ($urls as $url){
			// get the stream objects
			$response = file_get_contents($url);
			$data = json_decode($response);	
				
			foreach($data->streams as $stream){
				
				$channel_name = $stream->channel->name;
				
				// retrieve post id 
				$post_id = $post_ids[$channel_name];
				
				$viewers = $stream->viewers;
				update_post_meta($post_id, 'viewers', $viewers);
				
				$game = $stream->game;
				update_post_meta($post_id, 'game', $game);
				// determine if there is a term for this game already
				$term = get_term_by( 'name', $game, 'game' );
				if( $term ){
					// if term exists, assign it to the channel
					wp_set_object_terms( $post_id, $term->term_id, 'game');
				}
				
				// stream is online because otherwise we would not have gotten a response
				$status = "online";
				update_post_meta($post_id, 'online_status', 'online');
				
				$displayname = $stream->channel->display_name;
				update_post_meta($post_id, 'display_name', $displayname);
				
				$medium_thumbnail = $stream->preview->medium;
				update_post_meta($post_id, 'medium_thumbnail', $medium_thumbnail);
				
				$large_thumbnail = $stream->preview->large;
				
				$stream_title = $stream->channel->status;
				update_post_meta($post_id, 'stream_title', $stream_title);
				
				$last_seen_online = time();
					
				// save thumbs to files
				// medium thumb to file
				$url = $medium_thumbnail;

				$img = wp_upload_dir()['basedir'] . '/channel_thumbs/' . $channel_name . '_medium_thumb.jpg';
				
				$file = '';
				$file = file_get_contents($url);

				$hash = '';
				$hash = hash('md5', $file);
				// only save thumb if hash is not equal to the medium 404 image
				if($hash != '690c33919a7b72fba3e93693f7db51b7' && $hash != '441b54a77d4021199a96ca147d6991ac'){
					file_put_contents($img, $file);
				}

				// large thumb to file
				$url = $large_thumbnail;
				$img = wp_upload_dir()['basedir'] . '/channel_thumbs/' . $channel_name . '_large_thumb.jpg';
				$file = '';
				$file = file_get_contents($url);
				$hash = '';
				$hash = hash('md5', $file);
				// only save thumb if hash is not equal to the large 404 image
				if($hash != 'd2fbf1fd9c91803bb2e729cf283aa1c8'){
					file_put_contents($img, $file);
				}
					
				// set this channel to online so it won't be overwritten in the offline channels foreach
				$channels_array[$channel_name]['status'] = 'online';
									
				echo $viewers.' '.$game.' '.$status.' '.$medium_thumbnail.' '.$stream_title.' </br>';
			}
		}
	}
			
					

	// set all offline streamers to offline in db
	foreach($channels_array as $channel_name => $channel){
		// retrieve post id
		$post_id = $post_ids[$channel_name];
		if( $channel['status'] === 'offline'){
			update_post_meta($post_id, 'online_status', 'offline');
			update_post_meta($post_id, 'viewers', 0);
		}
	}
		
		
	$time_end = microtime(true);

	//dividing with 60 will give the execution time in minutes other wise seconds
	$execution_time = ($time_end - $time_start);

	//execution time of the script
}	
?>