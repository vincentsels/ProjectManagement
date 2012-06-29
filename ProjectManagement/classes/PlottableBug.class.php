<?php

class PlottableBug extends PlottableTask {
	public $work_data;

	private $est;
	private $done;
	private $todo;
	private $overdue;

	private $previous_bug;
	private $handler;

	public function __construct( $p_id, $p_weight, $p_target_date, $p_previous_bug, $p_handler ) {
		parent::__construct();
		$this->work_data = array();
		$this->type = PlottableTaskTypes::BUG;
		$this->id = $p_id;
		$this->weight = $p_weight;
		$this->target_date = $p_target_date;
		$this->previous_bug = $p_previous_bug;
		$this->handler = $p_handler;
	}

	public function plot() {
		// TODO: Implement plot() method.
	}

	protected function calculate_data_specific( $p_reference_date ) {

		# Step 1: Calculate the est, done, work and overdue

		$t_worktypes = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );

		foreach ( $this->work_data[PLUGIN_PM_DONE] as $t_value ) {
			@$this->done += $t_value;
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

		# First retrieve the amount of hours this resource works per day
		# Assumes 5 days a week. Could be enhanced to be configurable per user, project,...
		$t_hours_per_day = ProjectManagementCache::$resource_cache[$this->handler->id] / 5;
		$t_seconds_for_bug = $this->est / $t_hours_per_day * 24 * 60 * 60;
		# Todo: include logic for non-working days
		$this->task_end = $this->task_start + $t_seconds_for_bug;
	}
}