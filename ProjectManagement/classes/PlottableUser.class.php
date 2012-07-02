<?php

class PlottableUser extends PlottableTask {
	public function __construct( $p_id ) {
		parent::__construct();
		$this->type = PlottableTaskTypes::USER;
		$this->id = $p_id;
	}

	public function plot() {
		$t_start = format_short_date( $this->task_start );
		$t_finish = format_short_date( $this->task_end );
		echo '<br /><b>' . $t_start . ' - ' . $t_finish . ': [User] ' . $this->id . ' - ' . user_get_realname( $this->id ) . '</b><br />';

		foreach ( $this->children as $child ) {
			$child->plot();
		}
	}
}