<?php

# Constants
define( 'PLUGIN_PM_EST', 0 );
define( 'PLUGIN_PM_DONE', 1 );
define( 'PLUGIN_PM_TODO', 2 );
define( 'PLUGIN_PM_REMAINING', 3 );
define( 'PLUGIN_PM_DIFF', 4 );
define( 'PLUGIN_PM_OVERDUE', 5 );
define( 'PLUGIN_PM_DONE_BEFORE_REFDATE', 6 );

define( 'PLUGIN_PM_WORKTYPE_TOTAL', 100 );
define( 'PLUGIN_PM_TOKEN_RECENTLY_VISITED', 6876 ); # Has to be unique among plugins !!
define( 'PLUGIN_PM_TOKEN_RECENTLY_VISITED_COUNT', 50 );
define( 'PLUGIN_PM_TOKEN_EXPIRY_RECENTLY_VISITED', 60 * 60 * 24 * 3 ); # 3 days ?

define( 'PLUGIN_PM_DARK', 0 );
define( 'PLUGIN_PM_LIGHT', 1 );

define( 'PLUGIN_PM_CUST_BOTH', -1 );
define( 'PLUGIN_PM_CUST_PAYING', 0 );
define( 'PLUGIN_PM_CUST_APPROVING', 1 );
define( 'PLUGIN_PM_CUST_INTEGRATION_DEV', 2 );

define( 'PLUGIN_PM_ALL_CUSTOMERS', 0 );

define( 'PLUGIN_PM_CUST_CONCATENATION_CHAR', '|' );

define( 'PLUGIN_PM_PROJ_ID_UNPLANNED', -1 );
define( 'PLUGIN_PM_PROJ_ID_NONWORKING', -2 );

define( 'PLUGIN_PM_DUMMY_BUG', 999999 );

# Enums

class Action {
	const UPDATE           = 0;
	const INSERT           = 1;
	const INSERT_OR_UPDATE = 2;
	const DELETE           = 3;
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

	$t_hours   = str_pad( floor( abs( $p_minutes ) / 60 ), 2, '0', STR_PAD_LEFT );
	$t_minutes = str_pad( abs( $p_minutes ) % 60, 2, '0', STR_PAD_LEFT );

	$t_sign = ( $p_minutes < 0 ) ? '-' : '';

	return $t_sign . $t_hours . ':' . $t_minutes;
}

/**
 * Returns the amount of days, rounded to 1 decimal.
 * @param int $p_minutes
 * @return number
 */
function minutes_to_days( $p_minutes ) {
	return number_format( $p_minutes / 60 / 8, 1 );
}

/**
 * Parses the specified $p_time string and converts it to an amount of minutes.
 * @param string $p_time a string in the form of 'HH', 'HH:MM' or 'DD:HH:MM'.
 * @param bool $p_throw_error_on_invalid_input true to throw an error on invalid input, false to return null.
 * @param bool $p_allow_negative allows conversion of negative values.
 * @throws ERROR_PLUGIN_PM_INVALID_TIME_INPUT thrown upon invalid input when $p_throw_error_on_invalid_input is set to true.
 * @return the amount of minutes represented by the specified $p_time string.
 */
