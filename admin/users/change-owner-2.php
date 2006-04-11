<?php
/**
 * admin/users/change-password-2.php - Save new password
 *
 * Check that new password entries are identical
 * Then save in the database.
 *
 * $Id: change-owner-2.php,v 1.4 2006/04/11 01:42:45 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-users.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

//
// become Admin aware - Don't accept the user to edit from the URL
// or from POST for non-Admin types.
//
if ( check_user_role(false, $_SESSION['session_user_id'], 'Administrator') ) {
  getGlobalVar($edit_user_id, 'edit_user_id');
} else {
  $edit_user_id = $session_user_id;
}

getGlobalVar($current_user_id,'current_user_id');
getGlobalVar($new_user_id, 'new_user_id');

if ( !$current_user_id or !$new_user_id ) {

    $msg = urlencode(_("Error: No user selected"));
    header("Location: change-owner.php?msg=" . $msg);
    exit;	
}

getGlobalVar($Change_Type, 'Change');

$con = get_xrms_dbconnection();

//uncomment the debug line to see what's going on with the query
// $con->debug = 1;
if ($Change_Type == "Change All") {
	
$tables = array('companies', 'activities', 'campaigns', 'opportunities', 'cases');

foreach ($tables as $table) {
	
	$singular = make_singular ($table);
	
    $sql = "SELECT * FROM " . $table . "
            WHERE user_id = '" . $current_user_id . "'
 			AND " . $singular . "_record_status = 'a' ";
 			
    switch ($table) {
    	case "activities":
    	$sql .= "AND activity_status = 'o'";
    	break;
    }
    
    $rst = $con->execute($sql);

    if ($rst) {
    	if (!$rst->EOF) {
	    	$rec = array();
    		$rec['user_id'] = $new_user_id;
    
    		$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    		$con->execute($upd);

    		add_audit_item($con, $session_user_id, 'changed owner', $table, $current_user_id, 1);
    	}
   	} else {
    	    db_error_handler ($con, $sql);
    }
}

    $con->close();

    $msg = urlencode(_("Record Owners Changed"));
    
    header("Location: ../index.php?msg=" . $msg);
}
else
{
$company_rows = "";

      $sql = "select c.company_id as company_id, stat.crm_status_pretty_name as CrmStatus, c.company_name as company_name, addr.city as company_city "
           . "from companies c, addresses addr, users u, crm_statuses as stat "
           . "where c.user_id = " . $current_user_id . " and c.default_primary_address = addr.address_id and c.crm_status_id = stat.crm_status_id and c.company_record_status = 'a' group by company_id order by company_name";

$rst = $con->execute($sql);
if ($rst) {
    if ($rst->EOF) {
    	//no companies found, so redirect
	$user=get_xrms_user($con, false, $current_user_id);
	$username=$user['username'];
	$msg=urlencode(_("No companies owned by $username."));
	Header("Location: change-owner.php?msg=$msg");
	exit;
    }
    while (!$rst->EOF) {
        $company_rows .= "<tr>";
        $company_rows .= "<td class=widget_content_form_element><input type=checkbox name=array_of_company[]] value=" . $rst->fields['company_id'] . " checked></td>";
        $company_rows .= "<td class=widget_content>" . $rst->fields['company_name'] . "</td>";
        $company_rows .= "<td class=widget_content>" . $rst->fields['CrmStatus'] . "</td>";
        $company_rows .= "<td class=widget_content>" . $rst->fields['company_city'] . "</td>";
        $company_rows .= "</tr>\n";
        $rst->movenext();
    }
    $rst->close();
} else {
   db_error_handler($con, $sql);
}
getGlobalVar($msg, 'msg');
$page_title = _("Select company to move");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=change-owner-3.php method=post>
        <input type=hidden name=current_user_id value="<?php echo $current_user_id; ?>">
        <input type=hidden name=new_user_id value="<?php echo $new_user_id; ?>">
        
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=6><?php echo _("Confirm company"); ?></td>
            </tr>
            <tr>
                <td class=widget_label>&nbsp;</td>
                <td class=widget_label><?php echo _("Company"); ?></td>
                <td class=widget_label><?php echo _("CrmStatus"); ?></td>
                <td class=widget_label><?php echo _("City"); ?></td>
            </tr>
            <?php  echo $company_rows ; ?>
            <tr>
                <td class=widget_content_form_element colspan=6>
                <input type=submit class=button value="<?php echo _("Continue"); ?>"></td>
            </tr>
        </table>
        </form>
</div>

<?php

end_page();
}

/**
 *$Log: change-owner-2.php,v $
 *Revision 1.4  2006/04/11 01:42:45  vanmer
 *- changed the change owner application to use ACL administrator check instead of SESSION variable check
 *- added ability to selectively change ownership by company
 *- changed all variables from GET/POST to use getGlobalVar
 *- added extra error handling
 *- changes provided by and inspired by patches thanks to Jean-Noël HAYART
 *
 *Revision 1.3  2006/01/02 22:09:39  vanmer
 *- changed to use centralized dbconnection function
 *
 *Revision 1.2  2005/03/28 17:49:02  gpowers
 *- limited to changing open activities and active records
 *
 *Revision 1.1  2005/03/28 17:04:20  gpowers
 *- Implemented "Change Record Owner" Function
 *
 *Revision 1.9  2004/07/20 12:45:21  cpsource
 *- Allow non-Admin users to change their passwords, but do so
 *  in a secure manner.
 *
 *Revision 1.8  2004/07/16 23:51:38  cpsource
 *- require session_check ( 'Admin' )
 *
 *Revision 1.7  2004/06/14 22:50:14  introspectshun
 *- Add adodb-params.php include for multi-db compatibility.
 *- Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 *Revision 1.6  2004/05/13 16:36:46  braverock
 *- modified to work safely even when register_globals=on
 *  (!?! == dumb administrators ?!?)
 *- changed $user_id to $edit_user_id to avoid security collisions
 *  - fixes multiple reports of user role switching on user edits.
 *
 *Revision 1.5  2004/05/10 13:07:20  maulani
 *- Add level to audit trail
 *- Clean up audit trail text
 *
 *Revision 1.4  2004/03/12 16:34:31  maulani
 *- Add audit trail
 *- Add phpdoc
 *
 *Revision 1.2  2004/03/12 15:37:07  maulani
 *- Require new passwords be entered twice for validation
 *- Add phpdoc
 */
?>