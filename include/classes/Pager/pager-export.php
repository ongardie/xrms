<?php
/**
 * Export pager contents
 *
 * $Id: pager-export.php,v 1.2 2005/04/15 17:41:51 daturaarutad Exp $
 */

require_once('../../../include-locations.inc');

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


getGlobalVar($pager_id, 'pager_id');

$filename =  $pager_id .'-'. date('Y-m-d_H-i') . '.csv';

//echo "pager_id is $pager_id";

$session_data = $_SESSION[$pager_id . "_data"];
$column_info = $_SESSION[$pager_id . "_columns"];

if(is_array($session_data) && is_array($column_info)) {
	$csvdata = '';
	foreach($column_info as $column) {
		$csvdata .= $column['name'] . ',';
	}
	$csvdata = substr($csvdata, 0, -1);
	$csvdata .= "\n";
	
	foreach($session_data as $row) {
		foreach($column_info as $column) {
			if(false !== strpos($row[$column['index']], ',')) {
				$row[$column['index']] = '"' . $row[$column['index']] . '"';
			}
			$csvdata .= $row[$column['index']] . ',';
		}
		$csvdata = substr($csvdata, 0, -1);
		$csvdata .= "\n";
	}

	$filesize = strlen($csvdata);
	SendDownloadHeaders('text', 'csv', $filename, true, $filesize);
	echo $csvdata;

} else {
    echo "<p>" . _("There was a problem with your export") . ":\n";

	if(!is_array($session_data))
    	echo "<br>" . _("There is no data to export!") . "\n";

	if(!is_array($column_info)) 
    	echo "<br>" . _("There is no column_info!") . "\n";
}


/**
 * $Log: pager-export.php,v $
 * Revision 1.2  2005/04/15 17:41:51  daturaarutad
 * add quotes around values that contain commas...temporary fix until we implement a filter/hook
 *
 * Revision 1.1  2005/04/13 06:27:06  daturaarutad
 * new file
 *
 */
?>
