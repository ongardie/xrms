<?php
/**
 * Manage Case Statuses
 *
 * $Id: one.php,v 1.7 2004/07/16 23:51:35 cpsource Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$case_status_id = $_GET['case_status_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

$sql = "select * from case_statuses where case_status_id = $case_status_id";

$rst = $con->execute($sql);

if ($rst) {

        $case_status_short_name = $rst->fields['case_status_short_name'];
        $case_status_pretty_name = $rst->fields['case_status_pretty_name'];
        $case_status_pretty_plural = $rst->fields['case_status_pretty_plural'];
        $case_status_display_html = $rst->fields['case_status_display_html'];

        $rst->close();
}


$table_name = "case_statuses";

// list of all activity templates connected to this case
$sql_activity_templates="select activity_title,
        duration,
        activity_template_id,
        activity_type_pretty_name, sort_order
        from activity_templates, activity_types
        where on_what_id=$case_status_id
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
             . $rst->fields['activity_template_id'] . "&on_what_table=case_statuses&on_what_id="
             . $case_status_id . "&return_url=/admin/case-statuses/one.php?case_status_id="
	     . $case_status_id . "'>" 
	     . $rst->fields['activity_title'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['duration'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>'
                . '<table width=100% cellpadding=0 border=0 cellspacing=0>'
                . '<tr><td>' . $rst->fields['activity_type_pretty_name'] . '</td>'
                . '<td align=right>';
        if ($sort_order != $cnt) {
            $activity_rows .= '<a href="' . $http_site_root
            . '/admin/sort.php?direction=up&sort_order='
            . $sort_order . '&table_name=case_status&on_what_id=' . $case_status_id
            . '&return_url=/admin/case-statuses/one.php?case_status_id='
            . $case_status_id . '&activity_template=1">up</a> &nbsp; ';
        }
        if ($sort_order != $maxcnt) {
            $activity_rows .= '<a href="' . $http_site_root
            . '/admin/sort.php?direction=down&sort_order='
            . $sort_order . '&table_name=case_status&on_what_id=' . $case_status_id
            . '&return_url=/admin/case-statuses/one.php?case_status_id='
            . $case_status_id . '&activity_template=1">down</a>';
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

$page_title = _("Case Status : ").$case_status_pretty_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=case_status_id value="<?php  echo $case_status_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit Case Status Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=case_status_short_name value="<?php  echo $case_status_short_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=case_status_pretty_name value="<?php  echo $case_status_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=case_status_pretty_plural value="<?php  echo $case_status_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text size=60 name=case_status_display_html value="<?php  echo $case_status_display_html; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>
        </table>
    </form>


        <!-- link activities to cases //-->
        <form action="../activity-templates/new.php" method=post>
        <input type=hidden name=on_what_id value="<?php echo $case_status_id; ?>">
        <input type=hidden name=on_what_table value="<?php echo $table_name; ?>">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=3><?php echo _("Link Activity To Case Status"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Title"); ?></td>
                <td class=widget_label><?php echo _("Duration"); ?><br> <?php echo _("(defaults to days)"); ?></td>
                <td class=widget_label>Type</td>
            <tr>
                <td class=widget_content_form_element><input type=text size=40 name="title"></td>
                <td class=widget_content_form_element><input type=text name="duration"></td>
                <td class=widget_content_form_element>
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
                    echo "\t\t".'<td class=widget_content_form_element colspan=3>'._("No linked activities")."</td>\n";
                    echo "\t</tr>\n";
                }
            ?>
        </table>
        </form>


    </div>


    <!-- right column //-->
    <div id="Sidebar">

        <form action=delete.php method=post>
        <input type=hidden name=case_status_id value="<?php  echo $case_status_id; ?>" onsubmit="javascript: return confirm('<?php echo _("Delete Case Status?"); ?>');">
        <table class=widget cellspacing=1>
             <tr>
                   <td class=widget_header colspan=4><?php echo _("Delete Case Status"); ?></td>
             </tr>
             <tr>
                   <td class=widget_content>
                       <?php echo _("Click the button below to remove this"); ?><br><?php echo _("case status from the system."); ?>
                       <p><?php echo _("Note: This action CANNOT be undone!"); ?>
                       <p><input class=button type=submit value="<?php echo _("Delete Case Status"); ?>">
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
 * Revision 1.7  2004/07/16 23:51:35  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.6  2004/07/16 13:51:54  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.5  2004/06/14 21:37:55  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.4  2004/06/03 16:12:51  braverock
 * - add functionality to support workflow and activity templates
 * - add functionality to support changing sort order
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.3  2004/04/16 22:18:24  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/04/08 16:56:47  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>