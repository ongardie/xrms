<?php
/**
 * Import Template for XRMS - QuickBooks Template.
 *
 * Works on an Excel export of QuickBooks customers.
 * Convert that .xls file to a .csv file in order to use this template.
 *
 * These template files exist to define row to field mappings for
 * importing company and contact data into XRMS.
 *
 * Copyright (c) 2004-6 XRMS Development Team
 */

    $is_part_of_company = false;
    if($row['company'] != '')
        $is_part_of_company = true;
    if($row['last_name'] == ''){
        $names = explode(' ', $row['customer']);
	if(!empty($names)){
		$row['last_name'] = $names[count($names) - 1];
		if(count($names) > 1)
			$row['first_name'] = implode(' ', array_slice($names, 0, count($names) - 1));
	}
    }

    //company info
    if($is_part_of_company)
	$company_name    = $row['company'];
    else
        $company_name    = $row['last_name'].' Household';
    $legal_name          = '';
    $division_name       = '';
    $company_website     = '';
    $company_taxid       = $row['sales_tax_code'];
    $extref1             = '';
    $extref2             = '';
    $extref3             = '';
    $company_custom1     = '';
    $company_custom2     = '';
    $company_custom3     = '';
    $company_custom4     = '';
    $employees           = '';
    $revenue             = '';
    $credit_limit        = $row['credit_limit'];
    if(is_numeric($row['terms'])){
        $terms = $row['terms'];
    }elseif(is_numeric(substr($row['terms'], 4))){//account for "Net 15", "Net 60"
        $terms = substr($row['terms'], 4) + 0;
    }elseif($row['terms'] == 'Due on receipt'){
        $terms = 0;
    }else{
        $terms = '';
    }
    $terms               = $terms;
    $company_profile     = '';
    $company_code        = '';
    $company_phone       = '';
    $company_phone2      = '';
    $company_fax         = '';

    //contact info
    $contact_first_names   = $row['first_name'];
    $contact_last_name     = $row['last_name'];
    $contact_email         = htmlspecialchars($row['email']);
    if($is_part_of_company){
        $contact_work_phone    = $row['phone'];
    	$contact_cell_phone    = $row['alt_phone'];
        $contact_home_phone    = '';
    }else{
        $contact_work_phone    = '';
	$contact_cell_phone    = $row['alt_phone'];
        $contact_home_phone    = $row['phone'];
    }
    $contact_fax           = $row['fax'];
    $contact_division      = '';
    $contact_salutation    = $row['mr,_mrs'];
    $contact_date_of_birth = '';
    $contact_summary       = '';
    $contact_title         = '';
    $contact_description   = implode('; ', array_filter(array($row['job_type'], $row['job_description'])));
    $contact_aol           = '';
    $contact_yahoo         = '';
    $contact_msn           = '';
    $contact_interests     = '';
    $contact_custom1       = '';
    $contact_custom2       = '';
    $contact_custom3       = '';
    $contact_custom4       = '';
    $notes = '';
    if($row['has_notes'] != '' and $row['has_notes'] != 'No notes')
    	$notes = $row['has_notes'];
    $contact_profile       = implode('; ', array_filter(array($notes, $row['note'])));

    $gender                = ''; //probably could figure it out from title in most cases

    //address info
    if(!empty($row['city'])){
    	$address_name               = 'Billing';
	if(!empty($row['first_name']))
		$address_name .= ' ('.$row['first_name'].')';
    	$address_line1              = $row['street1'];
    	$address_line2              = $row['street2'];
    	$address_city               = $row['city'];
    	$address_state              = $row['state'];
    	$address_postal_code        = $row['zip'];
    	$address_country            = $row['country'];
    	$address_body               = '';
    	$address_use_pretty_address = '';
    }else{
	$address_name = $address_line1 = $address_line2 = $address_city = $address_state = $address_postal_code = $address_country = $address_body = $address_use_pretty_address = '';
    }

    //address 2 info
    if(!empty($row['ship_to_city'])){
    	$address2_name               = 'Shipping';
	if(!empty($row['first_name']))
		$address2_name .= ' ('.$row['first_name'].')';
    	$address2_line1              = $row['ship_to_street1'];
    	$address2_line2              = $row['ship_to_street2'];
    	$address2_city               = $row['ship_to_city'];
    	$address2_state              = $row['ship_to_state'];
    	$address2_postal_code        = $row['ship_to_zip'];
    	$address2_country            = $row['ship_to_country'];
    	$address2_body               = '';
    	$address2_use_pretty_address = '';
    }else{
	$address2_name = $address2_line1 = $address2_line2 = $address2_city = $address2_state = $address2_postal_code = $address2_country = $address2_body = $address2_use_pretty_address = '';
    }
    
    $cl = 'QuickBooks Customer ID';
    if($contact_custom1_label == $cl)
        $contact_custom1 = $row['customer'];
    if($contact_custom2_label == $cl)
        $contact_custom2 = $row['customer'];
    if($contact_custom3_label == $cl)
        $contact_custom3 = $row['customer'];
    if($contact_custom4_label == $cl)
        $contact_custom4 = $row['customer'];
?>
