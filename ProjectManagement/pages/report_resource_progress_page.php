<?php

access_ensure_global_level( plugin_config_get( 'view_project_progress_threshold' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_pm_reports_menu( 'resource_progress_page' );

$f_target_version = gpc_get_string( 'target_version', null );
$f_user_id        = gpc_get_int( 'user_id', ALL_USERS );

$t_project_without_versions = false;

if ( is_null( $f_target_version ) ) {

	# When no version number is specified and 'all projects' is selected, the system can
	# not determine the desired version number. Display this as an information message.
	if ( helper_get_current_project() == ALL_PROJECTS ) {
		$t_project_without_versions = true;
	}

	# Attempt to get the most logical one - the first non-released
	$t_non_released = version_get_all_rows_with_subs( helper_get_current_project(), false, false );
	if ( count( $t_non_released ) > 0 ) {
		$f_target_version = $t_non_released[count( $t_non_released ) - 1]['version'];
	} else {
		$t_project_without_versions = true;
	}
}

if ( $t_project_without_versions ) {
	echo plugin_lang_get( 'project_without_versions' );
} else {

	# Release dates of previous and current version
	$t_project_version_table = 'mantis_project_version_table';
	$t_query_release_date_target  = "SELECT date_order
										   FROM $t_project_version_table v
										  WHERE v.version = '$f_target_version'";
	$t_result_release_date_target = db_query_bound( $t_query_release_date_target );
	$t_release_date_target_array  = db_fetch_array( $t_result_release_date_target );
	$t_release_date_target        = $t_release_date_target_array ? $t_release_date_target_array['date_order'] : time();

	# Next get the release date of the previous version
	$t_query_release_date_previous  = "SELECT max(date_order) as date_order
											 FROM $t_project_version_table v
											WHERE v.date_order < '$t_release_date_target'";
	$t_result_release_date_previous = db_query_bound( $t_query_release_date_previous );
	$t_release_date_previous_array  = db_fetch_array( $t_result_release_date_previous );
	$t_release_date_previous        = $t_release_date_previous_array ? $t_release_date_previous_array['date_order'] : time();

	# Create the objects
	$t_all_users = array();
	$t_all_bugs_ordered = array();

	# Get all non-planned work and assign to special project
	$t_bug_table             = db_get_table( 'mantis_bug_table' );
	$t_project_table         = db_get_table( 'mantis_project_table' );
	$t_category_table        = db_get_table( 'mantis_category_table' );
	$t_work_table            = plugin_table( 'work' );
	$t_res_unav_table        = plugin_table( 'resource_unavailable' );
	$t_done				 	 = PLUGIN_PM_DONE;

	$t_query = "SELECT pc.id as project_id, pc.name as project_name, c.id as category_id, c.name as category_name,
					   b.id, b.handler_id, sum(w.minutes) as minutes
				  FROM $t_work_table w
				  JOIN $t_bug_table b ON w.bug_id = b.id
				  JOIN $t_project_table pc ON b.project_id = pc.id
				  JOIN $t_category_table c ON b.category_id = c.id
				 WHERE w.book_date BETWEEN $t_release_date_previous AND $t_release_date_target
			 	   AND w.minutes_type = $t_done
			 	   AND b.handler_id <> 0
			 	   AND b.target_version <> '$f_target_version'
			 	   AND NOT EXISTS
			 	   (
			 	   SELECT 1
			 	     FROM $t_res_unav_table u
			 	    WHERE u.user_id = w.user_id
			 	      AND w.book_date between u.start_date and u.end_date
			 	      AND u.include_work = 0
			 	   )
				 GROUP BY pc.id, pc.name, c.id, c.name, b.id, b.handler_id
				 ORDER BY b.handler_id, b.date_submitted";
	$t_result = db_query_bound( $t_query );

	$t_previous_bug = null;
	$t_previous_handler_id = null;
	$t_default_worktype = plugin_config_get( 'default_worktype' );
	while ( $row = db_fetch_array( $t_result ) ) {
		# Get the user, or if it doesn't exist, skip it
		if ( array_key_exists( $row['handler_id'], $t_all_users ) ) {
			$t_user = $t_all_users[$row['handler_id']];
		} else {
			$t_user = new PlottableUser( $row['handler_id'] );
			$t_all_users[$row['handler_id']] = $t_user;
			$t_all_bugs_ordered[$row['handler_id']] = array();
		}

		if ( $row['handler_id'] != $t_previous_handler_id ) {
			$t_previous_bug = null;
		}

		# Check whether the unplanned project already exists for this user
		if ( array_key_exists( PLUGIN_PM_PROJ_ID_UNPLANNED, $t_user->children ) ) {
			$t_project = $t_user->children[PLUGIN_PM_PROJ_ID_UNPLANNED];
		} else {
			$t_project = new PlottableProject( PLUGIN_PM_PROJ_ID_UNPLANNED, plugin_lang_get( 'unplanned' ) );
			$t_user->children[PLUGIN_PM_PROJ_ID_UNPLANNED] = $t_project;
		}

		# Assign the bug to this project
		$t_bug = new PlottableBug( $row['id'], null, null, $t_previous_bug, $t_user );
		$t_bug->work_data[PLUGIN_PM_DONE][$t_default_worktype] = $row['minutes'];

		$t_project->children[$row['id']] = $t_bug;
		$t_all_bugs_ordered[$row['handler_id']][] = $t_bug;

		$t_previous_bug = $t_bug;
		$t_previous_handler_id = $row['handler_id'];
	}

	# Fetch all relevant bugs
	$t_result = get_all_tasks( $f_target_version, $f_user_id, OFF );

	$t_previous_bug = null;
	$t_previous_handler_id = null;
	while ( $row = db_fetch_array( $t_result ) ) {
		if ( $row['handler_id'] == 0) {
			continue;
		}
		# Check whether this user already exists and if not, create it
		if ( array_key_exists( $row['handler_id'], $t_all_users ) ) {
			$t_user = $t_all_users[$row['handler_id']];
		} else {
			$t_user = new PlottableUser( $row['handler_id'] );
			$t_all_users[$row['handler_id']] = $t_user;
			$t_all_bugs_ordered[$row['handler_id']] = array();
		}

		if ( $row['handler_id'] != $t_previous_handler_id ) {
			# Try to get the latest 'unplanned' bug
			if ( array_key_exists( PLUGIN_PM_PROJ_ID_UNPLANNED, $t_user->children ) &&
				count( $t_user->children[PLUGIN_PM_PROJ_ID_UNPLANNED] ) > 0 ) {
				$t_previous_bug = end(array_values($t_user->children[PLUGIN_PM_PROJ_ID_UNPLANNED]->children));
			} else {
				$t_previous_bug = null;
			}
		}

		# Check whether this project already exists and if not, create it
		if ( array_key_exists( $row['project_id'], $t_user->children ) ) {
			$t_project = $t_user->children[$row['project_id']];
		} else {
			$t_project = new PlottableProject( $row['project_id'], $row['project_name'] );
			$t_user->children[$row['project_id']] = $t_project;
		}

		# Check whether this category already exists in this project and if not, create it
		if ( array_key_exists( $row['category_id'], $t_project->children ) ) {
			$t_category = $t_project->children[$row['category_id']];
		} else {
			$t_category = new PlottableCategory( $row['category_id'], $row['category_name'] );
			$t_project->children[$row['category_id']] = $t_category;
		}

		# Check whether this bug already exists in this category and if not, add it
		if ( array_key_exists( $row['id'], $t_category->children ) ) {
			$t_bug = $t_category->children[$row['id']];
		} else {
			$t_bug = new PlottableBug( $row['id'], $row['weight'], $row['due_date'], $t_previous_bug, $t_user );
			$t_all_bugs_ordered[$row['handler_id']][] = $t_bug;
			$t_category->children[$row['id']] = $t_bug;
		}

		# Set the work data for this bug
		$t_bug->work_data[$row['minutes_type']][$row['work_type']] = $row['minutes'];

		$t_previous_bug = $t_bug;
		$t_previous_handler_id = $row['handler_id'];
	}

	# Re-organize sub projects
	# TODO

	# Calculate the bug data first, per user, in the correct order
	ProjectManagementCache::CacheResourceData();
	foreach ( $t_all_bugs_ordered as $user ) {
		foreach ( $user as $bug ) {
			$bug->calculate_data( $t_release_date_previous );
		}
	}

	# Next, calculate all other (groups of) tasks
	foreach ( $t_all_users as $user ) {
		$user->calculate_data( $t_release_date_previous );
	}

	# Get all non-working days and assign to special project
	# TODO

	# Plot everything
	foreach ( $t_all_users as $user ) {
		$user->plot();
	}
}