<?php

class ProjectManagementPlugin extends MantisPlugin {

	function register() {
		$this->name        = 'Project Management';
		$this->description = 'Project management plugin that adds advanced functionality for timetracking, estimations, reporting,...';
		$this->page        = 'config_page';

		$this->version  = '1.3.0';
		$this->requires = array(
			'MantisCore' => '1.2.0'
		);

		$this->author  = 'Vincent Sels';
		$this->contact = 'vincent_sels@hotmail.com';
		$this->url     = '';
	}

	function schema() {
		return array(
			array( 'CreateTableSQL', array( plugin_table( 'work' ), "
						id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
						bug_id             I       NOTNULL UNSIGNED,
						user_id            I       NOTNULL UNSIGNED,
						work_type          I2      NOTNULL DEFAULT 50,
						minutes_type       I2      NOTNULL DEFAULT 0,
						minutes            I 	   NOTNULL DEFAULT 0,
						book_date          I,
						timestamp          I
						" ) ),
			array( 'CreateIndexSQL', array( 'idx_plugin_pm_work_bug_id', # used operationally
											plugin_table( 'work' ), 'bug_id' ) ),
			array( 'CreateTableSQL', array( plugin_table( 'resource' ), "
						user_id            I       NOTNULL UNSIGNED PRIMARY,
						hours_per_week	   I	   UNSIGNED,
						hourly_rate        F(3,2)
						" ) ),
			array( 'CreateIndexSQL', array( 'idx_plugin_pm_work_user_id_book_date', # used for reporting
											plugin_table( 'work' ), 'user_id, book_date' ) ),
			array( 'AddColumnSQL', array( plugin_table( 'resource' ), 'color I UNSIGNED' ) ),
			array( 'CreateTableSQL', array( plugin_table( 'customer' ), "
						id			I		NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
						name		C(64)	NOTNULL,
						share		F(3,5)	NOTNULL DEFAULT 0,
						can_approve	L		NOTNULL DEFAULT 1
						" ) ),
			array( 'CreateTableSQL', array( plugin_table( 'bug_customer' ), "
						bug_id		I		NOTNULL UNSIGNED PRIMARY,
						type		I2		NOTNULL PRIMARY,
						customers	C(64)	NOTNULL DEFAULT '0'
						" ) ),
			array( 'CreateTableSQL', array( plugin_table( 'user_customer' ), "
						user_id		I			NOTNULL UNSIGNED PRIMARY,
						customer_id	I			NOTNULL,
						function_description	C(64)
						" ) ),
			array( 'CreateTableSQL', array( plugin_table( 'target' ), "
						id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
						bug_id             I       NOTNULL UNSIGNED,
						owner_id           I       NOTNULL UNSIGNED,
						work_type          I2      NOTNULL DEFAULT 50,
						target_date        I	   NOTNULL UNSIGNED,
						completed_date     I	   UNSIGNED
						" ) ),
			array( 'CreateTableSQL', array( plugin_table( 'resource_unavailable' ), "
						id           	   I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
						user_id            I       NOTNULL UNSIGNED,
						start_date  	   I	   NOTNULL UNSIGNED,
						end_date  	  	   I	   NOTNULL UNSIGNED,
						type			   I2	   NOTNULL UNSIGNED DEFAULT 10,
						include_work	   L       NOTNULL DEFAULT 0,
						note	           C(64)
						" ) ),
			array( 'AddColumnSQL', array( plugin_table( 'resource' ), 'deployability I NOTNULL UNSIGNED DEFAULT 80' ) )
			);
	}

	function config() {
		return array(
			'work_types'								 => '20:analysis,50:development,80:testing',
			'enable_time_registration_threshold'		 => DEVELOPER,
			'view_time_reg_summary_threshold'			=> REPORTER,
			'edit_estimates_threshold'				   => MANAGER,
			'include_bookdate_threshold'				 => DEVELOPER,
			'view_registration_worksheet_threshold'	  => DEVELOPER,
			'view_registration_report_threshold'		 => DEVELOPER,
			'view_resource_management_threshold'		 => MANAGER,
			'view_project_progress_threshold'		 => DEVELOPER,
			'admin_threshold'							=> ADMINISTRATOR,
			'work_type_thresholds'					   => array( 50 => DEVELOPER ),
			'default_worktype'						   => 50,
			'dark_saturation'							=> 33,
			'dark_lightness'							 => 45,
			'light_saturation'						   => 100,
			'light_lightness'							=> 90,
			'display_detailed_bug_link'				  => TRUE,
			'finish_upon_resolving'					  => array( 20, 50 ),
			'finish_upon_closing'						=> array( 80 ),
			'decimal_separator'						  => ',',
			'thousand_separator'						 => '',
			'include_bugs_with_deadline'				 => ON,
			'view_customer_payment_summary_threshold'	=> REPORTER,
			'enable_customer_payment_threshold'		  => DEVELOPER,
			'enable_customer_approval_threshold'		 => UPDATER,
			'view_billing_threshold'					 => MANAGER,
			'default_owner'							  => array( 20 => REPORTER,
																   50 => DEVELOPER,
																   80 => REPORTER ),
			'edit_targets_threshold'					 => DEVELOPER,
			'unavailability_types'	                     => '10:unspecified,20:unavailable,30:out of office,40:appointment,50:private appointment,60:vacation,70:administration,80:service desk,90:other',
			'default_unavailability_type'				 => 10,
			'ignore_work_during_unavailability_periods' => array(60, 80),
			'release_buffer' 							=> 21,
			'view_target_overview_threshold'			=> REPORTER,
			'view_all_targets_threshold'				=> MANAGER
		);
	}

	function events() {
		# Note: I added this event only so that the error message
		# would stop from showing until Mantis adds this event in the
		# change status page, as mentioned in this Mantis ticket:
		# http://www.mantisbt.org/bugs/view.php?id=14329
		return array( 'EVENT_UPDATE_BUG_STATUS_FORM' => EVENT_TYPE_EXECUTE );
	}

	function hooks() {
		return array(
			'EVENT_VIEW_BUG_DETAILS'	  => 'view_bug_pm_summary',
			'EVENT_VIEW_BUG_EXTRA'		=> 'view_bug_time_registration',
			'EVENT_MENU_MAIN'			 => 'main_menu',
			'EVENT_BUG_DELETED'		   => 'delete_time_registration',
			'EVENT_REPORT_BUG'			=> 'bug_set_recently_visited',
			'EVENT_UPDATE_BUG'			=> 'update_bug',
			'EVENT_FILTER_COLUMNS'		=> 'filter_columns',
			'EVENT_LAYOUT_RESOURCES'	  => 'link_files',
			'EVENT_UPDATE_BUG_FORM'	   => 'update_bug_form',
			'EVENT_UPDATE_BUG_STATUS_FORM'	   => 'update_bug_form',
			'EVENT_ACCOUNT_PREF_UPDATE_FORM' => 'view_resource',
			'EVENT_ACCOUNT_PREF_UPDATE' => 'update_resource'
		);
	}

	function init() {
		require_once( 'ProjectManagementAPI.php' );
		require_once( 'date_api.php' );
		require_once( 'pages/html_api.php' );
		require_once( 'classes/MantisPmProject.class.php' );
		require_once( 'classes/MantisPmCategory.class.php' );
		require_once( 'classes/MantisPmBug.class.php' );
		require_once( 'classes/PlottableTask.class.php' );
		require_once( 'classes/PlottableUser.class.php' );
		require_once( 'classes/PlottableProject.class.php' );
		require_once( 'classes/PlottableCategory.class.php' );
		require_once( 'classes/PlottableBug.class.php' );
	}

	function link_files() {
		return '<link rel="stylesheet" type="text/css" href="' . plugin_file( 'style.css' ) . '"/>' . "\n" .
			'<script type="text/javascript" src="' . plugin_file( 'script.js' ) . '"></script>';
	}

	function filter_columns() {
		require_once( 'classes/ProjectManagementDoneColumn.class.php' );
		require_once( 'classes/ProjectManagementEstColumn.class.php' );
		require_once( 'classes/ProjectManagementTodoColumn.class.php' );
		return array(
			'ProjectManagementEstColumn',
			'ProjectManagementDoneColumn',
			'ProjectManagementTodoColumn'
		);
	}

	function main_menu( $p_event, $p_bug_id ) {
		$t_pagename = plugin_lang_get( 'reports' );
		# Only display main menu if at least one of the submenus is accessible
		if ( access_has_global_level( plugin_config_get( 'view_registration_worksheet_threshold' ) ) ) {
			$t_reports_page = plugin_page( 'time_registration_page', false );
		} else if ( access_has_global_level( plugin_config_get( 'view_registration_report_threshold' ) ) ) {
			$t_reports_page = plugin_page( 'time_registration_page', false );
		} else if ( access_has_global_level( plugin_config_get( 'view_resource_management_threshold' ) ) ) {
			$t_reports_page = plugin_page( 'time_registration_page', false );
		} else if ( access_has_global_level( plugin_config_get( 'view_project_progress_threshold' ) ) ) {
			$t_reports_page = plugin_page( 'time_registration_page', false );
		}
		if ( isset( $t_reports_page ) ) {
			return '<a href="' . $t_reports_page . '">' . $t_pagename . '</a>';
		}
	}

	function bug_set_recently_visited( $p_event, $p_bug_data, $p_bug_id ) {
		recently_visited_bug_add( $p_bug_id );
	}

	function view_resource( $p_event, $p_user_id ) {
		?>
		<tr><td class="form-title" colspan="2"><?php echo plugin_lang_get( "unavailability" ) ?></td></tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td  class="category"><?php echo plugin_lang_get( 'unavailability_add_new' ) ?></td>
			<td>
				<?php echo plugin_lang_get( 'unavailability_period' ) ?>:
				<input type="text" size="8" maxlength="10" autocomplete="off" id="period_start" name="period_start">
				<?php
				date_print_calendar( 'period_start_cal' );
				date_finish_calendar( 'period_start', 'period_start_cal' );
				?>-
				<input type="text" size="8" maxlength="10" autocomplete="off" id="period_end" name="period_end">
				<?php
				date_print_calendar( 'period_end_cal' );
				date_finish_calendar( 'period_end', 'period_end_cal' );
				?><br />
				<?php echo plugin_lang_get( 'unavailability_type' ) ?>:
				<select name="unavailability_type">
					<?php print_plugin_enum_string_option_list( 'unavailability_types', plugin_config_get( 'default_unavailability_type' ) ) ?>
				</select><br />
				<?php echo plugin_lang_get( 'unavailability_note' ) ?>:
				<input type="text" size="30" maxlength="64" autocomplete="off" id="unavailability_note" name="unavailability_note">
				<br />
				<input type="submit" name="add_clicked" value="<?php echo plugin_lang_get('unavailability_add_new') ?>">
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td  class="category"><?php echo plugin_lang_get( 'unavailability_overview' ) ?></td>
			<td>
				<select name="unavailability_remove">
					<?php print_resource_unavailability_list( $p_user_id ) ?>
				</select>
				<br />
				<input type="submit" name="remove_clicked" value="<?php echo plugin_lang_get('unavailability_remove') ?>">
			</td>
		</tr>
		<br />
		<?php
		if ( access_has_global_level(  plugin_config_get( 'view_resource_management_threshold' ), null ) ) {
			$t_user_table     = db_get_table( 'mantis_user_table' );
			$t_resource_table = plugin_table( 'resource' );

			$t_query      = "SELECT u.id, u.username, u.realname, u.access_level, r.hours_per_week,
									r.hourly_rate, r.color, r.deployability
                   		       FROM $t_user_table u
        			LEFT OUTER JOIN $t_resource_table r ON u.id = r.user_id
                  			  WHERE u.id = $p_user_id";
			$t_result     = db_query_bound( $t_query );
			$t_row	      = db_fetch_array( $t_result );
			?>
		<tr><td class="form-title" colspan="2"><?php echo plugin_lang_get( "resource_management" ) ?></td></tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td  class="category" width="25%"><?php echo plugin_lang_get( 'hours_per_week' ) ?></td>
			<td><input type="text" size="3" maxlength="2" name="hours_per_week_<?php echo $p_user_id?>"
					   value="<?php echo $t_row['hours_per_week'] ?>"></td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td  class="category"><?php echo plugin_lang_get( 'hourly_rate' ) ?></td>
			<td><input type="text" size="3" maxlength="6" name="hourly_rate_<?php echo $p_user_id?>"
					   value="<?php echo $t_row['hourly_rate'] ?>"></td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td  class="category"><?php echo plugin_lang_get( 'color' ) ?></td>
			<td>
				<select name="color_<?php echo $p_user_id?>">
					<?php print_color_option_list( $t_row['color'] ) ?>
				</select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td  class="category"><?php echo plugin_lang_get( 'deployability' ) ?></td>
			<td><input type="text" size="3" maxlength="3" name="deployability_<?php echo $p_user_id?>"
					   value="<?php echo $t_row['deployability'] ?>"></td>
		</tr>
			<?php
		}
	}

	function update_resource( $p_event, $p_user_id ) {
		# First determine whether the 'remove period' was clicked
		$f_remove_period_clicked = isset( $_POST['remove_clicked'] );
		if ( $f_remove_period_clicked ) {
			$f_id = gpc_get_int( 'unavailability_remove', null );

			$t_table = plugin_table( 'resource_unavailable' );
			$t_query = "DELETE FROM $t_table WHERE id = $f_id";
			db_query_bound( $t_query );
		} else {
			# Either the regular 'update' or the 'add period' button was clicked.
			# Either way, execute the regular logic.
			if ( access_has_global_level(  plugin_config_get( 'view_resource_management_threshold' ), null ) ) {
				$f_hourly_rate    = parse_float( gpc_get_string( 'hourly_rate_' . $p_user_id, null ) );
				$f_hours_per_week = gpc_get_int( 'hours_per_week_' . $p_user_id, null );
				$f_color          = gpc_get_int( 'color_' . $p_user_id, null );
				$f_deployability  = gpc_get_int( 'deployability_' . $p_user_id, null );

				if ( !empty( $f_hourly_rate ) || !empty( $f_hours_per_week ) ||
					!empty( $f_color  ) || !empty( $f_deployability  ) ) {
					resource_insert_or_update( $p_user_id, $f_hourly_rate, $f_hours_per_week,
						$f_color, $f_deployability );
				}
			}

			# Check for new period of unavailability
			$f_unavailable_start = strtotime_safe( gpc_get_string( 'period_start', false ) );
			$f_unavailable_end = strtotime_safe( gpc_get_string( 'period_end', false ) );
			$f_unavailable_type = gpc_get_int( 'unavailability_type', null );
			$f_unavailable_note = gpc_get_string( 'unavailability_note', null );

			if ( $f_unavailable_start ) {
				# A period has been entered
				if ( empty( $f_unavailable_end ) ) {
					# Assume a period of one day
					$f_unavailable_end = $f_unavailable_start;
				} else if ( $f_unavailable_end < $f_unavailable_start ) {
					trigger_error( plugin_lang_get( 'error_enddate_before_startdate' ), E_USER_ERROR );
				}

				# Passed arguments are always timestamps of whole dates, at midnight
				# Periods should start at midnight but end one second before midnight
				$t_day = 60 * 60 * 24;
				$f_unavailable_end = $f_unavailable_end + $t_day - 1;

				# Availability type is required
				if ( is_null( $f_unavailable_type ) ) {
					error_parameters( plugin_lang_get( 'unavailability_type' ) );
					trigger_error( ERROR_EMPTY_FIELD, ERROR );
				}

				$t_include_work = ( is_array( plugin_config_get( 'ignore_work_during_unavailability_periods' ) ) &&
					in_array( $f_unavailable_type, plugin_config_get( 'ignore_work_during_unavailability_periods' ) ) ) ? 0 : 1;

				resource_unavailability_period_add( $p_user_id, $f_unavailable_start, $f_unavailable_end,
					$f_unavailable_type, $t_include_work, $f_unavailable_note );
			}
		}
	}

	/**
	 * Upon resolving or closing a bug, 'finish' certain work types.
	 * Handle updated customer data.
	 */
	function update_bug( $p_event, $p_bug_data, $p_bug_id ) {
		if ( access_has_bug_level( plugin_config_get( 'enable_customer_payment_threshold' ), $p_bug_id ) ||
			access_has_bug_level( plugin_config_get( 'enable_customer_approval_threshold' ), $p_bug_id )
		) {
			# We need to check whether the page that triggered this event
			# supplied customer data. The 'assign to' button doesn't, for instance.
			$t_bug_customer_data_supplied = gpc_get_bool( 'bug_customer_supplied', false );
			if ( $t_bug_customer_data_supplied ) {
				$t_customers = customer_get_all();
				$t_data      = array();
				foreach ( $t_customers as $t_cust ) {
					$t_data[PLUGIN_PM_CUST_PAYING][$t_cust['id']]    =
						gpc_get_bool( $p_bug_id . '_' . PLUGIN_PM_CUST_PAYING . '_' . $t_cust['id'], false );
					$t_data[PLUGIN_PM_CUST_APPROVING][$t_cust['id']] =
						gpc_get_bool( $p_bug_id . '_' . PLUGIN_PM_CUST_APPROVING . '_' . $t_cust['id'], false );
				}
				# Add possible 'all customers'
				$t_data[PLUGIN_PM_CUST_PAYING][PLUGIN_PM_ALL_CUSTOMERS] =
					gpc_get_bool( $p_bug_id . '_' . PLUGIN_PM_CUST_PAYING . '_' . PLUGIN_PM_ALL_CUSTOMERS, false );

				if ( access_has_bug_level( plugin_config_get( 'enable_customer_payment_threshold' ), $p_bug_id ) ) {
					# In case customer payment is enabled, check to see whether at least one paying customer was selected
					# Populate an array with the supplied data
					$t_paying_string = '';
					if ( count( $t_data[PLUGIN_PM_CUST_PAYING] ) > 0 ) {
						foreach ( $t_data[PLUGIN_PM_CUST_PAYING] as $t_cust_id => $t_selected ) {
							if ( $t_selected ) {
								$t_paying_string .= PLUGIN_PM_CUST_CONCATENATION_CHAR . $t_cust_id;
							}
						}
					}
					if ( empty( $t_paying_string ) ) {
						# No customers selected, at least one is required.
						error_parameters( plugin_lang_get( 'paying_customers' ) );
						trigger_error( ERROR_EMPTY_FIELD, ERROR );
					}
					bug_customer_update_or_insert( $p_bug_id, $t_paying_string, PLUGIN_PM_CUST_PAYING );
				}

				if ( access_has_bug_level( plugin_config_get( 'enable_customer_approval_threshold' ), $p_bug_id ) ) {
					$t_approving_string = '';
					if ( count( $t_data[PLUGIN_PM_CUST_APPROVING] ) > 0 ) {
						foreach ( $t_data[PLUGIN_PM_CUST_APPROVING] as $t_cust_id => $t_selected ) {
							if ( $t_selected && $t_customers[$t_cust_id]['can_approve'] == 1 ) {
								$t_approving_string .= PLUGIN_PM_CUST_CONCATENATION_CHAR . $t_cust_id;
							}
						}
					}
					bug_customer_update_or_insert( $p_bug_id, $t_approving_string, PLUGIN_PM_CUST_APPROVING );
				}
			}
		}

		if ( $p_bug_data->status >= config_get( 'bug_resolved_status_threshold' ) ) {
			# The bug was resolved: if there was any work todo left, clear it
			$t_work_types_to_finish_upon_resolving = plugin_config_get( 'finish_upon_resolving' );
			if ( is_array( $t_work_types_to_finish_upon_resolving ) ) {
				foreach ( $t_work_types_to_finish_upon_resolving as $t_work_type ) {
					set_work( $p_bug_id, $t_work_type, PLUGIN_PM_TODO, 0, null, ACTION::INSERT_OR_UPDATE );
				}
			}
		}

		if ( $p_bug_data->status >= config_get( 'bug_closed_status_threshold' ) ) {
			# The bug was closed: if there was any work todo left, clear it
			$t_work_types_to_finish_upon_closing = plugin_config_get( 'finish_upon_closing' );
			if ( is_array( $t_work_types_to_finish_upon_closing ) ) {
				foreach ( $t_work_types_to_finish_upon_closing as $t_work_type ) {
					set_work( $p_bug_id, $t_work_type, PLUGIN_PM_TODO, 0, null, ACTION::INSERT_OR_UPDATE );
				}
			}
		}
	}

	function view_bug_pm_summary( $p_event, $p_bug_id ) {
		recently_visited_bug_add( $p_bug_id );

		# Fetch time registration summary
		if ( access_has_bug_level( plugin_config_get( 'view_time_reg_summary_threshold' ), $p_bug_id ) ) {
			$t_table  = plugin_table( 'work' );
			$t_query  = "SELECT work_type, minutes_type, sum(minutes) as minutes
						   FROM $t_table
						  WHERE bug_id = $p_bug_id
						  GROUP BY work_type, minutes_type
						  ORDER BY work_type, minutes_type";
			$t_result = db_query_bound( $t_query );

			$t_est  = null;
			$t_done = null;

			$t_work = array();
			while ( $t_row = db_fetch_array( $t_result ) ) {
				@$t_work[$t_row["work_type"]][$t_row["minutes_type"]] = $t_row["minutes"];

				if ( $t_row["minutes_type"] == PLUGIN_PM_DONE ) {
					@$t_done += $t_row["minutes"];
				} else if ( $t_row["minutes_type"] == PLUGIN_PM_EST ) {
					@$t_est += $t_row["minutes"];
				}
			}

			$t_todo = get_actual_work_todo( $t_work );

			echo '<tr ' . helper_alternate_class() . '>';
			echo '<td class="category">Est</td><td>' . minutes_to_time( $t_est, true ) . '</td>
			<td class="category">Done</td><td>' . minutes_to_time( $t_done, false ) . '</td>
			<td class="category">Todo</td><td>' . minutes_to_time( $t_todo, true ) . '</td></tr>';
		}

		# Fetch customer payment summary
		if ( access_has_bug_level( plugin_config_get( 'view_time_reg_summary_threshold' ), $p_bug_id ) ) {
			$t_selected_cust        = bug_customer_get_selected( $p_bug_id, PLUGIN_PM_CUST_PAYING );

			$t_selected_cust_string = customer_list_to_string( $t_selected_cust );

			echo '<tr ' . helper_alternate_class() . '>';
			echo '<td class="category">' . plugin_lang_get( 'paying_customers' ) . '</td>';
			echo '<td colspan="100%" >' . $t_selected_cust_string . '</td></tr>';
		}
	}


	function delete_time_registration( $p_event, $p_bug_id ) {
		$t_table = plugin_table( 'work' );
		$t_query = "DELETE FROM $t_table WHERE bug_id = $p_bug_id";
		db_query_bound( $t_query );
	}

	function view_bug_time_registration( $p_event, $p_bug_id ) {
		if ( !access_has_project_level( plugin_config_get( 'enable_time_registration_threshold' ) ) ) {
			return;
		}

		$t_work_table   = plugin_table( 'work' );
		$t_target_table = plugin_table( 'target' );
		$t_est          = PLUGIN_PM_EST;
		$t_done         = PLUGIN_PM_DONE;
		$t_todo         = PLUGIN_PM_TODO;

		# Fetch estimates for all work types
		$t_query_fetch_est  = "SELECT work_type, minutes as est
                                 FROM $t_work_table
                                WHERE bug_id = $p_bug_id
                                 AND minutes_type = $t_est";
		$t_result_fetch_est = db_query_bound( $t_query_fetch_est );

		# Fetch totals total of done of all work types
		$t_query_fetch_done  = "SELECT work_type, SUM(minutes) as done
                                  FROM $t_work_table
                                 WHERE bug_id = $p_bug_id
                                   AND minutes_type = $t_done
                                 GROUP BY work_type";
		$t_result_fetch_done = db_query_bound( $t_query_fetch_done );

		# Fetch todo of all work types
		$t_query_fetch_todo  = "SELECT work_type, minutes as todo
                                  FROM $t_work_table
                                 WHERE bug_id = $p_bug_id
                                   AND minutes_type = $t_todo";
		$t_result_fetch_todo = db_query_bound( $t_query_fetch_todo );

		# Get the different worktypes as an array
		$t_work_types = MantisEnum::getAssocArrayIndexedByValues( plugin_config_get( 'work_types' ) );

		# Remove worktypes which are off limits for this account
		$t_limited_work_types = plugin_config_get( 'work_type_thresholds' );
		foreach ( $t_limited_work_types as $t_work_type => $t_min_access_level ) {
			if ( access_get_global_level() < $t_min_access_level ) {
				unset( $t_work_types[$t_work_type] );
			}
		}

		# Include total
		if ( sizeof( $t_work_types ) > 1 ) {
			$t_work_types[PLUGIN_PM_WORKTYPE_TOTAL] = plugin_lang_get( 'total' );
		}

		# Build a two-dimentional array with the data
		$t_work = array( PLUGIN_PM_EST  => array(),
						 PLUGIN_PM_DONE => array(),
						 PLUGIN_PM_TODO => array() );
		while ( $row = db_fetch_array( $t_result_fetch_est ) ) {
			$t_work[PLUGIN_PM_EST][$row["work_type"]] = $row["est"];
			@$t_work[PLUGIN_PM_EST][PLUGIN_PM_WORKTYPE_TOTAL] += $row["est"];
		}
		while ( $row = db_fetch_array( $t_result_fetch_done ) ) {
			$t_work[PLUGIN_PM_DONE][$row["work_type"]] = $row["done"];
			@$t_work[PLUGIN_PM_DONE][PLUGIN_PM_WORKTYPE_TOTAL] += $row["done"];
		}
		while ( $row = db_fetch_array( $t_result_fetch_todo ) ) {
			$t_work[PLUGIN_PM_TODO][$row["work_type"]] = $row["todo"];
			@$t_work[PLUGIN_PM_TODO][PLUGIN_PM_WORKTYPE_TOTAL] += $row["todo"];
		}

		foreach ( $t_work_types as $t_work_type_code => $t_work_type_label ) {
			# Calculate remaining
			if ( isset( $t_work[PLUGIN_PM_EST][$t_work_type_code] ) ) {
				@$t_work[PLUGIN_PM_REMAINING][$t_work_type_code] =
					$t_work[PLUGIN_PM_EST][$t_work_type_code]
						- $t_work[PLUGIN_PM_DONE][$t_work_type_code];

				# Calculate difference between remaining and todo
				if ( isset( $t_work[PLUGIN_PM_TODO][$t_work_type_code] ) ) {
					@$t_work[PLUGIN_PM_DIFF][$t_work_type_code] =
						$t_work[PLUGIN_PM_EST][$t_work_type_code]
							- $t_work[PLUGIN_PM_DONE][$t_work_type_code]
							- $t_work[PLUGIN_PM_TODO][$t_work_type_code];
				} else {
					# If remaining is negative, this is the difference
					if ( $t_work[PLUGIN_PM_REMAINING][$t_work_type_code] <= 0 ) {
						$t_work[PLUGIN_PM_DIFF][$t_work_type_code]      = $t_work[PLUGIN_PM_REMAINING][$t_work_type_code];
						$t_work[PLUGIN_PM_REMAINING][$t_work_type_code] = 0;
					}

					# If remaining was calculated but no todo was specified,
					# add this to the calculated remaining to the total column
					@$t_work[PLUGIN_PM_TODO][PLUGIN_PM_WORKTYPE_TOTAL] += $t_work[PLUGIN_PM_REMAINING][$t_work_type_code];
				}
			}
		}

		# Fetch target data for all work types
		$t_query_fetch_targets  = "SELECT work_type, owner_id, target_date, completed_date
										  FROM $t_target_table
										 WHERE bug_id = $p_bug_id";
		$t_result_fetch_targets = db_query_bound( $t_query_fetch_targets );

		# Convert to 2-dimentional array
		$t_targets = array();
		while ( $row = db_fetch_array( $t_result_fetch_targets ) ) {
			$t_targets[$row["work_type"]] = $row;
		}

		?>
	<br/>

	<table width="100%" style="padding:0px">
	<tr class="print">

	<td>

	<a name="time_registration" id="time_registration"></a>
		<?php
		collapse_open( 'plugin_pm_time_reg' );
		?>
	<form name="time_registration" method="post" action="<?php echo plugin_page( 'time_registration_update' ) ?>">
		<?php
		echo form_security_field( 'plugin_ProjectManagement_time_registration_update' );

		# Rather strange way to pass an array of bug id's with only the selected bug_id in it.
		printf( "<input type=\"hidden\" name=\"bug_ids[]\" value=\"%d\" />", $p_bug_id );
		?>

		<table class="width100" cellspacing="1">
			<tr>
				<td colspan="100%" class="form-title">
					<?php
					collapse_icon( 'plugin_pm_time_reg' );
					echo plugin_lang_get( 'time_registration' );
					?>
				</td>
			</tr>
			<tr class="row-category">
				<td width="20%" style="min-width:145px">
					<div align="center"><?php echo plugin_lang_get( 'work_type' ) ?></div>
				</td>
				<td style="min-width:130px">
					<div align="center"><?php echo plugin_lang_get( 'est' ) ?></div>
				</td>
				<td width="20%" style="min-width:130px">
					<div align="center"><?php echo plugin_lang_get( 'done' ) ?></div>
				</td>
				<td width="30%" style="min-width:130px">
					<div align="center"><?php echo plugin_lang_get( 'todo' ) ?></div>
				</td>
				<td style="min-width:100px">
					<div align="center"><?php echo plugin_lang_get( 'diff' ) ?></div>
				</td>
			</tr>
			<?php
			foreach ( $t_work_types as $t_work_type_code => $t_work_type_label ) {
				?>
				<tr <?php echo ( $t_work_type_code == PLUGIN_PM_WORKTYPE_TOTAL ?
					'class="row-category-history"' : helper_alternate_class() ) ?>>

					<td class="category"><?php echo $t_work_type_label ?></td>

					<td>
						<?php
						if ( isset( $t_work[PLUGIN_PM_EST][$t_work_type_code] ) ) {
							echo minutes_to_time( $t_work[PLUGIN_PM_EST][$t_work_type_code], true );
						}
						if ( $t_work_type_code != PLUGIN_PM_WORKTYPE_TOTAL &&
							( !isset( $t_work[PLUGIN_PM_EST][$t_work_type_code] ) ||
								access_has_bug_level( plugin_config_get( 'edit_estimates_threshold' ), $p_bug_id ) )
						) {
							# Check whether est was already supplied, or user has rights to alter it regardless
							?>
							-> <input type="text" size="4" maxlength="7" autocomplete="off"
									  name= <?php echo '"change_' . $p_bug_id . '_' . PLUGIN_PM_EST . '_' . $t_work_type_code . '"' ?>>
							<?php
						}
						?>
					</td>

					<td>
						<?php
						if ( isset( $t_work[PLUGIN_PM_DONE][$t_work_type_code] ) ) {
							echo minutes_to_time( $t_work[PLUGIN_PM_DONE][$t_work_type_code], false );
						} else {
							echo minutes_to_time( 0, false );
						}
						if ( $t_work_type_code != PLUGIN_PM_WORKTYPE_TOTAL ) {
							?>
							+ <input type="text" size="4" maxlength="7" autocomplete="off"
									 name= <?php echo '"add_' . $p_bug_id . '_' . PLUGIN_PM_DONE . '_' . $t_work_type_code . '"' ?>>
							<?php
						}
						?>
					</td>

					<?php
					if ( !isset( $t_work[PLUGIN_PM_TODO][$t_work_type_code] ) &&
						isset( $t_work[PLUGIN_PM_REMAINING][$t_work_type_code] )
					) {
						# When todo was not supplied, display calculated remainder instead, in italic
						?>
					<td class="italic"><?php echo minutes_to_time( $t_work[PLUGIN_PM_REMAINING][$t_work_type_code], false ) ?>
						<?php
					} else if ( isset( $t_work[PLUGIN_PM_TODO][$t_work_type_code] ) ) {
						# Todo was supplied, so display that
						?>
					<td><?php echo minutes_to_time( $t_work[PLUGIN_PM_TODO][$t_work_type_code], false ) ?>
						<?php
					} else {
						echo '<td>';
					}
					if ( $t_work_type_code != PLUGIN_PM_WORKTYPE_TOTAL ) {
						?>
						-> <input type="text" size="4" maxlength="7" autocomplete="off"
								  name= <?php echo '"change_' . $p_bug_id . '_' . PLUGIN_PM_TODO . '_' . $t_work_type_code . '"' ?>>
						<?php
						if ( isset( $t_work[PLUGIN_PM_TODO][$t_work_type_code] ) ) {
							?>
							<input type="checkbox"
								   name= <?php echo '"clear_' . $p_bug_id . '_' . PLUGIN_PM_TODO . '_' . $t_work_type_code . '"' ?>> <?php echo plugin_lang_get( 'clear' ) ?>
							<?php
						}
					}
					echo '</td>';
					if ( isset( $t_work[PLUGIN_PM_DIFF][$t_work_type_code] ) ) {
						?>
						<td <?php echo ( $t_work[PLUGIN_PM_DIFF][$t_work_type_code] < 0 ? 'class="negative"' : 'class="positive"' )  ?>>
							<?php echo minutes_to_time( abs( $t_work[PLUGIN_PM_DIFF][$t_work_type_code] ) ) ?></td>
						<?php
					} else {
						echo '<td />';
					} ?>
				</tr>
				<?php
			}
			?>

			<tr>
				<td colspan="100%">
					<input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>">
					<?php
					if ( access_has_bug_level( plugin_config_get( 'include_bookdate_threshold' ), $p_bug_id ) ) {
						echo plugin_lang_get( 'book_date' ) . ': ';
						echo '<input type="text" size="8" maxlength="10" autocomplete="off" id="book_date" name="book_date" value="' .
							date( config_get( 'short_date_format' ) ) . '">';
						date_print_calendar( 'book_date_cal' );
						date_finish_calendar( 'book_date', 'book_date_cal' );
					}
					?>
				</td>
			</tr>
		</table>

		<?php
		collapse_closed( 'plugin_pm_time_reg' );
		?>

		<table class="width100" cellspacing="1">
			<tr>
				<td class="form-title" colspan="2">
					<?php
					collapse_icon( 'plugin_pm_time_reg' );
					echo plugin_lang_get( 'time_registration' );
					?></td>
			</tr>
		</table>
	</form>

		<?php
		collapse_end( 'plugin_pm_time_reg' );

		if ( access_has_bug_level( plugin_config_get( 'enable_customer_payment_threshold' ), $p_bug_id ) ||
			access_has_bug_level( plugin_config_get( 'enable_customer_approval_threshold' ), $p_bug_id )
		) {
			?>

		<br/>
		<a name="customer_section" id="customer_section"></a>
			<?php
			collapse_open( 'customer_section' );
			?>
		<form name="customer_section" method="post" action="<?php echo plugin_page( 'bug_customer_update' ) ?>">
			<?php
			echo form_security_field( 'plugin_ProjectManagement_bug_customer_update' );

			# Rather strange way to pass an array of bug id's with only the selected bug_id in it.
			printf( "<input type=\"hidden\" name=\"bug_ids[]\" value=\"%d\" />", $p_bug_id );
			echo '<input type="hidden" name="bug_customer_supplied" value="1">';
			?>

			<table class="width100" cellspacing="1">
				<tr>
					<td colspan="100%" class="form-title">
						<?php
						collapse_icon( 'customer_section' );
						echo plugin_lang_get( 'customer_section' );
						?>
						<span class="floatright">
						<input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>">
					</span>
					</td>
				</tr>
				<?php
				if ( access_has_bug_level( plugin_config_get( 'enable_customer_payment_threshold' ), $p_bug_id ) ) {
					?>
					<tr class="row-1">
						<td class="category" width="20%">
							<?php echo plugin_lang_get( 'paying_customers' ) ?>
						</td>
						<td>
							<input type="hidden" name="update_paying_cust" value="1">
							<?php print_customer_list( $p_bug_id ); ?>
						</td>
					</tr>
					<?php
				}
				if ( access_has_bug_level( plugin_config_get( 'enable_customer_approval_threshold' ), $p_bug_id ) ) {
					?>
					<tr class="row-2">
						<td class="category" width="20%">
							<?php echo plugin_lang_get( 'approving_customers' ) ?>
						</td>
						<td>
							<input type="hidden" name="update_approving_cust" value="1">
							<?php print_customer_list( $p_bug_id, PLUGIN_PM_CUST_APPROVING, false ); ?>
						</td>
					</tr>
					<?php }
				if ( access_has_bug_level( plugin_config_get( 'enable_customer_payment_threshold' ), $p_bug_id ) ) {
					$t_integration_dev_array = bug_customer_get_selected( $p_bug_id, PLUGIN_PM_CUST_INTEGRATION_DEV );
					$t_integration_dev = array_search( (string)PLUGIN_PM_ALL_CUSTOMERS, $t_integration_dev_array, true );
					?>
					<tr class="row-1">
						<td class="category" width="25%">
							<?php echo plugin_lang_get( 'integration_custom_dev' ) ?>
						</td>
						<td>
							<input type="hidden" name="update_integration_dev" value="1">
							<input type="checkbox"
							<?php
								echo ' name="' . $p_bug_id . '_' . PLUGIN_PM_CUST_INTEGRATION_DEV . '_' . PLUGIN_PM_ALL_CUSTOMERS . '"';
								echo false === $t_integration_dev ? '' : ' checked="checked"';
								echo '>';
								echo plugin_lang_get( 'integration_custom_dev_info' )
							?>
						</td>
					</tr>
					<?php
				}	 ?>
			</table>
			<?php
			collapse_closed( 'customer_section' );
			?>

			<table class="width100" cellspacing="1">
				<tr>
					<td class="form-title" colspan="2">
						<?php
						collapse_icon( 'customer_section' );
						echo plugin_lang_get( 'customer_section' );
						?></td>
				</tr>
			</table>
		</form>

			<?php
			collapse_end( 'customer_section' );
		}
		?>
	</td>

	<td>

		<a name="targets" id="targets"></a>
		<?php
		collapse_open( 'plugin_pm_targets' );
		?>
		<form name="targets" method="post" action="<?php echo plugin_page( 'target_update' ) ?>">
			<?php
			echo form_security_field( 'plugin_pm_targets_update' );

			# Rather strange way to pass an array of bug id's with only the selected bug_id in it.
			printf( "<input type=\"hidden\" name=\"bug_ids[]\" value=\"%d\" />", $p_bug_id );
			?>

			<table class="width100" cellspacing="1">
				<tr>
					<td colspan="100%" class="form-title">
						<?php
						collapse_icon( 'plugin_pm_targets' );
						echo plugin_lang_get( 'targets' );
						?>
					</td>
				</tr>
				<tr class="row-category">
					<td style="min-width:145px">
						<div align="center"><?php echo plugin_lang_get( 'work_type' ) ?></div>
					</td>
					<td style="min-width:115px">
						<div align="center"><?php echo plugin_lang_get( 'target_date' ) ?></div>
					</td>
					<td>
					</td>
					<td>
						<div align="center"><?php echo plugin_lang_get( 'owner' ) ?></div>
					</td>
					<td style="min-width:115px">
						<div align="center"><?php echo plugin_lang_get( 'completed' ) ?></div>
					</td>
				</tr>
				<?php
				foreach ( $t_work_types as $t_work_type_code => $t_work_type_label ) {
					if ( $t_work_type_code == PLUGIN_PM_WORKTYPE_TOTAL ) {
						continue;
					} else if ( array_key_exists( $t_work_type_code, $t_targets ) ) {
						$t_target             = $t_targets[$t_work_type_code];
						$t_target_date        = date( config_get( 'short_date_format' ), $t_target["target_date"] );
						$t_owner_id           = $t_target["owner_id"];
						$t_completed_date     = format_short_date( $t_target["completed_date"] );
						$t_days_overdue       = days_between( $t_target["target_date"], $t_target["completed_date"] ) * -1;
						$t_days_overdue_class = $t_days_overdue < 0 ? 'class="negative"' : 'class="positive"';

						$t_target_date_class = '';
						if ( is_null( $t_completed_date ) ) {
							if ( $t_owner_id == auth_get_current_user_id() ) {
								if ( $t_days_overdue < 0 ) {
									$t_target_date_class = 'class="target-date-overdue"';
								} else if ( $t_days_overdue == 0 ) {
									$t_target_date_class = 'class="target-date-notice"';
								}
							} else if ( $t_days_overdue < 0 ) {
								$t_target_date_class = 'class="target-date-notice"';
							}
						}

						$t_days_overdue = abs( $t_days_overdue );
					} else {
						# target for this worktype has not yet been defined
						$t_target_date        = null;
						$t_target_date_class  = '';
						$t_days_overdue       = null;
						$t_days_overdue_class = '';
						$t_completed_date     = null;
						# Find the default owner for this work type
						$t_default_owners = plugin_config_get( 'default_owner' );
						@$t_owner_type = $t_default_owners[$t_work_type_code];
						if ( $t_owner_type == REPORTER ) {
							$t_reporter_id = bug_get_field( $p_bug_id, 'reporter_id' );
							$t_owner_id    = is_null( $t_reporter_id ) ? -1 : $t_reporter_id;
						} else if ( $t_owner_type == DEVELOPER ) {
							$t_handler_id = bug_get_field( $p_bug_id, 'handler_id' );
							$t_owner_id   = is_null( $t_handler_id ) ? -1 : $t_handler_id;
						} else {
							$t_owner_id = -1;
						}
					}

					$t_can_edit_targets = access_has_global_level( plugin_config_get( 'edit_targets_threshold' ) );
					?>
					<tr <?php echo helper_alternate_class() ?>>

						<td class="category"><?php echo $t_work_type_label ?></td>

						<td <?php echo $t_target_date_class ?>>
							<?php  if ( $t_can_edit_targets || empty( $t_target_date ) ) { ?>
							<input type="text" size="8" maxlength="10" autocomplete="off"
								   id="<?php echo $p_bug_id . '_target_date_' . $t_work_type_code ?>"
								   name="<?php echo $p_bug_id . '_target_date_' . $t_work_type_code ?>"
								   value="<?php echo $t_target_date ?>">
							<?php
							date_print_calendar( 'target_date_cal_' . $t_work_type_code );
							date_finish_calendar( $p_bug_id . '_target_date_' . $t_work_type_code, 'target_date_cal_' . $t_work_type_code );
						} else {
							echo $t_target_date;
							echo '<input type="hidden" name="' . $p_bug_id . '_target_date_' . $t_work_type_code .
								'" value="' . $t_target_date . '"';
						} ?>
						</td>

						<td <?php echo $t_days_overdue_class ?>><?php echo $t_days_overdue ?></td>

						<td>
							<?php  if ( $t_can_edit_targets || empty( $t_target_date ) ) { ?>
							<select name="<?php echo $p_bug_id . '_owner_id_' . $t_work_type_code ?>">
								<option value="-1" selected="selected"></option>
								<?php print_user_option_list( $t_owner_id ) ?>
							</select>
							<?php
						} else {
							echo user_get_name( $t_owner_id );
							echo '<input type="hidden" name="' . $p_bug_id . '_owner_id_' . $t_work_type_code .
								'" value="' . $t_owner_id . '"';
						} ?>
						</td>

						<td>
							<?php  if ( $t_can_edit_targets || empty( $t_completed_date ) ) { ?>
							<input type="text" size="8" maxlength="10" autocomplete="off"
								   id="<?php echo $p_bug_id . '_completed_date_' . $t_work_type_code ?>"
								   name="<?php echo $p_bug_id . '_completed_date_' . $t_work_type_code ?>"
								   value="<?php echo $t_completed_date ?>">
							<?php
							date_print_calendar( 'completed_date_cal_' . $t_work_type_code );
							date_finish_calendar( $p_bug_id . '_completed_date_' . $t_work_type_code, 'completed_date_cal_' . $t_work_type_code );
						} else {
							echo $t_completed_date;
							echo '<input type="hidden" name="' . $p_bug_id . '_completed_date_' . $t_work_type_code .
								'" value="' . $t_completed_date . '"';
						}
							?>
						</td>
					</tr>
					<?php
				}
				?>

				<tr>
					<td colspan="100%">
						<input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>">
					</td>
				</tr>
			</table>

			<?php
			collapse_closed( 'plugin_pm_targets' );
			?>

			<table class="width100" cellspacing="1">
				<tr>
					<td class="form-title" colspan="2">
						<?php
						collapse_icon( 'plugin_pm_targets' );
						echo plugin_lang_get( 'targets' );
						?></td>
				</tr>
			</table>
		</form>

		<?php
		collapse_end( 'plugin_pm_targets' );
		?>

	</td>
	</table>
	<?php
	}

	function update_bug_form( $p_event, $p_bug_id ) {
		if ( access_has_bug_level( plugin_config_get( 'enable_customer_payment_threshold' ), $p_bug_id ) ) {

			echo '<input type="hidden" name="bug_customer_supplied" value="1">';

			?>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php
				echo '<span class="required">*</span>';
				echo plugin_lang_get( 'paying_customers' );
				?>
			</td>
			<td colspan="5">
				<?php print_customer_list( $p_bug_id ); ?>
			</td>
		</tr>
		<?php
		}
		if ( access_has_bug_level( plugin_config_get( 'enable_customer_approval_threshold' ), $p_bug_id ) ) {
			?>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo plugin_lang_get( 'approving_customers' ) ?>
			</td>
			<td colspan="5">
				<?php print_customer_list( $p_bug_id, PLUGIN_PM_CUST_APPROVING, false ); ?>
			</td>
		</tr>
		<?php
		}
	}

}

?>
