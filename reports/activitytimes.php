<?php
/**
 * Create a graph of activity for the requested user.
 *
 * @author Glenn Powers
 *
 * $Id: activitytimes.php,v 1.3 2004/06/12 05:35:58 introspectshun Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$starting = $_POST['starting'];
$ending = $_POST['ending'];
$user_id = $_POST['user_id'];

$page_title = "Timesheets";
start_page($page_title, true, $msg);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', '', false);
$rst->close();
?>

<table>
    <tr>
        <th>Start</th>
        <th>End</th>
        <th>User</th>
        <th></th>
    </tr>
        <tr>
            <form action="activitytimes.php" method=post>
                <td><input type=text name=starting value="<?php  echo date("Y-m-d"); ?>"></td>
                <td><input type=text name=ending value="<?php  echo date("Y-m-d"); ?>"></td>
                <td><?php echo $user_menu; ?></td>
                <td><input class=button type=submit value="Go"></td>
            </form>
        </tr>
</table>
<p>&nbsp;</p>

<?php
if ($user_id) {
    echo "<table>";
    echo "    <tr>";
    echo "        <th>Start</th>";
    echo "        <th>End</th>";
    echo "        <th>Duration</th>";
    echo "        <th>User</th>";
    echo "        <th>Company</th>";
    echo "        <th>Contact</th>";
    echo "        <th>Activity</th>";
    echo "    </tr>";

    $sql = "select username from users where user_id = $user_id";
    $rst = $con->execute($sql);
    $username = $rst->fields['username'];
    $rst->close();

    $start_date = "$starting 00:00:00";
    $end_date =  "$ending 23:23:59";

    $sql2 = "SELECT * from activities where activity_record_status = 'a' and user_id = $user_id and entered_at between " . $con->qstr($start_date, get_magic_quotes_gpc()) . " and " . $con->qstr($end_date, get_magic_quotes_gpc()) . " order by entered_at ";
    $rst = $con->execute($sql2);

    if ($rst) {
        while (!$rst->EOF) {
            echo "<tr>\n<td>" . $rst->fields['scheduled_at'] . "&nbsp;&nbsp;&nbsp;</td>\n";
            echo "<td>" . $rst->fields['ends_at'] . "&nbsp;&nbsp;</td>\n";
            $task_time = calcDateDiff( strtotime($rst->fields['ends_at']), strtotime($rst->fields['scheduled_at'] ) );
            $total_time = $total_time + ( strtotime($rst->fields['ends_at']) - strtotime($rst->fields['scheduled_at'] ) );
            echo "<td><strong>" . $task_time . "</strong>&nbsp;&nbsp;&nbsp;</td>\n";
            $sql4 = "SELECT last_name, first_names from users where user_id = " . $rst->fields['user_id'];
            $rst4 = $con->execute($sql4);
            echo "<td>" . $rst4->fields['last_name'] . ", " . $rst4->fields['first_names'] . "&nbsp;&nbsp;&nbsp;</td>\n";
            $sql5 = "SELECT company_name from companies where company_id = " . $rst->fields['company_id'];
            $rst5 = $con->execute($sql5);
            echo "<td>" . $rst5->fields['company_name'] . "&nbsp;&nbsp;&nbsp;</td>\n";
            $sql6 = "SELECT last_name, first_names from contacts where contact_id = " . $rst->fields['contact_id'];
            $rst6 = $con->execute($sql6);
            echo "<td>" . $rst6->fields['last_name'] . ", " . $rst6->fields['first_names'] . "&nbsp;&nbsp;&nbsp;</td>\n";
            echo "<td><a href=\"../activities/one.php?activity_id=" . $rst->fields['activity_id'] . "\">" . $rst->fields['activity_title'] . "</a></td>\n</td>\n";
            $rst->movenext();
        }
        $rst->close();
    }
    $con->close();

    echo "<tr><td></td><td align=right><strong>TOTAL</srtong><td><strong>" . formatSeconds($total_time) . "</strong></td><td></td><td></td><td></td></tr>\n";
    echo "</table>";
}

end_page();

function calcDateDiff( $date1, $date2 ) {
   if( $date2 > $date1 ) {
//       die( "error: date1 has to be >= date2 in calcDateDiff($date1, $date2)" );
       $diff = abs($date1-$date2);
       }
   $diff = $date1-$date2;
   $seconds = 0;
   $hours  = 0;
   $minutes = 0;
   if($diff % 86400 > 0) {
       $rest = ($diff % 86400);
       $days = ($diff - $rest) / 86400;
       if( $rest % 3600 > 0 ) {
           $rest1 = ($rest % 3600);
           $hours = ($rest - $rest1) / 3600;
           if( $rest1 % 60 > 0 ) {
               $rest2 = ($rest1 % 60);
               $minutes = ($rest1 - $rest2) / 60;
               $seconds = $rest2;
           }else
               $minutes = $rest1 / 60;
       }else
           $hours = $rest / 3600;
   }else
       $days = $diff / 86400;
//   return array( "days" => $days, "hours" => $hours, "minutes" => $minutes, "seconds" => $seconds);
    $result = sprintf("%02d:%02d", $hours, $minutes);
    return( $result );
}

function formatSeconds( $diff ) {
   $seconds = 0;
   $hours  = 0;
   $minutes = 0;
   if($diff % 86400 > 0) {
       $rest = ($diff % 86400);
       $days = ($diff - $rest) / 86400;
       if( $rest % 3600 > 0 ) {
           $rest1 = ($rest % 3600);
           $hours = ($rest - $rest1) / 3600;
           if( $rest1 % 60 > 0 ) {
               $rest2 = ($rest1 % 60);
               $minutes = ($rest1 - $rest2) / 60;
               $seconds = $rest2;
           }else
               $minutes = $rest1 / 60;
       }else
           $hours = $rest / 3600;
   }else
       $days = $diff / 86400;
    $result = sprintf("%02d:%02d", $hours, $minutes);
    return( $result );
}

/**
 * $Log: activitytimes.php,v $
 * Revision 1.3  2004/06/12 05:35:58  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.2  2004/04/20 14:00:58  braverock
 * - changed form definition
 *
 * Revision 1.1  2004/04/20 13:34:42  braverock
 * - add activity times report
 * - add open items report
 * - add completed items report
 *   - apply SF patches 928336, 937994, 938094 submitted by Glenn Powers
 *
 */
?>