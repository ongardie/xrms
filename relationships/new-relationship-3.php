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

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select to_what_id
    from relationships
    where relationship_type_id = $relationship_type_id
    and $working_direction" . "_what_id=$on_what_id
    and $opposite_direction" . "_what_id=$on_what_id2
    and relationship_status='a'";
$rst = $con->execute($sql);
//$con->debug=1;

if ($rst) {
    if($rst->rowcount() == 0) {
        //save to database
        $rec = array();
        $rec["$working_direction" . "_what_id"] = $on_what_id;
        $rec["$opposite_direction" . "_what_id"] = $on_what_id2;
        $rec['relationship_type_id'] = $relationship_type_id;
        $rec['established_at'] = time();

        $tbl = 'relationships';
        $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
        $con->execute($ins);

    }
} else {
    db_error_handler ($con, $sql);
}

$con->close();

if($return) {
    $_POST['working_direction'] = $real_working_direction;
    require("new-relationship.php");
}
else {
    header("Location: .." . $return_url);
}

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
