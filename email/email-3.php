<?php
/**
 *
 * Confirm email recipients.
 *
 * $Id: email-3.php,v 1.5 2004/06/14 16:54:37 introspectshun Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$email_template_title = $_POST['email_template_title'];
$email_template_body = $_POST['email_template_body'];

$_SESSION['email_template_body'] = serialize($email_template_body);

$array_of_contacts = unserialize($_SESSION['array_of_contacts']);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "select cont.contact_id, cont.email, cont.first_names, cont.last_name, c.company_name, u.username 
from contacts cont, companies c, users u 
where c.company_id = cont.company_id 
and c.user_id = u.user_id 
and c.company_id in (" . implode(',', $array_of_contacts) . ") and length(cont.email) > 0 and contact_record_status = 'a'";

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

$page_title = "Confirm Recipients";
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=email-4.php method=post>
		<table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=5>Confirm Recipients</td>
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

    </div>

        <!-- right column //-->
    <div id="Sidebar">

		&nbsp;

    </div>

</div>

<?php

end_page();

/**
 * $Log: email-3.php,v $
 * Revision 1.5  2004/06/14 16:54:37  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.4  2004/04/17 16:00:36  maulani
 * - Add CSS2 positioning
 *
 *
 */
?>
