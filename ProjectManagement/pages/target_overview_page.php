<?php
access_ensure_global_level( plugin_config_get( 'view_target_overview_threshold' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_pm_reports_menu( 'target_overview_page' );

$t_current_user = auth_get_current_user_id();
$f_user_id      = gpc_get_int( 'user_id', $t_current_user );

$t_project_select_clause = get_project_select_clause();

$t_target_table   = plugin_table( 'target' );
$t_user_table      = db_get_table( 'mantis_user_table' );
$t_bug_table      = db_get_table( 'mantis_bug_table' );
$t_project_table  = db_get_table( 'mantis_project_table' );
$t_category_table = db_get_table( 'mantis_category_table' );
$t_all_users      = ALL_USERS;

$t_query      = "SELECT u.id as user_id, p.name as project_name, c.name as category_name,
						b.id as bug_id, b.summary, t.work_type, t.target_date
					FROM $t_target_table t
					JOIN $t_user_table u ON t.owner_id = u.id
			        JOIN $t_bug_table b ON t.bug_id = b.id
			        JOIN $t_project_table p ON b.project_id = p.id
			        JOIN $t_category_table c ON b.category_id = c.id
				   WHERE (t.owner_id = $f_user_id OR $f_user_id = $t_all_users)
					 AND t.completed_date IS NULL
					 AND $t_project_select_clause
				   ORDER BY u.id, t.target_date";
$t_result = db_query_bound( $t_query );

if ( access_has_global_level( plugin_config_get( 'view_all_targets_threshold' ) ) ) {
	# Allow to view any user's targets
	?>
<div class="center">
	<table class="width100">
		<tr>
			<td class="center">
				<form name="target_overview" method="post"
					  action="<?php echo plugin_page( 'target_overview_page' ) ?>">
					<?php
					echo plugin_lang_get( 'owner' ), ': ';
					?>
					<select name="user_id">
						<option value="<?php echo ALL_USERS ?>" selected="selected"><?php echo lang_get('all_users') ?></option>
						<?php print_user_option_list( $f_user_id ) ?>
					</select>
					<input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>">
				</form>
			</td>
		</tr>
	</table>
</div>
<?php
}
?>
<br />
<table class="width100" cellspacing="1">
	<tr>
		<td colspan="100%" class="form-title">
			<?php echo plugin_lang_get( 'target_overview' ); ?>
		</td>
	</tr>
	<tr class="row-category">
        <?php
        if ( $t_current_user != $f_user_id ) {
            ?>
            <td>
                <div align="center"><?php echo plugin_lang_get( 'owner' ) ?></div>
            </td>
            <?php
        }
        ?>
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
			<div align="center"><?php echo plugin_lang_get( 'target_date' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo plugin_lang_get( 'overdue' ) ?></div>
		</td>
	</tr>

<?php
	while ( $row = db_fetch_array( $t_result ) ) {
		$t_project_name   = $row["project_name"];
		$t_category_name  = $row["category_name"];
		$t_bug_id    	  = string_get_bug_view_link( $row['bug_id'], null, false );
		$t_bug_summary    = $row["summary"];
		$t_work_type	  = MantisEnum::getLabel( plugin_config_get( "work_types" ), $row["work_type"] );
		$t_target_date    = date( config_get( 'short_date_format' ), $row["target_date"] );

		$t_days_overdue       = days_between( $row["target_date"] ) * -1;
		$t_days_overdue_class = $t_days_overdue < 0 ? 'class="negative"' : 'class="positive"';

		$t_target_date_class = '';
		if ( $f_user_id == auth_get_current_user_id() ) {
			if ( $t_days_overdue < 0 ) {
				$t_target_date_class = 'class="target-date-overdue"';
			} else if ( $t_days_overdue == 0 ) {
				$t_target_date_class = 'class="target-date-notice"';
			}
		} else if ( $t_days_overdue < 0 ) {
			$t_target_date_class = 'class="target-date-notice"';
		}

		$t_days_overdue = abs( $t_days_overdue );

		echo "<tr " . helper_alternate_class() . ">";

        if ( $t_current_user != $f_user_id ) {
            $t_user_name = user_get_name( $row["user_id"] );
            echo "<td>$t_user_name</td>";
        }

		echo "<td>$t_project_name</td>";
		echo "<td>$t_category_name</td>";
		echo "<td>$t_bug_id</td>";
		echo "<td>$t_bug_summary</td>";
		echo "<td>$t_work_type</td>";
		echo "<td $t_target_date_class >$t_target_date</td>";
		echo "<td $t_days_overdue_class >$t_days_overdue</td>";
		echo "</tr>";
	}
?>

</table>