<?php
/**
 * Save changes to divisions
 *
 * $Id: edit-division-2.php,v 1.14 2006/01/02 22:56:27 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

getGlobalVar($return_url, 'return_url');
$on_what_table='company_division';
$division_id = $_POST['division_id'];
$on_what_id=$division_id;

$session_user_id = session_check('','Update');

$company_id = $_POST['company_id'];
$address_id = $_POST['address_id'];
$division_name = $_POST['division_name'];
$description = $_POST['description'];

$use_pretty_address = ($use_pretty_address == 'on') ? "'t'" : "'f'";


    $con = get_xrms_dbconnection();

//    $con->debug=1;

    $sql = "SELECT * FROM company_division WHERE division_id = $division_id";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['division_id'] = $division_id;
    $rec['company_id'] = $company_id;
    $rec['address_id'] = $address_id;
    $rec['division_name'] = $division_name;
    $rec['description'] = $description;
    $rec['last_modified_at'] = time();
    $rec['last_modified_by'] = $session_user_id;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    do_hook_function('edit_division_process', $rec);

if (!$return_url) {
    $return_url="divisions.php?company_id=$company_id&msg=saved";
}

header("Location: $return_url");

/**
 * $Log: edit-division-2.php,v $
 * Revision 1.14  2006/01/02 22:56:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.13  2005/08/20 23:56:12  braverock
 * - back out incorrect comments from 1.11 commit
 *
 * Revision 1.12  2005/08/20 23:43:05  vanmer
 * - changed to use edit-division-process hook again instead of incorrectly added new-division process
 *
 * Revision 1.11  2005/08/10 19:47:00  jswalter
 * !!!incorrect changes backed out!!!
 *
 * Revision 1.10  2005/08/04 19:30:24  vanmer
 * - changed to use return_url for return
 * - changed default return from company one page to company divisions page
 *
 * Revision 1.9  2005/02/08 17:16:10  vanmer
 * - added company id to edit division collection
 *
 * Revision 1.8  2005/02/08 17:13:58  vanmer
 * - switched to pass correct collection to hook function
 *
 * Revision 1.7  2005/02/08 16:59:43  vanmer
 * - added hook for processing division edit
 *
 * Revision 1.6  2005/01/28 23:05:29  braverock
 * - change return url to send you back to companies/one.php
 *
 * Revision 1.5  2005/01/13 18:23:15  vanmer
 * - Basic ACL changes to allow create/delete functionality to be restricted
 *
 * Revision 1.4  2005/01/06 21:53:22  vanmer
 * - added address_id to new/edit-2 retrieve/store methods, to specify an address for a division
 *
 * Revision 1.3  2004/06/12 17:10:24  gpowers
 * - removed DBTimeStamp() calls for compatibility with GetInsertSQL() and
 *   GetUpdateSQL()
 *
 * Revision 1.2  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.1  2004/01/26 19:18:02  braverock
 * - added company division pages and fields
 * - added phpdoc
 *
 */
?>
