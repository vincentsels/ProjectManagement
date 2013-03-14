<?php

/**
 * TODO: Terrible performance; customers should be cashed!
 */
class ProjectManagementCustomersColumn extends MantisColumn {

    public $column = "customers_column";
    public $sortable = false;

    private $cache = array();

    public function __construct() {
        $this->title = plugin_lang_get( 'paying_customers', 'ProjectManagement' );
    }

    public function cache( $p_bugs ) {
        if ( count( $p_bugs ) < 1 ) {
            return;
        }

        $t_bug_customer_table = plugin_table( 'bug_customer', 'ProjectManagement' );

        $t_bug_ids = array();
        foreach ( $p_bugs as $t_bug ) {
            $t_bug_ids[] = $t_bug->id;
        }

        $t_bug_ids = implode( ',', $t_bug_ids );

        $t_query  = "SELECT bug_id, customers
                       FROM $t_bug_customer_table
                      WHERE bug_id IN ( $t_bug_ids )
                        AND type = 0";
        $t_result = db_query_bound( $t_query );

        while ( $t_row = db_fetch_array( $t_result ) ) {
            $this->cache[$t_row['bug_id']] = explode( PLUGIN_PM_CUST_CONCATENATION_CHAR, $t_row['customers'] );
        }
    }

    public function display( $p_bug, $p_columns_target ) {
        if ( isset( $this->cache[$p_bug->id] ) ) {
            plugin_push_current( 'ProjectManagement' );
            echo customer_list_to_string( $this->cache[$p_bug->id] );
            plugin_pop_current();
        }
    }

}
