<?php
/**
 * Show and edit the details for a single opportunity status
 *
 * Called from admin/opportunity-status/some.php
 *
 * $Id: one.php,v 1.6 2004/06/03 16:13:22 braverock Exp $
 */

//uinclude required common files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

//check to see if we are logged in
$session_user_id = session_check();

$opportunity_status_id = $_GET['opportunity_status_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from opportunity_statuses where opportunity_status_id = $opportunity_status_id";

//$con->debug=1;

$rst = $con->execute($sql);

if ($rst) {

    $opportunity_status_id = $rst->fields['opportunity_status_id'];
    $sort_order = $rst->fields['sort_order'];
    $status_open_indicator = $rst->fields['status_open_indicator'];
    $opportunity_status_short_name = $rst->fields['opportunity_status_short_name'];
    $opportunity_status_pretty_name = $rst->fields['opportunity_status_pretty_name'];
    $opportunity_status_pretty_plural = $rst->fields['opportunity_status_pretty_plural'];
    $opportunity_status_display_html = $rst->fields['opportunity_status_display_html'];
    $opportunity_status_long_desc = $rst->fields['opportunity_status_long_desc'];

    $rst->close();
}

$table_name = "opportunity_statuses";

// list of all activity templates connected to this opportunity
$sql_activity_templates="select activity_title,
        duration,
        activity_template_id,
        activity_type_pretty_name, sort_order
        from activity_templates, activity_types
        where on_what_id=$opportunity_status_id
        and on_what_table='$table_name'
        and activity_templates.activity_type_id=activity_types.activity_type_id
        and activity_template_record_status='a'
        order by sort_order";

$rst = $con->execute($sql_activity_templates);

$classname = 'open_activity';

//get first record count and last record count
$cnt = 1;
$maxcnt = $rst->rowcount();

//make activity_templates table in HTML
if ($rst) {
    while (!$rst->EOF) {
        $sort_order = $rst->fields['sort_order'];
        $activity_rows .= '<tr>';
//        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['activity_title'] . '</td>';
        $activity_rows .= "<td class='$classname'>"
            . "<a href='$http_site_root/admin/activity-templates/edit.php?activity_template_id="
	    . $rst->fields['activity_template_id'] . "&on_what_table=opportunity_statuses&on_what_id="
	    . $opportunity_status_id . "&return_url=/admin/opportunity-statuses/one.php?opportunity_status_id="
            . $opportunity_status_id . "'>"
            . $rst->fields['activity_title'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['duration'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>'
                . '<table width=100% cellpadding=0 border=0 cellspacing=0>'
                . '<tr><td>' . $rst->fields['activity_type_pretty_name'] . '</td>'
                . '<td align=right>';
        if ($sort_order != $cnt) {
            $activity_rows .= '<a href="' . $http_site_root
            . '/admin/sort.php?direction=up&sort_order='
            . $sort_order . '&table_name=opportunity_status&on_what_id=' . $opportunity_status_id
            . '&return_url=/admin/opportunity-statuses/one.php?opportunity_status_id='
            . $opportunity_status_id . '&activity_template=1">up</a> &nbsp; ';
        }
        if ($sort_order != $maxcnt) {
            $activity_rows .= '<a href="' . $http_site_root
            . '/admin/sort.php?direction=down&sort_order='
            . $sort_order . '&table_name=opportunity_status&on_what_id=' . $opportunity_status_id
            . '&return_url=/admin/opportunity-statuses/one.php?opportunity_status_id='
            . $opportunity_status_id . '&activity_template=1">down</a>';
        }
        $activity_rows .= '</td></tr></table></td></tr>';
        $rst->movenext();
    }
    $rst->close();
}


//get activity type menu
$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a'";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', '', true);
$rst->close();

$con->close();


$page_title = "Opportunity Status : $opportunity_status_pretty_name";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=opportunity_status_id value="<?php  echo $opportunity_status_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4>Edit Opportunity Status Information</td>
            </tr>
            <tr>
                <td class=widget_label_right>Short Name</td>
                <td class=widget_content_form_element><input type=text size=10 name=opportunity_status_short_name value="<?php  echo $opportunity_status_short_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Name</td>
                <td class=widget_content_form_element><input type=text size=20 name=opportunity_status_pretty_name value="<?php  echo $opportunity_status_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Plural</td>
                <td class=widget_content_form_element><input type=text size=20 name=opportunity_status_pretty_plural value="<?php  echo $opportunity_status_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Display HTML</td>
                <td class=widget_content_form_element><input type=text size=60 name=opportunity_status_display_html value="<?php  echo $opportunity_status_display_html; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Long Description</td>
                <td class=widget_content_form_element><input type=text size=80 name=opportunity_status_long_desc value="<?php  echo $opportunity_status_long_desc; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Open Status</td>
                <td class=widget_content_form_element>
                <select name="status_open_indicator">
                    <option value="o" <?php if (($status_open_indicator == "o") or ($status_open_indicator == '')) {print " selected ";} ?>>Open
                    <option value="w" <?php if ($status_open_indicator == "w") {print " selected ";} ?>>Closed/Won
                    <option value="l" <?php if ($status_open_indicator == "l") {print " selected ";} ?>>Closed/Lost
                </select>
                </td>
            </tr>

            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"></td>
            </tr>

        </table>
        </form>

        <!-- link activities to opportunities //-->
        <form action="../activity-templates/new.php" method=post>
        <input type=hidden name=on_what_id value="<?php echo $opportunity_status_id; ?>">
        <input type=hidden name=on_what_table value="<?php echo $table_name; ?>">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=3>Link Activity To Opportunity Status</td>
            </tr>
            <tr>
                <td class=widget_label>Title</td>
                <td class=widget_label>Duration<br>(defaults to days)</td>
                <td class=widget_label>Type</td>
            <tr>
                <td class=widget_content_form_element><input type=text size=40 name="title"></td>
                <td class=widget_content_form_element><input type=text name="duration"></td>
                <td class=widget_content_form_element>
                    <?php
                        echo $activity_type_menu;
                    ?>
                    &nbsp; <input class=button type=submit value="Add">
                </td>
            </tr>
            <?php
                if (!is_null($activity_rows)) {
                    echo $activity_rows;
                } else {
                    echo "<tr>\n";
                    echo "\t\t<td class=widget_content_form_element colspan=3>No linked activities</td>\n";
                    echo "\t</tr>\n";
                }
            ?>
        </table>
        </form>


    </div>


    <!-- right column //-->
    <div id="Sidebar">

        <form action=delete.php method=post>
        <input type=hidden name=opportunity_status_id value="<?php  echo $opportunity_status_id; ?>" onsubmit="javascript: return confirm('Delete Opportunity Status?');">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4>Delete Opportunity Status</td>
            </tr>
            <tr>
                <td class=widget_content>
                Click the button below to remove this opportunity status from the system.
                <p>Note: This action CANNOT be undone!
                <p><input class=button type=submit value="Delete Opportunity Status">
                </td>
            </tr>
        </table>
        </form>

    </div>


</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.6  2004/06/03 16:13:22  braverock
 * - add functionality to support workflow and activity templates
 * - add functionality to support changing sort order
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.5  2004/04/16 22:18:26  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.4  2004/03/15 16:49:56  braverock
 * - add sort_order and open status indicator to opportunity statuses
 *
 * Revision 1.3  2004/01/25 18:39:41  braverock
 * - fixed insert bugs so long_desc will be disoplayed and inserted properly
 * - added phpdoc
 *
 */
?>
