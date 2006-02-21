<?php
/**
*
* Show email messages not sent.
*
* $Id: email-4.php,v 1.30 2006/02/21 01:58:19 vanmer Exp $
*
* @todo use a more secure method than 'unlink' to delete files after sending them
*/

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-files.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

// check to see if we sent this message already
if ( $_SESSION['email_sent'] === false )
{

    $array_of_contacts = $_POST['array_of_contacts'];

    $sender_name = unserialize($_SESSION['sender_name']);
    $sender_address = unserialize($_SESSION['sender_address']);
    $bcc_address = unserialize($_SESSION['bcc_address']);
    $email_template_title = unserialize($_SESSION['email_template_title']);
    $email_template_body = unserialize($_SESSION['email_template_body']);

    $uploadDir = $GLOBALS['file_storage_directory'];
    $attachment_list = $_SESSION['attachment_list'];

    // Loop through entire FILES list and atache them to the message
    if ( $attachment_list )
    {
        foreach ( $attachment_list as $_ugly => $_file )
        {
            if ( $_file == '' )
                continue;

            // Create array to store file data
            $_fileData[$_file] = array();

            // Full path
            $_fileData[$_file]['path'] = $GLOBALS['file_storage_directory'] . $_ugly;

        // NOTE: comented this out until we figure out why PHP method barfs
        //    if (!function_exists('mime_content_type')) {
                // this version of PHP doesn't have the mime functions
                // compiled in, so load our drop-in replacement function
                // instead
                require_once($include_directory . 'mime/mime-array.php');
        //    }
            // we need the file's MIME type
            $_fileData[$_file]['mime'] = mime_content_type_ ( $_file );

            // we need the file itself
            $_fileData[$_file]['content'] = getFile($_fileData[$_file]['path']);

            // We need the these later
            $_fileData[$_file]['file_filesystem_name'] = $_ugly;
            $_fileData[$_file]['size']                 = strlen($_fileData[$_file]['content']);
        }
    }

    $con = get_xrms_dbconnection();
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

            $objSMTP->setConfig( $xrms_file_root.'/include/classes/SMTPs/SMTPs.ini.php');

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
                    $objSMTP->setAttachment ( $_data['content'], $_file, $_data['mime'] );
                }
            }

            $output = $msg_body;
            if (!$output) $output = ' ';

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

        $rst->close();

        // Set our flag to indiate this message has been sent already
        $_SESSION['email_sent'] = true;

        $feedback .= nl2br(htmlspecialchars($msg_body));

        // Create "activity" log
        $activity_data['activity_type_id']     = $activity_type_id;  // is pulled from activity_type table
        $activity_data['company_id']           = $company_id; // which company is this activity related to
        $activity_data['activity_title']       = 'eMail: ' . $email_template_title;
        $activity_data['activity_description'] = $output;
        $activity_data['activity_status']      = 'c';         // Closed status
        $activity_data['completed_bol']        = true;           // activity is completed

        if ( $activity_id = add_activity($con, $activity_data, $participants ) )
        {
            // Loop through the attched files, if any
            // and add them to the FILES table
            if ( $_fileData )
            {
                foreach ( $_fileData as $_file => $data )
                {
                    // Create new RECORD array '$rec' for SQL INSERT
                    $rec = array();

                    // File data
                    $rec['file_filesystem_name']   = $_file;
                    $rec['file_name']              = $_file;
                    $rec['file_size']              = $_fileData[$_file]['size'];
                    $rec['file_type']              = $_fileData[$_file]['mime'];

                    // These values, if not defined, will be set by default values defined within the Database
                    // Therefore they do not need to be created within this array for RECORD insertion
                    $rec['on_what_table'] = 'activities';
                    $rec['on_what_id']    = $activity_id;

                    // Add record to FILES table
                    if ( $file_id = add_file_record ( $con, $rec ) )
                    {
                        // Now we need to UPDATE that same record
                        // We need to RENAME the 'file_filesystem_name' name with the record ID
                        // and a random string for a "secure" file name
                        $rec = array();
                        $rec['file_id']              = $file_id;
                        $rec['file_filesystem_name'] = $file_id . '_' . random_string ( 24 );

                        if ( $_results = modify_file_record( $con, $rec ) )
                        {
                            // Write the contents out to disk
                            $_fullPath = $GLOBALS['file_storage_directory'] . $rec['file_filesystem_name'];
                            $fp = fopen  ($_fullPath, 'w+b');
                                fputs  ($fp, $_fileData[$_file]['content'] );
                                fclose ($fp);
                        }
                    }
                }
            }
        }
    }
    // Failed to create contact list
    else
    {
        db_error_handler($con, $sql);
    }

    if ( $attachment_list ) {
        foreach ( $attachment_list as $_file1 ){
            $old_fullPath = $GLOBALS['file_storage_directory'] . $_file1;
            /** @todo eventually unlink should be replaced by a more secure method **/
            unlink ($old_fullPath);
        }
    }

    $con->close();
} else { // Message has been sent already!
    $feedback = '<p /><b>' . _("This email message has already been sent") . '.</b>';
}


