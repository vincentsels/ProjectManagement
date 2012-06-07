<?php
$f_user_id = gpc_get_int( 'user_id', null );
$f_start_date = strtotime( str_replace( '/', '-', gpc_get_string( 'unavailability_remove', date( 'd/m/Y' ) ) ) );

if ( is_null( $f_start_date ) ) {
	error_parameters( plugin_lang_get( 'unavailability_period' ) );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

$t_from_preferences_page = false;
if ( is_null( $f_user_id ) ) {
	$t_from_preferences_page = true;
}

$t_table = plugin_table( 'resource_unavailable' );
$t_query = "DELETE FROM $t_table WHERE user_id = $f_user_id AND start_date = $f_start_date";
db_query_bound( $t_query );

log_event( LOG_FILTERING, $t_query );

if ( $t_from_preferences_page ) {
	print_successful_redirect( 'account_prefs_page.php' );
} else {
	print_successful_redirect( 'manage_user_edit_page.php?user_id=' . $f_user_id );
}