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
    // Clear Profiles
    $company_profile    = "";
    $contact_profile    = "";

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
    if ($row['middle_name']) {
        $contact_profile             .= "Middle Name: " . $row['middle_name'] . "\n";
    }
    $contact_last_name   = $row['last_name'];
    if ($row['suffix']) {
        $contact_profile            .= "Suffix: " . $row['suffix'] . "\n";
    }
    $gender              = $row['gender'];
    $company_name        = $row['company'];
    $legal_name          = $company_name;
    $contact_division    = $row['department'];
    $contact_title       = $row['job_title'];
    $address_line1       = $row['business_street'];
    $address_line2       = $row['business_street_2'];
    if ($row['business_street_3']) {
        $company_profile            .= "Business Street 3: " . $row['business_street_3'] . "\n";
    }
    $address_city        = $row['business_city'];
    $address_state       = $row['business_state'];
    $address_postal_code = $row['business_postal_code'];
    $address_country     = $row['business_country'];
    if ($row['home_street']) {
        $contact_profile            .= "Home Street: " . $row['home_street'] . "\n";
    }
    if ($row['home_street_2']) {
        $contact_profile            .= "Home Street 2: " . $row['home_street_2'] . "\n";
    }
    if ($row['home_street_3']) {
        $contact_profile            .= "Home Street 3: " . $row['home_street_3'] . "\n";
    }
    if ($row['home_city']) {
        $contact_profile            .= "Home City: " . $row['home_city'] . "\n";
    }
    if ($row['home_state']) {
        $contact_profile            .= "Home State: " . $row['home_state'] . "\n";
    }
    if ($row['home_postal_code']) {
        $contact_profile            .= "Home Postal Code: " . $row['home_postal_code'] . "\n";
    }
    if ($row['home_country']) {
        $contact_profile            .= "Home Country: " . $row['home_country'] . "\n";
    }
    if ($row['other_street']) {
        $contact_profile            .= "Other Street: " . $row['other_street'] . "\n";
    }
    if ($row['other_street_2']) {
        $contact_profile            .= "Other Street 2: " . $row['other_street_2'] . "\n";
    }
    if ($row['other_street_3']) {
        $contact_profile            .= "Other Street 3: " . $row['other_street_3'] . "\n";
    }
    if ($row['other_city']) {
        $contact_profile            .= "Other City: " . $row['other_city'] . "\n";
    }
    if ($row['other_state']) {
        $contact_profile            .= "Other State: " . $row['other_state'] . "\n";
    }
    if ($row['other_postal_code']) {
        $contact_profile            .= "Other Postal Code: " . $row['other_postal_code'] . "\n";
    }
    if ($row['other_country']) {
        $contact_profile            .= "Other Country: " . $row['other_country'] . "\n";
    }
    if ($row['assistant_s_phone']) {
        $contact_profile            .= "Assistant's Phone: " . $row['assistant_s_phone'] . "\n";
    }
    $company_fax             = $row['business_fax'];
    $company_phone           = $row['company_main_phone'];
    if(!strlen($company_phone)){
        $company_phone       = $row['business_phone'];
    }
    $company_phone2          = $row['business_phone_2'];
    if ($row['callback']) {
        $contact_profile    .= "Callback: " . $row['callback'] . "\n";
    }
    $contact_cell_phone      = $row['mobile_phone'];
    if (!strlen($contact_cell_phone)) {
        $contact_cell_phone  = $row['car_phone'];
    }
    $contact_work_phone      = $row['business_phone'];
    if (!strlen($contact_work_phone)){
        $contact_work_phone  = $row['company_main_phone'];
    }
    $contact_fax             = $row['home_fax'];
    $contact_home_phone      = $row['home_phone'];
    if ($row['home_phone_2']) {
        $contact_profile            .= "Home Phone 2: " . $row['home_phone_2'] . "\n";
    }
    if ($row['isdn']) {
        $contact_profile            .= "ISDN: " . $row['isdn'] . "\n";
    }
    if ($row['other_fax']) {
        $contact_profile            .= "Other FAX: " . $row['other_fax'] . "\n";
    }
    if ($row['other_phone']) {
        $contact_profile            .= "Other Phone: " . $row['other_phone'] . "\n";
    }
    if ($row['pager']) {
        $contact_profile            .= "Pager: " . $row['pager'] . "\n";
    }
    if ($row['primary_phone']) {
        $contact_profile            .= "Primary Phone: " . $row['primary_phone'] . "\n";
    }
    if ($row['radio_phone']) {
        $contact_profile            .= "Radio Phone: " . $row['radio_phone'] . "\n";
    }
    if ($row['tty/tdd_phone']) {
        $contact_profile            .= "TTY/TDD Phone: " . $row['tty/tdd_phone'] . "\n";
    }
    if ($row['telex']) {
        $contact_profile            .= "Telex: " . $row['telex'] . "\n";
    }
    $company_code        = $row['account'] . "\n";
    if ($row['anniversary'] != "0/0/00") {
        $contact_profile            .= "Anniversary: " . $row['anniversary'] . "\n";
    }
    if ($row['assistant_s_name']) {
        $contact_profile            .= "Assistant's Name: " . $row['assistant_s_name'] . "\n";
    }
    if ($row['billing_information']) {
        $contact_profile            .= "Billing Information: " . $row['billing_information'] . "\n";
    }
    $contact_date_of_birth    = $row['birthday'];
    if ($row['business_address_po_box']) {
        $contact_profile            .= "Business Address P.O. Box: " . $row['business_address_po_box'] . "\n";
    }
    if ($row['categories']) {
        $contact_profile            .= "Catagories: " . $row['categories'] . "\n";
    }
    if ($row['children']) {
        $contact_profile            .= "Children: " . $row['children'] . "\n";
    }
    if ($row['directory_server']) {
        $contact_profile            .= "Directory Server: " . $row['directory_server'] . "\n";
    }
    $contact_email       = $row['e-mail_address'];
    if ($row['e-mail_type']) {
        $contact_profile            .= "E-Mail Type: " . $row['e-mail_type'] . "\n";
    }
    if ($row['e-mail_display_name']) {
        $contact_profile            .= "E-Mail Display Name: " . $row['e-mail_display_name'] . "\n";
    }
    if ($row['e-mail_2_address']) {
        $contact_profile            .= "E-Mail 2 Address: " . $row['e-mail_2_address'] . "\n";
    }
    if ($row['e-mail_2_type']) {
        $contact_profile            .= "E-Mail 2 Type: " . $row['e-mail_2_type'] . "\n";
    }
    if ($row['e-mail_2_display_name']) {
        $contact_profile            .= "E-Mail 2 Display Name: " . $row['e-mail_2_display_name'] . "\n";
    }
    if ($row['e-mail_3_address']) {
        $contact_profile            .= "E-Mail 3 Address: " . $row['e-mail_3_address'] . "\n";
    }
    if ($row['e-mail_3_type']) {
        $contact_profile            .= "E-Mail 3 Type: " . $row['e-mail_3_type'] . "\n";
    }
    if ($row['e-mail_3_display_name']) {
        $contact_profile            .= "E-Mail 3 Display Name: " . $row['e-mail_3_display_name'] . "\n";
    }
    if ($row['gender'] != "Unspecified") {
        $contact_profile            .= "Gender: " . $row['gender'] . "\n";
    }
    if ($row['government_id_number']) {
        $contact_profile            .= "Government ID Number: " . $row['government_id_number'] . "\n";
    }
    $contact_interests        = $row['hobby'];
    if ($row['home_address_po_box']) {
        $contact_profile            .= "Home Address P.O. Box: " . $row['home_address_po_box'] . "\n";
    }
    if ($row['initials']) {
        $contact_profile            .= "Initials: " . $row['initials'] . "\n";
    }
    if ($row['internet_free_busy']) {
        $contact_profile            .= "Internet Free/Busy: " . $row['internet_free_busy'] . "\n";
    }
    if ($row['keywords']) {
        $contact_profile            .= "Keywords: " . $row['keywords'] . "\n";
    }
    if ($row['language']) {
        $contact_profile            .= "Language: " . $row['language'] . "\n";
    }
    if ($row['location']) {
        $contact_profile            .= "Location: " . $row['location'] . "\n";
    }
    if ($row['manager_s_name']) {
        $contact_profile            .= "Manager's Name: " . $row['manager_s_name'] . "\n";
    }
    if ($row['mileage']) {
        $contact_profile            .= "Mileage: " . $row['mileage'] . "\n";
    }
    $contact_profile        = $row['notes'];
    if ($row['office_location']) {
        $contact_profile            .= "Office Location: " . $row['office_location'] . "\n";
    }
    if ($row['organizational_id_number']) {
        $contact_profile            .= "Organizational ID Number: " . $row['organizational_id_number'] . "\n";
    }
    if ($row['other_address_po_box']) {
        $contact_profile            .= "Other Address P.O. Box: " . $row['other_address_po_box'] . "\n";
    }
    if ($row['priority'] != "Normal") {
        $contact_profile            .= "Priority: " . $row['priority'] . "\n";
    }
    if ($row['private'] != "False") {
        $contact_profile            .= "Private: " . $row['private'] . "\n";
    }
    if ($row['profession']) {
        $contact_profile            .= "Profession: " . $row['profession'] . "\n";
    }
    if ($row['referred_by']) {
        $company_profile            .= "Referred By: " . $row['referred_by'] . "\n";
    }
    if ($row['sensitivity'] != "Normal") {
        $contact_profile            .= "Sensitivity: " . $row['sensitivity'] . "\n";
    }
    if ($row['spouse']) {
        $contact_profile            .= "Spouse: " . $row['spouse'] . "\n";
    }
    $contact_custom1        = $row['user_1'];
    $contact_custom2        = $row['user_2'];
    $contact_custom3        = $row['user_3'];
    $contact_custom4        = $row['user_4'];
    $company_website        = $row['web_page'];

/**
 * $Log: import-template-outlook.php,v $
 * Revision 1.4  2004/07/10 11:51:06  braverock
 * - cleaned up assignments for outlook phone import
 *   - resolves SF bug 951241 based on contributor suggestions
 *
 * Revision 1.3  2004/07/07 22:18:33  braverock
 * - minor improvements to import process
 *
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