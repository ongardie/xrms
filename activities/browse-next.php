<?php
/**
 * Save the updated activity information to the database
 *
 *
 * $Id: browse-next.php,v 1.2 2004/06/15 18:08:49 introspectshun Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$current_activity_type_id = $_GET['current_activity_type_id'];
$pos = $_GET['pos'];
$current_on_what_table = $_GET['current_on_what_table'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

if($current_on_what_table == "opportunities") {
    $current_on_what_table_singular = "opportunity";
}
elseif($current_on_what_table == "cases") {
    $current_on_what_table_singular = "case";
}

//$next_to_check = $_SESSION['next_to_check'];
if(($pos > 0) and ($pos < sizeof($next_to_check))) {
    header("Location: one.php?activity_id=" . $next_to_check[$pos] . "&pos=" . $pos);
}
else {
    $next_to_check = array();
    $more_to_check = true;
    //Loop until all solutions are exhausted
    while($more_to_check) {

        if($pos ==  0) {
            //Find items within activity_type_id, that have expired
            //Important because it's sorted by lateness first, not probability
            $sql = "select a.activity_id
                from activities as a, $current_on_what_table
                where a.activity_type_id=$current_activity_type_id
                and a.activity_status = 'o'
                and a.activity_record_status='a'
                and a.ends_at < now()
                and a.user_id = $session_user_id
                and a.on_what_table='$current_on_what_table'
                and a.on_what_id=" . $current_on_what_table_singular . "_id
                order by a.ends_at asc";
            if($on_what_table == "opportunities") {
                $sql .= ", o.probability desc";
            }
            //Limit from current position to the end of the table

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
            }
            $rst->close();

            if($current_on_what_table == "opportunities") {
                //Find opportunity related items within activity_type_id
                $sql = "select a.activity_id
                    from activities a, opportunities o
                    where a.activity_type_id=$current_activity_type_id
                    and a.activity_status = 'o'
                    and a.activity_record_status='a'
                    and a.user_id = $session_user_id
                    and a.ends_at >= now()
                    and a.on_what_table = 'opportunities'
                    and a.on_what_id=o.opportunity_id
                    order by o.probability desc,
                    a.ends_at asc";

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
                }
                $rst->close();
            }

            $sql = "select a.activity_id
                from activities a, $current_on_what_table
                where a.activity_type_id=$current_activity_type_id
                and a.activity_status = 'o'
                and a.activity_record_status='a'
                and a.ends_at >= now()
                and a.user_id = $session_user_id
                and a.on_what_table = '$current_on_what_table'
                and a.on_what_id=" . $current_on_what_table_singular . "_id
                order by a.ends_at asc";
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
            }
            $rst->close();

        }

        if($more_to_check) {
            $sql = "select activity_type_id
                from activity_types
                where activity_type_id < $current_activity_type_id
                order by activity_type_id desc";
            $rst = $con->execute($sql);
            if(!$rst) {
                db_error_handler($con, $sql);
            }
            elseif($rst->rowcount() > 0) {
                $current_activity_type_id = $rst->fields['activity_type_id'];
                $pos = 0;
            }
            else {
                $more_to_check = false;
                header("Location: some.php");
            }
            $rst->close();
        }
        else {
            $_SESSION['next_to_check'] = $next_to_check;
            header("Location: one.php?activity_id=" . $next_to_check[0] . "&pos=0");
        }
    }
}

$con->close();

/**
 * $Log: %
 */
?>