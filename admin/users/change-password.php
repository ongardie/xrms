<?php
/**
 * admin/users/change-password.php - Save new password
 *
 * Form to enter a new password for a user
 * @todo - add javascript validation on the save.
 *
 * $Id: change-password.php,v 1.5 2004/06/14 22:50:14 introspectshun Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$edit_user_id = $_GET['edit_user_id'];

$page_title = "Change Password";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

                <form action="change-password-2.php" method="post">
                <input type=hidden name=edit_user_id value="<?php echo $edit_user_id; ?>">
                <table class=widget cellspacing=1>
                        <tr>
                                <td class=widget_header colspan=4>Change Password</td>
                        </tr>
                        <tr>
                                <td class=widget_label_right>New Password</td>
                                <td class=widget_content_form_element><input type=password size=30 name=password></td>
                        </tr>
                        <tr>
                                <td class=widget_label_right>Confirm New Password</td>
                                <td class=widget_content_form_element><input type=password size=30 name=confirm_password></td>
                        </tr>
                        <tr>
                                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"></td>
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
