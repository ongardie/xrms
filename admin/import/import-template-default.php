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
    if(!strlen($company_name)) {
        $company_name    = $row['company'];
    }
    $legal_name          = $row['legal_name'];
    $division_name       = $row['division_name'];
    if (!strlen($division_name)) {
        $division_name   = $row['division'];
    }
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
    $company_fax         = $row['company_fax'];

    //contact info
    $contact_first_names   = $row['first_names'];
    $contact_last_name     = $row['last_name'];
    $contact_email         = htmlspecialchars($row['email']);
    $contact_work_phone    = $row['work_phone'];
    $contact_home_phone    = $row['home_phone'];
    $contact_fax           = $row['contact_fax'];
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
    $gender                = $row['gender'];

    //address info
    $address_name               = $row['address_name'];
    $address_line1              = $row['line1'];
    if (!strlen($address_line1)) {
        $address_line1          = $row['street'];
    }
    $address_line2              = $row['line2'];
    $address_city               = $row['city'];
    $address_state              = $row['state'];
    if(!strlen($address_state)) {
        $address_state          = $row['province'];
    }
    $address_postal_code        = $row['postal_code'];
    $address_country            = $row['country'];
    $address_body               = $row['address_body'];
    $address_use_pretty_address = $row['use_pretty_address'];

/**
 * $Log: import-template-default.php,v $
 * Revision 1.4  2005/09/06 16:00:46  braverock
 * - patch typo in first_names.
 *   credit Bert (SF:camel2004) for the patch
 *
 * Revision 1.3  2005/04/12 13:13:47  niclowe
 * Fixed  bug[ 1180292 ] import of fax fails (ambiguous company and contact fax fields)
 *
 * Revision 1.2  2004/07/07 22:18:33  braverock
 * - minor improvements to import process
 *
 * Revision 1.1  2004/04/19 14:21:54  braverock
 * - add additional look-ups and tests on import
 * - improve error reporting
 * - revise process to use templates
 *   - makes use of material from SF patch 926925 by Glenn Powers
 *
 */
?>