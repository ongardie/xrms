<?php

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

global $accounting_system;

switch($accounting_system) {
	case 'sl':
		require_once('utils-accounting-sl.php');
		break;
	case 'nl':
		require_once('utils-accounting-nl.php');
		break;
	case 'qb':
		require_once('utils-accounting-qb.php');
		break;
	case '':
		require_once('utils-accounting-null.php');
		break;
}

function colorize_credit_limit($credit_limit) {
	
	$zero_or_larger = ($credit_limit < 0) ? 1 : 0;
	switch($zero_or_larger) {
		case 1:
			$colorized_credit_limit = "<font color=#ff3333>($" . number_format($credit_limit, 2) . ")</font>";
			break;
		case 0:
			$colorized_credit_limit = "<font color=#009900>($" . number_format($credit_limit, 2) . ")</font>";
			break;
	}
	
	return $colorized_credit_limit;
	
}

?>