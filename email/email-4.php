<?php
/**
*
* Show email messages not sent.
*
* $Id: email-4.php,v 1.11 2005/03/17 20:05:26 jswalter Exp $
*/

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$array_of_contacts = $_POST['array_of_contacts'];

$sender_name = unserialize($_SESSION['sender_name']);
$sender_address = unserialize($_SESSION['sender_address']);
$bcc_address = unserialize($_SESSION['bcc_address']);
$email_template_title = unserialize($_SESSION['email_template_title']);
$email_template_body = unserialize($_SESSION['email_template_body']);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

// loop through the contacts and send each one a copy of the message with a personalised "Dear contact"

$sql = "select * from contacts where contact_id in (" . implode(',', $array_of_contacts) . ")";
$rst = $con->execute($sql);

if ($rst) {

    require_once ( $include_directory . 'classes/SMTPs/SMTPs.php' );

    $msg_body = stripslashes($email_template_body);

    while (!$rst->EOF)
    {

        $_email_addr = $rst->fields['email'];

        $_full_name = '';

        if ( $rst->fields['first_names'] )
            $_full_name .= $rst->fields['first_names'] . ' ';

        $_full_name .= $rst->fields['last_name'];

        if ( $_full_name )
        {
            if ( $rst->fields['salutation'] )
                $_sal = 'Dear ' . $rst->fields['salutation'] . ' ' . $_full_name . ',' . "\r\n\r\n";

            $_email_full = '"' . $_full_name . '" <' . $_email_addr . '>';
        }
        else
        {
            $_sal = '';

            $_email_full = '<' . $_email_addr . '>';
        }

        $objSMTP = new SMTPs ();

        $objSMTP->setConfig( './SMTPs.ini.php');

        $objSMTP->setFrom ( '<' . $sender_name . '>' );
        $objSMTP->setSubject ( stripslashes($email_template_title) );
        $objSMTP->setTo ( $_email_full );
        $objSMTP->setSensitivity(1);

        $output = $_sal . $msg_body;

        $objSMTP->setBodyContent ( $output );

        $objSMTP->sendMsg ();

/*
        if (!mail($rst->fields['email'], $title, $output, $headers)) {
            echo "<font color=red>There was an error sending email</font>";
            $feedback .= "<font color=red><li>" . $rst->fields['email'] ." FAILED</li></font>";
                        //exit();
        } else{
*/
        $feedback .= "<li>" . $rst->fields['email'] ."</li>";
        //add activity
        $sql_insert_activity = "insert into activities set
                        activity_type_id = 3,
                        user_id = $session_user_id,
                        company_id = ".$rst->fields['company_id'].",
                        contact_id = ".$rst->fields['contact_id'].",
                        activity_title = '".addslashes($title)."',
                        activity_description = '".addslashes($output)."',
                        entered_at = ".$con->dbtimestamp(mktime()).",
                        last_modified_at = ".$con->dbtimestamp(mktime()).",
                        last_modified_by = $session_user_id,
                        scheduled_at=".$con->dbtimestamp(mktime()).",
                        ends_at=".$con->dbtimestamp(mktime()).",
                        activity_status ='c',
                        entered_by = $session_user_id;";
                        $con->execute($sql_insert_activity);
        //}
        $rst->movenext();
    }
    $feedback .= "<hr />Dear [first] [lastname],<p>";
    $feedback .= nl2br(htmlspecialchars($msg_body));
    $rst->close();
}

$con->close();

$page_title = _("'Messages Sent");
start_page($page_title, true, $msg);

?>

<div id="Main">
<div id="Content">

        <table class=widget cellspacing=1>
    <tr>
                 <td class=widget_header><?php echo _("Messages Sent"); ?></td>
    </tr>
    <tr>
                <td class=widget_content><?php echo _("The bulk e-mail sub-system has sent"); ?>:<br>
        <?php echo $feedback;?>
        </td>
    </tr>
    <tr>
                 <td class=widget_header><?php echo _("WARNING"); ?></td>
    </tr>
    <tr>
                <td class=widget_content><?php echo _("DO NOT RELOAD THIS PAGE! If you do, your message will be sent again."); ?>:<br>
        </td>
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
* Revision 1.11  2005/03/17 20:05:26  jswalter
*  - revamped sendmail operation completly
*  - removed the use of internal PHP 'mail()' call
*  - uses new SMTPs.php class object to handle mail
*
* Revision 1.10  2005/02/10 14:40:03  maulani
* - Set last modified info when creating activities
*
* Revision 1.9  2004/12/30 06:40:03  gpowers
* - removed extra single quote from titles
* - added "DO NOT RELOAD" warning
*
* Revision 1.8  2004/12/02 18:21:37  niclowe
* added default email origination from user table, added completed activity when a bulk email is sent
*
* Revision 1.7  2004/08/04 21:46:42  introspectshun
* - Localized strings for i18n/l10n support
* - All paths now relative to include-locations-location.inc
*
* Revision 1.6  2004/07/04 07:51:33  metamedia
* Minor changes and bug fixes to ensure that a mail merge from companies/one.php works.
*
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
