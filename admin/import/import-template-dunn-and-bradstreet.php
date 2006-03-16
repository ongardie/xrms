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
    $company_name        = $row['Ccompany Name'];
    if(!strlen($company_name)) {
        $company_name    = $row['company'];
    }
    $legal_name          = $company_name;
    $division_name       = $row['division_name'];
    if (!strlen($division_name)) {
        $division_name   = $row['division'];
    }
    $company_website     = $row['website'];
    $company_taxid       = $row['tax_id'];
    $extref1             = $row['extref1'];
    $extref2             = $row['extref2'];
    $extref3             = $row['extref3'];
    $company_custom1     = $row['D-U-N-S Number'];
    $company_custom2     = $row['Tradestyles'];
    $company_custom3     = $row['company_custom3'];
    $company_custom4     = $row['company_custom4'];
    $employees           = $row['employees'];
    $revenue             = $row['Sales'];
    $credit_limit        = $row['credit_limit'];
    $terms               = $row['terms'];
    $company_profile     = $row['company_profile'];
    $company_code        = $row['company_code'];
    $company_phone       = $row['Phone Number'];
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
    $gender                = $row['gender'];

    //address info
    $address_name               = $row['address_name'];
    $address_line1              = $row['Address'];
    $address_line2              = $row['line2'];
    $address_city               = $row['City'];
    $address_state              = $row['State'];
    $address_postal_code        = $row['ZIP'];
    $address_country            = $row['country'];
    $address_body               = $row['address_body'];
    $address_use_pretty_address = $row['use_pretty_address'];

