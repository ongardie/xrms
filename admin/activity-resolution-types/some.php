<?php
/**
 * Manage Activity Resolution Types
 *
 * $Id: some.php,v 1.6 2011/02/28 16:36:44 gopherit Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

getGlobalVar($msg, 'msg');

$con = get_xrms_dbconnection();

$sql = "select * from activity_resolution_types where resolution_type_record_status = 'a' order by sort_order";
$rst = $con->execute($sql);

if ($rst) {
        while (!$rst->EOF) {
                $table_rows .= '<tr>';
                $table_rows .= '<td class=widget_content>' . $rst->fields['activity_resolution_type_id'] . '</td>';
                $table_rows .= '<td class=widget_content><a href=one_activity_resolution_type.php?form_action=edit&activity_resolution_type_id='. $rst->fields['activity_resolution_type_id'] .'&return_url=some.php>' . $rst->fields['resolution_pretty_name'] . '</a></td>';
                $table_rows .= '<td class=widget_content>' . $rst->fields['resolution_short_name'] . '</td>';
                
        $table_rows .= '<td class=widget_content>';
        if($rst->fields['sort_order'] != 1) {
           $table_rows .= "<a href='../sort.php?direction=up&resort_id=".$rst->fields['activity_resolution_type_id']."&sort_order=" . $rst->fields['sort_order']
                . "&table_name=activity_resolution_type&return_url=/admin/activity-resolution-types/some.php'>"._("up")."</a>\n";
        }
        if($rst->fields['sort_order'] != $rst->rowcount()) {
            $table_rows .= "<a href='../sort.php?direction=down&resort_id=".$rst->fields['activity_resolution_type_id']."&sort_order=" . $rst->fields['sort_order']
                . "&table_name=activity_resolution_type&return_url=/admin/activity-resolution-types/some.php'>"._("down")."</a>\n";
        }
                $table_rows .= '</tr>';
                $rst->movenext();
        }
        $rst->close();
}
$con->close();

$page_title = _("Manage Activity Resolution Types");
start_page($page_title, TRUE, $msg);

?>

<div id="Main">
    <div id="Content">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=5><?php echo _("Activity Resolution Types"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("ID"); ?></td>
                <td class=widget_label><?php echo _("Full Name"); ?></td>
                <td class=widget_label><?php echo _("Short Name"); ?></td>
                <td class=widget_label width=15%><?php echo _("Move"); ?></td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>
<form method="POST" name="activity_resolution_types">
<input type="button" class="button" value="<?php echo _("Add New"); ?>" onclick="javascript: location.href='one_activity_resolution_type.php?form_action=new&return_url=some.php'">
</form>

    </div>

    <!-- right column //-->
    <div id="Sidebar">
    </div>
</div>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.6  2011/02/28 16:36:44  gopherit
 * Minor improvements.
 *
 * Revision 1.5  2007/10/17 15:06:24  randym56
 * Show ID field to make ACL mods for group members easier and match new docs
 *
 * Revision 1.4  2006/12/10 18:30:29  jnhayart
 * repair Add New button
 *
 * Revision 1.3  2006/12/05 11:09:59  jnhayart
 * Add cosmetics display, and control localisation
 *
 * Revision 1.2  2006/01/02 22:14:07  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.1  2005/06/30 04:35:29  vanmer
 * - initial revision of list for activity resolution types admin page
 *
 * Revision 1.13  2005/01/11 22:31:17  vanmer
 * - added resort_id to allow click up or down on an activity to type to actually the sort_order of that activity_resolution_type, rather than the one with the same sort_order and lowest activity_resolution_type_id
 *
 * Revision 1.12  2004/11/28 17:30:45  braverock
 * - localized strings for i18n
 *
 * Revision 1.11  2004/11/26 15:58:42  braverock
 * - localized strings for i18n
 *
 * Revision 1.10  2004/07/19 21:31:09  introspectshun
 * - Added i18n string for $page_title
 *
 * Revision 1.9  2004/07/16 23:51:34  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.8  2004/07/16 13:51:53  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.7  2004/07/15 21:11:58  introspectshun
 * - Minor tweaks for consistency
 *
 * Revision 1.6  2004/06/24 20:09:25  braverock
 * - use sort order when displaying activity types
 *   - patch provided by Neil Roberts
 *
 * Revision 1.5  2004/06/14 21:06:33  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.4  2004/06/13 09:13:57  braverock
 * - add sort_order to activity_resolution_types
 *
 * Revision 1.3  2004/04/16 22:18:23  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/04/08 16:56:46  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 */
?>