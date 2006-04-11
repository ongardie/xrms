<?php
/**
 * admin/users/change-owner-3.php - 
 *
 * Check that new password entries are identical
 * Then save in the database.
 *
 * $Id: change-owner-3.php,v 1.1 2006/04/11 01:42:45 vanmer Exp $
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
// or from POST for non-Admin types.
//

if (check_user_role(false, $_SESSION['session_user_id'], 'Administrator') ) {
  getGlobalVar($edit_user_id, 'edit_user_id');
} else {
  $edit_user_id = $session_user_id;
}

getGlobalVar($current_user_id, 'current_user_id');
getGlobalVar($new_user_id, 'new_user_id');

getGlobalVar($array_of_company, 'array_of_company');

getGlobalVar($Change_Type, 'Change');

if (is_array($array_of_company))
    $imploded_company = implode(',', $array_of_company);
else {
    $msg=urlencode( _("WARNING: No companies provided!"));
    Header("Location: change-owner-2.php?new_user_id=$new_user_id&current_user_id=$current_user_id&edit_user_id=$edit_user_id&msg=$msg");
    exit;
}
$con = get_xrms_dbconnection();

//uncomment the debug line to see what's going on with the query
// $con->debug = 1;
	
$tables = array('companies', 'activities', 'opportunities', 'cases');

foreach ($tables as $table) {
	
	$singular = make_singular ($table);
	
    $sql = "SELECT * FROM " . $table ;
    $sql .=	" WHERE user_id = '" . $current_user_id . "'
 			AND " . $singular . "_record_status = 'a' and company_id in (" . $imploded_company . ") ";
 			
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

/**
 * $Log: change-owner-3.php,v $
 * Revision 1.1  2006/04/11 01:42:45  vanmer
 * - changed the change owner application to use ACL administrator check instead of SESSION variable check
 * - added ability to selectively change ownership by company
 * - changed all variables from GET/POST to use getGlobalVar
 * - added extra error handling
 * - changes provided by and inspired by patches thanks to Jean-Noël HAYART
 *
**/
?>