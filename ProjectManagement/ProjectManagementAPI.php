<?php

# Constants
define( 'PLUGIN_PM_EST',		0 );
define( 'PLUGIN_PM_DONE',		1 );
define( 'PLUGIN_PM_TODO',		2 );
define( 'PLUGIN_PM_REMAINING',	3 );
define( 'PLUGIN_PM_DIFF',		4 );

define( 'PLUGIN_PM_WORKTYPE_TOTAL',	100 );

/**
 * Parses the supplied amount of hours and returns its
 * string representation as HH:MM
 * @param float $p_hours an amount of hours as decimal
 * @param bool $p_allow_blanks when true is passed, empty $p_hours return a blank string,
 * otherwise, empty $p_hours return the literal string '0:00'
 * @return string the amount of hours formatted ad HH:MM
 */
function hours_to_time( $p_hours, $p_allow_blanks = false ) {
	if ( $p_hours == 0 ) {
		if ( $p_allow_blanks ) {
			return null;
		}
		else {
			return '0:00';
		}
	}
	
	$t_hours = floor($p_hours);
	$t_minutes = str_pad(round(60 * ($p_hours - floor($p_hours))), 2, '0', STR_PAD_RIGHT);
	
	return $t_hours . ':' . $t_minutes;
}

?>