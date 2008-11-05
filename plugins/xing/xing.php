<?php
/*
*
* xing plugin
* by Stefan Pampel <stefan.pampel@polyformal.de> 
* polyformal ( http://www.polyformal.de/ )
* (c) 2006 (GNU GPL - see ../../COPYING)
* 
* This plugin allows to query a contact by first_names+lastname
* against the 'business-platform' xing (former known as openBC) for entries.
* Based on vcard plugin
*/
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$contact_id = $_GET['contact_id'];

$con = get_xrms_dbconnection();
// $con->debug = 1;


$sql = "select first_names, last_name from contacts where contact_id='" . $contact_id . "'";

$rst = $con->execute($sql);

    if ($rst) {
        if (!$rst->EOF) {
            $fullname = $rst->fields['first_names']."+".$rst->fields['last_name'];
            
        }
            $rst->close();
    }

header("Location: https://www.xing.com/app/search?op=universal&universal=$fullname");
exit;

?>
