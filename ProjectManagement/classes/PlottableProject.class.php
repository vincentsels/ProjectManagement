<?php

class PlottableProject extends PlottableTask {
	public function __construct( $p_id, $p_name ) {
		parent::__construct();
		$this->type = PlottableTaskTypes::PROJECT;
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
				<span class="filler" style="width:<?php echo $t_before ?>%"></span>
			<span class="progress" style="width:<?php echo $t_width ?>%">
				<?php echo $t_info ?>
				<span class="bar" style="width:<?php echo $t_after ?>%"></span>
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