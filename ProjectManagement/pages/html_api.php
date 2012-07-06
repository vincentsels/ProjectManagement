<?php

function print_pm_reports_menu( $p_page = '' ) {
	$t_pm_time_registration_page   = plugin_page( 'time_registration_page' );
	$t_pm_report_registration_page = plugin_page( 'report_registration_page' );
	$t_pm_resource_progress_page = plugin_page( 'report_resource_progress_page' );
	$t_pm_project_progress_page = plugin_page( 'report_project_progress_page' );
	$t_pm_resource_management_page = plugin_page( 'resource_management_page' );
	$t_pm_billing_page 			   = plugin_page( 'billing_page' );

	switch ( plugin_page( $p_page ) ) {
		case $t_pm_time_registration_page:
			$t_pm_time_registration_page = '';
			break;
		case $t_pm_report_registration_page:
			$t_pm_report_registration_page = '';
			break;
		case $t_pm_resource_progress_page:
			$t_pm_resource_progress_page = '';
			break;
		case $t_pm_project_progress_page:
			$t_pm_project_progress_page = '';
			break;
		case $t_pm_resource_management_page:
			$t_pm_resource_management_page = '';
			break;
		case $t_pm_billing_page:
			$t_pm_billing_page = '';
			break;
	}

	echo '<div align="center"><p>';
	if ( access_has_global_level( plugin_config_get( 'view_registration_worksheet_threshold' ) ) ) {
		print_bracket_link( $t_pm_time_registration_page, plugin_lang_get( 'time_registration_worksheet' ) );
	}
	if ( access_has_global_level( plugin_config_get( 'view_registration_report_threshold' ) ) ) {
		print_bracket_link( $t_pm_report_registration_page, plugin_lang_get( 'time_registration_overview' ) );
	}
	if ( access_has_global_level( plugin_config_get( 'view_project_progress_threshold' ) ) ) {
		print_bracket_link( $t_pm_resource_progress_page, plugin_lang_get( 'resource_progress' ) );
	}
	if ( access_has_global_level( plugin_config_get( 'view_project_progress_threshold' ) ) ) {
		print_bracket_link( $t_pm_project_progress_page, plugin_lang_get( 'project_progress' ) );
	}
	if ( access_has_global_level( plugin_config_get( 'view_resource_management_threshold' ) ) ) {
		print_bracket_link( $t_pm_resource_management_page, plugin_lang_get( 'resource_management' ) );
	}
	if ( access_has_global_level( plugin_config_get( 'view_billing_threshold' ) ) ) {
		print_bracket_link( $t_pm_billing_page, plugin_lang_get( 'billing' ) );
	}
	echo '</p></div>';
}

function print_pm_config_menu( $p_page = '' ) {
	$t_pm_config_main_page   = plugin_page( 'config_page' );
	$t_pm_config_customer_overview_page = plugin_page( 'customer_overview_page' );

	switch ( plugin_page( $p_page ) ) {
		case $t_pm_config_main_page:
			$t_pm_config_main_page = '';
			break;
		case $t_pm_config_customer_overview_page:
			$t_pm_config_customer_overview_page = '';
			break;
	}

	echo '<br /><div align="center">';
	if ( access_has_global_level( plugin_config_get( 'admin_threshold' ) ) ) {
		print_bracket_link( $t_pm_config_main_page, plugin_lang_get( 'general_configuration' ) );
	}
	if ( access_has_global_level( plugin_config_get( 'admin_threshold' ) ) ) {
		print_bracket_link( $t_pm_config_customer_overview_page, plugin_lang_get( 'customer_management' ) );
	}
	echo '</div>';
}

function print_plugin_enum_string_option_list( $p_enum_name, $p_val = 0 ) {
	$t_config_var_value = plugin_config_get( $p_enum_name );
	$t_enum_values      = MantisEnum::getAssocArrayIndexedByValues( $t_config_var_value );

	foreach ( $t_enum_values as $t_key => $t_value ) {
		echo '<option value="' . $t_key . '"';
		check_selected( $p_val, $t_key );
		echo '>' . $t_value . '</option>';
	}
}

function print_expand_icon_start( $p_div_id ) {
	echo '<a class="subtle" href="#" onclick="ShowOrHide( \'' . $p_div_id . '\' ); return false; ">
	<img id="' . $p_div_id . '_img" border="0" src="images/plus.png" alt="+" />&nbsp;';
}

function print_expand_icon_end() {
	echo '</a>';
}


function print_expandable_div_start( $p_div_id ) {
	echo '<div id="' . $p_div_id . '" class="hidden content-list">';
}

function print_expandable_div_end() {
	echo '</div>';
}

/**
 * Returns the specified color to be used in css elements that accept a color attribute like background-color.
 * It includes a trailing semi-colon. You must still include style="background-color:<...>" or similar.
 * It uses to the specified hue (default green), and in the specified style.
 * @param int $p_hue Hue value between 0 and 360 (http://msdn.microsoft.com/en-us/library/ms531197(v=vs.85).aspx#rgba)
 * @param int $p_style should be PLUGIN_PM_DARK (default) or PLUGIN_PM_LIGHT.
 * @param string $p_additional_style optionally supply additional style to be added after the color.
 */
