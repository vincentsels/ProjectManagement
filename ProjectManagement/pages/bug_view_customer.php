<a name="customer_section" id="customer_section"></a>
<?php
collapse_open( 'customer_section' );
?>
<form name="customer_section" method="post" action="<?php echo plugin_page( 'bug_customer_update' ) ?>">
	<?php
	echo form_security_field( 'plugin_ProjectManagement_bug_customer_update' );

	# Rather strange way to pass an array of bug id's with only the selected bug_id in it.
	printf( "<input type=\"hidden\" name=\"bug_ids[]\" value=\"%d\" />", $p_bug_id );
	echo '<input type="hidden" name="bug_customer_supplied" value="1">';
	?>

	<table class="width100" cellspacing="1">
		<tr>
			<td colspan="100%" class="form-title">
				<?php
				collapse_icon( 'customer_section' );
				echo plugin_lang_get( 'customer_section' );
				?>
				<span class="floatright">
				<input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>">
			</span>
			</td>
		</tr>
		<?php
		if ( access_has_bug_level( plugin_config_get( 'enable_customer_payment_threshold' ), $p_bug_id ) ) {
			?>
			<tr class="row-1">
				<td class="category" width="20%">
					<?php echo plugin_lang_get( 'paying_customers' ) ?>
				</td>
				<td>
					<input type="hidden" name="update_paying_cust" value="1">
					<?php print_customer_list( $p_bug_id ); ?>
				</td>
			</tr>
			<?php
		}
		if ( access_has_bug_level( plugin_config_get( 'enable_customer_approval_threshold' ), $p_bug_id ) ) {
			?>
			<tr class="row-2">
				<td class="category" width="20%">
					<?php echo plugin_lang_get( 'approving_customers' ) ?>
				</td>
				<td>
					<input type="hidden" name="update_approving_cust" value="1">
					<?php print_customer_list( $p_bug_id, PLUGIN_PM_CUST_APPROVING, false ); ?>
				</td>
			</tr>
			<?php }
		if ( access_has_bug_level( plugin_config_get( 'enable_customer_payment_threshold' ), $p_bug_id ) ) {
			$t_integration_dev_array = bug_customer_get_selected( $p_bug_id, PLUGIN_PM_CUST_INTEGRATION_DEV );
			$t_integration_dev = array_search( (string)PLUGIN_PM_ALL_CUSTOMERS, $t_integration_dev_array, true );
			?>
			<tr class="row-1">
				<td class="category" width="25%">
					<?php echo plugin_lang_get( 'integration_custom_dev' ) ?>
				</td>
				<td>
					<input type="hidden" name="update_integration_dev" value="1">
					<input type="checkbox"
					<?php
						echo ' name="' . $p_bug_id . '_' . PLUGIN_PM_CUST_INTEGRATION_DEV . '_' . PLUGIN_PM_ALL_CUSTOMERS . '"';
						echo false === $t_integration_dev ? '' : ' checked="checked"';
						echo '>';
						echo plugin_lang_get( 'integration_custom_dev_info' )
					?>
				</td>
			</tr>
			<?php
		}	 ?>
	</table>
	<?php
	collapse_closed( 'customer_section' );
	?>

	<table class="width100" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php
				collapse_icon( 'customer_section' );
				echo plugin_lang_get( 'customer_section' );
				?></td>
		</tr>
	</table>
</form>

<?php
collapse_end( 'customer_section' );
?>