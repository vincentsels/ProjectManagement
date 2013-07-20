<?php

//require_once( 'user_pref_api.php' );

function edit_unavailability_prefs($p_user_id = null, $p_error_if_protected = true, $p_accounts_menu = true, $p_redirect_url = '') {
	if ( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	$t_redirect_url = $p_redirect_url;
	if ( is_blank( $t_redirect_url ) ) {
		$t_redirect_url = 'account_unavailability_page.php';
	}

	# protected account check
	if ( user_is_protected( $p_user_id ) ) {
		if ( $p_error_if_protected ) {
			trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
		} else {
			return;
		}
	}

	# prefix data with u_
	//$t_pref = user_pref_get( $p_user_id );

	# Account Preferences Form BEGIN
?>
<br />
<div align="center">
<form method="post" action="<?php echo plugin_page( 'account_unavailability_update' ) ?>">
<?php echo form_security_field( 'plugin_ProjectManagement_account_unavailability' ) ?>
<input type="hidden" name="user_id" value="<?php echo $p_user_id ?>" />
<input type="hidden" name="redirect_url" value="<?php echo $t_redirect_url ?>" />
<table class="width75" cellspacing="1">
	<tr>
		<td class="form-title">
			<?php echo plugin_lang_get( 'unavailability_account_title' ) ?>
		</td>
		<td colspan="2" class="right">
			<?php
				if ( $p_accounts_menu ) {
					print_account_menu( plugin_page( 'account_unavailability_page', false ) );
				}
			?>
		</td>
	</tr>

	<tr <?php echo helper_alternate_class() ?>>
		<td class="category"><?php echo plugin_lang_get( 'unavailability_add_new_title' ) ?></td>
		<td>
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td>
						<?php echo plugin_lang_get( 'unavailability_period' ) ?>:
					</td>
					<td>
						<input type="text" size="8" maxlength="10" autocomplete="off" id="period_start" name="period_start">
						<?php
						date_print_calendar( 'period_start_cal' );
						date_finish_calendar( 'period_start', 'period_start_cal' );
						?>-
						<input type="text" size="8" maxlength="10" autocomplete="off" id="period_end" name="period_end">
						<?php
						date_print_calendar( 'period_end_cal' );
						date_finish_calendar( 'period_end', 'period_end_cal' );
						?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo plugin_lang_get( 'unavailability_type' ) ?>:
					</td>
					<td>
						<select name="unavailability_type">
							<?php print_plugin_enum_string_option_list( 'unavailability_types', plugin_config_get( 'default_unavailability_type' ) ) ?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo plugin_lang_get( 'unavailability_note' ) ?>:
					</td>
					<td>
						<input type="text" size="64" maxlength="64" autocomplete="off" id="unavailability_note" name="unavailability_note">
					</td>
				</tr>
			</table>
		</td>
		<td class="center">
			<input type="submit" name="add_clicked" value="<?php echo plugin_lang_get('unavailability_add_new') ?>">
		</td>
	</tr>
</table>

<br/>
<?php print_resource_unavailability_table ( $p_user_id, true, false ) ?>

<br/>
<?php print_resource_unavailability_table ( $p_user_id, false, true ) ?>


</form>
</div>
<?php
} # end of edit_unavailability_prefs()
