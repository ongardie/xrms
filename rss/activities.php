<?php
/**
 * @author Beth Macknik
 * 
 * Create an rss feed for activities.  URL options determine which activities are included
 * Options include:
 * max = maximum number of entries.  Overridden by system parameter if necessary
 * user = limit to created by or assigned user
 * created = limit to this created user
 * assigned = limit to this assigned user
 * status = open or scheduled or overdue or closed or current (open or closed).  Default all.
 * type = limit activity type.  Default all.
 *
 * $Id: activities.php,v 1.2 2005/02/10 14:15:25 maulani Exp $
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
$user        = isset($_GET['user']) ? $_GET['user'] : '';
$created     = isset($_GET['created']) ? $_GET['created'] : '';
$assigned    = isset($_GET['assigned']) ? $_GET['assigned'] : '';
$status      = isset($_GET['status']) ? $_GET['status'] : '';
$type        = isset($_GET['type']) ? $_GET['type'] : '';

//Make sure that max has a valid value.
if (!is_numeric($max)) $max = $system_max_entries;
$max = intval($max);
if ( $max <= 0 ) $max = $system_max_entries;
if ( $max > $system_max_entries ) $max = $system_max_entries;

//Make sure that User has a valid value
if ($user != '') {
	$sql = "select user_id, first_names, last_name from users where username='$user'";
	$rst = $con->execute($sql);
	if ($rst) {
    	$num_users = $rst->rowcount();
    	if ($num_users==0) {
    		$user = '';
    	} else {
			$user_id = $rst->fields['user_id'];
			$first_names = $rst->fields['first_names'];
			$last_name = $rst->fields['last_name'];
		}
		$rst->close();
	} else {
		db_error_handler($con, $sql);
	}
}

//Make sure that Created has a valid value
if ($created != '') {
	$sql = "select user_id, first_names, last_name from users where username='$created'";
	$rst = $con->execute($sql);
	if ($rst) {
    	$num_created = $rst->rowcount();
    	if ($num_created==0) {
    		$created = '';
    	} else {
			$created_user_id = $rst->fields['user_id'];
			$created_first_names = $rst->fields['first_names'];
			$created_last_name = $rst->fields['last_name'];
		}
		$rst->close();
	} else {
		db_error_handler($con, $sql);
	}
}

//Make sure that Assigned has a valid value
if ($assigned != '') {
	$sql = "select user_id, first_names, last_name from users where username='$assigned'";
	$rst = $con->execute($sql);
	if ($rst) {
    	$num_assigned = $rst->rowcount();
    	if ($num_assigned==0) {
    		$assigned = '';
    	} else {
			$assigned_user_id = $rst->fields['user_id'];
			$assigned_first_names = $rst->fields['first_names'];
			$assigned_last_name = $rst->fields['last_name'];
		}
		$rst->close();
	} else {
		db_error_handler($con, $sql);
	}
}

//Make sure that Status has a valid value
switch ($status) {
case "":
	$status = 'all';
	break;
case "open":
	$status = 'open';
	break;
case "scheduled":
	$status = 'scheduled';
	break;
case "overdue":
	$status = 'overdue';
	break;
case "closed":
	$status = 'closed';
	break;
case "current":
	$status = 'current';
	break;
default:
	$status = 'all';
	break;
}

//Make sure that Type has a valid value
if ($type != '') {
	$sql = "select activity_type_id from activity_types where activity_type_pretty_name='$type'";
	$rst = $con->execute($sql);
	if ($rst) {
    	$num_type = $rst->rowcount();
    	if ($num_type==0) {
    		$type = 'all';
    	} else {
			$activity_type_id = $rst->fields['activity_type_id'];
		}
		$rst->close();
	} else {
		db_error_handler($con, $sql);
	}
} else {
	$type='all';
}

$sql = "SELECT a.activity_id, a.activity_title, a.activity_description, a.activity_status, 
a.ends_at, c.company_name 
FROM activities a, companies c
WHERE a.company_id=c.company_id AND a.activity_record_status = 'a' ";

if ($user != '') {
	$sql .= "AND (a.user_id = $user_id OR a.entered_by = $user_id) ";
}

if ($created != '') {
	$sql .= "AND a.entered_by = $created_user_id ";
}

if ($assigned != '') {
	$sql .= "AND a.user_id = $assigned_user_id ";
}

switch ($status) {
case "open":
	$sql .= "AND a.activity_status = 'o' and a.scheduled_at <= NOW() ";
	break;
case "scheduled":
	$sql .= "AND a.activity_status = 'o' and a.scheduled_at > NOW() ";
	break;
case "overdue":
	$sql .= "AND a.activity_status = 'o' and a.ends_at < NOW() ";
	break;
case "closed":
	$sql .= "AND a.activity_status = 'c' ";
	break;
case "current":
	$sql .= "AND a.scheduled_at <= NOW() ";
	break;
}

if ($type != 'all') {
	$sql .= "AND a.activity_type_id = $activity_type_id ";
}

$sql .= 'ORDER BY a.scheduled_at DESC';

$items_text = '';
$rst = $con->selectlimit($sql, $max);
if ($rst) {
	$num_activities = $rst->rowcount();
	while (!$rst->EOF) {
		$activity_id = $rst->fields['activity_id'];
		$activity_title = str_replace("&", "&amp;", htmlentities($rst->fields['activity_title'], ENT_COMPAT, 'UTF-8'));
		$activity_description = str_replace("&", "&amp;", htmlentities($rst->fields['activity_description'], ENT_COMPAT, 'UTF-8'));
		$activity_status = $rst->fields['activity_status'];
		$ends_at = $rst->fields['ends_at'];
		//$last_modified_at = $rst->fields['last_modified_at'];
		$pub_date = date("r", strtotime($ends_at));
		$company_name = $rst->fields['company_name'];
		$description = "&lt;p&gt;&lt;b&gt;$company_name&lt;/b&gt;&lt;/p&gt;" . $activity_description;
		$items_text .= "      <item>\n";
		$items_text .= '         <title>' . $activity_title . '</title>' . "\n";
		$items_text .= '         <link>' . $http_site_root . '/activities/one.php?activity_id=' .$activity_id . '</link>' . "\n";
		$items_text .= '         <description>' . $description . '</description>' . "\n";
		$items_text .= '         <pubDate>' . $pub_date . '</pubDate>' . "\n";
		$items_text .= '         <guid isPermaLink="true">' . $http_site_root . '/activities/one.php?activity_id=' .$activity_id . '</guid>' . "\n";
		$items_text .= "      </item>\n\n";
		$rst->movenext();
	}
	$rst->close();
} else {
	db_error_handler($con, $sql);
}
$con->close();

$feed_location = $http_site_root . '/rss/activities.php';
$now = date("r");

//show_test_values ($num_activities,$activity_id,$activity_title,$activity_description,$activity_status);
header("Content-Type: text/xml");

echo '<?xml version="1.0" encoding="utf-8"?>' . "\n\n";
?>
<rss version="2.0">
   <channel>
      <title>XRMS Activities</title>
      <link><?php echo $feed_location; ?></link>
      <description>A list of activities from XRMS</description>
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
 * $Log: activities.php,v $
 * Revision 1.2  2005/02/10 14:15:25  maulani
 * - Change SQL to use SelectLimit
 *
 * Revision 1.1  2005/02/05 23:11:30  maulani
 * - Provide RSS Feed for activities
 *
 *
 */
?>
