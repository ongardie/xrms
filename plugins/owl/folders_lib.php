<?php
/**
 * Form for creating a new folder
 *
 * $Id: folders_lib.php,v 1.1 2005/11/09 19:24:15 daturaarutad Exp $
 */

/*
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
*/


function GetFolders($on_what_table, $on_what_id) {

	$con = get_xrms_dbconnection();

	if($on_what_table && $on_what_id) {
		$sql = "SELECT * FROM folders WHERE on_what_table = '$on_what_table' AND on_what_id = '$on_what_id'";
	} else {
		$sql = "SELECT * FROM folders";
	}

    $rst = $con->execute($sql);

	$return = array();

    if(!$rst) {
        db_error_handler($con, $sql);
    } else {
        while(!$rst->EOF) {
            $return[] = $rst->fields;
			$rst->MoveNext();
		}
	}

	return $return;
}




/**
 * $Log: folders_lib.php,v $
 * Revision 1.1  2005/11/09 19:24:15  daturaarutad
 * folder functions
 *
 */
?>
