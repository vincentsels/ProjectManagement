<?php

form_security_validate( 'plugin_ProjectManagement_time_registration_update' );

$t_work_types = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );

$f_bug_ids       = gpc_get_int_array( 'bug_ids' );
$f_redirect_page = gpc_get_string( 'redirect_page', null );
$f_book_date     = strtotime_safe( gpc_get_string( 'book_date', date( config_get( 'short_date_format' ) ) ) );
$f_data          = array();
$f_time_info     = gpc_get_string( 'pm_time_info', null );
 
$t_table = plugin_table( 'work' );
$t_view_mode = plugin_config_get( 'bug_view_mode' );
$t_view_mode = ( $t_view_mode === null ? PLUGIN_PM_BUG_VIEW_MODE_1 : $t_view_mode);

# Populate an array with the supplied data
foreach ( $f_bug_ids as $t_bug_id ) {

	$t_work_types_to_check = array();
	if ( $f_redirect_page == 'time_registration_page' ) {
		# Retrieve the work types of all the bugs, should always be supplied
		$t_bug_work_type                         = gpc_get_int( 'work_type_' . $t_bug_id, plugin_config_get( 'default_worktype' ) );
		$t_work_types_to_check[$t_bug_work_type] = $t_work_types[$t_bug_work_type];

	} else {
		if ( $t_view_mode == PLUGIN_PM_BUG_VIEW_MODE_2 ) {
			$t_bug_work_type                         = gpc_get_int( 'work_type_' . $t_bug_id );
			$t_work_types_to_check[$t_bug_work_type] = $t_work_types[$t_bug_work_type];
		}
		else {
			$t_work_types_to_check = $t_work_types;
		}
	}

	if ( $t_view_mode == PLUGIN_PM_BUG_VIEW_MODE_2 ) {
		foreach ( $t_work_types_to_check as $t_work_type_code => $t_work_type_label ) {
			error_parameters( $t_work_type_label );
			$f_data[$t_bug_id][PLUGIN_PM_EST][$t_work_type_code]  =
				time_to_minutes(
					gpc_get_string( 'change_' . $t_bug_id . '_' . PLUGIN_PM_EST, null ), false );
			$f_data[$t_bug_id][PLUGIN_PM_DONE][$t_work_type_code] =
				time_to_minutes(
					gpc_get_string( 'add_' . $t_bug_id . '_' . PLUGIN_PM_DONE, null ) );
			$f_data[$t_bug_id][PLUGIN_PM_TODO][$t_work_type_code] =
				time_to_minutes(
					gpc_get_string( 'change_' . $t_bug_id . '_' . PLUGIN_PM_TODO, null ), false );
			$f_data[$t_bug_id][PLUGIN_PM_COMPLETED][$t_work_type_code] =
				time_to_minutes(
					gpc_get_string( 'add_' . $t_bug_id . '_' . PLUGIN_PM_COMPLETED, null ), false );
			$f_data[$t_bug_id]['clear_todo'][$t_work_type_code]   =
				gpc_get_bool( 'clear_' . $t_bug_id . '_' . PLUGIN_PM_TODO, null );
		}
	}
	else {
		foreach ( $t_work_types_to_check as $t_work_type_code => $t_work_type_label ) {
			error_parameters( $t_work_type_label );
			$f_data[$t_bug_id][PLUGIN_PM_EST][$t_work_type_code]  =
				time_to_minutes(
					gpc_get_string( 'change_' . $t_bug_id . '_' . PLUGIN_PM_EST .
						( $f_redirect_page == 'time_registration_page' ? '' : '_' . $t_work_type_code ), null ), false );
			$f_data[$t_bug_id][PLUGIN_PM_DONE][$t_work_type_code] =
				time_to_minutes(
					gpc_get_string( 'add_' . $t_bug_id . '_' . PLUGIN_PM_DONE .
						( $f_redirect_page == 'time_registration_page' ? '' : '_' . $t_work_type_code ), null ) );
			$f_data[$t_bug_id][PLUGIN_PM_TODO][$t_work_type_code] =
				time_to_minutes(
					gpc_get_string( 'change_' . $t_bug_id . '_' . PLUGIN_PM_TODO .
						( $f_redirect_page == 'time_registration_page' ? '' : '_' . $t_work_type_code ), null ), false );
			$f_data[$t_bug_id]['clear_todo'][$t_work_type_code]   =
				gpc_get_bool( 'clear_' . $t_bug_id . '_' . PLUGIN_PM_TODO . '_' . $t_work_type_code, null );
		}
	}
}

