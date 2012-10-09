<?php

access_ensure_global_level( plugin_config_get( 'view_project_progress_threshold' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_pm_reports_menu( 'report_resource_progress_page' );

$f_target_version = gpc_get_string( 'target_version', null );
$f_from_version   = gpc_get_string( 'from_version', null );
$f_from_date	  = gpc_get_string( 'from_date', null );
$f_user_id        = gpc_get_int( 'user_id', ALL_USERS );
$f_group_by_projects = gpc_get_bool( 'group_by_projects', false );

$t_project_without_versions = false;
if ( empty( $f_target_version ) ) {
	# Attempt to get the most logical one - the first non-released
	$t_non_released = version_get_all_rows_with_subs( helper_get_current_project(), false, false );
	if ( count( $t_non_released ) > 0 && $t_non_released[count( $t_non_released ) - 1]['date_order'] > 1 ) {
		$f_target_version = $t_non_released[count( $t_non_released ) - 1]['version'];
	} else {
		$t_project_without_versions = true;
	}
}

if ( $t_project_without_versions ) {
	echo plugin_lang_get( 'project_without_versions' );
} else {
	# Release dates of previous and current version
	log_event( LOG_FILTERING, $f_target_version );
	$t_release_date_target = version_get_field( version_get_id( $f_target_version ), 'date_order' );
	$t_release_buffer = plugin_config_get( 'release_buffer' ) * 24 * 60 * 60;
	$t_last_dev_day = $t_release_date_target - $t_release_buffer;

	if ( !empty( $f_from_date ) ) {
		$t_release_date_previous = strtotime_safe( $f_from_date );
		$f_from_version = null;
	} else if ( !empty( $f_from_version ) ) {
		$t_release_date_previous = version_get_field( version_get_id( $f_from_version ), 'date_order' );
	} else {
		# Assume the version prior to the target version
		$t_project_version_table = db_get_table( 'mantis_project_version_table' );
		$t_query_release_date_previous  = "SELECT max(date_order) as date_order
										 FROM $t_project_version_table v
										WHERE v.date_order < '$t_release_date_target'";
		$t_result_release_date_previous = db_query_bound( $t_query_release_date_previous );
		$t_release_date_previous_array  = db_fetch_array( $t_result_release_date_previous );
		$t_release_date_previous        = empty( $t_release_date_previous_array['date_order'] ) ? time() :
			$t_release_date_previous_array['date_order'];
	}
	$t_reference_date_display = format_short_date( $t_release_date_previous );
	if ( !empty( $f_from_version ) ) {
		$t_reference_date_display .= ' (' . $f_from_version . ')';
	}
	$t_last_dev_day_display = format_short_date( $t_last_dev_day );
	$t_release_date_display = format_short_date( $t_release_date_target ) . ' (' . $f_target_version . ')';

	?>

<form name="project_progress" method="post"
	  action="<?php echo plugin_page( 'report_resource_progress_page' ) ?>">
<div class="center75">
	<table class="hide">
	<tr><td>
		<table class="width100" cellspacing="1">
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category"><?php echo plugin_lang_get( 'select_target_version' ), ': ' ?></td>
				<td colspan="3">
					<select name="target_version"><?php print_version_option_list( $f_target_version, null, VERSION_FUTURE, false, false ) ?></select>
				</td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category"><?php echo plugin_lang_get( 'select_from_version' ), ': ' ?></td>
				<td>
					<select name="from_version"><?php print_version_option_list( $f_from_version, null, VERSION_ALL, true, true ) ?></select>
				</td>
				<td class="category"><?php echo plugin_lang_get( 'select_from_date' ), ': ' ?></td>
				<td>
					<input type="text" size="8" maxlength="10" autocomplete="off" id="from_date" name="from_date"
							<?php
							if ( empty( $f_from_version ) ) {
								echo 'value="' . $f_from_date . '"';
							}
							?>
							>
					<?php
					date_print_calendar( 'from_date_cal' );
					date_finish_calendar( 'from_date', 'from_date_cal' );
					?>
				</td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category"><?php echo lang_get( 'username' ), ': ' ?></td>
				<td colspan="3">
					<select name="user_id">
						<option value="0" selected="selected"></option>
						<?php print_user_option_list( $f_user_id ) ?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="4" class="center"><input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>"></td>
			</tr>
		</table>
	</td>
	<td valign="top">
		<table class="width100" cellspacing="1">
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category"><?php echo plugin_lang_get( 'reference_date' ) ?></td>
				<td><?php echo $t_reference_date_display ?></td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category"><?php echo plugin_lang_get( 'last_dev_day' ) ?></td>
				<td><?php echo $t_last_dev_day_display ?></td>
			</tr>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category"><?php echo plugin_lang_get( 'release_date' ) ?></td>
				<td><?php echo $t_release_date_display ?></td>
			</tr>
		</table>
	</td></tr>
	</table>
</div>
<br/>

	<?php

	# Create the objects
	$t_all_users = array();
	$t_all_bugs_ordered = array();

	# Some constants to be used in subsequent queries
	$t_bug_table             = db_get_table( 'mantis_bug_table' );
	$t_project_table         = db_get_table( 'mantis_project_table' );
	$t_category_table        = db_get_table( 'mantis_category_table' );
	$t_work_table            = plugin_table( 'work' );
	$t_res_table       		 = plugin_table( 'resource' );
	$t_res_unav_table        = plugin_table( 'resource_unavailable' );
	$t_const_done		 	 = PLUGIN_PM_DONE;
	$t_const_all_users		 = ALL_USERS;

	# Populate the user and project structure
	# Only include users with hours per week filled in
	$t_query = "SELECT t.user_id
				  FROM $t_res_table t
				 WHERE t.hours_per_week > 0";
	$t_result = db_query_bound( $t_query );

	while ( $row = db_fetch_array( $t_result ) ) {
		$t_user = new PlottableUser( $row['user_id'] );
		$t_all_users[$row['user_id']] = $t_user;
		$t_all_bugs_ordered[$row['user_id']] = array();

		# Add the unplanned project for each user
		$t_project = new PlottableNotPlannedProject( $row['user_id'],
			$t_release_date_previous, $t_last_dev_day );
		$t_user->children[PLUGIN_PM_PROJ_ID_UNPLANNED] = $t_project;
	}

	# Get all non-planned work and assign to special project
	$t_query = "SELECT pc.id as project_id, pc.name as project_name, c.id as category_id, c.name as category_name,
					   b.id, b.handler_id, b.date_submitted, sum(w.minutes) as minutes, max(w.book_date) as book_date
				  FROM $t_work_table w
				  JOIN $t_bug_table b ON w.bug_id = b.id
				  JOIN $t_project_table pc ON b.project_id = pc.id
				  JOIN $t_category_table c ON b.category_id = c.id
				  JOIN $t_res_table r ON b.handler_id = r.user_id
				 WHERE w.book_date BETWEEN $t_release_date_previous AND $t_release_date_target
			 	   AND w.minutes_type = $t_const_done
			 	   AND b.handler_id <> 0
			 	   AND b.target_version <> '$f_target_version'
			 	   AND ($f_user_id = $t_const_all_users OR b.handler_id = $f_user_id)
			 	   AND NOT EXISTS
			 	   (
			 	   SELECT 1
			 	     FROM $t_res_unav_table u
			 	    WHERE u.user_id = w.user_id
			 	      AND w.book_date between u.start_date and u.end_date
			 	      AND u.include_work = 0
			 	   )
				 GROUP BY pc.id, pc.name, c.id, c.name, b.id, b.handler_id, b.date_submitted
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
			continue;
		}

		if ( $row['handler_id'] != $t_previous_handler_id ) {
			$t_previous_bug = null;
		}

		$t_project = $t_user->children[PLUGIN_PM_PROJ_ID_UNPLANNED];

		# Assign the bug to this project
		$t_bug = new PlottableBug( $t_release_date_previous, $row['handler_id'],
			$row['id'], null, null, $t_previous_bug, $t_user );
		$t_bug->set_work_data( PLUGIN_PM_DONE, $t_default_worktype, $row['minutes'], $row['book_date'] );

		$t_project->children[$row['id']] = $t_bug;
		$t_all_bugs_ordered[$row['handler_id']][] = $t_bug;

		$t_previous_bug = $t_bug;
		$t_previous_handler_id = $row['handler_id'];
	}

	# Calculate the bug data first, per user, in the correct order
	resource_cache_data();
	foreach ( $t_all_bugs_ordered as $user ) {
		foreach ( $user as $bug ) {
			$bug->calculate_data( $t_release_date_previous );
		}
	}

	# Next, calculate all other (groups of) tasks
	foreach ( $t_all_users as $user ) {
		foreach ( $user->children as $project ) {
			$project->calculate_data( $t_release_date_previous );
		}
	}

	# Fetch all relevant bugs
	$t_result = get_all_tasks( $f_target_version, $f_user_id, OFF );

	$t_previous_bug = null;
	$t_previous_handler_id = null;
	while ( $row = db_fetch_array( $t_result ) ) {
		if ( $row['handler_id'] == 0) {
			continue;
		}
		# Check whether this user already exists and if not, skip
		if ( array_key_exists( $row['handler_id'], $t_all_users ) ) {
			$t_user = $t_all_users[$row['handler_id']];
		} else {
			continue;
		}

		if ( $row['handler_id'] != $t_previous_handler_id ) {
			# Try to get the latest 'unplanned' bug
			if ( array_key_exists( PLUGIN_PM_PROJ_ID_UNPLANNED, $t_user->children ) &&
				count( $t_user->children[PLUGIN_PM_PROJ_ID_UNPLANNED] ) > 0 ) {

				$t_previous_bug = end( array_values( $t_user->children[PLUGIN_PM_PROJ_ID_UNPLANNED]->children ) );
			} else {
				$t_previous_bug = null;
			}
		}

		$t_actual_project_id = $row['project_id'];
		$t_actual_project_name = $row['project_name'];
		if ( !$f_group_by_projects ) {
			$t_actual_project_id = PLUGIN_PM_PROJ_ID_PLANNED;
			$t_actual_project_name = plugin_lang_get( 'planned' );
		}
		# Check whether this project already exists and if not, create it
		if ( array_key_exists( $t_actual_project_id, $t_user->children ) ) {
			$t_project = $t_user->children[$t_actual_project_id];
		} else {
			$t_project = new PlottableProject( $row['handler_id'],
				$t_actual_project_id, $t_actual_project_name );
			$t_user->children[$t_actual_project_id] = $t_project;
		}

		if ( $f_group_by_projects ) {
			# Check whether this category already exists in this project and if not, create it
			if ( array_key_exists( $row['category_id'], $t_project->children ) ) {
				$t_category = $t_project->children[$row['category_id']];
			} else {
				$t_category = new PlottableCategory( $row['handler_id'],
					$row['category_id'], $row['category_name'] );
				$t_project->children[$row['category_id']] = $t_category;
			}
		} else {
			$t_category = $t_project;
		}

		# Check whether this bug already exists in this category and if not, add it
		if ( array_key_exists( $row['id'], $t_category->children ) ) {
			$t_bug = $t_category->children[$row['id']];
		} else {
			$t_bug = new PlottableBug( $t_release_date_previous, $row['handler_id'], $row['id'],
				$row['weight'], $row['due_date'], $t_previous_bug, $t_user );
			$t_all_bugs_ordered[$row['handler_id']][] = $t_bug;
			$t_category->children[$row['id']] = $t_bug;
		}

		# Set the work data for this bug
		$t_bug->set_work_data( $row['minutes_type'], $row['work_type'], $row['minutes'], $row['book_date'] );

		$t_previous_bug = $t_bug;
		$t_previous_handler_id = $row['handler_id'];
	}

	# Re-organize sub projects
	# TODO

	foreach ( $t_all_bugs_ordered as $user ) {
		foreach ( $user as $bug ) {
			$bug->calculate_data( $t_release_date_previous );
		}
	}

	# Next, calculate all other (groups of) tasks
	foreach ( $t_all_users as $user ) {
		$user->calculate_data( $t_release_date_previous );
	}

	# Plot everything
	foreach ( $t_all_users as $user ) {
		$user->plot( $t_last_dev_day );
	}

	echo '</form>'; # The whole page must be a form to include the input elements from underlying sections
}