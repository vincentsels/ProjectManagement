<?php
form_security_validate( 'plugin_ProjectManagement_customer_delete' );

auth_reauthenticate();
access_ensure_global_level( plugin_config_get( 'admin_threshold' ) );

$f_customer_id = gpc_get_int( 'customer_id', null );

$t_customer_table = plugin_table( 'customer' );

$t_query = "DELETE FROM $t_customer_table WHERE id = $f_customer_id";
db_query_bound( $t_query );

form_security_purge( 'plugin_ProjectManagement_customer_delete' );
print_successful_redirect( plugin_page( 'customer_overview_page', true ) );
?>