foreach ( $f_data as $t_bug_id => $t_bug_data ) {
	if ( count( $t_bug_data[PLUGIN_PM_EST] ) > 0 ) {
		# Handle est: insert or update
		foreach ( $t_bug_data[PLUGIN_PM_EST] as $t_work_type => $t_minutes ) {
			if ( isset( $t_minutes ) ) {

				# Get the old value
				$t_minutes_type = PLUGIN_PM_EST;
				$t_query        = "SELECT minutes
                                     FROM $t_table
				                    WHERE bug_id = $t_bug_id
				                      AND work_type = $t_work_type
				                      AND minutes_type = $t_minutes_type";
				$t_result       = db_query_bound( $t_query );
				$t_row          = db_fetch_array( $t_result );
				$t_old_value    = ( $t_row == false ? null : round( $t_row["minutes"] / 60, 2 ) );
				$t_new_value    = round( $t_minutes / 60, 2 );
				$t_user_id      = auth_get_current_user_id();

				# Security repeated: check whether estimations may be modified!
				if ( !access_has_bug_level( plugin_config_get( 'edit_estimates_threshold' ), $t_bug_id ) ) {
					if ( $t_num_result > 0 ) {
						continue;
					}
				}

				set_work( $t_bug_id, $t_work_type, PLUGIN_PM_EST, $t_minutes, $f_book_date, Action::INSERT, $f_time_info ); //Action::INSERT_OR_UPDATE );
				
				if ( $t_view_mode == PLUGIN_PM_BUG_VIEW_MODE_2 ) {
					target_update_target_date( $t_bug_id, $t_work_type, $t_user_id, $f_book_date );
				}

				history_log_event_direct( $t_bug_id, sprintf( plugin_lang_get( 'history_added' ), $t_work_types[$t_work_type], plugin_lang_get( 'est' ) ),
					$t_old_value, $t_new_value );
			}
		}
	}
	if ( count( $t_bug_data[PLUGIN_PM_DONE] ) > 0 ) {
		# Handle done: insert
		foreach ( $t_bug_data[PLUGIN_PM_DONE] as $t_work_type => $t_minutes ) {
			if ( isset( $t_minutes ) ) {

				$t_minutes_type = PLUGIN_PM_TODO;
				$t_query        = "SELECT minutes
								FROM $t_table
								WHERE bug_id = $t_bug_id
									AND work_type = $t_work_type
									AND minutes_type = $t_minutes_type
								ORDER BY timestamp DESC LIMIT 1";
				$t_result         = db_query_bound( $t_query );
				$t_row_todo       = db_fetch_array( $t_result );
				$t_old_todo       = ( $t_row_todo == false ? null : $t_row_todo["minutes"] );
				$t_old_todo       = ( $t_old_todo === null || $t_old_todo <= 0 ? null : $t_old_todo );
				$t_old_todo_hours = ( $t_old_todo !== null && $t_old_todo > 0 ? round( $t_old_todo / 60, 2 ) : $t_old_todo );
				$t_new_todo       = ( $t_old_todo === null ? null : $t_old_todo - $t_minutes );
				$t_new_todo       = ( $t_new_todo !== null && $t_new_todo < 0 ? 0 : $t_new_todo );
				$t_new_todo_hours = ( $t_new_todo !== null && $t_new_todo > 0 ? round( $t_new_todo / 60, 2 ) : $t_new_todo );

				# Get the old value
				$t_minutes_type = PLUGIN_PM_DONE;
				$t_query        = "SELECT sum( minutes ) as minutes
								FROM $t_table
								WHERE bug_id = $t_bug_id
									AND work_type = $t_work_type
									AND minutes_type = $t_minutes_type";
				$t_result       = db_query_bound( $t_query );
				$t_row          = db_fetch_array( $t_result );
				$t_old_value    = ( $t_row == false ? null : round( $t_row["minutes"] / 60, 2 ) );
				$t_hours        = round( $t_minutes / 60, 2 );
				$t_new_value    = $t_old_value + $t_hours;
				$t_user_id      = auth_get_current_user_id();

				# Extra check: negative totals not possible!
				if ( $t_new_value < 0 ) {
					error_parameters( $t_work_types[$t_work_type] );
					trigger_error( plugin_lang_get( 'time_error' ), E_USER_ERROR );
				}

				set_work( $t_bug_id, $t_work_type, PLUGIN_PM_DONE, $t_minutes, $f_book_date, Action::INSERT, $f_time_info );
				
				if ( $t_view_mode == PLUGIN_PM_BUG_VIEW_MODE_2 && !( $t_new_todo === null ) ) {
					set_work( $t_bug_id, $t_work_type, PLUGIN_PM_TODO, $t_new_todo, $f_book_date, Action::INSERT );
				}
				
				if ( $t_hours < 0 ) {
					$t_sign = ' - ';
				} else {
					$t_sign = ' + ';
				}
				$t_change = $t_old_value . $t_sign . abs( $t_hours );
				history_log_event_direct( $t_bug_id, sprintf( plugin_lang_get( 'history_added' ), $t_work_types[$t_work_type], plugin_lang_get( 'done' ) ),
					$t_change, $t_new_value );
					
				if ( $t_view_mode == PLUGIN_PM_BUG_VIEW_MODE_2 && !( $t_new_todo === null ) ) {
					history_log_event_direct( $t_bug_id, sprintf( plugin_lang_get( 'history_added' ), $t_work_types[$t_work_type], plugin_lang_get( 'todo' ) ),
						$t_old_todo_hours, $t_new_todo_hours );
				}
			}
		}
	}
	
	if ( count( $t_bug_data[PLUGIN_PM_COMPLETED] ) > 0 ) {
		# Handle done: insert
		foreach ( $t_bug_data[PLUGIN_PM_COMPLETED] as $t_work_type => $t_minutes ) {
			if ( isset( $t_minutes ) ) {

				$t_minutes_type = PLUGIN_PM_TODO;
				$t_query        = "SELECT minutes
								FROM $t_table
								WHERE bug_id = $t_bug_id
									AND work_type = $t_work_type
									AND minutes_type = $t_minutes_type
								ORDER BY timestamp DESC LIMIT 1";
				$t_result         = db_query_bound( $t_query );
				$t_row_todo       = db_fetch_array( $t_result );
				$t_old_todo       = ( $t_row_todo == false ? null : $t_row_todo["minutes"] );
				$t_old_todo       = ( $t_old_todo === null || $t_old_todo <= 0 ? null : $t_old_todo );
				$t_old_todo_hours = ( $t_old_todo !== null && $t_old_todo > 0 ? round( $t_old_todo / 60, 2 ) : $t_old_todo );
				$t_new_todo       = ( $t_old_todo === null ? null : 0 );
				$t_new_todo_hours = ( $t_new_todo === null ? null : 0 );

				# Get the old value
				$t_minutes_type = PLUGIN_PM_DONE;
				$t_query        = "SELECT sum( minutes ) as minutes
                                     FROM $t_table
				                    WHERE bug_id = $t_bug_id
				                      AND work_type = $t_work_type
				                      AND minutes_type = $t_minutes_type";
				$t_result       = db_query_bound( $t_query );
				$t_row          = db_fetch_array( $t_result );
				$t_old_value    = ( $t_row == false ? null : round( $t_row["minutes"] / 60, 2 ) );
				$t_hours        = round( $t_minutes / 60, 2 );
				$t_new_value    = $t_old_value + $t_hours;
				$t_user_id      = auth_get_current_user_id();

				# Extra check: negative totals not possible!
				if ( $t_new_value < 0 ) {
					error_parameters( $t_work_types[$t_work_type] );
					trigger_error( plugin_lang_get( 'time_error' ), E_USER_ERROR );
				}

				if ($t_minutes > 0) {
					set_work( $t_bug_id, $t_work_type, PLUGIN_PM_DONE, $t_minutes, $f_book_date, Action::INSERT, $f_time_info );
					
					if ( $t_view_mode == PLUGIN_PM_BUG_VIEW_MODE_2 && !( $t_new_todo === null ) ) {
						set_work( $t_bug_id, $t_work_type, PLUGIN_PM_TODO, $t_new_todo, $f_book_date, Action::INSERT );
					}

					if ( $t_hours < 0 ) {
						$t_sign = ' - ';
					} else {
						$t_sign = ' + ';
					}
					$t_change = $t_old_value . $t_sign . abs( $t_hours );

					history_log_event_direct( $t_bug_id, sprintf( plugin_lang_get( 'history_added' ), $t_work_types[$t_work_type], plugin_lang_get( 'done' ) ),
						$t_change, $t_new_value );

					if ( $t_view_mode == PLUGIN_PM_BUG_VIEW_MODE_2 && !( $t_new_todo === null ) ) {
						history_log_event_direct( $t_bug_id, sprintf( plugin_lang_get( 'history_added' ), $t_work_types[$t_work_type], plugin_lang_get( 'todo' ) ),
							$t_old_todo_hours, $t_new_todo_hours );
					}
				}
				
				target_update_completed_date( $t_bug_id, $t_work_type, $t_user_id, $f_book_date );
			}
		}
	}

	if ( count( $t_bug_data[PLUGIN_PM_TODO] ) > 0 ) {
		# Handle todo: insert or update
		foreach ( $t_bug_data[PLUGIN_PM_TODO] as $t_work_type => $t_minutes ) {
			if ( isset( $t_minutes ) ) {

				# Get the old value
				$t_minutes_type = PLUGIN_PM_TODO;
				$t_query        = "SELECT minutes
                                     FROM $t_table
				                    WHERE bug_id = $t_bug_id
				                      AND work_type = $t_work_type
				                      AND minutes_type = $t_minutes_type";
				$t_result       = db_query_bound( $t_query );
				$t_row          = db_fetch_array( $t_result );
				$t_old_value    = ( $t_row == false ? null : round( $t_row["minutes"] / 60, 2 ) );
				$t_new_value    = round( $t_minutes / 60, 2 );
				$t_user_id      = auth_get_current_user_id();

				set_work( $t_bug_id, $t_work_type, PLUGIN_PM_TODO, $t_minutes, $f_book_date, Action::INSERT, $f_time_info ); //Action::INSERT_OR_UPDATE );
				
				if ( $t_view_mode == PLUGIN_PM_BUG_VIEW_MODE_2 ) {
					target_update_completed_date( $t_bug_id, $t_work_type, $t_user_id, $f_book_date );
				}

				history_log_event_direct( $t_bug_id, sprintf( plugin_lang_get( 'history_added' ), $t_work_types[$t_work_type], plugin_lang_get( 'todo' ) ),
					$t_old_value, $t_new_value );
			}
		}
	}

	if ( count( $t_bug_data['clear_todo'] ) > 0 ) {
		# Handle clearing of todo: delete
		foreach ( $t_bug_data['clear_todo'] as $t_work_type => $t_delete ) {
			if ( isset( $t_delete ) && $t_delete ) {

				# Get the old value
				$t_minutes_type = PLUGIN_PM_TODO;
				$t_query        = "SELECT minutes
                                     FROM $t_table
				                    WHERE bug_id = $t_bug_id
				                      AND work_type = $t_work_type
				                      AND minutes_type = $t_minutes_type";
				$t_result       = db_query_bound( $t_query );
				$t_row          = db_fetch_array( $t_result );
				$t_old_value    = ( $t_row == false ? null : round( $t_row["minutes"] / 60, 2 ) );
				$t_new_value    = plugin_lang_get( 'clear' );

				set_work( $t_bug_id, $t_work_type, PLUGIN_PM_TODO, $t_minutes, $f_book_date, Action::DELETE, $f_time_info );

				history_log_event_direct( $t_bug_id, sprintf( plugin_lang_get( 'history_added' ), $t_work_types[$t_work_type], plugin_lang_get( 'todo' ) ),
					$t_old_value, $t_new_value );
			}
		}
	}
}

form_security_purge( 'plugin_ProjectManagement_time_registration_update' );

if ( is_null( $f_redirect_page ) ) {
	$t_url = string_get_bug_view_url( $t_bug_id, auth_get_current_user_id() );
	print_successful_redirect( $t_url . "#time_registration" );
} else {
	print_successful_redirect( plugin_page( $f_redirect_page, true ) );
}
