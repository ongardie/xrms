<?php
/**
 * Show and edit the details for all crm statuses
 *
 * $Id: some.php,v 1.4 2004/04/16 22:18:25 maulani Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from crm_statuses where crm_status_record_status = 'a' order by crm_status_id";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        if (strlen($rst->fields['crm_status_display_html']) > 0) {
            $crm_status_display = $rst->fields['crm_status_display_html'];
        } else {
            $crm_status_display = $rst->fields['crm_status_pretty_name'];
        }
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content><a href=one.php?crm_status_id=' . $rst->fields['crm_status_id'] . '>' . $crm_status_display . '</a></td>';
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = "Manage CRM Statuses";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4>Existing CRM Statuses</td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <form action=add-2.php method=post>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>Add New CRM Status</td>
            </tr>
            <tr>
                <td class=widget_label_right>Short Name</td>
                <td class=widget_content_form_element><input type=text name=crm_status_short_name size=10></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Name</td>
                <td class=widget_content_form_element><input type=text name=crm_status_pretty_name size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Plural Name</td>
                <td class=widget_content_form_element><input type=text name=crm_status_pretty_plural size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right>Display HTML</td>
                <td class=widget_content_form_element><input type=text name=crm_status_display_html size=30></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Add"></td>
            </tr>
        </table>
        </form>

    </div>
</div>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.4  2004/04/16 22:18:25  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.3  2004/03/19 03:46:33  braverock
 * - reversed { on line 24
 *   - patch committed by Jake Starbile ( zathras66 )
 *
 * Revision 1.2  2004/02/22 17:05:09  braverock
 * - changed to show display_html
 *   Resolves SF bug 881277
 * - add phpdoc
 *
 */
?>