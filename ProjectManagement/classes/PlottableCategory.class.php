<?php

class PlottableCategory extends PlottableTask {
	public function __construct( $p_id, $p_name ) {
		parent::__construct();
		$this->type = PlottableTaskTypes::CATEGORY;
		$this->id = $p_id;
		$this->name = $p_name;
	}
}