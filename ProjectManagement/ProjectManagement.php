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
						work_type          I2      NOTNULL DEFAULT '50',
						hours_type         I2      NOTNULL DEFAULT '0',
						hours              F(15,3) NOTNULL DEFAULT 0,
						bookdate           T       NOTNULL,
						timestamp          T       NOTNULL
						" ) ),
				array( 'CreateIndexSQL', array( 'idx_plugin_pm_work_bug_id',
						plugin_table( 'work' ),
						'bug_id' ) )
		);
	}

	function config() {
		return array(
				'worktypes'   => '20:analysis,50:development,80:testing'
		);
	}

	function hooks() {
		return array(
				'EVENT_VIEW_BUG_EXTRA' => 'view_bug_time_registration'
		);
	}

	function init() {
		require_once( 'ProjectManagementAPI.php' );
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
		$query_fetch_est = "SELECT work_type, hours as est
			FROM $t_table 
			WHERE bug_id = $p_bug_id 
			AND hours_type = $t_est";
		$result_fetch_est = db_query_bound($query_fetch_est);
		$num_fetch_est = db_num_rows( $result_fetch_est );

		# Fetch totals total of done of all work types
		$query_fetch_done = "SELECT work_type, SUM(hours) as done
			FROM $t_table 
			WHERE bug_id = $p_bug_id 
			AND hours_type = $t_done
			GROUP BY work_type";
		$result_fetch_done = db_query_bound($query_fetch_done);
		$num_fetch_done = db_num_rows( $result_fetch_done );

		# Fetch todo of all work types
		$query_fetch_todo = "SELECT work_type, hours as todo
			FROM $t_table 
			WHERE bug_id = $p_bug_id 
			AND hours_type = $t_todo";
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
		
		foreach ( $t_work[PLUGIN_PM_EST] as $t_worktype_code => $t_worktype_label ) {
			# Calculate remaining
			if ( $t_work[PLUGIN_PM_EST][$t_worktype_code] !== null ) {
				$t_work[PLUGIN_PM_REMAINING][$t_worktype_code] = 
					$t_work[PLUGIN_PM_EST][$t_worktype_code] 
						- $t_work[PLUGIN_PM_DONE][$t_worktype_code];
				
				# Calculate difference between remaining and todo
				if ( $t_work[PLUGIN_PM_TODO][$t_worktype_code] !== null ) {
					$t_work[PLUGIN_PM_DIFF][$t_worktype_code] =
					$t_work[PLUGIN_PM_EST][$t_worktype_code] 
						- $t_work[PLUGIN_PM_DONE][$t_worktype_code] 
						- $t_work[PLUGIN_PM_TODO][$t_worktype_code];
				}
			}
		}

		echo ( '<br />' );
		collapse_open( 'plugin_pm_time_reg' );
		?>
		<table class="width100" cellspacing="1">
			<tr>
				<td colspan="6" class="form-title">
				<?php
				collapse_icon( 'plugin_pm_time_reg' );
				echo plugin_lang_get( 'time_registration' );
				?></td>
			</tr>
			<tr class="row-category">
				<td><div align="center"><?php echo plugin_lang_get( 'worktype' ) ?></div>
				</td>
				<td><div align="center"><?php echo plugin_lang_get( 'est' ) ?></div>
				</td>
				<td><div align="center"><?php echo plugin_lang_get( 'done' ) ?></div>
				</td>
				<td><div align="center"><?php echo plugin_lang_get( 'todo' ) ?></div>
				</td>
				<td><div align="center"><?php echo plugin_lang_get( 'diff' ) ?></div>
				</td>
			</tr>
		<?php 
		foreach ( $t_worktypes as $t_worktype_code => $t_worktype_label ) {
			?>
			<tr <?php echo ( $t_worktype_code == PLUGIN_PM_WORKTYPE_TOTAL ? 
					'class="row-category-history"' : helper_alternate_class() ) ?>>
					
				<td class="category"><?php echo $t_worktype_label ?></td> 
				
				<td><?php echo hours_to_time( $t_work[PLUGIN_PM_EST][$t_worktype_code], true ) ?></td>
				
				<td><?php echo hours_to_time( $t_work[PLUGIN_PM_DONE][$t_worktype_code], true ) ?></td>
				
				<?php 
				if ( $t_work[PLUGIN_PM_TODO][$t_worktype_code] === null ) {
					# When todo was not supplied, display calculated remainder instead, in italic
				?>
				<td class="italic"><?php echo hours_to_time( $t_work[PLUGIN_PM_REMAINING][$t_worktype_code], true ) ?></td>
				<?php 
				}
				else {
					# Todo was supplied, so display that
				?>
				<td><?php echo hours_to_time( $t_work[PLUGIN_PM_TODO][$t_worktype_code], true ) ?></td>
				<?php
				}
				?>
				
				<td <?php echo ( $t_work[PLUGIN_PM_DIFF][$t_worktype_code] < 0 ? 'class="negative"' : 'class="positive"' )  ?>>
					<?php echo hours_to_time( abs( $t_work[PLUGIN_PM_DIFF][$t_worktype_code] ), true ) ?></td>
				
			</tr>
			<?php 
		}
		?>
		</table>
		<?php 
		collapse_closed( 'plugin_pm_time_reg' );
		?>
		
		<table class="width100" cellspacing="1">
			<tr>
				<td class="form-title" colspan="2">
				<?php 
				collapse_icon( 'plugin_pm_time_reg' ); 
				echo plugin_lang_get( 'time_registration' );
				?></td>
			</tr>
		</table>
		
		<?php
		collapse_end( 'plugin_pm_time_reg' );

	}

}
?>
