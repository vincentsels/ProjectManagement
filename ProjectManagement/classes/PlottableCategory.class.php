<?php

class PlottableCategory extends PlottableTask {
	public function __construct( $p_id, $p_name ) {
		parent::__construct();
		$this->type = PlottableTaskTypes::CATEGORY;
		$this->id = $p_id;
		$this->name = $p_name;
	}

	public function plot() {
		$t_start = format_short_date( $this->task_start );
		$t_finish = format_short_date( $this->task_end );
		echo '--' . $t_start . ' - ' . $t_finish . ': [Category] ' . $this->id . ' - ' . category_get_name( $this->id ) . '<br />';

		foreach ( $this->children as $child ) {
			$child->plot();
		}
	}
}