<?php
/**
 * Export pager contents
 *
 * $Id: pager-export.php,v 1.5 2005/12/12 17:54:05 daturaarutad Exp $
 */

if(!$include_directory) {
    require_once('../../../include-locations.inc');
}

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
	// first output the column names
	$csvdata = '';
	foreach($column_info as $column) {
		$csvdata .= $column['name'] . ',';
	}
	$csvdata = substr($csvdata, 0, -1);
	$csvdata .= "\n";
	
	// now output the data
	foreach($session_data as $row) {

		foreach($column_info as $column) {
			// do some formatting of the data before moving to csvdata
			if('url' == $column['type']) {
				// extract <a...>(good stuff)</a>
				if(preg_match("/<a[^>]*>(.*)<\/a>/", $row[$column['index']], $matches))
				{
				    $row[$column['index']] = $matches[1];
				}
			}
			if('html' == $column['type']) {
				// extract all html
				$row[$column['index']] = preg_replace("/(<\/?)(\w+)([^>]*>)/e", '', $row[$column['index']]);
			}

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
 * Revision 1.5  2005/12/12 17:54:05  daturaarutad
 * only bring in include-locations.inc if $include_directory is undefined
 *
 * Revision 1.4  2005/04/29 16:22:26  daturaarutad
 * added html type which strips all html from column
 *
 * Revision 1.3  2005/04/29 15:15:39  daturaarutad
 * remove anchors for urls
 *
 * Revision 1.2  2005/04/15 17:41:51  daturaarutad
 * add quotes around values that contain commas...temporary fix until we implement a filter/hook
 *
 * Revision 1.1  2005/04/13 06:27:06  daturaarutad
 * new file
 *
 */
?>
