<?php
/**
 * Transfer a Contact to Another Company
 *
 * $Id: transfer-2.php,v 1.8 2004/07/22 11:21:13 cpsource Exp $
 */

require_once('include-locations-location.inc');

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

if(eregi("[a-zA-Z]", $company_name)) {
    $sql = "select company_name, company_id from companies where company_name like '%" . $company_name . "%' and company_record_status = 'a' order by company_name";
}
else {
    $sql = "select company_name, company_id from companies where company_id = '" . $company_name . "' and company_record_status = 'a'";
}
$rst = $con->execute($sql);

if($rst->rowcount()) {
    $company_menu = $rst->getmenu2('company_id', $company_id, false);
    $company_menu .= "&nbsp; <input type=button class=button value='More Info' onclick='document.forms[0].company_id.value=document.forms[1].company_id.options[document.forms[1].company_id.selectedIndex].value; document.forms[0].submit();'>";
}
$rst->close();

$con->close();

$page_title = $contact_name . " - " . _("Transfer to Another Company");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action="<?php echo $http_site_root; ?>/companies/one.php" method=get target="_blank">
            <input type="hidden" name="company_id">
        </form>
        <form action=transfer-3.php method=post>
        <input type=hidden name=contact_id value=<?php echo $contact_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Transfer to Another Company"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><?php  echo ($company_menu) ? $company_menu : _("No companies found"); ?></a></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                    <? if($company_menu) { ?><input class=button type=submit name=save value="<?php echo _("Save"); ?>"> <input class=button type=submit name=everywhere value="<?php echo _("Save and Update All Records"); ?>"><?php } ?>
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
 * Revision 1.8  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.7  2004/07/21 15:20:04  introspectshun
 * - Localized strings for i18n/translation support
 * - Removed include of lang file
 *
 * Revision 1.6  2004/07/20 20:34:23  introspectshun
 * - Replaced hard-coded host with $http_site_root
 *
 * Revision 1.5  2004/07/20 14:25:59  neildogg
 * - Search by ID
 *
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
