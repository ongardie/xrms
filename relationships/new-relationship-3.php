<?php
/**
 * Associated Relationships
 *
 * Add relationship from new-relationship-2.php by submitted id
 *
 * @author Neil Roberts
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once($include_directory . 'utils-relationships.php');

$session_user_id = session_check();

$return = ($_POST['return']) ? true : false;
$on_what_id = $_POST['on_what_id'];
$on_what_id2 = $_POST['on_what_id2'];
$working_direction = $_POST['working_direction'];
$real_working_direction = $_POST['real_working_direction'];
$return_url = $_POST['return_url'];
$relationship_type_id = $_POST['relationship_type_id'];

if($working_direction == "from") {
    $opposite_direction = "to";
}
else {
    $opposite_direction = "from";
}

$con = get_xrms_dbconnection();

$ret=add_relationship_from_directions($con, $relationship_type_id, $working_direction, $opposite_direction, $on_what_id, $on_what_id2);

if($return) {
    $_POST['working_direction'] = $real_working_direction;
    require("new-relationship.php");
}
else {
    header("Location: .." . $return_url);
}
$con->close();
/**
 * Revision 1.3  2004/07/07 21:53:13  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.2  2004/07/05 22:13:54  introspectshun
 * - Now uses GetInsertSQL
 * - Include adodb-params.php
 *
 * Revision 1.1  2004/07/01 19:48:10  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 */
?>
