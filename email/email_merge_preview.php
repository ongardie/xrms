<?php
/**
 *
 * Confirm email recipients.
 *
 * $Id: email_merge_preview.php,v 1.2 2010/03/05 17:54:53 gopherit Exp $
 */


require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

//$_SESSION['email_template_title'] = serialize($email_template_title);
//$_SESSION['email_template_body'] = serialize($email_template_body);
$contact_id=$_GET['contact_id'];
$page_title = _("Email Merge Preview");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">
        <?
            include_once "mail_merge_functions.inc";
            $prev_email_template_title=unserialize($_SESSION['email_template_title']);
            $prev_email_template_body= unserialize($_SESSION['email_template_body']);
            //echo $email_template_title;exit;
            $m=mail_merge_email($prev_email_template_title,$prev_email_template_body,$contact_id,$address_id="");
        ?>

        <samp><strong>Subject</strong></samp><br />
        <? echo $m[0];?><br /><br />

        <samp><strong>Body</strong></samp><br />
        <? echo $m[1];?>
    </div>

    <!-- right column //-->
    <div id="Sidebar">&nbsp;</div>
</div>

<?php

end_page();

/**
 * $Log: email_merge_preview.php,v $
 * Revision 1.2  2010/03/05 17:54:53  gopherit
 * FIXED: The script assumed a text-only template and rendered HTML templates with nl2br() which resulted in extra line feeds.
 *
 * Revision 1.1  2006/10/26 08:57:56  niclowe
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
