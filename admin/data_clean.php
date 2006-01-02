<?php
/**
 * install/data_clean.php - Cleanup the database
 *
 * When coding this file, it is important that everything only happen after
 * a test.  This file must be non-destructive and only make the changes that
 * must be made.
 *
 * @author Beth Macknik
 * @todo: Active companies should always have active addresses
 *
 * $Id: data_clean.php,v 1.14 2006/01/02 22:38:16 vanmer Exp $
 */

// where do we include from
require_once('../include-locations.inc');

// get required common files
// vars.php sets all of the installation-specific variables
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

// make a database connection
$con = get_xrms_dbconnection();
//$con->debug = 1;

$msg = '';

// Make sure that there is a last name for every contact
$sql = "SELECT * FROM contacts WHERE last_name = ''";
$rst = $con->execute($sql);

$rec = array();
$rec['last_name'] = '[last name]';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$rst = $con->execute($upd);

// Make sure that there is a first name for every contact
$sql = "SELECT * FROM contacts WHERE first_names = ''";
$rst = $con->execute($sql);

$rec = array();
$rec['first_names'] = '[first names]';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$rst = $con->execute($upd);

// Make sure that there is a company name for every company
$sql = "SELECT * FROM companies WHERE company_name = ''";
$rst = $con->execute($sql);

$rec = array();
$rec['company_name'] = '[company name]';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$rst = $con->execute($upd);

// There needs to be at least one contact for each company
$sql = "SELECT companies.company_id, companies.company_record_status ";
$sql .= "FROM companies ";
$sql .= "LEFT JOIN contacts ON companies.company_id = contacts.company_id ";
$sql .= "WHERE contacts.company_id IS NULL";
$rst = $con->execute($sql);
$companies_to_fix = $rst->RecordCount();
if ($companies_to_fix > 0) {
    $msg .= "Need to create contacts for $companies_to_fix companies<BR><BR>";
    while (!$rst->EOF) {
        $company_id = $rst->fields['company_id'];
        $company_record_status = $rst->fields['company_record_status'];

        $sql2 = "SELECT * FROM contacts WHERE 1 = 2"; //select empty record as placeholder
        $rst2 = $con->execute($sql2);
        
        $rec = array();
        $rec['company_id'] = $company_id;
        $rec['last_name'] = 'Contact';
        $rec['first_names'] = 'Default';
        $rec['contact_record_status'] = $company_record_status;
        $rec['entered_by'] = $session_user_id;
        $rec['entered_at'] = time();
        $rec['last_modified_at'] = time();
        $rec['last_modified_by'] = $session_user_id;

        $ins = $con->GetInsertSQL($rst2, $rec, get_magic_quotes_gpc());
        $con->execute($ins);

        $rst->movenext();
    }
}

// There needs to be at least one active contact for each active company
$sql = "SELECT companies.company_id ";
$sql .= "FROM companies ";
$sql .= "LEFT JOIN contacts ON companies.company_id = contacts.company_id ";
$sql .= "AND contacts.contact_record_status = 'a' ";
$sql .= "WHERE contacts.company_id IS NULL ";
$sql .= "AND companies.company_record_status = 'a' ";
$rst = $con->execute($sql);
$companies_to_fix = $rst->RecordCount();
if ($companies_to_fix > 0) {
    $msg .= "Need to create active contacts for $companies_to_fix active companies<BR><BR>";
    while (!$rst->EOF) {
        $company_id = $rst->fields['company_id'];

        $sql2 = "SELECT * FROM contacts WHERE 1 = 2"; //select empty record as placeholder
        $rst2 = $con->execute($sql2);
        
        $rec = array();
        $rec['company_id'] = $company_id;
        $rec['last_name'] = 'Contact';
        $rec['first_names'] = 'Default';
        $rec['entered_by'] = $session_user_id;
        $rec['entered_at'] = time();
        $rec['last_modified_at'] = time();
        $rec['last_modified_by'] = $session_user_id;

        $ins = $con->GetInsertSQL($rst2, $rec, get_magic_quotes_gpc());
        $con->execute($ins);

        $rst->movenext();
    }
}

