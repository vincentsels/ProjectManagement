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
         <td class="category"><?php echo plugin_lang_get( 'view_reports_threshold' ) ?><br />
         <span class="small"><?php echo plugin_lang_get( 'view_reports_threshold_info' ) ?></span></td>
         <td><select name="view_reports_threshold"><?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_reports_threshold' ) ) ?></select></td>
      </tr>
      <tr <?php echo helper_alternate_class() ?>>
         <td class="category"><?php echo plugin_lang_get( 'work_type_thresholds' ) ?><br />
         <span class="small"><?php echo plugin_lang_get( 'work_type_thresholds_info' ) ?></span></td>
         <td><input type="text" size="100" maxlength="200" name="work_type_thresholds" id="work_type_thresholds" value="<?php var_export( plugin_config_get( 'work_type_thresholds' ) ) ?>"></td>
      </tr>
      <tr>
         <td class="center" colspan="2"><input type="submit" value="<?php echo lang_get( 'update' ) ?>"/></td>
      </tr>
   </table>
</form>

<?php
   html_page_bottom(); 
?>