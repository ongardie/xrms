<?php
/**
 * Import Template for XRMS - SalesForce.com Import Template
 *
 * These template files exist to define row to field mappings for
 * importing company and contact data into XRMS.
 *
 * SalesForce.com Import Template should match standard export from SalesForce.com
 *
 * unmatched variables are assigned to $null
 *
 * @author Glenn Powers
 *
 * Copyright (c) 2004 XRMS Development Team
 */


   // unmatched vars that do not appear to be in the export
   $company_taxid       = $row['tax_id'];
   $extref1             = $row['extref1'];
   $extref2             = $row['extref2'];
   $extref3             = $row['extref3'];
   $custom1             = $row['custom1'];
   $custom2             = $row['custom2'];
   $custom3             = $row['custom3'];
   $custom4             = $row['custom4'];

   $revenue             = $row['revenue'];
   $credit_limit        = $row['credit_limit'];
   $terms               = $row['terms'];
   $profile             = $row['profile'];


   $contact_first_names  = $row['first_name'];
   $contact_last_name    = $row['last_name'];
   $title                = $row['title'];
   $null                 = $row['lead_source'];
   $null                 = $row['last_modified_date'];
   $address_state        = $row['mailing_state'];
   $contact_work_phone   = $row['phone'];
   $company_phone        = $row['phone'];
   $contact_email        = htmlspecialchars($row['email']);
   $company_name         = $row['account_name'];
   $legal_name           = $row['account_name'];
   $null                 = $row['rating'];
   $null                 = $row['industry'];
   $null                 = $row['type'];
   $null                 = $row['contact_owner'];
   $null                 = $row['created_by'];
   $null                 = $row['salutation'];
   $division_name        = $row['department'];
   $null                 = $row['birthdate'];
   $null                 = $row['asst._phone'];
   $null                 = $row['last_activity'];
   $null                 = $row['description'];
   $null                 = $row['reports_to'];
   $null                 = $row['mailing_street'];
   $address_line1        = $row['mailing_address_line1'];
   $address_line2        = $row['mailing_address_line2'];
   $null                 = $row['mailing_address_line3'];
   $address_city         = $row['mailing_city'];
   $address_postal_code  = $row['mailing_zip/postal_code'];
   $address_country      = $row['mailing_country'];
   $null                 = $row['mobile'];
   $contact_home_phone   = $row['home_phone'];
   $company_phone2       = $row['other_phone'];
   $contact_fax          = $row['fax'];
   $company_fax          = $row['fax'];
   $null                 = $row['account_site'];
   $null                 = $row['ticker_symbol'];
   $null                 = $row['annual_revenue'];
   $employees            = $row['employees'];
   $null                 = $row['ownership'];
   $null                 = $row['account_last_activity'];
   $null                 = $row['account_description'];
   $null                 = $row['account_created_date'];
   $null                 = $row['account_last_modified_date'];
   $null                 = $row['billing_street'];
   $null                 = $row['billing_address_line1'];
   $null                 = $row['billing_address_line2'];
   $null                 = $row['billing_address_line3'];
   $null                 = $row['billing_city'];
   $null                 = $row['billing_state'];
   $null                 = $row['billing_zip/postal_code'];
   $null                 = $row['billing_country'];
   $null                 = $row['account_phone'];
   $company_website      = $row['website'];
   $null                 = $row['token_count'];
   $null                 = $row['wireless_devices'];
   $null                 = $row['new_dev/month'];
   $null                 = $row['device_types'];
   $null                 = $row['#_cells_reimbursed'];
   $null                 = $row['radius_support'];
   $null                 = $row['other_authentication_needs'];
   $null                 = $row['contact_owner_alias'];
   $null                 = $row['created_alias'];
   $null                 = $row['last_modified_by'];
   $null                 = $row['last_modified_alias'];
   $null                 = $row['assistant'];
   $null                 = $row['owner_role_display'];
   $null                 = $row['owner_role_name'];
   $null                 = $row['created_date'];
   $null                 = $row['contact_id'];
   $null                 = $row['last_stay-in-touch_request_date'];
   $null                 = $row['last_stay-in-touch_save_date'];
   $null                 = $row['other_street'];
   $null                 = $row['other_address_line_1'];
   $null                 = $row['other_address_line_2'];
   $null                 = $row['other_address_line_3'];
   $null                 = $row['other_city'];
   $null                 = $row['other_state'];
   $null                 = $row['other_zip/postal_code'];
   $null                 = $row['other_country'];
   $null                 = $row['email_opt_out'];
   $null                 = $row['account_owner'];
   $null                 = $row['account_owner_alias'];
   $null                 = $row['account_owner_role_display'];
   $null                 = $row['account_owner_role_name'];
   $null                 = $row['sic_code'];
   $null                 = $row['account_number'];
   $null                 = $row['account_id'];
   $null                 = $row['parent_account'];
   $null                 = $row['parent_account_id'];
   $null                 = $row['shipping_street'];
   $null                 = $row['shipping_address_line1'];
   $null                 = $row['shipping_address_line2'];
   $null                 = $row['shipping_address_line3'];
   $null                 = $row['shipping_city'];
   $null                 = $row['shipping_state'];
   $null                 = $row['shipping_zip/postal_code'];
   $null                 = $row['shipping_country'];
   $null                 = $row['account_fax'];
   $null                 = $row['account_manager'];


/**
 * $Log: import-template-salesforce.php,v $
 * Revision 1.1  2004/04/19 14:21:54  braverock
 * - add additional look-ups and tests on import
 * - improve error reporting
 * - revise process to use templates
 *   - makes use of material from SF patch 926925 by Glenn Powers
 *
 */
?>