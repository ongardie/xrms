<?php
/**
 * Transfer a Contact to Another Company
 *
 * $Id: transfer.php,v 1.2 2004/06/15 17:26:21 introspectshun Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$contact_id = $_GET['contact_id'];
$address_id = $_GET['address_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

// This query is done separately in case there is no current address
$sql = "select * from contacts where contact_id = $contact_id";
$rst = $con->execute($sql);

$contact_name = $rst->fields['first_names'] . ' ' . $rst->fields['last_name'];
$company_id =  $rst->fields['company_id'];

$sql = "select company_name, company_id from companies where company_record_status = 'a'";
$rst = $con->execute($sql);

$company_menu = $rst->getmenu2('company_id', $company_id, false);
$rst->close();

$con->close();

$page_title = $contact_name . " - Transfer to Another Company";
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=transfer-2.php method=post>
        <input type=hidden name=contact_id value=<?php echo $contact_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>Transfer to Another Company</td>
            </tr>
            <tr>
                <td class=widget_label>New Company</td>
                <td class=widget_content><?php  echo $company_menu; ?></a></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                    <input class=button type=submit value="Save">
                </td>
            </tr>

        </table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        &nbsp;

    </div>
</div>

<?php

end_page();

/**
 * $Log: transfer.php,v $
 * Revision 1.2  2004/06/15 17:26:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL and Concat functions.
 *
 * Revision 1.1  2004/06/09 19:24:00  gpowers
 * - enables transfer of contact to new company
 *
 *
 */
?>
