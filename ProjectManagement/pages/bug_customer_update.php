<?php

form_security_validate( 'plugin_ProjectManagement_bug_customer_update' );

$f_bug_ids       = gpc_get_int_array( 'bug_ids' );
$f_redirect_page = gpc_get_string( 'redirect_page', null );

$f_data 		 = array();

$t_customers	 = customer_get_all();

if ( count( $t_customers ) > 0 ) {

	$t_update_paying_cust = gpc_get_int('update_paying_cust', 0);
	$t_update_approving_cust = gpc_get_int('update_approving_cust', 0);
	$t_update_integration_dev = gpc_get_int('update_integration_dev', 0);

	# Populate an array with the supplied data
	foreach ( $f_bug_ids as $t_bug_id ) {
		foreach ( $t_customers as $t_cust ) {
			$f_data[$t_bug_id][PLUGIN_PM_CUST_PAYING][$t_cust['id']] =
				gpc_get_bool( $t_bug_id . '_' . PLUGIN_PM_CUST_PAYING . '_' . $t_cust['id'], false );
			$f_data[$t_bug_id][PLUGIN_PM_CUST_APPROVING][$t_cust['id']] =
				gpc_get_bool( $t_bug_id . '_' . PLUGIN_PM_CUST_APPROVING . '_' . $t_cust['id'], false );
		}
		# Add possible 'all customers'
		$f_data[$t_bug_id][PLUGIN_PM_CUST_PAYING][PLUGIN_PM_ALL_CUSTOMERS] =
			gpc_get_bool( $t_bug_id . '_' . PLUGIN_PM_CUST_PAYING . '_' . PLUGIN_PM_ALL_CUSTOMERS, false );
	}

	foreach ( $f_data as $t_bug_id => $t_bug_data ) {
		if ( $t_update_paying_cust == 1 ) {
			$t_paying_string = '';
			if ( count( $t_bug_data[PLUGIN_PM_CUST_PAYING] ) > 0 ) {
				foreach ( $t_bug_data[PLUGIN_PM_CUST_PAYING] as $t_cust_id => $t_selected ) {
					if ( $t_selected ) {
						$t_paying_string .= PLUGIN_PM_CUST_CONCATENATION_CHAR . $t_cust_id;
					}
				}
			}
			bug_customer_update_or_insert( $t_bug_id, $t_paying_string, PLUGIN_PM_CUST_PAYING );
		}

		if ( $t_update_approving_cust == 1 ) {
			$t_approving_string = '';
			if ( count( $t_bug_data[PLUGIN_PM_CUST_APPROVING] ) > 0 ) {
				foreach ( $t_bug_data[PLUGIN_PM_CUST_APPROVING] as $t_cust_id => $t_selected ) {
					if ( $t_selected && $t_customers[$t_cust_id]['can_approve'] == 1 ) {
						$t_approving_string .= PLUGIN_PM_CUST_CONCATENATION_CHAR . $t_cust_id;
					}
				}
			}
			bug_customer_update_or_insert( $t_bug_id, $t_approving_string, PLUGIN_PM_CUST_APPROVING );
		}

		if ( $t_update_integration_dev == 1 ) {
			$t_integration_custom_dev_string = '';
			if ( gpc_get_bool( $t_bug_id . '_' . PLUGIN_PM_CUST_INTEGRATION_DEV . '_' . PLUGIN_PM_ALL_CUSTOMERS, false ) ) {
				$t_integration_custom_dev_string = PLUGIN_PM_CUST_CONCATENATION_CHAR . PLUGIN_PM_ALL_CUSTOMERS;
			}
			bug_customer_update_or_insert( $t_bug_id, $t_integration_custom_dev_string, PLUGIN_PM_CUST_INTEGRATION_DEV );
		}
	}

	form_security_purge( 'plugin_ProjectManagement_bug_customer_update' );

	if ( is_null( $f_redirect_page ) ) {
		$t_url = string_get_bug_view_url( $t_bug_id, auth_get_current_user_id() );
		print_successful_redirect( $t_url . "#customer_section" );
	} else {
		print_successful_redirect( plugin_page( $f_redirect_page, true ) );
	}
}

?>