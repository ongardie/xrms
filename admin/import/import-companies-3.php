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

$pointer = (strlen($_POST['pointer']) > 0) ? $_POST['pointer'] : 0;

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

$page_title = "Importing";
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=65% valign=top>

        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=7>Importing</td>
            </tr>
            <tr>
                <td class=widget_label>Company Name</td>
                <td class=widget_label>Company Phone</td>
                <td class=widget_label>Company Address</td>
                <td class=widget_label>Company State</td>
                <td class=widget_label>Company Postal Code</td>
                <td class=widget_label>Contact First Names</td>
                <td class=widget_label>Contact Last Name</td>
                <td class=widget_label>Contact Phone</td>
                <td class=widget_label>Contact E-Mail</td>
            </tr>

<?php

$row = 1;

$handle = fopen($tmp_upload_directory . 'companies-to-import.txt', 'r');

fseek($handle, $pointer);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$con->debug=1;

for ($i=0; $i < $how_many_rows_to_import_per_page; $i++) {

    $data = fgetcsv($handle, 1024, $delimiter);
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

    if ((strlen($contact_first_names) == 0) && (strlen($contact_last_name) == 0)) {
        $contact_last_name = 'Contact';
        $contact_first_names = 'Default';
    }

    $pointer = ftell($handle);
    if (strlen($company_name) > 0) {
        print("<tr><td class=widget_content>$company_name</td><td class=widget_content>$company_phone</td><td class=widget_content>$company_address</td><td class=widget_content>$company_city</td><td class=widget_content>$company_state</td><td class=widget_content>$company_postal_code</td><td class=widget_content>$contact_first_names</td><td class=widget_content>$contact_last_name</td><td class=widget_content>$contact_phone</td><td class=widget_content>$contact_email</td></tr>\n");
        $sql_insert_company = "insert into companies (user_id, crm_status_id, company_source_id, industry_id, account_status_id, rating_id, company_name) values ($user_id, $crm_status_id, $company_source_id, $industry_id, $account_status_id, $rating_id, " . $con->qstr($company_name, get_magic_quotes_gpc()) . ");";
        $con->execute($sql_insert_company);
        $company_id = $con->insert_id();
        $sql_update_company_code = "update companies set company_code = " . $con->qstr('C' . $company_id, get_magic_quotes_gpc()) . " where company_id = $company_id";
        $con->execute($sql_update_company_code);
        $sql_insert_address = "insert into addresses (company_id, address_name, address_body) values ($company_id, 'address', " . $con->qstr($company_address, get_magic_quotes_gpc()) . ")";
        $con->execute($sql_insert_address);
        $address_id = $con->insert_id();
        $sql_update_company_to_set_new_address_defaults = "update companies set default_billing_address = $address_id, default_shipping_address = $address_id, default_payment_address = $address_id where company_id = $company_id";
        $con->execute($sql_update_company_to_set_new_address_defaults);
        $sql_insert_contact = "insert into contacts (company_id, last_name, first_names, work_phone, email, entered_at, entered_by, last_modified_at, last_modified_by) values ($company_id, " . $con->qstr($contact_last_name, get_magic_quotes_gpc()) . ", " . $con->qstr($contact_first_names, get_magic_quotes_gpc()) . ", " . $con->qstr($contact_phone, get_magic_quotes_gpc()) . ", " . $con->qstr($contact_email, get_magic_quotes_gpc()) . ", " . $con->dbtimestamp(mktime()) . ", $session_user_id, " . $con->dbtimestamp(mktime()) . ", $session_user_id)";
        $con->execute($sql_insert_contact);
    }
    $row++;
}

fclose($handle);
$con->close();

?>

        </table>

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

<form action="import-companies-3.php" method="post">
<input type=hidden name=user_id value="<?php  echo $user_id; ?>">
<input type=hidden name=crm_status_id value="<?php  echo $crm_status_id; ?>">
<input type=hidden name=company_source_id value="<?php  echo $company_source_id; ?>">
<input type=hidden name=industry_id value="<?php  echo $industry_id; ?>">
<input type=hidden name=account_status_id value="<?php  echo $account_status_id; ?>">
<input type=hidden name=rating_id value="<?php  echo $rating_id; ?>">
<input type=hidden name=delimiter value="<?php  echo $delimiter; ?>">
<input type=hidden name=pointer value="<?php  echo $pointer; ?>">
</form>

<script language="javascript">
<!--

function reloadPage() {
    document.forms[0].submit();
}

function done() {
    location.href="<?php  echo $http_site_root; ?>/admin/index.php?msg=imported";
}

<?php if ($data) {echo("reloadPage()");} else {echo("done()");}?>

//-->
</script>

<?php end_page();; ?>