function time_to_minutes( $p_time, $p_allow_negative = true, $p_throw_error_on_invalid_input = true ) {
	if ( $p_time == '0' ) {
		return 0;
	} else if ( empty ( $p_time ) ) {
		return null;
	}

	$t_minutes = 0;

	# Check for d notation
	if ( !empty( $p_time ) && !is_numeric( $p_time ) && strrpos( strtolower( $p_time ), 'd' ) !== false ) {
		$t_days = trim( $p_time, 'Dd ' );
		if ( empty( $t_days ) || ( !is_numeric( $t_days ) || ( $t_days < 0 && !$p_allow_negative ) ) ) {
			if ( $p_throw_error_on_invalid_input ) {
				trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, E_USER_ERROR );
			} else {
				return null;
			}
		}

		$t_minutes = abs( $t_days ) * 8 * 60;
	} else {
		$t_time_array = explode( ':', $p_time );

		foreach ( $t_time_array as $t_key => $t_value ) {
			if ( !empty( $t_value ) && ( !is_numeric( $t_value ) || ( $t_value < 0 && !$p_allow_negative ) ) ) {
				if ( $p_throw_error_on_invalid_input ) {
					trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, E_USER_ERROR );
				} else {
					return null;
				}
			}
		}

		if ( count( $t_time_array ) == 3 ) {
			# User entered DD:HH:MM
			$t_minutes += abs( $t_time_array[0] ) * 8 * 60;
			$t_minutes += abs( $t_time_array[1] ) * 60;
			$t_minutes += abs( $t_time_array[2] );
		} else if ( count( $t_time_array ) == 2 ) {
			# User entered HH:MM
			$t_minutes += abs( $t_time_array[0] ) * 60;
			$t_minutes += abs( $t_time_array[1] );
		} else if ( count( $t_time_array ) == 1 ) {
			# User entered HH
			$t_minutes += abs( $t_time_array[0] ) * 60;
		} else {
			if ( $p_throw_error_on_invalid_input || ( $t_value < 0 && !$p_allow_negative ) ) {
				trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, E_USER_ERROR );
			} else {
				return null;
			}
		}
	}

	if ( $p_allow_negative && strstr( $p_time, '-' ) ) {
		$t_minutes *= -1;
	}

	return $t_minutes;
}

/**
 * Update the work of the specified $p_bug_id.
 * @return number number of affected rows.
 */
function set_work( $p_bug_id, $p_work_type, $p_minutes_type, $p_minutes, $p_book_date, $p_action ) {
	$t_rows_affected = 0;
	$t_table         = plugin_table( 'work' );
	$t_user_id       = auth_get_current_user_id();
	$t_timestamp     = time();

	if ( empty( $p_book_date ) ) {
		# When no book_date was set, default to today
		$p_book_date = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );
	}

	if ( $p_action == ACTION::UPDATE || $p_action == ACTION::INSERT_OR_UPDATE ) {
		#Update and check for rows affected
		$t_query = "UPDATE $t_table SET minutes = $p_minutes, timestamp = $t_timestamp, user_id = $t_user_id, book_date= $p_book_date
				WHERE bug_id = $p_bug_id AND work_type = $p_work_type AND minutes_type = $p_minutes_type";
		db_query_bound( $t_query );
		$t_rows_affected = db_affected_rows();
	}
	if ( $p_action == ACTION::INSERT || ( $p_action == ACTION::INSERT_OR_UPDATE && $t_rows_affected == 0 ) ) {
		#Insert and check for rows affected
		$t_query = "INSERT INTO $t_table ( bug_id, user_id, work_type, minutes_type,
					minutes, book_date, timestamp )
					VALUES ( $p_bug_id, $t_user_id, $p_work_type, $p_minutes_type,
					$p_minutes, $p_book_date, $t_timestamp )";
		db_query_bound( $t_query );
		$t_rows_affected = db_affected_rows();
	}
	else if ( $p_action == ACTION::DELETE ) {
		#Delete and check for rows affected
		$t_query = "DELETE FROM $t_table WHERE bug_id = $p_bug_id AND work_type= $p_work_type AND minutes_type= $p_minutes_type";
		db_query_bound( $t_query );
		$t_rows_affected = db_affected_rows();
	}

	return $t_rows_affected;
}

/**
 * Calculates the actual work todo based on the supplied data.
 * The data must be a multi-dimentional array in the form of
 * $p_work[WORK_TYPE][MINUTES_TYPE] = MINUTES
 * @param mixed $p_work multi-dimentional array in the form of
 * $p_work[WORK_TYPE][MINUTES_TYPE] = MINUTES.
 * @return the actual remaining hours todo.
 */
function get_actual_work_todo( $p_work_types ) {
	$t_todo = 0;

	foreach ( $p_work_types as $work_type => $minute_types ) {
		if ( isset( $minute_types[PLUGIN_PM_TODO] ) ) {
			$t_todo += $minute_types[PLUGIN_PM_TODO];
		} else if ( isset( $minute_types[PLUGIN_PM_EST] ) ) {
			@$t_todo += ( $minute_types[PLUGIN_PM_EST] - $minute_types[PLUGIN_PM_DONE] );
		}
	}

	return max( $t_todo, 0 );
}

