<?php
/**
 * Edit a single contact.
 *
 * This screen allows the user to edit all the details of a contact.
 *
 * $Id: edit.php,v 1.22 2004/07/30 09:45:24 cpsource Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

$session_user_id = session_check();

$msg        = isset($_GET['msg']) ? $_GET['msg'] : '';
$contact_id = $_GET['contact_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

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
    $company_name = $rst->fields['company_name'];
    $last_name = $rst->fields['last_name'];
    $first_names = $rst->fields['first_names'];
    $summary = $rst->fields['summary'];
    $title = $rst->fields['title'];
    $description = $rst->fields['description'];
    $date_of_birth = $rst->fields['date_of_birth'];
    $gender = $rst->fields['gender'];
    $salutation = $rst->fields['salutation'];
    $email = $rst->fields['email'];
    $work_phone = $rst->fields['work_phone'];
    $cell_phone = $rst->fields['cell_phone'];
    $home_phone = $rst->fields['home_phone'];
    $profile = $rst->fields['profile'];
    $fax = $rst->fields['fax'];
    $aol_name = $rst->fields['aol_name'];
    $yahoo_name = $rst->fields['yahoo_name'];
    $msn_name = $rst->fields['msn_name'];
    $interests = $rst->fields['interests'];
    $custom1 = $rst->fields['custom1'];
    $custom2 = $rst->fields['custom2'];
    $custom3 = $rst->fields['custom3'];
    $custom4 = $rst->fields['custom4'];
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

$salutation_menu = build_salutation_menu($salutation);

$sql = "select count(contact_id) as contact_count from contacts where company_id = $company_id and contact_record_status = 'a'";
$rst = $con->execute($sql);
$contact_count = $rst->fields['contact_count'];
$rst->close();

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $session_user_id, false);
$rst->close();

$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a'";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', '', false);
$rst->close();

$sql = "select address_name, address_id from addresses where company_id = $company_id and address_record_status = 'a' order by address_id";
$rst = $con->execute($sql);
$address_menu = $rst->getmenu2('address_id', $address_id, true);
$rst->close();
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
            <tr>
                <td class=widget_label_right><?php echo _("Company"); ?></td>
                <td class=widget_content_form_element><a href="../companies/one.php?company_id=<?php echo $company_id; ?>"><?php echo $company_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Division"); ?></td>
                <td class=widget_content_form_element><?php echo $division_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address"); ?></td>
                <td class=widget_content_form_element><?php echo $address_menu; ?></td>
            </tr>
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
                <td class=widget_label_right><?php echo _("Summary"); ?></td>
                <td class=widget_content_form_element><input type=text name=summary value="<?php echo $summary; ?>" size=35></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Title"); ?></td>
                <td class=widget_content_form_element><input type=text name=title value="<?php echo $title; ?>" size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><input type=text name=description value='<?php echo $description; ?>' size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("E-Mail"); ?></td>
                <td class=widget_content_form_element><input type=text name=email value='<?php echo $email; ?>' size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Work Phone"); ?></td>
                <td class=widget_content_form_element><input type=text name=work_phone value='<?php echo $work_phone; ?>' size=30></td>
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
                <td class=widget_label_right><?php echo _("AOL Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=aol_name value='<?php echo $aol_name; ?>' size=25></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Yahoo Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=yahoo_name value='<?php echo $yahoo_name; ?>' size=25></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("MSN Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=msn_name value='<?php echo $msn_name; ?>' size=25></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Interests"); ?></td>
                <td class=widget_content_form_element><input type=text name=interests size=35 value='<?php echo $interests; ?>'></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $contact_custom1_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom1 size=35 value="<?php echo $custom1; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $contact_custom2_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom2 size=35 value="<?php echo $custom2; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $contact_custom3_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom3 size=35 value="<?php echo $custom3; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $contact_custom4_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom4 size=35 value="<?php  echo $custom4; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px><?php echo _("Profile"); ?></td>
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
		  confGoTo( $quest, $button, $to_url );
		}
?>
                    <input class=button type=button value="<?php echo _("Transfer"); ?>" onclick="javascript: location.href='transfer.php?contact_id=<?php echo $contact_id; ?>';">
                    <input class=button type=button value="<?php echo _("Edit Address"); ?>" onclick="javascript: location.href='edit-address.php?contact_id=<?php echo $contact_id; ?>';">

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
 *
 */
?>
