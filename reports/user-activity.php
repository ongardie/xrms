<?php
/**
 * Create a graph of activity for the requested user.
 *
 * $Id: user-activity.php,v 1.8 2006/01/02 23:46:52 vanmer Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-graph.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

$user_id = $_POST['user_id'];

$sql = "select username from users where user_id = $user_id";
$rst = $con->execute($sql);
$username = $rst->fields['username'];
$rst->close();

$sql1 = "select activity_type_id, activity_type_pretty_plural from activity_types where activity_type_record_status = 'a'";
$rst1 = $con->execute($sql1);
$graph_legend_array = array();

while (!$rst1->EOF) {

    for ($i = 0; $i <= 12; $i++) {

        $start_date = date("Y-m-d", mktime(0,0,0, date('m') - $i, 1,date('Y')));
        $end_date = date("Y-m-d", mktime(0,0,0, date('m') - $i + 1, 1,date('Y')));

        $sql2 = "SELECT count(*) AS activity_count from activities where user_id = $user_id and activity_type_id = " . $rst1->fields['activity_type_id'] . " and entered_at between " . $con->qstr($start_date, get_magic_quotes_gpc()) . " and " . $con->qstr($end_date, get_magic_quotes_gpc());
        $rst2 = $con->execute($sql2);

        if ($rst2) {
            $activity_count = $rst2->fields['activity_count'];
            $rst2->close();
        }

        if (!$activity_count) {
            $activity_count = 0;
        }

        $array_of_activity_count_values_for_one_user[$i] = $activity_count;

    }

    $graph_rows .= "g.addRow(" . implode(',', array_reverse($array_of_activity_count_values_for_one_user)) . ");\n";
    array_push($graph_legend_array, "'" . $rst1->fields['activity_type_pretty_plural'] . "'");
    $rst1->movenext();

}

$rst1->close();
$con->close();

$graph_legend = implode(',', $graph_legend_array);

$page_title = _("Activity Summary") . ": " . $username;
start_page($page_title, true, $msg);

?>

<SCRIPT LANGUAGE="JavaScript1.2" SRC="<?php  echo $http_site_root; ?>/js/graph.js"></SCRIPT>

<div id="Main">
    <div id="ContentFullWidth">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Activity Summary"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_center><?php echo _("Activity Summary"); ?></td>
            </tr>
            <tr>

                <td class=widget_content_graph>
                <SCRIPT LANGUAGE="JavaScript1.2">
                var g = new Graph(<?php echo $report_graph_width . ' , ' . $report_graph_height; ?>);
                <?php echo $graph_rows; ?>
                g.scale = 1;
                g.setXScaleValues(<?php echo list_of_months(); ?>);
                g.stacked = true;
                g.setLegend(<?php echo $graph_legend; ?>);
                g.build();
                </SCRIPT>
                </td>

            </tr>

        </table>

    </div>

</div>

<?php

end_page();

/**
 * $Log: user-activity.php,v $
 * Revision 1.8  2006/01/02 23:46:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.7  2005/01/03 06:37:19  ebullient
 * update reports - graphs centered on page, reports surrounded by divs
 *
 * Revision 1.6  2004/07/20 18:36:58  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.5  2004/06/12 05:35:58  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.4  2004/04/17 15:57:03  maulani
 * - Add CSS2 positioning
 * - Add phpdoc
 *
 * Revision 1.3  2004/04/16 22:21:32  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.2  2004/01/26 15:52:42  braverock
 * - fixed short tags
 * - added phpdoc
 *
 */
?>
