<?php

class PlottableUser extends PlottableTask {
	public function __construct( $p_id ) {
		parent::__construct();
		$this->type = PlottableTaskTypes::USER;
		$this->id = $p_id;
	}

	public function plot() {
		// TODO: Implement plot() method.
	}

	protected function calculate_data_specific( $p_reference_date ) {
		// TODO: Implement calculate_data_specific() method.
	}
}