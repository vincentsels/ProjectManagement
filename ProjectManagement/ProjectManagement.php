<?php

class ProjectManagementPlugin extends MantisPlugin {

	function register() {
		$this->name = 'Project Management';
		$this->description = 'Project management plugin that adds advanced functionality for timetracking, estimations, reporting,...';
		$this->page = 'config_page';

		$this->version = '1.1.0';
		$this->requires = array(
				'MantisCore' => '1.2.0'
		);

		$this->author = 'Vincent Sels';
		$this->contact = 'vincent_sels@hotmail.com';
		$this->url = '';
	}

	function schema() {
		return array(
				array( 'CreateTableSQL', array( plugin_table( 'work' ), "
						id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
						bug_id             I       NOTNULL UNSIGNED,
						user_id            I       NOTNULL UNSIGNED,
						work_type          I2      NOTNULL DEFAULT 50,
						minutes_type       I2      NOTNULL DEFAULT 0,
						minutes            I 	   NOTNULL DEFAULT 0,
						book_date          I,
						timestamp          I
						" ) ),
				array( 'CreateIndexSQL', array( 'idx_plugin_pm_work_bug_id', # used operationally
						plugin_table( 'work' ), 'bug_id' ) ),
				array( 'CreateTableSQL', array( plugin_table( 'resource' ), "
						user_id            I       NOTNULL UNSIGNED PRIMARY,
						hours_per_week	   I	   UNSIGNED,
						hourly_rate        F(3,2)
						" ) ),
				array( 'CreateIndexSQL', array( 'idx_plugin_pm_work_user_id_book_date',  # used for reporting
						plugin_table( 'work' ), 'user_id, book_date' ) ),
				array ( 'AddColumnSQL', array( plugin_table( 'resource'), 'color I UNSIGNED' ) )
		);
	}

	function config() {
		return array(
				'work_types' => '20:analysis,50:development,80:testing',
				'enable_time_registration_threshold' => DEVELOPER,
				'view_time_registration_summary_threshold' => REPORTER,
				'edit_estimates_threshold' => MANAGER,
				'include_bookdate_threshold' => DEVELOPER,
				'view_registration_worksheet_threshold' => DEVELOPER,
				'view_registration_report_threshold' => DEVELOPER,
				'view_resource_management_threshold' => MANAGER,
				'view_resource_allocation_threshold' => DEVELOPER,
          		'admin_threshold' => ADMINISTRATOR,
				'work_type_thresholds' => array( 50 => DEVELOPER ),
				'default_worktype' => 50,
				'dark_saturation' => 33,
				'dark_lightness' => 45,
				'light_saturation' => 100,
				'light_lightness' => 90,
				'display_detailed_bug_link' => TRUE,
				'finish_upon_resolving' => array( 20, 50 ),
				'finish_upon_closing' => array ( 80 )
		);
	}

	function hooks() {
		return array(
				'EVENT_VIEW_BUG_DETAILS' => 'view_bug_time_registration_summary',
				'EVENT_VIEW_BUG_EXTRA' => 'view_bug_time_registration',
				'EVENT_MENU_MAIN' => 'main_menu',
				'EVENT_BUG_DELETED' => 'delete_time_registration',
				'EVENT_REPORT_BUG' => 'bug_set_recently_visited',
				'EVENT_UPDATE_BUG' => 'bug_finish_worktypes',
				'EVENT_FILTER_COLUMNS' => 'filter_columns',
				'EVENT_LAYOUT_RESOURCES' => 'link_files'
		);
	}

	function init() {
		require_once( 'ProjectManagementAPI.php' );
		require_once( 'date_api.php' );
		require_once( 'pages/html_api.php' );
		require_once( 'classes/MantisPmProject.class.php' );
		require_once( 'classes/MantisPmCategory.class.php' );
		require_once( 'classes/MantisPmBug.class.php' );
	}

	function link_files() {
		return '<link rel="stylesheet" type="text/css" href="' . plugin_file( 'style.css' ) . '"/>' . "\n" .
		'<script type="text/javascript" src="' . plugin_file( 'script.js' ) . '"></script>';
	}
	
	function filter_columns()
	{
		require_once( 'classes/ProjectManagementDoneColumn.class.php' );
		require_once( 'classes/ProjectManagementEstColumn.class.php' );
		require_once( 'classes/ProjectManagementTodoColumn.class.php' );
		return array(
				'ProjectManagementEstColumn',
				'ProjectManagementDoneColumn',
				'ProjectManagementTodoColumn'
		);
	}
	
	function main_menu( $p_event, $p_bug_id ) {
		$t_pagename = plugin_lang_get( 'reports' );
		# Only display main menu if at least one of the submenus is accessible
		if ( access_has_global_level( plugin_config_get( 'view_registration_worksheet_threshold' ) ) ) {
			$t_reports_page = plugin_page( 'time_registration_page', false );
		} else if ( access_has_global_level( plugin_config_get( 'view_registration_report_threshold' ) ) ) {
			$t_reports_page = plugin_page( 'time_registration_page', false );
		} else if ( access_has_global_level( plugin_config_get( 'view_resource_management_threshold' ) ) ) {
			$t_reports_page = plugin_page( 'time_registration_page', false );
		} else if ( access_has_global_level( plugin_config_get( 'view_resource_allocation_threshold' ) ) ) {
			$t_reports_page = plugin_page( 'time_registration_page', false );
		}
		if ( isset( $t_reports_page ) ) {
			return '<a href="' . $t_reports_page . '">' . $t_pagename . '</a>';
		}
	}
	
	function bug_set_recently_visited( $p_event, $p_bug_data, $p_bug_id ) {
		recently_visited_bug_add( $p_bug_id );
	}
	
	/**
	 * Upon resolving or closing a bug, 'finish' certain work types.
	 */
	function bug_finish_worktypes( $p_event, $p_bug_data, $p_bug_id ) {
		if ( $p_bug_data->status >= config_get( 'bug_resolved_status_threshold' ) ) {
			# The bug was resolved: if there was any work todo left, clear it
			$t_work_types_to_finish_upon_resolving = plugin_config_get( 'finish_upon_resolving' );
			if ( is_array( $t_work_types_to_finish_upon_resolving ) ) {
				foreach ( $t_work_types_to_finish_upon_resolving as $t_work_type ) {
					set_work( $p_bug_id, $t_work_type, PLUGIN_PM_TODO, 0, null, ACTION::INSERT_OR_UPDATE);
				}
			}
		}
		
		if ( $p_bug_data->status >= config_get( 'bug_closed_status_threshold' ) ) {
			# The bug was closed: if there was any work todo left, clear it
			$t_work_types_to_finish_upon_closing = plugin_config_get( 'finish_upon_closing' );
			if ( is_array( $t_work_types_to_finish_upon_closing ) ) {
				foreach ( $t_work_types_to_finish_upon_closing as $t_work_type ) {
					set_work( $p_bug_id, $t_work_type, PLUGIN_PM_TODO, 0, null, ACTION::INSERT_OR_UPDATE);
				}
			}
		}
	}

	/**
	 * @todo fix hours todo (add 'calculated' hours)
	 */
	function view_bug_time_registration_summary( $p_event, $p_bug_id ) {
		if ( !access_has_project_level( plugin_config_get( 'view_time_registration_summary_threshold' ) ) ) {
			return;
		}
		
		recently_visited_bug_add( $p_bug_id );
		
		# Fetch time registration summary
		$t_table = plugin_table('work');
		$query = "SELECT minutes_type, sum(minutes) as minutes
			FROM $t_table 
			WHERE bug_id = $p_bug_id
			GROUP BY minutes_type";
		$result = db_query_bound( $query );
		$num = db_num_rows( $result );
		
		$t_summary_info = array();
		for ( $i = 0; $i < $num; $i++ ) {
			$row = db_fetch_array( $result );
			$t_summary_info[$row['minutes_type']] = $row['minutes'];
		}
		$t_est_total = minutes_to_time( $t_summary_info[0], true );
		$t_done_total = minutes_to_time( $t_summary_info[1], false );
		$t_todo_total = minutes_to_time( $t_summary_info[2], true );
		
		echo '<tr ' . helper_alternate_class() . '>';
		echo '<td class="category">Est</td><td>' . $t_est_total . '</td>
		<td class="category">Done</td><td>' . $t_done_total . '</td>
		<td class="category">Todo</td><td>' . $t_todo_total . '</td></tr>';
	}
	
	function delete_time_registration ( $p_event, $p_bug_id ) {
		$t_table = plugin_table('work');
		$t_query = "DELETE FROM $t_table WHERE bug_id = $p_bug_id";
		db_query_bound( $t_query );
	}

	function view_bug_time_registration( $p_event, $p_bug_id ) {
		if ( !access_has_project_level( plugin_config_get( 'enable_time_registration_threshold' ) ) ) {
			return;
		}
		
		$t_table = plugin_table('work');
		$t_est = PLUGIN_PM_EST;
		$t_done = PLUGIN_PM_DONE;
		$t_todo = PLUGIN_PM_TODO;

		# Fetch estimates for all work types
		$query_fetch_est = "SELECT work_type, minutes as est
			FROM $t_table 
			WHERE bug_id = $p_bug_id 
			AND minutes_type = $t_est";
		$result_fetch_est = db_query_bound( $query_fetch_est );
		$num_fetch_est = db_num_rows( $result_fetch_est );

		# Fetch totals total of done of all work types
		$query_fetch_done = "SELECT work_type, SUM(minutes) as done
			FROM $t_table 
			WHERE bug_id = $p_bug_id 
			AND minutes_type = $t_done
			GROUP BY work_type";
		$result_fetch_done = db_query_bound($query_fetch_done);
		$num_fetch_done = db_num_rows( $result_fetch_done );

		# Fetch todo of all work types
		$query_fetch_todo = "SELECT work_type, minutes as todo
			FROM $t_table 
			WHERE bug_id = $p_bug_id 
			AND minutes_type = $t_todo";
		$result_fetch_todo = db_query_bound($query_fetch_todo);
		$num_fetch_todo = db_num_rows( $result_fetch_todo );
		
		# Get the different worktypes as an array
		$t_work_types = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );
		
		# Remove worktypes which are off limits for this account
		$t_limited_work_types = plugin_config_get( 'work_type_thresholds' );
		foreach ( $t_limited_work_types as $t_work_type => $t_min_access_level ) {
			if ( access_get_global_level() < $t_min_access_level ) {
				unset( $t_work_types[$t_work_type] );
			}
		}
		
		# Include total
		if ( sizeof( $t_work_types ) > 1 ) {
			$t_work_types[PLUGIN_PM_WORKTYPE_TOTAL] = plugin_lang_get( 'total' );
		}
		
		# Build a two-dimentional array with the data
		$t_work = array(PLUGIN_PM_EST => array(), PLUGIN_PM_DONE => array(), PLUGIN_PM_TODO => array());
		for ( $i=0; $i < $num_fetch_est; $i++ ) {
			$row = db_fetch_array( $result_fetch_est );
			$t_work[PLUGIN_PM_EST][$row["work_type"]] = $row["est"];
			$t_work[PLUGIN_PM_EST][PLUGIN_PM_WORKTYPE_TOTAL] += $row["est"];
		}
		for ( $i=0; $i < $num_fetch_done; $i++ ) {
			$row = db_fetch_array( $result_fetch_done );
			$t_work[PLUGIN_PM_DONE][$row["work_type"]] = $row["done"];
			$t_work[PLUGIN_PM_DONE][PLUGIN_PM_WORKTYPE_TOTAL] += $row["done"];
		}
		for ( $i=0; $i < $num_fetch_todo; $i++ ) {
			$row = db_fetch_array( $result_fetch_todo );
			$t_work[PLUGIN_PM_TODO][$row["work_type"]] = $row["todo"];
			$t_work[PLUGIN_PM_TODO][PLUGIN_PM_WORKTYPE_TOTAL] += $row["todo"];
		}
		
		foreach ( $t_work_types as $t_work_type_code => $t_work_type_label ) {
			# Calculate remaining
			if ( isset( $t_work[PLUGIN_PM_EST][$t_work_type_code] ) ) {
				$t_work[PLUGIN_PM_REMAINING][$t_work_type_code] = 
					$t_work[PLUGIN_PM_EST][$t_work_type_code] 
						- $t_work[PLUGIN_PM_DONE][$t_work_type_code];
				
				# Calculate difference between remaining and todo
				if ( isset( $t_work[PLUGIN_PM_TODO][$t_work_type_code] ) ) {
					$t_work[PLUGIN_PM_DIFF][$t_work_type_code] =
					$t_work[PLUGIN_PM_EST][$t_work_type_code] 
						- $t_work[PLUGIN_PM_DONE][$t_work_type_code] 
						- $t_work[PLUGIN_PM_TODO][$t_work_type_code];
				} else {
					# If remaining is negative, this is the difference
					if ( $t_work[PLUGIN_PM_REMAINING][$t_work_type_code] <= 0 ) {
						$t_work[PLUGIN_PM_DIFF][$t_work_type_code] = $t_work[PLUGIN_PM_REMAINING][$t_work_type_code];
						$t_work[PLUGIN_PM_REMAINING][$t_work_type_code] = 0;
					}
					
					# If remaining was calculated but no todo was specified,
					# add this to the calculated remaining to the total column
					$t_work[PLUGIN_PM_TODO][PLUGIN_PM_WORKTYPE_TOTAL] += $t_work[PLUGIN_PM_REMAINING][$t_work_type_code];
				}
			}
		}

		?>
		<br /><a name="time_registration" id="time_registration"></a>
		<?php
		collapse_open( 'plugin_pm_time_reg' );
		?>
		<form name="time_registration" method="post" action="<?php echo plugin_page('time_registration_update') ?>" >
      	<?php echo form_security_field( 'plugin_ProjectManagement_time_registration_update' ) ?>
      	
      	<?php printf( "<input type=\"hidden\" name=\"bug_ids[]\" value=\"%d\" />", $p_bug_id ); ?>
		
		<table class="width50" cellspacing="1">
			<tr>
				<td colspan="100%" class="form-title">
				<?php
				collapse_icon( 'plugin_pm_time_reg' );
				echo plugin_lang_get( 'time_registration' );
				?>
				</td>
			</tr>
			<tr class="row-category">
				<td width="20%" style="min-width:130px"><div align="center"><?php echo plugin_lang_get( 'work_type' ) ?></div></td>
				<td style="min-width:130px"><div align="center"><?php echo plugin_lang_get( 'est' ) ?></div></td>
				<td width="20%" style="min-width:130px"><div align="center"><?php echo plugin_lang_get( 'done' ) ?></div></td>
				<td width="30%" style="min-width:200px"><div align="center"><?php echo plugin_lang_get( 'todo' ) ?></div></td>
				<td style="min-width:100px"><div align="center"><?php echo plugin_lang_get( 'diff' ) ?></div></td>
			</tr>
		<?php 
		foreach ( $t_work_types as $t_work_type_code => $t_work_type_label ) {
			?>
			<tr <?php echo ( $t_work_type_code == PLUGIN_PM_WORKTYPE_TOTAL ? 
					'class="row-category-history"' : helper_alternate_class() ) ?>>
					
				<td class="category"><?php echo $t_work_type_label ?></td> 
				
				<td>
				<?php 
				echo minutes_to_time( $t_work[PLUGIN_PM_EST][$t_work_type_code], true );
				if ( $t_work_type_code != PLUGIN_PM_WORKTYPE_TOTAL && 
						( !isset( $t_work[PLUGIN_PM_EST][$t_work_type_code] ) || 
								access_has_bug_level( plugin_config_get( 'edit_estimates_threshold' ), $p_bug_id )  ) ) { 
					# Check whether est was already supplied, or user has rights to alter it regardless
					?>
					-> <input type="text" size="4" maxlength="7" autocomplete="off" name= <?php echo '"change_' . $p_bug_id . '_' . PLUGIN_PM_EST . '_' . $t_work_type_code . '"' ?>>
					<?php 
				}
				?>
				</td>
				
				<td>
				<?php 
				echo minutes_to_time( $t_work[PLUGIN_PM_DONE][$t_work_type_code], false );
				if ( $t_work_type_code != PLUGIN_PM_WORKTYPE_TOTAL ) { 
					?>
					+ <input type="text" size="4" maxlength="7" autocomplete="off" name= <?php echo '"add_' . $p_bug_id . '_' . PLUGIN_PM_DONE . '_' . $t_work_type_code . '"' ?>>
					<?php 
				}
				?>
				</td>
				
				<?php 
				if ( !isset( $t_work[PLUGIN_PM_TODO][$t_work_type_code] ) ) {
					# When todo was not supplied, display calculated remainder instead, in italic
					?>
					<td class="italic"><?php echo minutes_to_time( $t_work[PLUGIN_PM_REMAINING][$t_work_type_code], false ) ?>
					<?php 
				} else {
					# Todo was supplied, so display that
					?>
					<td><?php echo minutes_to_time( $t_work[PLUGIN_PM_TODO][$t_work_type_code], false ) ?>
					<?php
				}
				if ( $t_work_type_code != PLUGIN_PM_WORKTYPE_TOTAL ) { 
					?>
					-> <input type="text" size="4" maxlength="7" autocomplete="off" name= <?php echo '"change_' . $p_bug_id . '_' . PLUGIN_PM_TODO . '_' . $t_work_type_code . '"' ?>> 
					<?php 
					if ( isset( $t_work[PLUGIN_PM_TODO][$t_work_type_code] ) ) {
						?>
						<input type="checkbox" name= <?php echo '"clear_' . $p_bug_id . '_' . PLUGIN_PM_TODO . '_' . $t_work_type_code . '"' ?>> <?php echo plugin_lang_get( 'clear' ) ?>
					<?php 
					}
				}
				?>
				</td>
				
				<td <?php echo ( $t_work[PLUGIN_PM_DIFF][$t_work_type_code] < 0 ? 'class="negative"' : 'class="positive"' )  ?>>
					<?php echo minutes_to_time( abs( $t_work[PLUGIN_PM_DIFF][$t_work_type_code] ) ) ?></td>
				
			</tr>
			<?php 
		}
		?>
		
		<tr>
		<td colspan="100%">
		<input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>">
		<?php
			if ( access_has_bug_level( plugin_config_get( 'include_bookdate_threshold' ), $p_bug_id ) ) {
				echo plugin_lang_get( 'book_date' ) . ': ';
				echo '<input type="text" size="8" maxlength="10" autocomplete="off" id="book_date" name="book_date" value="' . date('d/m/Y') . '">';
				date_print_calendar( 'book_date_cal' );
				date_finish_calendar( 'book_date', 'book_date_cal');
			}
		?>
		</td>
		</tr>
		</table>
		<?php 
		collapse_closed( 'plugin_pm_time_reg' );
		?>
		
		<table class="width50" cellspacing="1">
			<tr>
				<td class="form-title" colspan="2">
				<?php 
				collapse_icon( 'plugin_pm_time_reg' ); 
				echo plugin_lang_get( 'time_registration' );
				?></td>
			</tr>
		</table>
		</form>
		
		<?php
		collapse_end( 'plugin_pm_time_reg' );

	}

}
?>
