<?php

// copyright 2007 Glenn Powers <glenn@net127.com>

function xrms_plugin_init_home_stats() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['private_sidebar_top']['opportunity_stats_sidebar'] = 'opp_stats';
}


function opp_stats() {
        global $con, $session_user_id, $http_site_root;

// $con->debug=1;

$data =  '
        <table class=widget cellspacing=1 width="100%">
	  <form action="' . $http_site_root . '/xrms/plugins/home_stats/submit.php" method=get>
            <tr>
                <td class=widget_header colspan=4>'
                . _("Sales Opportunities by Status")
                . '</td>
            </tr>
          <tr>
          <td class=widget_content>'
			. _("<br><br>Status")
                . '</td>
          <td class=widget_content>'
			. _("Last<br><strong>180</strong><br>Days")
                . '</td>
          <td class=widget_content>'
			. _("Last<br><strong>30</strong><br>Days")
                . '</td>
          <td class=widget_content>'
			. _("Last<br><strong>7</strong><br>Days")
                . '</td>
            </tr>

';

$sql = "SELECT opportunity_statuses.opportunity_status_pretty_name as status, count(opportunity_id) as apps, opportunity_statuses.opportunity_status_id as id
FROM opportunity_statuses
LEFT OUTER JOIN opportunities ON (opportunities.opportunity_status_id = opportunity_statuses.opportunity_status_id
    AND DATE_SUB(CURDATE(),INTERVAL 180 DAY) <= opportunities.entered_at
AND opportunity_statuses.opportunity_type_id = 1
AND opportunities.opportunity_record_status = 'a')
INNER JOIN opportunity_statuses as o2 ON (o2.opportunity_status_id = opportunity_statuses.opportunity_status_id
AND o2.opportunity_type_id = 1)
GROUP BY opportunity_statuses.opportunity_status_id";

$rst = $con->execute($sql);

$sql = "SELECT opportunity_statuses.opportunity_status_pretty_name as status, count(opportunity_id) as apps
FROM opportunity_statuses
LEFT OUTER JOIN opportunities ON (opportunities.opportunity_status_id = opportunity_statuses.opportunity_status_id
    AND DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= opportunities.entered_at
AND opportunity_statuses.opportunity_type_id = 1
AND opportunities.opportunity_record_status = 'a')
INNER JOIN opportunity_statuses as o2 ON (o2.opportunity_status_id = opportunity_statuses.opportunity_status_id
AND o2.opportunity_type_id = 1)
GROUP BY opportunity_statuses.opportunity_status_id";

$rst2 = $con->execute($sql);

$sql = "SELECT opportunity_statuses.opportunity_status_pretty_name as status, count(opportunity_id) as apps
FROM opportunity_statuses
LEFT OUTER JOIN opportunities ON (opportunities.opportunity_status_id = opportunity_statuses.opportunity_status_id
    AND DATE_SUB(CURDATE(),INTERVAL 7 DAY) <= opportunities.entered_at
AND opportunity_statuses.opportunity_type_id = 1
AND opportunities.opportunity_record_status = 'a')
INNER JOIN opportunity_statuses as o2 ON (o2.opportunity_status_id = opportunity_statuses.opportunity_status_id
AND o2.opportunity_type_id = 1)
GROUP BY opportunity_statuses.opportunity_status_id";

$rst3 = $con->execute($sql);

if ($rst) {
while (!$rst->EOF) {
$data .= '<tr>
                <td class=widget_content><a href="/xrms/opportunities/some.php?opportunities_opportunity_status_id=' . $rst->fields['id'] . '">
			    ' . $rst->fields['status'] . '</a>
                </td>
                <td class=widget_content>
			    ' . $rst->fields['apps'] . '
                </td>
                <td class=widget_content>
                ' . $rst2->fields['apps'] . '
                </td>
                <td class=widget_content>
                ' . $rst3->fields['apps'] . '
                </td>
            </tr>';

$total += $rst->fields['apps'];
$total2 += $rst2->fields['apps'];
$total3 += $rst3->fields['apps'];

        $rst->MoveNext();
	if ($rst->fields['status'] !== $rst2->fields['status']) {
        $rst2->MoveNext();
}
	if ($rst->fields['status'] !== $rst3->fields['status']) {
        $rst3->MoveNext();
}
}
$data .= '<tr>
                <td class=widget_content align=right>
                        <strong>TOTAL</strong>
                </td>
                <td class=widget_content>
                        ' . $total . '
                </td>
                <td class=widget_content>
                        ' . $total2 . '
                </td>
                <td class=widget_content>
                        ' . $total3 . '
                </td>
            </tr>';

} else {
$data .= '<tr><td colspan=2>' . _("No Applications") . '</td></tr>';
}
$data .= '</table>';

return $data;
}

?>
