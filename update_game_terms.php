<?php
/*
** update_game_terms.php
** @description: gets all the games from the twitch api and reflects them as terms in our game taxonomy
*/

require_once( explode( "wp-content" , __FILE__ )[0] . "wp-load.php" );

// twitch client id for rate limit
$client_id = "bvppwo05xerpyyfaolsgsy1njce52lj";
$limit = 100;

$url = 'https://api.twitch.tv/kraken/games/top/?client_id='.$client_id.'&limit=1';

$response = file_get_contents($url);
$data = json_decode($response);	

// determine how many games we need to retrieve
$total_games_amount = $data->_total;

// determine amount of calls we need to make to get all games
$amount_of_api_calls = ceil($data->_total / $limit);
// $amount_of_api_calls = 1;

$counter = 0;
// loop all games
while( $counter < $amount_of_api_calls ){
	echo $counter.'</br>';
	$offset = $counter * $limit;
	$url = 'https://api.twitch.tv/kraken/games/top/?client_id='.$client_id.'&limit='.$limit.'&offset='.$offset;
	$response = file_get_contents($url);
	$games = json_decode($response);
	
	foreach($games->top as $game){

		$term = wp_insert_term( $game->game->name, 'game' );
		if ( gettype($term) != 'object' ){
			// if this game is being added the first time, save the twitch id as term_meta
			update_term_meta( $term['term_id'], 'twitch_id', $game->game->_id );
		}
		
	}
	
	$counter++;
}

?>