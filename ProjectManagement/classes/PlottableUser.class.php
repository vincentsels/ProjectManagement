<?php

class PlottableUser extends PlottableTask {
	public function __construct( $p_id ) {
		parent::__construct();
		$this->type = PlottableTaskTypes::USER;
		$this->id = $p_id;
	}

	public function plot_specific_start( $p_unique_id, $p_min_date, $p_max_date ) {
		?>
	<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php
				print_expand_icon_start( $p_unique_id );
				echo user_get_realname( $this->id );
				print_expand_icon_end();
				?>
			</td>
		</tr>
		<?php
		echo '<tr><td colspan="2">';
		print_expandable_div_start( $p_unique_id );
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