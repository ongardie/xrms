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

   // Matched Headers
   $contact_home_phone  = $row['home_phone'];

   $address_name        = $row['address_name'];

   $company_code    = $row['accountno'];
   if ($row['actionon']) {
       $company_profile     .= "Action On: " . $row['actionon'] . "\n";
   }
   $address_line1       = $row['address1'];
   $address_line2       = $row['address2'];
   if ($row['address3']) {
       $company_profile     .= "Address 3:" . $row['address3'] . "\n";
   }
   if ($row['callbackat']) {
       $company_profile     .= "Call Back At: " . $row['callbackat'] . "\n";
   }
   if ($row['callbackon']) {
       $company_profile     .= "Call Back On: " . $row['callbackon'] . "\n";
   }
   if ($row['callbkfreq']) {
       $company_profile     .= "Call Back Freq: " . $row['callbkfreq'] . "\n";
   }
   $address_city        = $row['city'];
   if ($row['closedate']) {
       $company_profile     .= "Close Date: " . $row['closedate'] . "\n";
   }
   if ($row['closedate']) {
       $company_profile .= "Comments: " . $row['comments'] . "\n";
   }
   $company_name        = $row['company'];
   $legal_name          = $row['company'];

    preg_match("/([^\ ]+)( )(.*)/i",$row['contact'],$contact_split);
    $contact_first_names = $contact_split[1];
    $contact_last_name   = $contact_split[3];

   $address_country = $row['country'];
   if ($row['createat']) {
       $company_profile     .= "Create At: " . $row['createat'] . "\n";
   }
   if ($row['createby']) {
       $company_profile     .= "Create By: " . $row['createby'] . "\n";
   }
   if ($row['createon']) {
       $company_profile     .= "Create On: " . $row['createon'] . "\n";
   }
   $salutation          = $row['dear'];
   $division_name       = $row['department'];
   $contact_email       = htmlspecialchars($row['e-mail_address']);
   $contact_custom1     = $row['ext1'];
   $contact_custom2     = $row['ext2'];
   $contact_custom3     = $row['ext3'];
   $contact_custom4     = $row['ext4'];
   $company_fax         = $row['fax'];
   $contact_fax         = $row['fax'];
   if ($row['key1']) {
       $company_profile     .= "Key 1: " . $row['key1'] . "\n";
   }
   if ($row['key2']) {
       $company_profile     .= "Key 2: " . $row['key2'] . "\n";
   }
   if ($row['key3']) {
       $company_profile     .= "Key 3: " . $row['key3'] . "\n";
   }
   if ($row['key4']) {
       $company_profile     .= "Key 4: " . $row['key4'] . "\n";
   }
   if ($row['key5']) {
       $company_profile     .= "Key 5: " . $row['key5'] . "\n";
   }
   if ($row['lastatmpat']) {
       $company_profile     .= "Last Atm At: " . $row['lastatmpat'] . "\n";
   }
   if ($row['lastatmpon']) {
       $company_profile     .= "Last Atm On: " . $row['lastatmpon'] . "\n";
   }
   if ($row['lastcontat']) {
       $company_profile     .= "Last Contact At: " . $row['lastcontat'] . "\n";
   }
   if ($row['lastconton']) {
       $company_profile     .= "Last Contact On: " . $row['lastconton'] . "\n";
   }
   if ($row['lastdate']) {
       $company_profile     .= "Last Date: " . $row['lastdate'] . "\n";
   }
   if ($row['lastname']) {
       $company_profile     .= "Last Name: " . $row['lastname'] . "\n";
   }
   if ($row['lasttime']) {
       $company_profile     .= "Last Time: " . $row['lasttime'] . "\n";
   }
   if ($row['lastuser']) {
       $company_profile     .= "Last User: " . $row['lastuser'] . "\n";
   }
   if ($row['meetdateon']) {
       $company_profile     .= "Meet Date On: " . $row['meetdateon'] . "\n";
   }
   if ($row['meettimeat']) {
       $company_profile     .= "Meet Time At: " . $row['meettimeat'] . "\n";
   }
   if ($row['mergecodes']) {
       $company_profile     .= "Merge Codes: " . $row['mergecodes'] . "\n";
   }
   if ($row['nextaction']) {
       $company_profile     .= "Next Action: " . $row['nextaction'] . "\n";
   }
   $company_profile    .= $row['notes']. "\n";
   if ($row['owner']) {
       $company_profile     .= "Owner" . $row['owner'] . "\n";
   }
   $company_phone       = $row['phone1'];
   $contact_work_phone  = $row['phone1'];
   $company_phone2      = $row['phone2'];
   if ($row['phone3']) {
       $company_profile     .= "Phone 3: " . $row['phone3'] . "\n";
   }
   if ($row['prevresult']) {
       $company_profile     .= "Prev Result: " . $row['prevresult'] . "\n";
   }
   if ($row['recid']) {
       $company_profile     .= "Rec ID: " . $row['recid'] . "\n";
   }
   if ($row['secr']) {
       $company_profileuserdef10    .= "Secr: " . $row['secr'] . "\n";
   }
   if ($row['source']) {
       $company_profile     .= "Source: " . $row['source'] . "\n";
   }
   $address_state   = $row['state'];
   if ($row['status']) {
       $company_profile     .= "Status: " . $row['status'] . "\n";
   }
   if ($row['title']) {
       $contact_title = $row['title'];
   }
   if ($row['userdef01']) {
       $company_profile     .= "User Def 01: " . $row['userdef01'] . "\n";
   }
   if ($row['userdef02']) {
       $company_profile     .= "User Def 02: " . $row['userdef02'] . "\n";
   }
   if ($row['userdef03']) {
       $company_profile     .= "User Def 03: " . $row['userdef03'] . "\n";
   }
   if ($row['userdef04']) {
       $company_profile     .= "User Def 04: " . $row['userdef04'] . "\n";
   }
   if ($row['userdef05']) {
       $company_profile     .= "User Def 05: " . $row['userdef05'] . "\n";
   }
   if ($row['userdef06']) {
       $company_profile     .= "User Def 06: " . $row['userdef06'] . "\n";
   }
   if ($row['userdef07']) {
       $company_profile     .= "User Def 07: " . $row['userdef07'] . "\n";
   }
   if ($row['userdef08']) {
       $company_profile     .= "User Def 08: " . $row['userdef08'] . "\n";
   }
   if ($row['userdef09']) {
       $company_profile     .= "User Def 09: " . $row['userdef09'] . "\n";
   }
   if ($row['userdef10']) {
       $company_profile     .= "User Def 10: " . $row['userdef10'] . "\n";
   }
   $company_website     = $row['web_site'];
   $address_postal_code = $row['zip'];

/**
 * $Log: import-template-goldmine.php,v $
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