<?php
/**
 * Show and edit the details for a single opportunity status
 *
 * Called from admin/opportunity-status/some.php
 *
 * $Id: one.php,v 1.13 2005/01/11 22:23:32 vanmer Exp $
 */

//uinclude required common files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

//check to see if we are logged in
$session_user_id = session_check( 'Admin' );

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
        activity_type_pretty_name, activity_templates.sort_order
        from activity_templates, activity_types
        where on_what_id=$opportunity_status_id
        and on_what_table='$table_name'
        and activity_templates.activity_type_id=activity_types.activity_type_id
        and activity_template_record_status='a'
        order by activity_templates.sort_order";

$classname = 'open_activity';


$rst = $con->execute($sql_activity_templates);
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
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['activity_type_pretty_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>'
                . '<table width=100% cellpadding=0 border=0 cellspacing=0>'
                . '<tr><td>' . $sort_order . '</td>'
                . '<td align=right>';
        if ($sort_order != $cnt) {
            $activity_rows .= '<a href="' . $http_site_root
            . '/admin/sort.php?allowMultiple=1&direction=up&sort_order='
            . $sort_order . '&table_name=opportunity_status&on_what_id=' . $opportunity_status_id
            . '&return_url=/admin/opportunity-statuses/one.php?opportunity_status_id='
            . $opportunity_status_id . '&activity_template=1">up</a> &nbsp; ';
        }
        if ($sort_order != $maxcnt) {
            $activity_rows .= '<a href="' . $http_site_root
            . '/admin/sort.php?allowMultiple=1&direction=down&sort_order='
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


$page_title = _("Opportunity Status Details").': '.$opportunity_status_pretty_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=opportunity_status_id value="<?php  echo $opportunity_status_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit Opportunity Status Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=opportunity_status_short_name value="<?php  echo $opportunity_status_short_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=opportunity_status_pretty_name value="<?php  echo $opportunity_status_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=opportunity_status_pretty_plural value="<?php  echo $opportunity_status_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text size=60 name=opportunity_status_display_html value="<?php  echo $opportunity_status_display_html; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Long Description"); ?></td>
                <td class=widget_content_form_element><input type=text size=80 name=opportunity_status_long_desc value="<?php  echo $opportunity_status_long_desc; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Open Status"); ?></td>
                <td class=widget_content_form_element>
                <select name="status_open_indicator">
                    <option value="o" <?php if (($status_open_indicator == "o") or ($status_open_indicator == '')) {print " selected ";} ?>><?php echo _("Open"); ?>
                    <option value="w" <?php if ($status_open_indicator == "w") {print " selected ";} ?>><?php echo _("Closed/Won"); ?>
                    <option value="l" <?php if ($status_open_indicator == "l") {print " selected ";} ?>><?php echo _("Closed/Lost"); ?>
                </select>
                </td>
            </tr>

            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>

        </table>
        </form>

        <!-- link activities to opportunities //-->
        <form action="../activity-templates/new.php" method=post>
        <input type=hidden name=on_what_id value="<?php echo $opportunity_status_id; ?>">
        <input type=hidden name=on_what_table value="<?php echo $table_name; ?>">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Link Activity To Opportunity Status"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Title"); ?></td>
                <td class=widget_label><?php echo _("Duration"); ?><br><?php echo _("(defaults to days)"); ?></td>
                <td class=widget_label>Type</td>
                <td class=widget_label>Sort Order</td>
            <tr>
                <td class=widget_content_form_element><input type=text size=40 name="title"></td>
                <td class=widget_content_form_element><input type=text name="duration"></td>
                <td class=widget_content_form_element colspan=2>
                    <?php
                        echo $activity_type_menu;
                    ?>
                    &nbsp; <input class=button type=submit value="<?php echo _("Add"); ?>">
                </td>
            </tr>
            <?php
                if (!is_null($activity_rows)) {
                    echo $activity_rows;
                } else {
                    echo "<tr>\n";
                    echo "\t\t<td class=widget_content_form_element colspan=3>"._("No linked activities")."</td>\n";
                    echo "\t</tr>\n";
                }
            ?>
        </table>
        </form>


    </div>


    <!-- right column //-->
    <div id="Sidebar">

        <form action=delete.php method=post onsubmit="javascript: return confirm('<?php echo _("Delete Opportunity Status?"); ?>');">
        <input type=hidden name=opportunity_status_id value="<?php  echo $opportunity_status_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Delete Opportunity Status"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>
                <?php echo _("Click the button below to permanently remove this item."); ?>
                <p><?php echo _("Note: This action CANNOT be undone!"); ?></p>
                <p><input class=button type=submit value="<?php echo _("Delete"); ?>"></p>
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
 * Revision 1.13  2005/01/11 22:23:32  vanmer
 * - altered to allow multiple activities to exist at the same sort_order, for workflow additions
 * - altered to properly show up/down links by retrieving record count from correct recordset
 *
 * Revision 1.12  2004/07/25 18:38:17  johnfawcett
 * - corrected erroneously pasted string
 *
 * Revision 1.11  2004/07/25 18:26:22  johnfawcett
 * - standardized page title
 * - standardized delete text and button
 * - added delete confirm (call to javascript)
 *
 * Revision 1.10  2004/07/16 23:51:37  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.9  2004/07/16 13:51:59  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.8  2004/06/24 20:06:19  braverock
 * - add sort order to activity templates
 *   - patch provided by Neil Roberts
 *
 * Revision 1.7  2004/06/14 22:36:43  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
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
