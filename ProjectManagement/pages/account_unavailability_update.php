<?php

function update_resource( $p_user_id ) {
	# Check for new period of unavailability
	$f_unavailable_start = strtotime_safe( gpc_get_string( 'period_start' ), true );
	$f_unavailable_end = strtotime_safe( gpc_get_string( 'period_end' ), true );
	$f_unavailable_type = gpc_get_int( 'unavailability_type', null );
	$f_unavailable_note = gpc_get_string( 'unavailability_note', null );

	if ( $f_unavailable_start !== false ) {
		# A period has been entered
		if ( empty( $f_unavailable_end ) ) {
			# Assume a period of one day
			$f_unavailable_end = $f_unavailable_start;
		} else if ( $f_unavailable_end < $f_unavailable_start ) {
			trigger_error( plugin_lang_get( 'error_enddate_before_startdate' ), E_USER_ERROR );
		}

		# Passed arguments are always timestamps of whole dates, at midnight
		# Periods should start at midnight but end one second before midnight
		$t_day = 60 * 60 * 24;
		$f_unavailable_end = $f_unavailable_end + $t_day - 1;

		# Availability type is required
		if ( is_null( $f_unavailable_type ) ) {
			error_parameters( plugin_lang_get( 'unavailability_type' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		$t_include_work = ( is_array( plugin_config_get( 'unavailability_ignore_work' ) ) &&
			in_array( $f_unavailable_type, plugin_config_get( 'unavailability_ignore_work' ) ) ) ? 0 : 1;

		resource_unavailability_period_add( $p_user_id, $f_unavailable_start, $f_unavailable_end,
			$f_unavailable_type, $t_include_work, $f_unavailable_note );
	}
}
	
form_security_validate( 'plugin_ProjectManagement_account_unavailability' );

$t_user_id = auth_get_current_user_id();

update_resource ( $t_user_id );

form_security_purge( 'plugin_ProjectManagement_account_unavailability' );

print_successful_redirect( plugin_page( 'account_unavailability_page', true ) );