<?php

require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from opportunity_statuses where opportunity_status_record_status = 'a' order by opportunity_status_id";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content>'. htmlspecialchars($rst->fields['opportunity_status_pretty_name']) . '</td><td class=widget_content>'. htmlspecialchars($rst->fields['opportunity_status_long_desc']) . '</td>';
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = "View Opportunity Statuses";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=100% valign=top>

        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=4>Opportunity Statuses</td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
                <td class=widget_label>Description</td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>

        </td>

        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>

        <!-- right column //-->

    </tr>
</table>

<?php end_page();; ?>