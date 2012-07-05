<?php

class PlottableCategory extends PlottableTask {
	public function __construct( $p_id, $p_name ) {
		parent::__construct();
		$this->type = PlottableTaskTypes::CATEGORY;
		$this->id = $p_id;
		$this->name = $p_name;
	}

	public function plot_specific_start( $p_unique_id, $p_min_date, $p_max_date ) {
		$t_total_width = $p_max_date - $p_min_date;
		$t_before = ( $this->task_start - $p_min_date ) / $t_total_width * 99;
		$t_width = ( $this->task_end - $this->task_start ) / $t_total_width * 99;
		$t_after = ( $p_max_date - $this->task_end ) / $t_total_width * 100;

		$t_start = format_short_date( $this->task_start );
		$t_finish = format_short_date( $this->task_end );
		$t_info = '<a href="#" class="invisible" title="' . $t_start . ' - ' . $t_finish . '">';
		?>
	<tr class="progress-row row-category2">
		<td width="15%">
			<?php echo category_get_name( $this->id ) ?>
		</td>
		<td width="85%">
			<div class="resource-section">
				<span class="filler" style="width:<?php echo $t_before ?>%"></span>
			<span class="progress" style="width:<?php echo $t_width ?>%">
				<?php echo $t_info ?>
				<span class="bar" style="width:<?php echo $t_after ?>%"></span>
			</span>
			</div>
		</td>
	</tr>
	<?php
	}
}