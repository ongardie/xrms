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

    // Clear Profiles
    $company_profile    = "";
    $contact_profile    = "";

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
   $contact_title        = $row['title'];
   if ($row['lead_source']) {
      $company_profile            .= "Lead Source: " . $row['lead_source'] . "\n";
   }
   if ($row['last_modified_date']) {
      $company_profile            .= "Last Modify Date: " . $row['last_modified_date'] . "\n";
   }
   $address_state        = $row['mailing_state'];
   $contact_work_phone   = $row['phone'];
   $company_phone        = $row['phone'];
   $contact_email        = htmlspecialchars($row['email']);
   $company_name         = $row['account_name'];
   $legal_name           = $row['account_name'];
   if ($row['rating']) {
       $company_profile            .= "Rating: " . $row['rating'] . "\n";
   }
   if ($row['industry']) {
       $company_profile            .= "Industry: " . $row['industry'] . "\n";
   }
   if ($row['type']) {
       $company_profile            .= "Type: " . $row['type'] . "\n";
   }
   if ($row['contact_owner']) {
       $company_profile            .= "Contact Owner: " . $row['contact_owner'] . "\n";
   }
   if ($row['created_by']) {
       $company_profile            .= "Created By: " . $row['created_by'] . "\n";
   }
   $contact_salutation        = $row['salutation'];
   $division_name        = $row['department'];
   $contact_date_of_birth    = $row['birthdate'];
   if ($row['asst._phone']) {
       $contact_profile            .= "Asst. Phone: " . $row['asst._phone'] . "\n";
   }
   if ($row['last_activity']) {
       $company_profile            .= "Last Activity: " . $row['last_activity'] . "\n";
   }
   if ($row['description']) {
       $company_profile            .= "Description: " . $row['description'] . "\n";
   }
   if ($row['reports_to']) {
       $contact_profile            .= "Reports To: " . $row['reports_to'] . "\n";
   }
   if ($row['mailing_street']) {
       $company_profile            .= "Mailing Street: " . $row['mailing_street'] . "\n";
   }
   $address_line1        = $row['mailing_address_line1'];
   $address_line2        = $row['mailing_address_line2'];
   if ($row['mailing_address_line3']) {
      $company_profile            .= "Mailing Address Line 3:" . $row['mailing_address_line3'] . "\n";
   }
   $address_city         = $row['mailing_city'];
   $address_postal_code  = $row['mailing_zip/postal_code'];
   if ($row['mailing_country']) {
      $company_profile            .= "Mailing Country: " . $row['mailing_country'] . "\n";
   }
   if ($row['mobile']) {
      $contact_cell_phone  .= $row['mobile'];
   }
   $contact_home_phone   = $row['home_phone'];
   $company_phone2       = $row['other_phone'];
   $contact_fax          = $row['fax'];
   $company_fax          = $row['fax'];
   if ($row['account_site']) {
      $company_profile            .= "Account Site: " . $row['account_site'] . "\n";
   }
   if ($row['ticker_symbol']) {
      $company_profile            .= "Ticker Symbol: " . $row['ticker_symbol'] . "\n";
   }
   $revenue                .= $row['annual_revenue'];
   $employees            = $row['employees'];
   if ($row['ownership']) {
      $company_profile            .= "Ownership: " . $row['ownership'] . "\n";
   }
   if ($row['account_last_activity']) {
      $company_profile            .= "Account Last Activity: " . $row['account_last_activity'] . "\n";
   }
   if ($row['account_description']) {
      $company_profile            .= "Account Description: " . $row['account_description'] . "\n";
   }
   if ($row['account_created_date']) {
      $company_profile            .= "Account Created Date: " . $row['account_created_date'] . "\n";
   }
   if ($row['account_last_modified_date']) {
      $company_profile            .= "Account Last Modified Date: " . $row['account_last_modified_date'] . "\n";
   }
   if ($row['billing_street']) {
      $company_profile            .= "Billing Street: " . $row['billing_street'] . "\n";
   }
   if ($row['billing_address_line1']) {
      $company_profile            .= "Billing Address Line 1: " . $row['billing_address_line1'] . "\n";
   }
   if ($row['billing_address_line2']) {
      $company_profile            .= "Billing Address Line 2: " . $row['billing_address_line2'] . "\n";
   }
   if ($row['billing_address_line3']) {
      $company_profile            .= "Billing Address Line 3: " . $row['billing_address_line3'] . "\n";
   }
   if ($row['billing_city']) {
      $company_profile            .= "Billing City: " . $row['billing_city'] . "\n";
   }
   if ($row['billing_state']) {
      $company_profile            .= "Billing State: " . $row['billing_state'] . "\n";
   }
   if ($row['billing_zip/postal_code']) {
      $company_profile            .= "Billing Postal Code: " . $row['billing_zip/postal_code'] . "\n";
   }
   if ($row['billing_country']) {
      $company_profile            .= "Billing Country: " . $row['billing_country'] . "\n";
   }
   if ($row['account_phone']) {
      $company_profile            .= "Account Phone: " . $row['account_phone'] . "\n";
   }
   $company_website        = $row['website'];
   if ($row['token_count']) {
      $company_profile            .= "Token Count: " . $row['token_count'] . "\n";
   }
   if ($row['wireless_devices']) {
      $company_profile            .= "Wireless Devices: " . $row['wireless_devices'] . "\n";
   }
   if ($row['new_dev/month']) {
      $company_profile            .= "New Dev/Month: " . $row['new_dev/month'] . "\n";
   }
   if ($row['device_types']) {
      $company_profile            .= "Device Types: " . $row['device_types'] . "\n";
   }
   if ($row['#_cells_reimbursed']) {
      $company_profile            .= "# Cells Reimbursed: " . $row['#_cells_reimbursed'] . "\n";
   }
   if ($row['radius_support']) {
      $company_profile            .= "Radius Support: " . $row['radius_support'] . "\n";
   }
   if ($row['other_authentication_needs']) {
      $company_profile            .= "Other Authentication Needs: " . $row['other_authentication_needs'] . "\n";
   }
   if ($row['contact_owner_alias']) {
      $company_profile            .= "Contact Owner Alias: " . $row['contact_owner_alias'] . "\n";
   }
   if ($row['created_alias']) {
      $company_profile            .= "Created Alias: " . $row['created_alias'] . "\n";
   }
   if ($row['last_modified_by']) {
      $company_profile            .= "Last Modified By: " . $row['last_modified_by'] . "\n";
   }
   if ($row['last_modified_alias']) {
      $company_profile            .= "Last Modified Alias: " . $row['last_modified_alias'] . "\n";
   }
   if ($row['assistant']) {
      $company_profile            .= "Assistant: " . $row['assistant'] . "\n";
   }
   if ($row['owner_role_display']) {
      $company_profile            .= "Owner Role Display: " . $row['owner_role_display'] . "\n";
   }
   if ($row['owner_role_name']) {
      $company_profile            .= "Owner Role Name: " . $row['owner_role_name'] . "\n";
   }
   if ($row['created_date']) {
      $company_profile            .= "Created Date: " . $row['created_date'] . "\n";
   }
   if ($row['contact_id']) {
      $company_profile            .= "Contact ID: " . $row['contact_id'] . "\n";
   }
   if ($row['last_stay-in-touch_request_date']) {
      $company_profile            .= "Last Stay-In-Touch Request Date: " . $row['last_stay-in-touch_request_date'] . "\n";
   }
   if ($row['last_stay-in-touch_save_date']) {
      $company_profile            .= "Last Stay-In-Touch Save Date: " . $row['last_stay-in-touch_save_date'] . "\n";
   }
   if ($row['other_street']) {
      $company_profile            .= "Other Street: " . $row['other_street'] . "\n";
   }
   if ($row['other_address_line_1']) {
      $company_profile            .= "Other Address Line 1: " . $row['other_address_line_1'] . "\n";
   }
   if ($row['other_address_line_2']) {
      $company_profile            .= "Other Address Line 2: " . $row['other_address_line_2'] . "\n";
   }
   if ($row['other_address_line_3']) {
      $company_profile            .= "Other Address Line 3: " . $row['other_address_line_3'] . "\n";
   }
   if ($row['other_city']) {
      $company_profile            .= "Other City: " . $row['other_city'] . "\n";
   }
   if ($row['other_state']) {
      $company_profile            .= "Other State: " . $row['other_state'] . "\n";
   }
   if ($row['other_zip/postal_code']) {
      $company_profile            .= "Other Postal Code: " . $row['other_zip/postal_code'] . "\n";
   }
   if ($row['other_country']) {
      $company_profile            .= "Other Country: " . $row['other_country'] . "\n";
   }
   if ($row['email_opt_out']) {
      $company_profile            .= "Email Opt Out: " . $row['email_opt_out'] . "\n";
   }
   if ($row['account_owner']) {
      $company_profile            .= "Account Owner: " . $row['account_owner'] . "\n";
   }
   if ($row['account_owner_alias']) {
      $company_profile            .= "Account Owner Alias" . $row['account_owner_alias'] . "\n";
   }
   if ($row['account_owner_role_display']) {
      $company_profile            .= "Account Owner Role Display: " . $row['account_owner_role_display'] . "\n";
   }
   if ($row['account_owner_role_name']) {
      $company_profile            .= "Account Owner Role Name: " . $row['account_owner_role_name'] . "\n";
   }
   if ($row['sic_code']) {
      $company_profile            .= "SIC Code: " . $row['sic_code'] . "\n";
   }
   $company_code        = $row['account_number'];
   if ($row['account_id']) {
      $company_profile            .= "Account ID: " . $row['account_id'] . "\n";
   }
   if ($row['parent_account']) {
      $company_profile            .= "Parent Account: " . $row['parent_account'] . "\n";
   }
   if ($row['parent_account_id']) {
      $company_profile            .= "Parent Account ID: " . $row['parent_account_id'] . "\n";
   }
   if ($row['shipping_street']) {
      $company_profile            .= "Shipping Street: " . $row['shipping_street'] . "\n";
   }
   if ($row['shipping_address_line1']) {
      $company_profile            .= "Shipping Address Line 1: " . $row['shipping_address_line1'] . "\n";
   }
   if ($row['shipping_address_line2']) {
      $company_profile            .= "Shipping Address Line 2: " . $row['shipping_address_line2'] . "\n";
   }
   if ($row['shipping_address_line3']) {
      $company_profile            .= "Shipping Address Line 3: " . $row['shipping_address_line3'] . "\n";
   }
   if ($row['shipping_city']) {
      $company_profile            .= "Shipping Ciry: " . $row['shipping_city'] . "\n";
   }
   if ($row['shipping_state']) {
      $company_profile            .= "Shipping State: " . $row['shipping_state'] . "\n";
   }
   if ($row['shipping_zip/postal_code']) {
      $company_profile            .= "Shipping Postal Code: " . $row['shipping_zip/postal_code'] . "\n";
   }
   if ($row['shipping_country']) {
      $company_profile            .= "Shipping Country: " . $row['shipping_country'] . "\n";
   }
   $contact_fax            = $row['account_fax'];
   if ($row['']) {
      $company_profile            .= "Account Manager: " . $row['account_manager'] . "\n";
   }


/**
 * $Log: import-template-salesforce.php,v $
 * Revision 1.2  2004/05/06 20:04:48  braverock
 * - update templates to capture more fields
 *   - makes use of material from SF patch 938836 by Glenn Powers
 *
 * Revision 1.1  2004/04/19 14:21:54  braverock
 * - add additional look-ups and tests on import
 * - improve error reporting
 * - revise process to use templates
 *   - makes use of material from SF patch 926925 by Glenn Powers
 *
 */
?>