<?php
/**
 * Print a single Activity
 *
 * @author Neil Roberts
 *
 * @param int activity_id
 *
 */

//include required files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

if($_GET['activity_id']) {
  $activity_id = $_GET['activity_id'];
}
else {
    die("No activity ID");
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

$sql = "SELECT c.address_id, c.first_names, c.last_name, c.work_phone 
        FROM users u, contacts c
        WHERE u.user_id='$session_user_id'
        AND u.user_contact_id=c.contact_id";

$rst = $con->execute($sql);
if(!$rst) {
    db_error_handler($con, $sql);
}
elseif($rst->EOF) {
    die("This function requires a contact to be tied to the user");
}

$sql = "SELECT a.activity_description, c.first_names, c.last_name, o.company_name, c.address_id
        FROM activities a, contacts c, companies o
        WHERE a.activity_id='$activity_id'
        AND a.contact_id=c.contact_id
        AND c.company_id=o.company_id";

$rst2 = $con->execute($sql);
if(!$rst) {
    db_error_handler($con, $sql);
}
elseif(!$rst->EOF) {
    print "<font style=\"font-size: 10pt\">\n";
    print "<p align=right>" . date("F j, Y") . "</p>";
    print $rst2->fields['first_names'] . " " . $rst2->fields['last_name'] . "<br>\n";
    print $rst2->fields['company_name'] . "<br>\n";
    $address = get_formatted_address($con, $rst2->fields['address_id']);
    $address = split("<br>", $address);
    array_pop($address);
    $address = join("<br>", $address);
    print $address . "<br><br>\n";
    print "Dear " . $rst2->fields['first_names'] . ",<br><br>\n";
    print ereg_replace("\n", "<br>\n", $rst2->fields['activity_description']) . "<br><br>\n";
    print "Sincerely,<br><br><br>\n";
    if($rst->rowcount()) {
        print "<br><br>" . $rst->fields['first_names'] . " " . $rst->fields['last_name'] . "<br>";
        print get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['work_phone']);
    }
    print "</font>\n";
}

$rst->close();
$rst2->close();

$con->close();

?>
