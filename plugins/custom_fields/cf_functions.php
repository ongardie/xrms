<?php

$cf_plugin_version = "X1.0";

function connect ($dbconnection=false) {

	# Connect to database, return connection object.
	
	global $xrms_db_server, $xrms_db_username, $xrms_db_password;
	global $xrms_db_dbname, $xrms_db_dbtype, $con;

    static $opened_con=false;

    //hack to check dbconnection status before closing connection
    if ($dbconnection AND $opened_con) {
        $dbconnection->close();
        return true;
    }
	
	if (!$con) {
		$con = get_xrms_dbconnection();
        $opened_con=true;
	}
	//$con->debug = 1;
	return $con;
}

function dump ($sym, $label="") {

	# Display passed symbol
	
	if ($label) {
		echo "<h4>$label</h4>";
	}
	echo "<pre>";
	var_dump($sym);
	echo "</pre>";
}
	
function execute_sql ($sql) {

	# Execute passed SQL, return dataset
	
	$con = connect();
	$rst = $con->execute($sql);
	
	# Exit if SQL failed
	if (!$rst) {
		db_error_handler ($con, $sql);
		exit;
	}
	
	return $rst;
}
