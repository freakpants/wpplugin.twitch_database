<?php
/* Options Page for the Twitch Database Plugin */

global $option_name;
$option_name = 'twitch_database_plugin_options';

 // add the admin options page
add_action('admin_menu', 'plugin_admin_add_page');
function plugin_admin_add_page() {
	// add_options_page( $page_title, $menu_title, $capability , $menu_slug , $function)
	add_options_page( 'Twitch Database Settings', 'Twitch Database Settings', 'manage_options' , 'plugin' , 'plugin_options_page');
}

// add the admin settings and such
add_action('admin_init', 'plugin_admin_init');
function plugin_admin_init(){
	global $option_name;
	// register_setting( $option_group , $option_name , $sanitize_callback );
	register_setting( 'plugin_options' , $option_name , 'plugin_options_validate' );
	
	// add_settings_section( $id, $title, $callback, $page );
	add_settings_section('plugin_main', 'Main Settings', 'plugin_section_text', 'plugin');
	
	// add_settings_field( $id, $title, $callback, $page, $section, $args );
	add_settings_field('plugin_twitch_client_id', 'Twitch API Client ID', 'plugin_setting_string', 'plugin', 'plugin_main');
	add_settings_field('plugin_channels_overview_page_id', 'Overview Page of all Channels', 'overview_page_selection', 'plugin', 'plugin_main');
}

// display a select containing all pages, to select as the overview page of channels
function overview_page_selection(){
	global $option_name;
	$options = get_option( $option_name );
	// args array for wp_dropdown_pages
	$args = array( 
		'name' => $option_name . '[channels_overview_page_id]',
		'id' => 'plugin_channels_overview_page_id',
		'sort_column' => 'menu_order',
		'sort_order' => 'ASC',
		'show_option_none' => ' ',
		'class' =>  'twitch_database_select',
		'echo' => true,
		'selected' => $options['channels_overview_page_id']);
	wp_dropdown_pages( $args );
}

function plugin_setting_string() {
	global $option_name;
	$options = get_option('twitch_database_plugin_options');
	echo "<input id='plugin_twitch_client_id' name='" . $option_name . "[twitch_client_id]' size='40' type='text' value='{$options['twitch_client_id']}' />";
}

function plugin_section_text() {
	echo '<p>Main description of this section here.</p>';
}

function plugin_options_validate($input) {
	$newinput = $input;
	$newinput['twitch_client_id'] = trim($input['twitch_client_id']);
	if(!preg_match('/^[a-z0-9]*$/i', $newinput['twitch_client_id'])) {
		$newinput['twitch_client_id'] = '';
	}
	return $newinput;
}

// display the admin options page
function plugin_options_page() {
	?>
	<div>
	<h2>Twitch Database Settings</h2>
	<b>Status of allow_url_fopen:</b>
	<?php
	if ( ini_get('allow_url_fopen') ){
		echo '<b><span style="color:green">On</span></b>';
	} else {
		echo '<b><span style="color:red">Off</span></b>';
	}
	?>
	<form action="options.php" method="post">
	<?php settings_fields('plugin_options'); ?>
	<?php do_settings_sections('plugin'); ?>
	<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
	</form>

	</div>
	 <?php
}?>