/**
 * Returns the first day of the current month, or when specified,
 * the current month added (or substracted) with $p_add_months months.
 * @param int $p_add_months Optional. The amount of months to add or substract from the current month.
 * @param string $p_format Optional. The format of the date to return.
 * @return string the first day of the month, formated as $p_format.
 */
function first_day_of_month( $p_add_months = 0, $p_format = null ) {
	if ( $p_format == null ) {
		$p_format = config_get( 'short_date_format' );
	}
	return date( $p_format, mktime( 0, 0, 0, date( 'm' ) + $p_add_months, 1 ) );
}

/**
 * Returns the last day of the current month, or when specified,
 * the current month added (or substracted) with $p_add_months months.
 * @param int $p_add_months Optional. The amount of months to add or substract from the current month.
 * @param string $p_format Optional. The format of the date to return.
 * @return string the last day of the month, formated as $p_format.
 */
function last_day_of_month( $p_add_months = 0, $p_format = null ) {
	if ( $p_format == null ) {
		$p_format = config_get( 'short_date_format' );
	}
	return date( $p_format, mktime( 0, 0, 0, date( 'm' ) + $p_add_months + 1, 0 ) );
}

/**
 * Returns an array of key value pairs containing the key of the specified $p_enum_string
 * and the translated label as its value.
 * @param string $p_enum_string the enum string (without trailing 'enum_string'
 */
function get_translated_assoc_array_for_enum( $p_enum_string ) {
	$t_untranslated = MantisEnum::getAssocArrayIndexedByValues( config_get( $p_enum_string . '_enum_string' ) );
	$t_translated   = array();
	foreach ( $t_untranslated as $t_key => $t_value ) {
		$t_translated[$t_key] = get_enum_element( $p_enum_string, $t_key );
	}
	return $t_translated;
}

/**
 * Locale-aware floatval
 * @link http://www.php.net/manual/en/function.floatval.php#92563
 * @param string $floatString
 * @return number
 */
function parse_float( $p_floatstring ) {
	$t_locale_info = localeconv();
	$p_floatstring = str_replace( plugin_config_get( 'thousand_separator' ), "", $p_floatstring );
	$p_floatstring = str_replace( plugin_config_get( 'decimal_separator' ), ".", $p_floatstring );
	return floatval( $p_floatstring );
}

/**
 * Formats the specified $p_date as specified by the short_date_format configuration setting.
 */
function format_short_date( $p_date = null ) {
	if ( is_null( $p_date ) || empty( $p_date ) ) {
		return null;
	}
	return date( config_get( 'short_date_format' ), $p_date );
}

/**
 * Formats the specified $p_value as defined by the decimal_separator and
 * thousand_separator in the plugin config settings.
 * Optionally specify the amount of decimals to display and round at.
 * @param float $p_value the value to format.
 * @param int $p_decimals the amount of decimals to display and round at. Default = 2.
 * @return string
 */
function format( $p_value, $p_decimals = 2 ) {
	return number_format( round( $p_value, $p_decimals ), $p_decimals,
		plugin_config_get( 'decimal_separator' ),
		plugin_config_get( 'thousand_separator' ) );
}

/**
 * Convert a string array in the form of array( 'key' => 'val', key1 => val2,... ) to a php array.
 * Only works with this format of arrays!
 * @todo duplicated here from adm_config_report.php, should be moved to helper class or something imo.
 * @param complex $p_value
 * @return the array
 */
function string_to_array( $p_value ) {
	$t_value       = array();
	$t_full_string = trim( $p_value );
	if ( preg_match( '/array[\s]*\((.*)\)/s', $t_full_string, $t_match ) === 1 ) {
		// we have an array here
		$t_values = explode( ',', trim( $t_match[1] ) );
		foreach ( $t_values as $key => $value ) {
			if ( !trim( $value ) ) {
				continue;
			}
			$t_split = explode( '=>', $value, 2 );
			if ( count( $t_split ) == 2 ) {
				// associative array
				$t_new_key           = constant_replace( trim( $t_split[0], " \t\n\r\0\x0B\"'" ) );
				$t_new_value         = constant_replace( trim( $t_split[1], " \t\n\r\0\x0B\"'" ) );
				$t_value[$t_new_key] = $t_new_value;
			} else {
				// regular array
				$t_value[$key] = constant_replace( trim( $value, " \t\n\r\0\x0B\"'" ) );
			}
		}
	}
	return $t_value;
}

