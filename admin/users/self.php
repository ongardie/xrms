<?php
/**
 * admin/users/self.php - User self-administration page
 *
 * Users who do not have admin privileges can update their own
 * user record and password.
 *
 * $Id: self.php,v 1.18 2006/04/05 01:26:46 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check('', 'Update', true);
$user_id=$session_user_id;

getGlobalVar($msg, 'msg');

$con = get_xrms_dbconnection();

$sql = "select * from users where user_id = $session_user_id";

$rst = $con->execute($sql);

if ($rst) {

    $user_contact_id = $rst->fields['user_contact_id'];
    $role_id         = $rst->fields['role_id'];
    $new_username    = $rst->fields['username'];
    $first_names     = $rst->fields['first_names'];
    $last_name       = $rst->fields['last_name'];
    $email           = $rst->fields['email'];
    $gmt_offset      = $rst->fields['gmt_offset'];
    $language        = $rst->fields['language'];

    $rst->close();
}
//show_test_values($username, $last_name, $first_names, $session_user_id, $user_id);

$user_preferences_table=get_user_preferences_table($con);
require_once('user_roles_sidebar.php');
$sidebar_rows = $user_role_sidebar . $sidebar_rows;

$page_title = _("One User") . " : " . "$first_names $last_name";
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=self-2.php method=post>
        <input type=hidden name=edit_user_id value="<?php  echo $session_user_id; ?>">

        <input type=hidden name=user_contact_id value="<?php  echo $user_contact_id; ?>">
        <input type=hidden name=role_id value="<?php  echo $role_id; ?>">
        <input type=hidden name=new_username value="<?php  echo $new_username; ?>">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit User Information"); ?></td>
            </tr>
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
                <td class=widget_content_form_element><?php  echo $new_username; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("E-Mail"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text size=40 name=email value="<?php  echo $email; ?>">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("GMT Offset"); ?></td>
                <td class=widget_content_form_element><input type=text size=5 name=gmt_offset value="<?php  echo $gmt_offset; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                  <input class=button type=submit value="<?php echo _("Save Changes"); ?>">
                  <input class=button type=button onclick="javascript: location.href='change-password.php';" value="<?php echo _("Change Password"); ?>">
                </td>
            </tr>
        </table>
        </form>

         <form action='user_prefs.php' method=POST>
          <?php echo $user_preferences_table; ?>      
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
 *$Log: self.php,v $
 *Revision 1.18  2006/04/05 01:26:46  vanmer
 *- ensure that user has update permission on self
 *
 *Revision 1.17  2006/01/02 22:09:39  vanmer
 *- changed to use centralized dbconnection function
 *
 *Revision 1.16  2005/07/06 17:20:36  vanmer
 *- changed to use function to get user preferences table
 *
 *Revision 1.15  2005/06/24 23:55:19  vanmer
 *- added translation to output of title and descrption of preferences
 *
 *Revision 1.14  2005/06/07 21:45:45  vanmer
 *- included user roles sidebar for users self-administration, in read-only mode
 *
 *Revision 1.13  2005/05/10 13:34:13  braverock
 *- localized string patches provided by Alan Baghumian (alanbach)
 *
 *Revision 1.12  2005/05/06 00:31:30  vanmer
 *- added user preference piece for self.php to allow users to alter their preferences
 *
 *Revision 1.11  2004/09/21 19:21:19  introspectshun
 *- Finished localizing strings for i18n compatibility
 *
 *Revision 1.10  2004/07/20 12:45:22  cpsource
 *- Allow non-Admin users to change their passwords, but do so
 *  in a secure manner.
 *
 *Revision 1.9  2004/07/20 11:40:06  cpsource
 *- Fixed multiple errors
 *   misc undefined variables being used, g....
 *   non Admin users could end up at some.php and effect other users
 *   made self.php goto self-2.php instead of edit-2.php
 *   non Admin users can now admin their own user name only.
 *   added a successful update promit to private/index.php
 *
 *Revision 1.8  2004/07/16 23:51:38  cpsource
 *- require session_check ( 'Admin' )
 *
 *Revision 1.7  2004/07/16 13:55:08  braverock
 *- localize strings for i18n translation support
 *  - applies modified patches from Sebastian Becker (hyperpac)
 *
 *Revision 1.6  2004/07/13 13:24:05  braverock
 *- change user_type_id to role_id
 *
 *Revision 1.5  2004/06/14 22:50:14  introspectshun
 *- Add adodb-params.php include for multi-db compatibility.
 *- Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 *Revision 1.4  2004/05/13 16:36:46  braverock
 *- modified to work safely even when register_globals=on
 *  (!?! == dumb administrators ?!?)
 *- changed $user_id to $edit_user_id to avoid security collisions
 *  - fixes multiple reports of user role switching on user edits.
 *
 *Revision 1.3  2004/05/10 20:54:31  maulani
 *- Fix bug 951490.  Unprivileged users will now return to the home screen
 *  after modifying their user records.
 *
 *Revision 1.2  2004/04/16 22:18:27  maulani
 *- Add CSS2 Positioning
 *
 *Revision 1.1  2004/03/12 15:46:51  maulani
 *Temporary change for use until full access control is implemented
 *- Block non-admin users from the administration screen
 *- Allow all users to modify their own user record and password
 *- Add phpdoc
 */
?>