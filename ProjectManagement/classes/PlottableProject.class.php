<?php

class PlottableProject extends PlottableTask {
	public function __construct( $p_handler_id, $p_id, $p_name ) {
		parent::__construct( $p_handler_id );
		$this->type = PlottableTaskTypes::PROJECT;
		$this->id = $p_id;
		$this->name = $p_name;
	}

	public function plot_specific_start( $p_unique_id, $p_last_dev_day, $p_min_date, $p_max_date ) {
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

		$t_start = format_short_date( $this->task_start );
		$t_finish = format_short_date( $this->task_end );
		$t_info = '<a href="#" class="invisible" title="' . $t_start . ' - ' . $t_finish . '">';
		$t_text =  number_format( $t_total_work_width, 1 ) . '%';

		if ( $this->id == PLUGIN_PM_PROJ_ID_UNPLANNED || $this->id == PLUGIN_PM_PROJ_ID_NONWORKING ) {
			$t_project_name = $this->name;
		} else {
			$t_project_name = project_get_name( $this->id );
		}
		?>
	<tr class="progress-row row-category">
		<td width="15%" style="text-align:left;">
			<?php
			print_expand_icon_start( $p_unique_id );
			echo $t_project_name;
			print_expand_icon_end();
			?>
		</td>
		<td width="85%" style="text-align:left;">
			<div class="resource-section">
				<span class="filler" style="width: <?php echo $t_before ?>%"></span>
				<?php echo $t_info ?>
				<?php print_progress_span( $this->handler_id, $t_task_width, $this->task_end > $p_last_dev_day )  ?>
				<?php print_progressbar_span( $this->handler_id, $t_original_work_width )  ?>
				<?php echo $t_text ?>
				</span><?php
				if ( $t_na_with > 0 ) {
					print_na_span( $t_na_with );
					echo '</span>';
				}
				if ( $t_extra_work_width > 0 ) {
					print_overdue_span( $t_extra_work_width );
					echo '</span>';
				}
				?>
				</span></a>
			</div>
		</td>
	</tr>
	<?php
		echo '<tr><td colspan="2">';
		print_expandable_div_start( $p_unique_id );
		echo '<table class="width100" cellspacing="1">';
	}

	protected function plot_specific_end( $p_unique_id ) {
		echo '</table>';
		print_expandable_div_end();
		echo '</td></tr>';
	}
}

class PlottableNotPlannedProject extends PlottableProject {
	private $period_start;
	private $period_end;

	public function __construct( $p_handler_id, $p_period_start, $p_period_end ) {
		parent::__construct( $p_handler_id,
			PLUGIN_PM_PROJ_ID_UNPLANNED,
			plugin_lang_get( 'unplanned' ) );

		$this->type = PlottableTaskTypes::PROJECT;
		$this->task_start = $p_period_start; # Plot not-planned work first
		$this->period_start = $p_period_start;
		$this->period_end = $p_period_end;
	}

	protected function calculate_data_specific( $p_reference_date ) {
		global $g_resources;

		foreach ( $this->children as $child ) {
			# We're only interested in the hours done
			$this->done += $child->done;
		}

		# In order to calculate the estimated 'not plannable' work, use the
		# deployability of the resource.
		$t_deployability = $g_resources[$this->handler_id]['deployability'];
		$t_hours_per_week = $g_resources[$this->handler_id]['hours_per_week'];

		if ( $t_deployability === 0 || $t_hours_per_week === 0 ) {
			# This resource doesn't work. Issue a warning.
			trigger_error('Warning: user ' . user_get_name( $this->handler_id ) . ' does not have ' .
			'working hours per week or deployability set. Deadlines could not be estimated.');
			$this->est = $this->done;
			$this->overdue = $this->done;
		} else {
			# Estimated work for this resource is the total
			$this->est = $this->minutes_none_deployability( $this->period_start, $this->period_end,
				$t_deployability, $t_hours_per_week );
			$this->est = max( $this->est, $this->done );

			$t_na = 0;
			$this->calculate_actual_end_date( $this->task_start, $this->task_end, $this->est, $t_na );

			# Calculate overdue
			$t_est_till_today = $this->minutes_none_deployability( $this->period_start, min( time(), $this->period_end ),
				$t_deployability, $t_hours_per_week );
			$this->overdue = max( 0, $this->done - $t_est_till_today );

			# In case we still got time left, add a dummy bug to fill up the remaining space
			if ( $this->est - $this->done > 0 ) {
				$t_dummy = new PlottableBug( $this->handler_id, PLUGIN_PM_DUMMY_BUG, 0, null,
					end( array_values( $this->children ) ) );
				$t_dummy->work_data[PLUGIN_PM_EST][plugin_config_get( 'default_worktype' )] = $this->est - $this->done;
				$t_dummy->work_data[PLUGIN_PM_DONE][plugin_config_get( 'default_worktype' )] = 0;
				$t_dummy->calculate_data( $p_reference_date );
				$this->children[PLUGIN_PM_DUMMY_BUG] = $t_dummy;
			}
		}
	}

	private function minutes_none_deployability( $p_start_date, $p_end_date, $p_deployability, $p_hours_per_week ) {
		$t_days_between = ( $p_end_date - $p_start_date ) / ( 60 * 60 * 24 );
		$t_hours_per_day = $p_hours_per_week / 7;
		return $t_days_between * $t_hours_per_day * 60 * ( 100 - $p_deployability ) / 100;
	}
}