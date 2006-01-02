<?php
/**
 * @author Beth Macknik
 * 
 * Create an rss feed for companies.  URL options determine which companies are included
 * Options include:
 * max = maximum number of entries.  Overridden by system parameter if necessary
 * status = new or modified.  Default new.
 *
 * $Id: companies.php,v 1.5 2006/01/02 23:46:52 vanmer Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

$con = get_xrms_dbconnection();
//$con->debug = 1;

$rss_ok = get_system_parameter($con, 'RSS Feeds Enabled');
if ($rss_ok != 'y') exit;
$system_max_entries = get_system_parameter($con, 'Maximum RSS Feed Entries');


$max         = isset($_GET['max']) ? $_GET['max'] : $system_max_entries;
$status      = isset($_GET['status']) ? $_GET['status'] : 'new';

//Make sure that max has a valid value.
if (!is_numeric($max)) $max = $system_max_entries;
$max = intval($max);
if ( $max <= 0 ) $max = $system_max_entries;
if ( $max > $system_max_entries ) $max = $system_max_entries;

//Make sure that Status has a valid value
switch ($status) {
case "":
	$status = 'new';
	$feed_name = 'XRMS New Companies';
	break;
case "new":
	$status = 'new';
	$feed_name = 'XRMS New Companies';
	break;
case "modified":
	$status = 'modified';
	$feed_name = 'XRMS Modified Companies';
	break;
default:
	$status = 'new';
	$feed_name = 'XRMS New Companies';
	break;
}

$sql = "SELECT c.company_id, c.company_name, c.entered_at, 
        c.last_modified_at, CONCAT(u.email, ' (', u.first_names, ' ', u.last_name, ')') as author
        FROM companies c, users u
        WHERE c.company_record_status = 'a' 
        AND c.entered_by = u.user_id ";

switch ($status) {
case "new":
	$sql .= "ORDER BY c.entered_at DESC";
	break;
case "modified":
	$sql .= "ORDER BY c.last_modified_at DESC";
	break;
}


$items_text = '';
$rst = $con->selectlimit($sql, $max);
if ($rst) {
	$num_companies = $rst->rowcount();
	while (!$rst->EOF) {
		$company_id = $rst->fields['company_id'];
		$company_name = str_replace("&", "&amp;", htmlentities($rst->fields['company_name'], ENT_COMPAT, 'UTF-8'));
		$author = $rst->fields['author'];
		$entered_at = $rst->fields['entered_at'];
		$last_modified_at = $rst->fields['last_modified_at'];		
		$last_modified_f = date("r", strtotime($last_modified_at));
		if ($status == 'modified') {
			$pub_date = date("r", strtotime($last_modified_at));
		} else {
			$pub_date = date("r", strtotime($entered_at));
		}
		$items_text .= "      <item>\n";
		$items_text .= '         <title>' . $company_name . '</title>' . "\n";
		$items_text .= '         <link>' . $http_site_root . '/companies/one.php?company_id=' .$company_id . '</link>' . "\n";
		$items_text .= '         <description>' . $company_name . '</description>' . "\n";
		$items_text .= '         <author>' . $author . '</author>' . "\n";
		$items_text .= '         <pubDate>' . $pub_date . '</pubDate>' . "\n";
		$items_text .= '         <guid isPermaLink="false">' . $http_site_root . '/companies/one.php?company_id=' .$company_id . '?mod=' . $last_modified_f . '</guid>' . "\n";
		$items_text .= "      </item>\n\n";
		$rst->movenext();
	}
	$rst->close();
} else {
	db_error_handler($con, $sql);
}
$con->close();

$feed_location = $http_site_root . '/rss/companies.php';
$now = date("r");

//this needs to be changed to reflect the last actual change in the feed
$last_date = date("r");

header("Content-Type: text/xml");

echo '<?xml version="1.0" encoding="utf-8"?>' . "\n\n";
?>
<rss version="2.0">
   <channel>
      <title><?php echo $feed_name; ?></title>
      <link><?php echo $feed_location; ?></link>
      <description>A list of companies from XRMS</description>
      <language>en-US</language>
      <pubDate>$now</pubDate>
      <lastBuildDate>$last_date</lastBuildDate>
      <docs>http://blogs.law.harvard.edu/tech/rss</docs>
      <generator>XRMS</generator>
      <ttl>30</ttl>

 <?php echo $items_text; ?>

   </channel>
</rss>
<?php

/**
 * $Log: companies.php,v $
 * Revision 1.5  2006/01/02 23:46:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.4  2005/06/29 16:07:10  maulani
 * - Add author tag to feeds
 *
 * Revision 1.3  2005/05/09 13:06:15  maulani
 * - Correct SQL to use correct publication date
 *
 * Revision 1.2  2005/03/20 14:46:46  maulani
 * - Have RSS feed title relect options selected by user
 *
 * Revision 1.1  2005/03/07 11:52:48  maulani
 * - Add basic RSS feed for companies and contacts
 *
 *
 */
?>
