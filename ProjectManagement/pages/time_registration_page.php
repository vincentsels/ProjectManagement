<?php

access_ensure_global_level( plugin_config_get( 'view_registration_worksheet_threshold' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_pm_reports_menu( 'time_registration_page' );

$t_user                   = auth_get_current_user_id();
$t_recently_visited_array = recently_visited_bugs_get( $t_user );
$t_recently_visited       = implode( ', ', $t_recently_visited_array );

$t_work_table     = plugin_table( 'work' );
$t_bug_table      = db_get_table( 'mantis_bug_table' );
$t_project_table  = db_get_table( 'mantis_project_table' );
$t_category_table = db_get_table( 'mantis_category_table' );

$t_const_done             = PLUGIN_PM_DONE;
$t_const_recently_visited = PLUGIN_PM_TOKEN_RECENTLY_VISITED_COUNT;
$t_query_recent           = "SELECT b.last_updated, b.handler_id, b.project_id, p.name as project_name,
                            c.id as category_id, c.name as category_name, b.id as bug_id, b.summary as bug_summary, b.status,
                            (SELECT SUM(minutes) FROM $t_work_table w WHERE w.bug_id = b.id AND minutes_type = $t_const_done) as done
                             FROM $t_bug_table b
                             LEFT JOIN $t_project_table p ON b.project_id = p.id
                             LEFT JOIN $t_category_table c ON b.category_id = c.id
                            WHERE b.id IN ( $t_recently_visited )
                            ORDER BY b.last_updated DESC
                            LIMIT $t_const_recently_visited";
if ( !empty( $t_recently_visited ) ) {
	$t_result_recent = db_query_bound( $t_query_recent );
}

$t_today                 = strtotime( date( 'Y-m-d' ) );
$t_query_registered_day  = "SELECT b.id as bug_id, sum(w.minutes) as minutes, max(w.timestamp) as timestamp
							 FROM $t_work_table w JOIN $t_bug_table b ON w.bug_id = b.id
							WHERE w.user_id = $t_user
							  AND w.book_date = $t_today
							  AND w.minutes_type = 1
							GROUP BY bug_id
							ORDER BY timestamp DESC";
$t_result_registered_day = db_query_bound( $t_query_registered_day );

$t_week_start = strtotime( date( 'Y-m-d', strtotime( 'last sunday' ) ) );
$t_week_end   = strtotime( date( 'Y-m-d', strtotime( 'next sunday' ) ) );
# We can safely group by book_date, since this is always rounded to a day
$t_query_registered_week  = "SELECT w.book_date, sum(w.minutes) as minutes
							 FROM $t_work_table w
							WHERE w.user_id = $t_user
							  AND w.book_date BETWEEN $t_week_start AND $t_week_end
							  AND w.minutes_type = 1
							GROUP BY book_date
							ORDER BY book_date DESC";
$t_result_registered_week = db_query_bound( $t_query_registered_week );

$t_last_sunday                 = strtotime( 'last sunday' );
$t_last_week_start             = mktime( 0, 0, 0, date( 'm', $t_last_sunday ), date( 'd', $t_last_sunday ) - 7, date( 'Y', $t_last_sunday ) );
$t_last_week_end               = $t_last_sunday - 1;
$t_query_registered_last_week  = "SELECT w.book_date, sum(w.minutes) as minutes
								 FROM $t_work_table w
								WHERE w.user_id = $t_user
								  AND w.book_date BETWEEN $t_last_week_start AND $t_last_week_end
								  AND w.minutes_type = 1
								GROUP BY book_date
								ORDER BY book_date DESC";
$t_result_registered_last_week = db_query_bound( $t_query_registered_last_week );

$t_month_start             = mktime( 0, 0, 0, date( 'm' ), 1 );
$t_month_end               = mktime( 0, 0, 0, date( 'm' ) + 1, 1 ) - 1;
$t_query_registered_month  = "SELECT sum(w.minutes) as minutes
							 FROM $t_work_table w
							WHERE w.user_id = $t_user
							  AND w.book_date BETWEEN $t_month_start AND $t_month_end
							  AND w.minutes_type = 1";
$t_result_registered_month = db_query_bound( $t_query_registered_month );
$t_row_month               = db_fetch_array( $t_result_registered_month );

$t_last_month_start             = mktime( 0, 0, 0, date( 'm' ) - 1, 1 );
$t_last_month_end               = mktime( 0, 0, 0, date( 'm' ), 1 ) - 1;
$t_query_registered_last_month  = "SELECT sum(w.minutes) as minutes
									 FROM $t_work_table w
									WHERE w.user_id = $t_user
									  AND w.book_date BETWEEN $t_last_month_start AND $t_last_month_end
									  AND w.minutes_type = 1";
$t_result_registered_last_month = db_query_bound( $t_query_registered_last_month );
$t_row_last_month               = db_fetch_array( $t_result_registered_last_month );

?>

<table width="100%">
<tr class="print">

<td>

	<form name="time_registration" method="post" action="<?php echo plugin_page( 'time_registration_update' ) ?>">

		<input type="hidden" name="redirect_page" value="time_registration_page"/>

		<?php
		echo form_security_field( 'plugin_ProjectManagement_time_registration_update' );

		foreach ( $t_recently_visited_array as $t_bug_id ) {
			printf( "<input type=\"hidden\" name=\"bug_ids[]\" value=\"%d\" />", $t_bug_id );
		}
		?>

		<table class="width100" cellspacing="1">

			<tr>
				<td colspan="100%" class="form-title"><?php echo plugin_lang_get( 'recently_visited' ) ?>
					<span class="floatright">
		<?php
						if ( access_has_global_level( plugin_config_get( 'include_bookdate_threshold' ) ) ) {
							echo plugin_lang_get( 'book_date' ) . ': ';
							echo '<input type="text" size="8" maxlength="10" autocomplete="off" id="book_date" name="book_date" value="' . date( 'd/m/Y' ) . '"> ';
							date_print_calendar( 'book_date_cal' );
							date_finish_calendar( 'book_date', 'book_date_cal' );
						}
						?>
						<input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>">
	</span>
				</td>
			</tr>

			<tr class="row-category">
				<td>
					<div align="center"><?php echo lang_get( 'last_update' ) ?></div>
				</td>
				<td>
					<div align="center"><?php echo lang_get( 'project_name' ) ?></div>
				</td>
				<td>
					<div align="center"><?php echo lang_get( 'summary' ) ?></div>
				</td>
				<td>
					<div align="center"><?php echo plugin_lang_get( 'done' ) ?></div>
				</td>
			</tr>
			<tr class="spacer"/>

			<?php
			if ( !empty( $t_recently_visited ) ) {
				while ( $row = db_fetch_array( $t_result_recent ) ) {
					$t_last_update  = date( config_get( 'normal_date_format' ), $row["last_updated"] );
					$t_project_name = $row["project_name"] . ' - ' . $row["category_name"];
					$t_done_total   = minutes_to_time( $row["done"], false );

					# choose color based on status
					$status_color = get_status_color( $row["status"] );

					?>

					<tr bgcolor="<?php echo $status_color ?>">
						<td>
							<?php
							if ( $row["last_updated"] > strtotime( '-8 hours' ) ) {
								echo '<b>' . $t_last_update . '</b>';
							} else {
								echo $t_last_update;
							}
							if ( !empty( $row['handler_id'] ) ) {
								print ' - ' . prepare_user_name( $row['handler_id'] );
							}
							?>
						</td>
						<td><?php echo $t_project_name ?></td>
						<td>
							<?php
							print_bug_link( $row["bug_id"], plugin_config_get( 'display_detailed_bug_link' ) );
							echo ': ' . string_shorten( $row["bug_summary"], 70 );
							?>
						</td>
						<td class="right" style="min-width:270px">
							<?php echo $t_done_total ?>
							+ <input type="text" size="4" maxlength="7" autocomplete="off"
									 name= <?php echo '"add_' . $row["bug_id"] . '_' . PLUGIN_PM_DONE . '"' ?>>
							<select name="work_type_<?php echo $row["bug_id"] ?>">
								<?php print_plugin_enum_string_option_list( 'work_types', plugin_config_get( 'default_worktype' ) ) ?>
							</select>
						</td>
					</tr>

					<?php
				}
			}
			?>
		</table>
	</form>
</td>

<td>
	<table class="width100" cellspacing="1">
		<tr>
			<td colspan="100%" class="form-title">
				<?php echo plugin_lang_get( 'recently_registered' ) . ': ' . plugin_lang_get( 'day' ) ?>
			</td>
		</tr>
		<tr class="row-category">
			<td>
				<div align="center"><?php echo init_cap( 'bug' ) ?></div>
			</td>
			<td>
				<div align="center"><?php echo init_cap( 'hours', true ) ?></div>
			</td>
		</tr>
		<tr class="spacer"/>

		<?php
		$t_total = 0;
		while ( $row = db_fetch_array( $t_result_registered_day ) ) {
			$t_total += $row["minutes"];
			$t_hours = minutes_to_time( $row["minutes"], false );
			?>

			<tr <?php echo helper_alternate_class() ?>>
				<td><?php print_bug_link( $row["bug_id"], plugin_config_get( 'display_detailed_bug_link' ) ); ?></td>
				<td class="right"><?php echo $t_hours ?></td>
			</tr>

			<?php
		}
		?>
		<tr class="row-category2">
			<td class="bold"><?php echo init_cap( array( 'total', 'day' ), true ) ?></td>
			<td class="bold right"><?php echo minutes_to_time( $t_total, false ) ?></td>
		</tr>

	</table>

	<br/>

	<table class="width100" cellspacing="1">
		<tr>
			<td colspan="100%" class="form-title">
				<?php echo plugin_lang_get( 'recently_registered' ) . ': ' . plugin_lang_get( 'week' ) ?>
			</td>
		</tr>
		<tr class="row-category">
			<td>
				<div align="center"><?php echo plugin_lang_get( 'book_date' ) ?></div>
			</td>
			<td>
				<div align="center"><?php echo plugin_lang_get( 'hours' ) ?></div>
			</td>
		</tr>
		<tr class="spacer"/>

		<?php
		$t_total = 0;
		while ( @$row = db_fetch_array( $t_result_registered_week ) ) {
			$t_total += $row["minutes"];
			$t_book_date = format_short_date( $row["book_date"] );
			$t_hours     = minutes_to_time( $row["minutes"], false );
			?>

			<tr <?php echo helper_alternate_class() ?>>
				<td><?php echo $t_book_date ?></td>
				<td class="right"><?php echo $t_hours ?></td>
			</tr>

			<?php
		}
		?>
		<tr class="row-category2">
			<td class="bold"><?php echo init_cap( array( 'total', 'week' ), true ) ?></td>
			<td class="bold right"><?php echo minutes_to_time( $t_total, false ) ?></td>
		</tr>

	</table>

	<br/>

	<table class="width100" cellspacing="1">
		<tr>
			<td colspan="100%" class="form-title">
				<?php echo plugin_lang_get( 'recently_registered' ) . ': ' . plugin_lang_get( 'last_week' ) ?>
			</td>
		</tr>
		<tr class="row-category">
			<td>
				<div align="center"><?php echo plugin_lang_get( 'book_date' ) ?></div>
			</td>
			<td>
				<div align="center"><?php echo plugin_lang_get( 'hours' ) ?></div>
			</td>
		</tr>
		<tr class="spacer"/>

		<?php
		$t_total = 0;
		while ( $row = db_fetch_array( $t_result_registered_last_week ) ) {
			$t_total += $row["minutes"];
			$t_book_date = format_short_date( $row["book_date"] );
			$t_hours     = minutes_to_time( $row["minutes"], false );
			?>

			<tr <?php echo helper_alternate_class() ?>>
				<td><?php echo $t_book_date ?></td>
				<td class="right"><?php echo $t_hours ?></td>
			</tr>

			<?php
		}
		?>
		<tr class="row-category2">
			<td class="bold"><?php echo init_cap( array( 'total', 'last_week' ), true ) ?></td>
			<td class="bold right"><?php echo minutes_to_time( $t_total, false ) ?></td>
		</tr>

	</table>

	<br/>

	<table class="width100" cellspacing="1">
		<tr>
			<td colspan="100%" class="form-title">
				<?php echo plugin_lang_get( 'recently_registered' ) . ': ' . plugin_lang_get( 'last_months' ) ?>
			</td>
		</tr>
		<tr class="row-category">
			<td>
				<div align="center"><?php echo plugin_lang_get( 'month' ) ?></div>
			</td>
			<td>
				<div align="center"><?php echo plugin_lang_get( 'hours' ) ?></div>
			</td>
		</tr>
		<tr class="spacer"/>

		<tr <?php echo helper_alternate_class() ?>>
			<td><?php echo date( 'F' ) ?></td>
			<td class="right"><?php echo minutes_to_time( $t_row_month["minutes"], false ) ?></td>
		</tr>

		<tr <?php echo helper_alternate_class() ?>>
			<td><?php echo date( 'F', mktime( 0, 0, 0, date( 'm' ) - 1, 1 ) ) ?></td>
			<td class="right"><?php echo minutes_to_time( $t_row_last_month["minutes"], false ) ?></td>
		</tr>

	</table>

</td>

</tr>
</table>
<?php

html_page_bottom1( __FILE__ );

?>