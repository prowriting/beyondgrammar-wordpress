<?php
/*
   Plugin Name: BeyondGrammar for WordPress
   Plugin URI:  https://github.com/prowriting/beyondgrammar-wordpress
   Description: Bring real-time spelling, grammar and style checking into WordPress, the world's most popular content management system.
   Author:      @ProWritingAid
   Version:     0.1
   Author URI:  https://github.com/prowriting/beyondgrammar-wordpress
*/

$bg_menuSlug = "beyondgrammar";
$bg_optionGroup = "beyondgrammar_options";
$bg_optionName  = "beyondgrammar_options";
$bg_sectionId   = "beyondgrammar_main";
$bg_page = "beyondgrammar";

$bg_opts_apiKey = 'beyondgrammar_apiKey';
$bg_input_apiKey = "{$bg_optionName}[{$bg_opts_apiKey}]";
$bg_version = "1.0.18";

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Entry Point  //////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function bg_startPlugin(){
    add_action( 'init',       'bg_PatchEditor' );
    add_action( 'admin_init', 'bg_AddAdminSettings' );
    add_action( 'admin_menu', 'bg_AddPluginMenu' );
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Editor patches ////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
* Added filters related to configuring tinymce editor
*/
function bg_PatchEditor(){
    // Don't bother doing this stuff if the current user lacks permissions and only in RichEditor mode
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') && get_user_option('rich_editing') != 'true'){
        return;
    }
    
    add_filter('tiny_mce_before_init', 'bg_AddBeyondGrammarSettings' );
    add_filter("tiny_mce_version", "bg_SetupTinyMCEPlugin" );
    add_filter("mce_external_plugins", "bg_LoadBeyondGrammarMCEPlugin");
    add_filter("mce_buttons", "bg_AddBeyondGrammarButton");
}

/**
* Adding settings to the BeyondGrammar options
*/
function bg_AddBeyondGrammarSettings($settings){
    global $bg_optionGroup, $bg_opts_apiKey, $bg_version;
    
    $options = get_option($bg_optionGroup);
    $apiKey = '';
    if (array_key_exists ($bg_opts_apiKey, $options)){
        $apiKey = $options[$bg_opts_apiKey];
    }
    
    $settings['bgOptions'] = wp_json_encode(array(
        'service' => array(
            'apiKey'=>$apiKey,
            'i18n'=>array(
                'en'=>"https://prowriting.azureedge.net/beyondgrammar-tinymce/{$bg_version}/dist/i18n-en.js"
            )
        )
    ));
    
    return $settings;
}

/**
* Sets url to BeyondGrammar TinyMCE plugin
*/
function bg_LoadBeyondGrammarMCEPlugin($plugin_array){
    global $bg_version;
    $plugin_array['BeyondGrammar'] = "https://prowriting.azureedge.net/beyondgrammar-tinymce/{$bg_version}/dist/beyond-grammar-plugin.js";
    return $plugin_array;
}

/**
* Adds BeyondGrammar to TinyMCE plugin toolbar
*/
function bg_AddBeyondGrammarButton($buttons){
    array_push($buttons, "separator", "BeyondGrammar");
    return $buttons;
}


function bg_SetupTinyMCEPlugin($version) {
    return $version+2;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Settings //////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
* Register setting in WP core
*/
function bg_AddAdminSettings(){
    global $bg_optionGroup, $bg_optionName, $bg_page, $bg_sectionId, $bg_opts_apiKey;
    
    register_setting( $bg_optionGroup, $bg_optionName, 'bg_ValidateOptions' );
    add_settings_section($bg_sectionId, 'License Settings', 'bg_AddSettingsSectionHtml', $bg_page);	
    add_settings_field($bg_opts_apiKey, 'Api Key', 'bg_AddApiKeySettingsFieldHtml', $bg_page, $bg_sectionId);
}

/**
* Callback for validation option
*/
function bg_ValidateOptions($input) {
    global $bg_opts_apiKey;
	$newinput[$bg_opts_apiKey] = trim($input[$bg_opts_apiKey]);
	if(!preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/', $newinput[$bg_opts_apiKey])) {
		$newinput[$bg_opts_apiKey] = 'Invalid Api Key';
	}
	return $newinput;
}

/**
* Returns html for settings section
*/
function bg_AddSettingsSectionHtml() { }

/**
* Returns html for input api key
*/
function bg_AddApiKeySettingsFieldHtml() {
    global $bg_optionGroup, $bg_opts_apiKey, $bg_input_apiKey, $bg_page;
    
	$options = get_option($bg_optionGroup);
	$apiKey = '';
	if (array_key_exists ($bg_opts_apiKey, $options)){
		$apiKey = $options[$bg_opts_apiKey];
	}
	
	echo "<input id='${$bg_page}_{$bg_opts_apiKey}' name='{$bg_input_apiKey}' size='40' type='text' value='{$apiKey}' />";
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Plugin menu ///////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
* Add options page in WP core
*/
function bg_AddPluginMenu() {
    global $bg_menuSlug;
	add_options_page( 'BeyondGrammar Options', 'BeyondGrammar', 'manage_options', $bg_menuSlug, 'bg_AddPluginMenuHtml' );
}

/**
* Return html for saving options in WP core
*/
function bg_AddPluginMenuHtml() {
    global $bg_opts_apiKey, $bg_optionGroup, $bg_page;

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo('<h2>BeyondGrammar Settings</h2>');
		
	echo('	<form action="options.php" method="post">');
	settings_fields($bg_optionGroup);
	do_settings_sections($bg_page);
	echo("<input name='Submit' type='submit' class='button-primary' value='Save Changes' />");
	echo('</form>');
	echo '</div>';
}

bg_startPlugin();

?>