<?php
   auth_reauthenticate();
   access_ensure_global_level( plugin_config_get( 'admin_threshold' ) );
   html_page_top( plugin_lang_get( 'configuration' ) );
?>

<br />
<form method="post" action="<?php echo plugin_page( 'config_update' ) ?>">
   <?php echo form_security_field( 'plugin_ProjectManagement_config_update' ) ?>
   <table class="width100" align="center" cellspacing="1">
      <tr>
         <td class="form-title" colspan="2"><?php echo plugin_lang_get( 'title' ), ': ', plugin_lang_get( 'configuration' ) ?></td>
      </tr>
      <tr <?php echo helper_alternate_class() ?>>
         <td width="25%" class="category"><?php echo plugin_lang_get( 'work_types' ) ?><br />
         <span class="small"><?php echo plugin_lang_get( 'work_types_info' ) ?></span></td>
         <td width="75%" ><input type="text" size="100" maxlength="200" name="work_types" id="work_types" value="<?php echo plugin_config_get( 'work_types' ) ?>"></td>
      </tr>
      <tr <?php echo helper_alternate_class() ?>>
         <td class="category"><?php echo plugin_lang_get( 'enable_time_registration_threshold' ) ?><br />
         <td><select name="enable_time_registration_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'enable_time_registration_threshold' ) ) ?></select></td>
      </tr>
      <tr <?php echo helper_alternate_class() ?>>
         <td class="category"><?php echo plugin_lang_get( 'view_time_reg_summary_threshold' ) ?><br />
         <td><select name="view_time_reg_summary_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_time_reg_summary_threshold' ) ) ?></select></td>
      </tr>
      <tr <?php echo helper_alternate_class() ?>>
         <td class="category"><?php echo plugin_lang_get( 'edit_estimates_threshold' ) ?><br />
         <span class="small"><?php echo plugin_lang_get( 'edit_estimates_threshold_info' ) ?></span></td>
         <td><select name="edit_estimates_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'edit_estimates_threshold' ) ) ?></select></td>
      </tr>
      <tr <?php echo helper_alternate_class() ?>>
         <td class="category"><?php echo plugin_lang_get( 'include_bookdate_threshold' ) ?><br />
         <span class="small"><?php echo plugin_lang_get( 'include_bookdate_threshold_info' ) ?></span></td>
         <td><select name="include_bookdate_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'include_bookdate_threshold' ) ) ?></select></td>
      </tr>
      <tr <?php echo helper_alternate_class() ?>>
         <td class="category"><?php echo plugin_lang_get( 'work_type_thresholds' ) ?><br />
         <span class="small"><?php echo plugin_lang_get( 'work_type_thresholds_info' ) ?></span></td>
         <td><input type="text" size="100" maxlength="200" name="work_type_thresholds" id="work_type_thresholds" value="<?php var_export( plugin_config_get( 'work_type_thresholds' ) ) ?>"></td>
      </tr>
      <tr <?php echo helper_alternate_class() ?>>
         <td class="category"><?php echo plugin_lang_get( 'finish_upon_resolving' ) ?><br />
         <span class="small"><?php echo plugin_lang_get( 'finish_upon_resolving_info' ) ?></span></td>
         <td><input type="text" size="100" maxlength="200" name="finish_upon_resolving" id="finish_upon_resolving" value="<?php var_export( plugin_config_get( 'finish_upon_resolving' ) ) ?>"></td>
      </tr>
      <tr <?php echo helper_alternate_class() ?>>
         <td class="category"><?php echo plugin_lang_get( 'finish_upon_closing' ) ?><br />
         <span class="small"><?php echo plugin_lang_get( 'finish_upon_closing_info' ) ?></span></td>
         <td><input type="text" size="100" maxlength="200" name="finish_upon_closing" id="finish_upon_closing" value="<?php var_export( plugin_config_get( 'finish_upon_closing' ) ) ?>"></td>
      </tr>
      <tr <?php echo helper_alternate_class() ?>>
         <td class="category"><?php echo plugin_lang_get( 'view_time_registration_worksheet_threshold' ) ?></td>
         <td><select name="view_registration_worksheet_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_registration_worksheet_threshold' ) ) ?></select></td>
      </tr>
      <tr <?php echo helper_alternate_class() ?>>
         <td class="category"><?php echo plugin_lang_get( 'view_report_registration_threshold' ) ?></td>
         <td><select name="view_registration_report_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_registration_report_threshold' ) ) ?></select></td>
      </tr>
      <tr <?php echo helper_alternate_class() ?>>
         <td class="category"><?php echo plugin_lang_get( 'view_resource_management_threshold' ) ?></td>
         <td><select name="view_resource_management_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_resource_management_threshold' ) ) ?></select></td>
      </tr>
      <tr <?php echo helper_alternate_class() ?>>
         <td class="category"><?php echo plugin_lang_get( 'view_resource_allocation_threshold' ) ?></td>
         <td><select name="view_resource_allocation_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_resource_allocation_threshold' ) ) ?></select></td>
      </tr>
      <tr <?php echo helper_alternate_class() ?>>
         <td class="category"><?php echo plugin_lang_get( 'admin_threshold' ) ?></td>
         <td><select name="admin_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'admin_threshold' ) ) ?></select></td>
      </tr>
      <tr>
         <td class="center" colspan="2"><input type="submit" value="<?php echo lang_get( 'update' ) ?>"/></td>
      </tr>
   </table>
</form>

<?php
   html_page_bottom();
?>