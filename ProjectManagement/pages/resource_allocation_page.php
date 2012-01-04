<?php

access_ensure_global_level( plugin_config_get( 'view_resource_allocation_threshold' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_pm_reports_menu( 'resource_allocation_page' );

$t_target_version = '5.0.0';
$t_query = "SELECT pp.name as parent_project, pc.name as project_name, c.name as category_name, b.id, b.handler_id, w.work_type, w.minutes_type, sum(w.minutes) as minutes
  FROM mantis_bug_table b
  JOIN mantis_project_table pc ON b.project_id = pc.id
  JOIN mantis_category_table c ON b.category_id = c.id
  LEFT OUTER JOIN mantis_project_hierarchy_table h ON pc.id = h.child_id
  LEFT OUTER JOIN mantis_project_table pp ON h.parent_id = pp.id
  LEFT OUTER JOIN mantis_plugin_projectmanagement_work_table w ON b.id = w.bug_id " .
# WHERE b.target_version = '$t_target_version'
 "GROUP BY pp.name, pc.name, c.name, b.id, b.handler_id, w.work_type, w.minutes_type";
$t_result = db_query_bound( $t_query );
$t_rownum = db_num_rows( $t_result );

$t_all_projects = array(); # Array containing all projects

# Start creation of the objects
for ($i = 0; $i < $t_rownum; $i++) {
	$row = db_fetch_array( $t_result );
	
	# Check whether this project already exists and if not, create it
	if ( array_key_exists( $row['project_name'], $t_all_projects ) ) {
		$t_project = $t_all_projects[$row['project_name']];
	} else {
		$t_project = new MantisProject( $row['project_name'] );
		$t_all_projects[$row['project_name']] = $t_project;
	}
	
	# Check whether this category already exists in this project and if not, create it
	if ( !is_null( $t_project->categories ) && array_key_exists( $row['category_name'], $t_project->categories ) ) {
		$t_category = $t_project->categories[$row['category_name']];
	} else {
		$t_category = new MantisCategory( $row['category_name'], $row['project_name'] );
		$t_project->categories[$row['category_name']] = $t_category;
	}
	
	# Check whether this ticket already exists in this category and if not, add it
	if ( !is_null( $t_category->bugs ) && array_key_exists( $row['id'], $t_category->bugs ) ) {
		$t_bug = $t_category->bugs[$row['id']];
	} else {
		$t_bug = new MantisBug( $row['id'] );
		$t_category->bugs[$row['id']] = $t_bug;
	}
	
	# Set the est, done or todo for this bug and worktype
	if ( $row['minutes_type'] == PLUGIN_PM_EST ) {
		$t_bug->est[$row['work_type']] = $row['minutes'];
	} else if ( $row['minutes_type'] == PLUGIN_PM_DONE ) {
		$t_bug->done[$row['work_type']] = $row['minutes'];
	} else if ( $row['minutes_type'] == PLUGIN_PM_TODO ) {
		$t_bug->todo[$row['work_type']] = $row['minutes'];
	}
	
	# Set the handler_id
	if ( !empty( $row['handler_id'] ) ) {
		$t_bug->handler_id = $row['handler_id'];
	}
	
	# Set the parent project
	if ( !empty( $row['parent_project'] ) ) {
		$t_project->parent_project = $row['parent_project'];
	}
}

$t_main_projects = array();
$t_largest_value = 0;

# Set main and sub projects, and keep track of the largest value to be displayed
foreach ( $t_all_projects as $t_project_name => $t_project ) {
	if ( !isset( $t_project->parent_project ) ) {
		$t_main_projects[] = $t_project;
	} else {
		$t_all_projects[$t_project->parent_project]->sub_projects[] = $t_project;
	}
	
	$t_total_est = $t_project->est();
	if ( $t_total_est > $t_largest_value ) {
		$t_largest_value = $t_total_est;
	}
	
	$t_total_work = $t_project->done() + $t_project->todo();
	if ( $t_total_work > $t_largest_value ) {
		$t_largest_value = $t_total_work;
	}
}

/*
 * test set
 * 
$t_projects = array();
$t_project1 = new MantisProject( 'Project 1' );
$t_category1 = new MantisCategory( 'Category 1', 'Project 1' );
$t_category1->bugs[] = new MantisBug( 1 );
$t_category1->bugs[] = new MantisBug( 2 );
$t_project1->categories[] = $t_category1;
$t_category2 = new MantisCategory( 'Category 2', 'Project 1' );
$t_category2->bugs[] = new MantisBug( 3 );
$t_category2->bugs[] = new MantisBug( 4 );
$t_project1->categories[] = $t_category2;
$t_subproject = new MantisProject( 'SubProject' );
$t_category3 = new MantisCategory( 'Category 3', 'SubProject' );
$t_category3->bugs[] = new MantisBug( 5 );
$t_category3->bugs[] = new MantisBug( 6 );
$t_subproject->categories[] = $t_category3;
$t_project1->subprojects[] = $t_subproject;
$t_projects[] = $t_project1;
*/

foreach ( $t_main_projects as $t_main_project ) {
	$t_main_project->print_project( $t_largest_value );
	echo '<p>';
}

?>

<?php 

html_page_bottom1( __FILE__ );

?>