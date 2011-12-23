<?php

function print_pm_reports_menu( $p_page = '' ) {
	$t_pm_report_registration_page = plugin_page( 'report_registration_page' );
	$t_pm_report_timeline_page = plugin_page( 'resource_allocation_page' );

	switch( plugin_page( $p_page ) ) {
		case $t_pm_report_registration_page:
			$t_pm_report_registration_page = '';
			break;
		case $t_pm_report_timeline_page:
			$t_pm_report_timeline_page = '';
			break;
	}

	echo '<div align="center"><p>';
	print_bracket_link( $t_pm_report_registration_page, plugin_lang_get( 'time_registration' ) );
	print_bracket_link( $t_pm_report_timeline_page, plugin_lang_get( 'resource_allocation' ) );
	echo '</p></div>';
}

?>