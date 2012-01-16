<?php

access_ensure_global_level( plugin_config_get( 'view_resource_allocation_threshold' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_pm_reports_menu( 'resource_allocation_page' );

$f_target_version = gpc_get_string( 'target_version', null );
$f_user_id = gpc_get_int( 'user_id', ALL_USERS );

if ( is_null( $f_target_version ) ) {
	# Attempt to get the most logical one - the first non-released
	$t_non_released = version_get_all_rows_with_subs( null, false, false );
	if ( count( $t_non_released ) > 0 ) {
		$f_target_version = $t_non_released[count( $t_non_released ) - 1]['version'];
	}
}

$t_bug_table = db_get_table( 'mantis_bug_table' );
$t_project_table = db_get_table( 'mantis_project_table' );
$t_category_table = db_get_table( 'mantis_category_table' );
$t_hierarchy_table = db_get_table( 'mantis_project_hierarchy_table' );
$t_work_table = plugin_table( 'work' );

$t_query = "SELECT pp.name as parent_project, pc.name as project_name, c.name as category_name, b.id, b.handler_id, w.work_type, w.minutes_type, sum(w.minutes) as minutes
  FROM $t_bug_table b
  JOIN $t_project_table pc ON b.project_id = pc.id
  JOIN $t_category_table c ON b.category_id = c.id
  LEFT OUTER JOIN $t_hierarchy_table h ON pc.id = h.child_id
  LEFT OUTER JOIN $t_project_table pp ON h.parent_id = pp.id
  LEFT OUTER JOIN $t_work_table w ON b.id = w.bug_id 
 WHERE b.target_version = '$f_target_version'";

if ( $f_user_id != ALL_USERS ) {
	$t_query .= " AND b.handler_id = $f_user_id";
}

$t_query .= " GROUP BY pp.name, pc.name, c.name, b.id, b.handler_id, w.work_type, w.minutes_type";

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
		$t_project = new MantisPmProject( $row['project_name'] );
		$t_all_projects[$row['project_name']] = $t_project;
	}
	
	# Check whether this category already exists in this project and if not, create it
	if ( !is_null( $t_project->categories ) && array_key_exists( $row['category_name'], $t_project->categories ) ) {
		$t_category = $t_project->categories[$row['category_name']];
	} else {
		$t_category = new MantisPmCategory( $row['category_name'], $row['project_name'] );
		$t_project->categories[$row['category_name']] = $t_category;
	}
	
	# Check whether this ticket already exists in this category and if not, add it
	if ( !is_null( $t_category->bugs ) && array_key_exists( $row['id'], $t_category->bugs ) ) {
		$t_bug = $t_category->bugs[$row['id']];
	} else {
		$t_bug = new MantisPmBug( $row['id'] );
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

# Add empty main projects who don't contain anything themselves
foreach ( $t_all_projects as $t_project_name => $t_project ) {
	if ( isset( $t_project->parent_project ) && !array_key_exists( $t_project->parent_project , $t_all_projects ) ) {
		$t_parent = new MantisPmProject( $t_project->parent_project );
		$t_all_projects[$t_project->parent_project] = $t_parent;
	}
}

# Set main and sub projects
$t_main_projects = array();
foreach ( $t_all_projects as $t_project_name => $t_project ) {
	if ( !isset( $t_project->parent_project ) ) {
		$t_main_projects[] = $t_project;
	} else { 
		$t_all_projects[$t_project->parent_project]->sub_projects[] = $t_project;
	}
}

# Keep track of the largest value to be displayed
$t_largest_value = 0;
foreach ( $t_all_projects as $t_project_name => $t_project ) {
	$t_project_max_real_est = $t_project->get_max_real_est();
	if ( $t_project_max_real_est > $t_largest_value ) {
		$t_largest_value = $t_project_max_real_est;
	}
}

# Cache resource colors so they can be used in the printing
global $g_resource_colors;

$t_resource_table = plugin_table( 'resource' );
$t_color_query = "SELECT user_id, color FROM $t_resource_table WHERE color IS NOT NULL";
$t_color_result = db_query_bound( $t_color_query );
$t_color_rownum = db_num_rows( $t_color_result );

for ( $i = 0; $i < $t_color_rownum; $i++ ) {
	$t_row = db_fetch_array( $t_color_result );
	$g_resource_colors[$t_row['user_id']] = $t_row['color'];
}

?>

<div class="center">
<table class="width100">
<tr>
<td class="center">
<form name="resource_allocation" method="post" action="<?php echo plugin_page('resource_allocation_page') ?>" >
<?php echo lang_get( 'target_version' ), ': ' ?>
<select name="target_version"><?php print_version_option_list( $f_target_version, null, VERSION_FUTURE, false, true ) ?></select>
<?php echo ' - ', lang_get( 'username' ), ': ' ?>
<select name="user_id">
	<option value="0" selected="selected"></option>
	<?php print_user_option_list( $f_user_id ) ?>
</select>
<input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>">
</form>
</td>
</tr>
</table>
</div>
<br />

<?php 

foreach ( $t_main_projects as $t_main_project ) {
	$t_main_project->print_project( $t_largest_value );
	echo '<p>';
}

html_page_bottom1( __FILE__ );

?>
