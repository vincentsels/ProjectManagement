<?php

function print_pm_reports_menu( $p_page = '' ) {
	$t_pm_time_registration_page = plugin_page( 'time_registration_page' );
	$t_pm_report_registration_page = plugin_page( 'report_registration_page' );
	$t_pm_resource_allocation_page = plugin_page( 'resource_allocation_page' );
	$t_pm_resource_management_page = plugin_page( 'resource_management_page' );

	switch( plugin_page( $p_page ) ) {
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
	$t_enum_values = MantisEnum::getAssocArrayIndexedByValues( $t_config_var_value );

	foreach ( $t_enum_values as $t_key => $t_value ) {
		echo '<option value="' . $t_key . '"';
		check_selected( $p_val, $t_key );
		echo '>' . $t_value . '</option>';
	}
}

?>