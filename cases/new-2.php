<?php
/**
 * Insert a new Case into the Database
 *
 * $Id: new-2.php,v 1.13 2006/04/29 01:46:12 vanmer Exp $
 */
 
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-cases.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check('','Create');

getGlobalVar($return_url, 'return_url');

getGlobalVar($case_type_id, 'case_type_id');

//set from _POST since getGlobalVar wasn't giving a value, probably session confusion
$case_type_id=$_POST['case_type_id'];

getGlobalVar($case_status_id, 'case_status_id');
getGlobalVar($case_priority_id, 'case_priority_id');
getGlobalVar($user_id, 'user_id');
getGlobalVar($company_id, 'company_id');
getGlobalVar($division_id, 'division_id');
getGlobalVar($contact_id, 'contact_id');
getGlobalVar($case_title, 'case_title');
getGlobalVar($due_at, 'due_at');
getGlobalVar($case_description, 'case_description');

$con = get_xrms_dbconnection();
// $con->debug = 1;


//print_r($_POST);

//save to database
$rec = array();
$rec['case_type_id'] = $case_type_id;
$rec['case_status_id'] = $case_status_id;
$rec['case_priority_id'] = $case_priority_id;
$rec['user_id'] = $user_id;
$rec['company_id'] = $company_id;
$rec['division_id'] = $division_id;
$rec['contact_id'] = $contact_id;
$rec['case_title'] = $case_title;
$rec['case_description'] = $case_description;
$rec['due_at'] = strtotime($due_at);
$rec['entered_at'] = time();
$rec['entered_by'] = $session_user_id;
$rec['last_modified_at'] = time();
$rec['last_modified_by'] = $session_user_id;

$case_id=add_case($con, $rec, get_magic_quotes_gpc());

if ($case_id) {
    if (!$return_url) {
        header("Location: one.php?msg=case_added&case_id=$case_id");
    } else {
        $return_url=str_replace('XXX-case_id-XXX',$case_id, $return_url);
        header("Location: $return_url");
    }
} else {
    $msg=_("Failed to add case");
    header("Location: some.php?msg=$msg");

}

$con->close();


/**
 * $Log: new-2.php,v $
 * Revision 1.13  2006/04/29 01:46:12  vanmer
 * - moved workflow activity instantiation into cases API and out of edit-2.php and new-2.php
 *
 * Revision 1.12  2006/04/28 02:56:59  vanmer
 * - updated cases UI to use the new cases API functions
 * - altered workflow functions to use activities API
 *
 * Revision 1.11  2006/01/02 22:47:25  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.10  2005/03/29 23:52:48  maulani
 * - Add audit trail
 *
 * Revision 1.9  2005/01/13 18:13:12  vanmer
 * - Basic ACL changes to allow create functionality to be restricted
 *
 * Revision 1.8  2005/01/10 23:32:32  braverock
 * - add phpdoc
 *
 */
?>