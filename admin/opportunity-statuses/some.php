<?php
/**
 * Display all the opportunity statuses, and give the user the option to
 * add new statuses.
 *
 * @todo modify all opportunity status uses to use a sort order
 *
 * $Id: some.php,v 1.3 2004/01/25 18:39:41 braverock Exp $
 */

//include required XRMS common files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

//check to see if the user is logged in
$session_user_id = session_check();

//connect to the database
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from opportunity_statuses where opportunity_status_record_status = 'a' order by opportunity_status_id";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $table_rows .= "\n<tr>";
            $table_rows .= '<td class=widget_content>'
                         . '<a href=one.php?opportunity_status_id='
                         . $rst->fields['opportunity_status_id']
                         . '>';

            if (strlen ($opportunity_status_display_html) > 0) {
                $table_rows .= $rst->fields['opportunity_status_pretty_html'];
            } else {
                $table_rows .= $rst->fields['opportunity_status_pretty_name'];
            }

            $table_rows .= '</a></td>'
                         . '<td class=widget_content>'
                         . htmlspecialchars($rst->fields['opportunity_status_long_desc'])
                         . '</td>';

        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = "Manage Opportunity Statuses";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=65% valign=top>

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

        <td class=rcol width=33% valign=top>

        <form action=new-2.php method=post>
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=2>Add New Opportunity Status</td>
            </tr>
            <tr>
                <td class=widget_label_right>Short Name</td>
                <td class=widget_content_form_element><input type=text name=opportunity_status_short_name size=10></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Name</td>
                <td class=widget_content_form_element><input type=text name=opportunity_status_pretty_name size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Plural Name</td>
                <td class=widget_content_form_element><input type=text name=opportunity_status_pretty_plural size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right>Display HTML</td>
                <td class=widget_content_form_element><input type=text name=opportunity_status_display_html size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Long Description</td>
                <td class=widget_content_form_element><input type=text size=60 name=opportunity_status_long_desc value="<?php  echo $opportunity_status_long_desc; ?>"></td>
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
 * Revision 1.3  2004/01/25 18:39:41  braverock
 * - fixed insert bugs so long_desc will be disoplayed and inserted properly
 * - added phpdoc
 *
 */
?>