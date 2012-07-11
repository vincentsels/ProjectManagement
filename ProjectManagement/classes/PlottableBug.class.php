<?php

class PlottableBug extends PlottableTask {
	public $work_data;

	private $todo;
	private $previous_bug;

	public function __construct( $p_handler_id, $p_id, $p_weight, $p_due_date, $p_previous_bug ) {
		parent::__construct( $p_handler_id );
		$this->work_data = array();
		$this->type = PlottableTaskTypes::BUG;
		$this->id = $p_id;
		$this->weight = $p_weight;
		$this->due_date = $p_due_date;
		$this->previous_bug = $p_previous_bug;
		$this->calculated = false;
	}

	protected function calculate_data_specific( $p_reference_date ) {
		global $g_resources;

		# Step 1: Calculate the est, done, work and overdue

		$t_worktypes = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );

		if ( isset( $this->work_data[PLUGIN_PM_DONE] ) ) {
			foreach ( $this->work_data[PLUGIN_PM_DONE] as $t_value ) {
				@$this->done += $t_value;
			}
		}

		$this->todo = 0;
		foreach ( $t_worktypes as $t_work_type => $t_value ) {
			if ( isset( $this->work_data[PLUGIN_PM_TODO][$t_work_type] ) ) {
				$this->todo += $this->work_data[PLUGIN_PM_TODO][$t_work_type];
			} else if ( isset( $this->work_data[PLUGIN_PM_EST][$t_work_type] ) ) {
				$this->todo += max( $this->work_data[PLUGIN_PM_EST][$t_work_type] -
					@$this->work_data[PLUGIN_PM_DONE][$t_work_type], 0 );
			}
		}
		$this->todo = max( $this->todo, 0 );

		# Calculate the 'real estimate'
		$this->est = @$this->done + @$this->todo;

		$this->overdue = 0;
		foreach ( $t_worktypes as $t_work_type => $t_value ) {
			$this->overdue += @$this->work_data[PLUGIN_PM_DONE][$t_work_type] +
				@$this->work_data[PLUGIN_PM_TODO][$t_work_type] -
					@$this->work_data[PLUGIN_PM_EST][$t_work_type];
		}
		$this->overdue = max( $this->overdue, 0 );

		# Step 2: Next, calculate the start and finish dates

		if ( is_null( $this->previous_bug ) ) {
			$this->task_start = $p_reference_date;
		} else {
			$this->task_start = $this->previous_bug->task_end;
		}

		$this->calculate_actual_end_date( $this->task_start, $this->task_end, $this->est, $this->na );
	}

	public function plot_specific_start( $p_unique_id, $p_min_date, $p_max_date ) {
		if ( $this->id == PLUGIN_PM_DUMMY_BUG ) {
			return; # Don't show the dummy bug!
		}

		$t_total_width = $p_max_date - $p_min_date;
		$t_before = ( $this->task_start - $p_min_date ) / $t_total_width * 99;
		$t_task_width = ( $this->task_end - $this->task_start ) / $t_total_width * 99;

		if ( $this->est > 0 ) {
			$t_original_work_width = ( $this->done - $this->overdue ) / $this->est * 100;
			$t_total_work_width    = ( $this->done + $this->na ) / $this->est * 100;
			$t_na_with			   = $this->na / $this->est * 100;
			$t_extra_work_width    = $this->overdue / $this->est * 100;
		} else {
			$t_original_work_width = 0;
			$t_total_work_width    = 0;
			$t_na_with			   = 0;
			$t_extra_work_width    = 0;
		}

		$t_progress_info = minutes_to_time( $this->done, false ) . '&nbsp;/&nbsp;' . minutes_to_time( $this->est, false );
		$t_overdue_info = minutes_to_time( $this->overdue ) . '&nbsp;/&nbsp;' . minutes_to_time( $this->done );
		$t_progress_text = '<a href="#" class="invisible" title="' . $t_progress_info . '">' . number_format( $t_total_work_width, 1 ) . '%</a>';
		$t_na_text = '<a href="#" class="invisible" title="' . minutes_to_days( $this->na ) . ' ' . lang_get( 'days' ) . '"></a>';
		$t_overdue_text = '<a href="#" class="invisible" title="' . $t_overdue_info . '"></a>';
		$t_description = '<span class="description-info">: ' . bug_get_field( $this->id, 'summary' ) . '</span>';

		?>
	<tr class="progress-row row-2">
		<td width="15%">
			<?php print_bug_link( $this->id ) ?>
			<?php echo $t_description ?>
		</td>
		<td width="85%">
			<div class="resource-section">
			<span class="filler" style="width: <?php echo $t_before ?>%"></span>
			<?php print_progress_span( $this->handler_id, $t_task_width )  ?>
				<?php print_progressbar_span( $this->handler_id, $t_original_work_width )  ?>
					<?php echo $t_progress_text ?>
				</span><?php
				if ( $t_na_with > 0 ) {
					print_na_span( $t_na_with );
					echo $t_na_text . '</span>';
				}
				if ( $t_extra_work_width > 0 ) {
					print_overdue_span( $t_extra_work_width );
					echo $t_overdue_text . '</span>';
				}
				?>
			</span>
			</div>
		</td>
	</tr>
	<?php
	}
}