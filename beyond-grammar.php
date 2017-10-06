<?php
/*
   Plugin Name: BeyondGrammar for WordPress
   Plugin URI:  http://www.prowritingaid.com
   Description: More than just a spell/grammar check, Pro Writing Aid provides a thorough analysis of your writing to help you improve it. It highlights: sticky sentences; overused, vagues, and abstract words; repeated words and phrases, and more. Improve your writing easily in 5 minutes.
   Author:      @ProWritingAid
   Version:     0.1
   Author URI:  http://www.prowritingaid.com
*/

/*
menu_slug = 

*/

$bg_menuSlug = "beyondgrammar";
$bg_optionGroup = "beyondgrammar_options";
$bg_optionName  = "beyondgrammar_options";
$bg_sectionId   = "beyondgrammar_main";
$bg_page = "beyondgrammar";

$bg_opts_licenseCode = 'licenceCode_string'; //Do not touch yet
$bg_input_licenceCode = "{$bg_optionName}[{$bg_opts_licenseCode}]";

function bg_startPlugin(){
    add_action( 'admin_init', 'bg_AddAdminSettings' );
    add_action( 'admin_menu', 'bg_AddPluginMenu' );       
}

////////////
///Settings

/**
* Register setting in WP core
*/
function bg_AddAdminSettings(){
    global $bg_optionGroup, $bg_optionName, $bg_page, $bg_sectionId, $bg_opts_licenseCode;
    
    register_setting( $bg_optionGroup, $bg_optionName, 'bg_ValidateOptions' );
    add_settings_section($bg_sectionId, 'License Settings', 'bg_AddSettingsSectionHtml', $bg_page);	
    add_settings_field($bg_opts_licenseCode, 'License Code', 'bg_AddLicenseCodeSettingsFieldHtml', $bg_page, $bg_sectionId);
}

/**
* Callback for validation option
*/
function bg_ValidateOptions($input) {
    global $bg_opts_licenseCode;
    //{11111111-1111-1111-1111-111111111111}
	$newinput[$bg_opts_licenseCode] = trim($input[$bg_opts_licenseCode]);
	if(!preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/', $newinput[$bg_opts_licenseCode])) {
		$newinput[$bg_opts_licenseCode] = 'Invalid license code';
	}
	return $newinput;
}

/**
* Returns html for settings section
*/
function bg_AddSettingsSectionHtml() { }

/**
* Returns html for input license code
*/
function bg_AddLicenseCodeSettingsFieldHtml() {
    global $bg_optionGroup, $bg_opts_licenseCode, $bg_input_licenceCode, $bg_page;
    
	$options = get_option($bg_optionGroup);
	$licenceCode = '';
	if (array_key_exists ($bg_opts_licenseCode, $options)){
		$licenceCode = $options[$bg_opts_licenseCode];
	}
	
	echo "<input id='${$bg_page}_{$bg_opts_licenseCode}' name='{$bg_input_licenceCode}' size='40' type='text' value='{$licenceCode}' />";
}

////////////////
//Plugin menu

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
    global $bg_opts_licenseCode, $bg_optionGroup, $bg_page;

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo('<h2>BeyondGrammar Settings</h2>');
		
	echo('	<form action="options.php" method="post">');
	settings_fields($bg_optionGroup); //?????
	do_settings_sections($bg_page); //?????
	echo("<input name='Submit' type='submit' class='button-primary' value='Save Changes' />");
	echo('</form>');
	echo '</div>';
}


bg_startPlugin();
?>