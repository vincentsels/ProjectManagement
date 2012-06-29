<?php

class PlottableProject extends PlottableTask {
	public function __construct( $p_id, $p_name ) {
		parent::__construct();
		$this->type = PlottableTaskTypes::PROJECT;
		$this->id = $p_id;
		$this->name = $p_name;
	}

	public function plot() {
		// TODO: Implement plot() method.
	}

	protected function calculate_data_specific( $p_reference_date ) {
		// TODO: Implement calculate_data_specific() method.
	}
}