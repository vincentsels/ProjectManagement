<?php

class ProjectManagementDoneColumn extends MantisColumn {

	public $column = "done_column";
	public $sortable = false;

	private $cache = array();

	public function __construct() {
		$this->title = plugin_lang_get( 'done', 'ProjectManagement' );
	}

	public function cache( $p_bugs ) {
		if ( count( $p_bugs ) < 1 ) {
			return;
		}

		$t_work_table = plugin_table( 'work', 'ProjectManagement' );

		$t_bug_ids = array();
		foreach ( $p_bugs as $t_bug ) {
			$t_bug_ids[] = $t_bug->id;
		}

		$t_bug_ids = implode( ',', $t_bug_ids );

		$t_query  = "SELECT bug_id, sum(minutes) as minutes
                       FROM $t_work_table
                      WHERE bug_id IN ( $t_bug_ids ) AND minutes_type = 1
                      GROUP BY bug_id";
		$t_result = db_query_bound( $t_query );

		while ( $t_row = db_fetch_array( $t_result ) ) {
			$this->cache[$t_row['bug_id']] = $t_row['minutes'];
		}
	}

	public function display( $p_bug, $p_columns_target ) {
		if ( isset( $this->cache[$p_bug->id] ) ) {
			if ( $p_columns_target == COLUMNS_TARGET_VIEW_PAGE ||
				$p_columns_target == COLUMNS_TARGET_PRINT_PAGE
			) {
				echo minutes_to_time( $this->cache[$p_bug->id], false );
			} else {
				# In excel and csv, the users probably want this in hours
				echo number_format( $this->cache[$p_bug->id] / 60, 1 );
			}
		}
	}

}
