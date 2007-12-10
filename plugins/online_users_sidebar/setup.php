<?php

// copyright 2007 Glenn Powers <glenn@net127.com>

// Requires the MaxMind GeoIPCity or GeoLiteCity databases and Net/GeoIP.php PEAR library

function xrms_plugin_init_online_users_sidebar() {
    global $xrms_plugin_hooks, $session_user_id;
    $xrms_plugin_hooks['private_sidebar_bottom']['online_users_sidebar'] = 'online_users_sidebar';
}


function online_users_sidebar() {
    global $con, $session_user_id, $company_id;
    require_once "Net/GeoIP.php";
        
    $geoip = Net_GeoIP::getInstance("/usr/local/share/GeoIP/GeoIPCity.dat");

    $window = date("Y-m-d H:i:s", strtotime("-1 hour", strtotime(date("Y-m-d H:i:s"))));
    $today = date("Y-m-d");

    // $con->debug=1;

    $data =  '
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=4>'
                . _("Online Users")
                . '</td>
            </tr>
          <tr>
                <td class=widget_label>
                    Name
                </td>
                <td class=widget_label>
                    Approx. Location	
                </td>
                <td class=widget_label>
                    Idle Time
                </td>
            </tr>';

    $sql = "SELECT last_name, first_names, username, users.user_id as uid, MAX(audit_item_timestamp) as time, remote_addr, TIMEDIFF(NOW(), MAX(audit_item_timestamp)) as idle
            FROM audit_items
            LEFT JOIN users ON (audit_items.user_id = users.user_id)
            WHERE  audit_item_timestamp > '" . $window . "'
            GROUP BY username
            ORDER BY time DESC";

    $rst = $con->execute($sql);

    $user_id = $rst->fields['uid'];

    if ($rst) {
        while (!$rst->EOF) {
            $name = $rst->fields['first_names'] . ' ' . $rst->fields['last_name'];
            $data .= '<tr>
                <td class=widget_content>
                ' . http_root_href("/reports/audit-items.php?starting=$today&ending=$today&user_id=$user_id", $name) . '
                </td>
                <td class=widget_content>';

            $ip=ip2long($rst->fields['remote_addr']);
            $location = $geoip->lookupLocation($rst->fields['remote_addr']);

            $data .= '<a href="http://maps.google.com/maps?f=q&hl=en&ll=' . $location->latitude . ',' . $location->longitude .
                    '&spn=0.700835,1.73584&t=h" target="_new">' . $location->city . ", " . $location->region . '</a>
                </td>
				<td class=widget_content>
			    ' . $rst->fields['idle'] . '
                </td>
            </tr>';

        $rst->MoveNext();
        }
    } else {
        $data .= '<tr><td colspan=4>' . _("No Users Online") . '</td></tr>';
    }
    
$data .= '</table>';

return $data;
}

?>