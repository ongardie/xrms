<?php
/**
 * Browser to the next Activity in the list
 *
 * @author Neil Roberts
 *
 * $Id: browse-next.php,v 1.5 2004/06/25 03:11:47 braverock Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

if($_GET['current_on_what_table']) {
  $activity_type = $_GET['current_activity_type_id'];
  $current_on_what_table = $_GET['current_on_what_table'];
  $pos = 0;
}
else {
  $next_to_check = $_SESSION['next_to_check'];
  $pos = $_SESSION['pos'];
  $activity_type = $_SESSION['activity_type'];
  $current_on_what_table = $_SESSION['current_on_what_table'];
}

$_SESSION['current_on_what_table'] = $current_on_what_table;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

if($current_on_what_table == "opportunities") {
    $current_on_what_table_singular = "opportunity";
}
elseif($current_on_what_table == "cases") {
    $current_on_what_table_singular = "case";
}

if(($pos > 0) and ($pos < sizeof($next_to_check))) {
    header("Location: one.php?activity_id=" . $next_to_check[$pos]);
    $pos++;
    $_SESSION['pos'] = $pos;
}
else {
    $next_to_check = array();
    $more_to_check = true;
    //Loop until all solutions are exhausted
    while($more_to_check) {

        $next_to_check = array();

        //Only grab activities is $pos has been reset (ie on first load, or after the category has changed)
        if($pos == 0) {
            //Find items within activity_type_id, that have expired
            //Important because it's sorted by lateness first, not probability
            $sql = "select a.activity_id
                from activities as a ";
            if (strlen($current_on_what_table)>0) {
                $sql .=   " , $current_on_what_table ";
            }
            $sql .= "
                where a.activity_status = 'o'
                and a.activity_record_status='a'
                and a.ends_at < " . $con->DBTimestamp(time()) . "
                and a.user_id = $session_user_id ";

            if ($on_what_table == "opportunities") {
                $sql .= ", o.probability desc";
            }
            if (strlen($activity_type)>0) {
                $sql .= "\n and a.activity_type_id=$activity_type";
            }
            if (strlen($current_on_what_table)>0) {
                $sql .= "\n and a.on_what_table='$current_on_what_table'
                            and a.on_what_id="
                            . $current_on_what_table_singular . "_id ";
            }

            $sql .= "\n order by a.ends_at asc";

            $rst = $con->execute($sql);
            if(!$rst) {
                $more_to_check = false;
                db_error_handler($con, $sql);
            }
            elseif($rst->rowcount() > 0) {
                while(!$rst->EOF) {
                    $next_to_check[] = $rst->fields['activity_id'];
                    $rst->movenext();
                }
                $more_to_check = false;
                $rst->close();
            }
            else {
                $more_to_check = false;
            }

            //Get the remaining activities, sorting by probability(if applicable), then date
            $sql = "select a.activity_id
                from activities as a ";
            if (strlen($current_on_what_table)>0) {
                $sql .=   " , $current_on_what_table ";
            }
            $sql .= "
                where a.activity_status = 'o'
                and a.activity_record_status='a'
                and a.ends_at >= " . $con->DBTimestamp(time()) . "
                and a.user_id = $session_user_id ";

            if ($on_what_table == "opportunities") {
                $sql .= ", o.probability desc";
            }
            if (strlen($activity_type)>0) {
                $sql .= "\n and a.activity_type_id=$activity_type";
            }
            if (strlen($current_on_what_table)>0) {
                $sql .= "\n and a.on_what_table='$current_on_what_table'
                            and a.on_what_id="
                            . $current_on_what_table_singular . "_id ";
            }

            if($current_on_what_table == "opportunities") {
                $sql .= " order by probability desc, a.ends_at asc";
            }
            else {
                $sql .= " order by a.ends_at asc";
            }
            $rst = $con->execute($sql);
            if(!$rst) {
                $more_to_check = false;
                db_error_handler($con, $sql);
            }
            elseif($rst->rowcount() > 0) {
                while(!$rst->EOF) {
                    $next_to_check[] = $rst->fields['activity_id'];
                    $rst->movenext();
                }
                $more_to_check = false;
                $rst->close();
            }
            else {
                $more_to_check = false;
            }

        }

        if($more_to_check) {
            $sql = "select activity_type_id
                from activity_types ";
            if ($activity_type > 0) {
                $sql .= "\n where activity_type_id < $activity_type ";
            }
            $sql .= "\n order by activity_type_id desc";
            $rst = $con->execute($sql);
            if(!$rst) {
                db_error_handler($con, $sql);
            }
            elseif($rst->rowcount() > 0) {
                $pos = 0;
                $activity_type = $rst->fields['activity_type_id'];
                $rst->close();
            }
            else {
                $more_to_check = false;
                header("Location: some.php");
            }
        }
        else {
            $_SESSION['next_to_check'] = $next_to_check;
            $_SESSION['pos'] = 1;
            header("Location: one.php?activity_id=" . $next_to_check[0]);
        }
    }
}

$_SESSION['activity_type'] = $activity_type;

$con->close();

/**
 * $Log: browse-next.php,v $
 * Revision 1.5  2004/06/25 03:11:47  braverock
 * - add error handling to avoid empty result sets and endless loops
 *
 * Revision 1.4  2004/06/24 20:19:01  introspectshun
 * - Updated time formats in SELECTs to use DBTimestamp()
 *
 */
?>
