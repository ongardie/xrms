<?php
/**
 * install/sample.php - This page populates some sample data
 *
 * You do not need to run this unless you would like some sample data to use
 * when evaluating xrms.
 *
 * @author Beth Macknik
 * $Id: sample.php,v 1.5 2006/05/02 00:22:32 vanmer Exp $
 */

/**
 * Confirm that the table does not currently have any records.
 *
 */

// include the installation utility routines
require_once('install-utils.inc');
require_once('database.php');
require_once('data.php');

// where do we include from
require_once('../include-locations.inc');

// get required common files
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

// vars.php sets all of the installation-specific variables
require_once($include_directory . 'vars.php');

// make a database connection
$con = get_xrms_dbconnection();

// companies
if (confirm_no_records($con, 'companies')) {
    $sql ="insert into companies (user_id, company_source_id, crm_status_id, industry_id, account_status_id, rating_id, company_name, company_code, profile, phone, phone2, fax, url, default_primary_address, default_billing_address, default_shipping_address, default_payment_address, credit_limit, terms, extref1, extref2, entered_at, entered_by, last_modified_at, last_modified_by) values (1, 1, 2, 1, 4, 4, 'Bushwood Components', 'BUSH01', '(Bushwood Components is a fictitious company.)<p>This field can be used to hold a paragraph or two of text (either plain or <font color=blue><b>HTML</b></font>) about a company.', '(800) 555-2000', '(800) 555-2001', '(800) 555-2002', 'http://www.bushwood.com', 2, 2, 2, 2, 100000, 10, '10090', '10091', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1)";
    $rst = $con->execute($sql);
    $sql ="insert into companies (user_id, company_source_id, crm_status_id, industry_id, account_status_id, rating_id, company_name, company_code, profile, phone, phone2, fax, url, default_primary_address, default_billing_address, default_shipping_address, default_payment_address, credit_limit, terms, extref1, extref2, entered_at, entered_by, last_modified_at, last_modified_by) values (1, 2, 3, 2, 4, 4, 'Polymer Electronics', 'POLY01', '(Polymer Electronics is a fictitious company.)<p>This field can be used to hold a paragraph or two of text (either plain or <font color=blue><b>HTML</b></font>) about a company.', '(800) 555-3000', '(800) 555-3001', '(800) 555-3002', 'http://www.polymer.com', 3, 3, 3, 3, 200000, 20, '10092', '10093', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1)";
    $rst = $con->execute($sql);
    $sql ="insert into companies (user_id, company_source_id, crm_status_id, industry_id, account_status_id, rating_id, company_name, company_code, profile, phone, phone2, fax, url, default_primary_address, default_billing_address, default_shipping_address, default_payment_address, credit_limit, terms, extref1, extref2, entered_at, entered_by, last_modified_at, last_modified_by) values (1, 3, 4, 3, 4, 4, 'Callahan Manufacturing', 'CALL01', '(Callahan Manufacturing is a fictitious company.)<p>This field can be used to hold a paragraph or two of text (either plain or <font color=blue><b>HTML</b></font>) about a company.', '(800) 555-4000', '(800) 555-4001', '(800) 555-4002', 'http://www.callahan.com', 4, 4, 4, 4, 300000, 30, '10094', '10095', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1)";
    $rst = $con->execute($sql);
}

// addresses
if (confirm_no_records($con, 'addresses')) {
//deprecated Unknown Address record, now part of default install
//    $sql ="insert into addresses (address_id, company_id, address_name, address_body, address_record_status, country_id, line1, line2, city, province, postal_code, address_type, use_pretty_address, offset, daylight_savings_id) VALUES (1, 0, 'Unknown Address', 'This company or contact has an unknown address. Please Update', 'a', 218, 'Unknown Address', '', 'Unknown Address', 'AA', '', 'unknown', 't', NULL, NULL)";
    $sql ="insert into addresses (company_id, country_id, address_name, line1, line2, city, province, postal_code, address_body) values (1, 1, 'Address 1', '3201 West Rolling Hills Circle', '', 'Ft. Lauderdale', 'FL', '33328', '3201 West Rolling Hills Circle\nFt. Lauderdale, FL 33328\nUSA')";
    $rst = $con->execute($sql);
    $sql ="insert into addresses (company_id, country_id, address_name, line1, line2, city, province, postal_code, address_body) values (2, 1, 'Address 2', '11 Platinum Drive', '', 'Los Angeles', 'CA', '90001', '11 Platinum Drive\nLos Angeles, CA 90001\nUSA')";
    $rst = $con->execute($sql);
    $sql ="insert into addresses (company_id, country_id, address_name, line1, line2, city, province, postal_code, address_body) values (3, 1, 'Address 3', '123 Main Street', 'Suite 100', 'Sandusky', 'OH', '44870', '123 Main Street\nSuite 100\nSandusky, OH 44870\nUSA')";
    $rst = $con->execute($sql);
}

