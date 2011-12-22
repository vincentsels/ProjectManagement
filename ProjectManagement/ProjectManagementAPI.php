<?php

# Constants
define( 'PLUGIN_PM_EST',		0 );
define( 'PLUGIN_PM_DONE',		1 );
define( 'PLUGIN_PM_TODO',		2 );
define( 'PLUGIN_PM_REMAINING',	3 );
define( 'PLUGIN_PM_DIFF',		4 );

define( 'PLUGIN_PM_WORKTYPE_TOTAL',	100 );

# Enums

class Action {
	const UPDATE = 0;
	const INSERT = 1;
	const INSERT_OR_UPDATE = 2;
	const DELETE = 3;
}

/**
 * Converts the specified amount of minutes to a string formatted as 'HH:MM'.
 * @param float $p_minutes an amount of minutes.
 * @param bool $p_allow_blanks when true is passed, empty $p_minutes return a blank string,
 * otherwise, empty $p_minutes return the literal string '0:00'.
 * @return string the amount of minutes formatted as 'HH:MM'.
 */
function minutes_to_time( $p_minutes, $p_allow_blanks = true ) {
	if ( $p_minutes == 0 ) {
		if ( $p_allow_blanks ) {
			return null;
		} else {
			return '00:00';
		}
	}
	
	$t_hours = str_pad( floor( $p_minutes / 60 ), 2, '0', STR_PAD_LEFT );
	$t_minutes = str_pad( $p_minutes % 60, 2, '0', STR_PAD_RIGHT );
	
	return $t_hours . ':' . $t_minutes;
}

/**
 * Parses the specified $p_time string and converts it to an amount of minutes.
 * @param string $p_time a string in the form of 'HH', 'HH:MM' or 'DD:HH:MM'.
 * @param bool $p_throw_error_on_invalid_input true to throw an error on invalid input, false to return null.
 * @throws ERROR_PLUGIN_PM_INVALID_TIME_INPUT thrown upon invalid input when $p_throw_error_on_invalid_input is set to true.
 * @return the amount of minutes represented by the specified $p_time string.
 */
function time_to_minutes( $p_time, $p_throw_error_on_invalid_input = true ) {
	if ( empty ( $p_time ) ) {
		return null;
	}
	
	$t_time_array = explode( ':', $p_time );
	
	foreach ( $t_time_array as $t_value ) {
		if ( !is_numeric( $t_value ) ) {
			if ( $p_throw_error_on_invalid_input ) {
				trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, E_USER_ERROR );
			} else {
				return null;
			}
		}
	}
	
	$t_minutes;
	if ( count( $t_time_array ) == 3 ) {
		# User entered DD:HH:MM
		$t_minutes += $t_time_array[0] * 24 * 60;
		$t_minutes += $t_time_array[1] * 60;
		$t_minutes += $t_time_array[2];
	} else if ( count( $t_time_array ) == 2 ) {
		# User entered HH:MM
		$t_minutes += $t_time_array[0] * 60;
		$t_minutes += $t_time_array[1];
	} else if ( count( $t_time_array ) == 1 ) {
		# User entered HH
		$t_minutes += $t_time_array[0] * 60;
	} else {
		if ( $p_throw_error_on_invalid_input ) {
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, E_USER_ERROR );
		} else {
			return null;
		}
	}
	
	return $t_minutes;
}

function set_work( $p_bug_id, $p_work_type, $p_minutes_type, $p_minutes, $p_action ) {
	$t_rows_affected = 0;
	$t_table = plugin_table('work');
	$t_user_id = auth_get_current_user_id();
	$t_timestamp = time();
	
	if ( $p_action == ACTION::UPDATE || $p_action == ACTION::INSERT_OR_UPDATE ) {
		#Update and check for rows affected
		$t_query = 'UPDATE ' . $t_table . ' SET minutes=' . db_param() . ', timestamp =' . db_param() . ', user_id=' . db_param() .
				' WHERE bug_id=' . db_param() . ' AND work_type=' . db_param(). ' AND minutes_type=' . db_param();
		$t_fields = array( $p_minutes, $t_timestamp, $t_user_id, 
				$p_bug_id, $p_work_type, $p_minutes_type );
		db_query_bound( $t_query, $t_fields );
		$t_rows_affected = db_affected_rows();
	}
	if ( $p_action == ACTION::INSERT || ( $p_action == ACTION::INSERT_OR_UPDATE && $t_rows_affected == 0 )) {
		#Insert and check for rows affected
		$t_query = "INSERT INTO $t_table ( bug_id, user_id, work_type, minutes_type, 
					minutes, bookdate, timestamp ) VALUES ( " .
					db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . 
					db_param() . ', ' . db_param() . ', ' . db_param() . ')';
		$t_fields = array( $p_bug_id, $t_user_id, $p_work_type, $p_minutes_type,
				$p_minutes, $t_timestamp, $t_timestamp );
		db_query_bound( $t_query, $t_fields );
		$t_rows_affected = db_affected_rows();
	}
	else if ( $p_action == ACTION::DELETE ) {
		#Delete and check for rows affected
		$t_query = "DELETE FROM $t_table WHERE bug_id=" . db_param() . ' AND work_type=' . db_param() . ' AND minutes_type=' . db_param();
		$t_fields = array( $p_bug_id, $p_work_type, $p_minutes_type );
		db_query_bound( $t_query, $t_fields );
		$t_rows_affected = db_affected_rows();
	}
	
	return $t_rows_affected;
}

?>