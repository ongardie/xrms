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

    $null                = $row['public/private'];
    $null                = $row['record_manager'];
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
    $null                = $row['address_3'];
    $address_city        = $row['city'];
    $address_state       = $row['state'];
    $address_postal_code = $row['zip'];
    $null                = $row['country'];
    $null                = $row['id/status'];
    $company_phone       = $row['phone'];
    $contact_work_phone  = $row['phone'];
    $company_fax         = $row['fax'];
    $contact_fax         = $row['fax'];
    $contact_home_phone  = $row['home_phone'];
    $null                = $row['mobile_phone'];
    $null                = $row['pager'];
    $null                = $row['salutation'];
    $null                = $row['last_meeting'];
    $null                = $row['last_reach'];
    $null                = $row['last_attempt'];
    $null                = $row['letter_date'];
    $null                = $row['title'];
    $null                = $row['assistant'];
    $null                = $row['last_results'];
    $null                = $row['referred_by'];
    $contact_custom1     = $row['user_1'];
    $contact_custom2     = $row['user_2'];
    $contact_custom3     = $row['user_3'];
    $contact_custom4     = $row['user_4'];
    $null                = $row['user_5'];
    $null                = $row['user_6'];
    $null                = $row['user_7'];
    $null                = $row['user_8'];
    $null                = $row['user_9'];
    $null                = $row['user_10'];
    $null                = $row['user_11'];
    $null                = $row['user_12'];
    $null                = $row['user_13'];
    $null                = $row['user_14'];
    $null                = $row['user_15'];
    $null                = $row['home_address_1'];
    $null                = $row['home_address_2'];
    $null                = $row['home_city'];
    $null                = $row['home_state'];
    $null                = $row['home_zip'];
    $null                = $row['home_country'];
    $company_phone2      = $row['alt_phone'];
    $null                = $row['2nd_contact'];
    $null                = $row['2nd_title'];
    $null                = $row['2nd_phone'];
    $null                = $row['3rd_contact'];
    $null                = $row['3rd_title'];
    $null                = $row['3rd_phone'];
    $null                = $row['phone_ext.'];
    $null                = $row['fax_ext.'];
    $null                = $row['alt_phone_ext.'];
    $null                = $row['2nd_phone_ext.'];
    $null                = $row['3rd_phone_ext.'];
    $null                = $row['asst._title'];
    $null                = $row['asst._phone'];
    $null                = $row['asst._phone_ext.'];
    $division_name       = $row['department'];
    $null                = $row['spouse'];
    $null                = $row['record_creator'];
    $null                = $row['owner'];
    $null                = $row['2nd_last_reach'];
    $null                = $row['3rd_last_reach'];
    $company_website     = $row['web_site'];
    $null                = $row['ticker_symbol'];
    $null                = $row['create_date'];
    $null                = $row['edit_date'];
    $null                = $row['merge_date'];
    $contact_email       = $row['e-mail_login'];
    $null                = $row['e-mail_system'];

/**
 * $Log: import-template-act2000.php,v $
 * Revision 1.1  2004/04/19 14:21:54  braverock
 * - add additional look-ups and tests on import
 * - improve error reporting
 * - revise process to use templates
 *   - makes use of material from SF patch 926925 by Glenn Powers
 *
 */
?>