<?php

// Some class you've written...
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

// Including this sets up the JPSPAN constant
require_once($include_directory . '../js/jpspan/JPSpan.php');

// Load the PostOffice server
require_once(JPSPAN . 'Server/PostOffice.php');

$session_user_id = session_check();

$con = get_xrms_dbconnection();

class AsteriskTools{
	function AsteriskTools(){

	}

	function anyCalls(){
		global $con, $session_user_id;
		$sql = "SELECT * FROM users WHERE user_id = $session_user_id";
		$rst = $con->execute($sql);

		$sql = "SELECT * FROM cti_call_queue
                        WHERE extension = ".$rst->fields['extension']."
                        AND start_ts > (UNIX_TIMESTAMP() - 10)
                        AND ack = 0";
		$rst = $con->execute($sql);
		if($rst->_numOfRows > 0){
			return $rst->fields['id'];
		}else return false;

	}

	function ackCall($id){
		global $con, $session_user_id;

		$sql = "UPDATE cti_call_queue SET ack = 1 WHERE id = ".$id;
		$rst = $con->execute($sql);
	}

	function getCallerInfo($call_id){
		global $con, $session_user_id;
		$sql = "SELECT * FROM cti_call_queue
                        WHERE id = '" . $call_id . "' ";
		$rst = $con->execute($sql);

		$data = array();
		if (($rst) && (!$rst->EOF)) {
				$data['call_id'] = $rst->fields['id'];
				$data['callerid'] = $rst->fields['callerid'];
		}
		return $data;
	}
}
// Create the PostOffice server
$S = & new JPSpan_Server_PostOffice();

// Register your class with it...
$handle_desc = new JPSpan_HandleDescription();
$handle_desc->Class = 'AsteriskTools';
$handle_desc->methods = array('anyCalls', 'ackCall', 'getCallerInfo');
$S->addHandler(new AsteriskTools(), $handle_desc);

// This allows the JavaScript to be seen by
// just adding ?client to the end of the
// server's URL

if (isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'], 'client')==0) {

		// Compress the output Javascript (e.g. strip whitespace)
		//define('JPSPAN_INCLUDE_COMPRESS',TRUE);

		// Display the Javascript client
		$S->displayClient();

} else {

		// This is where the real serving happens...
		// Include error handler
		// PHP errors, warnings and notices serialized to JS
		require_once JPSPAN . 'ErrorHandler.php';

		// Start serving requests...
		$S->serve();

}
?>
