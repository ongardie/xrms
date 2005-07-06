<?php
/**
*
* Show email messages not sent.
*
* $Id: email-4.php,v 1.18 2005/07/06 16:44:42 braverock Exp $
*/

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-activities.php');
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

$uploadDir = unserialize($_SESSION['uploadDir']);
$attachment_list = explode ( '|', unserialize($_SESSION['attachment_list']) );

foreach ( $attachment_list as $_file )
{
    if ( $_file == '' )
        continue;

    // Create array to store file data
    $_fileData[$_file] = array();

    // Full path
    $_fileData[$_file]['path'] = $uploadDir . '/' . $_file;

    if (!function_exists('mime_content_type')) {
        // this version of PHP doesn't have the mime functions
        // compiled in, so load our drop-in replacement function
        // instead
        include($include_directory.'mime/mime-array.php');
    }
    // we need the file's MIME type
    $_fileData[$_file]['mime'] = mime_content_type ( $_fileData[$_file]['path'] );

    // we need the file itself
    $_fileData[$_file]['content'] = getFile($_fileData[$_file]['path']);
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

if (is_array($array_of_contacts)) {
    $imploded_contacts = implode(',', $array_of_contacts);
} elseif (is_numeric($array_of_contacts)) {
    $imploded_contacts= $array_of_contacts;
}else {
    echo _("WARNING: No array of contacts!") . "<br>";
}
// loop through the contacts and send each one a copy of the message
$sql = "select * from contacts where contact_id in (" . $imploded_contacts . ")";
$rst = $con->execute($sql);

//$user_contact_id = $_SESSION['user_contact_id'];


if ($rst) {

    // activity Type
    $activity_type_id = get_activity_type($con, 'ETO');
    $activity_type_id = $activity_type_id['activity_type_id'];

    $activity_participant_position_id = get_activity_participant_positions($con, 'To', $activity_type_id);
    $activity_participant_position_id = current ( $activity_participant_position_id );
    $activity_participant_position_id = $activity_participant_position_id['activity_participant_position_id'];


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

        // If we have any attachements, attach them
        if ( $_fileData )
        {
            // Attach each file in turn
            foreach ( $_fileData as $_file => $_data )
            {
                // Add the attachments
                $objSMTP->setAttachement ( $_data['content'], $_file, $_data['mime'] );
            }
        }

        $output = $msg_body;

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
        if ( ! $company_id )
            $company_id = $rst->fields['company_id'];

        $participants[] = array( 'contact_id' => $rst->fields['contact_id'],
                                'activity_participant_position_id' => $activity_participant_position_id);

        //}
        $rst->movenext();
    }   // WHILE email addesses

    $feedback .= "<hr />Dear [first] [lastname],<p>";
    $feedback .= nl2br(htmlspecialchars($msg_body));
    $rst->close();

    // Create "activity" log
    $activity_data['activity_type_id']     = $activity_type_id;  // is pulled from activity_type table
    $activity_data['company_id']           = $company_id; // which company is this activity related to
    $activity_data['activity_title']       = 'eMail: ' . $email_template_title;
    $activity_data['activity_description'] = $output;
    $activity_data['activity_status']      = 'c';         // Closed status
    $activity_data['completed_bol']        = true;           // activity is completed

/*
 * - on_what_table           - what the activity is attached or related to
 * - on_what_id              - which ID to use for this relationship
 * - on_what_status          - workflow status
*/
    if ( ! add_activity($con, $activity_data, $participants ) )
    {
        echo '$activity_data error!';
        exit;
    }
} else {
    db_error_handler($con, $sql);
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

// =============================================================
// =============================================================
/**
* This function will read a file in
* from a supplied filename and return it.
*/
function getFile($filename)
{
    $return = '';
    if ($fp = fopen($filename, 'rb')) {
        while (!feof($fp)) {
            $return .= fread($fp, 1024);
        }
        fclose($fp);
        return $return;

    } else {
        return false;
    }

};

/**
* $Log: email-4.php,v $
* Revision 1.18  2005/07/06 16:44:42  braverock
* - fix syntax error in if check
*
* Revision 1.17  2005/07/06 14:23:52  braverock
* - add check to make sure that mime_content_type fn exists
* - load our replacement mime_content_type fn if needed
*
* Revision 1.16  2005/06/24 16:58:20  jswalter
*  - made HTML more XHTML comliant
*  - added FILE attachement processing
*  - @TODO; Current version of SMTPs.php does not allow multiple attachements. Need to correct this, soon.
* Bug 310
*
* Revision 1.15  2005/06/22 22:26:19  jswalter
*  - MAIL MERGE will add an "activity record"
* Bug 442
*
* Revision 1.14  2005/06/15 14:24:08  braverock
* - add db_error_handler to result set check
*
* Revision 1.13  2005/06/15 14:21:15  braverock
* - add more compliant quoting of HTML and checkbox options
* - add better input validation for checking array from $_POST
*
* Revision 1.12  2005/03/17 22:07:33  braverock
*
* - modified to store subject of email as activity title
* - modified to use db_error_handler
*
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
