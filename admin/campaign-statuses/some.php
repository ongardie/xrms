<?php
/**
 * /admin/campaign-statuses/some.php
 * Displays all the campaign statuses, and gives the user the option to
 * add new statuses.
 *
 * $Id: some.php,v 1.11 2010/12/07 22:41:03 gopherit Exp $
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

$campaign_type_id = (int)$_GET['campaign_type_id'];

$sql = "SELECT campaign_type_pretty_name, campaign_type_id
        FROM campaign_types
        WHERE campaign_type_record_status = 'a'
        ORDER BY campaign_type_pretty_name";
$rst = $con->execute($sql);
if (!$rst) { db_error_handler($con, $sql); }
else { $type_menu= $rst->getmenu2('campaign_type_id', $campaign_type_id, true, false, 1, "id=campaign_type_id onchange=javascript:restrictByType('campaign_type_id');"); }


if ($campaign_type_id) {
    $sql = "SELECT *
            FROM campaign_statuses
            WHERE campaign_type_id = $campaign_type_id
            AND campaign_status_record_status = 'a'
            ORDER BY campaign_type_id, sort_order";
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
            $table_rows .= '<td class=widget_content>'. $rst->fields['campaign_status_short_name'] .'</td>';

            $table_rows .= '<td class=widget_content><a href="one.php?campaign_status_id='. $rst->fields['campaign_status_id'] .'">'
                        . _($rst->fields['campaign_status_pretty_name']) . '</a></td>';

            $table_rows .= '<td class=widget_content>'. $rst->fields['campaign_status_pretty_plural'] .'</td>';
            $table_rows .= '<td class=widget_content>'. $rst->fields['campaign_status_display_html'] .'</td>';

            $table_rows .= '<td class=widget_content>';
            $status_open_indicator = $rst->fields['status_open_indicator'];
            if (($status_open_indicator == 'o') or ($status_open_indicator == '')){
                $table_rows .= _('Open');
            } else {
                $table_rows .= _('Closed');
            }
             $table_rows .= '</td>';

            // Add descriptions
            $table_rows .= '<td class=widget_content>'
                        . htmlspecialchars($rst->fields['campaign_status_long_desc'])
                        . '</td>';

            // Add sort order
            $table_rows .= '<td class=widget_content>'
                        . $rst->fields['sort_order']
                        . '</td>';

            //sets up ordering links in the table

            $table_rows .= '<td class=widget_content>';
            if ($i > 1) {
                $table_rows .= '<a href="'. $http_site_root
                            . '/admin/sort.php?table_name=campaign_status&sort_order='. $sort_order .'&direction=up'
                            . '&resort_id='. $rst->fields['campaign_status_id'] .'&campaign_type_id='. $campaign_type_id
                            . '&return_url=/admin/campaign-statuses/some.php?campaign_type_id='. $campaign_type_id .'">'. _("up") .'</a> &nbsp; ';
            }
            if ($i < $maxcnt) {
                $table_rows .= '<a href="'. $http_site_root
                            . '/admin/sort.php?table_name=campaign_status&sort_order='. $sort_order .'&direction=down'
                            . '&resort_id='. $rst->fields['campaign_status_id'] .'&campaign_type_id='. $campaign_type_id
                            . '&return_url=/admin/campaign-statuses/some.php?campaign_type_id='. $campaign_type_id .'">'. _("down") .'</a>';
            }
            $table_rows .= '</td></tr>';

            $rst->movenext();
            $i++;
        }
        $rst->close();
        if (!$table_rows) {
            $table_rows='<tr><td colspan=8 class=widget_content>'._("No statuses defined for specified campaign type") . '</td></tr>';
        }
    }
} else { $table_rows='<tr><td colspan=8 class=widget_content>'._("Select a campaign type") . '</td></tr>'; }

$con->close();


$page_title = _("Manage Campaign Statuses");
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
                <td class=widget_header><?php echo _("Campaign Type"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><?php echo $type_menu; ?></td>
            </tr>
        </table>

        <form action=../sort.php method=post>
            <table class=widget cellspacing=1>
                <tr>
                    <td class=widget_header colspan=8><?php echo _("Campaign Statuses"); ?></td>
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

        <?php // If we have no campaign_type_id, skip the new campaign status form
            if ($campaign_type_id) { ?>

            <form action=new-2.php method=post>

                <input type="hidden" name="campaign_type_id" value="<?php echo $campaign_type_id; ?>">

                <table class=widget cellspacing=1>
                    <tr>
                        <td class=widget_header colspan=2><?php echo _("Add New Campaign Status"); ?></td>
                    </tr>

                    <tr>
                        <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                        <td class=widget_content_form_element><input type=text name=campaign_status_short_name size=10></td>
                    </tr>

                    <tr>
                        <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                        <td class=widget_content_form_element><input type=text name=campaign_status_pretty_name size=20></td>
                    </tr>

                    <tr>
                        <td class=widget_label_right><?php echo _("Full Plural Name"); ?></td>
                        <td class=widget_content_form_element><input type=text name=campaign_status_pretty_plural size=20></td>
                    </tr>

                    <tr>
                        <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                        <td class=widget_content_form_element><input type=text name=campaign_status_display_html size=30></td>
                    </tr>

                    <tr>
                        <td class=widget_label_right><?php echo _("Description"); ?></td>
                        <td class=widget_content_form_element><input type=text size=30 name=campaign_status_long_desc value="<?php  echo $campaign_status_long_desc; ?>"></td>
                    </tr>

                    <tr>
                        <td class=widget_label_right><?php echo _("Open Status"); ?></td>
                        <td class=widget_content_form_element>
                        <select name="status_open_indicator">
                            <option value="o"  selected ><?php echo _("Open"); ?>
                            <option value="c"           ><?php echo _("Closed"); ?>
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
 * Revision 1.11  2010/12/07 22:41:03  gopherit
 * Revised the Campaign Status functionality to mirror the functionality of Opportunity and Case Statuses.
 *
 * Revision 1.10  2007/10/17 15:09:27  randym56
 * Show ID field to make ACL mods for group members easier and match new docs
 *
 * Revision 1.9  2006/12/05 19:35:02  jnhayart
 * Add cosmetics display, and control localisation
 *
 * Revision 1.8  2006/01/02 21:37:28  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.7  2005/05/10 13:29:37  braverock
 * - localized string patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.6  2004/07/16 23:51:34  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 13:51:53  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/06/14 21:09:56  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/23 16:30:55  gpowers
 * added support for status_open_indicator,
 *     which is needed for reports/open-items.php and
 *     reports/completed-items.php
 * currently, there are two open statuses: open & closed
 * to add additional status, edit the HTML in this file.
 * 'o' means open, anything else means closed
 *
 * Revision 1.2  2004/04/16 22:18:23  maulani
 * - Add CSS2 Positioning
 *
 *
 */
?>