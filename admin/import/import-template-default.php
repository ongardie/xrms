<?php
/**
 * Import Template for XRMS - Default Template.
 *
 * These template files exist to define row to field mappings for
 * importing company and contact data into XRMS.
 *
 * The Default template should serve as an example for integrating
 * to other CRM export formats.
 *
 * Copyright (c) 2004 XRMS Development Team
 */

    //company info
    $company_name        = $row['company_name'];
    $legal_name          = $row['legal_name'];
    $division_name       = $row['division_name'];
    $company_website     = $row['website'];
    $company_taxid       = $row['tax_id'];
    $extref1             = $row['extref1'];
    $extref2             = $row['extref2'];
    $extref3             = $row['extref3'];
    $company_custom1     = $row['company_custom1'];
    $company_custom2     = $row['company_custom2'];
    $company_custom3     = $row['company_custom3'];
    $company_custom4     = $row['company_custom4'];
    $employees           = $row['employees'];
    $revenue             = $row['revenue'];
    $credit_limit        = $row['credit_limit'];
    $terms               = $row['terms'];
    $company_profile     = $row['company_profile'];
    $company_code        = $row['company_code'];
    $company_phone       = $row['phone'];
    $company_phone2      = $row['phone2'];
    $company_fax         = $row['fax'];

    //contact info
    $contact_first_names   = $row['first_name'];
    $contact_last_name     = $row['last_name'];
    $contact_email         = htmlspecialchars($row['email']);
    $contact_work_phone    = $row['work_phone'];
    $contact_home_phone    = $row['home_phone'];
    $contact_fax           = $row['fax'];
    $contact_division      = $row['division'];
    $contact_salutation    = $row['salutation'];
    $contact_date_of_birth = $row['date_of_birth'];
    $contact_summary       = $row['summary'];
    $contact_title         = $row['title'];
    $contact_description   = $row['description'];
    $contact_cell_phone    = $row['cell_phone'];
    $contact_aol           = $row['aol'];
    $contact_yahoo         = $row['yahoo'];
    $contact_msn           = $row['msn'];
    $contact_interests     = $row['interests'];
    $contact_custom1       = $row['contact_custom1'];
    $contact_custom2       = $row['contact_custom2'];
    $contact_custom3       = $row['contact_custom3'];
    $contact_custom4       = $row['contact_custom4'];
    $contact_profile       = $row['contact_profile'];

    //address info
    $address_name               = $row['address_name'];
    $address_line1              = $row['line1'];
    $address_line2              = $row['line2'];
    $address_city               = $row['city'];
    $address_state              = $row['state'];
    $address_postal_code        = $row['postal_code'];
    $address_country            = $row['country'];
    $address_body               = $row['address_body'];
    $address_use_pretty_address = $row['use_pretty_address'];

/**
 * $Log: import-template-default.php,v $
 * Revision 1.1  2004/04/19 14:21:54  braverock
 * - add additional look-ups and tests on import
 * - improve error reporting
 * - revise process to use templates
 *   - makes use of material from SF patch 926925 by Glenn Powers
 *
 */
?>