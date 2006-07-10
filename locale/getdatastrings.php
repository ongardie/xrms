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
$strings = array();

// Add more table names to $tables_to_extract if they contain strings
// and translation is required.

$tables_to_extract = array();
$tables_to_extract[] = 'account_statuses';
$tables_to_extract[] = 'activity_types';
$tables_to_extract[] = 'activity_resolution_types';
$tables_to_extract[] = 'campaign_types';
$tables_to_extract[] = 'campaign_statuses';
$tables_to_extract[] = 'case_priorities';
$tables_to_extract[] = 'case_statuses';
$tables_to_extract[] = 'case_types';
$tables_to_extract[] = 'crm_statuses';
$tables_to_extract[] = 'company_types';
$tables_to_extract[] = 'company_sources';
$tables_to_extract[] = 'industries';
$tables_to_extract[] = 'opportunity_types';
$tables_to_extract[] = 'opportunity_statuses';
$tables_to_extract[] = 'ratings';
$tables_to_extract[] = 'relationship_types';
$tables_to_extract[] = 'user_preference_types';
$tables_to_extract[] = 'user_preference_type_options';
$tables_to_extract[] = 'ControlledObject';
$tables_to_extract[] = 'Permission';
$tables_to_extract[] = 'Role';

    //loop on each table
    foreach ($tables_to_extract as $t) {
	echo "Extracting localized strings from Table: $t \n";
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
 * $Id: getdatastrings.php,v 1.8 2006/07/10 14:40:53 braverock Exp $
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
