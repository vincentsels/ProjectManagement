<?php
form_security_validate( 'plugin_ProjectManagement_time_registration_delete' );

$t_bug_id = gpc_get_int( 'bug_id' );
$t_delete_id = gpc_get_int( 'delete_id' );

$t_table = plugin_table( 'work' );
$query_pull_timerecords = "SELECT * FROM $t_table WHERE id = $t_delete_id ORDER BY timestamp DESC";
$result_pull_timerecords = db_query($query_pull_timerecords);
$row = db_fetch_array( $result_pull_timerecords );

$t_user_id = auth_get_current_user_id();
if ( $row["user_id"] == $t_user_id) {
	access_ensure_bug_level( plugin_config_get( 'admin_own_threshold' ), $t_bug_id );
} else {
	access_ensure_bug_level( plugin_config_get( 'admin_threshold' ), $t_bug_id );
}
$query_delete = "DELETE FROM $t_table WHERE id = $t_delete_id";        
db_query($query_delete);

history_log_event_direct( $t_bug_id, plugin_lang_get( 'history_deleted' ), format_short_date( $row["book_date"] ) . ": " . number_format($row["minutes"], 2, ',', '.') . " h. " . print_plugin_minutes_type_string( $row["minutes_type"] ) . " (" . print_plugin_enum_string_selected_value( 'work_types', $row["work_type"] ) . ")", "deleted", $user );

form_security_purge( 'plugin_ProjectManagement_time_registration_update' );

if ( is_null( $f_redirect_page ) ) {
	$t_url = string_get_bug_view_url( $t_bug_id, auth_get_current_user_id() );
	print_successful_redirect( $t_url . "#time_registration" );
} else {
	print_successful_redirect( plugin_page( $f_redirect_page, true ) );
}
