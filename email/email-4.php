<?php

require_once('vars.php');
require_once('utils-interface.php');
require_once('utils-misc.php');
require_once('adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$array_of_contacts = $_POST['array_of_contacts'];

$_SESSION['array_of_contacts'] = serialize($array_of_contacts);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;
$con->execute("update users set last_hit = " . $con->dbtimestamp(mktime()) . " where user_id = $session_user_id");

$con->close();

$page_title = 'Messages Not Sent';
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=35% valign=top>

        <form action=email-5.php method=post>
		<table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header>Messages Not Sent</td>
            </tr>
            <tr>
                <td class=widget_content_wrappable>
				<p>The messages have been composed and placed in the message queue, but they have not been sent, because bulk e-mail has not been enabled on this system.</p>
                </td>
            </tr>
		</table>
        </form>
		
		</td>
		<!-- gutter //-->
		<td class=gutter width=2%>
		&nbsp;
		</td>
		<!-- right column //-->
		<td class=rcol width=63% valign=top>
		
    	</td>
	</tr>
</table>

<?php end_page(); ?>