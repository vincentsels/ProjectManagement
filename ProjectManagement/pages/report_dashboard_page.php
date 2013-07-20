<?php

access_ensure_global_level( plugin_config_get( 'view_registration_report_threshold' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_pm_reports_menu( 'report_dashboard_page' );

$f_period_start = gpc_get_string( 'period_start', first_day_of_month( 0 ) );
$f_period_end   = gpc_get_string( 'period_end', last_day_of_month( 0 ) );
$f_user_id      = gpc_get_int( 'user_id', ALL_USERS );
$f_project_id   = gpc_get_int( 'project_id', helper_get_current_project() );
$f_work_type    = gpc_get_int( 'work_type', null );
$f_category_id  = gpc_get_int( 'category_id', null );

$t_work_table     = plugin_table( 'work' );
$t_resource_table = plugin_table( 'resource' );
$t_user_table     = db_get_table( 'mantis_user_table' );
$t_bug_table      = db_get_table( 'mantis_bug_table' );
$t_project_table  = db_get_table( 'mantis_project_table' );
$t_category_table = db_get_table( 'mantis_category_table' );
$t_work_types     = plugin_lang_get_enum( 'work_types' ); // MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );
$t_customer_work_type_exclusion_clause = build_customer_worktype_exclude_clause('work_type');

$t_const_done = PLUGIN_PM_DONE;
$t_startdate  = strtotime_safe( $f_period_start );
$t_enddate    = strtotime_safe( $f_period_end );
$t_query      = "SELECT w.user_id, u.username, w.book_date, b.project_id, p.name as project_name,
						 c.id as category_id, c.name as category_name, b.id as bug_id, b.summary as bug_summary,
						 b.status, w.work_type, w.minutes, r.hourly_rate
					FROM $t_work_table w
			   LEFT JOIN $t_bug_table b ON w.bug_id = b.id
			   LEFT JOIN $t_user_table u ON w.user_id = u.id
			   LEFT JOIN $t_project_table p ON b.project_id = p.id
			   LEFT JOIN $t_category_table c ON b.category_id = c.id
			   LEFT OUTER JOIN $t_resource_table r ON w.user_id = r.user_id
				   WHERE w.minutes_type = $t_const_done
					 AND w.book_date BETWEEN $t_startdate AND $t_enddate
					 AND $t_customer_work_type_exclusion_clause";

if ( $f_project_id != ALL_PROJECTS ) {
    $t_project_select_clause = get_project_select_clause();
	$t_query .= " AND " . $t_project_select_clause;
}

if ( $f_user_id != ALL_USERS ) {
	$t_query .= " AND w.user_id = $f_user_id";
}

if ( !empty( $f_work_type ) ) {
	$t_query .= " AND w.work_type = $f_work_type";
}

if ( !empty( $f_category_id ) ) {
	$t_query .= " AND b.category_id = $f_category_id";
}

$t_query .= " ORDER BY user_id, book_date, project_name, category_name, bug_id";

$t_result = db_query_bound( $t_query );

$t_per_work_type = array();
$t_per_project   = array();
$t_per_category  = array();

$t_collapse_time_registration  = 'plugin_pm_time_registration';
?>

<div class="center">
	<table class="width100">
		<tr>
			<td class="center">
				<form name="report_registration" method="post"
					  action="<?php echo plugin_page( 'report_dashboard_page' ) ?>">
					<?php
					echo plugin_lang_get( 'period' ) . ': ';
					echo '<input type="text" size="8" maxlength="10" autocomplete="off" id="period_start" name="period_start" value="' . $f_period_start . '">';
					date_print_calendar( 'period_start_cal' );
					date_finish_calendar( 'period_start', 'period_start_cal' );
					echo ' - <input type="text" size="8" maxlength="10" autocomplete="off" id="period_end" name="period_end" value="' . $f_period_end . '">';
					date_print_calendar( 'period_end_cal' );
					date_finish_calendar( 'period_end', 'period_end_cal' );
					echo ' - ', lang_get( 'username' ), ': ';
					?>
					<select <?php echo helper_get_tab_index() ?> name="user_id">
						<option value="0" selected="selected"></option>
						<?php print_user_option_list( $f_user_id ) ?>
					</select>
					<input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>">
				</form>
			</td>
		</tr>
	</table>
</div>

<?php
$t_total_cost = 0;
while ( $row = db_fetch_array( $t_result ) ) {
	$t_plugin_page    = plugin_page( 'report_dashboard_page.php' );
	$t_username       = $row["username"];
	$t_user_link      = $t_plugin_page . '&user_id=' . $row["user_id"];
	$t_project_name   = $row["project_name"];
	$t_project_link   = $t_plugin_page . '&project_id=' . $row["project_id"];
	$t_category_name  = $row["category_name"];
	$t_category_link  = $t_plugin_page . '&category_id=' . $row["category_id"];
	$t_bug_summary    = $row["bug_summary"];
	$t_book_date      = date( config_get( 'short_date_format' ), $row["book_date"] );
	$t_work_type      = plugin_get_enum_element( 'work_types', $row["work_type"]  );
	$t_work_type_link = $t_plugin_page . '&work_type=' . $row["work_type"];
	$t_hours          = format( $row["minutes"] / 60 );
	$t_hourly_rate    = format( $row["hourly_rate"] );
	$t_cost           = format( $row["minutes"] * $row["hourly_rate"] / 60 );

	@$t_per_work_type[$row["user_id"]][$row["work_type"]] += $row["minutes"] / 60;
	@$t_per_work_type[$row["user_id"]][plugin_lang_get( 'total' )] += $row["minutes"] / 60;

	if ( ! isset ( $t_per_project[$row["project_id"]]) || ! array_key_exists( $row["user_id"], $t_per_project[$row["project_id"]]["users"] ) ) {
		@$t_per_project[$row["project_id"]]["total_hourly_rate"] += $t_hourly_rate;
		@$t_per_project[$row["project_id"]]["total_users"] += 1;
		@$t_per_project[$row["project_id"]]["average_hourly_rate"] = @$t_per_project[$row["project_id"]]["total_hourly_rate"] / @$t_per_project[$row["project_id"]]["total_users"];
	}
	@$t_per_project[$row["project_id"]]["users"][$row["user_id"]]["time"] += $row["minutes"]; // / 60;
	@$t_per_project[$row["project_id"]]["users"][$row["user_id"]]["hourly_rate"] = $t_hourly_rate;
	@$t_per_project[$row["project_id"]]["users"][$row["user_id"]]["cost"] += $t_cost;
	@$t_per_project[$row["project_id"]]["users"][$row["user_id"]]["work_types"][$row["work_type"]]["time"] += $row["minutes"]; // / 60;
	@$t_per_project[$row["project_id"]]["users"][$row["user_id"]]["work_types"][$row["work_type"]]["cost"] += $t_cost;
	@$t_per_project[$row["project_id"]]["users"][$row["user_id"]]["total_work_types_time"] += $row["minutes"]; // / 60;
	@$t_per_project[$row["project_id"]]["users"][$row["user_id"]]["total_work_types_cost"] += $t_cost;
	@$t_per_project[$row["project_id"]]["total_time"] += $row["minutes"]; // / 60;
	@$t_per_project[$row["project_id"]]["total_cost"] += $t_cost;

	@$t_per_project[$row["project_id"]]["work_types"][$row["work_type"]]["time"] += $row["minutes"]; // / 60;
	@$t_per_project[$row["project_id"]]["work_types"][$row["work_type"]]["cost"] += $t_cost;
	
	
	@$t_per_category[$row["user_id"]][$row["category_id"]] += $row["minutes"] / 60;
	@$t_per_category[$row["user_id"]][plugin_lang_get( 'total' )] += $row["minutes"] / 60;

	@$t_total_cost += $row["minutes"] * $row["hourly_rate"] / 60;
	@$t_total_time += $t_hours;
}
?>


<br/>
<table width="100%" cellspacing="0" cellpadding="0" style="padding:0; magin:0">
	<tr>
		<td style="padding:0; magin:0; vertical-align:top" class="top">
<?php include 'report_registration_page_time_per_project_inc.php'; ?>
		</td>
		<td style="padding:0px 0px 0px 3px; margin:0; vertical-align:top">
<?php include 'report_registration_page_time_per_project_work_type_inc.php'; ?>
		</td>
	</tr>
</table>

<br/>
<?php include 'report_registration_page_time_per_project_detail_inc.php'; ?>

<?php
html_page_bottom1( __FILE__ );
?>
