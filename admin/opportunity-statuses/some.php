<?php
/**
 * Display all the opportunity statuses, and give the user the option to
 * add new statuses.
 *
 * @todo modify all opportunity status uses to use a sort order
 *
 * $Id: some.php,v 1.13 2006/01/10 08:21:13 gpowers Exp $
 */

//include required XRMS common files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

//check to see if the user is logged in
$session_user_id = session_check( 'Admin' );

//connect to the database
$con = get_xrms_dbconnection();

//print_r($_SESSION);
getGlobalVar($aopportunity_type_id,'aopportunity_type_id');

$sql = "SELECT opportunity_type_pretty_name,opportunity_type_id
        FROM opportunity_types
        WHERE opportunity_type_record_status = 'a'
        ORDER BY opportunity_type_pretty_name";
$rst = $con->execute($sql);
if (!$rst) { db_error_handler($con, $sql); }
else { $type_menu= $rst->getmenu2('aopportunity_type_id',$aopportunity_type_id, true, false, 1, "id=aopportunity_type_id onchange=javascript:restrictByOppType();"); }


if ($aopportunity_type_id) {
    $sql = "SELECT *
            FROM opportunity_statuses
            WHERE opportunity_status_record_status = 'a' AND opportunity_type_id=$aopportunity_type_id
            ORDER BY opportunity_type_id, sort_order";
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
                        . '<td class=widget_content><a href=one.php?opportunity_status_id=' . $rst->fields['opportunity_status_id'] . '>'
                        . _($rst->fields['opportunity_status_pretty_name']) . '</a></td>';

            //add descriptions
            $table_rows .= '<td class=widget_content>'
                        . htmlspecialchars($rst->fields['opportunity_status_long_desc'])
                        . '</td>';

            //sets up ordering links in the table

            $table_rows .= '<td class=widget_content>';
            if ($sort_order != $cnt) {
                $table_rows .= '<a href="' . $http_site_root
                            . '/admin/sort.php?direction=up&sort_order='
                            . $sort_order . "&table_name=opportunity_status&opportunity_type_id=$aopportunity_type_id&return_url=/admin/opportunity-statuses/some.php?aopportunity_type_id=$aopportunity_type_id\">"._("up").'</a> &nbsp; ';
            }
            if ($sort_order != $maxcnt) {
                $table_rows .= '<a href="' . $http_site_root
                            . '/admin/sort.php?direction=down&sort_order='
                            . $sort_order . "&table_name=opportunity_status&opportunity_type_id=$aopportunity_type_id&return_url=/admin/opportunity-statuses/some.php?aopportunity_type_id=$aopportunity_type_id\">"._("down").'</a>';
            }
            $table_rows .= '</td></tr>';

            $rst->movenext();
        }
        $rst->close();
        if (!$table_rows) {
            $table_rows='<tr><td colspan=3 class=widget_content>'._("No statuses defined for specified opportunity type") . '</td></tr>';
        }
    }
} else { $table_rows='<tr><td colspan=3 class=widget_content>'._("Select a opportunity type") . '</td></tr>'; }

$con->close();


$page_title = _("Manage Opportunity Statuses");
start_page($page_title);

?>

    <script language=JavaScript>
    <!--
        function restrictByOppType() {
            select=document.getElementById('aopportunity_type_id');
            location.href = 'some.php?aopportunity_type_id=' + select.value;
        }
     //-->
    </script>

<div id="Main">
   <div id="Content">
                <table class=widget classpacing=1>
                    <tr>
                        <td class=widget_header><?php echo _("Opportunity Type"); ?></td>
                    </tr>
                    <tr>
                        <td class=widget_content><?php echo $type_menu; ?></td>
                    </tr>
                </table>

   <form action=../sort.php method=post>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Opportunity Statuses"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Name"); ?></td>
                <td class=widget_label width=50%><?php echo _("Description"); ?></td>
                <td class=widget_label width=15%><?php echo _("Move"); ?></td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>
   </form>

   </div>

   <!-- right column //-->
   <div id="Sidebar">

       <form action=new-2.php method=post>
            <input type=hidden name=opportunity_type_id value="<?php echo $aopportunity_type_id; ?>">
       <table class=widget cellspacing=1>
           <tr>
               <td class=widget_header colspan=2><?php echo _("Add New Opportunity Status"); ?></td>
           </tr>
           <tr>
               <td class=widget_label_right><?php echo _("Short Name"); ?></td>
               <td class=widget_content_form_element><input type=text name=opportunity_status_short_name size=10></td>
           </tr>
           <tr>
               <td class=widget_label_right><?php echo _("Full Name"); ?></td>
               <td class=widget_content_form_element><input type=text name=opportunity_status_pretty_name size=20></td>
           </tr>
           <tr>
               <td class=widget_label_right><?php echo _("Full Plural Name"); ?></td>
               <td class=widget_content_form_element><input type=text name=opportunity_status_pretty_plural size=20></td>
           </tr>
           <tr>
               <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
               <td class=widget_content_form_element><input type=text name=opportunity_status_display_html size=30></td>
           </tr>
           <tr>
               <td class=widget_label_right><?php echo _("Description"); ?></td>
               <td class=widget_content_form_element><input type=text size=30 name=opportunity_status_long_desc value="<?php  echo $opportunity_status_long_desc; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Open Status"); ?></td>
                <td class=widget_content_form_element>
                <select name="status_open_indicator">
                    <option value="o"  selected ><?php echo _("Open"); ?>
                    <option value="w"           ><?php echo _("Closed/Won"); ?>
                    <option value="l"           ><?php echo _("Closed/Lost"); ?>
                </select>
                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Add"); ?>"></td>
            </tr>
      </table>
      </form>

   </div>
</div>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.13  2006/01/10 08:21:13  gpowers
 * - added limiting of opp. status by opp. type
 *
 * Revision 1.12  2006/01/02 21:59:08  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.11  2005/05/10 13:31:53  braverock
 * - localized string patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.10  2004/12/31 17:23:04  braverock
 * - cleaned up code formatting
 * - add db_error_handler
 * - prep for workflow extensions
 *
 * Revision 1.9  2004/07/16 23:51:37  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.8  2004/07/16 13:51:59  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
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
 */
?>
