<?php

class PlottableTaskTypes {
	const USER = 'U';
	const PROJECT = 'P';
	const CATEGORY = 'C';
	const BUG = 'B';
}

abstract class PlottableTask {
	protected $type;
	protected $id;
	protected $name;
	protected $weight;
	protected $due_date;

	public $children;

	protected $est;
	protected $done;
	protected $na;
	protected $overdue;
	protected $task_start;
	protected $task_end;
	protected $handler_id;

	private $calculated = false;

	public function __construct( $p_handler_id ) {
		$this->children = array();
		$this->est = 0;
		$this->done = 0;
		$this->overdue = 0;
		$this->handler_id = $p_handler_id;
	}

	public function plot( $p_min_date = null, $p_max_date = null ) {
		if ( is_null( $p_min_date ) ) {
			$p_min_date = $this->task_start;
		}
		if ( is_null( $p_max_date ) ) {
			$p_max_date = $this->task_end;
		}

		if ( $p_max_date - $p_min_date == 0 ) {
			return; # Prevent division by zero, don't plot this task
		}

		$t_unique_id = uniqid($this->type . '' . $this->id);

		$this->plot_specific_start( $t_unique_id, $p_min_date, $p_max_date );
		foreach ( $this->children as $child ) {
			$child->plot( $p_min_date, $p_max_date );
		}
		$this->plot_specific_end( $t_unique_id );
	}

	protected abstract function plot_specific_start( $p_unique_id, $p_min_date, $p_max_date );
	protected function plot_specific_end( $p_unique_id ) {
		# Standard behaviour does nothing
	}

	public function calculate_data( $p_reference_date ) {
		foreach ( $this->children as $child ) {
			$child->calculate_data( $p_reference_date );
		}
		if ( $this->calculated ) {
			return;
		}
		$this->calculate_data_specific( $p_reference_date );
		$this->calculated = true;
	}

	protected function calculate_data_specific( $p_reference_date ) {
		# Find the minimum start date and maximum end date of all children
		$t_min_start_date = 99999999999;
		$t_max_end_date = 1;
		foreach ( $this->children as $child ) {
			if ( $child->task_start < $t_min_start_date ) {
				$t_min_start_date = $child->task_start;
			}
			if ( $child->task_end > $t_max_end_date ) {
				$t_max_end_date = $child->task_end;
			}

			# The data of each task is the sum of the data of all its children
			$this->est += $child->est;
			$this->done += $child->done;
			$this->na += $child->na;
			$this->overdue += $child->overdue;
		}
		$this->task_start = $t_min_start_date;
		$this->task_end = $t_max_end_date;
	}

	public function remove_empty_children() {
		$t_has_children = false;
		foreach ( $this->children as $child ) {
			if ( count( $child->children ) > 0 ) {
				$t_has_children = true;
			}
		}
		if ( !$t_has_children ) {
			$this->children = array();
		}
	}

	protected function calculate_actual_end_date( &$p_task_start, &$p_task_end, &$p_est, &$p_na) {
		$p_task_end = $this->calculate_end_date( $p_task_start, $p_est );
		$p_total_na = 0;
		$p_new_na = $this->check_non_working_period( $p_task_start, $p_task_end );
		while ( $p_total_na != $p_new_na ) {
			$p_total_na = $p_new_na;
			$t_new_est = $p_est + $p_total_na;
			$p_task_end = $this->calculate_end_date( $p_task_start, $t_new_est );
			$p_new_na = $this->check_non_working_period( $p_task_start, $p_task_end );
		}
		$p_na = $p_total_na;
		$p_est += $p_total_na;
	}

	private function calculate_end_date( $p_task_start, $p_est ) {
		global $g_resources;

		$t_workdays_per_week = 7;
		$t_hours_per_day = $g_resources[$this->handler_id]['hours_per_week'] / $t_workdays_per_week;
		if ( $t_hours_per_day > 0 ) {
			$t_seconds_for_bug = $p_est / $t_hours_per_day * 24 * 60;
			return $p_task_start + $t_seconds_for_bug;
		}
		return $p_task_start;
	}

	private function check_non_working_period( $p_task_start, $p_task_end ) {
		global $g_resources;

		$t_seconds_na = 0;
		# Iterate through all the non-working days of the user
		foreach ( $g_resources[$this->handler_id]['resource_unavailable'] as $t_na_period ) {
			if ( $t_na_period['start_date'] <= $p_task_end &&
				$t_na_period['start_date'] > $p_task_start ) {
				$t_seconds_na = ($t_na_period['end_date'] - $t_na_period['start_date']);
			}
		}

		# Convert na period's seconds to 'relative' seconds
		$t_ratio = $g_resources[$this->handler_id]['hours_per_week'] / ( 7 * 24 );
		$t_seconds_na *= $t_ratio;

		return $t_seconds_na / 60;
	}
}