// There needs to be at least one address for each company
$sql = "SELECT companies.company_id, companies.company_record_status ";
$sql .= "FROM companies ";
$sql .= "LEFT JOIN addresses ON companies.company_id = addresses.company_id ";
$sql .= "WHERE addresses.company_id IS NULL";
$rst = $con->execute($sql);
$companies_to_fix = $rst->RecordCount();
if ($companies_to_fix > 0) {
    $msg .= "Need to create addresses for $companies_to_fix companies<BR><BR>";
    while (!$rst->EOF) {
        $company_id = $rst->fields['company_id'];
        $company_record_status = $rst->fields['company_record_status'];
        
        $sql2 = "SELECT * FROM addresses WHERE 1 = 2"; //select empty record as placeholder
        $rst2 = $con->execute($sql2);
        
        $rec = array();
        $rec['company_id'] = $company_id;
        $rec['country_id'] = $default_country_id;
        $rec['address_name'] = 'Main';
        $rec['address_record_status'] = $company_record_status;

        $ins = $con->GetInsertSQL($rst2, $rec, get_magic_quotes_gpc());
        $con->execute($ins);

        $rst->movenext();
    }
}

// There needs to be at least one active address for each active company
$sql = "SELECT companies.company_id ";
$sql .= "FROM companies ";
$sql .= "LEFT JOIN addresses ON companies.company_id = addresses.company_id ";
$sql .= "AND addresses.address_record_status = 'a' ";
$sql .= "WHERE addresses.company_id IS NULL ";
$sql .= "AND companies.company_record_status = 'a' ";
$rst = $con->execute($sql);
$companies_to_fix = $rst->RecordCount();
if ($companies_to_fix > 0) {
    $msg .= "Need to create active addresses for $companies_to_fix active companies<BR><BR>";
    while (!$rst->EOF) {
        $company_id = $rst->fields['company_id'];

        $sql2 = "SELECT * FROM addresses WHERE 1 = 2"; //select empty record as placeholder
        $rst2 = $con->execute($sql2);
        
        $rec = array();
        $rec['company_id'] = $company_id;
        $rec['country_id'] = $default_country_id;
        $rec['address_name'] = 'Main';

        $ins = $con->GetInsertSQL($rst2, $rec, get_magic_quotes_gpc());
        $con->execute($ins);

        $rst->movenext();
    }
}

// Each company must have a valid default_primary_address
$sql = "SELECT companies.company_id, companies.company_record_status ";
$sql .= "FROM companies ";
$sql .= "LEFT JOIN addresses ON companies.default_primary_address = addresses.address_id ";
$sql .= "AND companies.company_id = addresses.company_id ";
$sql .= "WHERE addresses.address_id IS NULL ";
$rst = $con->execute($sql);
$companies_to_fix = $rst->RecordCount();
if ($companies_to_fix > 0) {
    $msg .= "Need to assign default_primary_address for $companies_to_fix companies<BR><BR>";
    while (!$rst->EOF) {
        $company_id = $rst->fields['company_id'];
        $company_record_status = $rst->fields['company_record_status'];
        $sql = "select min(address_id) as the_address
                FROM addresses
                WHERE addresses.company_id = $company_id";
        if($company_record_status = 'a') {
            $sql .= " AND addresses.address_record_status = 'a'";
        }
        $ast = $con->execute($sql);
        $address_id = $ast->fields['the_address'];

        $sql2 = "SELECT * FROM companies WHERE company_id = $company_id";
        $rst2 = $con->execute($sql2);
        
        $rec = array();
        $rec['default_primary_address'] = $address_id;
        
        $upd = $con->GetUpdateSQL($rst2, $rec, false, get_magic_quotes_gpc());
        $con->execute($upd);

        $rst->movenext();
    }
}

// Each company must have a valid default_billing_address
$sql = "SELECT companies.company_id, companies.company_record_status ";
$sql .= "FROM companies ";
$sql .= "LEFT JOIN addresses ON companies.default_billing_address = addresses.address_id ";
$sql .= "AND companies.company_id = addresses.company_id ";
$sql .= "WHERE addresses.address_id IS NULL ";
$rst = $con->execute($sql);
$companies_to_fix = $rst->RecordCount();
if ($companies_to_fix > 0) {
    $msg .= "Need to assign default_billing_address for $companies_to_fix companies<BR><BR>";
    while (!$rst->EOF) {
        $company_id = $rst->fields['company_id'];
        $company_record_status = $rst->fields['company_record_status'];
        $sql = "select min(address_id) as the_address
                FROM addresses
                WHERE addresses.company_id = $company_id";
        if($company_record_status = 'a') {
            $sql .= " AND addresses.address_record_status = 'a'";
        }
        $ast = $con->execute($sql);
        $address_id = $ast->fields['the_address'];

        $sql2 = "SELECT * FROM companies WHERE company_id = $company_id";
        $rst2 = $con->execute($sql2);
        
        $rec = array();
        $rec['default_billing_address'] = $address_id;
        
        $upd = $con->GetUpdateSQL($rst2, $rec, false, get_magic_quotes_gpc());
        $con->execute($upd);

        $rst->movenext();
    }
}

