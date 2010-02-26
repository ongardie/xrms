<?php
/**
 *
 * Confirm email recipients.
 *
 * $Id: email-3.php,v 1.26 2010/02/26 19:45:32 gopherit Exp $
 */


require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

    // Set our flag to indiate this message has not been sent yet
    $_SESSION['email_sent'] = false;

    //Turn $_POST array into variables
    extract($_POST);

// see if a file was sent "piggy-back"
if ( $_FILES['attach']['error'] == 0 )
{
    require_once $include_directory . 'classes/File/file_upload.php';

    // Create new Class
    $objUpFile = new file_upload( 'attach' );

    if ( $objUpFile->getErrorCode() )
    {
        echo 'Could not create Upload Object: ';
        echo $objUpFile->getErrorMsg();
        exit;
    }

    // Where do we want this file sent to
    $objUpFile->setDestDir ( $GLOBALS['file_storage_directory'] );

    if ( $objUpFile->getErrorCode() )
    {
        echo 'Could not set Upload Directory: ';
        echo $objUpFile->getErrorMsg();
        exit;
    }

    // Now process uploaded file
    $objUpFile->processUpload();

    if ( $objUpFile->getErrorCode() )
    {
        echo 'Could not process upload file: ';
        echo $objUpFile->getErrorMsg();
        exit;
    }

    // place new upload into the array
    $attachment_list = $_SESSION['attachment_list'];
    $attachment_list[$objUpFile->getFilename()] = $objUpFile->getFilename();
    $_SESSION['attachment_list'] = $attachment_list;
}

$_SESSION['sender_name'] = serialize($sender_name);
$_SESSION['sender_address'] = serialize($sender_address);
$_SESSION['bcc_address'] = serialize($bcc_address);
$_SESSION['email_template_title'] = serialize($email_template_title);
$_SESSION['email_template_body'] = serialize($email_template_body);
$_SESSION['uploadDir'] = serialize( $xrms_file_root . '/upload' );


$array_of_contacts = unserialize($_SESSION['array_of_contacts']);

if (is_array($array_of_contacts))
    $imploded_contacts = implode(',', $array_of_contacts);
else
    echo _("WARNING: No array of contacts!") . "<br>";

$con = get_xrms_dbconnection();
//$con->debug = 1;

$sql = "select cont.contact_id, cont.email, cont.first_names, cont.last_name, c.company_name, u.username,c.company_id, cont.email_status
from contacts cont, companies c, users u
where c.company_id = cont.company_id
and c.user_id = u.user_id
and cont.contact_id in ($imploded_contacts)
and length(cont.email) > 0
and contact_record_status = 'a' order by c.company_name,cont.last_name asc";

$_x = 1;

$rst = $con->execute($sql);
if ($rst) {
    while (!$rst->EOF) {
        $contact_rows .= '<tr>';
        $contact_rows .= '<td class="widget_content_form_element">';
          switch ($rst->fields['email_status']) {
          case "a" : // active emails;
                    $contact_rows .= '<input type="checkbox" name="array_of_contacts[]" id="array_of_contacts_' . $_x++ . '" value="' . $rst->fields['contact_id'] . '" checked="checked"></td>';
                    break;
          case "o" : // opted out emails;
                    $contact_rows .= '<input type="checkbox" name="array_of_contacts[]" id="array_of_contacts_' . $_x++ . '" value="' . $rst->fields['contact_id'] . '">'._("Opted Out").'</td>';
                    break;
          case "b" : // code to do something;
                    $contact_rows .= '<input type="checkbox" name="array_of_contacts[]" id="array_of_contacts_' . $_x++ . '" value="' . $rst->fields['contact_id'] . '">'._("Bounced").'</td>';
                    break;
          case "" : // code to do something;
                    break;
          default : // code to do something;
                    $contact_rows .= '<input type="checkbox" name="array_of_contacts[]" id="array_of_contacts_' . $_x++ . '" value="' . $rst->fields['contact_id'] . '" checked="checked"></td>';
        }
        $contact_rows .= '<td class="widget_content">' . $rst->fields['company_name'] . '</td>';
        $contact_rows .= '<td class="widget_content">' . $rst->fields['username'] . '</td>';
        $contact_rows .= '<td class="widget_content">' . $rst->fields['first_names'] . ' ' . $rst->fields['last_name'] . '</td>';
        $contact_rows .= '<td class="widget_content">' . $rst->fields['email'] . '</td>';
        $contact_rows .= '<td class="widget_content"><a href="email_merge_preview.php?contact_id='.$rst->fields['contact_id'].'&company_id='.$rst->fields['company_id'].'" target=_blank>Preview</a></td>';
        $contact_rows .= "</tr>\n";
        $rst->movenext();
    }
    $count=$_x-1; // get last count of listed records
    $rst->close();
}

