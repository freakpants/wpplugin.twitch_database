<?php
/* Options Page for the Twitch Database Plugin */

 // add the admin options page
add_action('admin_menu', 'plugin_admin_add_page');
function plugin_admin_add_page() {
	add_options_page('Twitch Database Settings', 'Twitch Database Settings', 'manage_options', 'plugin', 'plugin_options_page');
}

// add the admin settings and such
add_action('admin_init', 'plugin_admin_init');
function plugin_admin_init(){
	register_setting( 'plugin_options', 'plugin_options', 'plugin_options_validate' );
	add_settings_section('plugin_main', 'Main Settings', 'plugin_section_text', 'plugin');
	add_settings_field('plugin_twitch_client_id', 'Twitch API Client ID', 'plugin_setting_string', 'plugin', 'plugin_main');
}

 function plugin_setting_string() {
	$options = get_option('plugin_options');
	echo "<input id='plugin_twitch_client_id' name='plugin_options[twitch_client_id]' size='40' type='text' value='{$options['twitch_client_id']}' />";
}

function plugin_section_text() {
echo '<p>Main description of this section here.</p>';
}

function plugin_options_validate($input) {
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