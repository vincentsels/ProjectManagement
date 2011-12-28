<?php

access_ensure_global_level( plugin_config_get( 'view_report_registration_threshold' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_pm_reports_menu( 'report_registration_page' );

$f_period_start = gpc_get_string( 'period_start', first_day_of_month( 0 ) );
$f_period_end = gpc_get_string( 'period_end', last_day_of_month( 0 ) );
$f_user_id = gpc_get_int( 'user_id', ALL_USERS );
$f_project_id = gpc_get_int( 'project_id', helper_get_current_project() );
$f_work_type = gpc_get_int( 'work_type', null );
$f_category_id = gpc_get_int( 'category_id', null );

$t_work_table = plugin_table('work');
$t_resource_table = plugin_table('resource');
$t_user_table = db_get_table( 'mantis_user_table' );
$t_bug_table = db_get_table( 'mantis_bug_table' );
$t_project_table = db_get_table( 'mantis_project_table' );
$t_category_table = db_get_table( 'mantis_category_table' );
$t_work_types = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );

$t_query = "SELECT w.user_id, u.username, w.book_date, b.project_id, p.name as project_name, 
				 c.id as category_id, c.name as category_name, b.id as bug_id, b.summary as bug_summary, 
				 w.work_type, w.minutes, r.hourly_rate
            FROM $t_work_table w
       LEFT JOIN $t_bug_table b ON w.bug_id = b.id
       LEFT JOIN $t_user_table u ON w.user_id = u.id
       LEFT JOIN $t_project_table p ON b.project_id = p.id
       LEFT JOIN $t_category_table c ON b.category_id = c.id
       LEFT OUTER JOIN $t_resource_table r ON w.user_id = r.user_id
           WHERE w.minutes_type = " . PLUGIN_PM_DONE . "
             AND w.book_date BETWEEN " . strtotime( str_replace( '/', '-', $f_period_start ) ) .
                           " AND " . strtotime( str_replace( '/', '-', $f_period_end ) );

if ( $f_project_id != ALL_PROJECTS ) {
	$t_subprojects[] = $f_project_id;
	foreach ( user_get_all_accessible_subprojects( auth_get_current_user_id(), $f_project_id ) as $t_subproject ) {
		$t_subprojects[] = $t_subproject;
	}
	$t_query .= " AND b.project_id IN ( " . implode(',', array_unique( $t_subprojects ) ) . " )";
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

$t_result = db_query_bound( $t_query, $t_params );
$t_num_result = db_num_rows( $t_result );

$t_per_work_type;
$t_per_category;

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
date_finish_calendar( 'period_start', 'period_start');
echo ' - <input type="text" size="8" maxlength="10" autocomplete="off" id="period_end" name="period_end" value="' . $f_period_end . '">';
date_print_calendar();
date_finish_calendar( 'period_end', 'period_end');
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
		<td><div align="center"><?php echo plugin_lang_get( 'hourly_rate' ) ?></div></td>
		<td><div align="center"><?php echo plugin_lang_get( 'cost' ) ?></div></td>
	</tr>
	
	<?php 
	
	$t_total_cost = 0;
	for ( $i=0; $i < $t_num_result; $i++ ) {
		$row = db_fetch_array( $t_result );
		
		$t_plugin_page = plugin_page( 'report_registration_page.php' );
		$t_username = $row["username"];
		$t_user_link = $t_plugin_page . '&user_id=' . $row["user_id"];
		$t_project_name = $row["project_name"];
		$t_project_link = $t_plugin_page . '&project_id=' . $row["project_id"];
		$t_category_name = $row["category_name"];
		$t_category_link = $t_plugin_page . '&category_id=' . $row["category_id"];
		$t_bug_summary = $row["bug_summary"];
		$t_book_date = date( 'd/m/Y', $row["book_date"] );
		$t_work_type = MantisEnum::getLabel( plugin_config_get( "work_types" ), $row["work_type"] );
		$t_work_type_link = $t_plugin_page . '&work_type=' . $row["work_type"];
		$t_hours = format( $row["minutes"] / 60 );
		$t_hourly_rate = format( $row["hourly_rate"] );
		$t_cost = format( $row["minutes"] * $row["hourly_rate"] / 60 );
		
		echo "<tr " . helper_alternate_class() . ">";
		echo "<td><a href=\"" . $t_user_link . "\">$t_username</a></td>";
		echo "<td>$t_book_date</td>";
		echo "<td><a href=\"" . $t_project_link . "\">$t_project_name</a></td>";
		echo "<td><a href=\"" . $t_category_link . "\">$t_category_name</a></td>";
		echo "<td>";
		print_bug_link( $row["bug_id"] );
		echo "</td>";
		echo "<td>$t_bug_summary</td>";
		echo "<td><a href=\"" . $t_work_type_link . "\">$t_work_type</a></td>";
		echo "<td class=\"right\">$t_hours</td>";
		echo "<td class=\"right\">$t_hourly_rate</td>";
		echo "<td class=\"right\">$t_cost</td>";
		echo "</tr>";
		
		$t_per_work_type[$row["user_id"]][$row["work_type"]] += $row["minutes"] / 60;
		$t_per_work_type[$row["user_id"]][plugin_lang_get( 'total' )] += $row["minutes"] / 60;
		
		$t_per_project[$row["user_id"]][$row["project_id"]] += $row["minutes"] / 60;
		$t_per_project[$row["user_id"]][plugin_lang_get( 'total' )] += $row["minutes"] / 60;
		
		$t_per_category[$row["user_id"]][$row["category_id"]] += $row["minutes"] / 60;
		$t_per_category[$row["user_id"]][plugin_lang_get( 'total' )] += $row["minutes"] / 60;
		
		$t_total_cost += $row["minutes"] * $row["hourly_rate"] / 60;
	}
	
	# Display a total cost line
	echo '<tr class="spacer">';
	echo '<tr class="row-category2"><td colspan="9" class="right bold">' . plugin_lang_get( 'total_cost' ) . '</td>';
	echo '<td class="right bold">' . format( $t_total_cost )  . '</td></td>';
	?>
	
	</table>
	
	<table width="100%" cellspacing="1">
	<tr>
	<td>
	<?php 
	if ( count( $t_per_work_type ) > 0 ) {
		?>
		<br />
		<table class="width100" cellspacing="1">
			<tr>
				<td colspan="100%" class="form-title">
				<?php echo plugin_lang_get( 'time_division' ); ?> - 
				<?php echo plugin_lang_get( 'per' ); ?> <?php echo plugin_lang_get( 'work_type' ); ?>
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
					echo "<td class=\"right\">". format( $t_categories[$t_work_type_value] ) . "</td>";
				}
				echo "<td class=\"right\">" . format( $t_categories[plugin_lang_get( 'total' )] ) . "</td></tr>";
			}
			?>
			
		</table>
		<?php
	}
	?>
	</td>
	<td>
	<?php 
	if ( count( $t_per_category ) > 0 ) {
		?>
		<br />
		<table class="width100" cellspacing="1">
			<tr>
				<td colspan="100%" class="form-title">
				<?php echo plugin_lang_get( 'time_division' ); ?> - 
				<?php echo plugin_lang_get( 'per' ); ?> <?php echo lang_get( 'category' ); ?>
				</td>
			</tr>
			<tr class="row-category">
				<td><div align="center"><?php echo lang_get( 'username' ) ?></div></td>
				<?php 
				foreach ( category_get_all_rows( $f_project_id  ) as $row ) {
					?>
					<td><div align="center"><?php echo $row["name"] ?></div></td>
					<?php 
				}
				?>
				<td><div align="center"><?php echo plugin_lang_get( 'total' ) ?></div></td>
			</tr>
			
			<?php 
			foreach ( $t_per_category as $t_user => $t_categories ) {
				echo "<tr " . helper_alternate_class() . ">";
				echo '<td class="category">';
				print_user( $t_user );
				echo "</td>";
				foreach ( category_get_all_rows( $f_project_id  ) as $row ) {
					echo "<td class=\"right\">" . format( $t_categories[$row["id"]] ) . "</td>";
				}
				echo "<td class=\"right\">" . format( $t_categories[plugin_lang_get( 'total' )] ) . "</td></tr>";
			}
			?>
			
		</table>
		<?php
	}
	?>
	</td>
	</tr>
	</table>
	<?php
html_page_bottom1( __FILE__ );
?>