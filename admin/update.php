<?php
/**
 * install/update.php - Update the database from a previous version of xrms
 *
 * When coding this file, it is important that everything only happen after
 * a test.  This file must be non-destructive and only make the changes that
 * must be made.
 *
 * @author Beth Macknik
 * $Id: update.php,v 1.17 2004/07/01 19:48:09 braverock Exp $
 */

// where do we include from
require_once('../include-locations.inc');

// get required common files
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

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
    $sql = "SELECT * FROM roles WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['role_short_name'] = 'Admin';
    $rec['role_pretty_name'] = 'Admin';
    $rec['role_pretty_plural'] = 'Admin';
    $rec['role_display_html'] = 'Admin';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);
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
        $sql = "SELECT * FROM roles WHERE 1 = 2"; //select empty record as placeholder
        $rst = $con->execute($sql);

        $rec = array();
        $rec['role_id'] = $role_id;
        $rec['username'] = 'user1';
        $rec['password'] = '24c9e15e52afc47c225b757e7bee1f9d';
        $rec['last_name'] = 'One';
        $rec['first_names'] = 'User';
        $rec['email'] = 'user1@somecompany.com';
        $rec['language'] = 'english';

        $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
        $con->execute($ins);
    } else {
        $user_id = $rst->fields['user_id'];
        $msg .= "Give Admin access to $user_id.<BR><BR>";
        $sql = "SELECT * FROM users WHERE user_id = $user_id";
        $rst = $con->execute($sql);

        $rec = array();
        $rec['role_id'] = $role_id;

        $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
        $con->execute($upd);
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
$sql = "SELECT * FROM campaign_statuses WHERE campaign_status_short_name = 'CLO'";
$rst = $con->execute($sql);

$rec = array();
$rec['status_open_indicator'] = 'c';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);
// end

//add sort order to activity types
//should put a test here, but alter table is non-destructive
$sql = "ALTER TABLE activity_types ADD sort_order TINYINT NOT NULL DEFAULT '1' AFTER activity_type_record_status";
$rst = $con->execute($sql);

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
$sql = "SELECT * FROM address_format_strings WHERE address_format_string != '" .
'$lines<br>$city, $province $postal_code<br>$country' . "' AND address_format_string_id=1";
$rst = $con->execute($sql);

$rec = array();
$rec['address_format_string'] = '$lines<br>$city, $province $postal_code<br>$country';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

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

// Add the system_parameters table
$sql = 'create table system_parameters (';
$sql .= 'param_id       varchar(40) not null unique,';
$sql .= 'string_val     varchar(100),';
$sql .= 'int_val        int,';
$sql .= 'float_val      float,';
$sql .= 'datetime_val   datetime';
$sql .= ')';
$rst = $con->execute($sql);

// Make sure that there is an default GST offset in system_parameters
$sql = "select count(*) as recCount from system_parameters where param_id='Default GST Offset'";
$rst = $con->execute($sql);
$recCount = $rst->fields['recCount'];
if ($recCount == 0) {
    $msg .= 'Added a default GST offset.<BR><BR>';
    $sql = "SELECT * FROM system_parameters WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['param_id'] = 'Default GST Offset';
    $rec['int_val'] = -5;

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);
}

//add statuses to activities
$sql = "alter table activities add on_what_status int not null default 0 after on_what_id";
$rst = $con->execute($sql);

//create the activity_templates table if we need it
$sql = "create table activity_templates (
                activity_template_id    int not null primary key auto_increment,
                role_id                 int not null default 0,
                activity_type_id        int not null default 0,
                on_what_table           varchar(100) not null default '',
                on_what_id              int not null default 0,
                activity_title          varchar(100) not null default '',
                activity_description    text not null default '',
                duration                varchar(20) default 1 not null,
                sort_order              tinyint not null default 1,
                activity_template_record_status         char not null default 'a'
                )";
        //execute
        $rst = $con->execute($sql);

