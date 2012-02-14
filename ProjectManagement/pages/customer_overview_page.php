<?php
auth_reauthenticate();
access_ensure_global_level( plugin_config_get( 'admin_threshold' ) );

html_page_top1( plugin_lang_get( 'configuration' ) );
html_page_top2();

print_manage_menu();
print_pm_config_menu( 'customer_overview_page' );

$t_customer_table = plugin_table( 'customer' );

$t_query = "SELECT * FROM $t_customer_table";
$t_result = db_query_bound( $t_query );
?>

<br/>
<table class="width75" align="center" cellspacing="1">
	<tr>
		<td class="form-title"
			colspan="2"><?php echo plugin_lang_get( 'title' ), ': ', plugin_lang_get( 'customer_management' ) ?></td>
	</tr>
	<tr class="row-category">
		<td><?php echo plugin_lang_get( 'customer_name' ) ?></td>
		<td><?php echo plugin_lang_get( 'customer_share' ) ?></td>
		<td><?php echo plugin_lang_get( 'customer_can_approve' ) ?></td>
		<td><?php echo lang_get( 'actions' ) ?></td>
	</tr>

	<?php
	while ( $row = db_fetch_array( $t_result ) ) {
		$t_customer_id = $row['id'];
		$t_name = $row['name'];
		$t_share = format( $row['share'], 2 );
		$t_can_approve = $row['can_approve'] == 1 ? '<img src="images/ok.gif" width="20" height="15" alt="X" title="X" />' : '&nbsp;';
		?>
		<tr <?php echo helper_alternate_class() ?>>
			<td><?php echo $t_name ?></td>
			<td><?php echo $t_share ?>%</td>
			<td><?php echo $t_can_approve ?></td>
			<td class="center">
				<form method="post" action="<?php echo plugin_page( 'customer_update_page' ) ?>">
					<input type="hidden" name="customer_id" value="<?php echo $t_customer_id ?>" />
					<input type="submit" value="<?php echo lang_get( 'edit_link' ) ?>"/>
				</form>
				<form method="post" action="<?php echo plugin_page( 'customer_delete' ) ?>">
					<?php echo form_security_field( 'plugin_ProjectManagement_customer_delete' ) ?>
					<input type="hidden" name="customer_id" value="<?php echo $t_customer_id ?>" />
					<input type="submit" value="<?php echo lang_get( 'remove_link' ) ?>"/>
				</form>
			</td>
		</tr>
		<?php
	}
	?>

	<tr>
		<form method="post" action="<?php echo plugin_page( 'customer_update_page' ) ?>">
			<td colspan="100%"><input type="submit"
													 value="<?php echo plugin_lang_get( 'add_new_customer' ) ?>"/></td>
		</form>
	</tr>
</table>

<?php
html_page_bottom();
?>