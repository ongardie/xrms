<?php

require_once('/demo/include/vars.php');
require_once('/demo/include/utils-interface.php');
require_once('/demo/include/utils-misc.php');
require_once('/demo/include/adodb/adodb.inc.php');
require_once('/demo/include/adodb/adodb-pager.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
$con->execute("update users set last_hit = " . $con->dbtimestamp(mktime()) . " where user_id = $session_user_id");


$sql = "select * from email_templates";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $tablerows .= '<tr>';
        $tablerows .= '<td class=widget_content><a href=one.php?email_template_id=' . $rst->fields['email_template_id'] . '>' . $rst->fields['email_template_title'] . '</a></td>';
        // $tablerows .= '<td class=widget_content>' . $rst->fields['company_code'] . '</td>';
        $tablerows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($tablerows) == 0) {
    $tablerows = '<tr><td class=widget_content colspan=1>No e-mail templates</td></tr>';
}

$page_title = 'E-Mail Templates';
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=65% valign=top>

        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=20>E-Mail Templates</td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
            </tr>
            <?php  echo $tablerows ?>
        </table>

        </td>
        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=33% valign=top>

        </td>
    </tr>
</table>

<?php end_page(); ?>