// create the relationship_types table if we need it
$sql ="CREATE TABLE relationship_types (
                relationship_type_id int(10) unsigned NOT NULL auto_increment,
                relationship_name varchar(48) NOT NULL default '',
                from_what_table varchar(24) NOT NULL default '',
                to_what_table varchar(24) NOT NULL default '',
                from_what_text varchar(32) NOT NULL default '',
                to_what_text varchar(32) NOT NULL default '',
                relationship_status char(1) NOT NULL default 'a',
                pre_formatting varchar(25) default NULL,
                post_formatting varchar(25) default NULL,
                PRIMARY KEY  (relationship_type_id)
                )";
        //execute
        $rst = $con->execute($sql);

if (confirm_no_records($con, 'relationship_types')) {
    $sql = "INSERT INTO relationship_types
            (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
            VALUES
            ('company relationships','companies','companies','Acquired','Acquired by','a',NULL,NULL)";
    $rst = $con->execute($sql);
    $sql = "INSERT INTO relationship_types
            (relationship_type_id,relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
            VALUES
            ('company relationships','companies','companies','Retains Consultant','Consultant for','a',NULL,NULL)";
    $rst = $con->execute($sql);
    $sql = "INSERT INTO relationship_types
            (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
            VALUES
            ('company relationships','companies','companies','Manufactures for','Uses Manufacturer','a',NULL,NULL)";
    $rst = $con->execute($sql);
    $sql = "INSERT INTO relationship_types
            (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
            VALUES
            ('company relationships','companies','companies','Parent Company of','Subsidiary of','a',NULL,NULL)";
    $rst = $con->execute($sql);
    $sql = "INSERT INTO relationship_types
            (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
            VALUES
            ('company relationships','companies','companies','Uses Supplier','Supplier for','a',NULL,NULL)";
    $rst = $con->execute($sql);
    $sql = "INSERT INTO relationship_types
            (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
            VALUES
            ('company link','contacts','companies','Owns','Owned By','a','<b>','</b>')";
    $rst = $con->execute($sql);
    $sql = "INSERT INTO relationship_types
            (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
            VALUES
            ('company link','contacts','companies','Manages','Managed By','a',NULL,NULL)";
    $rst = $con->execute($sql);
    $sql = "INSERT INTO relationship_types
            (relationship_name,from_what_table,to_what_table,from_what_text,to_what_text,relationship_status,pre_formatting,post_formatting)
            VALUES
            ('company link','contacts','companies','Consultant for','Retains Consultant','a',NULL,NULL)";
    $rst = $con->execute($sql);
}

// create the relationships table if we need it
$sql ="CREATE TABLE relationships (
        relationship_id int(10) unsigned NOT NULL auto_increment,
        from_what_id int(10) unsigned NOT NULL default '0',
        to_what_id int(10) unsigned NOT NULL default '0',
        relationship_type_id int(10) unsigned NOT NULL default '0',
        established_at datetime default NULL,
        ended_on datetime default NULL,
        relationship_status char(1) NOT NULL default 'a',
        PRIMARY KEY  (relationship_id),
        KEY from_what_id (from_what_id),
        KEY to_what_id (to_what_id)
        )";
        //execute
        $rst = $con->execute($sql);

// now convert existing relationships
if (confirm_no_records($con, 'relationships')) {
    $sql = "select company_from_id, relationship_type, company_to_id, established_at
            from company_relationship
            order by established_at desc";
    $rst = $con->execute($sql);
    if ($rst) {
        while (!$rst->EOF) {
            $direction = '';

            $to_what_id     = $rst->fields['company_to_id'];
            $from_what_id   = $rst->fields['company_from_id'];
            $established_at =  $con->qstr($rst->fields['established_at'], get_magic_quotes_gpc());

            $relationship_type_id = 0;

            $old_type = $con->qstr($rst->fields['relationship_type'], get_magic_quotes_gpc());
            $match_sql = "select relationship_type_id from relationship_types
                              where from_what_table = 'companies' and to_what_table = 'companies'
                              and to_what_text LIKE $old_type ";

            $relationship_type_id = (int) $con->GetOne($match_sql);
            if ((int) $relationship_type_id == (int) 0) {
                $match_sql = "select relationship_type_id from relationship_types
                              where from_what_table = 'companies' and to_what_table = 'companies'
                              and from_what_text LIKE $old_type ";

                $relationship_type_id = (int) $con->GetOne($match_sql);
                $direction = 'from';
            }
            if ($relationship_type_id) {
                //now insert the row in the new table
                if ($direction=='from'){
                    $new_from_id = $to_what_id;
                    $new_to_id   = $from_what_id;
                } else {
                    $new_to_id = $to_what_id;
                    $new_from_id = $from_what_id;
                }
                $sql = "insert into relationships
                (from_what_id, to_what_id, relationship_type_id, established_at)
                values (" . $new_from_id . ", " . $new_to_id . ", " . $relationship_type_id . ", $established_at)";
                $ins_rst=$con->execute($sql);
            }
            $rst->movenext();
        }
    }
} //end convert old relationships

// Make sure that the additional address format strings are added
$sql = "select count(*) as recCount from address_format_strings";
$rst = $con->execute($sql);
$recCount = $rst->fields['recCount'];
if ($recCount <16) {
    $msg .= 'Added additional address format strings.<BR><BR>';

    define('ADODB_FORCE_NULLS', 0);
    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 0;
    $rec['address_format_string'] = '';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);
    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 2;
    $rec['address_format_string'] = '$lines<br>$postal_code $city<br>$province<br>$country';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);
    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 3;
    $rec['address_format_string'] = '$lines<br>$postal_code $city $province<br>$country';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 4;
    $rec['address_format_string'] = '$lines<br>$city $province $postal_code<br>$country';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 5;
    $rec['address_format_string'] = '$lines<br>$postal_code $province<br>$city<br>$country';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 6;
    $rec['address_format_string'] = '$lines<br>$postal_code $city<br>$country';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 7;
    $rec['address_format_string'] = '$postal_code $city<br>$lines<br>$country';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 8;
    $rec['address_format_string'] = '$lines<br>$province<br>$city $postal_code<br>$country';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 9;
    $rec['address_format_string'] = '$lines<br>$city<br>$province $postal_code<br>$country';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 10;
    $rec['address_format_string'] = '$postal_code<br>$province $city<br>$lines<br>$country';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 11;
    $rec['address_format_string'] = '$lines<br>$city $province<br>$postal_code<br>$country';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 12;
    $rec['address_format_string'] = '$country $postal_code<br>$province $city<br>$lines';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 13;
    $rec['address_format_string'] = '$lines<br>$city<br>$province<br>$postal_code<br>$country';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 14;
    $rec['address_format_string'] = '$lines<br>$city $postal_code<br>$country';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $sql = "SELECT * FROM address_format_strings WHERE 1 = 2"; //select empty record as placeholder
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 15;
    $rec['address_format_string'] = '$lines<br>$city, $province $postal_code<br>$country';

    $ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $sql = "SELECT * FROM countries WHERE country_name in ('Argentina', 'Kuwait', 'Oman', 'Poland')";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 2;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $sql = "SELECT * FROM countries WHERE country_name in ('Brazil', 'China', 'Italy', 'Mexico', 'Portugal', 'Spain')";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 3;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $sql = "SELECT * FROM countries WHERE country_name in ('Australia', 'Canada', 'Hong Kong Special Administrative Region of China', 'Ireland', 'Taiwan')";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 4;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $sql = "SELECT * FROM countries WHERE country_name in ('Denmark')";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 5;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $sql = "SELECT * FROM countries WHERE country_name in ('Austria', 'Bahrain', 'Belgium', 'Bosnia and Herzegovina', 'Bulgaria', 'Croatia', 'Czech Republic', 'Egypt', 'Finland', 'France', 'France, metropolitan', 'Germany', 'Greece', 'Greenland', 'Iceland', 'Israel', 'Jordan', 'Lebanon', 'Luxembourg', 'Netherlands', 'Norway', 'Qatar', 'Romania', 'Saudi Arabia', 'Singapore', 'Slovakia', 'Slovenia', 'Sweden', 'Switzerland', 'Syrian Arab Republic', 'Turkey', 'Yemen')";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 6;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $sql = "SELECT * FROM countries WHERE country_name in ('Hungary')";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 7;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $sql = "SELECT * FROM countries WHERE country_name in ('India', 'New Zealand')";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 8;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $sql = "SELECT * FROM countries WHERE country_name in ('Indonesia')";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 9;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $sql = "SELECT * FROM countries WHERE country_name in ('Japan')";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 10;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $sql = "SELECT * FROM countries WHERE country_name in ('Republic of Korea', 'Ukraine')";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 11;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $sql = "SELECT * FROM countries WHERE country_name in ('Russian Federation')";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 12;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $sql = "SELECT * FROM countries WHERE country_name in ('South Africa', 'United Kingdom')";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 13;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $sql = "SELECT * FROM countries WHERE country_name in ('The former Yugoslav Republic of Macedonia')";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 14;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $sql = "SELECT * FROM countries WHERE country_name in ('United States', 'United States Virgin Islands')";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_id'] = 15;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);
}

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
 * Revision 1.17  2004/07/01 19:48:09  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 * Revision 1.16  2004/07/01 15:23:06  braverock
 * - update default data for relationship_types table
 * - use NAMES -> VALUES SQL construction to be safe
 *
 * Revision 1.15  2004/07/01 12:56:33  braverock
 * - add relationships and relationship_types tables and data to install and update
 *
 * Revision 1.14  2004/06/28 14:30:01  maulani
 * - add address format strings for many countries
 *
 * Revision 1.13  2004/06/26 13:11:29  braverock
 * - execute sql for sort order on activity types
 *   - applies SF patch #979564 by Marc Spoorendonk (grmbl)
 *
 * Revision 1.12  2004/06/14 18:13:51  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.11  2004/06/13 09:13:20  braverock
 * - add sort_order to activity_types
 *
 * Revision 1.10  2004/06/04 14:53:48  braverock
 * - change activity_templates duration to varchar for advanced date functionality
 *
 * Revision 1.9  2004/06/03 16:14:56  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.8  2004/05/04 23:48:02  maulani
 * - Added a system parameters table to the database.  This table can be used
 *   for items that would otherwise be dumped into the vars.php file. These
 *   include config items that are not required for database connectivity nor
 *   have access speed performance implications.  Accessor and setor functions
 *   added to utils-misc.
 * - Still need to create editing screen in admin section
 *
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
 * Revision 1.7  2004/04/13 12:29:20  maulani
 * - Move the data clean and update files into the admin section of XRMS
 *
 * Revision 1.6  2004/04/12 14:34:02  maulani
 * - Add indexes for foreign key company_id
 *
 * Revision 1.5  2004/03/26 16:17:00  maulani
 * - Cleanup formatting
 *
 * Revision 1.3  2004/03/23 14:34:05  braverock
 * - add check for result set before closing rst
 *
 * Revision 1.2  2004/03/22 02:05:08  braverock
 * - add case_priority_score_adjustment to fix SF bug 906413
 *
 * Revision 1.1  2004/03/18 01:07:18  maulani
 * - Create installation tests to check whether the include location and
 *   vars.php have been configured.
 * - Create PHP-based database installation to replace old SQL scripts
 * - Create PHP-update routine to update users to latest schema/data as
 *   XRMS evolves.
 *
 */
?>