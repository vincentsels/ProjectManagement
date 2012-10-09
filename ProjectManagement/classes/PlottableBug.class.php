<?php

class PlottableBug extends PlottableTask {
	private $work_data;

	private $todo;
	private $ref_date;
	private $todo_on_ref_date;
	private $previous_bug;

	public function __construct( $p_reference_date, $p_handler_id, $p_id, $p_weight, $p_due_date, $p_previous_bug ) {
		parent::__construct( $p_handler_id );
		$this->work_data = array();
		$this->type = PlottableTaskTypes::BUG;
		$this->ref_date = $p_reference_date;
		$this->id = $p_id;
		$this->weight = $p_weight;
		$this->due_date = $p_due_date;
		$this->previous_bug = $p_previous_bug;
		$this->calculated = false;
	}

	/**
	 * Sets the specified type of work and minutes.
	 * @param $p_minutes_type int The type of minutes; either PLUGIN_PM_DONE, PLUGIN_PM_EST or PLUGIN_PM_TODO
	 * @param $p_work_type int The type of work
	 * @param $p_minutes int The amount of minutes
	 * @param null $p_book_date timestamp For minutes DONE, specify the book date
	 */
	public function set_work_data( $p_minutes_type, $p_work_type, $p_minutes, $p_book_date = null ) {
		if ( $p_minutes_type == PLUGIN_PM_DONE && $p_book_date < $this->ref_date ) {
			$p_minutes_type = PLUGIN_PM_DONE_BEFORE_REFDATE;
		}
		$this->work_data[$p_minutes_type][$p_work_type] = $p_minutes;
	}

	protected function calculate_data_specific( $p_reference_date ) {
		global $g_resources;

		# Step 1: Calculate the est, done, work and overdue

		$t_worktypes = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );

		$this->todo = 0;
		foreach ( $t_worktypes as $t_work_type => $t_value ) {
			if ( isset( $this->work_data[PLUGIN_PM_TODO][$t_work_type] ) ) {
				$this->todo += $this->work_data[PLUGIN_PM_TODO][$t_work_type];
			} else if ( isset( $this->work_data[PLUGIN_PM_EST][$t_work_type] ) ) {
				$this->todo += max( $this->work_data[PLUGIN_PM_EST][$t_work_type] -
					@$this->work_data[PLUGIN_PM_DONE][$t_work_type] -
					@$this->work_data[PLUGIN_PM_DONE_BEFORE_REFDATE][$t_work_type], 0 );
			}
		}
		$this->todo = max( $this->todo, 0 );
		$this->todo_on_ref_date = $this->todo;

		if ( isset( $this->work_data[PLUGIN_PM_DONE] ) ) {
			foreach ( $this->work_data[PLUGIN_PM_DONE] as $t_value ) {
				@$this->done += $t_value;
			}
		}
		$t_done_on_refdate = 0;
		if ( isset( $this->work_data[PLUGIN_PM_DONE_BEFORE_REFDATE] ) ) {
			foreach ( $this->work_data[PLUGIN_PM_DONE_BEFORE_REFDATE] as $t_value ) {
				@$this->done += $t_value;
				$t_done_on_refdate += $t_value;
			}
		}

		# Calculate the 'real estimate'
		$this->est = @$this->done + @$this->todo;
		$this->todo_on_ref_date = max( $this->est - $t_done_on_refdate, 0 );

		$this->overdue = 0;
		foreach ( $t_worktypes as $t_work_type => $t_value ) {
			$this->overdue += @$this->work_data[PLUGIN_PM_DONE][$t_work_type] +
				@$this->work_data[PLUGIN_PM_TODO][$t_work_type] -
					@$this->work_data[PLUGIN_PM_EST][$t_work_type];
		}
		$this->overdue = max( $this->overdue, 0 );

		# Step 2: Next, calculate the start and finish dates

		if ( is_null( $this->previous_bug ) || $this->previous_bug === false ) {
			$this->task_start = $p_reference_date;
		} else {
			$this->task_start = $this->previous_bug->task_end;
		}

		$this->calculate_actual_end_date( $this->task_start, $this->task_end,
			$this->todo_on_ref_date, $this->est, $this->na );
	}

	public function plot_specific_start( $p_unique_id, $p_last_dev_day, $p_min_date, $p_max_date ) {
		$t_total_width = $p_max_date - $p_min_date;
		$t_before = ( $this->task_start - $p_min_date ) / $t_total_width * 99;
		$t_task_width = ( $this->task_end - $this->task_start ) / $t_total_width * 99;

		if ( $this->id == PLUGIN_PM_DUMMY_BUG ) {
			if ( $this->na > 0 ) {
				$t_na_with = $this->na / $this->est * $t_task_width;
				$t_na_text = '<a href="#" class="invisible" title="' . minutes_to_days( $this->na ) . ' ' . lang_get( 'days' ) . '"></a>';
				?>
				<tr class="progress-row row-2">
					<td width="15%"><?php echo plugin_lang_get( 'unavailable' ) ?>
					</td>
					<td width="85%">
						<div class="resource-section">
							<span class="filler" style="width: <?php echo $t_before ?>%"></span>
							<?php print_progress_span( $this->handler_id, $t_na_with, $this->task_end > $p_last_dev_day )  ?>
							<?php print_progressbar_span( $this->handler_id, 0 )  ?>
							</span><?php
								print_na_span( 100 );
								echo $t_na_text . '</span>';
							?>
							</span>
						</div>
					</td>
				</tr>
				<?php
			}
			return; # Don't show the dummy bug!
		}

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

		$t_progress_text = number_format( $t_total_work_width, 1 );
		$t_description = '<span class="description-info">: ' . bug_get_field( $this->id, 'summary' ) . '</span>';

		?>
	<tr class="progress-row row-2">
		<td width="15%">
			<?php print_bug_link( $this->id ) ?>
			<?php echo $t_description ?>
		</td>
		<td width="85%">
			<div class="resource-section">
			<span class="filler" style="width: <?php echo $t_before ?>%"></span>
            <a href="#" class="invisible" title="<?php echo $this->generate_info_message() ?>">
			<?php print_progress_span( $this->handler_id, $t_task_width, $this->task_end > $p_last_dev_day )  ?>
				<?php print_progressbar_span( $this->handler_id, $t_original_work_width )  ?>
					<?php echo $t_progress_text ?>
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