/**
 * Check if the passed string is a constant and return its value.
 * @todo duplicated here from adm_config_report.php, should be moved to helper class or something imo.
 */
function constant_replace( $p_name ) {
	$t_result = $p_name;
	if ( is_string( $p_name ) && defined( $p_name ) ) {
		// we have a constant
		$t_result = constant( $p_name );
	}
	return $t_result;
}

/**
 * Add a recently visited bug.
 * @param int $p_issue_id
 * @param int $p_user_id
 */
function recently_visited_bug_add( $p_issue_id, $p_user_id = null ) {
	$t_value = token_get_value( PLUGIN_PM_TOKEN_RECENTLY_VISITED, $p_user_id );
	if ( empty( $t_value ) ) {
		$t_value = $p_issue_id;
	} else {
		$t_ids   = explode( ',', $p_issue_id . ',' . $t_value );
		$t_ids   = array_unique( $t_ids );
		$t_ids   = array_slice( $t_ids, 0, PLUGIN_PM_TOKEN_RECENTLY_VISITED_COUNT );
		$t_value = implode( ',', $t_ids );
	}

	token_set( PLUGIN_PM_TOKEN_RECENTLY_VISITED, $t_value, TOKEN_EXPIRY_LAST_VISITED, $p_user_id );
}

/**
 * Retrieve an array of all recently visted bugs.
 * @param int $p_user_id
 * @return array of recently visited bugs
 */
function recently_visited_bugs_get( $p_user_id = null ) {
	$t_value = token_get_value( PLUGIN_PM_TOKEN_RECENTLY_VISITED, $p_user_id );

	if ( is_null( $t_value ) ) {
		return array();
	}

	$t_ids = explode( ',', $t_value );

	bug_cache_array_rows( $t_ids );
	return $t_ids;
}

/**
 * Returns the specified language string with only the initial letter capitalized.
 * @param string $p_lang_strings can either be one string to translate or an array of strings
 * @param bool $p_plugin Specify whether or not to use a translation from the plugin, defaults to false
 * @return string
 */
function init_cap( $p_lang_strings, $p_plugin = false ) {
	if ( is_array( $p_lang_strings ) ) {
		foreach ( $p_lang_strings as $p_lang_string ) {
			$p_lang_strings_arr[] = $p_lang_string;
		}
	} else {
		$p_lang_strings_arr[] = $p_lang_strings;
	}

	$t_val = '';
	if ( $p_plugin ) {
		foreach ( $p_lang_strings_arr as $t_str ) {
			$t_val .= plugin_lang_get( $t_str ) . ' ';
		}
	} else {
		foreach ( $p_lang_strings_arr as $t_str ) {
			$t_val .= lang_get( $t_str ) . ' ';
			;
		}
	}

	return ucfirst( strtolower( trim( $t_val ) ) );
}

function prepare_resource_name( $p_handler_id ) {
	if ( $p_handler_id == NO_USER ) {
		return '<span class="italic">' . plugin_lang_get( 'unassigned' ) . '</span>';
	} else {
		return user_get_name( $p_handler_id );
	}
}

function sort_array_by_key( &$p_array ) {
	ksort( $p_array );
	return $p_array;
}

/**
 * Retrieves an array of all customers, indexed by the customer_id.
 * @param int $p_type Optionally specify to only retrieve customers that 'can approve'.
 * @return array A list of all customers.
 */
function customer_get_all( $p_type = PLUGIN_PM_CUST_BOTH ) {
	$t_customer_table = plugin_table( 'customer' );
	$t_query_cust = "SELECT *  FROM $t_customer_table";
	$t_cust = array();

	if ( $p_type == PLUGIN_PM_CUST_APPROVING ) {
		$t_query_cust .= " WHERE can_approve = 1";
	}
	$t_result_cust = db_query_bound( $t_query_cust );
	while ( $row = db_fetch_array( $t_result_cust ) ) {
		$t_cust[$row['id']] = $row;
	}

	return $t_cust;
}

