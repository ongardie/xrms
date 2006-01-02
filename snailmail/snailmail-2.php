<?php
  /**
  *
  * Email.
  *
  * $Id: snailmail-2.php,v 1.3 2006/01/02 23:47:49 vanmer Exp $
  */

  require_once('include-locations-location.inc');

  require_once($include_directory . 'vars.php');
  require_once($include_directory . 'utils-interface.php');
  require_once($include_directory . 'utils-misc.php');
  require_once($include_directory . 'adodb/adodb.inc.php');
  require_once($include_directory . 'adodb-params.php');
  //added to allow export to csv
  include_once($include_directory . 'adodb/toexport.inc.php');

  $session_user_id = session_check();
  $msg = $_GET['msg'];

  $scope = $_GET['scope'];

  //echo $scope;exit;
  $company_id = $_GET['company_id'];

  // opportunities
  $user_id = $_POST['user_id'];
  $contact_id = $_POST['contact_id'];

  //activities
  $activity_id = $_POST['activity_id'];


  $array_of_contacts = $_POST['array_of_contacts'];

if (is_array($array_of_contacts))
    $imploded_contacts = implode(',', $array_of_contacts);
else
    echo _("WARNING: No array of contacts!") . "<br>";


$con = get_xrms_dbconnection();
//$con->debug = 1;

$sql = "select cont.contact_id, cont.salutation , cont.first_names, cont.last_name, " .
$con->concat("cont.first_names", "' '","cont.last_name") . 
" as contact_name, cont.summary , cont.title, cont.description, c.company_name, addr.address_body, addr.line1, addr.line2,addr.city,addr.province,addr.postal_code,u.username as owner
from contacts cont, companies c, users u, addresses addr
where c.company_id = cont.company_id
and c.user_id = u.user_id
and cont.contact_id in ($imploded_contacts)
and cont.address_id=addr.address_id
and contact_record_status = 'a' ";

$rst = $con->execute($sql);

$filename =  "SnailMailMergeExport" .'-'. date('Y-m-d_H-i') . '.csv';
$filesize = strlen($csvdata);
SendDownloadHeaders('text', 'csv', $filename, true, $filesize);
print rs2csv($rst);

?>

