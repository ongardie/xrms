<?php
/**
 * Get strings from data.php which should be localized
 * Run as php getdatastrings.php from the command line
 */
require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-misc.php');
$con=get_xrms_dbconnection();

$data_filenames= array($xrms_file_root.'/install/data.php', $xrms_file_root.'/include/classes/acl/acl_install.php');
$output_filename = $xrms_file_root.'/locale/datastrings.php';

// Add more table names to $tables_to_extract if they contain strings
// and translation is required.

$tables_to_extract = array("account_statuses",
                           "activity_types",
			   "activity_resolution_types",
                           "campaign_types",
                           "case_priorities",
                           "case_statuses",
                           "case_types",
                           "crm_statuses",
                           "company_types",
                           "system_parameters",
                           "company_sources",
                           "opportunity_statuses",
                           "industries",
                           "ratings",
                           "relationship_types",
                           "system_parameters",
                           "ControlledObject",
                           "Permission",
                           "Role",
                           );
    $strings = array();
    //loop on each table
    foreach ($tables_to_extract as $t) {
    	//get column info for table
	$col_info=$con->MetaColumns($t);
	$str_cols=array();
		//loop on columns
		foreach ($col_info as $col) {
			switch ($col->type) {
				//only take varchar fields
				case 'varchar':
					//ensure that field is longer than 10 characters (short names)
					if ($col->max_length > 10) {
						$str_cols[]=$col->name;
					}
				break;
			}
		}
	//make sure we found any columns in this table
	if (count($str_cols)>0) {
		$col_str=implode(", ", $str_cols);
		//select only the varchar fields from the table
		$sql= "SELECT $col_str FROM $t";
		$rst = $con->execute($sql);
		if (!$rst) db_error_handler($con, $sql);
		while (!$rst->EOF) {
			//loop on fields
			foreach ($rst->fields as $fkey=>$mv) {
				//trim spaces and tags
				$mv=trim(tag_remove($mv));
				//make sure field has more than one character
				if ($mv AND (strlen($mv)>1)) {
					//if we pass the above test, add to the list of strings
					$strings[]=$mv;
				}
			}
			//next character
			$rst->movenext();
		}
		$rst->close();
	}
    }

//ensure that strings are unique
$output_strings=array_unique($strings);
//and sorted
sort($output_strings);

//write them to our file
$fp = fopen($output_filename,'w') or die("Unable to open output file $output_filename\n");
fwrite($fp, "<?php\n");
fwrite($fp, '/**
 * File generated automatically by getdatastrings.php. Do not modify directly.
 *
 * This file is included in XRMS CVS as a convenience to translators.
 * generate a new version of this file by running
 * php ./getdatastrings.php
 * from the locale directory
 *
 * $Id: getdatastrings.php,v 1.7 2005/11/30 00:36:02 vanmer Exp $
 */'."\n");
foreach ($output_strings as $s)
{
    $s=trim($s, "' ");
    fwrite($fp, '$s=_("'.$s."\");\n");
}
fwrite($fp, "?>\n");
fclose($fp);

/* removes the html <x> tags from strings $s */
if (!function_exists('tag_remove')) {
function tag_remove($s)
{
	return preg_replace('/<[^>]*>/U',"",$s);
}
}
?>