<?php
/**
 * Import Template for XRMS - ACT 2000 Import Template
 *
 * These template files exist to define row to field mappings for
 * importing company and contact data into XRMS.
 *
 * Act 2000 Import Template should match standard export from Act!
 *
 * unmatched variables are assigned to $null
 *
 * @author Glenn Powers
 *
 * Copyright (c) 2004 XRMS Development Team
 */

    // clear $company_profile
    $company_profile = "";

    //company info
    $company_taxid       = $row['tax_id'];
    $extref1             = $row['extref1'];
    $extref2             = $row['extref2'];
    $extref3             = $row['extref3'];
    $employees           = $row['employees'];
    $revenue             = $row['revenue'];
    $credit_limit        = $row['credit_limit'];
    $terms               = $row['terms'];
    $profile             = $row['profile'];

    //address info
    $address_name        = $row['address_name'];

   if ($row['public/private']) {
      $company_profile        .= "Public/Private: " . $row['public/private'] . "\n";
   }
   if ($row['record_manager']) {
      $company_profile        .= "Record Manager: " . $row['record_manager'] . "\n";
   }
    $company_name        = $row['company'];
    $legal_name          = $row['company'];

    $contact_first_names = $row['first_name'];
    $contact_last_name   = $row['last_name'];
    if (!(strlen($contact_first_names) > 0)) {
        preg_match("/([^\ ]+)( )(.*)/i",$row['contact'],$contact_split);
        $contact_first_names = $contact_split[1];
        $contact_last_name   = $contact_split[3];
    }
    $address_line1       = $row['address_1'];
    $address_line2       = $row['address_2'];
   if ($row['address_3']) {
      $company_profile        .= "Address 3: " . $row['address_3'] . "\n";
   }
    $address_city        = $row['city'];
    $address_state       = $row['state'];
    $address_postal_code = $row['zip'];
   $address_country    = $row['country'];
   if ($row['id/status']) {
      $company_profile        .= "ID/Status: " . $row['id/status'] . "\n";
   }
    $company_phone       = $row['phone'];
    $contact_work_phone  = $row['phone'];
    $company_fax         = $row['fax'];
    $contact_fax         = $row['fax'];
    $contact_home_phone  = $row['home_phone'];
   $contact_cell_phone    = $row['mobile_phone'];
   if ($row['pager']) {
      $company_profile        .= "Pager: " . $row['pager'] . "\n";
   }
   $contact_salutation    = $row['salutation'];
   if ($row['last_meeting']) {
      $company_profile        .= "Last Meeting: " . $row['last_meeting'] . "\n";
   }
   if ($row['last_reach']) {
      $company_profile        .= "Last Reach: " . $row['last_reach'] . "\n";
   }
   if ($row['last_attempt']) {
      $company_profile        .= "Last Attempt: " . $row['last_attempt'] . "\n";
   }
   if ($row['letter_date']) {
      $company_profile        .= "Letter Date: " . $row['letter_date'] . "\n";
   }
   $contact_title    = $row['title'];
   if ($row['assistant']) {
      $company_profile        .= "Assistant: " . $row['assistant'] . "\n";
   }
   if ($row['last_results']) {
      $company_profile        .= "Last Results: " . $row['last_results'] . "\n";
   }
   if ($row['referred_by']) {
      $company_profile        .= "Referred By: " . $row['referred_by'] . "\n";
   }
    $contact_custom1     = $row['user_1'];
    $contact_custom2     = $row['user_2'];
    $contact_custom3     = $row['user_3'];
    $contact_custom4     = $row['user_4'];
   if ($row['user_5']) {
      $company_profile        .= "User 5: " . $row['user_5'] . "\n";
   }
   if ($row['user_6']) {
      $company_profile        .= "User 6: " . $row['user_6'] . "\n";
   }
   if ($row['user_7']) {
      $company_profile        .= "User 7: " . $row['user_7'] . "\n";
   }
   if ($row['user_8']) {
      $company_profile        .= "User 8: " . $row['user_8'] . "\n";
   }
   if ($row['user_9']) {
      $company_profile        .= "User 9: " . $row['user_9'] . "\n";
   }
   if ($row['user_10']) {
      $company_profile        .= "User 10: " . $row['user_10'] . "\n";
   }
   if ($row['user_11']) {
      $company_profile        .= "User 11: " . $row['user_11'] . "\n";
   }
   if ($row['user_12']) {
      $company_profile        .= "User 12: " . $row['user_12'] . "\n";
   }
   if ($row['user_13']) {
      $company_profile        .= "User 13: " . $row['user_13'] . "\n";
   }
   if ($row['user_14']) {
      $company_profile        .= "User 14: " . $row['user_14'] . "\n";
   }
   if ($row['user_15']) {
      $company_profile        .= "User 15: " . $row['user_15'] . "\n";
   }
   if ($row['home_address_1']) {
      $company_profile        .= "Home Address 1: " . $row['home_address_1'] . "\n";
   }
   if ($row['home_address_2']) {
      $company_profile        .= "Home Address 2: " . $row['home_address_2'] . "\n";
   }
   if ($row['home_city']) {
      $company_profile        .= "Home City: " . $row['home_city'] . "\n";
   }
   if ($row['home_state']) {
      $company_profile        .= "Home State: " . $row['home_state'] . "\n";
   }
   if ($row['home_zip']) {
      $company_profile        .= "Home Zip: " . $row['home_zip'] . "\n";
   }
   if ($row['home_country']) {
      $company_profile        .= "Home Country: " . $row['home_country'] . "\n";
   }
    $company_phone2      = $row['alt_phone'];
   if ($row['2nd_contact']) {
      $company_profile        .= "2nd Contact: " . $row['2nd_contact'] . "\n";
   }
   if ($row['2nd_title']) {
      $company_profile        .= "2nd Title: " . $row['2nd_title'] . "\n";
   }
   if ($row['2nd_phone']) {
      $company_profile        .= "2nd Phone: " . $row['2nd_phone'] . "\n";
   }
   if ($row['3rd_contact']) {
      $company_profile        .= "3rd Contact: " . $row['3rd_contact'] . "\n";
   }
   if ($row['3rd_title']) {
      $company_profile        .= "3rd Title: " . $row['3rd_title'] . "\n";
   }
   if ($row['3rd_phone']) {
      $company_profile        .= "3rd Phone: " . $row['3rd_phone'] . "\n";
   }
   if ($row['first_name']) {
      $company_profile        .= "First Name: " . $row['first_name'] . "\n";
   }
   if ($row['last_name']) {
      $company_profile        .= "Last Name: " . $row['last_name'] . "\n";
   }
   if ($row['phone_ext']) {
      $company_profile        .= "Phone Ext: " . $row['phone_ext'] . "\n";
   }
   if ($row['fax_ext']) {
      $company_profile        .= "FAX Ext: " . $row['fax_ext'] . "\n";
   }
   if ($row['alt_phone_ext']) {
      $company_profile        .= "Alt Phone Ext: " . $row['alt_phone_ext'] . "\n";
   }
   if ($row['2nd_phone_ext']) {
      $company_profile        .= "2nd Phone Ext: " . $row['2nd_phone_ext'] . "\n";
   }
   if ($row['3rd_phone_ext']) {
      $company_profile        .= "3rd Phone Ext: " . $row['3rd_phone_ext'] . "\n";
   }
   if ($row['asst_title']) {
      $company_profile        .= "Asst. Title: " . $row['asst_title'] . "\n";
   }
   if ($row['asst_phone']) {
      $company_profile        .= "Asst. Phone: " . $row['asst_phone'] . "\n";
   }
   if ($row['asst_phone_ext']) {
      $company_profile        .= "Asst. Phone Ext: " . $row['asst_phone_ext'] . "\n";
   }
    $division_name       = $row['department'];
   if ($row['spouse']) {
      $company_profile        .= "Spouse: " . $row['spouse'] . "\n";
   }
   if ($row['record_creator']) {
      $company_profile        .= "Record Creator: " . $row['record_creator'] . "\n";
   }
   if ($row['owner']) {
      $company_profile        .= "Owner: " . $row['owner'] . "\n";
   }
   if ($row['2nd_last_reach']) {
      $company_profile        .= "2nd Last Reach: " . $row['2nd_last_reach'] . "\n";
   }
   if ($row['3rd_last_reach']) {
      $company_profile        .= "3rd Last Reach: " . $row['3rd_last_reach'] . "\n";
   }
   $company_website    = $row['web_site'];
   if ($row['ticker_symbol']) {
      $company_profile        .= "Ticker Symbol: " . $row['ticker_symbol'] . "\n";
   }
   if ($row['create_date']) {
      $company_profile        .= "Create Date: " . $row['create_date'] . "\n";
   }
   if ($row['edit_date']) {
      $company_profile        .= "Edit Date: " . $row['edit_date'] . "\n";
   }
   if ($row['merge_date']) {
      $company_profile        .= "Merge Date: " . $row['merge_date'] . "\n";
   }
   $contact_email    = $row['email_login'];
   if ($row['email_system']) {
      $company_profile        .= "E-Mail System: " . $row['email_system'] . "\n";
   }

/**
 * $Log: import-template-act2000.php,v $
 * Revision 1.4  2005/01/07 20:00:07  gpowers
 * - cleared $company_profile at start
 *
 * Revision 1.3  2004/09/22 22:08:19  introspectshun
 * - Updated array keys to eliminate periods (.) and dashes (-)
 *   - Now works in conjunction with CSVtoArray fn in utils-misc.php
 *
 * Revision 1.2  2004/05/06 20:04:47  braverock
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
