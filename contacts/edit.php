<?php
/**
 * Edit a single contact.
 *
 * This screen allows the user to edit all the details of a contact.
 *
 * $Id: edit.php,v 1.47 2006/06/15 22:00:43 vanmer Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

$contact_id = $_GET['contact_id'];
getGlobalVar($return_url, 'return_url');
$on_what_id=$contact_id;

if (!$return_url) {
    $return_url=$http_site_root.current_page();
}
$url_return_url=urlencode($return_url);
$address_return_url=$http_site_root.current_page();

$session_user_id = session_check('','Update');

$msg        = isset($_GET['msg']) ? $_GET['msg'] : '';

$con = get_xrms_dbconnection();
//$con->debug = 1;

$sql = "SELECT * FROM users WHERE user_id = $session_user_id";
$rst = $con->execute($sql);

$rec = array();
$rec['last_hit'] = time();

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

update_recent_items($con, $session_user_id, "contacts", $contact_id);

$sql = "select cont.*, c.company_id, c.company_name
from contacts cont, companies c
where cont.company_id = c.company_id
and contact_id = $contact_id";

$rst = $con->execute($sql);

if ($rst) {
    $company_id = $rst->fields['company_id'];
    $division_id = $rst->fields['division_id'];
    $address_id = $rst->fields['address_id'];
    $edit_address=true;
    if ($address_id==1) {
        $edit_address=false;
    }
    $address= get_formatted_address($con,$address_id);
    $home_address_id = $rst->fields['home_address_id'];
    $edit_home_address=true;
    if ($home_address_id==1) {
        $edit_home_address=false;
    }
    $home_address= get_formatted_address($con,$home_address_id);
    $company_name = $rst->fields['company_name'];
    $last_name = $rst->fields['last_name'];
    $first_names = $rst->fields['first_names'];
    $summary = $rst->fields['summary'];
    $title = $rst->fields['title'];
    $description = $rst->fields['description'];
    $date_of_birth = $rst->fields['date_of_birth'];
    $tax_id = $rst->fields['tax_id'];
    $gender = $rst->fields['gender'];
    $salutation = $rst->fields['salutation'];
    $email = $rst->fields['email'];
    $work_phone = $rst->fields['work_phone'];
    $work_phone_ext = $rst->fields['work_phone_ext'];
    $cell_phone = $rst->fields['cell_phone'];
    $home_phone = $rst->fields['home_phone'];
    $profile = $rst->fields['profile'];
    $fax = get_formatted_phone($con, $rst->fields['address_id'],$rst->fields['fax']);
    $interests = $rst->fields['interests'];
    $custom1 = $rst->fields['custom1'];
    $custom2 = $rst->fields['custom2'];
    $custom3 = $rst->fields['custom3'];
    $custom4 = $rst->fields['custom4'];
    $user_id = $rst->fields['user_id'];
    $rst->close();
}

$sql = "select division_name, division_id
        from company_division
        where
        company_division.company_id = $company_id and
        division_record_status = 'a'";
$rst = $con->execute($sql);
if ($rst) {
    $division_menu = $rst->getmenu2('division_id', $division_id, true);
    $rst->close();
}

$salutation_menu = build_salutation_menu($con, $salutation, true);

$sql = "select count(contact_id) as contact_count from contacts where company_id = $company_id and contact_record_status = 'a'";
$rst = $con->execute($sql);
$contact_count = $rst->fields['contact_count'];
$rst->close();

$user_menu = get_user_menu($con, $session_user_id);

$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a'";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', '', false);
$rst->close();

$sql = "select address_name, address_id from addresses where company_id = $company_id and address_record_status = 'a' order by address_name";
$rst = $con->execute($sql);
if ($rst){
    $address_menu = $rst->getmenu2('address_id', $address_id, true);
    $rst->MoveFirst();
    $home_address_menu = $rst->getmenu2('home_address_id', $home_address_id, true);
    $rst->Close();
} else {
    db_error_handler ($con, $sql);
}

$user_id = ($user_id > 0) ? $user_id : $session_user_id;

$user_menu = get_user_menu($con, $user_id);

$contact_custom_rows = do_hook_function('contact_custom_inline_edit_display', $contact_custom_rows);

$accounting_rows = do_hook_function('contact_accounting_inline_edit', $accounting_rows);

$con->close();

$page_title = $first_names . ' ' . $last_name;
start_page($page_title, true, $msg);

// include confGoTo javascrip module
confGoTo_includes();

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=contact_id value=<?php echo $contact_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Contact Information"); ?></td>
            </tr>
	    <?php do_hook('contact_edit_form_top'); ?>
            <tr>
                <td class=widget_label_right><?php echo _("Salutation"); ?></td>
                <td class=widget_content_form_element><?php echo $salutation_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("First Names"); ?></td>
                <td class=widget_content_form_element><input type=text name=first_names value="<?php echo $first_names; ?>" size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Last Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=last_name value="<?php echo $last_name; ?>" size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Title"); ?></td>
                <td class=widget_content_form_element><input type=text name=title value="<?php echo $title; ?>" size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company"); ?></td>
                <td class=widget_content_form_element><a href="../companies/one.php?company_id=<?php echo $company_id; ?>"><?php echo $company_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Division"); ?></td>
                <td class=widget_content_form_element><?php echo $division_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Business Address"); ?></td>
                <td class=widget_content_form_element>
                    <?php echo $address; ?>
                    <input type=hidden name=address_return_url value="<?php echo $address_return_url; ?>">
                    <input type=hidden name=address_id value="<?php echo $address_id; ?>">
                    <br />
                        <input type=submit name=btChangeAddress value="<?php echo _("Choose New Address") ?>" class=button>
                        <?php if ($edit_address) { ?>&nbsp;<?php echo _("OR"); ?>&nbsp;
                            <input class=button type=submit name=btEditBusinessAddress value="<?php echo _("Edit Address"); ?>">
                        <?php } ?>

                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Home Address"); ?></td>
                <td class=widget_content_form_element>
                    <?php echo $home_address; ?>
                    <br />
                    <input type=hidden name=home_address_id value="<?php echo $home_address_id; ?>">
                    <input class=button type=submit name="btNewHomeAddress"  value="<?php echo _("Add New Address"); ?>">
                    <?php if ($edit_home_address) { ?>&nbsp;<?php echo  _("OR")?>&nbsp;
                        <input class=button type=submit name="btEditHomeAddress" value="<?php echo _("Edit Address"); ?>">
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("E-Mail"); ?></td>
                <td class=widget_content_form_element><input type=text name=email value='<?php echo $email; ?>' size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Work Phone"); ?></td>
                <td class=widget_content_form_element><input type=text name=work_phone value='<?php echo $work_phone; ?>' size=30>&nbsp;
                <?php echo _("x"); ?>&nbsp;<input type=text name=work_phone_ext size=5 value='<?php if ($work_phone_ext) {echo $work_phone_ext; } ?>'></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Cell Phone"); ?></td>
                <td class=widget_content_form_element><input type=text name=cell_phone value='<?php echo $cell_phone; ?>' size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Home Phone"); ?></td>
                <td class=widget_content_form_element><input type=text name=home_phone value='<?php echo $home_phone; ?>' size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Fax"); ?></td>
                <td class=widget_content_form_element><input type=text name=fax value='<?php echo $fax; ?>' size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Summary"); ?></td>
                <td class=widget_content_form_element><input type=text name=summary value="<?php echo $summary; ?>" size=35></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><input type=text name=description value='<?php echo $description; ?>' size=30></td>
            </tr>
            <!-- accounting plugin -->
            <tr>
                <td class=widget_label_right><?php echo _("Gender"); ?></td>
                <td class=widget_content_form_element>
                <select name="gender">
                    <option value="u" <?php if (($gender == "u") or ($gender == '')) {print " selected ";} ?>><?php echo _("Unknown"); ?>
                    <option value="m" <?php if ($gender == "m") {print " selected ";} ?>><?php echo _("Male"); ?>
                    <option value="f" <?php if ($gender == "f") {print " selected ";} ?>><?php echo _("Female"); ?>
                </select>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Date of Birth"); ?></td>
                <td class=widget_content_form_element><input type=text name=date_of_birth value="<?php echo $date_of_birth; ?>" size=10></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Tax ID"); ?></td>
                <td class=widget_content_form_element><input type=text name=tax_id value="<?php echo $tax_id; ?>" size=32></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Owner"); ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>

            <?php echo $accounting_rows; ?>

            <tr>
                <td class=widget_label_right><?php echo _("Interests"); ?></td>
                <td class=widget_content_form_element><input type=text name=interests size=35 value='<?php echo $interests; ?>'></td>
            </tr>

            <?php echo $contact_custom_rows; ?>

        <?php if ($contact_custom1_label!='(Custom 1)') { ?>
            <tr>
                <td class=widget_label_right><?php echo $contact_custom1_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom1 size=35 value="<?php echo $custom1; ?>"></td>
            </tr>
        <?php } if ($contact_custom2_label!='(Custom 2)') { ?>
            <tr>
                <td class=widget_label_right><?php echo $contact_custom2_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom2 size=35 value="<?php echo $custom2; ?>"></td>
            </tr>
        <?php } if ($contact_custom3_label!='(Custom 3)') { ?>
            <tr>
                <td class=widget_label_right><?php echo $contact_custom3_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom3 size=35 value="<?php echo $custom3; ?>"></td>
            </tr>
        <?php } if ($contact_custom4_label!='(Custom 4)') { ?>
            <tr>
                <td class=widget_label_right><?php echo $contact_custom4_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom4 size=35 value="<?php  echo $custom4; ?>"></td>
            </tr>
        <?php } //end custom field processing ?>
            <tr>
                <td class=widget_label_right><?php echo _("Profile"); ?></td>
                <td class=widget_content_form_element><textarea rows=8 cols=80 name=profile><?php echo $profile; ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                    <input class=button type=submit value="<?php echo _("Save"); ?>">
                    <input class=button type=button value="<?php echo _("Mail Merge"); ?>" onclick="javascript: location.href='../email/email.php?scope=contact&contact_id=<?php echo $contact_id; ?>';">
<?php
        if ( $contact_count > 1 ) {
          $quest = _("Delete Contact?");
          $button = _("Delete");
          $to_url = "delete.php?company_id=$company_id&contact_id=$contact_id";
          acl_confGoTo( $quest, $button, $to_url, 'contacts', $contact_id, 'Delete' );
        }
?>
                    <input class=button type=button value="<?php echo _("Transfer"); ?>" onclick="javascript: location.href='transfer.php?contact_id=<?php echo $contact_id; ?>';">


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
 * $Log: edit.php,v $
 * Revision 1.47  2006/06/15 22:00:43  vanmer
 * - changes to allow contacts to have an owner
 *
 * Revision 1.46  2006/03/21 03:04:01  ongardie
 * - Added contact_edit_form_top plugin hook.
 *
 * Revision 1.45  2006/01/16 14:47:54  niclowe
 * removed get_formatted_phone for phone numbers - you shouldnt format the phoen number on an edit as it will ruin the data when you save the edit
 *
 * Revision 1.44  2006/01/02 22:59:59  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.43  2005/12/06 22:30:20  jswalter
 *  - modified 'build_salutation_menu()' to force BLANK as first menu item
 *
 * Revision 1.42  2005/09/25 05:42:06  vanmer
 * - removed IM field references from all contact pages (now handled by plugin)
 * - added custom field hook for contacts new.php
 *
 * Revision 1.41  2005/08/15 19:10:51  braverock
 * - don't show custom1-4 if the labels haven't changed in vars.php
 *
 * Revision 1.40  2005/08/15 19:01:11  braverock
 * - rearrange order of fields to speed entry in a phone environment
 * - comment IM fields pending moving them to a plugin
 *
 * Revision 1.39  2005/07/07 23:16:12  vanmer
 * - changed front end to simply identify which button is pressed in order to submit changes to contact before
 * changing address
 *
 * Revision 1.38  2005/07/06 03:21:01  vanmer
 * - changed to use one-address.php to edit contact addresses, as well as add new ones
 * - added logic to hide edit address when address is the default of 1
 *
 * Revision 1.37  2005/07/06 02:08:58  vanmer
 * - changed to use select functionality from addresses.php in companies
 * - changed to allow direct edit of business address from edit contact
 *
 * Revision 1.36  2005/06/08 23:07:53  braverock
 * - fix cut and paste error on date_of_birth/tax_id
 *
 * Revision 1.35  2005/06/07 20:16:25  braverock
 * - sort address drop-down list by address_name
 *
 * Revision 1.34  2005/06/01 16:02:09  vanmer
 * - altered delete button to be controlled by the ACL
 *
 * Revision 1.33  2005/05/31 15:50:51  ycreddy
 * Added a hook for Contact Custom Inline Edit
 *
 * Revision 1.32  2005/05/16 21:30:22  vanmer
 * - added tax_id handling to contacts pages
 *
 * Revision 1.31  2005/05/04 14:36:14  braverock
 * - removed obsolete CSS widget_label_right_166px, replaced with widget_label_right
 *
 * Revision 1.30  2005/05/02 13:51:51  braverock
 * - add support for home address
 *
 * Revision 1.29  2005/05/02 13:19:58  braverock
 * - move edit address button to be next to the Address selector
 *
 * Revision 1.28  2005/05/02 13:15:32  braverock
 * - add get_formatted_phone rendering to phone numbers
 *
 * Revision 1.27  2005/04/26 17:28:03  gpowers
 * - added Extension ("x") to contact work phone
 * - removed non-digits from phone numbers in edit-2's, new-2's
 * - updated work phone display to include Extension
 *
 * Revision 1.26  2005/04/07 13:57:05  maulani
 * - Add salutation table to allow installation configurable list.  Also add
 *   many more default entries.
 *   RFE 913526 by algon.
 *
 * Revision 1.25  2005/03/21 13:40:55  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.24  2005/03/18 20:53:32  gpowers
 * - added hooks for inline info plugin
 *
 * Revision 1.23  2005/01/13 18:42:54  vanmer
 * - Basic ACL changes to allow edit functionality to be restricted
 *
 * Revision 1.22  2004/07/30 09:45:24  cpsource
 * - Place confGoTo setup later in startup sequence.
 *
 * Revision 1.21  2004/07/29 11:23:04  cpsource
 * - Added confGoTo sub-system for Delete confirm.
 *
 * Revision 1.20  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.19  2004/07/21 15:20:04  introspectshun
 * - Localized strings for i18n/translation support
 * - Removed include of lang file
 *
 * Revision 1.18  2004/07/15 14:49:45  cpsource
 * - Define $msg from $_GET or else ''
 *
 * Revision 1.17  2004/06/21 17:26:07  braverock
 * - address can be blank, revised argument to getmenu2
 *
 * Revision 1.16  2004/06/21 14:02:06  gpowers
 * - added space between "Transfer" and "Delete" buttons
 *
 * Revision 1.15  2004/06/15 17:26:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL and Concat functions.
 *
 * Revision 1.14  2004/06/15 14:31:33  gpowers
 * - corrected time formats
 *
 * Revision 1.13  2004/06/10 15:26:59  gpowers
 * - added "Transfer" and "Edit Address" buttons. (moved from one.php)
 *
 * Revision 1.12  2004/05/28 13:55:02  gpowers
 * removed "viewed" audit log entry. this is redundant, as this data is
 * already stored in httpd access logs.
 *
 * Revision 1.11  2004/05/10 13:07:22  maulani
 * - Add level to audit trail
 * - Clean up audit trail text
 *
 * Revision 1.10  2004/04/22 11:28:14  braverock
 * - move $rst->close() inside result loop for division lookup
 *
 * Revision 1.9  2004/04/17 16:03:45  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.8  2004/04/16 22:20:55  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.7  2004/01/26 19:13:34  braverock
 * - added company division fields
 * - added phpdoc
 */
?>
