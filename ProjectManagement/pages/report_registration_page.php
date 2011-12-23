<?php

$t_project_id = helper_get_current_project();
access_ensure_project_level( plugin_config_get( 'view_reports_threshold' ), $t_project_id );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_pm_reports_menu( 'report_registration_page.php' );

$t_period_start = gpc_get_string( 'period_start', first_day_of_month( 0 ) );
$t_period_end = gpc_get_string( 'period_end', last_day_of_month( 0 ) );
$t_work_table = plugin_table('work');
$t_user_table = db_get_table( 'mantis_user_table' );
$t_bug_table = db_get_table( 'mantis_bug_table' );
$t_project_table = db_get_table( 'mantis_project_table' );
$t_category_table = db_get_table( 'mantis_category_table' );

$t_query = "SELECT p.name as project_name, c.name as category_name, 
				 b.id as bug_id, b.summary as bug_summary, 
				 u.username as user_name, w.book_date, w.work_type, w.minutes
            FROM $t_work_table w
       LEFT JOIN $t_user_table u ON w.user_id = u.id
       LEFT JOIN $t_bug_table b ON w.bug_id = b.id
       LEFT JOIN $t_project_table p ON b.project_id = p.id
       LEFT JOIN $t_category_table c ON b.category_id = c.id
           WHERE w.minutes_type = " . db_param() . "
             AND w.book_date BETWEEN " . db_param() .
                           " AND " . db_param();
if ( $t_project_id != ALL_PROJECTS ) {
	$t_query += "AND p.id = " . db_param();
}

$t_params = array( PLUGIN_PM_DONE, strtotime( str_replace( '/', '-', $t_period_start ) ),
		strtotime( str_replace( '/', '-', $t_period_end ) ),
		$t_project_id);

$t_result = db_query_bound( $t_query, $t_params );
$t_num_result = db_num_rows( $t_result );

?>

<div class="center">
<table class="width100">
<tr>
<td class="center">
<form name="report_registration" method="post" action="<?php echo plugin_page('report_registration_page') ?>" >
<?php
echo plugin_lang_get( 'period' ) . ': ';
echo '<input type="text" size="8" maxlength="10" autocomplete="off" id="period_start" name="period_start" value="' . $t_period_start . '">';
date_print_calendar();
date_finish_calendar( 'period_start', 'trigger');
echo ' - <input type="text" size="8" maxlength="10" autocomplete="off" id="period_end" name="period_end" value="' . $t_period_end . '">';
date_print_calendar();
date_finish_calendar( 'period_end', 'trigger');
?>
<input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>">
</form>
</td>
</tr>
</table>
</div>
<br />
<table class="width100" cellspacing="1">
	<tr>
		<td colspan="100%" class="form-title">
		<?php echo plugin_lang_get( 'report_registration' ); ?>
		</td>
	</tr>
	<tr class="row-category">
		<td><div align="center"><?php echo lang_get( 'project_name' ) ?></div></td>
		<td><div align="center"><?php echo lang_get( 'category' ) ?></div></td>
		<td><div align="center"><?php echo lang_get( 'bug' ) ?></div></td>
		<td><div align="center"><?php echo lang_get( 'summary' ) ?></div></td>
		<td><div align="center"><?php echo lang_get( 'username' ) ?></div></td>
		<td><div align="center"><?php echo plugin_lang_get( 'book_date' ) ?></div></td>
		<td><div align="center"><?php echo plugin_lang_get( 'work_type' ) ?></div></td>
		<td><div align="center"><?php echo plugin_lang_get( 'hours' ) ?></div></td>
	</tr>
	
	<?php 
	
	for ( $i=0; $i < $t_num_result; $i++ ) {
		$row = db_fetch_array( $t_result );
		
		$t_project_name = $row["project_name"];
		$t_category_name = $row["category_name"];
		$t_bug_id = $row["bug_id"];
		$t_bug_summary = $row["bug_summary"];
		$t_user_name = $row["user_name"];
		$t_book_date = date( 'd/m/Y', $row["book_date"] );
		$t_work_type = MantisEnum::getLabel( plugin_config_get( "worktypes" ), $row["work_type"] );
		$t_hours = round( $row["minutes"] / 60, 1 );
		
		echo "<tr " . helper_alternate_class() . ">
			<td>$t_project_name</td>
			<td>$t_category_name</td>
			<td>$t_bug_id</td>
			<td>$t_bug_summary</td>
			<td>$t_user_name</td>
			<td>$t_book_date</td>
			<td>$t_work_type</td>
			<td>$t_hours</td></tr>";
	}
	
	?>
	
</table>

<?php
html_page_bottom1( __FILE__ );
?>