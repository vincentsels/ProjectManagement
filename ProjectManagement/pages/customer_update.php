<?php
form_security_validate( 'plugin_ProjectManagement_customer_update' );

auth_reauthenticate();
access_ensure_global_level( plugin_config_get( 'admin_threshold' ) );

$f_customer_id = gpc_get_int( 'customer_id', null );
$f_name = gpc_get_string( 'customer_name', null );
$f_share = parse_float( gpc_get_string( 'customer_share', 0 ) );
$f_can_approve = gpc_get_bool( 'customer_can_approve', 0 );

$t_customer_table = plugin_table( 'customer' );

if ( empty( $f_customer_id ) ) {
	# Insert new customer
	$t_query = "INSERT INTO $t_customer_table (name, share, can_approve)
				VALUES ('$f_name', $f_share, $f_can_approve)";
	db_query_bound( $t_query );
} else {
	# Update existing customer
	$t_query = "UPDATE $t_customer_table
				   SET name = '$f_name', share = $f_share, can_approve = $f_can_approve
				 WHERE id = $f_customer_id";
	db_query_bound( $t_query );
}

form_security_purge( 'plugin_ProjectManagement_config_update' );
print_successful_redirect( plugin_page( 'customer_overview_page', true ) );
?>