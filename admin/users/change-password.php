<?php
/**
 * admin/users/change-password.php - Save new password
 *
 * Form to enter a new password for a user
 * @todo - add javascript validation on the save.
 *
 * $Id: change-password.php,v 1.11 2005/11/01 02:23:14 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
getGlobalVar($msg, 'msg');
//
// become Admin aware - Don't accept the user to edit from the URL
// for non-Admin types.
//
if (check_user_role(false, $session_user_id, 'Administrator')) {
  $edit_user_id = $_GET['edit_user_id'];
} else {
  $edit_user_id = $session_user_id;
}

$page_title = _("Change Password");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

                <form action="change-password-2.php" method="post">
                <input type=hidden name=edit_user_id value="<?php echo $edit_user_id; ?>">
                <table class=widget cellspacing=1>
                        <tr>
                                <td class=widget_header colspan=4><?php echo _("Change Password"); ?></td>
                        </tr>
                        <tr>
                                <td class=widget_label_right><?php echo _("New Password"); ?></td>
                                <td class=widget_content_form_element><input type=password size=30 name=password></td>
                        </tr>
                        <tr>
                                <td class=widget_label_right><?php echo _("Confirm New Password"); ?></td>
                                <td class=widget_content_form_element><input type=password size=30 name=confirm_password></td>
                        </tr>
                        <tr>
                                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
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
 *$Log: change-password.php,v $
 *Revision 1.11  2005/11/01 02:23:14  vanmer
 *- added output of msg to change password
 *
 *Revision 1.10  2005/07/22 15:45:24  braverock
 *- add additional result set error handling and more informative error msgs
 *- remove trailing whitespace
 *
 *Revision 1.9  2005/05/31 20:28:29  vanmer
 *- altered to use ACL check instead of Admin role check to control password change
 *
 *Revision 1.8  2004/07/20 12:45:22  cpsource
 *- Allow non-Admin users to change their passwords, but do so
 *  in a secure manner.
 *
 *Revision 1.7  2004/07/16 23:51:38  cpsource
 *- require session_check ( 'Admin' )
 *
 *Revision 1.6  2004/07/16 13:55:07  braverock
 *- localize strings for i18n translation support
 *  - applies modified patches from Sebastian Becker (hyperpac)
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
 *Revision 1.3  2004/04/16 22:18:26  maulani
 *- Add CSS2 Positioning
 *
 *Revision 1.2  2004/03/12 15:37:07  maulani
 *- Require new passwords be entered twice for validation
 *- Add phpdoc
 *
 */
?>