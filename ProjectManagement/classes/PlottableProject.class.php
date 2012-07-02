<?php

class PlottableProject extends PlottableTask {
	public function __construct( $p_id, $p_name ) {
		parent::__construct();
		$this->type = PlottableTaskTypes::PROJECT;
		$this->id = $p_id;
		$this->name = $p_name;
	}

	public function plot_specific( $p_min_date, $p_max_date ) {
		$t_start = format_short_date( $this->task_start );
		$t_finish = format_short_date( $this->task_end );
		if ( $this->id == PLUGIN_PM_PROJ_ID_UNPLANNED || $this->id == PLUGIN_PM_PROJ_ID_NONWORKING ) {
			$t_project_name = $this->name;
		} else {
			$t_project_name = project_get_name( $this->id );
		}
		echo '<b>-' . $t_start . ' - ' . $t_finish . ': [Project] ' . $this->id . ' - ' . $t_project_name . '</b><br />';
	}
}