/**
 * Gets an array with the selected customer_id's for the specified bug.
 * @param $p_bug_id
 * @param $p_type
 * @return array the selected customers for the specified bug
 */
function bug_customer_get_selected( $p_bug_id, $p_type ) {
	$t_selected_cust = array();
	if ( !is_null( $p_bug_id ) ) {
		$t_bug_customer_table = plugin_table( 'bug_customer' );
		$t_query_bug_cust     = "SELECT * FROM $t_bug_customer_table WHERE bug_id = $p_bug_id AND type = $p_type";
		$t_result_bug_cust    = db_query_bound( $t_query_bug_cust );
		while ( $t_bug_cust = db_fetch_array( $t_result_bug_cust ) ) {
			$t_selected_cust = explode( PLUGIN_PM_CUST_CONCATENATION_CHAR, $t_bug_cust['customers'] );
		}
	}
	return $t_selected_cust;
}

/**
 * @param $t_selected_cust array an array of the customer id's to convert to string
 * @return A comma-separated list of the names of the customers in the passed $t_selected_cust array
 */
function customer_list_to_string( $t_selected_cust ) {
	if ( empty( $t_selected_cust ) ) {
		return '';
	}

	$t_all_cust             = customer_get_all();
	$t_selected_cust_string = '';
	if ( array_search( (string)PLUGIN_PM_ALL_CUSTOMERS, $t_selected_cust, true ) ) {
		$t_selected_cust_string .= init_cap( 'all' ) . ', ';
	} else {
		foreach ( $t_selected_cust as $t_customer_id ) {
			if ( array_key_exists( $t_customer_id, $t_all_cust ) ) {
				$t_selected_cust_string .= $t_all_cust[$t_customer_id]['name'] . ', ';
			}
		}
	}
	$t_selected_cust_string = rtrim( $t_selected_cust_string, ', ' );
	return $t_selected_cust_string;
}

/**
 * Updates the list of customers of the specified $p_type for the specified $p_bug_id.
 * @param $p_bug_id
 * @param string $p_cust_string A CUST_CONCATENATION_CHAR seperated list of customer id's
 * @param int $p_type Default = PLUGIN_PM_CUST_PAYING
 */
function bug_customer_update_or_insert( $p_bug_id, $p_cust_string, $p_type = PLUGIN_PM_CUST_PAYING) {
	$t_bug_cust_table = plugin_table( 'bug_customer' );

	$t_query = "SELECT * FROM $t_bug_cust_table WHERE bug_id = $p_bug_id AND type = $p_type";
	$t_result = db_query_bound( $t_query );
	$t_old = db_fetch_array( $t_result );
	$t_new_as_string = customer_list_to_string( explode( PLUGIN_PM_CUST_CONCATENATION_CHAR, $p_cust_string ) );
	$t_old_as_string = '';
	$t_log = false;

	if ( $t_old !== false ) {
		$t_old_cust_string = $t_old['customers'];
		if ( $t_old_cust_string != $p_cust_string ) {
			$t_query = "UPDATE $t_bug_cust_table
						   SET customers = '$p_cust_string'
						 WHERE bug_id = $p_bug_id
						   AND type = $p_type";
			db_query_bound( $t_query );

			$t_old_as_string = customer_list_to_string(
				explode( PLUGIN_PM_CUST_CONCATENATION_CHAR, $t_old_cust_string ) );
			$t_log = true;
		}
	} else if ( '' !== $p_cust_string ) {
		$t_query = "INSERT INTO $t_bug_cust_table(bug_id, type, customers)
	                VALUES($p_bug_id, $p_type, '$p_cust_string')";
		db_query_bound( $t_query );
		$t_log = true;
	}

	if ( $t_log ) {
		if ( $p_type == PLUGIN_PM_CUST_PAYING || $p_type == PLUGIN_PM_CUST_APPROVING ) {
			$t_type_as_string = ( $p_type == PLUGIN_PM_CUST_PAYING ? plugin_lang_get( 'paying_customers' ) :
				plugin_lang_get( 'approving_customers' ) );
			history_log_event_direct( $p_bug_id, $t_type_as_string,
				$t_old_as_string, $t_new_as_string );
		} else if ( $p_type == PLUGIN_PM_CUST_INTEGRATION_DEV ) {
			history_log_event_direct( $p_bug_id, plugin_lang_get( 'integration_custom_dev' ),
				'', ( $p_cust_string === PLUGIN_PM_CUST_CONCATENATION_CHAR . '0' ? lang_get( 'yes' ) : lang_get( 'no' ) ) );
		}
	}
}