// Each company must have a valid default_shipping_address
$sql = "SELECT companies.company_id, companies.company_record_status ";
$sql .= "FROM companies ";
$sql .= "LEFT JOIN addresses ON companies.default_shipping_address = addresses.address_id ";
$sql .= "AND companies.company_id = addresses.company_id ";
$sql .= "WHERE addresses.address_id IS NULL ";
$rst = $con->execute($sql);
$companies_to_fix = $rst->RecordCount();
if ($companies_to_fix > 0) {
    $msg .= "Need to assign default_shipping_address for $companies_to_fix companies<BR><BR>";
    while (!$rst->EOF) {
        $company_id = $rst->fields['company_id'];
        $company_record_status = $rst->fields['company_record_status'];
        $sql = "select min(address_id) as the_address
                FROM addresses
                WHERE addresses.company_id = $company_id";
        if($company_record_status = 'a') {
            $sql .= " AND addresses.address_record_status = 'a'";
        }
        $ast = $con->execute($sql);
        $address_id = $ast->fields['the_address'];

        $sql2 = "SELECT * FROM companies WHERE company_id = $company_id";
        $rst2 = $con->execute($sql2);
        
        $rec = array();
        $rec['default_shipping_address'] = $address_id;
        
        $upd = $con->GetUpdateSQL($rst2, $rec, false, get_magic_quotes_gpc());
        $con->execute($upd);

        $rst->movenext();
    }
}

// Each company must have a valid default_payment_address
$sql = "SELECT companies.company_id, companies.company_record_status ";
$sql .= "FROM companies ";
$sql .= "LEFT JOIN addresses ON companies.default_payment_address = addresses.address_id ";
$sql .= "AND companies.company_id = addresses.company_id ";
$sql .= "WHERE addresses.address_id IS NULL ";
$rst = $con->execute($sql);
$companies_to_fix = $rst->RecordCount();
if ($companies_to_fix > 0) {
    $msg .= "Need to assign default_payment_address for $companies_to_fix companies<BR><BR>";
    while (!$rst->EOF) {
        $company_id = $rst->fields['company_id'];
        $company_record_status = $rst->fields['company_record_status'];
        $sql = "select min(address_id) as the_address
                FROM addresses
                WHERE addresses.company_id = $company_id";
        if($company_record_status = 'a') {
            $sql .= " AND addresses.address_record_status = 'a'";
        }
        $ast = $con->execute($sql);
        $address_id = $ast->fields['the_address'];

        $sql2 = "SELECT * FROM companies WHERE company_id = $company_id";
        $rst2 = $con->execute($sql2);
        
        $rec = array();
        $rec['default_payment_address'] = $address_id;
        
        $upd = $con->GetUpdateSQL($rst2, $rec, false, get_magic_quotes_gpc());
        $con->execute($upd);

        $rst->movenext();
    }
}

//close the database connection, because we don't need it anymore
$con->close();

$page_title = _("Database Cleanup Complete");
start_page($page_title, true, $msg);

echo $msg;
?>

<BR>
<?php echo _("Your database has been cleaned."); ?>
<BR><BR>


<?php

end_page();

/**
 * $Log: data_clean.php,v $
 * Revision 1.14  2006/01/02 22:38:16  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.13  2005/12/06 22:40:20  vanmer
 * - removed data_clean code that deals with system parameters
 *
 * Revision 1.12  2005/01/23 21:47:58  maulani
 * - Move vars.php to load earlier in the file to address bug 1105960.
 *   For an unknown reason, vars.php must load before the utils and adodb
 *   files.
 *
 * Revision 1.11  2005/01/09 15:54:43  maulani
 * - Set a company name for all companies that have a blank name
 *
 * Revision 1.10  2004/11/10 15:31:05  maulani
 * - Add clean routine to fix bad entry in system_parameters that was corrupted
 *   by bug in SetSystemParamemeters routine
 *
 * Revision 1.9  2004/07/16 18:52:43  cpsource
 * - Add role check inside of session_check
 *
 * Revision 1.8  2004/07/16 13:52:00  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.7  2004/06/14 18:13:51  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.6  2004/04/21 21:54:35  maulani
 * - Add additional company <--> address relationship verifications
 *
 * Revision 1.5  2004/04/21 16:25:15  maulani
 * - Remove code that could alter valid data
 *
 * Revision 1.4  2004/04/21 05:16:22  braverock
 * - set default address id for companies without address
 *   - fixes loop logic error in original code
 *   - from patch submitted by Glenn Powers
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