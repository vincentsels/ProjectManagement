<?php

access_ensure_global_level( plugin_config_get( 'view_time_registration_worksheet_threshold' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_pm_reports_menu( 'time_registration_page' );

$t_recently_visited_enabled = last_visited_enabled();
$t_recently_visited = implode(', ', last_visited_get_array());

$t_work_table = plugin_table('work');
$t_bug_table = db_get_table( 'mantis_bug_table' );
$t_project_table = db_get_table( 'mantis_project_table' );
$t_category_table = db_get_table( 'mantis_category_table' );

$t_query_recent = "SELECT b.last_updated, b.handler_id, b.project_id, p.name as project_name, c.id as category_id, c.name as category_name, 
					b.id as bug_id, b.summary as bug_summary, b.status, 
					(SELECT SUM(minutes) FROM $t_work_table w WHERE w.bug_id = b.id AND minutes_type = " . PLUGIN_PM_DONE . ") as done
            FROM $t_bug_table b
       LEFT JOIN $t_project_table p ON b.project_id = p.id
       LEFT JOIN $t_category_table c ON b.category_id = c.id
		   WHERE b.id IN ( $t_recently_visited )
		   ORDER BY b.last_updated DESC
		   LIMIT " . PLUGIN_PM_RECENTLY_VISITED_COUNT;
$t_result_recent = db_query_bound( $t_query_recent );
$t_num_result_recent = db_num_rows( $t_result_recent );

?>

<table width="100%">
<tr class="print">

<td>

<form name="time_registration" method="post" action="<?php echo plugin_page('time_registration_update') ?>" >

<input type="hidden" name="redirect_page" value="time_registration_page" />

<?php 
echo form_security_field( 'plugin_ProjectManagement_time_registration_update' );

foreach ( last_visited_get_array() as $t_bug_id ) {
	printf( "<input type=\"hidden\" name=\"bug_ids[]\" value=\"%d\" />", $t_bug_id ); 
}
?>

<table class="width100" cellspacing="1">
<tr><td colspan="100%" class="form-title"><?php echo plugin_lang_get( 'recently_visited' ) ?><span class="floatright"><input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>"></span></td></tr>
<?php
if ( $t_recently_visited_enabled == OFF ) {
?>
	<tr><td><i><?php plugin_lang_get( 'turn_on_recently_visited' ) ?></i></td></tr>
<?php
} else {
?>
	<tr class="row-category">
		<td><div align="center"><?php echo lang_get( 'last_update' ) ?></div></td>
		<td><div align="center"><?php echo lang_get( 'assigned_to' ) ?></div></td>
		<td><div align="center"><?php echo lang_get( 'project_name' ) ?></div></td>
		<td><div align="center"><?php echo lang_get( 'category' ) ?></div></td>
		<td><div align="center"><?php echo lang_get( 'bug' ) ?></div></td>
		<td><div align="center"><?php echo lang_get( 'summary' ) ?></div></td>
		<td><div align="center"><?php echo plugin_lang_get( 'done' ) ?></div></td>
	</tr>
	<tr class="spacer"/>
<?php

	for ( $i = 0; $i < $t_num_result_recent; $i++ ) {
		$row = db_fetch_array( $t_result_recent );
		
		$t_last_update = date( config_get( 'normal_date_format' ), $row["last_updated"] );
		$t_handler = prepare_user_name( $row['handler_id'] );
		$t_project_name = $row["project_name"];
		$t_category_name = $row["category_name"];
		$t_bug_summary = $row["bug_summary"];
		$t_done_total = minutes_to_time( $row["done"], false );
		
		# choose color based on status
		$status_color = get_status_color( $row["status"] );
		
		?>
		
		<tr bgcolor="<?php echo $status_color ?>">
			<td>
			<?php
				if( $row["last_updated"] > strtotime( '-8 hours' ) ) {
					echo '<b>' . $t_last_update . '</b>';
				} else {
					echo $t_last_update;
				}
			?>
			</td>
			<td><?php echo $t_handler ?></td>
			<td><?php echo $t_project_name?></td>
			<td><?php echo $t_category_name ?></td>
			<td><?php print_bug_link( $row["bug_id"] ) ?></td>
			<td><?php echo $t_bug_summary ?></td>
			<td class="right">
				<?php echo $t_done_total ?>
				+ <input type="text" size="4" maxlength="7" autocomplete="off" 
					name= <?php echo '"add_' . $row["bug_id"] . '_' . PLUGIN_PM_DONE . '"' ?>>
				<select name="work_type_<?php echo $row["bug_id"]?>">
					<?php print_plugin_enum_string_option_list( 'work_types', plugin_config_get( 'default_worktype' ) ) ?>
				</select>
			</td>
		</tr>
		
		<?php
	}

}
?>
</table
>
</form>
</td>

<td>
<table class="width100">
<tr>
<td colspan="100%" class="form-title"><?php echo plugin_lang_get( 'recently_registered' ) ?></td></tr>
<tr><td><i>Coming soon...</i></td></tr>
</table>
</td>

</tr>
</table>
<?php

html_page_bottom1( __FILE__ );

?>