/**
 * Returns the days between two given dates, or between the given date and today if only one is supplied.
 * Returns a positive number if the given date is in the past, negative if it is in the future.
 */
function days_between( $p_date, $p_refdate = null ) {
	if ( is_null( $p_refdate ) ) {
		$p_refdate = time();
	}

	return floor( ( $p_refdate - $p_date ) / (60*60*24) );
}

/**
 * Updates or inserts the specified target data.
 */
function target_update( $p_bug_id, $p_work_type, $p_owner_id, $p_target_date, $p_completed_date = null ) {
	$p_completed_date_formated = $p_completed_date;
	if ( empty( $p_completed_date ) ) {
		$p_completed_date_formated = 'NULL';
	}

	$t_work_types = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );
	$t_target_table = plugin_table( 'target' );

	$t_query = "SELECT * FROM $t_target_table WHERE bug_id = $p_bug_id AND work_type = $p_work_type";
	$t_result = db_query_bound( $t_query );
	$t_exists = false;
	if ( db_num_rows( $t_result ) > 0 ) {
		$t_exists = true;
		$t_old = db_fetch_array( $t_result );
	}

	if ( $t_exists ) {
		$t_query = "UPDATE $t_target_table
					   SET owner_id = $p_owner_id, target_date = $p_target_date, completed_date = $p_completed_date_formated
					 WHERE bug_id = $p_bug_id
					   AND work_type = $p_work_type";
		db_query_bound( $t_query );

		# Log updated record to history
		if ( $t_old["owner_id"] != $p_owner_id ) {
			history_log_event_direct( $p_bug_id, plugin_lang_get( 'owner' ) . " ($t_work_types[$p_work_type])",
				user_get_name( $t_old["owner_id"] ), user_get_name( $p_owner_id ) );
		}
		if ( $t_old["target_date"] != $p_target_date ) {
			history_log_event_direct( $p_bug_id, plugin_lang_get( 'target_date' ) . " ($t_work_types[$p_work_type])",
				format_short_date( $t_old["target_date"] ), format_short_date( $p_target_date ) );
		}
		if ( $t_old["completed_date"] != $p_completed_date ) {
			history_log_event_direct( $p_bug_id, plugin_lang_get( 'completed' ) . " ($t_work_types[$p_work_type])",
				format_short_date( $t_old["completed_date"] ), format_short_date( $p_completed_date ) );
		}
	} else {
		$t_query = "INSERT INTO $t_target_table(bug_id, work_type, owner_id, target_date, completed_date)
	                VALUES($p_bug_id, $p_work_type, $p_owner_id, $p_target_date, $p_completed_date_formated)";
		db_query_bound( $t_query );

		# Log new record to history
		history_log_event_direct( $p_bug_id, plugin_lang_get( 'new_target' ) . " ($t_work_types[$p_work_type])",
			null, format_short_date( $p_target_date ) . ' (' . user_get_name( $p_owner_id ) . ')' );
	}
}

