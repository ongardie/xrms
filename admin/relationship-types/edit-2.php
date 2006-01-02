<?php
/**
 * Edit a relationship type
 *
 * $Id: edit-2.php,v 1.3 2006/01/02 22:03:16 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$relationship_type_id = $_POST['relationship_type_id'];
$relationship_name = $_POST['relationship_name'];
$from_what_table = $_POST['from_what_table'];
$to_what_table = $_POST['to_what_table'];
$from_what_text = $_POST['from_what_text'];
$to_what_text = $_POST['to_what_text'];
$pre_formatting = $_POST['pre_formatting'];
$post_formatting = $_POST['post_formatting'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM relationship_types WHERE relationship_type_id = $relationship_type_id";
$rst = $con->execute($sql);

$rec = array();
$rec['relationship_name'] = $relationship_name;
$rec['from_what_table'] = $from_what_table;
$rec['to_what_table'] = $to_what_table;
$rec['from_what_text'] = $from_what_text;
$rec['to_what_text'] = $to_what_text;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$rec = array();
$rec['pre_formatting'] = $pre_formatting;
$rec['post_formatting'] = $post_formatting;

define('ADODB_FORCE_NULLS',1);
$upd = $con->GetUpdateSQL($rst, $rec, true, get_magic_quotes_gpc());
define('ADODB_FORCE_NULLS',0);
$con->execute($upd);

$con->close();

header("Location: some.php");

/**
 * $Log: edit-2.php,v $
 * Revision 1.3  2006/01/02 22:03:16  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.2  2004/09/29 14:38:38  braverock
 * - add phpdoc
 * - rationalize indentation
 * - fix return url after edit
 *
 */
?>