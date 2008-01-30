<?php

// copyright 2007 Glenn Powers <glenn@net127.com>
 
function gup_audit_items() {

global $http_site_root, $con, $session_user_id, $company_id;

// $con->debug=1;

$data .= "<form action=\"" . $http_site_root . current_page() . "\" name=\"NewContactsForm\" target=\"tabIframe1\" method=\"GET\">";
$data .= "<input type=hidden name=company_id value=\"" . $company_id . "\">";

$sql = "SELECT audit_item_id, audit_item_timestamp, last_name as user, audit_item_type
            FROM audit_items
            JOIN users ON (audit_items.user_id = users.user_id)
            WHERE audit_item_record_status = 'a'
            AND audit_item_type NOT LIKE 'viewed'
            and on_what_table = 'contacts'
            and on_what_id = '" . $contact_id . "' 
";

$columns = array();
$columns[] = array('name' => _('Log#'), 'index_sql' => 'audit_item_id', 'sql_sort_column' => 'audit_item_id', 'default_sort' => 'asc');
$columns[] = array('name' => _('Date'), 'audit_item_timestamp' => 'oppstatus');
$columns[] = array('name' => _('User'), 'index_sql' => 'user');
$columns[] = array('name' => _('Action'), 'index_calc' => 'audit_item_type');

// $default_columns = array();

$pager_columns = new Pager_Columns('NewContactsPager', $columns, $default_columns, 'ContactsForm');
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$contacts_pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');

$new_contact_location="../contacts/new.php?company_id=$company_id";

$pager = new GUP_Pager($con, $sql, 'getContactDetails', _('Audit Log'), 'NewContactsForm', 'NewContactsPager', $columns, false, true);
$contacts_export_button=$pager->GetAndUseExportButton();
$endrows = "<tr><td class=widget_content_form_element colspan=10>
            $pager_columns_button $contacts_export_button
            <input class=button type=button value=\"" .  _('Mail Merge') . "\" onclick=\"javascript: location.href='../email/email.php?scope=company&company_id=$company_id'\">" .
            render_create_button("New",'button',"location.href='$new_contact_location';") .  "</td></tr>";

$pager->AddEndRows($endrows);

$data .= $pager->Render($system_rows_per_page);

// $comma_separated = implode(",", $data);
return  $data;

}
