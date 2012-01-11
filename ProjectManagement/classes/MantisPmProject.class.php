<?php

class MantisPmProject {
	public $project_name;
	public $parent_project;
	public $sub_projects;
	public $categories;

	private $project_data;

	public function __construct( $p_projectname ) {
		$this->project_name = $p_projectname;
		$this->sub_projects = array();
		$this->categories = array();
		$this->project_data = null;
	}

	public function calculate_project_data() {
		if ( !is_null( $this->project_data ) ) {
			# Only calculate once
			return;
		}

		foreach ( $this->sub_projects as $t_subproject ) {
			$t_subproject->add_project_data( $this->project_data );
		}
		foreach ( $this->categories as $t_category ) {
			$t_category->add_category_data( $this->project_data );
		}
	}

	public function add_project_data( &$p_data ) {
		$this->calculate_project_data();

		foreach ( $this->project_data as $t_handler_id => $t_data ) {
			foreach ( $t_data as $t_minutes_type => $t_value ) {
				$p_data[$t_handler_id][$t_minutes_type] += $t_value;
			}
		}
	}

	public function add_data( &$p_data ) {
		foreach ( $this->sub_projects as $subproject ) {
			$subproject->add_data( $p_data );
		}
		foreach ( $this->categories as $category ) {
			$category->add_data( $p_data );
		}
	}

	public function get_max_real_est() {
		$this->calculate_project_data();

		$t_max_val = 0;
		foreach ( $this->project_data as $t_handler_id => $t_data ) {
			$t_real_est = max( $t_data[PLUGIN_PM_EST], $t_data[PLUGIN_PM_DONE] + $t_data[PLUGIN_PM_TODO] );
			if ( $t_real_est > $t_max_val ) {
				$t_max_val = $t_real_est;
			}
		}

		return $t_max_val;
	}

	public function print_project( $p_total_value = -1 ) {
		global $g_resource_colors;

		echo '<div class="project">';

		echo '<span class="progress-total-section">';

		echo '<span class="progress-text-section title-section">';
		print_expand_icon_start( $this->project_name );
		echo $this->project_name;
		print_expand_icon_end();
		echo '</span>'; # End of text section

		echo '<span class="progress-bar-section">';

		$this->calculate_project_data();

		foreach ( sort_array_by_key( $this->project_data ) as $t_handler_id => $t_data ) {

			$t_est = $t_data[PLUGIN_PM_EST];
			$t_done = $t_data[PLUGIN_PM_DONE];
			$t_todo = $t_data[PLUGIN_PM_TODO];
			$t_overdue = $t_data[PLUGIN_PM_OVERDUE];

			# Calculate the width of the project
			$t_real_est = max( $t_est, $t_done + $t_todo );
			$t_total = $t_real_est / $p_total_value * 100;

			if ( $t_real_est > 0 ) {
				$t_progress = $t_done / $t_real_est * 100;
			} else {
				$t_progress = 0;
			}

			$t_progress_info = minutes_to_days( $t_done ) . '&nbsp;/&nbsp;' . minutes_to_days( $t_real_est );
			$t_progress_text = '<a href="#" class="invisible bold" title="' . $t_progress_info . '">' . number_format( $t_progress, 1 ) . '%</a>';

			echo '<div class="resource-section">';
			echo '<span class="resource-name-section title-section">' . prepare_resource_name( $t_handler_id ) . '</span>';

			echo '<span class="resource-progress-section">';
			echo '<span class="progress" ';
			print_background_color( $g_resource_colors[$t_handler_id], PLUGIN_PM_LIGHT, 'width: ' . $t_total . '%' );
			echo '>';
			echo '<span class="bar" ';
			print_background_color( $g_resource_colors[$t_handler_id], PLUGIN_PM_DARK, 'width: ' . $t_progress . '%' );
			echo '>' . $t_progress_text . '</span>';
			echo '</span>';
			echo '</span>'; # End of resource progress section

			echo '</div>'; # End of resource section
		}

		echo '</span>'; # End of bar section

		echo '</span>'; # End of total section

		print_expandable_div_start( $this->project_name );
		foreach ( $this->categories as $category ) {
			$category->print_category( $p_total_value );
		}

		foreach ( $this->sub_projects as $subproject ) {
			$subproject->print_project( $p_total_value );
		}
		print_expandable_div_end();

		echo '</div>';
	}
}