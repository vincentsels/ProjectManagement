<?php

class PlottableProject extends PlottableTask {
	public function __construct( $p_id, $p_name ) {
		parent::__construct();
		$this->type = PlottableTaskTypes::PROJECT;
		$this->id = $p_id;
		$this->name = $p_name;
	}

	public function plot() {
		$t_start = format_short_date( $this->task_start );
		$t_finish = format_short_date( $this->task_end );
		echo '<b>-' . $t_start . ' - ' . $t_finish . ': [Project] ' . $this->id . ' - ' . project_get_name( $this->id ) . '</b><br />';

		foreach ( $this->children as $child ) {
			$child->plot();
		}
	}
}