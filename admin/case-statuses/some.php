<?php
/**
* Manage Case Statuses
*
* $Id: some.php,v 1.16 2006/01/02 21:41:51 vanmer Exp $
*/

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$con = get_xrms_dbconnection();

//print_r($_SESSION);
getGlobalVar($acase_type_id,'acase_type_id');

$sql = "SELECT case_type_pretty_name,case_type_id
        FROM case_types
        WHERE case_type_record_status = 'a'
        ORDER BY case_type_pretty_name";
$rst = $con->execute($sql);
if (!$rst) { db_error_handler($con, $sql); }
else { $type_menu= $rst->getmenu2('acase_type_id',$acase_type_id, true, false, 1, "id=acase_type_id onchange=javascript:restrictByCaseType();"); }


if ($acase_type_id) {
    $sql = "SELECT *
            FROM case_statuses
            WHERE case_status_record_status = 'a' AND case_type_id=$acase_type_id
            ORDER BY case_type_id, sort_order";
    $rst = $con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); }


    //get first row count and last row count
    $cnt = 1;
    $maxcnt = $rst->rowcount();

    //get rows, place them in table form
    $table_rows='';
    if ($rst) {
        while (!$rst->EOF) {

            $sort_order = $rst->fields['sort_order'];
            $table_rows .= '<tr>'
                        . '<td class=widget_content><a href=one.php?case_status_id=' . $rst->fields['case_status_id'] . '>'
                        . _($rst->fields['case_status_pretty_name']) . '</a></td>';

            //add descriptions
            $table_rows .= '<td class=widget_content>'
                        . htmlspecialchars($rst->fields['case_status_long_desc'])
                        . '</td>';

            //sets up ordering links in the table

            $table_rows .= '<td class=widget_content>';
            if ($sort_order != $cnt) {
                $table_rows .= '<a href="' . $http_site_root
                            . '/admin/sort.php?direction=up&sort_order='
                            . $sort_order . "&table_name=case_status&case_type_id=$acase_type_id&return_url=/admin/case-statuses/some.php?acase_type_id=$acase_type_id\">"._("up").'</a> &nbsp; ';
            }
            if ($sort_order != $maxcnt) {
                $table_rows .= '<a href="' . $http_site_root
                            . '/admin/sort.php?direction=down&sort_order='
                            . $sort_order . "&table_name=case_status&case_type_id=$acase_type_id&return_url=/admin/case-statuses/some.php?acase_type_id=$acase_type_id\">"._("down").'</a>';
            }
            $table_rows .= '</td></tr>';

            $rst->movenext();
        }
        $rst->close();
        if (!$table_rows) {
            $table_rows='<tr><td colspan=3 class=widget_content>'._("No statuses defined for specified case type") . '</td></tr>';
        }
    }
} else { $table_rows='<tr><td colspan=3 class=widget_content>'._("Select a case type") . '</td></tr>'; }
$con->close();

$page_title = _("Manage Case Statuses");
start_page($page_title);

?>
    <script language=JavaScript>
    <!--
        function restrictByCaseType() {
            select=document.getElementById('acase_type_id');
            location.href = 'some.php?acase_type_id=' + select.value;
        }
     //-->
    </script>

<div id="Main">
   <div id="Content">
                <table class=widget classpacing=1>
                    <tr>
                        <td class=widget_header><?php echo _("Case Type"); ?></td>
                    </tr>
                    <tr>
                        <td class=widget_content><?php echo $type_menu; ?></td>
                    </tr>
                </table>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=3><?php echo _("Case Statuses"); ?></td>
            </tr>
            <tr>
            <!-- <td class=widget_label><?php echo _("Type"); ?></td> -->
                <td class=widget_label><?php echo _("Name"); ?></td>
            <td class=widget_label width=50%><?php echo _("Description"); ?></td>
                <td class=widget_label width=15%><?php echo _("Move"); ?></td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>

   </div>

      <!-- right column //-->
   <div id="Sidebar">

<?php if ($acase_type_id) { ?>
    <form action=new-2.php method=post>
        <input type=hidden name=case_type_id value="<?php echo $acase_type_id; ?>">
    <table class=widget cellspacing=1>
        <tr>
            <td class=widget_header colspan=2>
            <?php echo _("Add New Case Status"); ?>
         </td>
        </tr>
        <tr>
            <td class=widget_label_right>
            <?php echo _("Short Name"); ?>
         </td>
            <td class=widget_content_form_element>
            <input type=text name=case_status_short_name size=10>
         </td>
        </tr>
        <tr>
            <td class=widget_label_right>
            <?php echo _("Full Name"); ?>
         </td>
            <td class=widget_content_form_element>
            <input type=text name=case_status_pretty_name size=20>
         </td>
        </tr>
        <tr>
            <td class=widget_label_right>
            <?php echo _("Full Plural Name"); ?>
         </td>
            <td class=widget_content_form_element>
            <input type=text name=case_status_pretty_plural size=20>
         </td>
        </tr>
        <tr>
            <td class=widget_label_right>
            <?php echo _("Display HTML"); ?>
         </td>
            <td class=widget_content_form_element>
            <input type=text name=case_status_display_html size=30>
         </td>
        </tr>
      <tr>
         <td class=widget_label_right>
            <?php echo _("Description"); ?>
         </td>
         <td class=widget_content_form_element>
            <input type=text size=30 name=case_status_long_desc>
         </td>
      </tr>

        <tr>
            <td class=widget_content_form_element colspan=2>
            <input class=button type=submit value="<?php echo _("Add"); ?>">
         </td>
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