function resource_insert_or_update( $p_user_id, $p_hourly_rate, $p_hours_per_week, $p_color, $p_deployability ) {
	$t_resource_table = plugin_table( 'resource' );

	$t_query_old_row  = "SELECT user_id, hourly_rate, hours_per_week, color, deployability
						      FROM $t_resource_table
							 WHERE user_id = $p_user_id";
	$t_result_old_row = db_query_bound( $t_query_old_row );
	$t_old_row = null;

	if ( db_num_rows( $t_result_old_row ) == 1 ) {
		$t_old_row = db_fetch_array( $t_result_old_row );
	}

	if ( is_null( $t_old_row ) ) {
		$t_query_insert = "INSERT INTO $t_resource_table(user_id, hourly_rate, hours_per_week, color, deployability)
			VALUES ($p_user_id, $p_hourly_rate, $p_hours_per_week, $p_color, $p_deployability)";
		db_query_bound( $t_query_insert );
	}
	else
	{
		$t_query_update_update_clause = "UPDATE $t_resource_table SET ";
		$t_query_update_set_clause    = array();
		$t_query_update_where_clause  = " WHERE user_id = $p_user_id";

		if ( !empty( $p_hourly_rate ) && $p_hourly_rate != $t_query_old_row['hourly_rate'] ) {
			$t_query_update_set_clause[] = "hourly_rate = $p_hourly_rate";
		}
		if ( !empty( $p_hours_per_week ) && $p_hours_per_week != $t_query_old_row['hours_per_week'] ) {
			$t_query_update_set_clause[] = "hours_per_week = $p_hours_per_week";
		}
		if ( !empty( $p_color ) && $p_color != $t_query_old_row['color'] ) {
			$t_query_update_set_clause[] = "color = $p_color";
		}
		if ( !empty( $p_deployability ) && $p_deployability != $t_query_old_row['deployability'] ) {
			$t_query_update_set_clause[] = "deployability = $p_deployability";
		}

		db_query_bound( $t_query_update_update_clause .
			implode( ', ', $t_query_update_set_clause ) .
			$t_query_update_where_clause );
	}
}

function resource_unavailability_period_add( $p_user_id, $p_start_date, $p_end_date,
											 $p_type, $p_include_work, $p_note ) {
	$t_res_unavailable_tab = plugin_table( 'resource_unavailable' );
	$t_query_insert = "INSERT INTO $t_res_unavailable_tab(user_id, start_date, end_date, type, include_work, note)
		VALUES ($p_user_id, $p_start_date, $p_end_date, $p_type, $p_include_work, '$p_note')";
	db_query_bound( $t_query_insert );
}

function resource_cache_data() {
	global $g_resources;

	$t_resource_table = plugin_table( 'resource' );
	$t_query    = "SELECT user_id, color, deployability, hours_per_week FROM $t_resource_table";
	$t_result   = db_query_bound( $t_query );

	while ( $t_row = db_fetch_array( $t_result ) ) {
		$t_user_id = $t_row['user_id'];
		$g_resources[$t_user_id] = $t_row;
		# Fetch the non-working days
		$t_resource_na_table = plugin_table( 'resource_unavailable' );
		$t_query_na    = "SELECT start_date, end_date, type, note
							FROM $t_resource_na_table
						   WHERE user_id = $t_user_id";
		$t_result_na   = db_query_bound( $t_query_na );
		$g_resources[$t_user_id]['resource_unavailable'] = array();
		while ( $t_res_na = db_fetch_array( $t_result_na ) ) {
			$g_resources[$t_user_id]['resource_unavailable'][] = $t_res_na;
		}
	}
	# Add default
	$g_resources[NO_USER]['user_id'] = NO_USER;
	$g_resources[NO_USER]['color'] = 120;
	$g_resources[NO_USER]['deployability'] = 100;
	$g_resources[NO_USER]['hours_per_week'] = 0;
	$g_resources[NO_USER]['resource_unavailable'] = array();
}


/**
 * @param $f_target_version string Required. Specify a the target version to get the tasks for.
 * @param int $f_user_id Optionally specify a single user to get the tasks for.
 * @param int $p_include_bugs_with_deadline Whether or not to include bugs which have a deadline in the
 * release cycle period. Use the include_bugs_with_deadline config setting.
 * @return ADORecordSet|bool
 */