// contacts
if (confirm_no_records($con, 'contacts')) {
    // insert contacts for the first company
    $sql ="insert into contacts (company_id, address_id, last_name, first_names, summary, title, description, email, work_phone, aol_name, yahoo_name, msn_name, entered_at, entered_by, last_modified_at, last_modified_by) values (1, 1, 'Webb', 'Ty', '1/2 owner', 'Account Manager', 'dad never liked us', 'twebb@bushwoodcc.com', '(555) 555-2100', 'twebb', 'twebb', 'twebb', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1)";
    $rst = $con->execute($sql);
    $sql ="insert into contacts (company_id, address_id, last_name, first_names, summary, title, description, email, work_phone, aol_name, yahoo_name, msn_name, entered_at, entered_by, last_modified_at, last_modified_by) values (1, 1, 'Spackler', 'Carl', 'do not call', 'Assistant Greenskeeper', 'to the bejeezus belt!', 'cspackler@bushwoodcc.com', '(555) 555-2200', 'cspackler', 'cspackler', 'cspackler', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1)";
    $rst = $con->execute($sql);

    // insert contacts for the second company
    $sql ="insert into contacts (company_id, address_id, last_name, first_names, summary, title, description, email, work_phone, aol_name, yahoo_name, msn_name, entered_at, entered_by, last_modified_at, last_modified_by) values (2, 2, 'Fufkin', 'Artie', '', 'Director', '', 'artie@polymer.com', '(555) 555-3100', 'artie', 'artie', 'artie', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1)";
    $rst = $con->execute($sql);
    $sql ="insert into contacts (company_id, address_id, last_name, first_names, summary, title, description, email, work_phone, aol_name, yahoo_name, msn_name, entered_at, entered_by, last_modified_at, last_modified_by) values (2, 2, 'Smalls', 'Derek', '', 'Bass', '', 'derek@polymer.com', '(555) 555-3200', 'derek', 'derek', 'derek', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1)";
    $rst = $con->execute($sql);

    // insert contacts for the third company
    $sql ="insert into contacts (company_id, address_id, last_name, first_names, summary, title, description, email, work_phone, aol_name, yahoo_name, msn_name, entered_at, entered_by, last_modified_at, last_modified_by) values (3, 3, 'Callahan', 'Tommy', 'works nights', 'President/CEO', '', 'tommy@callahan.com', '(555) 555-4100', 'tcallahan', 'tcallahan', 'tcallahan', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1)";
    $rst = $con->execute($sql);
    $sql ="insert into contacts (company_id, address_id, last_name, first_names, summary, title, description, email, work_phone, aol_name, yahoo_name, msn_name, entered_at, entered_by, last_modified_at, last_modified_by) values (3, 3, 'Hayden', 'Richard', 'good contact', 'Buyer', 'All Lines', 'richard@callahan.com', '(555) 555-4200', 'rhayden', 'rhayden', 'rhayden', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1)";
    $rst = $con->execute($sql);
}




$page_title = "Sample Creation Complete";
start_page($page_title, false, $msg);

?>

<BR>
Some sample data has been populated in your database.
<BR><BR>
The initial user available is "user1" with a password of "user1".  You should change this
as soon as you login.  (It can be changed in Users within the Administration section.)
<BR><BR>
You may now <a href="../login.php">login</a> to get started.



<?php

end_page();

/**
 * $Log: sample.php,v $
 * Revision 1.5  2006/05/02 00:22:32  vanmer
 * - moved default address from sample data into base install
 * - removed default address from sample data
 *
 * Revision 1.4  2006/01/02 23:23:09  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.3  2005/05/24 15:21:24  braverock
 * - add missing "
 *
 * Revision 1.2  2005/05/24 15:20:36  braverock
 * - add unknown address as first address in sample data
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