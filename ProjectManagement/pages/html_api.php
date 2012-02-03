<?php

function print_pm_reports_menu( $p_page = '' ) {
	$t_pm_time_registration_page   = plugin_page( 'time_registration_page' );
	$t_pm_report_registration_page = plugin_page( 'report_registration_page' );
	$t_pm_resource_allocation_page = plugin_page( 'resource_allocation_page' );
	$t_pm_resource_management_page = plugin_page( 'resource_management_page' );

	switch ( plugin_page( $p_page ) ) {
		case $t_pm_time_registration_page:
			$t_pm_time_registration_page = '';
			break;
		case $t_pm_report_registration_page:
			$t_pm_report_registration_page = '';
			break;
		case $t_pm_resource_allocation_page:
			$t_pm_resource_allocation_page = '';
			break;
		case $t_pm_resource_management_page:
			$t_pm_resource_management_page = '';
			break;
	}

	echo '<div align="center"><p>';
	if ( access_has_global_level( plugin_config_get( 'view_registration_worksheet_threshold' ) ) ) {
		print_bracket_link( $t_pm_time_registration_page, plugin_lang_get( 'time_registration_worksheet' ) );
	}
	if ( access_has_global_level( plugin_config_get( 'view_registration_report_threshold' ) ) ) {
		print_bracket_link( $t_pm_report_registration_page, plugin_lang_get( 'time_registration_overview' ) );
	}
	if ( access_has_global_level( plugin_config_get( 'view_resource_allocation_threshold' ) ) ) {
		print_bracket_link( $t_pm_resource_allocation_page, plugin_lang_get( 'resource_allocation' ) );
	}
	if ( access_has_global_level( plugin_config_get( 'view_resource_management_threshold' ) ) ) {
		print_bracket_link( $t_pm_resource_management_page, plugin_lang_get( 'resource_management' ) );
	}
	echo '</p></div>';
}

function print_plugin_enum_string_option_list( $p_enum_name, $p_val = 0 ) {
	$t_config_var_value = plugin_config_get( $p_enum_name );
	$t_enum_values      = MantisEnum::getAssocArrayIndexedByValues( $t_config_var_value );

	foreach ( $t_enum_values as $t_key => $t_value ) {
		echo '<option value="' . $t_key . '"';
		check_selected( $p_val, $t_key );
		echo '>' . $t_value . '</option>';
	}
}

function print_expand_icon_start( $p_div_id ) {
	echo '<a class="subtle" href="#" onclick="ShowOrHide( \'' . $p_div_id . '\' ); return false; ">
	<img id="' . $p_div_id . '_img" border="0" src="images/plus.png" alt="+" />&nbsp;';
}

function print_expand_icon_end() {
	echo '</a>';
}


function print_expandable_div_start( $p_div_id ) {
	echo '<div id="' . $p_div_id . '" class="hidden content-list">';
}

function print_expandable_div_end() {
	echo '</div>';
}

/**
 * Returns the specified color to be used in css elements that accept a color attribute like background-color.
 * It includes a trailing semi-colon. You must still include style="background-color:<...>" or similar.
 * It uses to the specified hue (default green), and in the specified style.
 * @param int $p_hue Hue value between 0 and 360 (http://msdn.microsoft.com/en-us/library/ms531197(v=vs.85).aspx#rgba)
 * @param int $p_style should be PLUGIN_PM_DARK (default) or PLUGIN_PM_LIGHT.
 * @param string $p_additional_style optionally supply additional style to be added after the color.
 */
function print_background_color( $p_hue = 120, $p_style = PLUGIN_PM_DARK ) {
	$t_h = $p_hue;
	$t_s = ( $p_style == PLUGIN_PM_DARK ? plugin_config_get( 'dark_saturation' ) : plugin_config_get( 'light_saturation' ) );
	$t_l = ( $p_style == PLUGIN_PM_DARK ? plugin_config_get( 'dark_lightness' ) : plugin_config_get( 'light_lightness' ) );

	echo "hsl($t_h, $t_s%, $t_l%);";
}

/**
 * Prints a transparent red overlay.
 */
function print_overdue_color() {
	echo "rgba(255, 0, 0, 0.5);";
}

/**
 * Prints a color selection option list with 15 available colors.
 * @param int $p_val the value to select.
 */
function print_color_option_list( $p_val = 0 ) {
	for ( $i = 0; $i < 360; $i += 20 ) {
		echo '<option style="background-color:"', print_background_color( $i ), '" value="' . $i . '"';
		check_selected( $i, $p_val );
		echo '>' . $i . str_repeat( '&nbsp;', 10 ) . '</option>';
	}
}

?>