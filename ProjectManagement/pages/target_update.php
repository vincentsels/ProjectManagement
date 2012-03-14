<?php

form_security_validate( 'plugin_pm_targets_update' );

$f_bug_ids       = gpc_get_int_array( 'bug_ids' );
$f_redirect_page = gpc_get_string( 'redirect_page', null );

$t_work_types = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );
$t_work_type_thresholds = plugin_config_get( 'work_type_thresholds' );
$f_data 	  = array();

if ( count( $t_work_types ) > 0 ) {
	# Populate an array with the supplied data
	foreach ( $f_bug_ids as $t_bug_id ) {
		foreach ( $t_work_types as $t_work_type => $t_work_type_label ) {
			# Check whether this work type is enabled for the current user
			if ( array_key_exists( $t_work_type, $t_work_type_thresholds ) &&
				!access_has_global_level( $t_work_type_thresholds[$t_work_type] ) )
				continue; # Ignore otherwise

			$f_data[$t_bug_id][$t_work_type]["target_date"] =
				strtotime( str_replace( '/', '-',
					gpc_get_string( $t_bug_id . '_target_date_' . $t_work_type, date( 'd/m/Y' ), null ) ) );
			$f_data[$t_bug_id][$t_work_type]["owner_id"] =
				gpc_get_int( $t_bug_id . '_owner_id_' . $t_work_type, -1 );

			$t_completed_date_as_string = gpc_get_string( $t_bug_id . '_completed_date_' . $t_work_type, date( 'd/m/Y' ), null );
			if ( !is_null( $t_completed_date_as_string ) ) {
				$f_data[$t_bug_id][$t_work_type]["completed_date"] = strtotime( str_replace( '/', '-', $t_completed_date_as_string ) );
			}

			# Check for errors
			if ( !empty( $f_data[$t_bug_id][$t_work_type]["target_date"] ) &&
				$f_data[$t_bug_id][$t_work_type]["owner_id"] == -1 ) {
				error_parameters( $t_work_types[$t_work_type] );
				trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, E_USER_ERROR );
			}
		}
	}

	foreach ( $f_data as $t_bug_id => $t_work_type_data ) {
		foreach ( $t_work_type_data as $t_work_type => $t_data ) {
			if ( !empty( $t_data["target_date"] ) ) {
				target_update( $t_bug_id, $t_work_type,
					$t_data["owner_id"], $t_data["target_date"], $t_data["completed_date"] );
			}
		}
	}

	form_security_purge( 'plugin_pm_targets_update' );

	if ( is_null( $f_redirect_page ) ) {
		$t_url = string_get_bug_view_url( $t_bug_id, auth_get_current_user_id() );
		print_successful_redirect( $t_url . "#targets" );
	} else {
		print_successful_redirect( plugin_page( $f_redirect_page, true ) );
	}
}

?>