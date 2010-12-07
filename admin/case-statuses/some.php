<?php
/**
 * /admin/case-statuses/some.php
 * Displays all the case statuses, and gives the user the option to
 * add new statuses.
 *
 * $Id: some.php,v 1.18 2010/12/07 22:19:53 gopherit Exp $
 */

// Include required XRMS common files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

// Check to see if the user is logged in
$session_user_id = session_check( 'Admin' );

// Connect to the database
$con = get_xrms_dbconnection();

$case_type_id = (int)$_GET['case_type_id'];

$sql = "SELECT case_type_pretty_name, case_type_id
        FROM case_types
        WHERE case_type_record_status = 'a'
        ORDER BY case_type_pretty_name";
$rst = $con->execute($sql);
if (!$rst) { db_error_handler($con, $sql); }
else { $type_menu= $rst->getmenu2('case_type_id', $case_type_id, true, false, 1, "id=case_type_id onchange=javascript:restrictByType('case_type_id');"); }


if ($case_type_id) {
    $sql = "SELECT *
            FROM case_statuses
            WHERE case_type_id = $case_type_id
            AND case_status_record_status = 'a'
            ORDER BY case_type_id, sort_order";
    $rst = $con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); }


    //get first row count and last row count
    $i = 1;
    $maxcnt = $rst->rowcount();

    //get rows, place them in table form
    $table_rows='';
    if ($rst) {
        while (!$rst->EOF) {

            $sort_order = $rst->fields['sort_order'];
            $table_rows .= '<tr>';
            $table_rows .= '<td class=widget_content>'. $rst->fields['case_status_short_name'] .'</td>';
            
            $table_rows .= '<td class=widget_content><a href="one.php?case_status_id='. $rst->fields['case_status_id'] .'">'
                        . _($rst->fields['case_status_pretty_name']) . '</a></td>';

            $table_rows .= '<td class=widget_content>'. $rst->fields['case_status_pretty_plural'] .'</td>';
            $table_rows .= '<td class=widget_content>'. $rst->fields['case_status_display_html'] .'</td>';

            $table_rows .= '<td class=widget_content>';
            $status_open_indicator = $rst->fields['status_open_indicator'];
            if (($status_open_indicator == 'o') or ($status_open_indicator == '')){
                        $table_rows .= _('Open');
            }
            if ($status_open_indicator == 'r') {
                $table_rows .= _('Closed/Resolved');
            }
            if ($status_open_indicator == 'u') {
                $table_rows .= _('Closed/Unresolved');
            }
            $table_rows .='</td>';

            // Add descriptions
            $table_rows .= '<td class=widget_content>'
                        . htmlspecialchars($rst->fields['case_status_long_desc'])
                        . '</td>';

            // Add sort order
            $table_rows .= '<td class=widget_content>'
                        . $rst->fields['sort_order']
                        . '</td>';

            //sets up ordering links in the table

            $table_rows .= '<td class=widget_content>';
            if ($i > 1) {
                $table_rows .= '<a href="'. $http_site_root
                            . '/admin/sort.php?table_name=case_status&sort_order='. $sort_order .'&direction=up'
                            . '&resort_id='. $rst->fields['case_status_id'] .'&case_type_id='. $case_type_id
                            . '&return_url=/admin/case-statuses/some.php?case_type_id='. $case_type_id .'">'. _("up") .'</a> &nbsp; ';
            }
            if ($i < $maxcnt) {
                $table_rows .= '<a href="'. $http_site_root
                            . '/admin/sort.php?table_name=case_status&sort_order='. $sort_order .'&direction=down'
                            . '&resort_id='. $rst->fields['case_status_id'] .'&case_type_id='. $case_type_id
                            . '&return_url=/admin/case-statuses/some.php?case_type_id='. $case_type_id .'">'. _("down") .'</a>';
            }
            $table_rows .= '</td></tr>';

            $rst->movenext();
            $i++;
        }
        $rst->close();
        if (!$table_rows) {
            $table_rows='<tr><td colspan=8 class=widget_content>'._("No statuses defined for specified case type") . '</td></tr>';
        }
    }
} else { $table_rows='<tr><td colspan=8 class=widget_content>'._("Select a case type") . '</td></tr>'; }

$con->close();


$page_title = _("Manage Case Statuses");
start_page($page_title);

?>

<script type="text/javascript" language="JavaScript">
<!--
    function restrictByType(selectName) {
        select=document.getElementById(selectName);
        location.href = 'some.php?' + selectName + '=' + select.value;
    }
 //-->
</script>

