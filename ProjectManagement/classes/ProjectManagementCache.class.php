<?php

class ProjectManagementCache {
	public static $resource_cache;
	public static function CacheResourceData() {
		$t_user_table     = db_get_table( 'mantis_user_table' );
		$t_resource_table = plugin_table( 'resource' );
		$t_query = "SELECT u.id, r.hours_per_week
				      FROM $t_user_table u
		   LEFT OUTER JOIN $t_resource_table r ON u.id = r.user_id
				     WHERE u.enabled = 1
			      ORDER BY access_level DESC, username";
		$t_result     = db_query_bound( $t_query );
		while ( $row = db_fetch_array( $t_result ) ) {
			self::$resource_cache[$row['id']] = $row['hours_per_week'];
		}
	}
}