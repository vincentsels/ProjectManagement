<?php

function print_pm_reports_menu( $p_page = '' ) {
	$t_pm_time_dashboard_page      = plugin_page( 'report_dashboard_page' );
	$t_pm_time_registration_page   = plugin_page( 'time_registration_page' );
	$t_pm_target_overview_page     = plugin_page( 'target_overview_page' );
	$t_pm_report_registration_page = plugin_page( 'report_registration_page' );
	$t_pm_resource_progress_page   = plugin_page( 'report_resource_progress_page' );
	$t_pm_project_progress_page    = plugin_page( 'report_project_progress_page' );
	$t_pm_resource_management_page = plugin_page( 'resource_management_page' );
	$t_pm_billing_page             = plugin_page( 'billing_page' );

	switch ( plugin_page( $p_page ) ) {
		case $t_pm_time_dashboard_page:
			$t_pm_time_dashboard_page = '';
			break;
		case $t_pm_time_registration_page:
			$t_pm_time_registration_page = '';
			break;
		case $t_pm_target_overview_page:
			$t_pm_target_overview_page = '';
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
	if ( access_has_global_level( plugin_config_get( 'view_registration_report_threshold' ) ) ) {
		print_bracket_link( $t_pm_time_dashboard_page, plugin_lang_get( 'time_dashboard' ) );
	}
	if ( access_has_global_level( plugin_config_get( 'view_registration_worksheet_threshold' ) ) ) {
		print_bracket_link( $t_pm_time_registration_page, plugin_lang_get( 'time_registration_worksheet' ) );
	}
	if ( access_has_global_level( plugin_config_get( 'view_target_overview_threshold' ) ) ) {
		print_bracket_link( $t_pm_target_overview_page, plugin_lang_get( 'target_overview' ) );
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
		echo '>' . plugin_get_enum_element ( $p_enum_name, $t_key ) . '</option>';
	}
}

function print_plugin_enum_string_selected_value( $p_enum_name, $p_val = 0 ) {
	$t_config_var_value = plugin_config_get( $p_enum_name );
	$t_enum_values      = MantisEnum::getAssocArrayIndexedByValues( $t_config_var_value );

	foreach ( $t_enum_values as $t_key => $t_value ) {
		if ( $p_val == $t_key ) 
			return plugin_get_enum_element ( $p_enum_name, $t_key );
	}
	
	return null;
}

function print_plugin_minutes_type_string( $p_val = "0" ) {
	if ( $p_val == PLUGIN_PM_EST ) {
		return plugin_lang_get( 'est' );
	}
	else if ( $p_val == PLUGIN_PM_DONE ) {
		return plugin_lang_get( 'done' );
	}
	else if ( $p_val == PLUGIN_PM_TODO ) {
		return plugin_lang_get( 'todo' );
	}
	
	return $p_val;
}

/***
 * Prints the start of a region that is used as header of a collapsable section.
 * @param $p_div_id string The unique name for this div.
 * @param $p_visible bool Whether or not the div should be shown immediately. Default false.
 * Note that when setting this to true, also the 'print_expandable_div_start' method must use 'true' as this parameter.
 */
function print_expand_icon_start( $p_div_id, $p_visible = false ) {
	echo '<a class="subtle" href="#" onclick="ShowOrHide( \'' . $p_div_id . '\' ); return false; ">
		<img id="' . $p_div_id . '_img" border="0" ' .
		($p_visible ? 'src="images/minus.png" alt="-"' : 'src="images/plus.png" alt="+"') . ' />&nbsp;';
}

/***
 * Prints the end of the region used as a header of a collapsable section.
 */
function print_expand_icon_end() {
	echo '</a>';
}

/***
 * Prints the start of an expandable section.
 * @param $p_div_id string The unique name of this div.
 * @param bool $p_visible Whether or not this div should be shown immediately. Default false.
 */
function print_expandable_div_start( $p_div_id, $p_visible = false ) {
	echo '<div id="' . $p_div_id . '" class="' . ($p_visible ? '' : 'hidden') . ' content-list">';
}

/***
 * Print the end of an expandable section.
 */
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
	echo "rgba(255, 0, 0, 0.3);";
}

/**
 * Prints a transparent grey overlay.
 */
