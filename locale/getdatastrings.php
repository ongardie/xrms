<?php
/**
 * Get strings from data.php which should be localized
 * Run as php getdatastrings.php from the command line
 */
require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');

$data_filename = $xrms_file_root.'/install/data.php';
$output_filename = $xrms_file_root.'/locale/datastrings.php';

// Add more table names to $tables_to_extract if they are loaded by data.php
// and translation is required.

$tables_to_extract = array("account_statuses",
                           "activity_types",
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
                           );

$file = file_get_contents($data_filename);
$strings = array();
foreach ($tables_to_extract as $t)
{
// extract values from the insert into statements 
    if (preg_match_all("/insert\s+into\s+$t.*values\s+\((.*)\)\s*(?:\"|\')\s*;/Ui",$file,$matches))
    {
        foreach ($matches[1] as $m)
        {
// get the single values
			$matched_val = split(',',$m);
			foreach ($matched_val as $mv)
			{
				$mv = trim($mv);
// if the strings is not numeric, it is longer than 1 (3 including the quotes) or it isn't a short
// code which at the moment I have assumed to be uppercase strings of length <=5 (<=7 including quotes)
				if (!is_numeric($mv) && strlen($mv) > 3 && !(strtoupper($mv)==$mv && strlen($mv) <= 7))
				{
					$strings[]= tag_remove($mv);
				}
			}
			
        }
    }
}
$output_strings=array_unique($strings);
sort($output_strings);
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
 * $Id: getdatastrings.php,v 1.3 2004/08/19 19:55:46 johnfawcett Exp $
 */'."\n");
foreach ($output_strings as $s)
{
    $s=trim($s);
    fwrite($fp, '$s=_('.$s.");\n");
}
fwrite($fp, "?>\n");
fclose($fp);

/* removes the html <x> tags from strings $s */

function tag_remove($s)
{
	return preg_replace('/<[^>]*>/U',"",$s);
}

?>

