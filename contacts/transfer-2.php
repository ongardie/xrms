<?php
/**
 * Transfer a Contact to Another Company
 *
 * $Id: transfer-2.php,v 1.15 2010/08/17 22:09:04 gopherit Exp $
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

$con = get_xrms_dbconnection();
// $con->debug = 1;

// This query is done separately in case there is no current address
$sql = "select * from contacts where contact_id = $contact_id";
$rst = $con->execute($sql);

$contact_name = $rst->fields['first_names'] . ' ' . $rst->fields['last_name'];
$company_id =  $rst->fields['company_id'];

if($company_name) {
    $sql = "SELECT company_name, company_id FROM companies
            WHERE company_name LIKE ". $con->qstr(company_search_string($company_name),get_magic_quotes_gpc()) . " AND company_record_status = 'a' ORDER BY company_name";
    $company_name_rst = $con->execute($sql);
} else {
    $sql = "SELECT company_name, company_id FROM companies WHERE company_id = " . $company_id . " AND company_record_status = 'a'";
}

if($company_name_rst && $company_name_rst->rowcount()) {
    $company_menu = $company_name_rst->getmenu2('company_id', false, false);
    $company_menu .= "&nbsp; <input type=button class=button value='More Info' onclick='document.forms[0].company_id.value=document.forms[1].company_id.options[document.forms[1].company_id.selectedIndex].value; document.forms[0].submit();'>";
    $company_name_rst->close();
    $con->close();
} else {
    if (!$company_menu) {
        $company_name=urlencode($company_name);
        Header("Location: transfer.php?company_name=$company_name&contact_id=$contact_id&msg=".urlencode(_("No Companies Found, please try another search")));
        exit;
    }
}
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
        <input type=hidden name=old_company_id value=<?php echo $company_id; ?>>
        <table class=widget cellspacing=1>

            <tr>
                <td class=widget_header colspan=2><?php echo _("Transfer to Another Company"); ?></td>
            </tr>

            <tr>
                <td class=widget_content><?php  echo ($company_menu) ? $company_menu : _("No companies found"); ?></td>
            </tr>

            <tr>
                <td class=widget_content_form_element colspan=2>
                    <?php if($company_menu) { ?>
                        <input type="checkbox" name=everywhere /><font color="#FF0000"><strong><?php echo _("Also transfer all records attached to this contact to the new company"); ?></strong></font>&nbsp;&nbsp;
                        <input class=button type=submit name=save value="<?php echo _("Save"); ?>">
                    <?php } ?>
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
 * Revision 1.15  2010/08/17 22:09:04  gopherit
 * Fixed Bug Artifact # 3047297: /contacts/transfer-2.php dies when the search string of the company we are looking to transfer to is blank or contains non-alpha characters.
 *
 * Revision 1.14  2010/08/17 18:40:16  gopherit
 * Minor interface improvements
 *
 * Revision 1.13  2006/01/02 23:00:00  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.12  2005/08/05 21:44:51  vanmer
 * - changed contact company searches to use centralized company search string function
 *
 * Revision 1.11  2005/08/04 20:58:28  vanmer
 * - added check for results, return to last page if none are found
 * - changed dropdown to not contain blanks, default to first entry
 *
 * Revision 1.10  2005/08/04 18:58:38  vanmer
 * - added passthrough of contact's old company
 *
 * Revision 1.9  2005/01/25 03:50:48  braverock
 * - removed errant short tag
 *
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