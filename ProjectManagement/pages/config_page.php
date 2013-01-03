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
	<table class="width75" align="center" cellspacing="1">
		<tr>
			<td class="form-title"
				colspan="2"><?php echo plugin_lang_get( 'time_registration' ) ?></td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td width="30%" class="category"><?php echo plugin_lang_get( 'work_types' ) ?><br/>
				<span class="small"><?php echo plugin_lang_get( 'work_types_info' ) ?></span></td>
			<td><input type="text" class="large-textbox" maxlength="200" name="work_types" id="work_types"
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
			<td><input type="text" class="large-textbox" maxlength="200" name="work_type_thresholds" id="work_type_thresholds"
					   value="<?php var_export( plugin_config_get( 'work_type_thresholds' ) ) ?>"></td>
		</tr>
		<tr class="spacer"/>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'finish_upon_resolving' ) ?><br/>
				<span class="small"><?php echo plugin_lang_get( 'finish_upon_resolving_info' ) ?></span></td>
			<td><input type="text" class="large-textbox" maxlength="200" name="finish_upon_resolving" id="finish_upon_resolving"
					   value="<?php var_export( plugin_config_get( 'finish_upon_resolving' ) ) ?>"></td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'finish_upon_closing' ) ?><br/>
				<span class="small"><?php echo plugin_lang_get( 'finish_upon_closing_info' ) ?></span></td>
			<td><input type="text" class="large-textbox" maxlength="200" name="finish_upon_closing" id="finish_upon_closing"
					   value="<?php var_export( plugin_config_get( 'finish_upon_closing' ) ) ?>"></td>
		</tr>
    </table><br />

    <table class="width75" align="center" cellspacing="1">
        <tr>
            <td class="form-title"
                colspan="2"><?php echo lang_get( 'access_levels' ) ?></td>
        </tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td width="30%" class="category"><?php echo plugin_lang_get( 'view_time_registration_worksheet_threshold' ) ?></td>
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
    </table><br />

    <table class="width75" align="center" cellspacing="1">
        <tr>
            <td class="form-title"
                colspan="2"><?php echo plugin_lang_get( 'localization' ) ?></td>
        </tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td width="30%" class="category"><?php echo plugin_lang_get( 'decimal_separator' ) ?></td>
			<td><input type="text" class="small-textbox" maxlength="2" name="decimal_separator" id="decimal_separator"
					   value="<?php echo plugin_config_get( 'decimal_separator' ) ?>"></td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category"><?php echo plugin_lang_get( 'thousand_separator' ) ?></td>
			<td><input type="text" class="small-textbox" maxlength="2" name="thousand_separator" id="thousand_separator"
					   value="<?php echo plugin_config_get( 'thousand_separator' ) ?>"></td>
		</tr>
    </table><br />

    <table class="width75" align="center" cellspacing="1">
        <tr>
            <td class="form-title"
                colspan="2"><?php echo plugin_lang_get( 'customer_section' ) ?></td>
        </tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td width="30%" class="category"><?php echo plugin_lang_get( 'enable_customer_payment_threshold' ) ?><br/>
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
    </table><br />

    <table class="width75" align="center" cellspacing="1">
        <tr>
            <td class="form-title"
                colspan="2"><?php echo plugin_lang_get( 'targets' ) ?></td>
        </tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td width="30%" class="category"><?php echo plugin_lang_get( 'default_owner' ) ?><br/>
				<span class="small"><?php echo plugin_lang_get( 'default_owner_info' ) ?></span></td>
			<td><input type="text" class="large-textbox" maxlength="200" name="default_owner" id="default_owner"
					   value="<?php var_export( plugin_config_get( 'default_owner' ) ) ?>"></td>
		</tr>
    </table><br />

    <table class="width75" align="center" cellspacing="1">
        <tr>
            <td class="form-title"
                colspan="2"><?php echo plugin_lang_get( 'project_progress' ), ' & ', plugin_lang_get( 'resource_progress' ) ?></td>
        </tr>
        <tr <?php echo helper_alternate_class() ?>>
            <td width="30%" class="category"><?php echo plugin_lang_get( 'include_bugs_with_deadline' ) ?><br/>
                <span class="small"><?php echo plugin_lang_get( 'include_bugs_with_deadline_warning' ) ?></span></td>
            <td><input type="checkbox" name="include_bugs_with_deadline"
                       id="include_bugs_with_deadline" <?php echo plugin_config_get( 'include_bugs_with_deadline' ) ? 'checked="checked" ' : '' ?>>
                <?php echo plugin_lang_get( 'include_bugs_with_deadline_info' ) ?></span></td>
        </tr>
        <tr class="spacer"/>
		<tr <?php echo helper_alternate_class() ?>>
			<td width="30%" class="category"><?php echo plugin_lang_get( 'release_buffer' ) ?></td>
			<td><input type="text" class="small-textbox" maxlength="2" name="release_buffer" id="release_buffer"
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
			$t_work_hours_per_day = plugin_config_get( 'work_hours_per_day' );
			?>
            <td class="category"><?php echo plugin_lang_get( 'work_hours_per_day' ) ?></td>
            <td>
				<?php echo plugin_lang_get( 'monday' ) . ':' ?>
				<input type="text" class="small-textbox" maxlength="2" name="work_hours_per_day_1" id="work_hours_per_day_1"
					   value="<?php echo @$t_work_hours_per_day[1] ?>">
				<?php echo ' &nbsp; ' . plugin_lang_get( 'tuesday' ) . ':' ?>
                <input type="text" class="small-textbox"  maxlength="2" name="work_hours_per_day_2" id="work_hours_per_day_2"
                       value="<?php echo @$t_work_hours_per_day[2] ?>">
				<?php echo ' &nbsp; ' . plugin_lang_get( 'wednesday' ) . ':' ?>
                <input type="text" class="small-textbox" maxlength="2" name="work_hours_per_day_3" id="work_hours_per_day_3"
                       value="<?php echo @$t_work_hours_per_day[3] ?>">
				<?php echo ' &nbsp; ' . plugin_lang_get( 'thursday' ) . ':' ?>
                <input type="text" class="small-textbox" maxlength="2" name="work_hours_per_day_4" id="work_hours_per_day_4"
                       value="<?php echo @$t_work_hours_per_day[4] ?>">
				<?php echo ' &nbsp; ' . plugin_lang_get( 'friday' ) . ':' ?>
                <input type="text" class="small-textbox" maxlength="2" name="work_hours_per_day_5" id="work_hours_per_day_5"
                       value="<?php echo @$t_work_hours_per_day[5] ?>">
				<?php echo ' &nbsp; ' . plugin_lang_get( 'saturday' ) . ':' ?>
                <input type="text" class="small-textbox" maxlength="2" name="work_hours_per_day_6" id="work_hours_per_day_6"
                       value="<?php echo @$t_work_hours_per_day[6] ?>">
				<?php echo ' &nbsp; ' . plugin_lang_get( 'sunday' ) . ':' ?>
                <input type="text" class="small-textbox" maxlength="2" name="work_hours_per_day_7" id="work_hours_per_day_7"
                       value="<?php echo @$t_work_hours_per_day[7] ?>">
            </td>
        </tr>
        <tr class="spacer"/>
        <tr <?php echo helper_alternate_class() ?>>
            <td class="category"><?php echo plugin_lang_get( 'unavailability_types' ) ?></td>
            <td><input type="text" class="large-textbox" maxlength="200" name="unavailability_types" id="unavailability_types"
                                   value="<?php echo plugin_config_get( 'unavailability_types' ) ?>"></td>
        </tr>

		<tr>
			<td class="center" colspan="2"><input type="submit" value="<?php echo lang_get( 'update' ) ?>"/></td>
		</tr>
	</table><br />
</form>

<?php
html_page_bottom();
?>