<?php

class MantisProject {
	public $project_name;
	public $parent_project;
	public $sub_projects;
	public $categories;
	
	public function __construct( $p_projectname ) {
		$this->project_name = $p_projectname;
		$this->sub_projects = array();
		$this->categories = array();
	}
	
	public function est() {
		foreach ( $this->sub_projects as $subproject ) {
			$t_est += $subproject->est();
		}
		foreach ( $this->categories as $category ) {
			$t_est += $category->est();
		}
		return $t_est;
	}
	
	public function done() {
		foreach ( $this->sub_projects as $subproject ) {
			$t_done += $subproject->done();
		}
		foreach ( $this->categories as $category ) {
			$t_done += $category->done();
		}
		return $t_done;
	}
	
	public function todo() {
		foreach ( $this->sub_projects as $subproject ) {
			$t_todo += $subproject->todo();
		}
		foreach ( $this->categories as $category ) {
			$t_todo += $category->todo();
		}
		return $t_todo;
	}
	
	public function overdue() {
		foreach ( $this->sub_projects as $subproject ) {
			$t_overdue += $subproject->overdue();
		}
		foreach ( $this->categories as $category ) {
			$t_overdue += $category->overdue();
		}
		return $t_overdue;
	}
	
	public function print_project( $p_largest_value = -1 ) {
		echo '<div class="project">';
		
		print_expand_icon_start( $this->project_name );
		echo '<span class="project-title">Project: ', $this->project_name, '</span> - ',
			minutes_to_time( $this->done(), false ), ' / ', minutes_to_time( $this->est(), false ), 
			' (',  minutes_to_time( $this->overdue(), false ), ')';
		print_expand_icon_end();
		
		print_expandable_div_start( $this->project_name );
		foreach ( $this->categories as $category ) {
			$category->print_category( $p_largest_value );
		}
		
		foreach ( $this->sub_projects as $subproject ) {
			$subproject->print_project( $p_largest_value );
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
	
	public function __construct( $p_category_name, $p_parent_project_name ) {
		$this->category_name = $p_category_name;
		$this->parent_project = $p_parent_project_name;
		$this->unique_id = $this->parent_project . '_' . $this->category_name;
		$this->bugs = array();
	}
	
	public function est() {
		foreach ( $this->bugs as $bug ) {
			$t_est += $bug->est();
		}
		return $t_est;
	}
	
	public function done() {
		foreach ( $this->bugs as $bug ) {
			$t_done += $bug->done();
		}
		return $t_done;
	}
	
	public function todo() {
		foreach ( $this->bugs as $bug ) {
			$t_todo += $bug->todo();
		}
		return $t_todo;
	}
	
	public function overdue() {
		foreach ( $this->bugs as $bug ) {
			$t_overdue += $bug->overdue();
		}
		return $t_overdue;
	}
	
	public function print_category( $p_largest_value = -1 ) {
		echo '<div class="category">';
		
		print_expand_icon_start( $this->unique_id );
		echo '<span class="category-title">Category: ', $this->category_name, '</span> - ',
			minutes_to_time( $this->done(), false ), ' / ', minutes_to_time( $this->est(), false ), 
			' (',  minutes_to_time( $this->overdue(), false ), ')';
		print_expand_icon_end();
		
		print_expandable_div_start( $this->unique_id );
		foreach ( $this->bugs as $bug ) {
			$bug->print_bug( $p_largest_value );
		}
		print_expandable_div_end();
		
		echo '</div>';
	}
}

class MantisBug {
	public $bug_id = -1;
	public $handler_id = 1;
	public $est;
	public $done;
	public $todo;
	
	public function __construct( $p_bug_id ) {
		$this->bug_id = $p_bug_id;
		$this->est = array();
		$this->done = array();
		$this->todo = array();
	}
	
	public function est() {
		foreach ( $this->est as $t_value ) {
			$t_est += $t_value;
		}
		return $t_est;
	}
	
	public function done() {
		foreach ( $this->done as $t_value ) {
			$t_done += $t_value;
		}
		return $t_done;
	}

	public function todo() {
		$t_worktypes = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );
		foreach ( $t_worktypes as $t_work_type => $t_value ) {
			if ( !empty( $this->todo[$t_work_type] ) ) {
				$t_todo += $this->todo[$t_work_type];
			} else if ( !empty( $this->est[$t_work_type] ) ) {
				$t_todo += ( $this->est[$t_work_type] - $this->done[$t_work_type] );
			}
		}
		return max( $t_todo, 0 );
	}
	
	public function overdue() {
		$t_worktypes = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );
		foreach ( $t_worktypes as $t_work_type => $t_value ) {
			$t_overdue += $this->done[$t_work_type] + $this->todo[$t_work_type] - $this->est[$t_work_type];
		}
		return max( $t_overdue, 0 );
	}
	
	public function print_bug( $p_largest_value = -1 ) {
		echo '<div class="bug">';
		
		# Calculate the width of the bug
		$t_real_est = max( $this->est(), $this->done() + $this->todo() );
		$t_total = $t_real_est / $p_largest_value * 100;
		
		if ( $this->est() > 0 ) {
			$t_progress = round( $this->done() / $t_real_est * 100 );
		} else {
			$t_progress = 0;
		}
		
		echo 'Bug, id: ' , $this->bug_id , ' - ', 
			minutes_to_time( $this->done(), false ), ' / ', minutes_to_time( $this->est(), false ), 
			' (',  minutes_to_time( $this->overdue(), false ), ')  ';
		echo '<span class="progress" style="width:' . $t_total . '%">';
		echo '  <span class="bar" style="width:' . $t_progress . '%">' . $t_progress . '%</span>';
		echo '</span>';
		
		echo '</div>';
	}
}