// added by Randy to show opt-out message if preference is set to yes
$sql_opt_out = "SELECT * FROM user_preferences WHERE user_preference_type_id='25'";
$rst_opt_out = $con->execute($sql_opt_out);
if ($rst_opt_out->fields['user_preference_value'] == 'y') $show_opt_out = true; else $show_opt_out = false;

$con->close();

// Provide a hook to allow plugins to use their own bulk mailer scripts
// rather than the built-in SMTP.
$bulk_mailer = do_hook_function('bulk_mailer', $http_site_root);
if (!$bulk_mailer)
    $bulk_mailer = "email-4.php";

$page_title = _("Confirm Recipients");
start_page($page_title, true, $msg);

?>

<script type="text/javascript"><!--

    function checkAll() {
        for (i = 1; i < <?php echo $_x; ?>; i++)
            document.getElementById('array_of_contacts_' + i).checked = true ;
    }

    function uncheckAll() {
        for (i = 1; i < <?php echo $_x; ?>; i++)
            document.getElementById('array_of_contacts_' + i).checked = false ;
    }
    function switchAll() {
        for (i = 1; i < <?php echo $_x; ?>; i++)
            if (document.getElementById('array_of_contacts_' + i).checked == true)
                document.getElementById('array_of_contacts_' + i).checked = false;
            else
                document.getElementById('array_of_contacts_' + i).checked = true ;
    }
//--></script>


