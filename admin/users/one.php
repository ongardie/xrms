<?php
/**
 * Edit the details for one user
 *
 * $Id: one.php,v 1.16 2004/07/25 19:14:59 johnfawcett Exp $
 */

//include required files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$edit_user_id = $_GET['edit_user_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from users where user_id = $edit_user_id";

$rst = $con->execute($sql);

if ($rst) {

    $user_contact_id = $rst->fields['user_contact_id'];
    $new_username    = $rst->fields['username'];
    $first_names     = $rst->fields['first_names'];
    $last_name       = $rst->fields['last_name'];
    $email           = $rst->fields['email'];
    $role_id         = $rst->fields['role_id'];
    $gmt_offset      = $rst->fields['gmt_offset'];
    $language        = $rst->fields['language'];

    $rst->close();
}

$sql2 = "select role_pretty_name, role_id from roles where role_record_status = 'a' order by role_id";
$rst = $con->execute($sql2);
$role_menu = $rst->getmenu2('role_id', $role_id, false);
$rst->close();

if($my_company_id) {
    $sql = "select " . $con->Concat("last_name", "', '", "first_names") . " AS contact_name,
            contact_id
            FROM contacts
            WHERE company_id = $my_company_id
            AND contact_record_status = 'a'
            ORDER BY contact_name";
    $rst = $con->execute($sql);
    $contact_menu = $rst->getmenu2('user_contact_id', $user_contact_id, true);
}

$con->close();

$page_title = _("User Details") . ': ' . $first_names . ' ' . $last_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=edit_user_id value="<?php  echo $edit_user_id; ?>">

        <input type=hidden name=user_contact_id value="<?php  echo $user_contact_id; ?>">
        <input type=hidden name=role_id value="<?php  echo $role_id; ?>">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit User Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Role"); ?></td>
                <td class=widget_content_form_element><?php  echo $role_menu; ?></td>
            </tr>
<?php
    if($my_company_id) {
?>
            <tr>
                <td class=widget_label_right><?php echo _("Contact"); ?></td>
                <td class=widget_content_form_element><?php  echo $contact_menu; ?></td>
            </tr>
<?php 
    }
 ?>
            <tr>
                <td class=widget_label_right><?php echo _("Last Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=30 name=last_name value="<?php  echo $last_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("First Names"); ?></td>
                <td class=widget_content_form_element><input type=text size=30 name=first_names value="<?php  echo $first_names; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Username"); ?></td>
                <td class=widget_content_form_element><input type=text name=new_username value="<?php  echo $new_username; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("E-Mail"); ?></td>
                <td class=widget_content_form_element><input type=text size=40 name=email value="<?php  echo $email; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Language"); ?></td>
                <td class=widget_content_form_element>English</td></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("GMT Offset"); ?></td>
                <td class=widget_content_form_element><input type=text size=5 name=gmt_offset value="<?php  echo $gmt_offset; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>">
                    <input class=button type=button onclick="javascript: location.href='change-password.php?edit_user_id=<?php echo $edit_user_id ?>';" value="<?php echo _("Change Password"); ?>">
                </td>
            </tr>
        </table>
        </form>

        <form action=delete.php method=post onsubmit="javascript: return confirm('<?php echo _("Delete User?"); ?>');">
        <input type=hidden name=edit_user_id value="<?php  echo $edit_user_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Delete User"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>
                <?php echo _("Click the button below to permanently remove this item."); ?>
                <p><?php echo _("Note: This action CANNOT be undone!"); ?></p>
                <p><input class=button type=submit value="<?php echo _("Delete"); ?>"></p>
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
 * $Log: one.php,v $
 * Revision 1.16  2004/07/25 19:14:59  johnfawcett
 * - reinserted ? in gettext string - needed by some languages
 * - standardized delete text and button
 *
 * Revision 1.15  2004/07/25 16:02:12  johnfawcett
 * - unified page title
 * - removed punctuation from gettext strings
 *
 * Revision 1.14  2004/07/20 12:48:50  cpsource
 * - Require 'Admin' to run.
 *
 * Revision 1.13  2004/07/20 11:40:06  cpsource
 * - Fixed multiple errors
 *    misc undefined variables being used, g....
 *    non Admin users could end up at some.php and effect other users
 *    made self.php goto self-2.php instead of edit-2.php
 *    non Admin users can now admin their own user name only.
 *    added a successful update promit to private/index.php
 *
 * Revision 1.12  2004/07/20 10:46:46  cpsource
 * - Fixed syntax error at line 59
 *
 * Revision 1.11  2004/07/16 23:51:38  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.10  2004/07/16 13:55:08  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.9  2004/07/13 18:16:16  neildogg
 * - Add admin support to allow a contact to be tied to the user
 *
 * Revision 1.8  2004/07/13 13:24:05  braverock
 * - change user_type_id to role_id
 *
 * Revision 1.7  2004/06/14 22:50:14  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.6  2004/05/17 17:23:43  braverock
 * - change $username to not conflict when register_globals is on (?!?)
 *   - fixed SF bug 952670 - credit to jmaguire123 and sirjo for troubleshooting
 *
 * Revision 1.5  2004/05/13 16:36:46  braverock
 * - modified to work safely even when register_globals=on
 *   (!?! == dumb administrators ?!?)
 * - changed $user_id to $edit_user_id to avoid security collisions
 *   - fixes multiple reports of user role switching on user edits.
 *
 * Revision 1.4  2004/04/16 22:18:27  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.3  2004/03/15 15:39:05  braverock
 * - properly set role_id in user edit page
 *  - fixes SF bug 876781
 * - add phpdoc
 */
?>
