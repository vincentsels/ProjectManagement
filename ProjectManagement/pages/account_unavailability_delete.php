<?php

function delete_resource( ) {
	$f_id = gpc_get_int( 'unavailability_remove', null );

	$t_table = plugin_table( 'resource_unavailable' );
	$t_query = "DELETE FROM $t_table WHERE id = $f_id";
	db_query_bound( $t_query );

}
	
form_security_validate( 'plugin_ProjectManagement_account_unavailability' );

$t_user_id = auth_get_current_user_id();

delete_resource();

form_security_purge( 'plugin_ProjectManagement_account_unavailability' );

print_successful_redirect( plugin_page( 'account_unavailability_page', true ) );