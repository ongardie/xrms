<?php

// copyright 2007 Glenn Powers <glenn@net127.com>

function xrms_plugin_init_flags() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['company_sidebar_top']['flags'] = 'company_flags';
    $xrms_plugin_hooks['contact_sidebar_top']['flags'] = 'contact_flags';
    $xrms_plugin_hooks['contact_content_top']['flags'] = 'register_flags';
}

function register_flags() {
        global $con, $session_user_id, $on_what_table, $on_what_id;

// $con->debug=1;

$sql = "SELECT " . $on_what_table . "_record_status
FROM " . $on_what_table . "
WHERE " . $on_what_table . "_id = '" . $on_what_id . "'";

$rst = $con->execute($sql);

    if (!$rst->EOF) {
        if ($rst->fields[$on_what_table . '_record_status'] == 'p') {
$status = _("PENDING");
        if ($rst->fields[$on_what_table . '_record_status'] == 'a') {
// $status = _("ACTIVE");
}
        if ($rst->fields[$on_what_table . '_record_status'] == 'd') {
$status = _("DELETED");
}

if ($status) {
$data =  '<table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=4>'
                . $status
                . '</td>
            </tr>
    </table>';
}
return $data;
        }
    } else {
        return;
    }
}



function contact_flags() {
        global $con, $session_user_id, $contact_id;

        // DELETED CONTACT
        
$data =  '<table class=widget cellspacing=1 width="100%">
            <tr>
            	<td class=widget_header width=26%>' . _("Record Status") . '</td>
            	<td class=widget_header width=25%>' . _("Alert") . '</td>
            	<td class=widget_header width=25%>' . _("Maxed") . '</td>
            	<td class=widget_header width=25%>' . _("Status") . '</td>
            	<td class=widget_header width=25%>' . _("Notices") . '</td>
            </tr>
            <tr>       	
';

// $con->debug=1;

$sql = "SELECT contact_record_status
FROM contacts
WHERE contact_id = '" . $contact_id . "'";

$rst = $con->execute($sql);

    if (!$rst->EOF) {
        if ($rst->fields['contact_record_status'] == "d") {

            $data .= '<td class=widget_content width=25%><h1>'
                . _("DELETED RECORD")
                . '</h1></td>
';
} else {
	$data .= '<td width=25%><h2>' . _("Active") . '</h2></td>';
}
} else {
	$data .= '<td width=25%></td>';
}

// Alerts

$sql = "SELECT cf_data.value
FROM cf_data
JOIN cf_instances ON (cf_instances.key_id = '" . $contact_id . "'
and cf_instances.instance_id = cf_data.instance_id)
WHERE cf_data.field_id = 40";

$rst = $con->execute($sql);

    if (!$rst->EOF) {
        if ($rst->fields['value'] == 1) {

$data .=  '<td class=widget_content width=25%>'
                . _("WARNING!")
                . '</td>
';
} else {
	$data .= '<td width=25%><h2>' . _("No") . '</h2></td>';
}
}else {
	$data .= '<td width=25%><h2>' . _("No") . '</h2></td>';
}

// MAXED

$sql = "SELECT cf_data.value
FROM cf_data
JOIN cf_instances ON (cf_instances.key_id = '" . $contact_id . "'
and cf_instances.instance_id = cf_data.instance_id)
WHERE cf_data.field_id = 42";

$rst = $con->execute($sql);

    if (!$rst->EOF) {
        if ($rst->fields['value'] == 1) {

$data .=  '<td class=widget_content width=25%><h2>'
                . _("Yes")
                . '</h2></td>
';
} else {
	$data .= '<td width=25%><h2>' . _("No") . '</h2></td>';
}
}else {
	$data .= '<td width=25%><h2>' . _("No") . '</h2></td>';
}

// STATUS

$sql = "SELECT opportunity_status_pretty_name
FROM opportunities
LEFT JOIN opportunity_statuses ON (opportunities.opportunity_status_id = opportunity_statuses.opportunity_status_id) 
WHERE opportunities.contact_id = '" . $contact_id . "'
AND opportunity_record_status = 'a'
LIMIT 1";

$rst = $con->execute($sql);

if (!$rst->EOF) {

$data .= '               <td class=widget_content  width=25%>
<h2>' . $rst->fields['opportunity_status_pretty_name'] . '</h2>
                </td>
';

    }else {
	$data .= '<td width=25%></td>';
}

// notices
    
        $opp_sql = "SELECT *
                    FROM activities
                    WHERE  contact_id = '" . $contact_id . "'
                    AND activity_type_id = '12' 
                    AND activity_record_status= 'a'
                    ORDER BY activity_id
                    LIMIT 1";

        $opp_rst = $con->execute($opp_sql);
        if (!$opp_rsa->EOF) {
                    $date=date('Y-m-d', strtotime($opp_rst->fields['scheduled_at']));

$data .=  '<td class=widget_content width=25%>
             <h2>' . $date . '</h2>
                </td>
';

}else {
	$data .= '<td width=25%></td>';
}
$data .= "</tr>
</table>
";

return $data;
}


function company_flags() {
        global $con, $session_user_id, $company_id;

$data =  '<table class=widget cellspacing=1 width="100%">
            <tr>
              <td class=widget_header width=50%>Record Status</td>
              <td class=widget_header width=50%>Enrollment Status</td>
            </tr>
            <tr>
';

// $con->debug=1;

$sql = "SELECT company_record_status
FROM companies
WHERE company_id = '" . $company_id . "'";

$rst = $con->execute($sql);

    if (!$rst->EOF) {
        if ($rst->fields['company_record_status'] == "d") {
$data .= '
                <td class=widget_content width=50%><h1>'
                . _("DELETED")
                . '</h1></td>
            ';
    } else {
$data .= '
                <td class=widget_content width=50%><h3>'
                . _("Active")
                . '</h3></td>
            ';
}
} else {
$data .= '
                <td class=widget_content width=50%>'
                . _("Unknown")
                . '</td>
            ';
}



if (($session_user_id == 2) || ($session_user_id == 3) || ($session_user_id == 14) || ($session_user_id == 20) || ($session_user_id == 27)  || ($session_user_id == 28) ) {

// $con->debug=1;

$sql = "SELECT opportunity_status_pretty_name as status, opportunities.opportunity_status_id as id, opportunities.opportunity_id
FROM opportunities
JOIN opportunity_statuses ON (opportunities.opportunity_status_id = opportunity_statuses.opportunity_status_id)
WHERE opportunity_statuses.opportunity_type_id = 2
AND company_id = '" . $company_id . "'";

$rst = $con->execute($sql);

    if (!$rst->EOF) {
        if ($rst->fields['id'] == 12) {
            $data .= '
                <td class=widget_content width=50%><h3>
			' . $rst->fields['status'] . '</h3>
                </td>
            ';
        } else {
            $data .= '
                <td class=widget_content width=50%>
                    Not Enrolled, ' . $rst->fields['status'] . '</a><br>
          <form action="/xrms/opportunities/edit.php" method="get">
          <input type="hidden" name="opportunity_id" value="' . $rst->fields['opportunity_id'] . '">
          <input type="submit" value="Update Opportunity">
          </form>
                </td>
            ';
}
    } else {
        $data .= '<td class=widget_content width=50%>'
              . _("No Opportunity") . '<br>
	  <form action="/xrms/opportunities/new.php" method=get>
          <input type="hidden" name="company_id" value="' . $company_id . '">
          <input type="hidden" name="opportunity_type_id" value="2">
          <input type="submit" value="Create Opportunity">
          </form>
          </td>';
}
$data .= "</tr>
</table>
";

return $data;
}
}

?>