function print_background_color( $p_hue = 120, $p_style = PLUGIN_PM_DARK ) {
	$t_h = $p_hue;
	$t_s = ( $p_style == PLUGIN_PM_DARK ? plugin_config_get( 'dark_saturation' ) : plugin_config_get( 'light_saturation' ) );
	$t_l = ( $p_style == PLUGIN_PM_DARK ? plugin_config_get( 'dark_lightness' ) : plugin_config_get( 'light_lightness' ) );

	echo "hsl($t_h, $t_s%, $t_l%);";
}

/**
 * Prints a transparent red overlay.
 */
function print_overdue_color() {
	echo "rgba(255, 0, 0, 0.5);";
}

/*
 * Prints the opening span tag for the work bar.
 * Note the closing span must still be printed.
 */
function print_progress_span( $p_handler_id, $p_width ) {
	global $g_resource_colors;
	echo '<span class="progress" style="background-color:';
	print_background_color( $g_resource_colors[$p_handler_id], PLUGIN_PM_LIGHT );
	echo ' border-color: ';
	print_background_color( $g_resource_colors[$p_handler_id], PLUGIN_PM_DARK );
	echo ' width: ' . $p_width . '%">';
}

/*
 * Prints the opening span tag for the progress bar within the work bar.
 * Note the closing span must still be printed.
 */
function print_progressbar_span( $p_handler_id, $p_width ) {
	global $g_resource_colors;
	echo '<span class="bar" style="background-color:';
	print_background_color( $g_resource_colors[$p_handler_id], PLUGIN_PM_DARK );
	echo ' width: ' . $p_width . '%">';
}

/*
 * Prints the opening span tag for the overdue part in a work bar.
 * Note the closing span must still be printed.
 */
function print_overdue_span( $p_width ) {
	echo '<span class="bar overdue" style="background-color:';
	print_overdue_color();
	echo ' width: ' . $p_width . '%">';
}

/**
 * Prints a color selection option list with 15 available colors.
 * @param int $p_val the value to select.
 */
function print_color_option_list( $p_val = 0 ) {
	for ( $i = 0; $i < 360; $i += 20 ) {
		echo '<option style="background-color:"', print_background_color( $i ), '" value="' . $i . '"';
		check_selected( $i, $p_val );
		echo '>' . $i . str_repeat( '&nbsp;', 10 ) . '</option>';
	}
}

function print_customer_list( $p_bug_id = null, $p_type = PLUGIN_PM_CUST_PAYING, $p_include_all = true ) {
	# In case a bug_id and type were supplied, check to see which customers
	# were checked for this type.
	$t_selected_cust = bug_customer_get_selected( $p_bug_id, $p_type );
	$t_customers = customer_get_all( $p_type );

	if ( $p_include_all ) {
		$t_all = array_search( (string)PLUGIN_PM_ALL_CUSTOMERS, $t_selected_cust, true );
		echo '<input type="checkbox" name="' . $p_bug_id . '_' . $p_type . '_' . PLUGIN_PM_ALL_CUSTOMERS . '" ' .
			(false === $t_all ? '' : 'checked="checked"') . ' > ' . init_cap( 'all' ) . ' &nbsp;';
	}
	if ( count( $t_customers ) > 0 ) {
		foreach ( $t_customers as $row ) {
			$t_id = $row['id'];
			$t_name = $row['name'];
			$t_exists = array_search( $t_id, $t_selected_cust );

			echo '<input type="checkbox" name="'. $p_bug_id . '_' . $p_type . '_' . $t_id . '" ' .
				($t_exists ? 'checked="checked"' : '') . ' > ' . $t_name . ' &nbsp;';
		}
	}
}

function print_resource_unavailability_list( $p_user_id ) {
	$t_table  = plugin_table( 'resource_unavailable' );
	$t_query  = "SELECT id, start_date, end_date, type, note
				   FROM $t_table
				  WHERE user_id = $p_user_id";
	$t_result = db_query_bound( $t_query );

	# First print an empty entry to avoid accidental deletion!
	echo '<option value="" selected="selected"></option>';

	$t_config_var_value = plugin_config_get( 'unavailability_types' );
	$t_enum_values      = MantisEnum::getAssocArrayIndexedByValues( $t_config_var_value );
	while ( $t_row = db_fetch_array( $t_result ) ) {
		$t_period_string =
			date( config_get( 'short_date_format' ), $t_row["start_date"] ) . ' - ' .
			date( config_get( 'short_date_format' ), $t_row["end_date"] ) . ': ' .
			$t_enum_values[$t_row["type"]];
		if ( !empty( $t_row['note'] ) ) {
			$t_period_string .= ' (' . $t_row['note'] . ')';
		}
		echo '<option value="' . $t_row['id'] . '">' . $t_period_string . '</option>';
	}
}

?>