<?php

function print_pm_reports_menu( $p_page = '' ) {
	$t_pm_report_registration_page = 'report_registration_page.php';
	$t_pm_report_timeline_page = 'report_timeline_page.php';

	switch( $p_page ) {
		case $t_pm_report_registration_page:
			$t_pm_report_registration_page = '';
			break;
		case $t_pm_report_timeline_page:
			$t_pm_report_timeline_page = '';
			break;
	}

	echo '<div align="center"><p>';
	print_bracket_link( helper_mantis_url( $t_pm_report_registration_page ), plugin_lang_get( 'report_registration' ) );
	print_bracket_link( helper_mantis_url( $t_pm_report_timeline_page ), plugin_lang_get( 'report_timeline' ) );
	echo '</p></div>';
}

?>