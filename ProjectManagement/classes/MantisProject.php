<?php

class MantisProject {
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
			echo '<span class="progress" style="width:' . $t_total . '%">';
			echo '  <span class="bar" style="width:' . $t_progress . '%">' . $t_progress_text . '</span>';
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

class MantisCategory {
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
				$p_data[$t_handler_id][$t_minutes_type] += $t_value;
			}
		}
	}
	
	public function print_category( $p_total_value = -1 ) {
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
			echo '<span class="progress" style="width:' . $t_total . '%">';
			echo '  <span class="bar" style="width:' . $t_progress . '%">' . $t_progress_text . '</span>';
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

class MantisBug {
	public $bug_id;
	public $handler_id;
	public $est;
	public $done;
	public $todo;
	
	private $bug_data;
	
	public function __construct( $p_bug_id ) {
		$this->bug_id = $p_bug_id;
		$this->est = array();
		$this->done = array();
		$this->todo = array();
		$this->bug_data = null;
	}
	
	public function calculate_bug_data( ) {
		if ( !is_null( $this->bug_data ) ) {
			# Only calculate once
			return;
		}
		
		$t_worktypes = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );
		
		foreach ( $this->est as $t_value ) {
			$this->bug_data[PLUGIN_PM_EST] += $t_value;
		}
		
		foreach ( $this->done as $t_value ) {
			$this->bug_data[PLUGIN_PM_DONE] += $t_value;
		}
		
		foreach ( $t_worktypes as $t_work_type => $t_value ) {
			if ( !empty( $this->todo[$t_work_type] ) ) {
				$t_todo += $this->todo[$t_work_type];
			} else if ( !empty( $this->est[$t_work_type] ) ) {
				$t_todo += ( $this->est[$t_work_type] - $this->done[$t_work_type] );
			}
		}
		$this->bug_data[PLUGIN_PM_TODO] = max( $t_todo, 0 );
		
		foreach ( $t_worktypes as $t_work_type => $t_value ) {
			$t_overdue += $this->done[$t_work_type] + $this->todo[$t_work_type] - $this->est[$t_work_type];
		}
		$this->bug_data[PLUGIN_PM_OVERDUE] = max( $t_overdue, 0 );
	}
	
	public function add_bug_data( &$p_data ) {
		$this->calculate_bug_data();
		
		foreach ( $this->bug_data as $t_minutse_type => $t_value ) {
			$p_data[$this->handler_id][$t_minutse_type] += $t_value;
		}
	}
		
	public function print_bug( $p_total_value = -1 ) {
		echo '<div class="bug">';
		$t_alternate_class = helper_alternate_class();
		
		echo '<span class="progress-total-section">';
		
		echo '<span ' . $t_alternate_class . '>';
		echo '<span class="progress-text-section">', string_get_bug_view_link( $this->bug_id, null, false ), '</span>';
		echo '</span>';
		
		$this->calculate_bug_data();
		
		$t_est = $this->bug_data[PLUGIN_PM_EST];
		$t_done = $this->bug_data[PLUGIN_PM_DONE];
		$t_todo = $this->bug_data[PLUGIN_PM_TODO];
		$t_overdue = $this->bug_data[PLUGIN_PM_OVERDUE];
		
		# Calculate the width of the bug
		$t_real_est = max( $t_est, $t_done + $t_todo );
		$t_total = $t_real_est / $p_total_value * 100;
		
		if ( $t_real_est > 0 ) {
			$t_progress = round( $t_done / $t_real_est * 100 );
		} else {
			$t_progress = 0;
		}
		
		$t_progress_info = minutes_to_time( $t_done, false ) . '&nbsp;/&nbsp;' . minutes_to_time( $t_real_est, false );
		$t_progress_text = '<a href="#" class="invisible" title="' . $t_progress_info . '">' . number_format( $t_progress, 1 ) . '%</a>';
		
		echo '<span class="progress-bar-section">';
		
		echo '<div class="resource-section">';
		echo '<span ' . $t_alternate_class . '>';
		echo '<span class="resource-name-section">' . prepare_resource_name( $this->handler_id ) . '</span>';
		echo '</span>';
		
		echo '<span class="resource-progress-section">';
		echo '<span class="progress" style="width:' . $t_total . '%">';
		echo '  <span class="bar" style="width:' . $t_progress . '%">' . $t_progress_text . '</span>';
		echo '</span>';
		echo '</span>'; # End of resource progress section
			
		echo '</div>'; # End of resource section
			
		echo '</span>'; # End of bar section
		
		echo '</span>'; # End of total section
		
		echo '</div>';
	}
}