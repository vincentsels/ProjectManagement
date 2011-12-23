<?php

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_pm_reports_menu( 'report_registration_page.php' );

$f_period_start = gpc_get_string( 'period_start', first_day_of_month( 0 ) );
$f_period_end = gpc_get_string( 'period_end', last_day_of_month( 0 ) );
$f_user_id = gpc_get_int( 'user_id', ALL_USERS );
$f_project_id = gpc_get_int( 'project_id', helper_get_current_project() );

access_ensure_project_level( plugin_config_get( 'view_reports_threshold' ), $f_project_id );

$t_work_table = plugin_table('work');
$t_user_table = db_get_table( 'mantis_user_table' );
$t_bug_table = db_get_table( 'mantis_bug_table' );
$t_project_table = db_get_table( 'mantis_project_table' );
$t_category_table = db_get_table( 'mantis_category_table' );
$t_work_types = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );

$t_query = "SELECT w.user_id, u.username, w.book_date, b.project_id, p.name as project_name, c.name as category_name, 
				 b.id as bug_id, b.summary as bug_summary, 
				 w.work_type, w.minutes
            FROM $t_work_table w
       LEFT JOIN $t_bug_table b ON w.bug_id = b.id
       LEFT JOIN $t_user_table u ON w.user_id = u.id
       LEFT JOIN $t_project_table p ON b.project_id = p.id
       LEFT JOIN $t_category_table c ON b.category_id = c.id
           WHERE w.minutes_type = " . db_param() . "
             AND w.book_date BETWEEN " . db_param() .
                           " AND " . db_param();

$t_params = array( PLUGIN_PM_DONE, strtotime( str_replace( '/', '-', $f_period_start ) ),
		strtotime( str_replace( '/', '-', $f_period_end ) ));

if ( $f_project_id != ALL_PROJECTS ) {
	$t_query .= " AND b.project_id = " . db_param();
	$t_params[] = $f_project_id;
}


if ( $f_user_id != ALL_USERS ) {
	$t_query .= " AND w.user_id = " . db_param();
	$t_params[] = $f_user_id;
}

$t_query .= " ORDER BY user_id, book_date, project_name, category_name, bug_id";

$t_result = db_query_bound( $t_query, $t_params );
$t_num_result = db_num_rows( $t_result );

$t_per_work_type;

?>

<div class="center">
<table class="width100">
<tr>
<td class="center">
<form name="report_registration" method="post" action="<?php echo plugin_page('report_registration_page') ?>" >
<?php
echo plugin_lang_get( 'period' ) . ': ';
echo '<input type="text" size="8" maxlength="10" autocomplete="off" id="period_start" name="period_start" value="' . $f_period_start . '">';
date_print_calendar();
date_finish_calendar( 'period_start', 'trigger');
echo ' - <input type="text" size="8" maxlength="10" autocomplete="off" id="period_end" name="period_end" value="' . $f_period_end . '">';
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
		<?php echo plugin_lang_get( 'time_registration' ); ?>
		</td>
	</tr>
	<tr class="row-category">
		<td><div align="center"><?php echo lang_get( 'username' ) ?></div></td>
		<td><div align="center"><?php echo plugin_lang_get( 'book_date' ) ?></div></td>
		<td><div align="center"><?php echo lang_get( 'project_name' ) ?></div></td>
		<td><div align="center"><?php echo lang_get( 'category' ) ?></div></td>
		<td><div align="center"><?php echo lang_get( 'bug' ) ?></div></td>
		<td><div align="center"><?php echo lang_get( 'summary' ) ?></div></td>
		<td><div align="center"><?php echo plugin_lang_get( 'work_type' ) ?></div></td>
		<td><div align="center"><?php echo plugin_lang_get( 'hours' ) ?></div></td>
	</tr>
	
	<?php 
	
	for ( $i=0; $i < $t_num_result; $i++ ) {
		$row = db_fetch_array( $t_result );
		
		$t_username = $row["username"];
		$t_user_link = plugin_page( 'report_registration_page.php' ) . '&user_id=' . $row["user_id"];
		$t_project_name = $row["project_name"];
		$t_project_link = plugin_page( 'report_registration_page.php' ) . '&project_id=' . $row["project_id"];
		$t_category_name = $row["category_name"];
		$t_bug_summary = $row["bug_summary"];
		$t_book_date = date( 'd/m/Y', $row["book_date"] );
		$t_work_type = MantisEnum::getLabel( plugin_config_get( "work_types" ), $row["work_type"] );
		$t_hours = round( $row["minutes"] / 60, 1 );
		
		echo "<tr " . helper_alternate_class() . ">";
		echo "<td><a href=" . $t_user_link . ">$t_username</a></td>";
		echo "<td>$t_book_date</td>";
		echo "<td><a href=" . $t_project_link . ">$t_project_name</a></td>";
		echo "<td>$t_category_name</td>";
		echo "<td>";
		print_bug_link( $row["bug_id"] );
		echo "</td>";
		echo "<td>$t_bug_summary</td>";
		echo "<td>$t_work_type</td>";
		echo "<td>$t_hours</td></tr>";
		
		$t_per_work_type[$row["user_id"]][$row["work_type"]] += $t_hours;
		$t_per_work_type[$row["user_id"]][plugin_lang_get( 'total' )] += $t_hours;
	}
	?>
	
	</table>
	
	<?php 
	if ( count( $t_per_work_type ) > 0 ) {
		?>
		<br />
		<table class="width100" cellspacing="1">
			<tr>
				<td colspan="100%" class="form-title">
				<?php echo plugin_lang_get( 'time_division' ); ?>
				</td>
			</tr>
			<tr class="row-category">
				<td><div align="center"><?php echo lang_get( 'username' ) ?></div></td>
				<?php 
				foreach ( $t_work_types as $t_work_type_value => $t_work_type_label ) {
					?>
					<td><div align="center"><?php echo $t_work_type_label ?></div></td>
					<?php 
				}
				?>
				<td><div align="center"><?php echo plugin_lang_get( 'total' ) ?></div></td>
			</tr>
			
			<?php 
			
			
			foreach ( $t_per_work_type as $t_user => $t_categories ) {
				echo "<tr " . helper_alternate_class() . ">";
				echo '<td class="category">';
				print_user( $t_user );
				echo "</td>";
				foreach ( $t_work_types as $t_work_type_value => $t_work_type_label ) {
					echo "<td>$t_categories[$t_work_type_value]</td>";
				}
				echo "<td>" . $t_categories[plugin_lang_get( 'total' )] . "</td></tr>";
			}
			
			?>
			
		</table>
		
		<?php
	}
html_page_bottom1( __FILE__ );
?>