if ($row['County']) { $profile .= "County = {$row['County']}\n";}
if ($row['Emp Here']) { $profile .= "Emp Here = {$row['Emp Here']}\n";}
if ($row['Emp Total']) { $profile .= "Emp Total = {$row['Emp Total']}\n";}
if ($row['Location Type']) { $profile .= "Location Type = {$row['Location Type']}\n";}
if ($row['Line of Business']) { $profile .= "Line of Business = {$row['Line of Business']}\n";}
if ($row['1st Primary SIC']) { $profile .= "1st Primary SIC = {$row['1st Primary SIC']}\n";}
if ($row['2nd Primary SIC']) { $profile .= "2nd Primary SIC = {$row['2nd Primary SIC']}\n";}
if ($row['3rd Primary SIC']) { $profile .= "3rd Primary SIC = {$row['3rd Primary SIC']}\n";}
if ($row['4th Primary SIC']) { $profile .= "4th Primary SIC = {$row['4th Primary SIC']}\n";}
if ($row['Ownership Date']) { $profile .= "Ownership Date = {$row['Ownership Date']}\n";}
if ($row['Metropolitan Area']) { $profile .= "Metropolitan Area = {$row['Metropolitan Area']}\n";}
if ($row['State of Inc']) { $profile .= "State of Inc = {$row['State of Inc']}\n";}
if ($row['Import/Export']) { $profile .= "Import/Export = {$row['Import/Export']}\n";}
if ($row['Public/Private']) { $profile .= "Public/Private = {$row['Public/Private']}\n";}
if ($row['Ticker Symbol']) { $profile .= "Ticker Symbol = {$row['Ticker Symbol']}\n";}
if ($row['Parent Name']) { $profile .= "Parent Name = {$row['Parent Name']}\n";}
if ($row['Parent D-U-N-S']) { $profile .= "Parent D-U-N-S = {$row['Parent D-U-N-S']}\n";}
if ($row['Headquarter Name']) { $profile .= "Headquarter Name = {$row['Headquarter Name']}\n";}
if ($row['Headquarter D-U-N-S']) { $profile .= "Headquarter D-U-N-S = {$row['Headquarter D-U-N-S']}\n";}
if ($row['HQ Url1']) { $profile .= "HQ Url1 = {$row['HQ Url1']}\n";}
if ($row['HQ Url2']) { $profile .= "HQ Url2 = {$row['HQ Url2']}\n";}
if ($row['HQ Url3']) { $profile .= "HQ Url3 = {$row['HQ Url3']}\n";}
if ($row['HQ Url4']) { $profile .= "HQ Url4 = {$row['HQ Url4']}\n";}
if ($row['HQ Url5']) { $profile .= "HQ Url5 = {$row['HQ Url5']}\n";}
if ($row['HQ Url6']) { $profile .= "HQ Url6 = {$row['HQ Url6']}\n";}
if ($row['HQ Url7']) { $profile .= "HQ Url7 = {$row['HQ Url7']}\n";}
if ($row['HQ Url8']) { $profile .= "HQ Url8 = {$row['HQ Url8']}\n";}
if ($row['HQ Url9']) { $profile .= "HQ Url9 = {$row['HQ Url9']}\n";}
if ($row['HQ Url10']) { $profile .= "HQ Url10 = {$row['HQ Url10']}\n";}
if ($row['HQ Url11']) { $profile .= "HQ Url11 = {$row['HQ Url11']}\n";}
if ($row['HQ Url12']) { $profile .= "HQ Url12 = {$row['HQ Url12']}\n";}
if ($row['HQ Url13']) { $profile .= "HQ Url13 = {$row['HQ Url13']}\n";}
if ($row['HQ Url14']) { $profile .= "HQ Url14 = {$row['HQ Url14']}\n";}
if ($row['HQ Url15']) { $profile .= "HQ Url15 = {$row['HQ Url15']}\n";}
if ($row['HQ Url16']) { $profile .= "HQ Url16 = {$row['HQ Url16']}\n";}
if ($row['HQ Url17']) { $profile .= "HQ Url17 = {$row['HQ Url17']}\n";}
if ($row['HQ Url18']) { $profile .= "HQ Url18 = {$row['HQ Url18']}\n";}
if ($row['HQ Url19']) { $profile .= "HQ Url19 = {$row['HQ Url19']}\n";}
if ($row['HQ Url20']) { $profile .= "HQ Url20 = {$row['HQ Url20']}\n";}
if ($row['Site Url1']) { $profile .= "Site Url1 = {$row['Site Url1']}\n";}
if ($row['Site Url2']) { $profile .= "Site Url2 = {$row['Site Url2']}\n";}
if ($row['Site Url3']) { $profile .= "Site Url3 = {$row['Site Url3']}\n";}
if ($row['Site Url4']) { $profile .= "Site Url4 = {$row['Site Url4']}\n";}
if ($row['Site Url5']) { $profile .= "Site Url5 = {$row['Site Url5']}\n";}
if ($row['Site Url6']) { $profile .= "Site Url6 = {$row['Site Url6']}\n";}
if ($row['Site Url7']) { $profile .= "Site Url7 = {$row['Site Url7']}\n";}
if ($row['Site Url8']) { $profile .= "Site Url8 = {$row['Site Url8']}\n";}
if ($row['Site Url9']) { $profile .= "Site Url9 = {$row['Site Url9']}\n";}
if ($row['Site Url10']) { $profile .= "Site Url10 = {$row['Site Url10']}\n";}
if ($row['Site Url11']) { $profile .= "Site Url11 = {$row['Site Url11']}\n";}
if ($row['Site Url12']) { $profile .= "Site Url12 = {$row['Site Url12']}\n";}
if ($row['Site Url13']) { $profile .= "Site Url13 = {$row['Site Url13']}\n";}
if ($row['Site Url14']) { $profile .= "Site Url14 = {$row['Site Url14']}\n";}
if ($row['Site Url15']) { $profile .= "Site Url15 = {$row['Site Url15']}\n";}
if ($row['Site Url16']) { $profile .= "Site Url16 = {$row['Site Url16']}\n";}
if ($row['Site Url17']) { $profile .= "Site Url17 = {$row['Site Url17']}\n";}
if ($row['Site Url18']) { $profile .= "Site Url18 = {$row['Site Url18']}\n";}
if ($row['Site Url19']) { $profile .= "Site Url19 = {$row['Site Url19']}\n";}
if ($row['Site Url20']) { $profile .= "Site Url20 = {$row['Site Url20']}\n";}
if ($row['Rel Url1']) { $profile .= "Rel Url1 = {$row['Rel Url1']}\n";}
if ($row['Rel Url2']) { $profile .= "Rel Url2 = {$row['Rel Url2']}\n";}
if ($row['Rel Url3']) { $profile .= "Rel Url3 = {$row['Rel Url3']}\n";}
if ($row['Rel Url4']) { $profile .= "Rel Url4 = {$row['Rel Url4']}\n";}
if ($row['Rel Url5']) { $profile .= "Rel Url5 = {$row['Rel Url5']}\n";}
if ($row['Rel Url6']) { $profile .= "Rel Url6 = {$row['Rel Url6']}\n";}
if ($row['Rel Url7']) { $profile .= "Rel Url7 = {$row['Rel Url7']}\n";}
if ($row['Rel Url8']) { $profile .= "Rel Url8 = {$row['Rel Url8']}\n";}
if ($row['Rel Url9']) { $profile .= "Rel Url9 = {$row['Rel Url9']}\n";}
if ($row['Rel Url10']) { $profile .= "Rel Url10 = {$row['Rel Url10']}\n";}
if ($row['Rel Url11']) { $profile .= "Rel Url11 = {$row['Rel Url11']}\n";}
if ($row['Rel Url12']) { $profile .= "Rel Url12 = {$row['Rel Url12']}\n";}
if ($row['Rel Url13']) { $profile .= "Rel Url13 = {$row['Rel Url13']}\n";}
if ($row['Rel Url14']) { $profile .= "Rel Url14 = {$row['Rel Url14']}\n";}
if ($row['Rel Url15']) { $profile .= "Rel Url15 = {$row['Rel Url15']}\n";}
if ($row['Rel Url16']) { $profile .= "Rel Url16 = {$row['Rel Url16']}\n";}
if ($row['Rel Url17']) { $profile .= "Rel Url17 = {$row['Rel Url17']}\n";}
if ($row['Rel Url18']) { $profile .= "Rel Url18 = {$row['Rel Url18']}\n";}
if ($row['Rel Url19']) { $profile .= "Rel Url19 = {$row['Rel Url19']}\n";}
if ($row['Rel Url20']) { $profile .= "Rel Url20 = {$row['Rel Url20']}\n";}
if ($row['Bank']) { $profile .= "Bank = {$row['Bank']}\n";}
if ($row['Accountant']) { $profile .= "Accountant = {$row['Accountant']}\n";}
if ($row['Exec 1']) { $profile .= "Exec 1 = {$row['Exec 1']}\n";}
if ($row['Exec 1 Title']) { $profile .= "Exec 1 Title = {$row['Exec 1 Title']}\n";}
if ($row['Exec 2']) { $profile .= "Exec 2 = {$row['Exec 2']}\n";}
if ($row['Exec 2 Title']) { $profile .= "Exec 2 Title = {$row['Exec 2 Title']}\n";}
if ($row['Exec 3']) { $profile .= "Exec 3 = {$row['Exec 3']}\n";}
if ($row['Exec 3 Title']) { $profile .= "Exec 3 Title = {$row['Exec 3 Title']}\n";}
if ($row['Exec 4']) { $profile .= "Exec 4 = {$row['Exec 4']}\n";}
if ($row['Exec 4 Title']) { $profile .= "Exec 4 Title = {$row['Exec 4 Title']}\n";}
if ($row['Exec 5']) { $profile .= "Exec 5 = {$row['Exec 5']}\n";}
if ($row['Exec 5 Title']) { $profile .= "Exec 5 Title = {$row['Exec 5 Title']}\n";}
if ($row['Exec 6']) { $profile .= "Exec 6 = {$row['Exec 6']}\n";}
if ($row['Exec 6 Title']) { $profile .= "Exec 6 Title = {$row['Exec 6 Title']}\n";}
if ($row['Exec 7']) { $profile .= "Exec 7 = {$row['Exec 7']}\n";}
if ($row['Exec 7 Title']) { $profile .= "Exec 7 Title = {$row['Exec 7 Title']}\n";}
if ($row['Exec 8']) { $profile .= "Exec 8 = {$row['Exec 8']}\n";}
if ($row['Exec 8 Title']) { $profile .= "Exec 8 Title = {$row['Exec 8 Title']}\n";}
if ($row['Exec 9']) { $profile .= "Exec 9 = {$row['Exec 9']}\n";}
if ($row['Exec 9 Title']) { $profile .= "Exec 9 Title = {$row['Exec 9 Title']}\n";}
if ($row['Exec 10']) { $profile .= "Exec 10 = {$row['Exec 10']}\n";}
if ($row['Exec 10 Title']) { $profile .= "Exec 10 Title = {$row['Exec 10 Title']}\n";}
if ($row['Exec 11']) { $profile .= "Exec 11 = {$row['Exec 11']}\n";}
if ($row['Exec 11 Title']) { $profile .= "Exec 11 Title = {$row['Exec 11 Title']}\n";}
if ($row['Exec 12']) { $profile .= "Exec 12 = {$row['Exec 12']}\n";}
if ($row['Exec 12 Title']) { $profile .= "Exec 12 Title = {$row['Exec 12 Title']}\n";}
if ($row['Exec 13']) { $profile .= "Exec 13 = {$row['Exec 13']}\n";}
if ($row['Exec 13 Title']) { $profile .= "Exec 13 Title = {$row['Exec 13 Title']}\n";}
if ($row['Exec 14']) { $profile .= "Exec 14 = {$row['Exec 14']}\n";}
if ($row['Exec 14 Title']) { $profile .= "Exec 14 Title = {$row['Exec 14 Title']}\n";}
if ($row['Exec 15']) { $profile .= "Exec 15 = {$row['Exec 15']}\n";}
if ($row['Exec 15 Title']) { $profile .= "Exec 15 Title = {$row['Exec 15 Title']}\n";}
if ($row['Exec 16']) { $profile .= "Exec 16 = {$row['Exec 16']}\n";}
if ($row['Exec 16 Title']) { $profile .= "Exec 16 Title = {$row['Exec 16 Title']}\n";}
if ($row['Exec 17']) { $profile .= "Exec 17 = {$row['Exec 17']}\n";}
if ($row['Exec 17 Title']) { $profile .= "Exec 17 Title = {$row['Exec 17 Title']}\n";}
if ($row['Exec 18']) { $profile .= "Exec 18 = {$row['Exec 18']}\n";}
if ($row['Exec 18 Title']) { $profile .= "Exec 18 Title = {$row['Exec 18 Title']}\n";}
if ($row['Exec 19']) { $profile .= "Exec 19 = {$row['Exec 19']}\n";}
if ($row['Exec 19 Title']) { $profile .= "Exec 19 Title = {$row['Exec 19 Title']}\n";}
if ($row['Exec 20']) { $profile .= "Exec 20 = {$row['Exec 20']}\n";}
if ($row['Exec 20 Title']) { $profile .= "Exec 20 Title = {$row['Exec 20 Title']}\n";}
if ($row['Exec 21']) { $profile .= "Exec 21 = {$row['Exec 21']}\n";}
if ($row['Exec 21 Title']) { $profile .= "Exec 21 Title = {$row['Exec 21 Title']}\n";}
if ($row['Exec 22']) { $profile .= "Exec 22 = {$row['Exec 22']}\n";}
if ($row['Exec 22 Title']) { $profile .= "Exec 22 Title = {$row['Exec 22 Title']}\n";}
if ($row['Exec 23']) { $profile .= "Exec 23 = {$row['Exec 23']}\n";}
if ($row['Exec 23 Title']) { $profile .= "Exec 23 Title = {$row['Exec 23 Title']}\n";}
if ($row['Exec 24']) { $profile .= "Exec 24 = {$row['Exec 24']}\n";}
if ($row['Exec 24 Title']) { $profile .= "Exec 24 Title = {$row['Exec 24 Title']}\n";}
if ($row['Exec 25']) { $profile .= "Exec 25 = {$row['Exec 25']}\n";}
if ($row['Exec 25 Title']) { $profile .= "Exec 25 Title = {$row['Exec 25 Title']}\n";}
if ($row['Exec 26']) { $profile .= "Exec 26 = {$row['Exec 26']}\n";}
if ($row['Exec 26 Title']) { $profile .= "Exec 26 Title = {$row['Exec 26 Title']}\n";}
if ($row['Exec 27']) { $profile .= "Exec 27 = {$row['Exec 27']}\n";}
if ($row['Exec 27 Title']) { $profile .= "Exec 27 Title = {$row['Exec 27 Title']}\n";}
if ($row['Exec 28']) { $profile .= "Exec 28 = {$row['Exec 28']}\n";}
if ($row['Exec 28 Title']) { $profile .= "Exec 28 Title = {$row['Exec 28 Title']}\n";}
if ($row['Exec 29']) { $profile .= "Exec 29 = {$row['Exec 29']}\n";}
if ($row['Exec 29 Title']) { $profile .= "Exec 29 Title = {$row['Exec 29 Title']}\n";}
if ($row['Exec 30']) { $profile .= "Exec 30 = {$row['Exec 30']}\n";}
if ($row['Exec 30 Title']) { $profile .= "Exec 30 Title = {$row['Exec 30 Title']}\n";}

/**
 * $Log: import-template-dunn-and-bradstreet.php,v $
 * Revision 1.2  2006/03/16 21:53:46  vanmer
 * - fixed parse errors in dunn and bradstreet template
 * - partial patch suggested by icheb_ AT sourceforge DOT net
 *
 * Revision 1.1  2004/07/21 20:43:53  gpowers
 * - Import Template for Dunn adnd Bradstreet CSV dataset
 *   - ** UNTESTED **
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
