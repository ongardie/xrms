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

$tables_to_extract = array("industries");

$file = file_get_contents($data_filename);
$strings = array();
foreach ($tables_to_extract as $t)
{
	if (preg_match_all("/insert\s+into\s+$t.*values\s+\([^,]*,([^,]*),([^,]*),(.*)\)/i",$file,$matches))
	{
		foreach ($matches[1] as $m)
		{
			$strings[]= $m;
		}
		foreach ($matches[2] as $m)
		{
			$strings[]= $m;
		}
		foreach ($matches[3] as $m)
		{
			$strings[]= $m;
		}
	}
}
$output_strings=array_unique($strings);

$fp = fopen($output_filename,'w') or die("Unable to open output file $output_filename\n");
fwrite($fp, "<?php\n");
fwrite($fp, "//File generated automatically by getdbstrings.php. Do not modify directly.\n");
foreach ($output_strings as $s)
{
	fwrite($fp, '$s=_('.$s.");\n");
}
fwrite($fp, "?>\n");
fclose($fp);
?>

