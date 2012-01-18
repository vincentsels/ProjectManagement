<?php

# Constants
define( 'PLUGIN_PM_EST',		0 );
define( 'PLUGIN_PM_DONE',		1 );
define( 'PLUGIN_PM_TODO',		2 );
define( 'PLUGIN_PM_REMAINING',	3 );
define( 'PLUGIN_PM_DIFF',		4 );
define( 'PLUGIN_PM_OVERDUE',	5 );

define( 'PLUGIN_PM_WORKTYPE_TOTAL',					100 );
define( 'PLUGIN_PM_TOKEN_RECENTLY_VISITED',			6876 ); # Has to be unique among plugins !!
define( 'PLUGIN_PM_TOKEN_RECENTLY_VISITED_COUNT',	50 );
define( 'PLUGIN_PM_TOKEN_EXPIRY_RECENTLY_VISITED',	60 * 60 * 24 * 3 ); # 3 days ?

define( 'PLUGIN_PM_DARK', 0 );
define( 'PLUGIN_PM_LIGHT', 1 );

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
function minutes_to_time( $p_minutes, $p_allow_blanks = true, $p_show_days = false ) {
	if ( $p_minutes == 0 ) {
		if ( $p_allow_blanks ) {
			return null;
		} else {
			return '00:00';
		}
	}
	
	$t_hours = str_pad( floor( abs($p_minutes) / 60 ), 2, '0', STR_PAD_LEFT );
	$t_minutes = str_pad( abs($p_minutes) % 60, 2, '0', STR_PAD_LEFT );
	
	if ( $p_minutes < 0 ) {
		$t_sign = '-';
	}
	
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
	if ( $p_time == '0') {
		return 0;
	} else if ( empty ( $p_time ) ) {
		return null;
	}
	
	$t_time_array = explode( ':', $p_time );
	
	foreach ( $t_time_array as $t_value ) {
		if ( !empty($t_value) && ( !is_numeric( $t_value ) || ( $t_value < 0 && !$p_allow_negative ) ) ) {
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
		$t_minutes += abs($t_time_array[0]) * 8 * 60;
		$t_minutes += abs($t_time_array[1]) * 60;
		$t_minutes += abs($t_time_array[2]);
	} else if ( count( $t_time_array ) == 2 ) {
		# User entered HH:MM
		$t_minutes += abs($t_time_array[0]) * 60;
		$t_minutes += abs($t_time_array[1]);
	} else if ( count( $t_time_array ) == 1 ) {
		# User entered HH
		$t_minutes += abs($t_time_array[0]) * 60;
	} else {
		if ( $p_throw_error_on_invalid_input || ( $t_value < 0 && !$p_allow_negative ) ) {
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, E_USER_ERROR );
		} else {
			return null;
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
	$t_table = plugin_table('work');
	$t_user_id = auth_get_current_user_id();
	$t_timestamp = time();
	
	if ( $p_action == ACTION::UPDATE || $p_action == ACTION::INSERT_OR_UPDATE ) {
		#Update and check for rows affected
		$t_query = "UPDATE $t_table SET minutes = $p_minutes, timestamp = $t_timestamp, user_id = $t_user_id, book_date= $p_book_date
				WHERE bug_id = $p_bug_id AND work_type = $p_work_type AND minutes_type = $p_minutes_type";
		db_query_bound( $t_query );
		$t_rows_affected = db_affected_rows();
	}
	if ( $p_action == ACTION::INSERT || ( $p_action == ACTION::INSERT_OR_UPDATE && $t_rows_affected == 0 )) {
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
 * Returns the first day of the current month, or when specified,
 * the current month added (or substracted) with $p_add_months months.
 * @param int $p_add_months Optional. The amount of months to add or substract from the current month.
 * @param string $p_format Optional. The format of the date to return. Default is 'd/m/Y'.
 * @return string the first day of the month, formated as $p_format.
 */
function first_day_of_month( $p_add_months = 0, $p_format = 'd/m/Y' ) {
	return date( $p_format, mktime( 0, 0, 0, date('m') + $p_add_months, 1 ) );
}

/**
 * Returns the last day of the current month, or when specified,
 * the current month added (or substracted) with $p_add_months months.
 * @param int $p_add_months Optional. The amount of months to add or substract from the current month.
 * @param string $p_format Optional. The format of the date to return. Default is 'd/m/Y'.
 * @return string the last day of the month, formated as $p_format.
 */
function last_day_of_month( $p_add_months = 0, $p_format = 'd/m/Y' ) {
	return date( $p_format, mktime( 0, 0, 0, date('m') + $p_add_months + 1, 0 ) );
}

/**
 * Returns an array of key value pairs containing the key of the specified $p_enum_string
 * and the translated label as its value.
 * @param string $p_enum_string the enum string (without trailing 'enum_string'
 */
function get_translated_assoc_array_for_enum( $p_enum_string ) {
	$t_untranslated = MantisEnum::getAssocArrayIndexedByValues( config_get( $p_enum_string . '_enum_string' ) );
	$t_translated = array();
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
function parse_float( $p_floatstring ){
	$t_locale_info = localeconv();
	$p_floatstring = str_replace( $t_locale_info["mon_thousands_sep"] , "", $p_floatstring );
	$p_floatstring = str_replace( $t_locale_info["mon_decimal_point"] , ".", $p_floatstring );
	return floatval( $p_floatstring );
}

/**
 * Formats the specified $p_decimal as '100 000,00'
 * @param float $p_decimal
 * @return string
 */
function format( $p_decimal ) {
	return number_format( round( $p_decimal, 2 ), 2, ',', ' ' );
}

/**
 * Convert a string array in the form of array( 'key' => 'val', key1 => val2,... ) to a php array.
 * Only works with this format of arrays!
 * @todo duplicated here from adm_config_report.php, should be moved to helper class or something imo.
 * @param complex $p_value
 * @return the array
 */
function string_to_array( $p_value ) {
	$t_value = array();
	$t_full_string = trim( $p_value );
	if ( preg_match('/array[\s]*\((.*)\)/s', $t_full_string, $t_match ) === 1 ) {
		// we have an array here
		$t_values = explode( ',', trim( $t_match[1] ) );
		foreach ( $t_values as $key => $value ) {
			if ( !trim( $value ) ) {
				continue;
			}
			$t_split = explode( '=>', $value, 2 );
			if ( count( $t_split ) == 2 ) {
				// associative array
				$t_new_key = constant_replace( trim( $t_split[0], " \t\n\r\0\x0B\"'" ) );
				$t_new_value = constant_replace( trim( $t_split[1], " \t\n\r\0\x0B\"'" ) );
				$t_value[ $t_new_key ] = $t_new_value;
			} else {
				// regular array
				$t_value[ $key ] = constant_replace( trim( $value, " \t\n\r\0\x0B\"'" ) );
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
	if( empty( $t_value ) ) {
		$t_value = $p_issue_id;
	} else {
		$t_ids = explode( ',', $p_issue_id . ',' . $t_value );
		$t_ids = array_unique( $t_ids );
		$t_ids = array_slice( $t_ids, 0, PLUGIN_PM_TOKEN_RECENTLY_VISITED_COUNT );
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

	if( is_null( $t_value ) ) {
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
	
	if ( $p_plugin ) {
		foreach ( $p_lang_strings_arr as $t_str ) {
			$t_val .= plugin_lang_get( $t_str ) . ' ';
		}
	} else {
		foreach ( $p_lang_strings_arr as $t_str ) {
			$t_val .= lang_get( $t_str ) . ' ';;
		}
	}
	
	return ucfirst( strtolower( trim( $t_val ) ) );
}

function prepare_resource_name( $p_handler_id ) {
	if ( empty( $p_handler_id ) ) {
		return '<span class="italic">' . plugin_lang_get( 'unassigned' ) . '</span>';
	} else {
		return user_get_name( $p_handler_id );
	}
}

function sort_array_by_key( &$p_array ) {
	ksort( $p_array );
	return $p_array;
}

?>