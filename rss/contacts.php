<?php
/**
 * @author Beth Macknik
 * 
 * Create an rss feed for contacts.  URL options determine which contacts are included
 * Options include:
 * max = maximum number of entries.  Overridden by system parameter if necessary
 * status = new or modified.  Default new.
 *
 * $Id: contacts.php,v 1.1 2005/03/07 11:52:48 maulani Exp $
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
	break;
case "new":
	$status = 'new';
	break;
case "modified":
	$status = 'modified';
	break;
default:
	$status = 'new';
	break;
}

$sql = "SELECT c.contact_id, b.company_name, CONCAT(c.first_names, ' ', c.last_name) as contact_name
        FROM contacts c, companies b
        WHERE c.contact_record_status = 'a' and c.company_id=b.company_id ";

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
	$num_contacts = $rst->rowcount();
	while (!$rst->EOF) {
		$contact_id = $rst->fields['contact_id'];
		$contact_name = str_replace("&", "&amp;", htmlentities($rst->fields['contact_name'], ENT_COMPAT, 'UTF-8'));
		$company_name = str_replace("&", "&amp;", htmlentities($rst->fields['company_name'], ENT_COMPAT, 'UTF-8'));
		$entered_at = $rst->fields['entered_at'];
		$last_modified_at = $rst->fields['last_modified_at'];
		$pub_date = date("r", strtotime($ends_at));
		$items_text .= "      <item>\n";
		$items_text .= '         <title>' . $contact_name . '</title>' . "\n";
		$items_text .= '         <link>' . $http_site_root . '/contacts/one.php?contact_id=' .$contact_id . '</link>' . "\n";
		$items_text .= '         <description>' . $company_name . '</description>' . "\n";
		$items_text .= '         <pubDate>' . $pub_date . '</pubDate>' . "\n";
		$items_text .= '         <guid isPermaLink="true">' . $http_site_root . '/contacts/one.php?contact_id=' .$contact_id . '</guid>' . "\n";
		$items_text .= "      </item>\n\n";
		$rst->movenext();
	}
	$rst->close();
} else {
	db_error_handler($con, $sql);
}
$con->close();

$feed_location = $http_site_root . '/rss/contacts.php';
$now = date("r");

header("Content-Type: text/xml");

echo '<?xml version="1.0" encoding="utf-8"?>' . "\n\n";
?>
<rss version="2.0">
   <channel>
      <title>XRMS Contacts</title>
      <link><?php echo $feed_location; ?></link>
      <description>A list of contacts from XRMS</description>
      <language>en-us</language>
      <lastBuildDate>$now</lastBuildDate>
      <docs>http://blogs.law.harvard.edu/tech/rss</docs>
      <generator>XRMS</generator>
      <ttl>30</ttl>

 <?php echo $items_text; ?>

   </channel>
</rss>
<?php

/**
 * $Log: contacts.php,v $
 * Revision 1.1  2005/03/07 11:52:48  maulani
 * - Add basic RSS feed for companies and contacts
 *
 *
 */
?>
