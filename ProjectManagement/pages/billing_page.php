<?php
access_ensure_global_level( plugin_config_get( 'view_billing_threshold' ) );

$f_export = gpc_get_bool( 'export', false );
$f_period_start = gpc_get_string( 'period_start', first_day_of_month( -1 ) );
$f_period_end   = gpc_get_string( 'period_end', last_day_of_month( -1 ) );

$t_work_table         = plugin_table( 'work' );
$t_resource_table     = plugin_table( 'resource' );
$t_bug_customer_table = plugin_table( 'bug_customer' );
$t_user_table         = db_get_table( 'mantis_user_table' );
$t_bug_table          = db_get_table( 'mantis_bug_table' );
$t_pm_bug_table       = plugin_table( 'bug' );
$t_project_table      = db_get_table( 'mantis_project_table' );
$t_category_table     = db_get_table( 'mantis_category_table' );

$t_const_done = PLUGIN_PM_DONE;
$t_const_paying = PLUGIN_PM_CUST_PAYING;
$t_startdate  = strtotime_safe( $f_period_start );
$t_enddate    = strtotime_safe( $f_period_end );

$t_project_select_clause = get_project_select_clause();
$t_customer_work_type_exclusion_clause = build_customer_worktype_exclude_clause('work_type');

$t_query      = "SELECT p.name as project_name, c.name as category_name, u.username, u.realname,
						b.id as bug_id, b.summary as bug_summary,
						sum(w.minutes) as minutes, max(r.hourly_rate) as hourly_rate,
						max(bc.customers) as customers
					FROM $t_work_table w
			   LEFT JOIN $t_bug_table b ON w.bug_id = b.id
			   LEFT JOIN $t_user_table u ON w.user_id = u.id
			   LEFT JOIN $t_project_table p ON b.project_id = p.id
			   LEFT JOIN $t_category_table c ON b.category_id = c.id
			   LEFT OUTER JOIN $t_resource_table r ON w.user_id = r.user_id
			   LEFT OUTER JOIN $t_bug_customer_table bc ON b.id = bc.bug_id AND $t_const_paying = bc.type
				   WHERE w.minutes_type = $t_const_done
					 AND w.book_date BETWEEN $t_startdate AND $t_enddate
					 AND $t_project_select_clause
					 AND $t_customer_work_type_exclusion_clause
					 AND b.id IN (SELECT bug_id FROM $t_pm_bug_table WHERE is_billable = 1)
				   GROUP BY p.name, c.name, u.username, b.id, b.summary
				   ORDER BY p.name, c.name, u.username, b.id, b.summary";

$t_result = db_query_bound( $t_query );

$t_all_customers = customer_get_all( PLUGIN_PM_CUST_PAYING );

$t_custom_fields_to_include = array();
$t_plugin_columns_to_include = array();
$t_all_plugin_columns = columns_get_plugin_columns();
foreach ( plugin_config_get( 'fields_to_include_in_overviews' ) as $field_name ) {
    $t_custom_field_name = column_get_custom_field_name( $field_name );
    if( $t_custom_field_name !== null ) {
        $t_custom_field_id = custom_field_get_id_from_name( $t_custom_field_name );
        if( $t_custom_field_id ) {
            $t_custom_fields_to_include[$t_custom_field_id] = $t_custom_field_name;
        }
    } else if ( isset( $t_all_plugin_columns[$field_name] ) ) {
        $t_column_object = $t_all_plugin_columns[$field_name];
        $t_plugin_columns_to_include[$field_name] = $t_column_object;
    }
}

