<?php
/**
 * Save Updated Email Template to the database
 *
 * $Id: update-template.php,v 1.2 2010/08/06 22:12:56 gopherit Exp $
 */
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$type = $_GET['type'];

$email_template_id = $_POST['email_template_id'];
$email_template_title = (strlen($_POST['email_template_title']) > 0) ? $_POST['email_template_title'] : _("Template").'&nbsp;'.$email_template_id;
$email_template_body = $_POST['email_template_body'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM email_templates WHERE email_template_id = $email_template_id";
$rst = $con->execute($sql);

$rec = array();
$rec['email_template_title'] = $email_template_title;
$rec['email_template_body'] = $email_template_body;

if ($type == 'update') {
	$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
	$con->execute($upd);
        }
if ($type == 'new') {
	$tbl = 'email_templates';
	$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
	$con->execute($ins);
	}
if ($type == 'delete') {
	$rec['email_template_record_status'] = 'd';

	$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
	$con->execute($upd);
        }
        
$con->close();

header("Location: email_template_list.php?email_template_id=$email_template_id&msg=saved");

/**
 * $Log: update-template.php,v $
 * Revision 1.2  2010/08/06 22:12:56  gopherit
 * Updated the Administrative email template editing functionality to mirror the eMailMerge editing scripts: added user fields, additional CKEditor buttons and fixed a quote escaping bug.
 *
 * Revision 1.1  2008/09/13 14:39:12  randym56
 * Added delete and save-as-new functions
 *
 * Revision 1.6  2006/04/18 15:36:48  braverock
 * - localize Template for i18n
 * - add phpdoc
 *
 */
?>