$page_title = _("Messages Sent");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">
        <table class="widget" cellspacing="1">
            <tr>
                <td class="widget_header">
                    <?php echo _("Messages Sent"); ?>
                </td>
            </tr>
            <tr>
                <td class="widget_content">
                    <?php echo _("The bulk e-mail sub-system has sent"); ?>:<br>
                    <?php echo $feedback;?>
                </td>
            </tr>
<?php
        // Loop through the attched files, if any
        // and add display them
        if ( $_fileData )
        {
?>
            <tr>
                <td class="widget_header">
                    <?php echo _("Attached Files"); ?>
                </td>
            </tr>
          <tr>
                <td class="widget_content">
  <?php
            foreach ( $_fileData as $_file => $data )
            {
                echo '&nbsp;&nbsp;&nbsp; * ' . $_file . '<br />';
            }
?>
                </td>
            </tr>
<?php
        }
?>        </table>
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
function getFile($file_to_open)
{
    $chunksize=1*(1024*1024);

    $return = '';
    //open and output file contents
    if (is_file($file_to_open)){
        $fp = fopen($file_to_open, 'rb');
        if ($fp) {
            while (!feof($fp)) {
                $return = fread($fp, $chunksize);
            } //end while
            fclose ($fp);
        } else {
            //file open failed
            //should put an error here
        }
        return $return;
    } else { //end is_file test, should error if this isn't a file
        return false;
    }
};

/**
* $Log: email-4.php,v $
* Revision 1.30  2006/02/21 01:58:19  vanmer
* - changed to use SMTPs.ini.php file from include/classes/SMTPs instead of email directory
*
* Revision 1.29  2006/01/02 23:02:14  vanmer
* - changed to use centralized dbconnection function
*
* Revision 1.28  2005/10/10 12:31:41  braverock
* - remove trailing whitespace
*
* Revision 1.27  2005/10/10 12:31:05  braverock
* - fix bug where no email sent if no body
* - fix bug where temporary files are not deleted, causing security and collision problems
*   - credit patches to Daniele Baudone (SF:dbaudone)
*
* Revision 1.26  2005/08/23 16:51:05  braverock
* - fix typo in SetAttachment() call
*
* Revision 1.25  2005/07/20 22:15:30  jswalter
*  - corrected issue around an empty "$attachment_list'
*
* Revision 1.24  2005/07/08 21:18:52  jswalter
*  - added conditional check so message is not sent more than once
*
* Revision 1.23  2005/07/08 19:29:45  jswalter
*  - added access to 'utils-files.php'
*  - added properties to the attachment array
*  - Attached files, ad-hoc and pre-defined, are added to FILES table
*  - attached files are now written to disk with secure names
* Bug 309
* Bug 310
* Bug 311
*
* Revision 1.22  2005/07/08 15:15:24  braverock
* - use custom mime_content_type_ fn to avoid problems w/ php std fn
* - change getfile fn to do better tests and read bigger blocks at a time
*
* Revision 1.21  2005/07/08 02:14:00  jswalter
*  - corrected typo in mime_type call
*
* Revision 1.20  2005/07/08 01:43:29  jswalter
*   - added note about commented-out 'mime_content_type()' check
*   - modified file attachement processing to handle new method
* Bug 311
*
* Revision 1.19  2005/07/06 18:17:16  braverock
* - change back to custom function as php std mime_content_type fn
*   causes problems on several configs
*
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