<?php

function xrms_plugin_init_zone_reports() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['private_front_splash']['zone_reports'] = 'zone_reports';
}

function zone_reports() {
	global $opportunity_id, $con, $session_user_id, $include_directory;
	require_once 'Zend/XmlRpc/Client.php';

require_once('gup-red.php');
$gup_red = $opportunity_rows;

require_once('gup-yellow.php');
$gup_yellow = $opportunity_rows;

require_once('gup-green.php');
$gup_green = $opportunity_rows;

return '
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td bgcolor="#ff0000">
	        ' .  $gup_red . '
                </td>
            </tr>
            <tr>
                <td>
	        ' .  $gup_yellow . '
                </td>
            </tr>
            <tr>
                <td>
	        ' .  $gup_green . '
                </td>
            </tr>
        </table>
';

}

?>