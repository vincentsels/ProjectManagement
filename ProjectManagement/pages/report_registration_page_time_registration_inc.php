<?php collapse_open( $t_collapse_time_registration ); ?>
<table class="width100" cellspacing="1">
	<tr>
		<td colspan="100%" class="form-title">
			<?php collapse_icon( $t_collapse_time_registration ); ?>
			<?php echo plugin_lang_get( 'time_registration' ); ?>
		</td>
	</tr>
	<tr class="row-category">
		<td>
			<div align="center"><?php echo lang_get( 'username' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo plugin_lang_get( 'book_date' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo lang_get( 'project_name' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo lang_get( 'category' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo lang_get( 'bug' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo lang_get( 'summary' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo plugin_lang_get( 'work_type' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo plugin_lang_get( 'hours' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo plugin_lang_get( 'hourly_rate' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo plugin_lang_get( 'cost' ) ?></div>
		</td>
	</tr>
    <tr class="spacer"/>

	<?php

	$t_total_cost = 0;
	while ( $row = db_fetch_array( $t_result ) ) {
		$t_plugin_page    = plugin_page( 'report_registration_page.php' );
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

		echo "<tr bgcolor=\"" . get_status_color( $row["status"] ) . "\">";
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

		@$t_per_work_type[$row["user_id"]][$row["work_type"]] += $row["minutes"] / 60;
		@$t_per_work_type[$row["user_id"]][plugin_lang_get( 'total' )] += $row["minutes"] / 60;

		@$t_per_project[$row["user_id"]][$row["project_id"]] += $row["minutes"] / 60;
		@$t_per_project[$row["user_id"]][plugin_lang_get( 'total' )] += $row["minutes"] / 60;

		@$t_per_category[$row["user_id"]][$row["category_id"]] += $row["minutes"] / 60;
		@$t_per_category[$row["user_id"]][plugin_lang_get( 'total' )] += $row["minutes"] / 60;

		@$t_total_cost += $row["minutes"] * $row["hourly_rate"] / 60;
	}

	# Display a total cost line
	echo '<tr class="spacer">';
	echo '<tr class="row-category2"><td colspan="9" class="right bold">' . plugin_lang_get( 'total_cost' ) . '</td>';
	echo '<td class="right bold">' . format( $t_total_cost ) . '</td></tr>';
	?>

</table>
<?php collapse_closed( $t_collapse_time_registration ); ?>

<table class="width100" cellspacing="1">
	<tr>
		<td colspan="100%" class="form-title">
			<?php collapse_icon( $t_collapse_time_registration ); ?>
			<?php echo plugin_lang_get( 'time_registration' ); ?>
		</td>
	</tr>
</table>
<?php collapse_end( $t_collapse_time_registration ); ?>
