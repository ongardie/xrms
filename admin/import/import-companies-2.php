<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$delimiter = $_POST['delimiter'];
$user_id = $_POST['user_id'];
$crm_status_id = $_POST['crm_status_id'];
$company_source_id = $_POST['company_source_id'];
$industry_id = $_POST['industry_id'];
$account_status_id = $_POST['account_status_id'];
$rating_id = $_POST['rating_id'];

move_uploaded_file($_FILES['file1']['tmp_name'], $tmp_upload_directory . 'companies-to-import.txt');

$page_title = "Preview Data";
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=65% valign=top>

        <form action="import-companies-3.php" method="post">
        <input type=hidden name=delimiter value="<?php  echo $delimiter; ?>">
        <input type=hidden name=user_id value="<?php  echo $user_id; ?>">
        <input type=hidden name=crm_status_id value="<?php  echo $crm_status_id; ?>">
        <input type=hidden name=company_source_id value="<?php  echo $company_source_id; ?>">
        <input type=hidden name=industry_id value="<?php  echo $industry_id; ?>">
        <input type=hidden name=account_status_id value="<?php  echo $account_status_id; ?>">
        <input type=hidden name=rating_id value="<?php  echo $rating_id; ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=8>Preview Data</td>
            </tr>
            <tr>
                <td class=widget_label>#</td>
                <td class=widget_label>Company Name</td>
                <td class=widget_label>Company Phone</td>
                <td class=widget_label>Company Address</td>
                <td class=widget_label>Company City</td>
                <td class=widget_label>Company State</td>
                <td class=widget_label>Company Postal Code</td>
                <td class=widget_label>Contact First Names</td>
                <td class=widget_label>Contact Last Name</td>
                <td class=widget_label>Contact Phone</td>
                <td class=widget_label>Contact E-Mail</td>
            </tr>

<?php

switch ($delimiter) {
    case 'comma':
        $delimiter = ",";
        break;
    case 'tab':
        $delimiter = "\t";
        break;
    case 'pipe':
        $delimiter = "|";
        break;
}

$row = 1;

$handle = fopen($tmp_upload_directory . 'companies-to-import.txt', 'r');
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

while ($data = fgetcsv($handle, 1024, $delimiter) and $row <= 20) {
    $num = count($data);
    for ($c=0; $c < $num; $c++) {
        $company_name = $data[0];
        $company_phone = $data[1];
        $company_address = $data[2];
        $company_city = $data[3];
        $company_state = $data[4];
        $company_postal_code = $data[5];
        $contact_first_names = $data[6];
        $contact_last_name = $data[7];
        $contact_phone = $data[8];
        $contact_email = $data[9];
    }
    // print "<li>row: $row, data[0]: $data[0], data[1]: $data[1], data[2]: $data[2], data[3]: $data[3], data[4]: $data[4], data[5]: $data[5], data[6]: $data[6], data[7]: $data[7], data[8]: $data[8]";
    print("<tr><td class=widget_label_right>$row</td><td class=widget_content>$company_name</td><td class=widget_content>$company_phone</td><td class=widget_content>$company_address</td><td class=widget_content>$company_city</td><td class=widget_content>$company_state</td><td class=widget_content>$company_postal_code</td><td class=widget_content>$contact_first_names</td><td class=widget_content>$contact_last_name</td><td class=widget_content>$contact_phone</td><td class=widget_content>$contact_email</td></tr>");
    // add_market_information_item($con, $market_information_item_type_id, $company_id, $contact_id, $session_user_id, $mfr_pn, $clean_pn, $mfr, $qty, $dc, $pkg, $price, $note);
    $row++;
}

fclose($handle);
$con->close();

?>
            <tr>
                <td class=widget_content colspan=8><input class=button type=submit value="Continue"></td>
            </tr>
        </table>
        </form>

        </td>
        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=33% valign=top>

        </td>
    </tr>
</table>

<?php end_page();; ?>