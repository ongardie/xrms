<?php
/**
 *
 * Show email messages not sent.
 *
 * $Id: email-4.php,v 1.5 2004/06/14 16:54:37 introspectshun Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$array_of_contacts = $_POST['array_of_contacts'];
$email_template_body = unserialize($_SESSION['email_template_body']);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

// loop through the contacts and send each one a copy of the message

$sql = "select email from contacts where contact_id in (" . implode(',', $array_of_contacts) . ")";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $message .= "<li>" . $rst->fields['email'] . " - " . $email_template_body;
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = 'Messages Not Sent';
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

		<table class=widget cellspacing=1>
            <tr>
                <td class=widget_header>Messages Not Sent</td>
            </tr>
            <tr>
                <td class=widget_content>These messages have not been sent, because bulk e-mail has not been enabled on this system.</td>
            </tr>
		</table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

		&nbsp;

    </div>

</div>

<?php

end_page();

/**
 * $Log: email-4.php,v $
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