<div id="Main">
    <div id="Content">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Case Type"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><?php echo $type_menu; ?></td>
            </tr>
        </table>

        <form action=../sort.php method=post>
            <table class=widget cellspacing=1>
                <tr>
                    <td class=widget_header colspan=8><?php echo _("Case Statuses"); ?></td>
                </tr>
                <tr>
                    <td class=widget_label><?php echo _("Short Name"); ?></td>
                    <td class=widget_label><?php echo _("Full Name"); ?></td>
                    <td class=widget_label><?php echo _("Full Plural Name"); ?></td>
                    <td class=widget_label><?php echo _("Display HTML"); ?></td>
                    <td class=widget_label><?php echo _("Open Status"); ?></td>
                    <td class=widget_label width=50%><?php echo _("Description"); ?></td>
                    <td class=widget_label><?php echo _("Sort Order"); ?></td>
                    <td class=widget_label width=15%><?php echo _("Move"); ?></td>
                </tr>
                <?php  echo $table_rows; ?>
            </table>
        </form>

    </div>

    <!-- right column //-->
    <div id="Sidebar">

        <?php // If we have no case_type_id, skip the new case status form
            if ($case_type_id) { ?>

            <form action=new-2.php method=post>

                <input type="hidden" name="case_type_id" value="<?php echo $case_type_id; ?>">

                <table class=widget cellspacing=1>
                    <tr>
                        <td class=widget_header colspan=2><?php echo _("Add New Case Status"); ?></td>
                    </tr>

                    <tr>
                        <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                        <td class=widget_content_form_element><input type=text name=case_status_short_name size=10></td>
                    </tr>

                    <tr>
                        <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                        <td class=widget_content_form_element><input type=text name=case_status_pretty_name size=20></td>
                    </tr>

                    <tr>
                        <td class=widget_label_right><?php echo _("Full Plural Name"); ?></td>
                        <td class=widget_content_form_element><input type=text name=case_status_pretty_plural size=20></td>
                    </tr>

                    <tr>
                        <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                        <td class=widget_content_form_element><input type=text name=case_status_display_html size=30></td>
                    </tr>

                    <tr>
                        <td class=widget_label_right><?php echo _("Description"); ?></td>
                        <td class=widget_content_form_element><input type=text size=30 name=case_status_long_desc value="<?php  echo $case_status_long_desc; ?>"></td>
                    </tr>

                    <tr>
                        <td class=widget_label_right><?php echo _("Open Status"); ?></td>
                        <td class=widget_content_form_element>
                            <select name="status_open_indicator">
                                <option value="o"  selected ><?php echo _("Open"); ?>
                                <option value="r"           ><?php echo _("Closed/Resolved"); ?>
                                <option value="u"           ><?php echo _("Closed/Unresolved"); ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td class=widget_label_right><?php echo _("Sort Order"); ?></td>
                        <td class=widget_content_form_element><input type=text name=sort_order size=5></td>
                    </tr>

                    <tr>
                        <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Add"); ?>"></td>
                    </tr>
                </table>
            </form>
        <?php } ?>
   </div>
</div>

<?php

end_page();

/**
* $Log: some.php,v $
* Revision 1.18  2010/12/07 22:19:53  gopherit
* Replaced acase_type_id with opportunity_type_id and pulled its value direction from $_GET.
* Fixed case status sorting.
*
* Revision 1.17  2010/11/26 18:30:15  gopherit
* FIXED Bug # 3119879  Added missing fields in Open Status selector in /admin/case-statuses/some.php.  Updated the /admin/case-statuses/new-2.php database storage call.
*
* Revision 1.16  2006/01/02 21:41:51  vanmer
* - changed to use centralized dbconnection function
*
* Revision 1.15  2005/07/08 17:10:34  braverock
* - remove obsolete todo item
*
* Revision 1.14  2005/05/13 22:50:10  braverock
* - change 'Status Type' to 'Case Type'
*
* Revision 1.13  2005/05/10 13:30:35  braverock
* - localized string patches provided by Alan Baghumian (alanbach)
*
* Revision 1.12  2005/02/24 12:42:46  braverock
* - improve SQL formatting
* - only show case statuses/types that have an 'a'ctive status
*   - modified from patch submitted by Keith Edmunds
*
* Revision 1.11  2005/02/10 01:57:32  braverock
* - clean up code formatting
* - make sure only active statuses are shown
*
* Revision 1.10  2005/01/10 21:40:46  vanmer
* - added case_type, needed for distinguishing between statuses
* - added dropdown for selecting type, for use in creating new status
* - only show new status box when case_type_id is set (type selected with dropdown)
*
* Revision 1.9  2004/12/31 17:52:56  braverock
* - add description for consistency
*
* Revision 1.8  2004/12/31 17:24:30  braverock
* - cleaned up code formatting
* - added description column to match opportunity statuses
* - prep for workflow extensions
*   @todo add sorting and display by case type
*
* Revision 1.7  2004/07/16 23:51:35  cpsource
* - require session_check ( 'Admin' )
*
* Revision 1.6  2004/07/16 13:51:55  braverock
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
*/
?>