# Fill the billing array
$t_billing = array();
while ( $row = db_fetch_array( $t_result ) ) {

	$t_billing_row                  = array();
	$t_billing_row['project_name']  = $row["project_name"];
	$t_billing_row['category_name'] = $row["category_name"];
	$t_billing_row['username']      = $row["username"];
	$t_billing_row['realname']      = $row["realname"];
	$t_billing_row['bug_id']        = $row["bug_id"];
	$t_billing_row['bug_summary']   = $row["bug_summary"];
	$t_billing_row['hours']         = $row["minutes"] / 60;
	$t_billing_row['hourly_rate']   = $row["hourly_rate"];
	$t_billing_row['cost']          = $row["minutes"] * $row["hourly_rate"] / 60;

	$t_paying_customers = explode( PLUGIN_PM_CUST_CONCATENATION_CHAR, $row['customers'] );
	$t_paying_customers = array_filter( $t_paying_customers );
	if ( count( $t_paying_customers ) == 0 ||
		array_search( (string)PLUGIN_PM_ALL_CUSTOMERS, $t_paying_customers, true ) ) {
		# The paying customers have not yet been set; assume all customers
		# or 'all customers' was checked.
		$t_paying_customers = array_keys( $t_all_customers );
	}

	# Calculate the total added percentage for this bug
	$t_total_percentage = 0;
	foreach ( $t_paying_customers as $cust_id ) {
		$t_total_percentage += $t_all_customers[$cust_id]['share'];
	}

	foreach ( $t_all_customers as $cust_id => $cust ) {
		if ( array_search( $cust_id, $t_paying_customers ) !== false && $t_total_percentage !== 0 ) {
			$t_billing_row[$cust['name']] =
				$row["minutes"] * $row["hourly_rate"] / 60 * $cust['share'] / $t_total_percentage;
		} else {
			$t_billing_row[$cust['name']] = 0;
		}
	}

    # Custom fields
    foreach ( $t_custom_fields_to_include as $field_id => $field_name  ) {
        $t_billing_row[$field_name] = custom_field_get_value( $field_id, $row["bug_id"] );
    }

    foreach ( $t_plugin_columns_to_include as $col_name => $col_object ) {
        $t_bug = bug_get( $row["bug_id"] );
        $col_object->cache(array($t_bug));
        $t_billing_row[$col_object->title] = get_plugin_col_value( $col_object, $t_bug );
    }

	$t_billing[] = $t_billing_row;
}

# Calculate total per customer
$t_totals_row = array();
foreach ( $t_billing as $row ) {
    foreach ( $t_all_customers as $cust ) {
		@$t_totals_row[$cust['name']] += $row[$cust['name']];
		@$t_totals_row['cost'] += $row[$cust['name']];
	}
    @$t_totals_row['hours'] += $row['hours'];
}
$t_totals_row['project_name'] = init_cap( 'total', true );
$t_totals_row['hourly_rate'] = 0;

