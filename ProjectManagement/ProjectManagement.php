<?php

class ProjectManagementPlugin extends MantisPlugin {

	function register() {
		$this->name		= 'Project Management';
		$this->description = 'Project management plugin that adds advanced functionality for timetracking, estimations, reporting,...';
		$this->page		= 'config_page';

		$this->version  = '1.5.0'; // TO REVIEW: Changed from 1.4.2
		$this->requires = array(
			'MantisCore' => '1.2.0',
			'ArrayExportExcel' => '0.3'
		);

		$this->author  = 'Vincent Sels';
		$this->contact = 'vincent@selsitsolutions.com';
		$this->url	 = '';
	}

	function schema() {
		return array(
			array( 'CreateTableSQL', array( plugin_table( 'work' ), "
						id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
						bug_id             I       NOTNULL UNSIGNED,
						user_id            I       NOTNULL UNSIGNED,
						work_type          I2      NOTNULL DEFAULT 50,
						minutes_type       I2      NOTNULL DEFAULT 0,
						minutes            I       NOTNULL DEFAULT 0,
						book_date          I,
						timestamp          I
						" ) ),
			array( 'CreateIndexSQL', array( 'idx_plugin_pm_work_bug_id', # used operationally
											plugin_table( 'work' ), 'bug_id' ) ),
			array( 'CreateTableSQL', array( plugin_table( 'resource' ), "
						user_id            I       NOTNULL UNSIGNED PRIMARY,
						hours_per_week     I       UNSIGNED,
						hourly_rate        F(3,2)
						" ) ),
			array( 'CreateIndexSQL', array( 'idx_plugin_pm_work_user_id_book_date', # used for reporting
											plugin_table( 'work' ), 'user_id, book_date' ) ),
			array( 'AddColumnSQL', array( plugin_table( 'resource' ), 'color I UNSIGNED' ) ),
			array( 'CreateTableSQL', array( plugin_table( 'customer' ), "
						id                I        NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
						name              C(64)    NOTNULL,
						share             F(3,5)   NOTNULL DEFAULT 0,
						can_approve       L        NOTNULL DEFAULT 1
						" ) ),
			array( 'CreateTableSQL', array( plugin_table( 'bug' ), "
						bug_id            I        NOTNULL UNSIGNED PRIMARY,
						is_billable       L        NOTNULL DEFAULT 1
						" ) ),
			array( 'CreateTableSQL', array( plugin_table( 'bug_customer' ), "
						bug_id            I        NOTNULL UNSIGNED PRIMARY,
						type              I2       NOTNULL PRIMARY,
						customers         C(64)    NOTNULL DEFAULT '0'
						" ) ),
			array( 'CreateTableSQL', array( plugin_table( 'user_customer' ), "
						user_id           I        NOTNULL UNSIGNED PRIMARY,
						customer_id       I        NOTNULL,
						function_description    C(64)
						" ) ),
			array( 'CreateTableSQL', array( plugin_table( 'project_customer' ), "
						project_id        I        NOTNULL UNSIGNED PRIMARY,
						customer_id       I        NOTNULL PRIMARY
						" ) ),
			array( 'CreateTableSQL', array( plugin_table( 'target' ), "
						id                I        NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
						bug_id            I        NOTNULL UNSIGNED,
						owner_id          I        NOTNULL UNSIGNED,
						work_type         I2       NOTNULL DEFAULT 50,
						target_date       I        NOTNULL UNSIGNED,
						completed_date    I        UNSIGNED
						" ) ),
			array( 'CreateTableSQL', array( plugin_table( 'resource_unavailable' ), "
						id                I        NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
						user_id           I        NOTNULL UNSIGNED,
						start_date        I        NOTNULL UNSIGNED,
						end_date          I        NOTNULL UNSIGNED,
						type              I2       NOTNULL UNSIGNED DEFAULT 10,
						include_work      L        NOTNULL DEFAULT 0,
						note              C(64)
						" ) ),
			array( 'AddColumnSQL', array( plugin_table( 'resource' ),  'deployability    I        NOTNULL UNSIGNED DEFAULT 80' ) ),
			array( 'AddColumnSQL', array( plugin_table( 'work' ),      'info             C(255)   DEFAULT NULL' ) ),
			array( 'AddColumnSQL', array( plugin_table( 'customer' ),  'association_mode I2       NOTNULL DEFAULT 1' ) ),
			array( 'UpdateFunction', '', array() ) # required to trigger a new schema version
			);
	}

	function config() {
		return array(
			'work_types'								=> '20:analysis,50:development,80:testing',
			'enable_time_registration_threshold'		=> DEVELOPER,
			'view_time_reg_summary_threshold'			=> REPORTER,
			'edit_estimates_threshold'					=> MANAGER,
			'include_bookdate_threshold'				=> DEVELOPER,
			'view_registration_worksheet_threshold'		=> DEVELOPER,
			'view_registration_report_threshold'		=> DEVELOPER,
			'view_resource_management_threshold'		=> MANAGER,
			'view_project_progress_threshold'			=> DEVELOPER,
			'admin_threshold'							=> ADMINISTRATOR,
			'admin_own_threshold'						=> DEVELOPER,
			'work_type_thresholds'						=> array( 50 => DEVELOPER ),
			'default_worktype'							=> 50,
			'dark_saturation'							=> 33,
			'dark_lightness'							=> 45,
			'light_saturation'							=> 100,
			'light_lightness'							=> 90,
			'display_detailed_bug_link'					=> TRUE,
			'finish_upon_resolving'						=> array( 20, 50 ),
			'finish_upon_closing'						=> array( 80 ),
			'work_types_for_customer'					=> array( 20 ),
			'bug_view_mode'								=> 1,
			'decimal_separator'							=> ',',
			'thousand_separator'						=> '',
			'include_bugs_with_deadline'				=> ON,
			'view_customer_payment_summary_threshold'	=> REPORTER,
			'enable_customer_payment_threshold'		 	=> DEVELOPER,
			'enable_customer_approval_threshold'		=> UPDATER,
			'view_billing_threshold'					=> MANAGER,
			'edit_targets_threshold'					=> DEVELOPER,
			'billable_behavior_over_severity'           => array( 10 => 1, 20 => 1, 30 => 1, 40 => 1, 50 => 1, 60 => 1, 70 => 1, 80 => 1),
			'billable_mandatory_minimun_status'         => RESOLVED,
			'unavailability_types'						=> '10:unspecified,20:unavailable,30:out of office,40:appointment,50:private appointment,60:vacation,70:administration,80:service desk,90:other',
			'default_unavailability_type'				=> 10,
			'unavailability_ignore_work'				=> array(60, 80),
			'release_buffer' 							=> 21,
			'view_target_overview_threshold'			=> REPORTER,
			'view_all_targets_threshold'				=> MANAGER,
			'group_by_projects_by_default'				=> TRUE,
			'show_projects_by_default'					=> TRUE,
			'show_all_work_types_on_bug_targets'		=> TRUE,
			'work_hours_per_day'						=> array( 1 => 8, 2 => 8, 3 => 8, 4 => 8, 5 => 7, 6 => 0, 7 => 0 ),
			'fields_to_include_in_overviews'			=> array(),
			'currency_symbol'                           => '&euro;',
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
			'EVENT_VIEW_BUG_DETAILS'            => 'view_bug_pm_summary',
			'EVENT_VIEW_BUG_EXTRA'              => 'view_bug_time_registration',
			'EVENT_MENU_ISSUE'                  => 'timerecord_menu',
			'EVENT_MENU_MAIN'                   => 'main_menu',
			'EVENT_BUG_DELETED'                 => 'delete_time_registration',
			'EVENT_REPORT_BUG'                  => 'bug_set_recently_visited',
			'EVENT_UPDATE_BUG'                  => 'update_bug',
			'EVENT_FILTER_COLUMNS'              => 'filter_columns',
			'EVENT_LAYOUT_RESOURCES'            => 'link_files',
			'EVENT_UPDATE_BUG_FORM'             => 'update_bug_form',
			'EVENT_UPDATE_BUG_STATUS_FORM'      => 'update_bug_form',
			'EVENT_MENU_ACCOUNT'                => 'account_menu',
			'EVENT_ACCOUNT_PREF_UPDATE_FORM'    => 'view_resource',
			'EVENT_ACCOUNT_PREF_UPDATE'         => 'update_resource',
			'EVENT_MANAGE_PROJECT_PAGE'         => 'manage_project_page',
			'EVENT_MANAGE_PROJECT_CREATE'       => 'manage_project_create',
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
		require_once( 'classes/ProjectManagementCustomersColumn.class.php' );
		return array(
			'ProjectManagementEstColumn',
			'ProjectManagementDoneColumn',
			'ProjectManagementTodoColumn',
			'ProjectManagementCustomersColumn'
		);
	}

	function upgrade( $p_schema ) {
		if ( $p_schema == 11 ) {
			# In v1.4.1   option 'default_owner' no longer exists and is replaced by option
			# 'work_types_for_customer'. Migration required.

			$default_owners = plugin_config_get( 'default_owner' );
			$work_types_for_reporter_and_under = array();
			foreach ( $default_owners as $work_type => $owner ) {
				if ( $owner <= REPORTER ) {
					$work_types_for_reporter_and_under[] = $work_type;
				}
			}
			plugin_config_set( 'work_types_for_customer', $work_types_for_reporter_and_under );
			plugin_config_delete( 'default_owner' );
		}
		
		// TODO:
		// array( 'CreateTableSQL', array( plugin_table( 'project_customer' ), "
		// 				project_id        I        NOTNULL UNSIGNED PRIMARY,
		// 				customer_id       I        NOTNULL PRIMARY
		// 				" ) ),
		//array( 'CreateTableSQL', array( plugin_table( 'bug' ), "
		//				bug_id            I        NOTNULL UNSIGNED PRIMARY,
		//				is_billable       L        NOTNULL DEFAULT 1
		//				" ) ),
		// array( 'AddColumnSQL', array( plugin_table( 'work' ),      'info             C(255)   DEFAULT NULL' ) ),
		// array( 'AddColumnSQL', array( plugin_table( 'customer' ),  'association_mode I2       NOTNULL DEFAULT 1' ) ),
		// UPDATE plugin_table( 'customer' ) SET association_mode = 0;
		

		return true;
	}
	
	function timerecord_menu() {
		$t_bugid =  gpc_get_int( 'id' );
		if( access_has_project_level( plugin_config_get( 'enable_time_registration_threshold' ) ) ) {
			$import_page = 'view.php?';
			$import_page .= 'id=';
			$import_page .= $t_bugid ;
			$import_page .= '#time_registration';

			return array( plugin_lang_get( 'timerecord_menu' ) => $import_page);
		}
		else {
			return array ();
		}
	}
	
	function account_menu ( $p_event ) {
		$t_account_unavailability_page = plugin_page( 'account_unavailability_page', false );
		if ( $t_account_unavailability_page == $_SERVER['REQUEST_URI'] ) {
			$t_account_unavailability_page = '';
		}
		print_bracket_link( $t_account_unavailability_page, plugin_lang_get( 'unavailability' ) );
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
		# Check if bug brands new to pre-process billable state
		if ( basename($_SERVER['PHP_SELF']) != 'bug_report_page.php' ) {
			$t_is_billable = is_billable( $p_bug_data->severity );
			
			pm_bug_update_or_insert( $p_bug_id, $t_is_billable );
		}
		
		recently_visited_bug_add( $p_bug_id );
	}

	function view_resource( $p_event, $p_user_id ) {
		if ( basename($_SERVER['PHP_SELF']) != 'account_prefs_page.php' ) {
		?>
		<tr><td class="form-title" colspan="2"><?php echo plugin_lang_get( "unavailability" ) ?></td></tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td  class="category"><?php echo plugin_lang_get( 'unavailability_add_new' ) ?></td>
			<td>
				<table cellpadding="0" cellspacing="0" border="0" style="width: 100%">
					<tr>
						<td>
							<?php echo plugin_lang_get( 'unavailability_period' ) ?>:
						</td>
						<td>
							<input type="text" size="8" maxlength="10" autocomplete="off" id="period_start" name="period_start">
							<?php
							date_print_calendar( 'period_start_cal' );
							date_finish_calendar( 'period_start', 'period_start_cal' );
							?>-
							<input type="text" size="8" maxlength="10" autocomplete="off" id="period_end" name="period_end">
							<?php
							date_print_calendar( 'period_end_cal' );
							date_finish_calendar( 'period_end', 'period_end_cal' );
							?>
						</td>
						<td rowspan="3" class="right">
							<input type="submit" name="add_clicked" value="<?php echo plugin_lang_get('unavailability_add_new') ?>">
						</td>
					</tr>
					<tr>
						<td>
							<?php echo plugin_lang_get( 'unavailability_type' ) ?>:
						</td>
						<td>
							<select name="unavailability_type">
								<?php print_plugin_enum_string_option_list( 'unavailability_types', plugin_config_get( 'default_unavailability_type' ) ) ?>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo plugin_lang_get( 'unavailability_note' ) ?>:
						</td>
						<td>
							<input type="text" size="64" maxlength="64" autocomplete="off" id="unavailability_note" name="unavailability_note">
						</td>
					</tr>
				</table>
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
		}
		if ( access_has_global_level(  plugin_config_get( 'view_resource_management_threshold' ), null ) ) {
			$t_user_table	 = db_get_table( 'mantis_user_table' );
			$t_resource_table = plugin_table( 'resource' );

			$t_query	  = "SELECT u.id, u.username, u.realname, u.access_level, r.hours_per_week,
									r.hourly_rate, r.color, r.deployability
									FROM $t_user_table u
					LEFT OUTER JOIN $t_resource_table r ON u.id = r.user_id
				  			  WHERE u.id = $p_user_id";
			$t_result	 = db_query_bound( $t_query );
			$t_row		  = db_fetch_array( $t_result );
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
			<?php
			$t_work_hours_per_day = plugin_config_get( 'work_hours_per_day', null, false, $p_user_id );
			if ( is_null( $t_work_hours_per_day ) ) {
				# When this config option has not been overwritten for this user, use the general one
				$t_work_hours_per_day = plugin_config_get( 'work_hours_per_day', null );
			}
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
		<tr <?php echo helper_alternate_class() ?>>
			<td  class="category"><?php echo plugin_lang_get( 'deployability' ) ?></td>
			<td><input type="text" size="3" maxlength="3" name="deployability_<?php echo $p_user_id?>"
						value="<?php echo $t_row['deployability'] ?>"></td>
		</tr>
		<tr class="spacer"/>
		<tr <?php echo helper_alternate_class() ?>>
			<td  class="category"><?php echo plugin_lang_get( 'color' ) ?></td>
			<td>
				<select style="background-color: <?php print_background_color( $t_row['color'] ) ?>; color:<?php print_background_color( $t_row['color'], PLUGIN_PM_LIGHT ) ?>"
						name="color_<?php echo $p_user_id?>">
					<?php print_color_option_list( $t_row['color'] ) ?>
				</select>
			</td>
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
				$f_hourly_rate	= parse_float( gpc_get_string( 'hourly_rate_' . $p_user_id, null ) );
				$f_hours_per_week = gpc_get_int( 'hours_per_week_' . $p_user_id, null );
				$f_color		  = gpc_get_int( 'color_' . $p_user_id, null );
				$f_deployability  = gpc_get_int( 'deployability_' . $p_user_id, null );

				if ( !empty( $f_hourly_rate ) || !empty( $f_hours_per_week ) ||
					!empty( $f_color  ) || !empty( $f_deployability  ) ) {
					resource_insert_or_update( $p_user_id, $f_hourly_rate, $f_hours_per_week,
						$f_color, $f_deployability );
				}

				# Weekly work days

				$t_days_set = array();
				for ( $i = 1; $i <= 7; $i++ ) {
					$t_days_set[$i] = gpc_get_int( 'work_hours_per_day_' . $i, 0 );
				}
				if ( $t_days_set !== plugin_config_get( 'work_hours_per_day', null, true ) ) {
					plugin_config_set( 'work_hours_per_day', $t_days_set, $p_user_id );
				} else {
					# If the set values are the default, remove this setting for this user
					plugin_config_delete( 'work_hours_per_day', $p_user_id );
				}
			}

			if ( isset( $_POST['period_start'] ) ) {
				# Check for new period of unavailability
				$f_unavailable_start = strtotime_safe( gpc_get_string( 'period_start' ), true );
				$f_unavailable_end = strtotime_safe( gpc_get_string( 'period_end' ), true );
				$f_unavailable_type = gpc_get_int( 'unavailability_type', null );
				$f_unavailable_note = gpc_get_string( 'unavailability_note', null );

				if ( $f_unavailable_start !== false ) {
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

					$t_include_work = ( is_array( plugin_config_get( 'unavailability_ignore_work' ) ) &&
						in_array( $f_unavailable_type, plugin_config_get( 'unavailability_ignore_work' ) ) ) ? 0 : 1;

					resource_unavailability_period_add( $p_user_id, $f_unavailable_start, $f_unavailable_end,
						$f_unavailable_type, $t_include_work, $f_unavailable_note );
				}
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
				$t_customers     = customer_get_by_bug( $p_bug_id );
				$t_data	         = array();
				$t_is_billable   = is_billable( $p_bug_data->severity, gpc_get_bool( 'is_billable_' . $p_bug_id, 0 ) );
				$t_all_customers = gpc_get_bool( $p_bug_id . '_' . PLUGIN_PM_CUST_PAYING . '_' . PLUGIN_PM_ALL_CUSTOMERS, false );
				
				if ( ( $p_bug_data->status >= config_get( 'bug_closed_status_threshold' ) || $p_bug_data->status >= config_get( 'bug_resolved_status_threshold' ) ) 
						&& $t_all_customers ) {
					foreach ( $t_customers as $t_cust ) {
						$t_data[PLUGIN_PM_CUST_PAYING][$t_cust['id']] = true;
						$t_data[PLUGIN_PM_CUST_APPROVING][$t_cust['id']] = true;
					}
				}
				else {
					foreach ( $t_customers as $t_cust ) {
						$t_data[PLUGIN_PM_CUST_PAYING][$t_cust['id']]	=
							gpc_get_bool( $p_bug_id . '_' . PLUGIN_PM_CUST_PAYING . '_' . $t_cust['id'], false );
						$t_data[PLUGIN_PM_CUST_APPROVING][$t_cust['id']] =
							gpc_get_bool( $p_bug_id . '_' . PLUGIN_PM_CUST_APPROVING . '_' . $t_cust['id'], false );
					}
					# Add possible 'all customers'
					$t_data[PLUGIN_PM_CUST_PAYING][PLUGIN_PM_ALL_CUSTOMERS] = $t_all_customers;
				}
				

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
					
					# Wait to show error to higher level
					if ( empty( $t_paying_string ) && $t_is_billable && pm_bug_is_billable_affecting_required_paying_customers( $p_bug_data->status )  ) {
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
				
				pm_bug_update_or_insert( $p_bug_id, $t_is_billable );
			}
		}

		if ( $p_bug_data->status >= config_get( 'bug_resolved_status_threshold' ) ) {
			# The bug was resolved: if there was any work todo left, clear it
			$t_work_types_to_finish_upon_resolving = plugin_config_get( 'finish_upon_resolving' );
			if ( is_array( $t_work_types_to_finish_upon_resolving ) ) {
				foreach ( $t_work_types_to_finish_upon_resolving as $t_work_type ) {
					set_work_if_minutes_changed( $p_bug_id, $t_work_type, PLUGIN_PM_TODO, 0, null, ACTION::INSERT ); //_OR_UPDATE );
				}
			}
		}

		if ( $p_bug_data->status >= config_get( 'bug_closed_status_threshold' ) ) {
			# The bug was closed: if there was any work todo left, clear it
			$t_work_types_to_finish_upon_closing = plugin_config_get( 'finish_upon_closing' );
			if ( is_array( $t_work_types_to_finish_upon_closing ) ) {
				foreach ( $t_work_types_to_finish_upon_closing as $t_work_type ) {
					set_work_if_minutes_changed( $p_bug_id, $t_work_type, PLUGIN_PM_TODO, 0, null, ACTION::INSERT ); //_OR_UPDATE );
				}
			}
		}
	}

	function view_bug_pm_summary( $p_event, $p_bug_id ) {
		recently_visited_bug_add( $p_bug_id );

		# Fetch time registration summary
		if ( access_has_bug_level( plugin_config_get( 'view_time_reg_summary_threshold' ), $p_bug_id ) ) {
			$t_table  = plugin_table( 'work' );
			$t_customer_work_type_exclusion_clause = build_customer_worktype_exclude_clause('work_type');
			$t_query  = "SELECT work_type, minutes_type, sum(minutes) as minutes
						FROM $t_table
						WHERE minutes_type = 1 AND bug_id = $p_bug_id
							AND $t_customer_work_type_exclusion_clause
						GROUP BY work_type, minutes_type";
			$t_query  .= " UNION ";
			$t_query  .= "SELECT work_type, minutes_type, sum(minutes) as minutes
						FROM $t_table W
						WHERE minutes_type <> 1 AND bug_id = $p_bug_id
							AND $t_customer_work_type_exclusion_clause
							AND W.id = (
								SELECT id FROM $t_table TEMP
								WHERE TEMP.bug_id = W.bug_id AND TEMP.work_type = W.work_type AND TEMP.minutes_type = W.minutes_type
								ORDER BY timestamp DESC LIMIT 1
							)
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

			echo '<tr class="spacer"><td colspan="100%"></td></tr>';
			echo '<tr ' . helper_alternate_class() . '>';
			echo '<td class="category">'.  plugin_lang_get( 'est' ) . '</td><td>' . minutes_to_time( $t_est, true ) . '</td>
			<td class="category">' . plugin_lang_get( 'done' ) . '</td><td>' . minutes_to_time( $t_done, false ) . '</td>
			<td class="category">' . plugin_lang_get( 'todo' ) . '</td><td>' . minutes_to_time( $t_todo, true ) . '</td></tr>';
		}

		# Fetch customer payment summary
		if ( access_has_bug_level( plugin_config_get( 'view_time_reg_summary_threshold' ), $p_bug_id ) ) {
			$t_bug_data = bug_get( $p_bug_id );
			$t_billable_behavior_over_severity = get_billable_behavior_over_severity ( $t_bug_data->severity );
			if ( $t_billable_behavior_over_severity != PLUGIN_PM_BILLABLE_BEHAVIOR_NEVER_BILLABLE ) {
				$t_selected_cust		= bug_customer_get_selected( $p_bug_id, PLUGIN_PM_CUST_PAYING );

				$t_selected_cust_string = customer_list_to_string( $t_selected_cust );

				echo '<tr ' . helper_alternate_class() . '>';
				echo '<td class="category">' . plugin_lang_get( 'paying_customers' ) . '</td>';
				echo '<td colspan="100%" >' . $t_selected_cust_string . '</td></tr>';
			}
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
								FROM $t_work_table W
								WHERE bug_id = $p_bug_id
									AND minutes_type = $t_est
									AND id = (
										SELECT id FROM $t_work_table TEMP
										WHERE TEMP.bug_id = W.bug_id AND TEMP.work_type = W.work_type AND TEMP.minutes_type = W.minutes_type
										ORDER BY timestamp DESC LIMIT 1
									)";
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
								FROM $t_work_table W
								WHERE bug_id = $p_bug_id
									AND minutes_type = $t_todo
									AND id = (
										SELECT id FROM $t_work_table TEMP
										WHERE TEMP.bug_id = W.bug_id AND TEMP.work_type = W.work_type AND TEMP.minutes_type = W.minutes_type
										ORDER BY timestamp DESC LIMIT 1
									)";
		$t_result_fetch_todo = db_query_bound( $t_query_fetch_todo );

		# Fetch all work types
		$t_query_fetch_all   = "SELECT W.id, W.user_id, U.realname, W.work_type, W.minutes_type, W.minutes, W.book_date, W.info, W.timestamp
								FROM $t_work_table W
								LEFT JOIN mantis_user_table U ON U.id = W.user_id
								WHERE bug_id = $p_bug_id
								ORDER BY timestamp DESC";
		$t_result_fetch_all = db_query_bound( $t_query_fetch_all );

		# Get the different worktypes as an array
		$t_work_types = plugin_lang_get_enum( 'work_types' );

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
			# PATCH-LITE
			# Do not add TODO work if EST is not defined; else, total has no coherence with ticket overview
			if ( isset ( $t_work[PLUGIN_PM_EST][$row["work_type"]] ) ) {
				@$t_work[PLUGIN_PM_TODO][PLUGIN_PM_WORKTYPE_TOTAL] += $row["todo"];
			}
			# END PATCH-LITE
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
						$t_work[PLUGIN_PM_DIFF][$t_work_type_code] = $t_work[PLUGIN_PM_REMAINING][$t_work_type_code];
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

		$t_time_registered = array();
		while ( $row = db_fetch_array( $t_result_fetch_all ) ) {
			$t_time_registered[$row["id"]] = $row;
		}

		if ( plugin_config_get( 'bug_view_mode' ) == 2 ) {
			# ******************************* TIME REGISTRATION ******************************* -->
			echo '<br/>';
			include 'pages/bug_view_time_registration_option2.php';
			# ******************************* END TIME REGISTRATION ******************************* -->

			# ******************************* TARGETS ******************************* -->
			echo '<br/>';
			include 'pages/bug_view_target_option2.php';
			# ******************************* END TARGETS ******************************* -->

			# ******************************* CUSTOMER ******************************* -->
			if ( access_has_bug_level( plugin_config_get( 'enable_customer_payment_threshold' ), $p_bug_id ) ||
				access_has_bug_level( plugin_config_get( 'enable_customer_approval_threshold' ), $p_bug_id )
			) {
				echo '<br/>';
				include 'pages/bug_view_customer.php';
			}
			
			# ******************************* END CUSTOMER ******************************* -->
		}
		else {
		?>
			<table width="100%" style="padding:0px">
			<tr class="print">
			<td>
				<?php
				# ******************************* TIME REGISTRATION ******************************* -->
				include 'pages/bug_view_time_registration_option1.php';
				# ******************************* END TIME REGISTRATION ******************************* -->

				# ******************************* CUSTOMER ******************************* -->
				if ( access_has_bug_level( plugin_config_get( 'enable_customer_payment_threshold' ), $p_bug_id ) ||
					access_has_bug_level( plugin_config_get( 'enable_customer_approval_threshold' ), $p_bug_id )
				) {
					echo '<br/>';
					include 'pages/bug_view_customer.php';
				}
				# ******************************* END CUSTOMER ******************************* -->
				?>
			</td>
			<td>
				<?php
				# ******************************* TARGETS ******************************* -->
				include 'pages/bug_view_target_option1.php';
				# ******************************* END TARGETS ******************************* -->
				?>
			</td>
			</table>
		<?php
		}
		
	}
	
	function update_bug_form( $p_event, $p_bug_id ) {
		# All data in 1 row
		/*
		if ( access_has_bug_level( plugin_config_get( 'enable_customer_payment_threshold' ), $p_bug_id ) ) {
			$t_pm_bug = pm_bug_get( $p_bug_id );
			$t_bug_data = bug_get( $p_bug_id );
			$t_bug_data_status = gpc_get_int( 'new_status', $t_bug_data->status );
			$t_enable_customer_approval_threshold = access_has_bug_level( plugin_config_get( 'enable_customer_approval_threshold' ), $p_bug_id );
			$t_is_event_bug_status_form = ($p_event == 'EVENT_UPDATE_BUG_STATUS_FORM' ? true : false);
			$t_spacer_colspan = '4';
			if ( $t_is_event_bug_status_form ) {
				$t_spacer_colspan = '2';
			}
			else if ( $t_enable_customer_approval_threshold ) {
				$t_spacer_colspan = '6';
			}
		?>
		<input type="hidden" name="bug_customer_supplied" value="1">
		<?php if ( !$t_is_event_bug_status_form ) { ?>
		<tr class="spacer"><td colspan="<?php echo $t_spacer_colspan ?>"></td></tr>
		<?php } ?>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo plugin_lang_get( 'is_billable' ); ?>
			</td>
			<td>
				<?php 
					if ( pm_bug_is_billable_affecting_required_paying_customers( $t_bug_data_status ) == true ) {
						echo '<input type="checkbox" id="is_billable_' . $p_bug_id . '" name="is_billable_' . $p_bug_id . '" ' . ( $t_pm_bug["is_billable"] == 1 ? 'checked="checked"' : '' ) . ' onclick="document.getElementById(\'paying_customers_required_mark\').style.display=(this.checked?\'inline\':\'none\')">';
					}
					else {
						echo '<input type="checkbox" id="is_billable_' . $p_bug_id . '" name="is_billable_' . $p_bug_id . '" ' . ( $t_pm_bug["is_billable"] == 1 ? 'checked="checked"' : '' ) . '>';
					}
				?>
			</td>
		<?php 
			if ( $t_is_event_bug_status_form ) {
				echo '</tr><tr ' . helper_alternate_class() . '>';
			}
		?>
			<td class="category">
				<?php
				if ( pm_bug_is_billable_affecting_required_paying_customers( $t_bug_data_status ) ) {
					echo '<span id="paying_customers_required_mark" class="required" style="display:' . ( $t_pm_bug["is_billable"] == 1 ? 'inline;' : 'none;' ) . '">*</span>';
				}
				echo plugin_lang_get( 'paying_customers' );
				?>
			</td>
			<td <?php if ( !$t_enable_customer_approval_threshold && !$t_is_event_bug_status_form ) echo 'colspan="3"' ?>>
				<?php print_customer_list( $p_bug_id ); ?>
			</td>
		<?php if ( $t_enable_customer_approval_threshold ) { 
				if ( $t_is_event_bug_status_form ) {
					echo '</tr><tr ' . helper_alternate_class() . '>';
				}
		?>
			<td class="category">
				<?php echo plugin_lang_get( 'approving_customers' ) ?>
			</td>
			<td>
				<?php print_customer_list( $p_bug_id, PLUGIN_PM_CUST_APPROVING, false ); ?>
			</td>
		<?php } ?>
		</tr>
		<?php
		}
		*/
		
		
		# All data in 3 rows
		if ( access_has_bug_level( plugin_config_get( 'enable_customer_payment_threshold' ), $p_bug_id ) ) {
			$t_pm_bug = pm_bug_get( $p_bug_id );
			$t_bug_data = bug_get( $p_bug_id );
			$t_bug_data_status = gpc_get_int( 'new_status', $t_bug_data->status );
			$t_enable_customer_approval_threshold = access_has_bug_level( plugin_config_get( 'enable_customer_approval_threshold' ), $p_bug_id );
			$t_is_event_bug_status_form = ($p_event == 'EVENT_UPDATE_BUG_STATUS_FORM' ? true : false);
			$t_billable_behavior_over_severity = get_billable_behavior_over_severity ( $t_bug_data->severity );
		?>
		<input type="hidden" name="bug_customer_supplied" value="1">
		<?php if ( !$t_is_event_bug_status_form ) { ?>
		<tr class="spacer"><td colspan="5"></td></tr>
		<?php } ?>
		<?php
			if ( $t_billable_behavior_over_severity != PLUGIN_PM_BILLABLE_BEHAVIOR_NEVER_BILLABLE ) {
				if ( $t_billable_behavior_over_severity == PLUGIN_PM_BILLABLE_BEHAVIOR_OPTIONAL_UNSELECTED || $t_billable_behavior_over_severity == PLUGIN_PM_BILLABLE_BEHAVIOR_OPTIONAL_SELECTED ) {
		?>
				<tr <?php echo helper_alternate_class() ?>>
					<td class="category">
						<?php echo plugin_lang_get( 'is_billable' ); ?>
					</td>
					<td colspan="5">
						<?php 
							if ( pm_bug_is_billable_affecting_required_paying_customers( $t_bug_data_status ) == true ) {
								echo '<input type="checkbox" id="is_billable_' . $p_bug_id . '" name="is_billable_' . $p_bug_id . '" ' . ( $t_pm_bug["is_billable"] == 1 ? 'checked="checked"' : '' ) . ' onclick="document.getElementById(\'paying_customers_required_mark\').style.display=(this.checked?\'inline\':\'none\')">';
							}
							else {
								echo '<input type="checkbox" id="is_billable_' . $p_bug_id . '" name="is_billable_' . $p_bug_id . '" ' . ( $t_pm_bug["is_billable"] == 1 ? 'checked="checked"' : '' ) . '>';
							}
						?>
					</td>
				</tr>
		<?php }	?>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php
				if ( $t_billable_behavior_over_severity != PLUGIN_PM_BILLABLE_BEHAVIOR_NEVER_REQUIRED 
						&& ( pm_bug_is_billable_affecting_required_paying_customers( $t_bug_data_status ) 
								|| $t_billable_behavior_over_severity == PLUGIN_PM_BILLABLE_BEHAVIOR_ALWAYS_REQUIRED ) ) {
					echo '<span id="paying_customers_required_mark" class="required" style="display:' . ( $t_pm_bug["is_billable"] == 1 || $t_billable_behavior_over_severity == PLUGIN_PM_BILLABLE_BEHAVIOR_ALWAYS_REQUIRED ? 'inline;' : 'none;' ) . '">*</span>';
				}
				echo plugin_lang_get( 'paying_customers' );
				?>
			</td>
			<td colspan="5">
				<?php print_customer_list( $p_bug_id ); ?>
			</td>
		</tr>
		<?php 
			}
			if ( $t_enable_customer_approval_threshold ) {
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
	
	function manage_project_create( $p_event, $p_project_id ) {
		$t_association_mode_auto   = PLUGIN_PM_ASSOCIATION_AUTO;
		$t_customer_table          = plugin_table( 'customer' );
		
		$t_query_fetch_auto  = 
				"SELECT id, name, share, can_approve, association_mode
				FROM $t_customer_table
				WHERE association_mode = $t_association_mode_auto
				";
		$t_result_fetch_auto = db_query_bound( $t_query_fetch_auto );
		
		while ( $row = db_fetch_array( $t_result_fetch_auto ) ) {
			set_project_customer( $p_project_id, $row["id"] );
		}
	}
	
	function manage_project_page( $p_event, $p_project_id ) {

		//$t_project_id = gpc_get_int( 'project_id' );
		$t_customer_table         = plugin_table( 'customer' );
		$t_project_customer_table = plugin_table( 'project_customer' );
		$t_association_mode_all   = PLUGIN_PM_ASSOCIATION_ALL;

		# Fetch associated customers
		$t_query_fetch_associated  = 
				"SELECT id, name, share, can_approve, association_mode
				FROM $t_customer_table
				WHERE association_mode = $t_association_mode_all
				
				UNION
				
				SELECT C.id, C.name, C.share, C.can_approve, C.association_mode
				FROM $t_project_customer_table P
				LEFT JOIN $t_customer_table C ON C.id = P.customer_id
				WHERE P.project_id = $p_project_id 
					AND C.association_mode <> $t_association_mode_all
				
				ORDER BY name
				";
							
		$t_result_fetch_associated = db_query_bound( $t_query_fetch_associated );

		# Fetch NOT associated customers
		$t_query_fetch_not_associated  = 
				"SELECT id, name, share, can_approve, association_mode
				FROM $t_customer_table
				WHERE association_mode <> $t_association_mode_all
				AND NOT id IN (SELECT customer_id FROM $t_project_customer_table WHERE project_id = $p_project_id)
				";
							
		$t_result_fetch_not_associated = db_query_bound( $t_query_fetch_not_associated );
		$t_count_not_associated = db_num_rows( $t_result_fetch_not_associated );

	?>
		<br />
		<div align="center">
			<table class="width75" cellspacing="1">
				<tr>
					<td class="form-title" colspan="2"><?php echo plugin_lang_get( 'customer_management' ) ?></td>
				</tr>
				<tr class="row-category">
					<td><?php echo plugin_lang_get( 'customer_name' ) ?></td>
					<td><?php echo plugin_lang_get( 'customer_share' ) ?></td>
					<td><?php echo plugin_lang_get( 'customer_can_approve' ) ?></td>
					<td><?php echo plugin_lang_get( 'customer_association_mode' ) ?></td>
					<td><?php echo lang_get( 'actions' ) ?></td>
				</tr>

				<?php
				while ( $row = db_fetch_array( $t_result_fetch_associated ) ) {
					$t_customer_id = $row['id'];
					$t_name = $row['name'];
					$t_share = format( $row['share'], 2 );
					$t_can_approve = $row['can_approve'] == 1 ? '<img src="images/ok.gif" width="20" height="15" alt="X" title="X" />' : '&nbsp;';
					$t_association_mode = $row['association_mode'];
					?>
					<tr <?php echo helper_alternate_class() ?>>
						<td><?php echo $t_name ?></td>
						<td><?php echo $t_share ?>%</td>
						<td><?php echo $t_can_approve ?></td>
						<td><?php echo get_association_mode_string ( $t_association_mode ) ?></td>
						<td class="center">
							<?php if ( $row["association_mode"] != PLUGIN_PM_ASSOCIATION_ALL ) { ?>
							<form method="post" action="<?php echo plugin_page( 'project_customer_delete' ) ?>">
								<?php echo form_security_field( 'plugin_ProjectManagement_project_customer_delete' ) ?>
								<input type="hidden" name="customer_id" value="<?php echo $t_customer_id ?>" />
								<input type="hidden" name="project_id" value="<?php echo $p_project_id ?>" />
								<input type="submit" value="<?php echo lang_get( 'remove_link' ) ?>"/>
							</form>
							<?php } ?>
						</td>
					</tr>
					<?php
				}
				?>

				<?php if ( $t_count_not_associated > 0 ) { ?>
				<tr>
					<form method="post" action="<?php echo plugin_page( 'project_customer_add' ) ?>">
						<td colspan="100%">
							<?php echo form_security_field( 'plugin_ProjectManagement_project_customer_add' ) ?>
							<input type="hidden" name="project_id" value="<?php echo $p_project_id ?>" />
							<select id="customer_id" name="customer_id">
							<?php
							while ( $row = db_fetch_array( $t_result_fetch_not_associated ) ) {
							
								echo '<option value="' . $row["id"] . '">' . $row["name"] . '</option>';
							
							}
							?>
							</select>
							<input type="submit" value="<?php echo plugin_lang_get( 'add_customer' ) ?>"/>
						</td>
					</form>
				</tr>
				<?php } ?>
			</table>
		</div>
		
	<?php
	}

}
