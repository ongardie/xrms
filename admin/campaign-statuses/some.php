<?php
/**
 * /admin/campaign-statuses/some.php
 *
 * List campaign-statuses
 *
 * $Id: some.php,v 1.3 2004/04/23 16:30:55 gpowers Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from campaign_statuses where campaign_status_record_status = 'a' order by campaign_status_id";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content><a href="one.php?campaign_status_id=' . $rst->fields['campaign_status_id'] . '">' . $rst->fields['campaign_status_pretty_name'] . '</a></td>';
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = "Manage Campign Statuses";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4>Campaign Statuses</td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <form action=new-2.php method=post>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>Add New Campaign Status</td>
            </tr>
            <tr>
                <td class=widget_label_right>Short Name</td>
                <td class=widget_content_form_element><input type=text name=campaign_status_short_name size=10></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Name</td>
                <td class=widget_content_form_element><input type=text name=campaign_status_pretty_name size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Plural Name</td>
                <td class=widget_content_form_element><input type=text name=campaign_status_pretty_plural size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right>Display HTML</td>
                <td class=widget_content_form_element><input type=text name=campaign_status_display_html size=30></td>
            </tr>
            <tr>
            <tr>
                <td class=widget_label_right>Open Status</td>
                <td class=widget_content_form_element>
                <select name="status_open_indicator">
                    <option value="o"  selected >Open
                    <option value="c"           >Closed
                </select>
                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Add"></td>
            </tr>
        </table>

    </div>
</div>

<?php

end_page();

/**
 * $Log: some.php,v $
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
