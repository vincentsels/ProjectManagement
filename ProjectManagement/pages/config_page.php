<?php
auth_reauthenticate();
access_ensure_global_level( plugin_config_get( 'admin_threshold' ) );

html_page_top1( plugin_lang_get( 'configuration' ) );
html_page_top2();

print_manage_menu();
print_pm_config_menu( 'config_page' );
?>

<br/>
<form method="post" action="<?php echo plugin_page( 'config_update' ) ?>">
	<?php echo form_security_field( 'plugin_ProjectManagement_config_update' ) ?>
	<table class="width100" align="center" cellspacing="1">
		<tr>
			<td class="form-title"
				colspan="2"><?php echo plugin_lang_get( 'title' ), ': ', plugin_lang_get( 'configuration' ) ?></td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td width="25%" class="category"><?php echo plugin_lang_get( 'work_types' ) ?><br/>
				<span class="small"><?php echo plugin_lang_get( 'work_types_info' ) ?></span></td>
			<td width="75%"><input type="text" size="100" maxlength="200" name="work_types" id="work_types"
								   value="<?php echo plugin_config_get( 'work_types' ) ?>"></td>
		</tr>
		<tr class="spacer"/>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'enable_time_registration_threshold' ) ?><br/>
			<td><select
				name="enable_time_registration_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'enable_time_registration_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'view_time_reg_summary_threshold' ) ?><br/>
			<td><select
				name="view_time_reg_summary_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_time_reg_summary_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'edit_estimates_threshold' ) ?><br/>
				<span class="small"><?php echo plugin_lang_get( 'edit_estimates_threshold_info' ) ?></span></td>
			<td><select
				name="edit_estimates_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'edit_estimates_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'include_bookdate_threshold' ) ?><br/>
				<span class="small"><?php echo plugin_lang_get( 'include_bookdate_threshold_info' ) ?></span></td>
			<td><select
				name="include_bookdate_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'include_bookdate_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'work_type_thresholds' ) ?><br/>
				<span class="small"><?php echo plugin_lang_get( 'work_type_thresholds_info' ) ?></span></td>
			<td><input type="text" size="100" maxlength="200" name="work_type_thresholds" id="work_type_thresholds"
					   value="<?php var_export( plugin_config_get( 'work_type_thresholds' ) ) ?>"></td>
		</tr>
		<tr class="spacer"/>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'finish_upon_resolving' ) ?><br/>
				<span class="small"><?php echo plugin_lang_get( 'finish_upon_resolving_info' ) ?></span></td>
			<td><input type="text" size="100" maxlength="200" name="finish_upon_resolving" id="finish_upon_resolving"
					   value="<?php var_export( plugin_config_get( 'finish_upon_resolving' ) ) ?>"></td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'finish_upon_closing' ) ?><br/>
				<span class="small"><?php echo plugin_lang_get( 'finish_upon_closing_info' ) ?></span></td>
			<td><input type="text" size="100" maxlength="200" name="finish_upon_closing" id="finish_upon_closing"
					   value="<?php var_export( plugin_config_get( 'finish_upon_closing' ) ) ?>"></td>
		</tr>
		<tr class="spacer"/>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'view_time_registration_worksheet_threshold' ) ?></td>
			<td><select
				name="view_registration_worksheet_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_registration_worksheet_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'view_report_registration_threshold' ) ?></td>
			<td><select
				name="view_registration_report_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_registration_report_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'view_resource_management_threshold' ) ?></td>
			<td><select
				name="view_resource_management_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_resource_management_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'view_project_progress_threshold' ) ?></td>
			<td><select
				name="view_project_progress_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_project_progress_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'view_target_overview_threshold' ) ?></td>
			<td><select
				name="view_target_overview_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_target_overview_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'view_all_targets_threshold' ) ?></td>
			<td><select
				name="view_all_targets_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_all_targets_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'admin_threshold' ) ?></td>
			<td><select
				name="admin_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'admin_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr class="spacer"/>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'decimal_separator' ) ?></td>
			<td><input type="text" size="2" maxlength="2" name="decimal_separator" id="decimal_separator"
					   value="<?php echo plugin_config_get( 'decimal_separator' ) ?>"></td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'thousand_separator' ) ?></td>
			<td><input type="text" size="2" maxlength="2" name="thousand_separator" id="thousand_separator"
					   value="<?php echo plugin_config_get( 'thousand_separator' ) ?>"></td>
		</tr>
		<tr class="spacer"/>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'include_bugs_with_deadline' ) ?><br/>
				<span class="small"><?php echo plugin_lang_get( 'include_bugs_with_deadline_warning' ) ?></span></td>
			<td><input type="checkbox" name="include_bugs_with_deadline"
					   id="include_bugs_with_deadline" <?php echo plugin_config_get( 'include_bugs_with_deadline' ) ? 'checked="checked" ' : '' ?>>
				<?php echo plugin_lang_get( 'include_bugs_with_deadline_info' ) ?></span></td>
		</tr>
		<tr class="spacer"/>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'enable_customer_payment_threshold' ) ?><br/>
				<span class="small"><?php echo plugin_lang_get( 'enable_customer_payment_threshold_info' ) ?></span>
			</td>
			<td><select
				name="enable_customer_payment_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'enable_customer_payment_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'enable_customer_approval_threshold' ) ?><br/>
				<span class="small"><?php echo plugin_lang_get( 'enable_customer_approval_threshold_info' ) ?></span>
			</td>
			<td><select
				name="enable_customer_approval_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'enable_customer_approval_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'view_customer_payment_summary_threshold' ) ?>
			</td>
			<td><select
				name="view_customer_payment_summary_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_customer_payment_summary_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'view_billing_threshold' ) ?><br/></td>
			<td><select
				name="view_billing_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_billing_threshold' ) ) ?></select>
			</td>
		</tr>
		<tr class="spacer"/>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'default_owner' ) ?><br/>
				<span class="small"><?php echo plugin_lang_get( 'default_owner_info' ) ?></span></td>
			<td><input type="text" size="100" maxlength="200" name="default_owner" id="default_owner"
					   value="<?php var_export( plugin_config_get( 'default_owner' ) ) ?>"></td>
		</tr>
		<tr class="spacer"/>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'release_buffer' ) ?></td>
			<td><input type="text" size="2" maxlength="2" name="release_buffer" id="release_buffer"
					   value="<?php echo plugin_config_get( 'release_buffer' ) ?>"></td>
		</tr>
        <tr <?php echo helper_alternate_class() ?>>
            <td class="category"><?php echo plugin_lang_get( 'group_by_projects_by_default' ) ?></td>
            <td><input type="checkbox" name="group_by_projects_by_default"
                       id="group_by_projects_by_default"
				<?php echo plugin_config_get( 'group_by_projects_by_default' ) ? 'checked="checked" ' : '' ?>>
			</td>
        </tr>
        <tr <?php echo helper_alternate_class() ?>>
            <td class="category"><?php echo plugin_lang_get( 'show_projects_by_default' ) ?></td>
            <td><input type="checkbox" name="show_projects_by_default"
                       id="show_projects_by_default"
				<?php echo plugin_config_get( 'show_projects_by_default' ) ? 'checked="checked" ' : '' ?>>
            </td>
        </tr>

        <tr <?php echo helper_alternate_class() ?>>
			<?php
			$t_weekly_work_days = plugin_config_get( 'weekly_work_days' );
			?>
            <td class="category"><?php echo plugin_lang_get( 'weekly_work_days' ) ?></td>
            <td>
				<input type="checkbox" name="weekly_work_days_1" id="weekly_work_days_1"
					<?php if ( array_search( 1, $t_weekly_work_days ) !== false ) { echo 'checked="checked"'; } ?>>
				<?php echo plugin_lang_get( 'monday' ) . ' ' ?>
                <input type="checkbox" name="weekly_work_days_2" id="weekly_work_days_2"
					<?php if ( array_search( 2, $t_weekly_work_days ) !== false ) { echo 'checked="checked"'; } ?>>
				<?php echo plugin_lang_get( 'tuesday' ) . ' ' ?>
                <input type="checkbox" name="weekly_work_days_3" id="weekly_work_days_3"
					<?php if ( array_search( 3, $t_weekly_work_days ) !== false ) { echo 'checked="checked"'; } ?>>
				<?php echo plugin_lang_get( 'wednesday' ) . ' ' ?>
                <input type="checkbox" name="weekly_work_days_4" id="weekly_work_days_4"
					<?php if ( array_search( 4, $t_weekly_work_days ) !== false ) { echo 'checked="checked"'; } ?>>
				<?php echo plugin_lang_get( 'thursday' ) . ' ' ?>
                <input type="checkbox" name="weekly_work_days_5" id="weekly_work_days_5"
					<?php if ( array_search( 5, $t_weekly_work_days ) !== false ) { echo 'checked="checked"'; } ?>>
				<?php echo plugin_lang_get( 'friday' ) . ' ' ?>
                <input type="checkbox" name="weekly_work_days_6" id="weekly_work_days_6"
					<?php if ( array_search( 6, $t_weekly_work_days ) !== false ) { echo 'checked="checked"'; } ?>>
				<?php echo plugin_lang_get( 'saturday' ) . ' ' ?>
                <input type="checkbox" name="weekly_work_days_7" id="weekly_work_days_7"
					<?php if ( array_search( 7, $t_weekly_work_days ) !== false ) { echo 'checked="checked"'; } ?>>
				<?php echo plugin_lang_get( 'sunday' ) . ' ' ?>
            </td>
        </tr>


		<tr>
			<td class="center" colspan="2"><input type="submit" value="<?php echo lang_get( 'update' ) ?>"/></td>
		</tr>
	</table>
</form>

<?php
html_page_bottom();
?>