<?php

require_once('vars.php');
require_once('utils-interface.php');
require_once('utils-misc.php');
require_once('adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$email_template_title = $_POST['email_template_title'];
$email_template_body = $_POST['email_template_body'];

$array_of_companies = unserialize($_SESSION['array_of_companies']);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;
$con->execute("update users set last_hit = " . $con->dbtimestamp(mktime()) . " where user_id = $session_user_id");

$sql = "select cont.contact_id, cont.email, cont.first_names, cont.last_name, c.company_name, u.username 
from contacts cont, companies c, users u 
where c.company_id = cont.company_id 
and c.user_id = u.user_id 
and c.company_id in (" . implode($array_of_companies, ',') . ") and length(cont.email) > 0 and contact_record_status = 'act'";

$rst = $con->execute($sql);
if ($rst) {
    while (!$rst->EOF) {
        $contact_rows .= "<tr>";
        $contact_rows .= "<td class=widget_content_form_element><input type=checkbox name=array_of_contacts[] value=" . $rst->fields['contact_id'] . " checked></td>";
        $contact_rows .= "<td class=widget_content>" . $rst->fields['company_name'] . "</td>";
        $contact_rows .= "<td class=widget_content>" . $rst->fields['username'] . "</td>";
        $contact_rows .= "<td class=widget_content>" . $rst->fields['first_names'] . ' ' . $rst->fields['last_name'] . "</td>";
        $contact_rows .= "<td class=widget_content>" . $rst->fields['email'] . "</td>";
        $contact_rows .= "</tr>\n";
        $rst->movenext();
    }
    
    $rst->close();
}

$con->close();

$page_title = 'Select Contacts';
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=55% valign=top>

        <form action=email-4.php method=post>
		<table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=5>Select Contacts</td>
            </tr>
            <tr>
                <td class=widget_label>&nbsp;</td>
                <td class=widget_label>Company</td>
                <td class=widget_label>Owner</td>
                <td class=widget_label>Contact</td>
                <td class=widget_label>E-Mail</td>
            </tr>
            <?php  echo $contact_rows ?>
            <tr>
                <td class=widget_content_form_element colspan=5><input type=submit class=button value="Continue"></td>
            </tr>
		</table>
        </form>
		
		</td>
		<!-- gutter //-->
		<td class=gutter width=2%>
		&nbsp;
		</td>
		<!-- right column //-->
		<td class=rcol width=43% valign=top>
		
    	</td>
	</tr>
</table>

<?php end_page(); ?>