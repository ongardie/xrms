<?php
/**
*
* Show email messages not sent.
*
* $Id: email-4.php,v 1.40 2010/03/05 20:59:22 gopherit Exp $
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
$return_url = $_GET['return_url'];
$optout = $_POST['optout'];

// check to see if we sent this message already
if ( $_SESSION['email_sent'] === false ) {

    $array_of_contacts = $_POST['array_of_contacts'];

    $sender_name = unserialize($_SESSION['sender_name']);
    $sender_address = unserialize($_SESSION['sender_address']);
    $bcc_address = unserialize($_SESSION['bcc_address']);
    $email_template_title = unserialize($_SESSION['email_template_title']);
    $email_template_body = unserialize($_SESSION['email_template_body']);

    $uploadDir = $GLOBALS['file_storage_directory'];
    $attachment_list = $_SESSION['attachment_list'];

    // Loop through entire FILES list and atache them to the message
    if ( $attachment_list ) {
        foreach ( $attachment_list as $_ugly => $_file ) {

            // Skip blank file entries
            if ( $_file == '' ) continue;

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
    } else {
        $page_title = _("Messages Cannot Be Sent");
        $msg = "WARNING: No array of contacts!";
        start_page($page_title, true, $msg);
        end_page();
        exit;
    }
    // loop through the contacts and send each one a copy of the message
    $sql = "select * from contacts where contact_id in (" . $imploded_contacts . ")";
    $rst = $con->execute($sql);

    //$user_contact_id = $_SESSION['user_contact_id'];

    if ($rst) {
        // activity Type
        $activity_type_id = get_activity_type($con, 'ETO');
        $activity_type_id = $activity_type_id['activity_type_id'];

        /*
        Nic Lowe
        This code is very ugly - what does it do?
        It generates an error - Warning: current(): Passed variable is not an array or object in /home/chamel/public_html/xrms/email/email-4.php on line 95
        Why does it do this 3 times?
        */
        $activity_participant_position_id = get_activity_participant_positions($con, 'To', $activity_type_id);
        $activity_participant_position_id = current ( $activity_participant_position_id );
        $activity_participant_position_id = $activity_participant_position_id['activity_participant_position_id'];

        $feedback = '<ul>';

        require_once ( $include_directory . 'classes/SMTPs/SMTPs.php' );

        while (!$rst->EOF) {
            //Not quite sure of the purpose of this code...I would have put it into a function instead...Nic Lowe
            $_email_addr = $rst->fields['email'];
            $_full_name = '';
            if ( $rst->fields['first_names'] ) {
                    $_full_name .= $rst->fields['first_names'] .' '. $rst->fields['last_name'];
            }
            if ( $_full_name ) {
                if ( $rst->fields['salutation'] )
                    $_sal = 'Dear ' . $rst->fields['salutation'] . ' ' . $_full_name . ',' . "\r\n\r\n";
                    $_email_full = '"' . $_full_name . '" <' . $_email_addr . '>';
            } else {
                $_sal = '';
                $_email_full = '<' . $_email_addr . '>';
            }

            //here is where we do the mail merge of the variables
            include_once "mail_merge_functions.inc";
            //echo addslashes($email_template_body); //debug

            // add opt-out message at bottom of e-mail if check box is selected
            if ($optout=='on') {
                $optout_id = $rst->fields['contact_id'] * 195 / 2 * 1956;
                $output1=$email_template_body . "<br /><center>To unsubscribe from this email newsletter instantly, click this link: <a href=\"". $http_site_root . "/email/optout.php?optout_id=". $optout_id ."&email=". $_email_addr ."\">UNSUBSCRIBE</a></center>";
            } else {
                $output1 = $email_template_body;
            }

            $m=mail_merge_email($email_template_title,$output1,$rst->fields['contact_id'],false);
            $msg_subject=$m[0];
            $msg_body=$m[1];

            //echo stripslashes($msg_body); //debug
            $objSMTP = new SMTPs ();
            $objSMTP->setConfig($include_directory.'classes/SMTPs/SMTPs.ini.php');
            $objSMTP->setFrom ( $sender_name . ' <' . $sender_address . '>' );
            $objSMTP->setSubject ( stripslashes($msg_subject) );
            $objSMTP->setTo ( $_email_full );
            $objSMTP->setSensitivity(0); //1 = personal

            // If we have any attachements, attach them
            if ( $_fileData ) {
                // Attach each file in turn
                foreach ( $_fileData as $_file => $_data ) {
                    // Add the attachments
                    $objSMTP->setAttachment ( $_data['content'], $_file, $_data['mime'] );
                }
            }

            $output = $msg_body;
            if (!$output) $output = ' ';

            # Text Version
            $msg1 = str_replace("<br>", "\n", $output);
            $msg1 = str_replace("<p>", "\n\n", $msg1);
            $msg1 = str_replace("<BR>", "\n", $msg1);
            $msg1 = str_replace("<P>", "\n\n", $msg1);
            $msg1 = str_replace("<br />", "\n", $msg1);
            $msg1 = str_replace("&nbsp;", " ", $msg1);
            $txt = strip_tags($msg1);
            //echo "Text: ".$txt."<hr>";
            //$objSMTP->setBodyContent ( $txt );
            $objSMTP->setBodyContent ($output,'html');
            $objSMTP->setTransEncodeType(0); //0=7bit, 1=8bit
            $objSMTP->setMD5flag(true);
            $objSMTP->setSensitivity(0); //0=none
            $objSMTP->setPriority(3); //3=normal

            //this line of code sends the message to the SMTP server
            $mail_result=$objSMTP->sendMsg ();

            //the $mail_result variable checks to see whether it went or not..
            if ($mail_result) {
                    $feedback .= "<li>". $rst->fields['email'] ."</li>";
            } else {
                    $feedback .= "<font color=red><li>FAILED:". $rst->fields['email'] .":".$objSMTP->getErrors()."</li></font>";
            }

            //add activity - check the to see that the mail worked first before you add the activity though........
            // Create "activity" log
            if ($mail_result) {
                $activity_data['contact_id']           = $rst->fields['contact_id']; // the contact this activity related to
                $activity_data['activity_type_id']     = $activity_type_id;  // is pulled from activity_type table
                $activity_data['company_id']           = $rst->fields['company_id']; // which company is this activity related to
                $activity_data['activity_title']       = 'eMail: ' . $msg_subject;
                $activity_data['activity_description'] = 'Bulk email sent';
                $activity_data['activity_status']      = 'c';         // Closed status
                $activity_data['completed_bol']        = true;           // activity is completed

                $participants = array( 'contact_id' => $rst->fields['contact_id'],'activity_participant_position_id' => $activity_participant_position_id);

                //this line adds the activity..
                if ( $activity_id = add_activity($con, $activity_data, $participants ) ) {
                    // Loop through the attched files, if any
                    // and add them to the FILES table
                    //.....this big and nested and seemingly complex code could be better done in a separate function...Nic Lowe..
                    if ( $_fileData ) {
                        foreach ( $_fileData as $_file => $data ) {
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
                            if ( $file_id = add_file_record ( $con, $rec ) ) {
                                // Now we need to UPDATE that same record
                                // We need to RENAME the 'file_filesystem_name' name with the record ID
                                // and a random string for a "secure" file name
                                $rec = array();
                                $rec['file_id']              = $file_id;
                                $rec['file_filesystem_name'] = $file_id . '_' . random_string ( 24 );

                                if ( $_results = modify_file_record( $con, $rec ) ) {
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
            //Move to the next record in the contact data array you passed this script
            $rst->movenext();

        } // END WHILE email addesses

        if ($bcc_address) {
            //send only one copy of a bulk mail to the BCC to prevent multiple BCCs
            $objSMTP = new SMTPs ();
            $objSMTP->setConfig($include_directory.'classes/SMTPs/SMTPs.ini.php');
            $objSMTP->setFrom ( $sender_name . '<' . $sender_address . '>' );
            $msg_subject = "BCC: ".$msg_subject;
            $objSMTP->setSubject ( stripslashes($msg_subject) );
            $objSMTP->setTo ( $bcc_address );
            $objSMTP->setSensitivity(0); //1 = personal

            // If we have any attachements, attach them
            if ( $_fileData ) {
                // Attach each file in turn
                foreach ( $_fileData as $_file => $_data ) {
                    // Add the attachments
                    $objSMTP->setAttachment ( $_data['content'], $_file, $_data['mime'] );
                }
            }

            $output = $msg_body;
            if (!$output) $output = ' ';

            # Text Version
            $msg1 = str_replace("<br>", "\n", $output);
            $msg1 = str_replace("<p>", "\n\n", $msg1);
            $msg1 = str_replace("<BR>", "\n", $msg1);
            $msg1 = str_replace("<P>", "\n\n", $msg1);
            $msg1 = str_replace("<br />", "\n", $msg1);
            $msg1 = str_replace("&nbsp;", " ", $msg1);
            $txt = strip_tags($msg1);
            //echo "Text: ".$txt."<hr>";
            //$objSMTP->setBodyContent ( $txt );
            $objSMTP->setBodyContent ($output,'html');
            $objSMTP->setTransEncodeType(0); //0=7bit, 1=8bit
            $objSMTP->setMD5flag(true);
            $objSMTP->setSensitivity(0); //0=none
            $objSMTP->setPriority(3); //3=normal

            //this line of code sends the message to the SMTP server
            $mail_result=$objSMTP->sendMsg ();
        }

    $rst->close();

    // Set our flag to indiate this message has been sent already
    $_SESSION['email_sent'] = true;

    $feedback .= "</ul><br /><hr /><samp><strong>Subject:</strong></samp><BR>".nl2br(htmlspecialchars($email_template_title))."<br /><br /><samp><strong>Body:</strong></samp><BR>".nl2br(htmlspecialchars($email_template_body));


    } else {
    // Failed to create contact list
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
                // Loop through the attched files, if any, and add display them
                if ( $_fileData ) { ?>
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
            <?php } ?>

            <tr>
                <td class=widget_content_form_element>
                <form action="<?php echo $http_site_root . $return_url; ?>" method="POST">
                    <input class=button value="<?php echo _('Close') ?>" type="submit">
                </form>
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
* $Log: email-4.php,v $
* Revision 1.40  2010/03/05 20:59:22  gopherit
* Added "Close" button to return the user where they came from.
*
* Revision 1.39  2010/02/24 22:33:51  gopherit
* Cleaned up and reorganized the script code.  More can be done in that regard yet.
*
* Revision 1.38  2008/05/29 14:10:54  randym56
* clean up no-contacts error message
*
* Revision 1.37  2007/10/27 01:37:06  randym56
* 1. Fixed BCC (was not working at all)
* 2. Added the function to put custom fields for user sending the email (if the user has a related contact record).
* 3. Added all HTML editing functions for tinymce.
* 4. Enabled the ability for selecting/de-selecting individuals from the list.
* 5. Added an "Opt-out" checkbox that gets added to the footer of the e-mail so that when the URL is clicked by a recipient it moves their e-mail to "opt-out".  This is turned on in the preferences DB (item 24).
*
* Revision 1.36  2006/12/27 14:04:06  jnhayart
* Apply patch from Randy
*
* Revision 1.35  2006/12/11 17:34:36  jnhayart
* add Translation
* and force email in HTML (temporary) for test
*
* Revision 1.34  2006/11/02 12:38:57  niclowe
* Fixed bug http://sourceforge.net/forum/message.php?msg_id=3990616
*
* Revision 1.33  2006/10/26 20:07:37  niclowe
* fixed activity handling.
* BUG [ 1289036 ] Mail: Add activity on mail merge
*
* Activities are now added for each contact that an email is sent to instead of adding to a participant list.
* In addition, the contact id of the activity is now set - its wasnt in the past reculting in orphaned company records (had a company id but not contact id)
*
* Revision 1.31  2006/10/26 08:57:56  niclowe
* -added custom field to mail merge
* -added error trapping for emails that fail silently (or appear to have worked)
* -added mail merge preview for custom emails
*
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