<?php

//
// system configuration file
//


// database connection info for XRMS system
// eventually there will be a nice, pretty web-based way to install XRMS, but for now you've got to create the db
// yourself and run the xrms-initialization scripts (*.sql) found in the [xrms_root]/sql/mysql directory to create the
// appropriate tables and insert some sample/default data

$xrms_db_dbtype = 'mysql';
$xrms_db_server = 'localhost';
$xrms_db_username = 'your_mysql_username';
$xrms_db_password = 'your_mysql_password';
$xrms_db_dbname = 'your_mysql_database';

// where is this application, web-wise? (no trailing slash)
$http_site_root = "http://www.yoursitename.com/xrms";

//where is the appliation in the filesystem (no trailing slash)
$xrms_file_root = "/full/path/to/xrms";

// directory where uploaded files should go
$max_file_size = 200000;
$tmp_upload_directory = "/full/path/to/xrms/tmp/";
$file_storage_directory = "/full/path/to/xrms/files/storage/";

// directory for exports
// this needs to be relative to the xrms web root (browser needs to be able to see it)
// (no trailing slash)
$tmp_export_directory = "/relative/path/to/xrms/exports/";

// accounting software integration is in the works, but for now
$accounting_system = ''; // no integration

// if you have more than one XRMS installation, these need to be unique so that users logged in to one
// application can't just start using the other one.  This variable sets "scope" to the user's login.
$xrms_system_id = "XRMS";

// what should this application be called?
$app_title = 'XRMS';

// replace this with your organization's name
$system_company_name = 'Acme Distribution';

// so that order numbers can be continuous with whatever you're using now
$order_number_seed = 1000;

// a few user-definable settings (there should be lots more)

$system_rows_per_page = 15;
$recent_items_limit = 15;
$display_how_many_activities_on_company_page = 20;
$display_how_many_activities_on_contact_page = 30;
$display_how_many_audit_items_on_dashboard = 20;
$how_many_rows_to_import_per_page = 10;

/* STYLE OPTIONS */

// replace this with HTML or another image
$required_indicator = "<img height=12 width=12 alt=required src=$http_site_root/img/required.gif>";

// if vertical space is tight, shrink this down and change the font size in the stylesheet
// these shouldn't even be here, though... all style info should be set in the stylesheet
$page_title_height = 70;
$report_graph_height = 400;
$report_graph_width = 600;

// doesn't quite work yet, but should by version 0.9
$after_adding_new_companies_from_your_web_site_redirect_to_this_page = "http://www.yoursite.com/thank-you.html";

// label up to four custom fields for contact information

$contact_custom1_label = "(Custom 1)";
$contact_custom2_label = "(Custom 2)";
$contact_custom3_label = "(Custom 3)";
$contact_custom4_label = "(Custom 4)";

// label up to four custom fields for company information

$company_custom1_label = "(Custom 1)";
$company_custom2_label = "(Custom 2)";
$company_custom3_label = "(Custom 3)";
$company_custom4_label = "(Custom 4)";

?>
