<?php
/**
 * Import Template for XRMS - GoldMine v6.50 Import Template
 *
 * These template files exist to define row to field mappings for
 * importing company and contact data into XRMS.
 *
 * GoldMine v6.50 Import Template should match standard export from Goldmine
 *
 * unmatched variables are assigned to $null
 *
 * @author Glenn Powers
 *
 * Copyright (c) 2004 XRMS Development Team
 */

    // unmatched vars that do not appear to be in the export
    $division_name       = $row['division_name'];
    $company_taxid       = $row['tax_id'];
    $extref1             = $row['extref1'];
    $extref2             = $row['extref2'];
    $extref3             = $row['extref3'];
    $employees           = $row['employees'];
    $revenue             = $row['revenue'];
    $credit_limit        = $row['credit_limit'];
    $terms               = $row['terms'];
    $profile             = $row['profile'];

    //contact info
    $contact_home_phone  = $row['home_phone'];

    //address info
    $address_name        = $row['address_name'];

   $null                = $row['accountno'];
   $null                = $row['actionon'];
   $address_line1       = $row['address1'];
   $address_line2       = $row['address2'];
   $null                = $row['address3'];
   $null                = $row['callbackat'];
   $null                = $row['callbackon'];
   $null                = $row['callbkfreq'];
   $address_city        = $row['city'];
   $null                = $row['closedate'];
   $null                = $row['comments'];
   $company_name        = $row['company'];
   $legal_name          = $row['company'];

    preg_match("/([^\ ]+)( )(.*)/i",$row['contact'],$contact_split);
    $contact_first_names = $contact_split[1];
    $contact_last_name   = $contact_split[3];

   $null                = $row['country'];
   $null                = $row['createat'];
   $null                = $row['createby'];
   $null                = $row['createon'];
   $salutation          = $row['dear'];
   $division_name       = $row['department'];
   $contact_email       = htmlspecialchars($row['e-mail_address']);
   $contact_custom1     = $row['ext1'];
   $contact_custom2     = $row['ext2'];
   $contact_custom3     = $row['ext3'];
   $contact_custom4     = $row['ext4'];
   $company_fax         = $row['fax'];
   $contact_fax         = $row['fax'];
   $null                = $row['key1'];
   $null                = $row['key2'];
   $null                = $row['key3'];
   $null                = $row['key4'];
   $null                = $row['key5'];
   $null                = $row['lastatmpat'];
   $null                = $row['lastatmpon'];
   $null                = $row['lastcontat'];
   $null                = $row['lastconton'];
   $null                = $row['lastdate'];
   $null                = $row['lastname'];
   $null                = $row['lasttime'];
   $null                = $row['lastuser'];
   $null                = $row['meetdateon'];
   $null                = $row['meettimeat'];
   $null                = $row['mergecodes'];
   $null                = $row['nextaction'];
// $null        = $row['notes'];
   $null                = $row['owner'];
   $company_phone       = $row['phone1'];
   $contact_work_phone  = $row['phone1'];
   $company_phone2      = $row['phone2'];
   $null                = $row['phone3'];
   $null                = $row['prevresult'];
   $null                = $row['recid'];
   $null                = $row['secr'];
   $null                = $row['source'];
   $address_state       = $row['state'];
   $null                = $row['status'];
   $null                = $row['title'];
   $null                = $row['userdef01'];
   $null                = $row['userdef02'];
   $null                = $row['userdef03'];
   $null                = $row['userdef04'];
   $null                = $row['userdef05'];
   $null                = $row['userdef06'];
   $null                = $row['userdef07'];
   $null                = $row['userdef08'];
   $null                = $row['userdef09'];
   $null                = $row['userdef10'];
   $company_website     = $row['web_site'];
   $address_postal_code = $row['zip'];

/**
 * $Log: import-template-goldmine.php,v $
 * Revision 1.1  2004/04/19 14:21:54  braverock
 * - add additional look-ups and tests on import
 * - improve error reporting
 * - revise process to use templates
 *   - makes use of material from SF patch 926925 by Glenn Powers
 *
 */
?>