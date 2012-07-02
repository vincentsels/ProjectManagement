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

	# Step 1: Fetch all relevant bugs
	$t_result = get_all_tasks( $f_target_version, $f_user_id, OFF );

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


	# Step 2: Create the objects
	$t_all_users = array();
	$t_all_bugs_ordered = array();

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
			$t_previous_bug = null;
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
			$t_all_bugs_ordered[] = $t_bug;
			$t_category->children[$row['id']] = $t_bug;
		}

		# Set the work data for this bug
		$t_bug->work_data[$row['minutes_type']][$row['work_type']] = $row['minutes'];

		$t_previous_bug = $t_bug;
		$t_previous_handler_id = $row['handler_id'];
	}

	# Step 3: re-organize sub projects
	# TODO

	# Step 4: calculate the bug data first in the correct order
	ProjectManagementCache::CacheResourceData();
	foreach ( $t_all_bugs_ordered as $bug ) {
		$bug->calculate_data( $t_release_date_previous );
	}

	# Next, calculate all other tasks
	foreach ( $t_all_users as $user ) {
		$user->calculate_data( $t_release_date_previous );
	}

	# Step 5: plot everything
	foreach ( $t_all_users as $user ) {
		$user->plot();
	}
}