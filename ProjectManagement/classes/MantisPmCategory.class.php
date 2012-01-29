<?php

class MantisPmCategory {
	public $category_name;
	public $parent_project;
	public $unique_id;
	public $bugs;

	private $category_data;

	public function __construct( $p_category_name, $p_parent_project_name ) {
		$this->category_name = $p_category_name;
		$this->parent_project = $p_parent_project_name;
		$this->unique_id = $this->parent_project . '_' . $this->category_name;
		$this->bugs = array();
		$this->category_data = null;
	}

	public function calculate_category_data() {
		if ( !is_null( $this->category_data ) ) {
			# Only calculate once
			return;
		}

		foreach ( $this->bugs as $bug ) {
			$bug->add_bug_data( $this->category_data );
		}
	}

	public function add_category_data( &$p_data ) {
		$this->calculate_category_data();

		foreach ( $this->category_data as $t_handler_id => $t_data ) {
			foreach ( $t_data as $t_minutes_type => $t_value ) {
				@$p_data[$t_handler_id][$t_minutes_type] += $t_value;
			}
		}
	}

	public function print_category( $p_total_value = -1 ) {
		global $g_resource_colors;

		echo '<div class="category">';

		echo '<span class="progress-total-section">';

		echo '<span class="progress-text-section title-section">';
		print_expand_icon_start( $this->unique_id );
		echo $this->category_name;
		print_expand_icon_end();
		echo '</span>'; # End of text section

		echo '<span class="progress-bar-section">';

		$this->calculate_category_data();

		foreach ( sort_array_by_key( $this->category_data ) as $t_handler_id => $t_data ) {

			$t_est = @$t_data[PLUGIN_PM_EST];
			$t_done = @$t_data[PLUGIN_PM_DONE];
			$t_overdue = @$t_data[PLUGIN_PM_OVERDUE];

			# Calculate the width of the project
			$t_total = $t_est / $p_total_value * 100;

			if ( $t_est > 0 ) {
				$t_original_work = ( $t_done - $t_overdue ) / $t_est * 100;
				$t_total_work = $t_done / $t_est * 100;
				$t_extra_work = $t_overdue / $t_est * 100;
			} else {
				$t_original_work = 0;
				$t_total_work = 0;
				$t_extra_work = 0;
			}

			$t_progress_info = minutes_to_days( $t_done ) . '&nbsp;/&nbsp;' . minutes_to_days( $t_est );
			$t_progress_text = '<a href="#" class="invisible bold" title="' . $t_progress_info . '">' . number_format( $t_total_work, 1 ) . '%</a>';

			echo '<div class="resource-section">';
			echo '<span class="resource-name-section title-section">' . prepare_resource_name( $t_handler_id ) . '</span>';

			echo '<span class="resource-progress-section">';
			echo '<span class="progress" style="background-color:';
			print_background_color( $g_resource_colors[$t_handler_id], PLUGIN_PM_LIGHT );
			echo ' border-color: ';
			print_background_color( $g_resource_colors[$t_handler_id], PLUGIN_PM_DARK );
			echo ' width: ' . $t_total . '%">';
			echo '<span class="bar" style="background-color:';
			print_background_color( $g_resource_colors[$t_handler_id], PLUGIN_PM_DARK );
			echo ' width: ' . $t_original_work . '%">' . $t_progress_text . '</span>';
			if ( $t_extra_work > 0 ) {
				echo '<span class="bar overdue" style="background-color:';
				print_overdue_color();
				echo ' width: ' . $t_extra_work . '%"></span>';
			}
			echo '</span>';
			echo '</span>'; # End of resource progress section

			echo '</div>'; # End of resource section
		}

		echo '</span>'; # End of bar section

		echo '</span>'; # End of total section

		print_expandable_div_start( $this->unique_id );
		foreach ( $this->bugs as $bug ) {
			$bug->print_bug( $p_total_value );
		}
		print_expandable_div_end();

		echo '</div>';
	}
}