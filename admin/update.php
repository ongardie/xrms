<?php
/**
 * install/update.php - Update the database from a previous version of xrms
 *
 * When coding this file, it is important that everything only happen after
 * a test.  This file must be non-destructive and only make the changes that
 * must be made.
 *
 * @author Beth Macknik
 * $Id: update.php,v 1.7 2004/04/25 23:09:56 braverock Exp $
 */

/**
 * Confirm that the table does not currently have any records.
 *
 */

// where do we include from
require_once('../include-locations.inc');

// get required common files
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

// vars.php sets all of the installation-specific variables
require_once($include_directory . 'vars.php');

$session_user_id = session_check();

// make a database connection
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$msg = '';


// Make sure that there is an admin record in roles
$sql = "select count(*) as recCount from roles where role_short_name='Admin'";
$rst = $con->execute($sql);
$recCount = $rst->fields['recCount'];
if ($recCount == 0) {
    $msg .= 'Added an Admin role.<BR><BR>';
    $sql ="insert into roles (role_short_name, role_pretty_name, role_pretty_plural, role_display_html) values ('Admin', 'Admin', 'Admin', 'Admin')";
    $rst = $con->execute($sql);
}


// Make sure that there is a user with admin privileges
$sql = "select role_id from roles where role_short_name='Admin'";
$rst = $con->execute($sql);
$role_id = $rst->fields['role_id'];
$sql = "select count(*) as recCount from users where role_id=$role_id";
$rst = $con->execute($sql);
$recCount = $rst->fields['recCount'];
if ($recCount == 0) {
    // none of the users have Admin access, so give the user with the lowest user_id Admin access
    $sql = "select min(user_id) as user_id from users";
    $rst = $con->execute($sql);
    if (!$rst) {
        // Oops.  The real problem is that we have no users.  Create one with admin access
        $msg .= 'Add user1 with Admin access.<BR><BR>';
        $sql ="insert into users (role_id, username, password, last_name, first_names, email, language) values ($role_id, 'user1', '24c9e15e52afc47c225b757e7bee1f9d', 'One', 'User', 'user1@somecompany.com', 'english')";
        $rst = $con->execute($sql);
    } else {
        $user_id = $rst->fields['user_id'];
        $msg .= "Give Admin access to $user_id.<BR><BR>";
        $sql ="update users set role_id=$role_id where user_id=$user_id";
        $rst = $con->execute($sql);
    }
}

//make sure that there is a case_priority_score_adjustment column
//should put a test here, but alter table is non-destructive
$sql = "alter table case_priorities add case_priority_score_adjustment int not null after case_priority_display_html";
$rst = $con->execute($sql);
// end case_priority_display_html

//make sure that there is a status_open_indicator column in campagins
//should put a test here, but alter table is non-destructive
//This is used for reports/open-items.php and reports/completed-items.php reports
//Similiar to opportunity_statuses, 'o' means open, anything else means "completed" for the completed-item report
$sql = "alter table campaign_statuses add status_open_indicator char(1) not null default \"o\" after campaign_status_id";
$rst = $con->execute($sql);
// end

//set "CLOSED" campagin status_open_indicator to "c"
//should put a test here, but alter table is non-destructive
//This is used for reports/open-items.php and reports/completed-items.php reports
//This sets the default "Closed" campagin status with a status_open_indicator of "c" for "Closed"
$sql = "update campaign_statuses set status_open_indicator = \"c\" where campaign_status_short_name = \"CLO\"";
$rst = $con->execute($sql);
// end

//make sure that there is connection detail columns in the audit_items table
//these are done separately in case one column already exists
//should put a test here, but alter table is non-destructive
//These items are used for "Connection Details" in reports/audit-items.php
//remote_addr is the client's IP address. varchar(40) should be big enough for IPv6 addresses
$sql = "alter table audit_items add remote_addr varchar(40) after audit_item_timestamp";
$rst = $con->execute($sql);
//remote_port is the client's requesting port.r
// This is useful for comparing to network
//packet dumps and tracing connections through firewalls.
$sql = "alter table audit_items add remote_port int(6) after remote_addr";
$rst = $con->execute($sql);
//session_id stores _COOKIE["PHPSESSID"], used for tracking a user's session
$sql = "alter table audit_items add session_id varchar(50) after remote_port";
$rst = $con->execute($sql);
// end

//make sure that there is a status_open_indicator column in campagins
//should put a test here, but alter table is non-destructive
$sql = "alter table campaign_statuses add status_open_indicator char(1) not null default 'o' after campaign_status_id";
$rst = $con->execute($sql);
// end case_priority_display_html

//make sure that the contacts table has a division_id filed, since folks with a 12Jan install won't have it
//should put a test here, but alter table is non-destructive
$sql = "alter table contacts add division_id int not null after company_id";
$rst = $con->execute($sql);
//end division_id update

// Fix problem introduced by buggy Mar 19, 2004 install code
// This will modify the initial data appropriately
$sql = "update address_format_strings set address_format_string='";
$sql .= '$lines<br>$city, $province $postal_code<br>$country';
$sql .= "' where address_format_string!='";
$sql .= '$lines<br>$city, $province $postal_code<br>$country';
$sql .= "' and address_format_string_id=1";
$rst = $con->execute($sql);

// Add indexes so data integrity checks take a reasonable about of time
$sql = "create index company_id on addresses (company_id)";
$rst = $con->execute($sql);
$sql = "create index company_id on contacts (company_id)";
$rst = $con->execute($sql);
$sql = "create index company_record_status on companies (company_record_status)";
$rst = $con->execute($sql);
$sql = "create index contact_record_status on contacts (contact_record_status)";
$rst = $con->execute($sql);
$sql = "create index address_record_status on addresses (address_record_status)";
$rst = $con->execute($sql);

// Make sure that the database has the correct legal_name column
$sql = "alter table companies change company_legal_name legal_name varchar( 100 ) not null";
$rst = $con->execute($sql);


//close the database connection, because we don't need it anymore
$con->close();

$page_title = "Update Complete";
start_page($page_title, true, $msg);

echo $msg;
?>

<BR>
Your database has been updated.
<BR><BR>



<?php

end_page();

/**
 * $Log: update.php,v $
 * Revision 1.7  2004/04/25 23:09:56  braverock
 * add division_id alter table command to resolve problems from upgrading from 12Jan
 *
 * Revision 1.6  2004/04/23 17:11:41  gpowers
 * Removed http_user_agent from audit_items table. It is space consuming and
 * redundant, as most httpd servers can be configured to log this information.
 *
 * If anyone has run the previsous version of this script, no harm will be
 * done, they will just have an extra column in the audit table. But, I wanted
 * to patch this ASAP, to minize the number of people who might run it.
 *
 * Revision 1.5  2004/04/23 16:00:53  gpowers
 * Removed addresses.line3 - this was not an approved change
 * Added comments telling the reasons for the changes
 *
 * Revision 1.4  2004/04/23 15:07:29  gpowers
 * added addresses.line, campaign_statuses.status_open_indicator, audit_items.remote_addr, audit_items.remote_port, audit_items.session_id, audit_items.http_user_agent
 *
 * Revision 1.3  2004/04/13 15:47:12  maulani
 * - add data integrity check so all companies have addresses
 *
 * Revision 1.2  2004/04/13 15:06:41  maulani
 * - Add active contact data integrity check to database cleanup
 *
 * Revision 1.1  2004/04/12 18:59:01  maulani
 * - Make database structure and data cleanup available withing Admin interface
 *
 */
?>
