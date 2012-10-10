<?php

class PlottableUser extends PlottableTask {
	public function __construct( $p_handler_id ) {
		parent::__construct( $p_handler_id );
		$this->type = PlottableTaskTypes::USER;
		$this->id = $p_handler_id;
	}

	public function plot_specific_start( $p_unique_id, $p_last_dev_day, $p_min_date, $p_max_date ) {
		global $g_resources;
		?>
	<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php
				print_expand_icon_start( $p_unique_id, plugin_config_get('show_projects_by_default') );
				echo user_get_realname( $this->id );
				print_expand_icon_end();
				$t_deployability_name = 'deployability_' . $this->id;
				$t_deployability_value = $g_resources[$this->id]['deployability'];
				$f_deployability = gpc_get_int( $t_deployability_name, -1 );
				$t_class_text = '';
				if ( $f_deployability != -1 && $f_deployability <> $t_deployability_value ) {
					$t_deployability_value = min( $f_deployability, 100 );
					$t_class_text = 'class="modified"';
				}
				echo ' &nbsp; <input ' . $t_class_text . ' type="text" size="2" maxlength="3" autocomplete="off" id="' . $t_deployability_name . '"
				name="' . $t_deployability_name . '" value="' . $t_deployability_value . '">%';
				$t_finished_text = plugin_lang_get( 'finished' ) . ': <span ';
				if ( $this->task_end > $p_last_dev_day ) {
					$t_finished_text .= 'class="overdue"';
				}
				$t_finished_text .= '>' . format_short_date( $p_max_date ) . '</span>';
				?>
				<span class="floatright"><?php echo $t_finished_text ?></span>
			</td>
		</tr>
		<?php
		echo '<tr><td colspan="2">';
		print_expandable_div_start( $p_unique_id, plugin_config_get('show_projects_by_default') );
		echo '<table class="width100" cellspacing="1">';
	}

	public function plot_specific_end( $p_unique_id ) {
		?>
		</table>
		<?php print_expandable_div_end(); ?>
		</td></tr></table><br />
		<?php
	}
}