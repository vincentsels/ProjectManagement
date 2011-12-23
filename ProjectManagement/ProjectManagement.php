<?php

class ProjectManagementPlugin extends MantisPlugin {

	function register() {
		$this->name = 'Project Management';
		$this->description = 'Project management plugin that adds advanced functionality for timetracking, estimations, reporting,...';
		$this->page = 'config_page';

		$this->version = '1.0.0';
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
				array( 'CreateIndexSQL', array( 'idx_plugin_pm_work_bug_id',
						plugin_table( 'work' ),
						'bug_id' ) )
		);
	}

	function config() {
		return array(
				'worktypes'   => '20:analysis,50:development,80:testing',
				'edit_estimates_threshold' => MANAGER,
				'include_bookdate_threshold' => REPORTER,
				'view_reports_threshold' => DEVELOPER
		);
	}

	function hooks() {
		return array(
				'EVENT_VIEW_BUG_EXTRA' => 'view_bug_time_registration',
				'EVENT_MENU_MAIN' => 'main_menu'
		);
	}

	function init() {
		require_once( 'ProjectManagementAPI.php' );
		require_once( 'date_api.php' );
		require_once( 'pages/html_api.php' );
	}
	
	function main_menu( $p_event, $p_bug_id ) {
		$t_reports_page = plugin_page( 'report_registration_page', false );
		$t_pagename = plugin_lang_get( 'reports' );
		if ( access_has_project_level( plugin_config_get( 'view_reports_threshold' ) ) ) {
			return '<a href="' . $t_reports_page . '">' . $t_pagename . '</a>';
		}
	}

	/**
	 * Show TimeTracking information when viewing bugs.
	 * @param string Event name
	 * @param int Bug ID
	 */
	function view_bug_time_registration( $p_event, $p_bug_id ) {
		$t_table = plugin_table('work');
		$t_est = PLUGIN_PM_EST;
		$t_done = PLUGIN_PM_DONE;
		$t_todo = PLUGIN_PM_TODO;

		# Fetch estimates for all work types
		$query_fetch_est = "SELECT work_type, minutes as est
			FROM $t_table 
			WHERE bug_id = $p_bug_id 
			AND minutes_type = $t_est";
		$result_fetch_est = db_query_bound($query_fetch_est);
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
		$t_worktypes = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'worktypes' ) );
		if ( sizeof( $t_worktypes ) > 1 ) {
			$t_worktypes[PLUGIN_PM_WORKTYPE_TOTAL] = plugin_lang_get( 'total' );
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
		
		foreach ( $t_worktypes as $t_worktype_code => $t_worktype_label ) {
			# Calculate remaining
			if ( !empty( $t_work[PLUGIN_PM_EST][$t_worktype_code] ) ) {
				$t_work[PLUGIN_PM_REMAINING][$t_worktype_code] = 
					$t_work[PLUGIN_PM_EST][$t_worktype_code] 
						- $t_work[PLUGIN_PM_DONE][$t_worktype_code];
				
				# Calculate difference between remaining and todo
				if ( !empty( $t_work[PLUGIN_PM_TODO][$t_worktype_code] ) ) {
					$t_work[PLUGIN_PM_DIFF][$t_worktype_code] =
					$t_work[PLUGIN_PM_EST][$t_worktype_code] 
						- $t_work[PLUGIN_PM_DONE][$t_worktype_code] 
						- $t_work[PLUGIN_PM_TODO][$t_worktype_code];
				} else {
					# If remaining is negative, this is the difference
					if ( $t_work[PLUGIN_PM_REMAINING][$t_worktype_code] <= 0 ) {
						$t_work[PLUGIN_PM_DIFF][$t_worktype_code] = $t_work[PLUGIN_PM_REMAINING][$t_worktype_code];
						$t_work[PLUGIN_PM_REMAINING][$t_worktype_code] = 0;
					}
					
					# If remaining was calculated but no todo was specified,
					# add this to the calculated remaining to the total column
					$t_work[PLUGIN_PM_TODO][PLUGIN_PM_WORKTYPE_TOTAL] += $t_work[PLUGIN_PM_REMAINING][$t_worktype_code];
				}
			}
		}

		?>
		<br /><a name="time_registration" id="time_registration"></a>
		<?php
		collapse_open( 'plugin_pm_time_reg' );
		?>
		<form name="time_registration" method="post" action="<?php echo plugin_page('time_registration') ?>" >
      	<?php echo form_security_field( 'plugin_ProjectManagement_time_registration' ) ?>
      	
		<input type="hidden" name="bug_id" value="<?php echo $p_bug_id; ?>">
		
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
				<td width="20%"><div align="center"><?php echo plugin_lang_get( 'work_type' ) ?></div></td>
				<td><div align="center"><?php echo plugin_lang_get( 'est' ) ?></div></td>
				<td width="20%"><div align="center"><?php echo plugin_lang_get( 'done' ) ?></div></td>
				<td width="25%"><div align="center"><?php echo plugin_lang_get( 'todo' ) ?></div></td>
				<td><div align="center"><?php echo plugin_lang_get( 'diff' ) ?></div></td>
			</tr>
		<?php 
		foreach ( $t_worktypes as $t_worktype_code => $t_worktype_label ) {
			?>
			<tr <?php echo ( $t_worktype_code == PLUGIN_PM_WORKTYPE_TOTAL ? 
					'class="row-category-history"' : helper_alternate_class() ) ?>>
					
				<td class="category"><?php echo $t_worktype_label ?></td> 
				
				<td>
				<?php 
				echo minutes_to_time( $t_work[PLUGIN_PM_EST][$t_worktype_code], true );
				if ( $t_worktype_code != PLUGIN_PM_WORKTYPE_TOTAL && 
						( empty( $t_work[PLUGIN_PM_EST][$t_worktype_code] ) || 
								access_has_bug_level( plugin_config_get( 'edit_estimates_threshold' ), $p_bug_id )  ) ) { 
					# Check whether est was already supplied, or user has rights to alter it regardless
					?>
					-> <input type="text" size="4" maxlength="7" autocomplete="off" name= <?php echo '"change_' . PLUGIN_PM_EST . '_' . $t_worktype_code . '"' ?>>
					<?php 
				}
				?>
				</td>
				
				<td>
				<?php 
				echo minutes_to_time( $t_work[PLUGIN_PM_DONE][$t_worktype_code], false );
				if ( $t_worktype_code != PLUGIN_PM_WORKTYPE_TOTAL ) { 
					?>
					+ <input type="text" size="4" maxlength="7" autocomplete="off" name= <?php echo '"add_' . PLUGIN_PM_DONE . '_' . $t_worktype_code . '"' ?>>
					<?php 
				}
				?>
				</td>
				
				<?php 
				if ( empty ( $t_work[PLUGIN_PM_TODO][$t_worktype_code] ) ) {
					# When todo was not supplied, display calculated remainder instead, in italic
					?>
					<td class="italic"><?php echo minutes_to_time( $t_work[PLUGIN_PM_REMAINING][$t_worktype_code], false ) ?>
					<?php 
				} else {
					# Todo was supplied, so display that
					?>
					<td><?php echo minutes_to_time( $t_work[PLUGIN_PM_TODO][$t_worktype_code], false ) ?>
					<?php
				}
				if ( $t_worktype_code != PLUGIN_PM_WORKTYPE_TOTAL ) { 
					?>
					-> <input type="text" size="4" maxlength="7" autocomplete="off" name= <?php echo '"change_' . PLUGIN_PM_TODO . '_' . $t_worktype_code . '"' ?>> 
					<?php 
					if ( !empty ( $t_work[PLUGIN_PM_TODO][$t_worktype_code] ) ) {
						?>
						<input type="checkbox" name= <?php echo '"clear_' . PLUGIN_PM_TODO . '_' . $t_worktype_code . '"' ?>> <?php echo plugin_lang_get( 'clear' ) ?>
					<?php 
					}
				}
				?>
				</td>
				
				<td <?php echo ( $t_work[PLUGIN_PM_DIFF][$t_worktype_code] < 0 ? 'class="negative"' : 'class="positive"' )  ?>>
					<?php echo minutes_to_time( abs( $t_work[PLUGIN_PM_DIFF][$t_worktype_code] ) ) ?></td>
				
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
				date_print_calendar();
				date_finish_calendar( 'book_date', 'trigger');
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
