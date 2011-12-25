<?php
	form_security_validate( 'plugin_ProjectManagement_resource_management' );
	
	$t_user_array = explode( ',', gpc_get_string( 'users', null ) );
	
	foreach ( $t_user_array as $t_user_id )	{
		$f_hourly_rate = gpc_get_string( 'hourly_rate_' . $t_user_id, null );
		$f_hours_per_week = gpc_get_int( 'hours_per_week_' . $t_user_id, null );
		
		if ( !empty( $f_hourly_rate ) || !empty( $f_hours_per_week ) ) {
			insert_or_update_record( $t_user_id, $f_hourly_rate, $f_hours_per_week );
		}
	}
	
	function insert_or_update_record ( $p_user_id, $p_hourly_rate, $p_hours_per_week ) {
		$t_resource_table = plugin_table( 'resource' );
		
		$t_query_old_row = "SELECT user_id, hourly_rate, hours_per_week FROM $t_resource_table WHERE user_id = $p_user_id";
		$t_result_old_row = db_query_bound( $t_query_old_row );
		$t_old_row;
		
		if ( db_num_rows( $t_result_old_row ) == 1 ) {
			$t_old_row = db_fetch_array( $t_result_old_row );
		}
		
		if ( !isset( $t_old_row ) ) {
			$t_query_insert = "INSERT INTO $t_resource_table(user_id, hourly_rate, hours_per_week)
			VALUES ($p_user_id, $p_hourly_rate, $p_hours_per_week)";
			db_query_bound( $t_query_insert );
		}
		else
		{
			$t_query_update_update_clause = "UPDATE $t_resource_table SET ";
			$t_query_update_set_clause = array();
			$t_query_update_where_clause = " WHERE user_id = $p_user_id";
			
			if ( !empty( $p_hourly_rate ) ) {
				$t_query_update_set_clause[] = "hourly_rate = $p_hourly_rate";
			}
			if ( !empty( $p_hours_per_week ) ) {
				$t_query_update_set_clause[] = "hours_per_week = $p_hours_per_week";
			}
			
			db_query_bound( $t_query_update_update_clause . 
					implode( ', ', $t_query_update_set_clause ) . 
					$t_query_update_where_clause );
		}
	}

	form_security_purge( 'plugin_ProjectManagement_resource_management' );
	print_successful_redirect( plugin_page( 'resource_management_page', true ) );
?>