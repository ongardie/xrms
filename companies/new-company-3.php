<?php
/**
 * Associated Companies
 *
 * Add company from new-company-2.php by submitted company_id
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

$relationship_name = $_POST['relationship_name'];
$from_what_table = $_POST['on_what_table'];
$to_what_table = $_POST['to_what_table'];
$from_what_id = $_POST['from_what_id'];
$to_what_id = $_POST['to_what_id'];
$on_what_id = $_POST['on_what_id'];
$return_url = $_POST['return_url'];
$relationship_type_id = $_POST['relationship_type_id'];

if($to_what_id) {
    $working_direction = "to";
    $opposite_direction = "from";
    $from_what_id = $on_what_id;
    $overall_id = $to_what_id;
}
else {
    $working_direction = "from";
    $opposite_direction = "to";
    $to_what_id = $on_what_id;
    $overall_id = $from_what_id;
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select to_what_id
    from relationships
    where relationship_type_id = $relationship_type_id
    and $working_direction" . "_what_id=$overall_id
    and $opposite_direction" . "_what_id=$on_what_id";
$rst = $con->execute($sql);

if ($rst) {
    if($rst->rowcount() == 0) {
        $sql = "SELECT * FROM relationships WHERE 1 = 2"; //select empty record as placeholder
        $rst = $con->execute($sql);

        $rec = array();
        $rec["$working_direction" . "_what_id"] = $overall_id;
        $rec["$opposite_direction" . "_what_id"] = $on_what_id;
        $rec['relationship_type_id'] = $relationship_type_id;
        $rec['established_at'] = time();

        $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
        $con->execute($ins);
        // $con->debug=1;

    }
} else {
    db_error_handler ($con, $sql);
}

$con->close();

header("Location: .." . $return_url);

/**
 * $Log: new-company-3.php,v $
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