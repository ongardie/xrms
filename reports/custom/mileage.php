<?php
/**
 * reportname: Mileage Calculation
 * reportdescrip: Selectable by user for individual reporting.
 * author: Randy Martinsen
 */
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-cases.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = (strlen($_POST['msg']) > 0) ? $_POST['msg'] : $_GET['msg'];
$starting = (strlen($_POST['starting']) > 0) ? $_POST['starting'] : $_GET['starting'];
$ending = (strlen($_POST['ending']) > 0) ? $_POST['ending'] : $_GET['ending'];
$user_id = $session_user_id;

if (strlen($only_show_completed) > 0) {
	$checked_only_show_completed = "checked";
	$only_show_completed = true;
}
else $only_show_completed = false;

if (!strlen($starting) > 0) $starting = date("Y-m").'-01';
if (!strlen($ending) > 0) $ending = date("Y-m-d");

$page_title = _("User Mileage Report - All Closed Activities");
start_page($page_title, true, $msg);

$con = get_xrms_dbconnection();
// $con->debug = 1;

//$user_menu = get_all users;
$user_menu = get_user_menu($con, $user_id, true, 'user_id', false);

?>

<form action="mileage.php" method=post>
<table>
    <tr>
        <th><?php echo _("Start"); ?></th>
        <th><?php echo _("End"); ?></th>
        <?php 
			if(check_user_role(false, $_SESSION['session_user_id'], 'Administrator')) {
            echo "<th>User ID</th>";
			}
			?>
        
        <th></th>
    </tr>
        <tr>
        <td><input type=text name=starting value="<?php  echo $starting; ?>"></td>
        <td><input type=text name=ending value="<?php  echo $ending; ?>"></td>
        <?php
        if(check_user_role(false, $_SESSION['session_user_id'], 'Administrator')) {
            echo "<td class=widget_content_form_element>".$user_menu."</td>";
            } else echo "<input type=hidden name=user_id value=".$user_id;
        ?>
        <td><input class=button type=submit value="<?php echo _("Go"); ?>"></td>
        </tr>
</table>
</form>

<div id="report">

<?php

/*
    $sql = "select username from users where user_id = $user_id";
    $rst = $con->execute($sql);
    $username = $rst->fields['username'];
    $rst->close();
*/

    $start_date = "$starting 00:00:00";
    $end_date =  "$ending 23:23:59";

    $sql2 = "SELECT CONCAT(u.first_names,' ',u.last_name) as user_name, u.user_id, a.* from activities a, users u
    		where a.activity_record_status = 'a'
			and a.mileage > 0
    		and a.scheduled_at between " . $con->qstr($start_date, get_magic_quotes_gpc()) . "
    		and " . $con->qstr($end_date, get_magic_quotes_gpc()) . "
    		and a.activity_status <> 'o'
			and u.user_id = a.user_id ";
			if ($user_id > 0) $sql2 .= "and u.user_id = $user_id ";
    		$sql2 .= "order by u.user_id, a.scheduled_at";
    $rst = $con->execute($sql2);

    if ($rst) { 
    echo "<table>";
	echo "<tr><td colspan='6'><hr></td></tr><tr><td colspan='6'>".$rst->fields['user_name']." (ID: ".$rst->fields['user_id'].")</td></tr>";
    echo "    <tr>";
    echo "        <th>" . _("Start") . "</th>";
    echo "        <th>" . _("End") . "</th>";
    echo "        <th align=center>" . _("Total<br>Miles") . "</th>";
    echo "        <th>" . _("Contact") . "</th>";
    echo "        <th>" . _("Activity") . "</th>";
    echo "        <th>" . _("Description") . "</th>";
    echo "    </tr>";
		$total_miles = 0;
        while (!$rst->EOF) {
			$user_id = $rst->fields['user_id'];
            echo "<tr>\n<td>" . $rst->fields['scheduled_at'] . "&nbsp;&nbsp;&nbsp;</td>\n";
            echo "<td>" . $rst->fields['ends_at'] . "&nbsp;&nbsp;</td>\n";
			echo "<td align=right><strong>" . $rst->fields['mileage'] . "</strong>&nbsp;&nbsp;&nbsp;</td>\n";
			$total_miles = $total_miles + $rst->fields['mileage'];
            $sql6 = "SELECT last_name, first_names from contacts where contact_id = " . $rst->fields['contact_id'];
            $rst6 = $con->execute($sql6);
            echo "<td>" . $rst6->fields['last_name'] . ", " . $rst6->fields['first_names'] . "&nbsp;&nbsp;&nbsp;</td>\n";
            echo "<td><a href=\"".$http_site_root."/activities/one.php?activity_id=" . $rst->fields['activity_id'] . "\">" . $rst->fields['activity_title'] . "</a></td>\n</td>\n";
            echo "<td>" . substr($rst->fields['activity_description'],0,50) . "</td>\n";
            $rst->movenext();
			if ($user_id <> $rst->fields['user_id']) {
				echo "</tr><tr><td colspan=2></td><td align=left colspan=4><strong>" . _("TOTAL") . " " . number_format($total_miles,2) ."</strong><td></tr>\n";
				$total_time = 0;
				if (!$rst->EOF) {
					echo "<tr><td colspan='6'><hr></td></tr><tr><td colspan='6'>".$rst->fields['user_name']." (ID: ".$rst->fields['user_id'].")</td></tr>";
					echo "    <tr>";
					echo "        <th>" . _("Start") . "</th>";
					echo "        <th>" . _("End") . "</th>";
					echo "        <th>" . _("Duration") . "</th>";
					echo "        <th align=center>" . _("Total<br>Miles") . "</th>";
					echo "        <th>" . _("Contact") . "</th>";
					echo "        <th>" . _("Activity") . "</th>";
    				echo "        <th>" . _("Description") . "</th>";
					echo "    </tr>";
					}
				}
        }
        $rst->close();
    }

$con->close();

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
    $result = sprintf("%02d:%02d:%02d", $days, $hours, $minutes);
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
    $result = sprintf("%02d:%02d:%02d", $days, $hours, $minutes);
    return( $result );
}
/**
 */
?>
