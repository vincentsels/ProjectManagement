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
	protected $target_date;

	public $children;

	public $task_start;
	public $task_end;

	public function __construct( ) {
		$this->children = array();
	}

	public abstract function plot();

	public function calculate_data( $p_reference_date ) {
		foreach ( $this->children as $child ) {
			$child->calculate_data( $p_reference_date );
			$child->calculate_data_specific( $p_reference_date );
		}
	}

	protected function calculate_data_specific( $p_reference_date ) {
		# TODO: implement default calculation
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