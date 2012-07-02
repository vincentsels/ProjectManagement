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
			<td class="form-title" colspan="2"> <?php echo user_get_realname( $this->id ) ?></td>
		</tr>
		<?php
	}

	public function plot_specific_end( $p_unique_id ) {
		?>
	</table><br />
		<?php
	}
}