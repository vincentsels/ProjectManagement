<?php

access_ensure_global_level( plugin_config_get( 'view_time_registration_worksheet' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_pm_reports_menu( 'time_registration_page' );

?>
<br />Coming soon...<br />
<?php

html_page_bottom1( __FILE__ );

?>