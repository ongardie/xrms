<?php
/**
 *
 * Create an rss feed for cases. URL options determine which activities are included
 * Options include:
 * status = open or closed or all. Default all.
 * max = maximum number of entries.  Overridden by system parameter if necessary
 *
 * Initially written for activivities by Beth Macknik
 * Rewritten for cases by Mattias Forsberg
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

$rss_ok = get_system_parameter($con, 'RSS Feeds Enabled');
if ($rss_ok != 'y') exit;
$system_max_entries = get_system_parameter($con, 'Maximum RSS Feed Entries');


$max         = isset($_GET['max']) ? $_GET['max'] : $system_max_entries;
$status      = isset($_GET['status']) ? $_GET['status'] : '';

//Make sure that max has a valid value.
if (!is_numeric($max)) $max = $system_max_entries;
$max = intval($max);
if ( $max <= 0 ) $max = $system_max_entries;
if ( $max > $system_max_entries ) $max = $system_max_entries;


//Make sure that Status has a valid value
switch ($status) {
case "":
	$status = 'all';
	$feed_name = 'XRMS Cases';
	break;
case "open":
	$status = 'open';
	$feed_name = 'XRMS Open Cases';
	break;
case "closed":
	$status = 'closed';
	$feed_name = 'XRMS Closed Cases';
	break;
default:
	$status = 'all';
	$feed_name = 'XRMS Cases';
	break;
}


$sql = "SELECT a.case_id, a.case_status_id, a.company_id, a.user_id, a.case_title,
a.case_description, a.case_record_status, a.last_modified_at,
c.company_name, os.case_status_pretty_name, os.case_status_long_desc
FROM cases a, companies c, case_statuses os
WHERE a.company_id = c.company_id
AND a.case_status_id  = os.case_status_id ";

switch ($status) {
case "open":
	$sql .= "AND os.status_open_indicator = 'o' ";
	break;
case "closed":
	$sql .= "AND os.status_open_indicator = 'c' ";
	break;
 }

$sql .= "ORDER BY a.last_modified_at DESC";

$items_text = '';
$rst = $con->selectlimit($sql, $max);
if ($rst) {
	$num_activities = $rst->rowcount();
	while (!$rst->EOF) {
      $case_id = $rst->fields['case_id'];
      $case_title = str_replace("&", "&amp;", htmlentities($rst->fields['case_title'], ENT_COMPAT, 'ISO-8859-1'));
      $case_description = str_replace("&", "&amp;", htmlentities($rst->fields['case_description'], ENT_COMPAT, 'ISO-8859-1'));
      $case_status = str_replace("&", "&amp;", htmlentities($rst->fields['case_status_pretty_name'], ENT_COMPAT, 'ISO-8859-1'));
      $case_status_description = str_replace("&", "&amp;", htmlentities($rst->fields['case_status_long_desc'], ENT_COMPAT, 'ISO-8859-1'));
      $case_modified_date = $rst->fields['last_modified_at'];
      $pub_date = date("r", strtotime($case_modified_date));
      $company_name = $rst->fields['company_name'];
      $title = $case_title . " (" . $company_name . ")";
      $description = "&lt;p&gt;&lt;b&gt;Status: $case_status&lt;/b&gt;&lt;br/&gt;&lt;i&gt;$case_status_description&lt;/i&gt;&lt;/p&gt;&lt;p&gt;" . $case_description . "&lt;/p&gt;";

        $items_text .= "      <item>\n";
		$items_text .= '         <title>' . $title . '</title>' . "\n";
		$items_text .= '         <link>' . $http_site_root . '/cases/one.php?case_id=' .$case_id . '</link>' . "\n";
		$items_text .= '         <description>' . $description . '</description>' . "\n";
		$items_text .= '         <pubDate>' . $pub_date . '</pubDate>' . "\n";
		$items_text .= '         <guid isPermaLink="true">' . $http_site_root . '/cases/one.php?case_id=' .$activity_id . '</guid>' . "\n";
		$items_text .= "      </item>\n\n";
		$rst->movenext();
	}
	$rst->close();
} else {
	db_error_handler($con, $sql);
}
$con->close();

$feed_location = $http_site_root . '/rss/cases.php';
$now = date("r");

header("Content-Type: text/xml");

echo '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n\n";
?>
<rss version="2.0">
   <channel>
      <title><?php echo $feed_name; ?></title>
      <link><?php echo $feed_location; ?></link>
      <description>A list of cases from XRMS</description>
      <language>en-us</language>
      <lastBuildDate><?php echo $now; ?></lastBuildDate>
      <docs>http://blogs.law.harvard.edu/tech/rss</docs>
      <generator>XRMS</generator>
      <ttl>30</ttl>

 <?php echo $items_text; ?>

   </channel>
</rss>
<?php

/**
 * $Log: cases.php,v $
 * Revision 1.2  2006/04/06 13:48:16  maulani
 * - Add phpdoc
 *
 * Revision 1.1  2006/04/05 23:11:30  vanmer
 * - added RSS feeds for cases and opportunities
 *
 */
?>