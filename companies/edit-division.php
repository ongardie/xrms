<?php
/**
 * Save changes to divisions
 *
 * $Id: edit-division.php,v 1.2 2004/04/16 22:19:38 maulani Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$company_id = $_GET['company_id'];
$division_id = $_GET['division_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select d.*, c.company_name from companies c, company_division d where c.company_id = d.company_id and d.division_id = $division_id";

$rst = $con->execute($sql);

if ($rst) {
    $division_id = $rst->fields['division_id'];
    $company_name = $rst->fields['company_name'];
    $division_name = $rst->fields['division_name'];
    $description = $rst->fields['description'];
    $rst->close();
}

$con->close();

$page_title = $company_name . ' - ' . $division_name . ' - Edit Division';
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-division-2.php method=post>
        <input type=hidden name=company_id value=<?php echo $company_id; ?>>
        <input type=hidden name=division_id value=<?php echo $division_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>Edit Division</td>
            </tr>
            <tr>
                <td class=widget_label_right>Company</td>
                <td class=widget_content><a href="../companies/one.php?company_id=<?php echo $company_id; ?>"><?php  echo $company_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right>Division Name</td>
                <td class=widget_content_form_element><input type=text size=30 name=division_name value="<?php echo $division_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Description</td>
                <td class=widget_content_form_element><textarea rows=8 cols=80 name=description><?php echo $description; ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"> <input class=button type=button value="Delete Division" onclick="javascript: location.href='delete-division.php?company_id=<?php echo $company_id ?>&division_id=<?php echo $division_id ?>';"></td>
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
 * $Log: edit-division.php,v $
 * Revision 1.2  2004/04/16 22:19:38  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.1  2004/01/26 19:18:02  braverock
 * - added company division pages and fields
 * - added phpdoc
 *
 */
?>