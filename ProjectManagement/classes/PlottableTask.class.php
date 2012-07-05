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
	protected $overdue;
	protected $task_start;
	protected $task_end;

	private $calculated = false;

	public function __construct( ) {
		$this->children = array();
		$this->est = 0;
		$this->done = 0;
		$this->overdue = 0;
	}

	public function plot( $p_min_date = null, $p_max_date = null ) {
		if ( is_null( $p_min_date ) ) {
			$p_min_date = $this->task_start;
		}
		if ( is_null( $p_max_date ) ) {
			$p_max_date = $this->task_end;
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
			$this->overdue += $child->overdue;
		}
		$this->task_start = $t_min_start_date;
		$this->task_end = $t_max_end_date;
	}

	public function remove_empty_children() {
		$t_has_children = false;
		foreach ( $this->children as $child ) {
			$child->remove_empty_children();
			if ( count( $child->children ) > 0 ) {
				$t_has_children = true;
			}
		}
		if ( !$t_has_children ) {
			$this->children = array();
		}
	}
}