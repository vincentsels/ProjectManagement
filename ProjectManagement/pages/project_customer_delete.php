<?php
form_security_validate( 'plugin_ProjectManagement_project_customer_delete' );

$f_customer_id = gpc_get_int( 'customer_id', null );
$f_project_id = gpc_get_int( 'project_id', null );

access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

project_ensure_exists( $f_project_id );

$t_project_customer_table = plugin_table( 'project_customer' );
$t_query = "DELETE FROM $t_project_customer_table WHERE project_id = $f_project_id AND customer_id = $f_customer_id";
db_query_bound( $t_query );

form_security_purge( 'plugin_ProjectManagement_project_customer_delete' );

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