if ( $f_export && count( $t_billing ) > 0 ) {
    # Export to excel

    # Construct filename
    $t_filename = format_date_for_filename( $t_startdate, false ) . '_' . format_date_for_filename( $t_enddate, false );
    $t_filename .= '_' . format_date_for_filename( time(), true );

    #Prepare customers
    $t_customer_data = array();
    $t_col_cust_name = plugin_lang_get( 'customer_name' );
    $t_col_cust_share = plugin_lang_get( 'customer_share' );
    foreach ( $t_all_customers as $customer ) {
        if ( !isset( $customer['name'] ) ) {
            continue;
        }
        $t_customer_row = array();
        $t_customer_row[$t_col_cust_name] = $customer['name'];
        $t_customer_row[$t_col_cust_share] = $customer['share'];
        $t_customer_data[] = $t_customer_row;
    }

    $xls = new ExcelExporterIncludingHeader;
    $xls->addWorksheetWithHeader( 'Detail', $t_billing );
    $xls->addWorksheetWithHeader( 'Customers', $t_customer_data );
    $xls->sendWorkbook(  $t_filename . '.xls' );
} else {
    # Add totals to the array
    $t_billing[] = $t_totals_row;

    html_page_top1( plugin_lang_get( 'title' ) );
    html_page_top2();

    print_pm_reports_menu( 'billing_page' );
    ?>

<div class="center">
	<table class="width100">
		<tr>
			<td class="center">
				<form name="billing_page" method="post"
					  action="<?php echo plugin_page( 'billing_page' ) ?>">
					<?php
					echo plugin_lang_get( 'period' ) . ': ';
					echo '<input type="text" size="8" maxlength="10" autocomplete="off" id="period_start" name="period_start" value="' . $f_period_start . '">';
					date_print_calendar( 'period_start_cal' );
					date_finish_calendar( 'period_start', 'period_start_cal' );
					echo ' - <input type="text" size="8" maxlength="10" autocomplete="off" id="period_end" name="period_end" value="' . $f_period_end . '">';
					date_print_calendar( 'period_end_cal' );
					date_finish_calendar( 'period_end', 'period_end_cal' );
					?>
					<input name="submit" type="submit" value="<?php echo lang_get( 'update' ) ?>">
				</form>
			</td>
		</tr>
	</table>
</div>
<br/>
<table class="width100" cellspacing="1">
	<tr>
		<td colspan="100%" class="form-title">
			<?php
            echo plugin_lang_get( 'billing' );
            echo ' <span style="font-weight:normal">';
            $t_url = plugin_page( 'billing_page' );
            $t_url .= '&export=true';
            $t_url .= '&period_start=' . $f_period_start;
            $t_url .= '&period_end=' . $f_period_end;
            print_bracket_link( $t_url, plugin_lang_get( 'export_to_excel', 'ArrayExportExcel' ) );
            echo '</span>';
            ?>
		</td>
	</tr>
	<tr class="row-category">
		<td>
			<div align="center"><?php echo lang_get( 'project_name' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo lang_get( 'category' ) ?></div>
		</td>
        <?php
        foreach ( $t_custom_fields_to_include as $field_name ) {
            echo '<td><div align="center">', $field_name, '</div></td>';
        }
        foreach ( $t_plugin_columns_to_include as $col_object ) {
            echo '<td><div align="center">', $col_object->title, '</div></td>';
        }
        ?>
		<td>
			<div align="center"><?php echo lang_get( 'username' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo init_cap( 'bug' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo lang_get( 'summary' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo plugin_lang_get( 'hours' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo plugin_lang_get( 'hourly_rate' ) ?></div>
		</td>
		<td>
			<div align="center"><?php echo plugin_lang_get( 'cost' ) ?></div>
		</td>
		<?php
		foreach ( $t_all_customers as $cust ) {
			?>
			<td>
				<div align="center"><?php echo $cust['name'] ?></div>
			</td>
			<?php
		}
		?>
	</tr>
	<tr class="row-category">
		<td></td><td></td><td></td><td></td><td></td><td></td><td></td>
        <?php
        for ( $i = 0; $i < count( $t_custom_fields_to_include ); $i++ ) {
            echo '<td></td>';
        }
        for ( $i = 0; $i < count( $t_plugin_columns_to_include ); $i++ ) {
            echo '<td></td>';
        }
        ?>
		<td>100%</td>
		<?php
		foreach ( $t_all_customers as $cust ) {
			?>
			<td>
				<div align="center"><?php echo $cust['share'] . '%' ?></div>
			</td>
			<?php
		}
		?>
	</tr>

	<?php
	foreach ( $t_billing as $row ) {
		$t_class = helper_alternate_class();
		$bug_view_link = string_get_bug_view_link( $row["bug_id"], null, false );
		if ( $row["bug_id"] == null ) {
			$bug_view_link = '';
		}
		if ( array_search( $row, $t_billing ) == count( $t_billing ) - 1 ) {
			# Total row
			echo '<tr class="spacer" />';
			$t_class = 'class="row-category"';
		}
		echo '<tr ' . $t_class . '>';
		echo '<td> ' . $row['project_name'] . '</td>';
		echo '<td> ' . $row['category_name'] . '</td>';
        foreach ( $t_custom_fields_to_include as $field_id => $field_name  ) {
            echo '<td> ' . $row[$field_name] . '</td>';
        }
        foreach ( $t_plugin_columns_to_include as $col_name => $col_obj  ) {
            echo '<td> ' . $row[$col_obj->title] . '</td>';
        }
		echo '<td> ' . $row['realname'] . '</td>';
		echo '<td> ' . $bug_view_link . '</td>';
		echo '<td> ' . $row['bug_summary'] . '</td>';
		echo '<td><div class="right">' . format( $row['hours'], 2, false ) . '</div></td>';
		echo '<td><div class="right">' . format( $row['hourly_rate'], 2, false ) . ' ' . ($row['hourly_rate'] ? plugin_config_get ( 'currency_symbol' ) : '') . '</div></td>';
		echo '<td><div class="right">' . format( $row['cost'], 2, false ) . ' ' . ($row['cost'] ? plugin_config_get ( 'currency_symbol' ) : '') . '</div></td>';
		foreach ( $t_all_customers as $cust ) {
			echo '<td><div class="right">' . format( $row[$cust['name']], 2, false ) . ' ' . ($row[$cust['name']] ? plugin_config_get ( 'currency_symbol' ) : '') . '</div></td>';
		}
		echo '</tr>';
	}
	?>

</table>

<?php
html_page_bottom1( __FILE__ );
}
?>
