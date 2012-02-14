<?php

access_ensure_global_level( plugin_config_get( 'view_resource_management_threshold' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_pm_reports_menu( 'resource_management_page' );

$t_user_table     = db_get_table( 'mantis_user_table' );
$t_resource_table = plugin_table( 'resource' );

$t_query      = "SELECT u.id, u.username, u.realname, u.access_level, r.hours_per_week, r.hourly_rate, r.color
                   FROM $t_user_table u
        LEFT OUTER JOIN $t_resource_table r ON u.id = r.user_id
                  WHERE u.enabled = 1
               ORDER BY access_level DESC, username";
$t_result     = db_query_bound( $t_query );
$t_user_array = array();

$t_access_levels = get_translated_assoc_array_for_enum( 'access_levels' );
?>

<form method="post" action="<?php echo plugin_page( 'resource_management_update' ) ?>">
	<?php echo form_security_field( 'plugin_ProjectManagement_resource_management' ) ?>

	<table class="width100">
		<tr>
			<td class="form-title" colspan="100%"><?php echo plugin_lang_get( "resource_management" )?></td>
		</tr>
		<tr class="row-category">
			<td><?php echo lang_get( 'access_level' ) ?></td>
			<td><?php echo lang_get( 'username' ) ?></td>
			<td><?php echo plugin_lang_get( 'hours_per_week' ) ?></td>
			<td><?php echo plugin_lang_get( 'hourly_rate' ) ?></td>
			<td><?php echo plugin_lang_get( 'color' ) ?></td>
			<td><?php echo plugin_lang_get( 'clear' ) ?></td>
		</tr>

		<?php
		while ( $t_row = db_fetch_array( $t_result ) ) {
			$t_user_array[] = $t_row['id'];
			?>
			<tr <?php echo helper_alternate_class() ?>>
				<td><?php echo $t_access_levels[$t_row['access_level']]  ?></td>
				<td><?php print_user( $t_row['id'] ) ?></td>
				<td>
					<input type="text" size="2" maxlength="2" name="hours_per_week_<?php echo $t_row['id']?>"
						   value="<?php echo $t_row['hours_per_week'] ?>">
				</td>
				<td>
					<input type="text" size="3" maxlength="6" name="hourly_rate_<?php echo $t_row['id']?>"
						   value="<?php echo format( $t_row['hourly_rate'] ) ?>">
				</td>
				<td>
					<select name="color_<?php echo $t_row['id'] ?>">
						<?php print_color_option_list( $t_row['color'] ) ?>
					</select>
				</td>
				<td>
					<input type="checkbox"
						   name="clear_<?php echo $t_row['id']?>"> <?php echo plugin_lang_get( 'clear' ) ?>
				</td>
			</tr>
			<?php
		}
		?>

		<tr>
			<td colspan="100%">
				<input type="hidden" name="users" id="users" value="<?php echo implode( ',', $t_user_array )?>">
				<input type="submit" value="<?php echo lang_get( 'update' ) ?>"/></td>
		</tr>
	</table>
</form>

<?php

html_page_bottom1( __FILE__ );

?>