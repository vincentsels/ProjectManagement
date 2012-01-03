<?php

access_ensure_global_level( plugin_config_get( 'view_resource_allocation_threshold' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_pm_reports_menu( 'resource_allocation_page' );

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

foreach ( $t_projects as $t_project ) {
	$t_project->print_project();
	echo '<p>';
}

?>

<?php 

html_page_bottom1( __FILE__ );

?>