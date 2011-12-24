<?php

function print_pm_reports_menu( $p_page = '' ) {
	$t_pm_report_registration_page = plugin_page( 'report_registration_page' );
	$t_pm_resource_allocation_page = plugin_page( 'resource_allocation_page' );
	$t_pm_resource_management_page = plugin_page( 'resource_management_page' );

	switch( plugin_page( $p_page ) ) {
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
	print_bracket_link( $t_pm_report_registration_page, plugin_lang_get( 'time_registration' ) );
	print_bracket_link( $t_pm_resource_allocation_page, plugin_lang_get( 'resource_allocation' ) );
	print_bracket_link( $t_pm_resource_management_page, plugin_lang_get( 'resource_management' ) );
	echo '</p></div>';
}

?>