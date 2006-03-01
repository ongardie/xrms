<?php
/**
 * Export pager contents
 *
 * $Id: pager-export.php,v 1.7 2006/03/01 03:07:16 vanmer Exp $
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

$con = get_xrms_dbconnection();
// $con->debug = 1;

$session_user_id = session_check();


getGlobalVar($pager_id, 'pager_id');
getGlobalVar($custom_header, 'custom_header');
getGlobalVar($custom_footer, 'custom_footer');
getGlobalVar($hide_field_headers, 'hide_field_headers');

$filename =  $pager_id .'-'. date('Y-m-d_H-i') . '.csv';

//echo "pager_id is $pager_id";

$session_data = $_SESSION[$pager_id . "_data"];
$column_info = $_SESSION[$pager_id . "_columns"];

if(is_array($session_data) && is_array($column_info)) {
	// first output the column names
	$csvdata = '';

    //include custom headers if provided
    if ($custom_header) $csvdata.=$custom_header."\n";

    //by default, include field headers, unless hide field headers is explicitly set
    if (!$hide_field_headers) {
        foreach($column_info as $column) {
            $csvdata .= $column['name'] . ',';
        }
        $csvdata = substr($csvdata, 0, -1);
        $csvdata .= "\n";
    }	
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

    if ($custom_footer) { $csvdata.=$custom_footer."\n"; }

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
 * Revision 1.7  2006/03/01 03:07:16  vanmer
 * - added extra parameters to control export file layout
 * - added flag to control display of header row with fieldnames
 * - added custom header/footer output, if provided
 *
 * Revision 1.6  2006/01/02 23:04:33  vanmer
 * - changed to use centralized dbconnection function
 *
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