function get_all_tasks( $f_target_version, $f_user_id = ALL_USERS, $p_include_bugs_with_deadline = ON ) {
	$t_bug_table             = db_get_table( 'mantis_bug_table' );
	$t_project_table         = db_get_table( 'mantis_project_table' );
	$t_category_table        = db_get_table( 'mantis_category_table' );
	$t_hierarchy_table       = db_get_table( 'mantis_project_hierarchy_table' );
	$t_project_version_table = db_get_table( 'mantis_project_version_table' );
	$t_work_table            = plugin_table( 'work' );

	$t_query = "SELECT pp.id as parent_project_id, pp.name as parent_project,
				  pc.id as project_id, pc.name as project_name, c.id as category_id, c.name as category_name,
				  b.sponsorship_total as weight, b.due_date,
				  b.id, b.handler_id, w.work_type, w.minutes_type, sum(w.minutes) as minutes, max(w.book_date) as book_date
				  FROM $t_bug_table b
				  JOIN $t_project_table pc ON b.project_id = pc.id
				  JOIN $t_category_table c ON b.category_id = c.id
				  LEFT OUTER JOIN $t_hierarchy_table h ON pc.id = h.child_id
				  LEFT OUTER JOIN $t_project_table pp ON h.parent_id = pp.id
				  LEFT OUTER JOIN $t_work_table w ON b.id = w.bug_id
				 WHERE (b.target_version = '$f_target_version'";

	if ( ON == $p_include_bugs_with_deadline ) {
		# First get the release date of the currently targeted version
		$t_query_release_date_target  = "SELECT date_order
										   FROM $t_project_version_table v
										  WHERE v.version = '$f_target_version'";
		$t_result_release_date_target = db_query_bound( $t_query_release_date_target );
		$t_release_date_target_array  = db_fetch_array( $t_result_release_date_target );
		$t_release_date_target        = $t_release_date_target_array ? $t_release_date_target_array['date_order'] : time();

		# Next get the release date of the previous version
		$t_query_release_date_previous  = "SELECT max(date_order) as date_order
											 FROM $t_project_version_table v
											WHERE v.date_order < '$t_release_date_target'";
		$t_result_release_date_previous = db_query_bound( $t_query_release_date_previous );
		$t_release_date_previous_array  = db_fetch_array( $t_result_release_date_previous );
		$t_release_date_previous        = $t_release_date_previous_array ? $t_release_date_previous_array['date_order'] : time();

		# It must have been possible to determine both dates in order for this clause to work
		if ( !empty( $t_release_date_target ) && !empty( $t_release_date_previous ) ) {
			# due date is a required field, a value of 1 means no due date
			$t_query .= " OR b.due_date BETWEEN $t_release_date_previous AND $t_release_date_target ";
		}
	}

	$t_query .= ")";

	if ( $f_user_id != ALL_USERS ) {
		$t_query .= " AND b.handler_id = $f_user_id";
	}

	$t_query .= " GROUP BY pp.id, pp.name, pc.id, pc.name, c.id, c.name, b.id, b.handler_id, w.work_type, w.minutes_type,
				  b.sponsorship_total, b.due_date
				  ORDER BY handler_id, b.resolution DESC, CASE WHEN MAX(b.due_date) = 1 THEN 9999999999 ELSE MAX(b.due_date) END, weight DESC, id";

	$t_result = db_query_bound( $t_query );
	return $t_result;
}

function get_limit_clause_after_select( $p_limit_amount ) {
	if ( !stristr( config_get( 'db_type', '' ), 'mssql') ) {
		return ' ';
	} else {
		# MsSql: use TOP clause
		return ' TOP ' . $p_limit_amount;
	}
}

function get_limit_clause_after_order_by( $p_limit_amount) {
	if( !stristr( config_get( 'db_type', '' ), 'mssql')) {
		# Other databases: use LIMIT clause
		return ' LIMIT ' . $p_limit_amount;
	} else {
		return ' ';
	}
}

if ( !function_exists( 'strtotime_safe' ) ) {
	/**
	 * Fixes 0013332: Due date not saved successfully when date-format is set to 'd/m/Y'
	 * The normal strtotime can't handle the format d/m/Y, since it will interpret
	 * it as m/d/Y. To determine whether this is the case, this function looks
	 * at the short_date_format setting.
	 * Also, if the passed argument is null and parameter $p_allow_null is false (default),
	 * date_get_null() is returned.
	 * @param string $p_date
	 * @param bool $p_allow_null
	 * @return number
	 */
	function strtotime_safe( $p_date, $p_allow_null = false ) {
		if( !$p_allow_null && ( $p_date == null || is_blank ( $p_date ) || $p_date === 1 ) ) {
			return date_get_null();
		}

		if ( config_get( 'short_date_format' ) == 'd/m/Y' ) {
			return strtotime( str_replace( '/', '-', $p_date ) );
		} else {
			return strtotime( $p_date );
		}
	}
}

?>
