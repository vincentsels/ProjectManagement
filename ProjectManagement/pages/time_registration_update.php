<?php

form_security_validate( 'plugin_ProjectManagement_time_registration_update' );

$t_work_types = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );

$f_bug_id		= gpc_get_int( 'bug_id' );
$f_book_date	= strtotime( str_replace('/', '-', gpc_get_string( 'book_date', date('d/m/Y') ) ) );
$f_data			= array(PLUGIN_PM_EST => array(), PLUGIN_PM_DONE => array(), PLUGIN_PM_TODO => array());

$t_table = plugin_table('work');

# Populate an array with the supplied data
foreach ( $t_work_types as $t_work_type_code => $t_work_type_label ) {
	error_parameters( $t_work_type_label );
	$f_data[PLUGIN_PM_EST][$t_work_type_code] = time_to_minutes( gpc_get_string( 'change_' . PLUGIN_PM_EST . '_' . $t_work_type_code, null ), false );
	$f_data[PLUGIN_PM_DONE][$t_work_type_code] = time_to_minutes( gpc_get_string( 'add_' . PLUGIN_PM_DONE . '_' . $t_work_type_code, null ) );
	$f_data[PLUGIN_PM_TODO][$t_work_type_code] = time_to_minutes( gpc_get_string( 'change_' . PLUGIN_PM_TODO . '_' . $t_work_type_code, null ), false );
	$f_data['clear_todo'][$t_work_type_code] = gpc_get_bool( 'clear_' . PLUGIN_PM_TODO . '_' . $t_work_type_code, null );
}

# Handle estimations: insert or update
foreach ( $f_data[PLUGIN_PM_EST] as $t_work_type => $t_minutes ) {
	if ( !empty( $t_minutes ) ) {
		
		# Get the old value
		$t_minutes_type = PLUGIN_PM_EST;
		$t_query = "SELECT minutes FROM $t_table
		WHERE bug_id = $f_bug_id AND work_type = $t_work_type AND minutes_type = $t_minutes_type AND minutes > 0";
		$t_result = db_query_bound( $t_query );
		$t_row = db_fetch_array( $t_result );
		$t_old_value = ( $t_row == false ? null : round( $t_row["minutes"] / 60, 2 ) );
		$t_new_value = round( $t_minutes / 60, 2 );
		
		# Security repeated: check whether estimations may be modified!
		if ( !access_has_bug_level( plugin_config_get( 'edit_estimates_threshold' ), $f_bug_id ) ) {
			# There may not yet be an estimate for this bug!
			if ( $t_num_result > 0 ) {
				continue;
			}
		}
		
		set_work( $f_bug_id, $t_work_type, PLUGIN_PM_EST, $t_minutes, $f_book_date, Action::INSERT_OR_UPDATE );
		
		history_log_event_direct( $f_bug_id, plugin_lang_get( 'est' ) . " ($t_work_types[$t_work_type])", 
				$t_old_value, $t_new_value );
	}
}

# Handle done: insert
foreach ( $f_data[PLUGIN_PM_DONE] as $t_work_type => $t_minutes ) {
	if ( !empty( $t_minutes ) ) {
		
		# Get the old value
		$t_minutes_type = PLUGIN_PM_DONE;
		$t_query = "SELECT sum(minutes) as minutes FROM $t_table
		WHERE bug_id = $f_bug_id AND work_type = $t_work_type AND minutes_type = $t_minutes_type";
		$t_result = db_query_bound( $t_query );
		$t_row = db_fetch_array( $t_result );
		$t_old_value = ( $t_row == false ? null : round( $t_row["minutes"] / 60, 2 ) );
		$t_hours = round( $t_minutes / 60, 2 );
		$t_new_value = $t_old_value + $t_hours;
		
		# Extra check: negative totals not possible!
		if ( $t_new_value < 0 ) {
			error_parameters( $t_work_types[$t_work_type] );
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, E_USER_ERROR );
		}
		
		set_work( $f_bug_id, $t_work_type, PLUGIN_PM_DONE, $t_minutes, $f_book_date, Action::INSERT );
		
		if ( $t_hours < 0 ) {
			$t_sign = ' - ';
		} else {
			$t_sign = ' + ';
		}
		$t_change = $t_old_value . $t_sign . abs($t_hours);
		history_log_event_direct( $f_bug_id, plugin_lang_get( 'done' ) . " ($t_work_types[$t_work_type])", 
				$t_change, $t_new_value );
	}
}

# Handle todo: insert or update
foreach ( $f_data[PLUGIN_PM_TODO] as $t_work_type => $t_minutes ) {
	if ( !empty( $t_minutes ) ) {
		
		# Get the old value
		$t_minutes_type = PLUGIN_PM_TODO;
		$t_query = "SELECT minutes FROM $t_table
		WHERE bug_id = $f_bug_id AND work_type = $t_work_type AND minutes_type = $t_minutes_type AND minutes > 0";
		$t_result = db_query_bound( $t_query );
		$t_row = db_fetch_array( $t_result );
		$t_old_value = ( $t_row == false ? null : round( $t_row["minutes"] / 60, 2 ) );
		$t_new_value = round( $t_minutes / 60, 2 );
		
		set_work( $f_bug_id, $t_work_type, PLUGIN_PM_TODO, $t_minutes, $f_book_date, Action::INSERT_OR_UPDATE );
		
		history_log_event_direct( $f_bug_id, plugin_lang_get( 'todo' ) . " ($t_work_types[$t_work_type])", 
				$t_old_value, $t_new_value );
	}
}

# Handle clearing of todo: delete
foreach ( $f_data['clear_todo'] as $t_work_type => $t_delete ) {
	if ( !empty( $t_delete ) && $t_delete ) {
		
		# Get the old value
		$t_minutes_type = PLUGIN_PM_TODO;
		$t_query = "SELECT minutes FROM $t_table
		WHERE bug_id = $f_bug_id AND work_type = $t_work_type AND minutes_type = $t_minutes_type AND minutes > 0";
		$t_result = db_query_bound( $t_query );
		$t_row = db_fetch_array( $t_result );
		$t_old_value = ( $t_row == false ? null : round( $t_row["minutes"] / 60, 2 ) );
		$t_new_value = plugin_lang_get( 'clear' );
		
		set_work( $f_bug_id, $t_work_type, PLUGIN_PM_TODO, $t_minutes, $f_book_date, Action::DELETE );
		
		history_log_event_direct( $f_bug_id, plugin_lang_get( 'todo' ) . " ($t_work_types[$t_work_type])", 
				$t_old_value, $t_new_value );
	}
}

form_security_purge( 'plugin_ProjectManagement_time_registration_update');

$t_url = string_get_bug_view_url( $f_bug_id, auth_get_current_user_id() );
print_successful_redirect( $t_url . "#time_registration" );

?>