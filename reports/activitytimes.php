<?php
/**
 * Create a graph of activity for the requested user.
 *
 * @author Glenn Powers
 *
 * $Id: activitytimes.php,v 1.9 2006/01/02 23:46:52 vanmer Exp $
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
$only_show_completed = $_POST['only_show_completed'];

//echo ">>$only_show_completed";

if (strlen($only_show_completed) > 0) {
	$checked_only_show_completed = "checked";
	$only_show_completed = true;
}
else $only_show_completed = false;

if (!strlen($starting) > 0) $starting = date("Y-m-d");
if (!strlen($ending) > 0) $ending = date("Y-m-d");

$page_title = _("Timesheets");
start_page($page_title, true, $msg);

$con = get_xrms_dbconnection();
// $con->debug = 1;

$user_menu = get_user_menu($con, $user_id);
?>

<form action="activitytimes.php" method=post>
<table>
    <tr>
        <th><?php echo _("Start"); ?></th>
        <th><?php echo _("End"); ?></th>
        <th><?php echo _("User"); ?></th>
        <th></th>
    </tr>
        <tr>
                <td><input type=text name=starting value="<?php  echo $starting; ?>"></td>
                <td><input type=text name=ending value="<?php  echo $ending; ?>"></td>
                <td><?php echo $user_menu; ?></td>
            <td><input type=checkbox name=only_show_completed value="true" <?php  echo $checked_only_show_completed; ?>><?php echo _("Only show completed activities"); ?></input></td>
            <td><input class=button type=submit value="<?php echo _("Go"); ?>"></td>
        </tr>
</table>
</form>

<div id="report">

<?php
if ($user_id) {
    echo "<table>";
    echo "    <tr>";
    echo "        <th>" . _("Start") . "</th>";
    echo "        <th>" . _("End") . "</th>";
    echo "        <th>" . _("Duration") . "</th>";
    echo "        <th>" . _("User") . "</th>";
    echo "        <th>" . _("Company") . "</th>";
    echo "        <th>" . _("Contact") . "</th>";
    echo "        <th>" . _("Activity") . "</th>";
    echo "    </tr>";

    $sql = "select username from users where user_id = $user_id";
    $rst = $con->execute($sql);
    $username = $rst->fields['username'];
    $rst->close();

    $start_date = "$starting 00:00:00";
    $end_date =  "$ending 23:23:59";

    $sql2 = "SELECT * from activities
    		where activity_record_status = 'a'
    		and user_id = $user_id 
    		and scheduled_at between " . $con->qstr($start_date, get_magic_quotes_gpc()) . "
    		and " . $con->qstr($end_date, get_magic_quotes_gpc());
    if ($only_show_completed) $sql2 .= " and activity_status <> 'o' ";
    $sql2 .= " order by scheduled_at ";
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

    echo "<tr><td></td><td align=right><strong>" . _("TOTAL") . "</strong><td><strong>" . formatSeconds($total_time) . "</strong></td><td colspan=2>($total_time " . _("seconds") . ")</td><td></td><td></td></tr>\n";
    echo "</table>";
}
echo "</div>\n";
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
 * Revision 1.9  2006/01/02 23:46:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.8  2005/03/21 13:40:57  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.7  2005/01/08 06:47:18  gpowers
 * - fixed formatting
 *
 * Revision 1.6  2005/01/03 06:37:19  ebullient
 * update reports - graphs centered on page, reports surrounded by divs
 *
 * Revision 1.5  2004/07/20 18:36:57  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.4  2004/07/04 09:56:07  metamedia
 * Added option to include only completed activities in the report. Also modified form so that the form elements are rendered with values that match the last query.
 *
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
