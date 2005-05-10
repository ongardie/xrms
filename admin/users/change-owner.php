<?php
/**
 * admin/users/change-owner.php - Save new password
 *
 * Form to enter a new password for a user
 * @todo - add javascript validation on the save.
 *
 * $Id: change-owner.php,v 1.3 2005/05/10 13:34:12 braverock Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

//
// become Admin aware - Don't accept the user to edit from the URL
// for non-Admin types.
//
if ( 'Admin' != $_SESSION['role_short_name'] ) {
  $edit_user_id = $session_user_id;
} else {
  $edit_user_id = $_GET['edit_user_id'];
}

//connect to the database
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$page_title = _("Change Record Owner");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action="change-owner-2.php" method="post">
            <input type=hidden name=edit_user_id value="<?php echo $edit_user_id; ?>">
            <table class=widget cellspacing=1>
                <tr>
                    <td class=widget_header width=\"33%">
                        <?php echo _("Current Owner"); ?>
                    </td>
                    <td class=widget_header width=\"33%">
                        <?php echo _("New Owner"); ?>
                    </td>
                    <td class=widget_header width=\"33%">
                    </td>
                <tr>
                    <td>     
                                	<?php 
        $sql3 = "SELECT  username, user_id as current_user_id
        FROM users
        WHERE user_record_status = 'a'
        ORDER BY username";

		$rst3 = $con->execute($sql3);
if ($rst3) {
    echo $rst3->getmenu2('current_user_id', '', true, false, 0, 'style="font-size: x-small; border: outset; width: 80px;"');
    $rst3->close();
} else {
    db_error_handler ($con, $sql);
}

echo "</td><td>";

        $sql3 = "SELECT username, user_id as new_user_id
        FROM users
        WHERE user_record_status = 'a'
        ORDER BY username";

		$rst3 = $con->execute($sql3);
if ($rst3) {
    echo $rst3->getmenu2('new_user_id', '', true, false, 0, 'style="font-size: x-small; border: outset; width: 80px;"');
    $rst3->close();
} else {
    db_error_handler ($con, $sql);
}

?>
                    <td>
                        <input type=submit value="<?php echo _("Change"); ?>">        
                    </td>
                </tr>
            </table>
        </form>
        <?php echo _("This will change the owner of open activities, companies, campaigns, opportunities and cases."); ?>
    </div>

        <!-- right column //-->
    <div id="Sidebar">
                &nbsp;
    </div>
</div>

<?php

$con->close;

end_page();

/**
 *$Log: change-owner.php,v $
 *Revision 1.3  2005/05/10 13:34:12  braverock
 *- localized string patches provided by Alan Baghumian (alanbach)
 *
 *Revision 1.2  2005/03/28 17:49:10  gpowers
 *- limited to changing open activities and active records
 *
 *Revision 1.1  2005/03/28 17:04:20  gpowers
 *- Implemented "Change Record Owner" Function
 *

 *
 */
?>