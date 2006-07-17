<?php
/**
 * Edit the details for one user
 *
 * $Id: one.php,v 1.27 2006/07/17 06:10:53 vanmer Exp $
 */

//include required files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

getGlobalVar($msg, 'msg');
getGlobalVar($return_url, 'return_url');

if (!$return_url) {
    $return_url="some.php";
}

$edit_user_id = $_GET['edit_user_id'];

$con = get_xrms_dbconnection();

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
    $record_status  = $rst->fields['user_record_status'];
    if ($record_status=='a') $enabled=true;
    else $enabled=false;
    $rst->close();
}

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
$current_return_url=$return_url;
require_once('user_roles_sidebar.php');
$return_url=$current_return_url;
$user_preferences_table=get_user_preferences_table($con, $edit_user_id);

// make sure $sidebar_rows is defined
if ( !isset($sidebar_rows) ) {
  $sidebar_rows = '';
}

//call the sidebar hook
$sidebar_rows = do_hook_function('admin_user_edit_sidebar', $sidebar_rows);
$sidebar_rows = $user_role_sidebar . $sidebar_rows;
$con->close();

$page_title = _("User Details") . ': ' . $first_names . ' ' . $last_name;
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=return_url value="<?php echo $return_url; ?>">
        <input type=hidden name=edit_user_id value="<?php  echo $edit_user_id; ?>">

        <input type=hidden name=user_contact_id value="<?php  echo $user_contact_id; ?>">
        <input type=hidden name=role_id value="<?php  echo $role_id; ?>">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit User Information"); ?></td>
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
                <td class=widget_label_right><?php echo _("GMT Offset"); ?></td>
                <td class=widget_content_form_element><input type=text size=5 name=gmt_offset value="<?php  echo $gmt_offset; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Enabled"); ?></td>
                <td class=widget_content_form_element><input type=checkbox name=enabled value="YES" <?php if ($enabled) echo "CHECKED";?>></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>">
                    <input class=button type=button onclick="javascript: location.href='change-password.php?edit_user_id=<?php echo $edit_user_id ?>';" value="<?php echo _("Change Password"); ?>">
                </td>
            </tr>
        </table>
        </form>
         <form action='user_prefs.php' method=POST>
	  <input type=hidden name=return_url value="one.php?edit_user_id=<?php echo $edit_user_id; ?>">
          <?php echo $user_preferences_table; ?>
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

        <?php echo $sidebar_rows; ?>

    </div>
</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.27  2006/07/17 06:10:53  vanmer
 * - altered to allow admin to change user preferences from one.php page
 * - added parameters for user_id and return_url for user_prefs control page
 *
 * Revision 1.26  2006/04/16 17:49:52  johnfawcett
 * removed hardcoded language
 * added user preferences display / modification
 *
 * Revision 1.25  2006/01/02 22:09:39  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.24  2005/07/06 17:22:14  vanmer
 * - added return url when editing user data
 *
 * Revision 1.23  2005/06/15 18:25:40  vanmer
 * - added output of msg string on users/one.php page
 *
 * Revision 1.22  2005/06/07 21:35:15  vanmer
 * - changed to use sidebar include file instead of inline sidebar
 *
 * Revision 1.21  2005/05/25 17:05:03  vanmer
 * - removed roles table reference from users
 *
 * Revision 1.20  2005/05/18 05:50:02  vanmer
 * - added sidebar to manage user roles from user edit page
 *
 * Revision 1.19  2005/03/31 21:39:11  gpowers
 * - added sidebar plugin hook
 *
 * Revision 1.18  2005/02/10 23:46:13  vanmer
 * - added enabled flag to reflect user account status
 *
 * Revision 1.17  2005/01/13 17:56:13  vanmer
 * - added new ACL code to user management section
 *
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
