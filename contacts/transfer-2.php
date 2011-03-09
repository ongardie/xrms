<?php
/**
 * Transfer a Contact to Another Company
 *
 * $Id: transfer-2.php,v 1.16 2011/03/09 17:00:43 gopherit Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_POST['msg'];
$contact_id = (int)$_POST['contact_id'];
$new_company_name = $_POST['company_name'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

// This query is done separately in case there is no current address
$sql = "SELECT  first_names, 
                last_name,
                company_id
        FROM contacts
        WHERE contact_id = $contact_id";
$rst = $con->execute($sql);

$contact_name = $rst->fields['first_names'] . ' ' . $rst->fields['last_name'];

// Fetch the old company_id
$old_company_id =  $rst->fields['company_id'];
$rst->Close();

// Fetch a list of the rest of the contacts from the old company
$sql = 'SELECT '. $con->Concat('first_names',"' '",'last_name') ." AS contact_name, contact_id
        FROM contacts
        WHERE contact_record_status = 'a'
            AND company_id = $old_company_id
            AND contact_id <> $contact_id
        ORDER BY last_name, first_names";
$rst = $con->Execute($sql);

if ( $rst AND !$rst->EOF ) {
    $new_contact_menu = $rst->getmenu2('new_contact_id', FALSE, TRUE);
} else {
    $new_contact_menu = FALSE;
}

if($new_company_name) {
    $sql = 'SELECT company_name, company_id
            FROM companies
            WHERE ((company_name LIKE '. $con->qstr(company_search_string($new_company_name),get_magic_quotes_gpc()) .')
                    OR (company_id = '. $con->qstr($new_company_name) ."))
                AND company_record_status = 'a'
            ORDER BY company_name";
    $rst = $con->execute($sql);
} else {
    Header("Location: transfer.php?contact_id=$contact_id&msg=".urlencode(_("Please enter a company name to search for")));
    exit;
}

if($rst AND !$rst->EOF) {
    $new_company_menu = $rst->getmenu2('new_company_id', false, false);
    $new_company_menu .= '&nbsp; <input type=button class=button value="More Info" onclick="document.forms[0].company_id.value=document.forms[\'TransferForm\'].company_id.options[document.forms[\'TransferForm\'].company_id.selectedIndex].value; document.forms[0].submit();">';
    $rst->close();
}
$con->close();

if (!$new_company_menu) {
    $new_company_name=urlencode($new_company_name);
    Header("Location: transfer.php?company_name=$new_company_name&contact_id=$contact_id&msg=".urlencode(_("No Companies Found, please try another search")));
    exit;
}

$page_title = $contact_name . " - " . _("Transfer Contact to Another Company");
start_page($page_title, true, $msg);

?>

<script type="text/javascript">
    function confirmSubmitTransfer() {
        if (confirm('<?php echo _('You are about to modify all of the records attached to this contact') .'.\n\n'. _('Do you wish to continue') .'?'; ?>')) {
            document.forms['TransferForm'].submit();
        }
    }

    function validateTransferForm() {
        if (document.forms['TransferForm']['transfer_mode'][0].checked) {
            confirmSubmitTransfer();
        } else if (document.forms['TransferForm']['transfer_mode'][1].checked
                    && document.forms['TransferForm']['new_contact_id'].value > 0) {
            confirmSubmitTransfer();
        } else {
            alert ('<?php echo _('Please select how you would like the contact records to be handled'); ?>');
        }
    }

</script>

<div id="Main">
    <div id="Content">
        <form action="<?php echo $http_site_root; ?>/companies/one.php" method=get target="_blank">
            <input type="hidden" name="company_id">
        </form>
        <form action=transfer-3.php method=post id="TransferForm">
        <input type=hidden name=contact_id value=<?php echo $contact_id; ?>>
        <input type=hidden name=old_company_id value=<?php echo $old_company_id; ?>>
        <table class=widget cellspacing=1>

            <tr>
                <td class="widget_header" colspan="2"><?php echo _('Transfer') .' '. $contact_name .' '. _('to') .':'; ?></td>
            </tr>

            <tr>
                <td class="widget_content" colspan="2"><?php  echo ($new_company_menu) ? $new_company_menu : _("No companies found"); ?></td>
            </tr>

            <tr>
                <td class="widget_header" colspan="2" style="font-weight: bold; color: red;">
                    <?php echo _('Please select how you want the records attached to') .' '. $contact_name .' '. _('to be handled') .':'; ?>
                </td>
            </tr>

            <tr>
                <td class="widget_content_form_element">
                    <input type="radio" name="transfer_mode" value="company"/><?php echo _("Move all records together with the contact to the new company"); ?>
                </td>
                <td class="widget_content_form_element"<?php if($new_contact_menu) { ?> rowspan="2"<?php } ?>>
                    <input class=button type="button" name=save value="<?php echo _("Transfer"); ?>" onclick="return validateTransferForm();">
                </td>
            </tr>

            <tr>
                <?php if($new_contact_menu) { ?>
                    <td class="widget_content_form_element">
                        <input type="radio" name="transfer_mode" value="contact" /><?php echo _("Retain all records in the old company and transfer them to") .':'. $new_contact_menu; ?>
                    </td>
                <?php } ?>
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
 * Revision 1.16  2011/03/09 17:00:43  gopherit
 * FIXED Bug Artifact #3204309  When a contact is transfered, the user now has two choices:
 *  - move all records with the contact to the new company
 *  - leave all the records with another contact at the old company.  In this case, the activity will now also properly track its additional participants.
 *
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