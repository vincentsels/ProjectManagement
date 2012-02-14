<?php
auth_reauthenticate();
access_ensure_global_level( plugin_config_get( 'admin_threshold' ) );

html_page_top1( plugin_lang_get( 'edit_customer' ) );
html_page_top2();

$f_customer_id = gpc_get_int( 'customer_id', null );

$t_name = null;
$t_share = null;
$t_can_approve = null;

if ( !empty( $f_customer_id ) ) {
	$t_customer_table = plugin_table( 'customer' );
	$t_query = "SELECT * FROM $t_customer_table WHERE id = $f_customer_id";
	$t_result = db_query_bound( $t_query );
	$t_customer = db_fetch_array( $t_result );
	if ( is_array( $t_customer ) ) {
		$t_name = $t_customer['name'];
		$t_share = format( $t_customer['share'], 5 );
		$t_can_approve = $t_customer['can_approve'];
	}
}

print_manage_menu();
print_pm_config_menu();
?>

<br/>
<form method="post" action="<?php echo plugin_page( 'customer_update' ) ?>">
	<?php echo form_security_field( 'plugin_ProjectManagement_customer_update' ) ?>

	<input type="hidden" name="customer_id" value="<?php echo $f_customer_id ?>" />;

	<table class="width50" align="center" cellspacing="1">
		<tr>
			<td class="form-title"
				colspan="2"><?php echo plugin_lang_get( 'title' ), ': ', plugin_lang_get( 'edit_customer' ) ?></td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'customer_name' ) ?><br/>
			<td>
				<input type="text" size="64" maxlength="64" name="customer_name" id="customer_name"
					   value="<?php echo $t_name ?>">
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'customer_share' ) ?><br/>
			<td>
				<input type="text" size="6" maxlength="9" name="customer_share" id="customer_share"
					   value="<?php echo $t_share ?>"> %
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'customer_can_approve' ) ?><br/>
			<td><input type="checkbox" name="customer_can_approve"
					   id="customer_can_approve" <?php echo $t_can_approve == 1 ? 'checked="checked" ' : '' ?>>
			</td>
		</tr>
		<tr>
			<td class="center" colspan="100%"><input type="submit" value="<?php echo lang_get( 'update' ) ?>"/></td>
		</tr>
	</table>
</form>

<?php
html_page_bottom();
?>