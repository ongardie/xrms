<?php
/**
 * Import Template for XRMS - Microsoft Outlook 2000/XP/2003
 *
 * These template files exist to define row to field mappings for
 * importing company and contact data into XRMS.
 *
 * Microsoft Outlook 2000/XP/2003 Import Template should match
 * standard export from MS Outlook
 *
 * unmatched variables are assigned to $null
 *
 * @author Glenn Powers
 *
 * Copyright (c) 2004 XRMS Development Team
 */

    // non-microsoft outlook headers
    // unmatched vars that do not appear to be in the export
    $division_name       = $row['division_name'];
    $company_website     = $row['website'];
    $company_taxid       = $row['tax_id'];
    $extref1             = $row['extref1'];
    $extref2             = $row['extref2'];
    $extref3             = $row['extref3'];
    $employees           = $row['employees'];
    $revenue             = $row['revenue'];
    $credit_limit        = $row['credit_limit'];
    $terms               = $row['terms'];
    $profile             = $row['profile'];

    // microsoft outlook headers
    $title               = $row['title'];
    $contact_first_names = $row['first_name'];
    $null                = $row['middle_name'];
    $contact_last_name   = $row['last_name'];
    $null                = $row['suffix'];
    $company_name        = $row['company'];
    $legal_name          = $company_name;
    $null                = $row['department'];
    $null                = $row['job_title'];
    $address_line1       = $row['business_street'];
    $address_line2       = $row['business_street_2'];
    $null                = $row['business_street_3'];
    $address_city        = $row['business_city'];
    $address_state       = $row['business_state'];
    $address_postal_code = $row['business_postal_code'];
    $null                = $row['business_country'];
    $null                = $row['home_street'];
    $null                = $row['home_street_2'];
    $null                = $row['home_street_3'];
    $null                = $row['home_city'];
    $null                = $row['home_state'];
    $null                = $row['home_postal_code'];
    $null                = $row['home_country'];
    $null                = $row['other_street'];
    $null                = $row['other_street_2'];
    $null                = $row['other_street_3'];
    $null                = $row['other_city'];
    $null                = $row['other_state'];
    $null                = $row['other_postal_code'];
    $null                = $row['other_country'];
    $null                = $row['assistant_s_phone'];
    $company_fax         = $row['business_fax'];
    $company_phone       = $row['business_phone'];
    $company_phone2      = $row['business_phone_2'];
    $null                = $row['callback'];
    $null                = $row['car_phone'];
    $contact_work_phone  = $row['company_main_phone'];
    $contact_fax         = $row['home_fax'];
    $contact_home_phone  = $row['home_phone'];
    $null                = $row['home_phone_2'];
    $null                = $row['isdn'];
    $null                = $row['mobile_phone'];
    $null                = $row['other_fax'];
    $null                = $row['other_phone'];
    $null                = $row['pager'];
    $null                = $row['primary_phone'];
    $null                = $row['radio_phone'];
    $null                = $row['tty/tdd_phone'];
    $null                = $row['telex'];
    $null                = $row['account'];
    $null                = $row['anniversary'];
    $null                = $row['assistant_s_name'];
    $null                = $row['billing_information'];
    $null                = $row['birthday'];
    $null                = $row['business_address_po_box'];
    $null                = $row['categories'];
    $null                = $row['children'];
    $null                = $row['directory_server'];
    $contact_email       = $row['e-mail_address'];
    $null                = $row['e-mail_type'];
    $null                = $row['e-mail_display_name'];
    $null                = $row['e-mail_2_address'];
    $null                = $row['e-mail_2_type'];
    $null                = $row['e-mail_2_display_name'];
    $null                = $row['e-mail_3_address'];
    $null                = $row['e-mail_3_type'];
    $null                = $row['e-mail_3_display_name'];
    $null                = $row['gender'];
    $null                = $row['government_id_number'];
    $null                = $row['hobby'];
    $null                = $row['home_address_po_box'];
    $null                = $row['initials'];
    $null                = $row['internet_free_busy'];
    $null                = $row['keywords'];
    $null                = $row['language'];
    $null                = $row['location'];
    $null                = $row['manager_s_name'];
    $null                = $row['mileage'];
    $null                = $row['notes'];
    $null                = $row['office_location'];
    $null                = $row['organizational_id_number'];
    $null                = $row['other_address_po_box'];
    $null                = $row['priority'];
    $null                = $row['private'];
    $null                = $row['profession'];
    $null                = $row['referred_by'];
    $null                = $row['sensitivity'];
    $null                = $row['spouse'];
    $contact_custom1     = $row['user_1'];
    $contact_custom2     = $row['user_2'];
    $contact_custom3     = $row['user_3'];
    $contact_custom4     = $row['user_4'];
    $null                = $row['web_page'];

/**
 * $Log: import-template-outlook.php,v $
 * Revision 1.1  2004/04/19 14:21:54  braverock
 * - add additional look-ups and tests on import
 * - improve error reporting
 * - revise process to use templates
 *   - makes use of material from SF patch 926925 by Glenn Powers
 *
 */
?>