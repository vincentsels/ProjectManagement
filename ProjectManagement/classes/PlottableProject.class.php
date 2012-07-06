<?php

class PlottableProject extends PlottableTask {
	public function __construct( $p_handler_id, $p_id, $p_name ) {
		parent::__construct( $p_handler_id );
		$this->type = PlottableTaskTypes::PROJECT;
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

		if ( $this->id == PLUGIN_PM_PROJ_ID_UNPLANNED || $this->id == PLUGIN_PM_PROJ_ID_NONWORKING ) {
			$t_project_name = $this->name;
		} else {
			$t_project_name = project_get_name( $this->id );
		}
		?>
	<tr class="progress-row row-category">
		<td width="15%" style="text-align:left;">
			<?php
			print_expand_icon_start( $p_unique_id );
			echo $t_project_name;
			print_expand_icon_end();
			?>
		</td>
		<td width="85%" style="text-align:left;">
			<div class="resource-section">
				<span class="filler" style="width: <?php echo $t_before ?>%"></span>
				<?php print_progress_span( $this->handler_id, $t_task_width )  ?>
					<?php print_progressbar_span( $this->handler_id, $t_original_work_width )  ?>
						<?php echo $t_text ?>
					</span><?php
					if ( $t_extra_work_width > 0 ) {
						print_overdue_span( $t_extra_work_width );
						echo $t_text . '</span>';
					}
					?>
				</span>
			</div>
		</td>
	</tr>
	<?php
		echo '<tr><td colspan="2">';
		print_expandable_div_start( $p_unique_id );
		echo '<table class="width100" cellspacing="1">';
	}

	protected function plot_specific_end( $p_unique_id ) {
		echo '</table>';
		print_expandable_div_end();
		echo '</td></tr>';
	}
}