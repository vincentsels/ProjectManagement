<?php
form_security_validate( 'plugin_ProjectManagement_resource_management' );

$t_user_array = explode( ',', gpc_get_string( 'users', null ) );

foreach ( $t_user_array as $t_user_id ) {
	$f_hourly_rate    = parse_float( gpc_get_string( 'hourly_rate_' . $t_user_id, null ) );
	$f_hours_per_week = gpc_get_int( 'hours_per_week_' . $t_user_id, null );
	$f_color          = gpc_get_int( 'color_' . $t_user_id, null );
	$f_deployability  = gpc_get_int( 'deployability_' . $t_user_id, null );
	$f_clear          = gpc_get_bool( 'clear_' . $t_user_id, false );

	if ( $f_clear ) {
		$t_resource_table = plugin_table( 'resource' );
		$t_query = "DELETE FROM $t_resource_table WHERE user_id = $t_user_id";
		db_query_bound( $t_query );
	} else 	if ( !empty( $f_hourly_rate ) || !empty( $f_hours_per_week ) || !empty( $f_color ) ) {
		resource_insert_or_update( $t_user_id, $f_hourly_rate, $f_hours_per_week, $f_color, $f_deployability );
	}
}

form_security_purge( 'plugin_ProjectManagement_resource_management' );
print_successful_redirect( plugin_page( 'resource_management_page', true ) );
?>