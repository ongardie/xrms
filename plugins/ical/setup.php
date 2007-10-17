<?php
/*
*
* iCal plugin
* 
* @author Randy Martinsen <randym56@hotmail.com>
*
* $Id: setup.php,v 1.1 2007/10/17 00:56:27 randym56 Exp $
*
* NOTE: Required modification of activities_widget.php v1.58 to show the button when this plugin is activated
*
*/

function xrms_plugin_init_ical() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['ical_button']['iCal'] = 'iCal';
}

function iCal() {
    global $http_site_root;

    return " <input class=button type=button value=\"" .  _("iCal") . "\" onclick=\"javascript: location.href='" . $http_site_root . "/plugins/ical/ical.php';\">";

}

/**
 * $Log: setup.php,v $
 * Revision 1.1  2007/10/17 00:56:27  randym56
 * iCal creator - requires updates to activities-widget.php.
 *
 * Revision 1.0
 *
 */
?>
