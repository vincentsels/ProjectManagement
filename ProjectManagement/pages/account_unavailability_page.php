<?php

#============ Parameters ============
# (none)

#============ Permissions ============
auth_ensure_user_authenticated();

current_user_ensure_unprotected();

include( 'account_unavailability_inc.php' );

html_page_top( plugin_lang_get( 'unavailability' ) );

edit_unavailability_prefs();

html_page_bottom();
 