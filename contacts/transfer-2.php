<?php
/**
 * Transfer a Contact to Another Company
 *
 * $Id: transfer-2.php,v 1.4 2004/07/19 22:18:09 neildogg Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_POST['msg'];
$contact_id = $_POST['contact_id'];
$company_name = $_POST['company_name'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

// This query is done separately in case there is no current address
$sql = "select * from contacts where contact_id = $contact_id";
$rst = $con->execute($sql);

$contact_name = $rst->fields['first_names'] . ' ' . $rst->fields['last_name'];
$company_id =  $rst->fields['company_id'];

$sql = "select company_name, company_id from companies where company_name like '%" . $company_name . "%' and company_record_status = 'a' order by company_name";
$rst = $con->execute($sql);

if($rst->rowcount()) {
    $company_menu = $rst->getmenu2('company_id', $company_id, false);
    $company_menu .= "&nbsp; <input type=button class=button value='More Info' onclick='document.forms[0].company_id.value=document.forms[1].company_id.options[document.forms[1].company_id.selectedIndex].value; document.forms[0].submit();'>";
}
$rst->close();

$con->close();

$page_title = $contact_name . " - Transfer to Another Company";
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action="https://68.162.84.101/xrms/companies/one.php" method=get target="_blank">
            <input type="hidden" name="company_id">
        </form>
        <form action=transfer-3.php method=post>
        <input type=hidden name=contact_id value=<?php echo $contact_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>Transfer to Another Company</td>
            </tr>
            <tr>
                <td class=widget_content><?php  echo ($company_menu) ? $company_menu : "No companies found"; ?></a></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                    <? if($company_menu) { ?><input class=button type=submit name=save value="Save"> <input class=button type=submit name=everywhere value="Save and Update All Records"><?php } ?>
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
 * $Log: transfer-2.php,v $
 * Revision 1.4  2004/07/19 22:18:09  neildogg
 * - Added company search box
 *  - Added move all records with contact
 *
 * Revision 1.3  2004/07/06 17:25:22  braverock
 * - fixed sort order on transfer
 *   - resolves SF bug 978438 reported by Walt Pennington
 *
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
