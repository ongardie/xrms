<?php
//example for CLIENT APPLICATION which remotely queries whether an address exists in the XRMS database
error_reporting(1); // error reporting set to fatal only
define("XMLRPC_DEBUG", 1);
include_once "xmlrpc.inc";

$site='127.0.0.1';
$location='/xrms/plugins/xmlrpc_db_sync/server.php';
$DBUSERNAME="root";
$DBPASSWORD="";

/*returns the address id from XRMS - used in xrms_add_update_account_as_company
send it an array of address_data in the form
$address_data['line1']="12 smith street";
$address_data['city']="Newtown";
etc..
Keys (ie 'city') must conform to the XRMS database schema.
*/
function xrms_find_address($address_data){
			//the info needed to make the XMLRPC connection
			global $site,$location;
			//Prepare the data for transmission
			$data=array(XMLRPC_prepare($address_data));
			//XMLRPC returns a 2 dimensional array with the success plus data back - split it into two variables
			list($xrms_find_address_success,$xrms_find_address_response)=XMLRPC_request($site, $location, 'xrms_find_address', $data);
			//return the address ID from XRMS.
			return $xrms_find_address_response;

}

function xrms_HelloWorld($dummy){
			//the info needed to make the XMLRPC connection
			global $site,$location;
			//Prepare the data for transmission
			$data=array(XMLRPC_prepare($address_data));
			//XMLRPC returns a 2 dimensional array with the success plus data back - split it into two variables
			list($xrms_find_address_success,$xrms_find_address_response)=XMLRPC_request($site, $location, 'HelloWorld', $data);
			//return the address ID from XRMS.
			return $xrms_find_address_response;

}

$response= xrms_HelloWorld(1);
if( $response=="Hello World") {echo $response;} else {echo "<pre>Error with XMLRPC server";};

?>