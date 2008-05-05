<?php
/*
*
* reports plugin
* 
* @author Randy Martinsen <randym56@hotmail.com>
*
* $Id: setup.php,v 1.1 2008/05/05 22:19:00 randym56 Exp $
*
*/

function xrms_plugin_init_reports() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['reports_button']['reports'] = 'reports';
}

function reports() {
    global $http_site_root;

    return " <input class=button type=button value=\"" .  _("Edit Custom Reports") . "\" onclick=\"javascript: location.href='" . $http_site_root . "/plugins/reports/reports.php';\">";

}

/**
 * $Log: setup.php,v $
 * Revision 1.1  2008/05/05 22:19:00  randym56
 * Custom reports plugin added to XRMS
 *
 *
 */
?>
