<?php

class PlottableCategory extends PlottableTask {
	public function __construct( $p_id, $p_name ) {
		parent::__construct();
		$this->type = PlottableTaskTypes::CATEGORY;
		$this->id = $p_id;
		$this->name = $p_name;
	}

	public function plot_specific_start( $p_unique_id, $p_min_date, $p_max_date ) {
		$t_start = format_short_date( $this->task_start );
		$t_finish = format_short_date( $this->task_end );
		?>
	<tr class="row-category2"><td><?php echo category_get_name( $this->id ) ?></td><td><?php echo $t_start . ' - ' . $t_finish ?></td></tr>
	<?php
	}
}