<?php

class PlottableBug extends PlottableTask {
	public $work_data;

	private $est;
	private $done;
	private $todo;
	private $overdue;

	private $previous_bug;
	private $handler;

	public function __construct( $p_id, $p_weight, $p_due_date, $p_previous_bug, $p_handler ) {
		parent::__construct();
		$this->work_data = array();
		$this->type = PlottableTaskTypes::BUG;
		$this->id = $p_id;
		$this->weight = $p_weight;
		$this->due_date = $p_due_date;
		$this->previous_bug = $p_previous_bug;
		$this->handler = $p_handler;
		$this->calculated = false;
	}

	protected function calculate_data_specific( $p_reference_date ) {

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
			$this->name = 'previous_bug is null';
		} else {
			$this->task_start = $this->previous_bug->task_end;
			$this->name = 'previous_bug id = ' . $this->previous_bug->id . ' and task_end is ' . $this->previous_bug->task_end;
		}

		# First retrieve the amount of hours this resource works per day
		# Assumes 5 days a week. Could be enhanced to be configurable per user, project,...
		$t_workdays_per_week = 7;
		$t_hours_per_day = ProjectManagementCache::$resource_cache[$this->handler->id] / $t_workdays_per_week;
		if ( $t_hours_per_day > 0 ) {
			$t_seconds_for_bug = $this->est / $t_hours_per_day * 24 * 60;
			# Todo: include logic for non-working days
			$this->task_end = $this->task_start + $t_seconds_for_bug;
		}
	}

	public function plot_specific_start( $p_unique_id, $p_min_date, $p_max_date ) {
		$t_total_width = $p_max_date - $p_min_date;
		$t_before = ( $this->task_start - $p_min_date ) / $t_total_width * 99;
		$t_width = ( $this->task_end - $this->task_start ) / $t_total_width * 99;
		$t_after = ( $p_max_date - $this->task_end ) / $t_total_width * 100;

		$t_start = format_short_date( $this->task_start );
		$t_finish = format_short_date( $this->task_end );
		$t_info = '<a href="#" class="invisible" title="' . $t_start . ' - ' . $t_finish . '">';
		?>
	<tr class="progress-row row-2">
		<td width="15%">
			<?php echo $this->id ?>
		</td>
		<td width="85%">
			<div class="resource-section">
			<span class="filler" style="width:<?php echo $t_before ?>%"></span>
			<span class="progress" style="width:<?php echo $t_width ?>%">
				<?php echo $t_info ?>
				<span class="bar" style="width:<?php echo $t_after ?>%"></span>
			</span>
			</div>
		</td>
	</tr>
	<?php
	}
}