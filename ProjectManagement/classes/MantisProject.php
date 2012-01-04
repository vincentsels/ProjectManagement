<?php

class MantisProject {
	public $project_name;
	public $subprojects = array();
	public $categories = array();
	
	public function __construct( $p_projectname ) {
		$this->project_name = $p_projectname;
	}
	
	public function est() {
		foreach ( $this->subprojects as $subproject ) {
			$t_est += $subproject->est();
		}
		foreach ( $this->categories as $category ) {
			$t_est += $category->est();
		}
		return $t_est;
	}
	
	public function print_project() {
		echo '<div class="project">';
		
		print_expand_icon_start( $this->project_name );
		echo '<span class="project-title">Project: ' . $this->project_name . '</span> - completed: ' . $this->est();
		print_expand_icon_end();
		
		print_expandable_div_start( $this->project_name );
		foreach ( $this->categories as $category ) {
			$category->print_category();
		}
		
		foreach ( $this->subprojects as $subproject ) {
			$subproject->print_project();
		}
		print_expandable_div_end();
		
		echo '</div>';
	}
}

class MantisCategory {
	public $category_name;
	public $parent_project;
	public $unique_id;
	public $bugs = array();
	
	public function __construct( $p_category_name, $p_parent_project_name ) {
		$this->category_name = $p_category_name;
		$this->parent_project = $p_parent_project_name;
		$this->unique_id = $this->parent_project . '_' . $this->category_name;
	}
	
	public function est() {
		foreach ( $this->bugs as $bug ) {
			$t_est += $bug->est;
		}
		return $t_est;
	}
	
	public function print_category() {
		echo '<div class="category">';
		
		print_expand_icon_start( $this->unique_id );
		echo '<span class="category-title">Category: ' . $this->category_name . '</span> - completed: ' . $this->est();
		print_expand_icon_end();
		
		print_expandable_div_start( $this->unique_id );
		foreach ( $this->bugs as $bug ) {
			$bug->print_bug();
		}
		print_expandable_div_end();
		
		echo '</div>';
	}
}

class MantisBug {
	public $bug_id = -1;
	public $handler_id = 1;
	public $est = 0;
	public $done = 0;
	public $todo = 0;
	
	public function __construct( $p_bug_id, $p_est = 1 ) {
		$this->bug_id = $p_bug_id;
		$this->est = $p_bug_id;
	}
	
	public function print_bug() {
		echo '<div class="bug">';
		
		echo 'Bug, id: ' . $this->bug_id . ' - progress: ' . $this->est;
		
		echo '</div>';
	}
}