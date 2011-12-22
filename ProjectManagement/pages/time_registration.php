<?php

form_security_validate( 'plugin_ProjectManagement_time_registration' );

$t_worktypes = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'worktypes' ) );

$f_bug_id		= gpc_get_int( 'bug_id' );
$f_book_date	= strtotime( str_replace('/', '-', gpc_get_string( 'book_date', date('d/m/Y') ) ) );
$f_data			= array(PLUGIN_PM_EST => array(), PLUGIN_PM_DONE => array(), PLUGIN_PM_TODO => array());

# Populate an array with the supplied data
foreach ( $t_worktypes as $t_worktype_code => $t_worktype_label ) {
	error_parameters( $t_worktype_label );
	$f_data[PLUGIN_PM_EST][$t_worktype_code] = time_to_minutes( gpc_get_string( 'change_' . PLUGIN_PM_EST . '_' . $t_worktype_code, null ) );
	$f_data[PLUGIN_PM_DONE][$t_worktype_code] = time_to_minutes( gpc_get_string( 'add_' . PLUGIN_PM_DONE . '_' . $t_worktype_code, null ) );
	$f_data[PLUGIN_PM_TODO][$t_worktype_code] = time_to_minutes( gpc_get_string( 'change_' . PLUGIN_PM_TODO . '_' . $t_worktype_code, null ) );
	$f_data['clear_todo'][$t_worktype_code] = gpc_get_bool( 'clear_' . PLUGIN_PM_TODO . '_' . $t_worktype_code, null );
}

# Handle estimations: insert or update
foreach ( $f_data[PLUGIN_PM_EST] as $t_worktype => $t_minutes ) {
	if ( !empty( $t_minutes ) ) {
		set_work( $f_bug_id, $t_worktype, PLUGIN_PM_EST, $t_minutes, $f_book_date, Action::INSERT_OR_UPDATE );
	}
}

# Handle done: insert
foreach ( $f_data[PLUGIN_PM_DONE] as $t_worktype => $t_minutes ) {
	if ( !empty( $t_minutes ) ) {
		set_work( $f_bug_id, $t_worktype, PLUGIN_PM_DONE, $t_minutes, $f_book_date, Action::INSERT );
	}
}

# Handle todo: insert or update
foreach ( $f_data[PLUGIN_PM_TODO] as $t_worktype => $t_minutes ) {
	if ( !empty( $t_minutes ) ) {
		set_work( $f_bug_id, $t_worktype, PLUGIN_PM_TODO, $t_minutes, $f_book_date, Action::INSERT_OR_UPDATE );
	}
}

# Handle clearing of todo: delete
foreach ( $f_data['clear_todo'] as $t_worktype => $t_minutes ) {
	if ( !empty( $t_minutes ) ) {
		set_work( $f_bug_id, $t_worktype, PLUGIN_PM_TODO, $t_minutes, $f_book_date, Action::DELETE );
	}
}

form_security_purge( 'plugin_ProjectManagement_time_registration');

$t_url = string_get_bug_view_url( $f_bug_id, auth_get_current_user_id() );
print_successful_redirect( $t_url . "#time_registration" );

?>