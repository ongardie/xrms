<?php
/**
 * Export contacts from the search rwsults from contacts/some.php
 *
 * $Id: export.php,v 1.6 2005/01/13 22:39:30 braverock Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'adodb/toexport.inc.php');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$session_user_id = session_check();

$last_name = $_POST['last_name'];
$first_names = $_POST['first_names'];
$title = $_POST['title'];
$description = $_POST['description'];
$company_name = $_POST['company_name'];
$company_code = $_POST['company_code'];
$company_type_id = $_POST['company_type_id'];
$category_id = $_POST['category_id'];
$user_id = $_POST['user_id'];




$sql = "select
   cont.salutation as 'Salutation',
   cont.first_names as 'First Name',
   cont.last_name as 'Last Name',
   c.company_name as 'Company',
   c.company_code as 'Code',
   cont.title as 'Title',
   cont.description as 'Description',
   cont.email as 'eMail',
   cont.work_phone as 'Direct phone',
   a.line1 as 'Line 1',
   a.line2 as 'Line 2',
   a.postal_code as 'Postal Code',
   a.city as 'City',
   u.username as 'Owner' ";

$from = "from contacts cont, companies c, addresses a, users u ";

$where .= "where c.company_id = cont.company_id ";
$where .= "and cont.address_id = a.address_id ";
$where .= "and c.user_id = u.user_id ";
$where .= "and contact_record_status = 'a'";

$criteria_count = 0;

if (strlen($last_name) > 0) {
    $criteria_count++;
    $where .= " and cont.last_name like " . $con->qstr('%' . $last_name . '%', get_magic_quotes_gpc());
}

if (strlen($first_names) > 0) {
    $criteria_count++;
    $where .= " and cont.first_names like " . $con->qstr('%' . $first_names . '%', get_magic_quotes_gpc());
}

if (strlen($title) > 0) {
    $criteria_count++;
    $where .= " and cont.title like " . $con->qstr($title . '%', get_magic_quotes_gpc());
}

if (strlen($description) > 0) {
    $criteria_count++;
    $where .= " and cont.description like " . $con->qstr($description . '%', get_magic_quotes_gpc());
}

if (strlen($company_name) > 0) {
    $criteria_count++;
    $where .= " and c.company_name like " . $con->qstr($company_name . '%', get_magic_quotes_gpc());
}

if (strlen($company_code) > 0) {
    $criteria_count++;
    $where .= " and c.company_code like " . $con->qstr($company_code, get_magic_quotes_gpc());
}

if (strlen($category_id) > 0) {
    $criteria_count++;
    $from .= ", entity_category_map ecm ";
    $where .= " and ecm.on_what_table = 'contacts' and cont.contact_id = ecm.on_what_id and ecm.category_id = $category_id ";

}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $where .= " and c.user_id = $user_id";
}


$sql .= $from . $where . $group_by ;



// echo $sql ;

$rst = $con->execute($sql);

$filename =  'contacts_' . . date('Y-m-d_H-i') . . '.csv';

if ($rst) {
    $csvdata= rs2csv($rst);
    if ($csvdata) {
      $filesize = strlen($csvdata);
    }  
    $rst->close();
} else {
    echo "<p>" . _("There was a problem with your export") . ":\n";
    if (!$csvdata) {
        echo "<br>" . _("Unable to create file") . ": $xrms_file_root.$tmp_export_directory/$filename \n";
    }
    if (!$rst) {
        db_error_handler($con,$sql);
    }
}

$con->close();

SendDownloadHeaders('text', 'csv', $filename, true, $filesize);
echo $csvdata;

/**
 * $Log: export.php,v $
 * Revision 1.6  2005/01/13 22:39:30  braverock
 * - modified to use SendDownLoadHeaders
 * - modified to send data directly,rather than writing a file
 * - use timestamp in the filename
 *
 * Revision 1.5  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.4  2004/07/13 21:17:19  braverock
 * - fixed a couple of limitng bugs
 * - add phpdoc to start of file
 *
 * Revision 1.3  2004/06/21 19:50:24  introspectshun
 * - Fixed merge problem caused by incompatible line breaks.
 *
 * Revision 1.1  2004/04/20 12:32:43  braverock
 * - add export function for contacts
 *   - apply SF patch 938388 submitted by frenchman
 *
 * Revision 1.0 2004/04/16 frenchman
 *
 */
?>