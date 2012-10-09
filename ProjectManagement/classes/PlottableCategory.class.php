<?php

class PlottableCategory extends PlottableTask {
	public function __construct( $p_handler_id, $p_id, $p_name ) {
		parent::__construct( $p_handler_id );
		$this->type = PlottableTaskTypes::CATEGORY;
		$this->id = $p_id;
		$this->name = $p_name;
	}

	public function plot_specific_start( $p_unique_id, $p_last_dev_day, $p_min_date, $p_max_date ) {
		$t_total_width = $p_max_date - $p_min_date;
		$t_before = ( $this->task_start - $p_min_date ) / $t_total_width * 99;
		$t_task_width = ( $this->task_end - $this->task_start ) / $t_total_width * 99;

		if ( $this->est > 0 ) {
			$t_original_work_width = ( $this->done - $this->overdue ) / $this->est * 100;
			$t_total_work_width    = $this->done / ( $this->est - $this->na ) * 100;
			$t_na_with			   = $this->na / $this->est * 100;
			$t_extra_work_width    = $this->overdue / $this->est * 100;
		} else {
			$t_original_work_width = 0;
			$t_total_work_width    = 0;
			$t_na_with			   = 0;
			$t_extra_work_width    = 0;
		}

		$t_text =  number_format( $t_total_work_width, 1 ) . '%';
		?>
	<tr class="progress-row row-category2">
		<td width="15%">
			<?php echo category_get_name( $this->id ) ?>
		</td>
		<td width="85%">
			<div class="resource-section">
				<span class="filler" style="width: <?php echo $t_before ?>%"></span>
                <a href="#" class="invisible" title="<?php echo $this->generate_info_message() ?>">
				<?php print_progress_span( $this->handler_id, $t_task_width, $this->task_end > $p_last_dev_day )  ?>
				<?php print_progressbar_span( $this->handler_id, $t_original_work_width )  ?>
				<?php echo $t_text ?>
				</span><?php
				if ( $t_na_with > 0 ) {
					print_na_span( $t_na_with );
					echo '</span>';
				}
				if ( $t_extra_work_width > 0 ) {
					print_overdue_span( $t_extra_work_width );
					echo '</span>';
				}
				?>
				</span></a>
			</div>
		</td>
	</tr>
	<?php
	}
}