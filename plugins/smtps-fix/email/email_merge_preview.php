<?php
/**
 *
 * Confirm email recipients.
 *
 * $Id: email_merge_preview.php,v 1.1 2008/03/15 16:54:31 randym56 Exp $
 */


require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-mail-merge.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$con = get_xrms_dbconnection();

$contact_id=$_GET['contact_id'];
$email_template_title = unserialize($_SESSION['email_template_title']);
$email_template_body = unserialize($_SESSION['email_template_body']);


?>

<HTML>
<body>
<?
    //echo $email_template_title."<p>".$email_template_body;exit;
    $m=mail_merge_email($con,$email_template_title,$email_template_body,$contact_id);
?><samp><strong>Subject</strong></samp><br />

<?      echo nl2br($m[0]);?><br />
<br />

<samp><strong>Body:</strong></samp><hr />

<?      
//echo nl2br($m[1]);
echo $m[1];
?>

</div>

<?php


/**
 * $Log: email_merge_preview.php,v $
 * Revision 1.1  2008/03/15 16:54:31  randym56
 * Updated SMTPs to allow for individual user SMTP addressing - requires installation and activation of mcrypt in PHP - follow README.txt instructions
 *
 * Revision 1.1  2006/10/26 08:57:56  niclowe
 * -added custom field to mail merge
 * -added error trapping for emails that fail silently (or appear to have worked)
 * -added mail merge preview for custom emails
 *
 */
?>
