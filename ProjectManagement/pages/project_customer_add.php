<?php
form_security_validate( 'plugin_ProjectManagement_project_customer_add' );

$f_customer_id = gpc_get_int( 'customer_id', null );
$f_project_id = gpc_get_int( 'project_id', null );

access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

project_ensure_exists( $f_project_id );

set_project_customer( $f_project_id, $f_customer_id );

form_security_purge( 'plugin_ProjectManagement_project_customer_add' );

$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;
html_page_top( null, $t_redirect_url );

?>
<br />
<div align="center">
<?php
echo lang_get( 'operation_successful' ).'<br />';
print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php
html_page_bottom();
