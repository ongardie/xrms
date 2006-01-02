<?php
/**
 * Transfer a Contact to Another Company
 *
 * $Id: transfer.php,v 1.10 2006/01/02 23:00:00 vanmer Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
getGlobalVar($company_name, 'company_name');
$msg = isset($_GET['msg']) ? $_GET['msg']: '';
$contact_id = $_GET['contact_id'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

// This query is done separately in case there is no current address
$sql = "select * from contacts where contact_id = $contact_id";
$rst = $con->execute($sql);

$contact_name = $rst->fields['first_names'] . ' ' . $rst->fields['last_name'];
$company_id =  $rst->fields['company_id'];

$con->close();

$page_title = $contact_name . " - " . _("Transfer to Another Company");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=transfer-2.php method=post>
        <input type=hidden name=contact_id value=<?php echo $contact_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Search for Company"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Name or ID"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text size=18 maxlength=100 name="company_name" value="<?php echo $company_name; ?>"> <?php  echo $required_indicator ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                    <input class=button type=submit value="<?php echo _("Search"); ?>">
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
 * Revision 1.10  2006/01/02 23:00:00  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.9  2005/08/04 20:59:10  vanmer
 * - removed msg from hidden variables, not needed to pass on every msg found
 * - added extra parameter to display company_name if provided
 *
 * Revision 1.8  2004/07/30 11:32:01  cpsource
 * - Define msg properly
 *   Fix bug with new.php wereby division_id and address_id were
 *     not set properly for getmenu2.
 *
 * Revision 1.7  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.6  2004/07/21 15:20:04  introspectshun
 * - Localized strings for i18n/translation support
 * - Removed include of lang file
 *
 * Revision 1.5  2004/07/20 15:38:37  neildogg
 * - Fixed copy and paste problem with required indicator
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