function print_na_color() {
	echo "rgba(20, 20, 20, 0.3);";
}

/*
 * Prints the opening span tag for the work bar.
 * Note the closing span must still be printed.
 */
function print_progress_span( $p_handler_id, $p_width, $p_overdue = false ) {
	global $g_resources;
	
	$t_color = 120;
	if ( $g_resources != null 
			&& count ( $g_resources ) > $p_handler_id 
			&& $g_resources[$p_handler_id] != null 
			&& $g_resources[$p_handler_id]['color'] != null ) {
		
		$t_color = $g_resources[$p_handler_id]['color'];
	}
	
	echo '<span class="progress" style="background-color:';
	print_background_color( $t_color, PLUGIN_PM_LIGHT );
	echo ' border-color: ';
	if ( $p_overdue ) {
		echo "rgb(255, 0, 0);";
	} else {
		print_background_color( $t_color, PLUGIN_PM_DARK );
	}
	echo ' width: ' . $p_width . '%">';
}

/*
 * Prints the opening span tag for the progress bar within the work bar.
 * Note the closing span must still be printed.
 */
function print_progressbar_span( $p_handler_id, $p_width ) {
	global $g_resources;

	$t_color = 120;
	if ( $g_resources != null 
			&& count ( $g_resources ) > $p_handler_id 
			&& $g_resources[$p_handler_id] != null 
			&& $g_resources[$p_handler_id]['color'] != null ) {
		
		$t_color = $g_resources[$p_handler_id]['color'];
	}

	echo '<span class="bar" style="background-color:';
	print_background_color( $t_color, PLUGIN_PM_DARK );
	echo ' width: ' . $p_width . '%">';
}

/*
 * Prints the opening span tag for the overdue part in a work bar.
 * Note the closing span must still be printed.
 */
function print_overdue_span( $p_width ) {
	echo '<span class="bar bar-overdue" style="background-color:';
	print_overdue_color();
	echo ' width: ' . $p_width . '%">';
}

/*
 * Prints the opening span tag for the unavailability part in a work bar.
 * Note the closing span must still be printed.
 */
function print_na_span( $p_width ) {
	echo '<span class="bar bar-na" style="background-color:';
	print_na_color();
	echo ' width: ' . $p_width . '%">';
}

/**
 * Prints a color selection option list with 15 available colors.
 * @param int $p_val the value to select.
 */
function print_color_option_list( $p_val = 0 ) {
	for ( $i = 0; $i < 360; $i += 20 ) {
		echo '<option style="background-color:', print_background_color( $i ), '; color:', print_background_color( $i, PLUGIN_PM_LIGHT ), '" value="' . $i . '"';
		check_selected( $i, $p_val );
		echo '>' . $i . str_repeat( '&nbsp;', 10 ) . '</option>';
	}
}

