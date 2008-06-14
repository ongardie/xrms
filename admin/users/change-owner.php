<?php
/**
 * admin/users/change-owner.php - Save new password
 *
 * Form to enter a new password for a user
 * @todo - add javascript validation on the save.
 *
 * $Id: change-owner.php,v 1.8 2008/06/14 13:36:36 randym56 Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

//
// become Admin aware - Don't accept the user to edit from the URL
// for non-Admin types.
//
if ( check_user_role(false, $_SESSION['session_user_id'], 'Administrator')) {
  getGlobalVar($edit_user_id, 'edit_user_id');
} else {
  $edit_user_id = $session_user_id;
}

//connect to the database
$con = get_xrms_dbconnection();
getGlobalVar($msg, 'msg');

$page_title = _("Change Record Owner");
start_page($page_title, true, $msg);

$current_user_menu = get_user_menu($con, '', false, 'current_user_id');
$new_user_id = get_user_menu($con, '', false, 'new_user_id');

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
                    	<?php echo _("Select Tables to Change"); ?>
                    </td>
                <tr>
                    <td>     
                   	<?php echo $current_user_menu ;
					echo "</td><td>";
					echo $new_user_id; ?>
                    <td>
                    	<input type="checkbox" name="companies" value="1" checked>Companies<br>
                    	<input type="checkbox" name="contacts" value="1" checked>Contacts<br>
                        <input type="checkbox" name="activities" value="1" checked>Activities<br>
                        <input type="checkbox" name="campaigns" value="1" checked>Campaigns<br>
                        <input type="checkbox" name="opportunities" value="1" checked>Opportunities<br>
                        <input type="checkbox" name="cases" value="1" checked>Cases<br>
                        <input type=submit name="Change" value="<?php echo _("Change All"); ?>"><BR>
                        <input type=submit name="Change" value="<?php echo _("Change Selected (all)"); ?>"><BR>
                        <input type=submit name="Change" value="<?php echo _("Change Selected (none)"); ?>">        
                    </td>
                </tr>
            </table>
        </form>
        <?php 
        	echo _("Change all : This will change the owner of all records in selected tables.");
			echo "<BR>";
        	echo _("Change Selected : This will change the owner of selected company records related to all tables selected above (except campaigns)...");
			echo "<BR>"; 
        	echo _("&nbsp;&nbsp;All -> All companies are checked.");
			echo "<BR>"; 
        	echo _("&nbsp;&nbsp;None -> No companies are checked.");
			echo "<BR>"; 
         ?>
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
 *Revision 1.8  2008/06/14 13:36:36  randym56
 *- Add Contacts table to list of tables.
 *- Add table select options.
 *
 *Revision 1.7  2006/12/03 20:18:46  braverock
 *- fix mistranslated strings
 *
 *Revision 1.6  2006/07/29 19:48:27  jnhayart
 *Release with capabilitie to select all or none on new screen
 *need add  "Change Selected (all)" and "Change Selected (none)" to langage file
 *change order of company list ( Crm_Statut then Company_Name )
 *
 *Revision 1.5  2006/04/11 01:42:45  vanmer
 *- changed the change owner application to use ACL administrator check instead of SESSION variable check
 *- added ability to selectively change ownership by company
 *- changed all variables from GET/POST to use getGlobalVar
 *- added extra error handling
 *- changes provided by and inspired by patches thanks to Jean-Noël HAYART
 *
 *Revision 1.4  2006/01/02 22:09:39  vanmer
 *- changed to use centralized dbconnection function
 *
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
