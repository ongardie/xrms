<?php
/**
 * Manage Company Types
 *
 * $Id: some.php,v 1.4 2004/04/08 16:56:47 maulani Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from company_types where company_type_record_status = 'a' order by company_type_pretty_name";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content><a href=one.php?company_type_id=' . $rst->fields['company_type_id'] . '>' . $rst->fields['company_type_pretty_name'] . '</a></td>';
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$page_title = "Manage Company Types";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=65% valign=top>

        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=4>company_types</td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>

        </td>

        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>

        <!-- right column //-->

        <td class=rcol width=33% valign=top>

        <form action=add-2.php method=post>
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=2>Add New Company Type</td>
            </tr>
            <tr>
                <td class=widget_label_right>Short Name</td>
                <td class=widget_content_form_element><input type=text name=company_type_short_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Name</td>
                <td class=widget_content_form_element><input type=text name=company_type_pretty_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Plural Name</td>
                <td class=widget_content_form_element><input type=text name=company_type_pretty_plural size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Display HTML</td>
                <td class=widget_content_form_element><input type=text name=company_type_display_html size=30></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Add"></td>
            </tr>
        </table>
        </form>

        </td>
    </tr>
</table>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.4  2004/04/08 16:56:47  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>