function print_customer_list( $p_bug_id = null, $p_type = PLUGIN_PM_CUST_PAYING, $p_include_all = true ) {
	# In case a bug_id and type were supplied, check to see which customers
	# were checked for this type.
	$t_selected_cust = bug_customer_get_selected( $p_bug_id, $p_type );
	
	if ( $p_bug_id == null ) {
		$t_customers = customer_get_all( $p_type );
	}
	else {
		$t_customers = customer_get_by_bug ( $p_bug_id, $p_type );
	}

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
	$t_enum_values      = plugin_lang_get_enum ( 'unavailability_types' ); //MantisEnum::getAssocArrayIndexedByValues( $t_config_var_value );
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

function print_resource_unavailability_table( $p_user_id, $p_include_future = true, $p_include_past = false ) {
	if ( $p_include_future === false && $p_include_past === false ) {
		return;
	}

	$t_collapse_unavailability = 'plugin_pm_unavailability';
	$t_title = plugin_lang_get( 'unavailability_period_title' );
	
	$t_table  = plugin_table( 'resource_unavailable' );
	$t_query  = "SELECT id, start_date, end_date, type, note
				   FROM $t_table
				  WHERE user_id = $p_user_id";
	
	if ( $p_include_future === true && $p_include_past === false ) {
		$t_collapse_unavailability = 'plugin_pm_unavailability_future';
		$t_title = plugin_lang_get( 'unavailability_period_title' );
		$t_time = time();
		$t_query .= " AND start_date > $t_time";
		$t_query .= " ORDER BY start_date ASC";
	}
	else if ( $p_include_future === false && $p_include_past === true ) {
		# Shows unavailability until 1 year old max
		$t_collapse_unavailability = 'plugin_pm_unavailability_past';
		$t_title = plugin_lang_get( 'unavailability_past_period_title' );
		$t_time_to = time();
		$t_time_from = strtotime( '-1 year' , $t_time_to );
		$t_query .= " AND start_date >= $t_time_from AND start_date <= $t_time_to";
		$t_query .= " ORDER BY start_date DESC";
	}
	
	$t_result      = db_query_bound( $t_query );
	$t_enum_values = plugin_lang_get_enum ( 'unavailability_types' );

	collapse_open( $t_collapse_unavailability );
	echo '<table class="width75" cellspacing="1">';
	echo '<tr>';
	echo '<td colspan="100%" class="form-title">';
	collapse_icon( $t_collapse_unavailability );
	echo $t_title;
	echo '</td>';
	echo '</tr>';
	echo '<tr class="row-category">';
	echo '<td>Inicio</td>';
	echo '<td>Fin</td>';
	echo '<td>Tipo</td>';
	echo '<td>Nota</td>';
	if ( $p_include_future === true ) { echo '<td></td>'; }
	echo '</tr>';
		
	while ( $t_row = db_fetch_array( $t_result ) ) {
		echo '<tr ' . helper_alternate_class() .'>';
		echo '<td>' . date( config_get( 'normal_date_format' ), $t_row["start_date"] ) . '</td>';
		echo '<td>' . date( config_get( 'normal_date_format' ), $t_row["end_date"] ) . '</td>';
		echo '<td>' . $t_enum_values[$t_row["type"]] . '</td>';
		echo '<td>' . (!empty( $t_row['note'] ) ? $t_row['note'] : '') . '</td>';
		if ( $p_include_future === true ) { 
			if ( $t_row["start_date"] > time() ) {
				# 'remove_clicked' is sent to be compatible with previous version of deleting unavailability
				# Should be removed in a short time
				echo '<td class="center">';
				echo '<a href="' . plugin_page('account_unavailability_delete') . '&remove_clicked=1&unavailability_remove=' . $t_row["id"] . form_security_param( 'plugin_ProjectManagement_account_unavailability' ) . '">' . plugin_lang_get( 'delete_time_registration' ) . '</a>';
				echo '</td>';
			}
			else {
				echo '<td></td>';
			}
		}
		echo '</tr>';
	}
	echo '</table>';
	
	collapse_closed( $t_collapse_unavailability );
	
	echo '<table class="width75" cellspacing="1">';
	echo '<tr>';
	echo '<td colspan="100%" class="form-title">';
	collapse_icon( $t_collapse_unavailability );
	echo $t_title;
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	
	collapse_end( $t_collapse_unavailability );
}

function print_association_mode_option_list( $p_val = PLUGIN_PM_ASSOCIATION_DEFAULT ) {
	//$t_config_var_name = $p_enum_name . '_enum_string';
	
	echo '<select id="association_mode" name="association_mode">';
	
	$t_key  = PLUGIN_PM_ASSOCIATION_ALL;
	$t_value = plugin_lang_get( 'association_mode_all' );
	echo '<option value="' . $t_key . '"';
	check_selected( $p_val, $t_key );
	echo '>' . $t_value . '</option>';

	$t_key  = PLUGIN_PM_ASSOCIATION_AUTO;
	$t_value = plugin_lang_get( 'association_mode_auto' );
	echo '<option value="' . $t_key . '"';
	check_selected( $p_val, $t_key );
	echo '>' . $t_value . '</option>';

	$t_key  = PLUGIN_PM_ASSOCIATION_MANUAL;
	$t_value = plugin_lang_get( 'association_mode_manual' );
	echo '<option value="' . $t_key . '"';
	check_selected( $p_val, $t_key );
	echo '>' . $t_value . '</option>';
	
	echo '</select>';
}

function get_association_mode_string( $p_val = null ) {
	if ( is_null ($p_val) ) {
		return '';
	}
	
	if ( $p_val == PLUGIN_PM_ASSOCIATION_ALL ) {
		return plugin_lang_get( 'association_mode_all' );
	}
	if ( $p_val == PLUGIN_PM_ASSOCIATION_AUTO ) {
		return plugin_lang_get( 'association_mode_auto' );
	}
	if ( $p_val == PLUGIN_PM_ASSOCIATION_MANUAL ) {
		return plugin_lang_get( 'association_mode_manual' );
	}
}

function print_is_billable_check( $p_bug_id ) {
	if ( $p_bug_id == null ) {
		return;
	}
	
	$t_pm_bug = pm_bug_get( $p_bug_id );
	echo '<input type="checkbox" id="is_billable_' . $p_bug_id . '" name="is_billable_' . $p_bug_id . '" ' . ( $t_pm_bug["is_billable"] == 1 ? 'checked="checked"' : '' ) . '>';
}

# print the full status option list
function print_status_option_list_all( $p_select_label, $p_current_value = 0, $p_allow_close = false, $p_project_id = ALL_PROJECTS ) {
	$t_current_auth = access_get_project_level( $p_project_id );

	$t_enum_list = get_status_option_list_all( $t_current_auth, $p_current_value, true, $p_allow_close, $p_project_id );

	if( count( $t_enum_list ) > 1 ) {

		# resort the list into ascending order
		ksort( $t_enum_list );
		reset( $t_enum_list );
		echo '<select ', helper_get_tab_index(), ' name="' . $p_select_label . '">';
		foreach( $t_enum_list as $key => $val ) {
			echo '<option value="' . $key . '"';
			check_selected( $key, $p_current_value );
			echo '>' . $val . '</option>';
		}
		echo '</select>';
	} else if ( count( $t_enum_list ) == 1 ) {
		echo array_pop( $t_enum_list );
	} else {
		echo MantisEnum::getLabel( lang_get( 'status_enum_string' ), $p_current_value );
	}
}

function print_billable_behavior( $p_select_label, $p_select_value = PLUGIN_PM_BILLABLE_BEHAVIOR_OPTIONAL_UNSELECTED ) {
	echo '<select ', helper_get_tab_index(), ' name="' . $p_select_label . '">';
	
	echo '<option value="' .PLUGIN_PM_BILLABLE_BEHAVIOR_OPTIONAL_UNSELECTED . '"';
	check_selected( PLUGIN_PM_BILLABLE_BEHAVIOR_OPTIONAL_UNSELECTED, $p_select_value );
	echo '>' . plugin_lang_get( 'billable_behavior_over_severity_optional_unselected' ) . '</option>';
	
	echo '<option value="' .PLUGIN_PM_BILLABLE_BEHAVIOR_OPTIONAL_SELECTED . '"';
	check_selected( PLUGIN_PM_BILLABLE_BEHAVIOR_OPTIONAL_SELECTED, $p_select_value );
	echo '>' . plugin_lang_get( 'billable_behavior_over_severity_optional_selected' ) . '</option>';
	
	echo '<option value="' .PLUGIN_PM_BILLABLE_BEHAVIOR_ALWAYS_REQUIRED . '"';
	check_selected( PLUGIN_PM_BILLABLE_BEHAVIOR_ALWAYS_REQUIRED, $p_select_value );
	echo '>' . plugin_lang_get( 'billable_behavior_over_severity_always_required' ) . '</option>';
	
	echo '<option value="' .PLUGIN_PM_BILLABLE_BEHAVIOR_NEVER_REQUIRED . '"';
	check_selected( PLUGIN_PM_BILLABLE_BEHAVIOR_NEVER_REQUIRED, $p_select_value );
	echo '>' . plugin_lang_get( 'billable_behavior_over_severity_never_required' ) . '</option>';
	
	echo '<option value="' .PLUGIN_PM_BILLABLE_BEHAVIOR_NEVER_BILLABLE . '"';
	check_selected( PLUGIN_PM_BILLABLE_BEHAVIOR_NEVER_BILLABLE, $p_select_value );
	echo '>' . plugin_lang_get( 'billable_behavior_over_severity_never_billable' ) . '</option>';
	
	echo '</select>';
}