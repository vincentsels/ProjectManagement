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
		$t_task_width = ( $this->task_end - $this->task_start ) / $t_total_width * 99;

		if ( $this->est > 0 ) {
			$t_original_work_width = ( $this->done - $this->overdue ) / $this->est * 100;
			$t_extra_work_width    = $this->overdue / $this->est * 100;
		} else {
			$t_original_work_width = 0;
			$t_extra_work_width    = 0;
		}

		$t_start = format_short_date( $this->task_start );
		$t_finish = format_short_date( $this->task_end );
		$t_text = '<a href="#" class="invisible" title="' . $t_start . ' - ' . $t_finish . '"></a>';
		?>
	<tr class="progress-row row-category2">
		<td width="15%">
			<?php echo category_get_name( $this->id ) ?>
		</td>
		<td width="85%">
			<div class="resource-section">
				<span class="filler" style="width: <?php echo $t_before ?>%"></span>
			<span class="progress" style="width: <?php echo $t_task_width ?>%">
				<span class="bar" style="width: <?php echo $t_original_work_width ?>%">
					<?php echo $t_text ?>
				</span><?php
				if ( $t_extra_work_width > 0 ) {
					echo '<span class="bar overdue" style="background-color:';
					print_overdue_color();
					echo ' width: ' . $t_extra_work_width . '%">' . $t_text . '</span>';
				}
				?>
			</span>
			</div>
		</td>
	</tr>
	<?php
	}
}