<div id="Main">
    <div id="Content">
        <form action="<?php echo $bulk_mailer; ?>" method="post" name="sendmail">
            <table class="widget" cellspacing="1">

                <tr>
                    <td class=widget_header colspan=6><?php echo _("Confirm Recipients"); ?></td>
                </tr>

                <tr>
                    <td class="widget_label">&nbsp;</td>
                    <td class="widget_label"><?php echo _("Company"); ?></td>
                    <td class="widget_label"><?php echo _("Owner"); ?></td>
                    <td class="widget_label"><?php echo _("Contact"); ?></td>
                    <td class="widget_label"><?php echo _("E-Mail"); ?></td>
                    <td class="widget_label"><?php echo _("Preview"); ?></td>
                </tr>

                <?php  echo $contact_rows ?>

                <tr>
                    <td class="widget_content_form_element" colspan="6">
                        <input type="submit" class="button" value="<?php echo _("Continue"); ?>">&nbsp;
                            <?php if ($show_opt_out) {?>
                                <input type="checkbox" name="optout"><?php echo _("Opt-Out Message"); ?>?&nbsp;
                            <?php } ?>
                        <input type=button value="Check All" onClick="checkAll()">&nbsp;
                        <input type=button value="Uncheck All" onClick="uncheckAll()">&nbsp;
                        <input type=button value="Switch All" onClick="switchAll()">
                    </td>
                </tr>

                <tr>
                    <td class="widget_content_form_element" colspan="6">
                        <font color="#FF0000"><strong>NOTE: Selecting more than 10 contacts may take a <u>long time</u>... Please be patient - DO NOT REFRESH or Click Send more than ONE TIME or you may send mail multiple times to the same contacts.</strong></font>
                    </td>
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
 * Revision 1.26  2010/02/26 19:45:32  gopherit
 * Provided a 'bulk_mailer' hook to allow plugins to use their own bulk mailer scripts rather than the built-in SMTP.
 *
 * Revision 1.25  2010/02/24 18:25:18  gopherit
 * Oops... Submitted older file version with a missing <tr> tag.  All good now.
 *
 * Revision 1.24  2010/02/24 18:20:29  gopherit
 * Fixed: Added JavaScript for the 'Check All', 'Uncheck All' and 'Switch All' buttons functionality.  See Bug artifact #2958093.
 *
 * Revision 1.23  2007/10/27 01:36:49  randym56
 * 1. Fixed BCC (was not working at all)
 * 2. Added the function to put custom fields for user sending the email (if the user has a related contact record).
 * 3. Added all HTML editing functions for tinymce.
 * 4. Enabled the ability for selecting/de-selecting individuals from the list.
 * 5. Added an "Opt-out" checkbox that gets added to the footer of the e-mail so that when the URL is clicked by a recipient it moves their e-mail to "opt-out".  This is turned on in the preferences DB (item 24).
 *
 * Revision 1.22  2007/06/13 16:51:42  niclowe
 * fixed bug where opted out email addresses werent counted in bulk mailer.
 *
 * Revision 1.20  2006/10/26 08:57:56  niclowe
 * -added custom field to mail merge
 * -added error trapping for emails that fail silently (or appear to have worked)
 * -added mail merge preview for custom emails
 *
 * Revision 1.19  2006/01/02 23:02:14  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.18  2005/09/20 14:37:06  ycreddy
 * Fix to how file_upload.php is included
 *
 * Revision 1.17  2005/07/08 19:56:16  jswalter
 *  - added 'email_sent' flag to stop users for sending messages multiple times
 *
 * Revision 1.16  2005/07/08 19:00:46  jswalter
 *  - modified upload path to use $GLOBALS['file_storage_directory']
 *
 * Revision 1.15  2005/07/08 01:33:15  jswalter
 *  - modified $_SESSION['attachment_list'] handling
 *
 * Revision 1.14  2005/06/24 16:55:48  jswalter
 *  - made HTML more XHTML comliant
 *  - added FILE submit processing
 * Bug 310
 *
 * Revision 1.13  2005/06/22 22:30:14  jswalter
 *  - added ID attribute to the person checkbox objects
 *  - checkbox ID attribute will increment for each checkbox created
 *
 * Revision 1.12  2005/06/15 14:21:14  braverock
 * - add more compliant quoting of HTML and checkbox options
 * - add better input validation for checking array from $_POST
 *
 * Revision 1.11  2005/05/25 21:22:43  braverock
 * - change name array_of_contacts[] to array_of_contacts to solve IE compatibility problem
 *
 * Revision 1.10  2004/08/26 22:55:26  niclowe
 * Enabled mail merge functionality for companies/some.php
 * Sorted pre-sending email checkbox page by company then contact lastname
 * Enabled mail merge for advanced-search companies
 *
 * Revision 1.9  2004/08/18 00:06:17  niclowe
 * Fixed bug 941839 - Mail Merge not working
 *
 * Revision 1.8  2004/08/04 21:46:42  introspectshun
 * - Localized strings for i18n/l10n support
 * - All paths now relative to include-locations-location.inc
 *
 * Revision 1.7  2004/07/04 07:51:33  metamedia
 * Minor changes and bug fixes to ensure that a mail merge from companies/one.php works.
 *
 * Revision 1.6  2004/07/03 15:03:52  metamedia
 * Minor bug fixes so that a mail merge from company/one.php (and hopefully other pages) will work.
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