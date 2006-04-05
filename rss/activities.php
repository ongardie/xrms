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
 * $Id: activities.php,v 1.8 2006/04/05 01:21:51 vanmer Exp $
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
	$feed_name = 'XRMS Activities';
	break;
case "open":
	$status = 'open';
	$feed_name = 'XRMS Open Activities';
	break;
case "scheduled":
	$status = 'scheduled';
	$feed_name = 'XRMS Scheduled Activities';
	break;
case "overdue":
	$status = 'overdue';
	$feed_name = 'XRMS Overdue Activities';
	break;
case "closed":
	$status = 'closed';
	$feed_name = 'XRMS Closed Activities';
	break;
case "current":
	$status = 'current';
	$feed_name = 'XRMS Current Activities';
	break;
default:
	$status = 'all';
	$feed_name = 'XRMS Activities';
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

$sql = "SELECT a.activity_id, 
               a.activity_title, 
               a.activity_description, 
               a.activity_status, 
               a.ends_at, 
               a.last_modified_at, 
               c.company_name, 
               CONCAT(u.email, ' (', u.first_names, ' ', u.last_name, ')') as author
        FROM activities a, companies c, users u
        WHERE a.company_id=c.company_id 
        AND a.activity_record_status = 'a'
        AND u.user_id=a.entered_by ";

if ($user != '') {
	$feed_name .= " User is $user";
	$sql .= "AND (a.user_id = $user_id OR a.entered_by = $user_id) ";
}

if ($created != '') {
	$feed_name .= " Created by $created";
	$sql .= "AND a.entered_by = $created_user_id ";
}

if ($assigned != '') {
	$feed_name .= " Assigned to $assigned";
	$sql .= "AND a.user_id = $assigned_user_id ";
}

if ($type != 'all') {
	$feed_name .= " Type is $type";
	$sql .= "AND a.activity_type_id = $activity_type_id ";
}

switch ($status) {
case "all":
	$sql .= 'ORDER BY a.last_modified_at DESC';
	break;
case "open":
	$sql .= "AND a.activity_status = 'o' and a.scheduled_at <= NOW() ";
	$sql .= 'ORDER BY a.last_modified_at DESC';
	break;
case "scheduled":
	$sql .= "AND a.activity_status = 'o' and a.scheduled_at > NOW() ";
	$sql .= 'ORDER BY a.scheduled_at ASC';
	break;
case "overdue":
	$sql .= "AND a.activity_status = 'o' and a.ends_at < NOW() ";
	$sql .= 'ORDER BY a.ends_at ASC';
	break;
case "closed":
	$sql .= "AND a.activity_status = 'c' ";
	$sql .= 'ORDER BY a.last_modified_at DESC';
	break;
case "current":
	$sql .= "AND a.scheduled_at <= NOW() ";
	$sql .= 'ORDER BY a.last_modified_at DESC';
	break;
}

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
		$last_modified_at = $rst->fields['last_modified_at'];
		$last_modified_f = date("r", strtotime($last_modified_at));
		$pub_date = date("r", strtotime($last_modified_at));
		$company_name = $rst->fields['company_name'];
		$author = $rst->fields['author'];
		$description = "&lt;p&gt;&lt;b&gt;$company_name&lt;/b&gt;&lt;/p&gt;" . $activity_description;
		$items_text .= "      <item>\n";
		$items_text .= '         <title>' . $activity_title . '</title>' . "\n";
		$items_text .= '         <link>' . $http_site_root . '/activities/one.php?activity_id=' .$activity_id . '</link>' . "\n";
		$items_text .= '         <description>' . $description . '</description>' . "\n";
		$items_text .= '         <author>' . $author . '</author>' . "\n";
		$items_text .= '         <pubDate>' . $pub_date . '</pubDate>' . "\n";
		$items_text .= '         <guid isPermaLink="false">' . $http_site_root . '/activities/one.php?activity_id=' .$activity_id . '?mod=' . $last_modified_f . '</guid>' . "\n";
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

//this needs to be changed to reflect the last actual change in the feed
$last_date = date("r");

header("Content-Type: text/xml");

echo '<?xml version="1.0" encoding="utf-8"?>' . "\n\n";
?>
<rss version="2.0">
   <channel>
      <title><?php echo $feed_name; ?></title>
      <link><?php echo $feed_location; ?></link>
      <description>A list of activities from XRMS</description>
      <language>en-US</language>
      <pubDate><?php echo $now; ?></pubDate>
      <lastBuildDate><?php echo $last_date; ?></lastBuildDate>
      <docs>http://blogs.law.harvard.edu/tech/rss</docs>
      <generator>XRMS</generator>
      <ttl>30</ttl>

 <?php echo $items_text; ?>

   </channel>
</rss>
<?php

/**
 * $Log: activities.php,v $
 * Revision 1.8  2006/04/05 01:21:51  vanmer
 * - ensure last date is provided to rss stream
 *
 * Revision 1.7  2006/01/02 23:46:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.6  2005/06/29 16:06:55  maulani
 * - Add author tag to feeds
 *
 * Revision 1.5  2005/05/09 13:05:21  maulani
 * - Correct SQL for All Activities case
 *
 * Revision 1.4  2005/03/20 15:22:23  maulani
 * - Have RSS feed title relect options selected by user
 *
 * Revision 1.3  2005/03/07 11:51:15  maulani
 * - Improve sort based on activity status
 *
 * Revision 1.2  2005/02/10 14:15:25  maulani
 * - Change SQL to use SelectLimit
 *
 * Revision 1.1  2005/02/05 23:11:30  maulani
 * - Provide RSS Feed for activities
 *
 */
?>
