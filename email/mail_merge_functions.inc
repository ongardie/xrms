<?php
require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$con = get_xrms_dbconnection();

/*
This library is designed to hold functions you can use to turn an email with {fieldname} tags into it into a merged email
It should be pretty simple.

I havent included any error handling routines for templates with incorrect contact,company or address ids
It will simply fail. I may include this later.

Why not add it yourself?


*/

// This function replaces quotes in a string with &quot; and &Prime; for " and ' in HTML so that the string_replace will work below
function htmlreverse($svalue){
        if ($svalue != "") {$svalue = str_replace("\"", "&quot;", $svalue);}
        if ($svalue != "") {$svalue = str_replace("'", "&prime;", $svalue);}
    return $svalue;
}

// This function reverses the &quot; and &Prime; with " and '
function htmlquotes($svalue){
        if ($svalue != "") {$svalue = str_replace("&quot;", "\"", $svalue);}
        if ($svalue != "") {$svalue = str_replace("&prime;", "'", $svalue);}
    return $svalue;
}

/*
 * This function merges the email into the final product
 * It returns an array ($subject,$body)
 * If you dont pass it any of the DB variables, it will just return the subject and body
 * If you pass it a bad contact id, it will return FALSE for both.
*/
function mail_merge_email($subject,$body,$contact_id="",$address_id="") {
    $mm['subject']  = mail_merge_text($subject, $contact_id, $address_id);
    $mm['body']     = mail_merge_text($body, $contact_id, $address_id);

    return $mm;
}

/*
 * This function expects new values for the merge array an array (the common format of
 * $rst->fields) and appends the new values to the merge array by prepending
 * the array keys with the supplied prepend string
*/
function append_merge_array ($new_values, $prepend_string, &$merge_array) {

    $tmp_keys = array_keys($new_values);
    $tmp_values = array_values($new_values);

    foreach ($new_values as $key => $value) {
        $merge_array['keys'][] = '{'. $prepend_string .'_'. $key .'}';
        $merge_array['values'][] = $value;
    }
}

/*
This is the function which does the merge of anything you send it - a subject or a body...
*/
function mail_merge_text($text, $contact_id, $home_address=false) {

    $con = get_xrms_dbconnection();

    //if the DB record ids are set, get the data
    if(isset($contact_id)) {

        $merge_array = array();

        // Loading all tables at the same time creats duplicate column names problems
        // so let's do this step by step

        // Load all contact values
        $sql = "SELECT * FROM contacts WHERE contacts.contact_id = ". $contact_id .";";
        $rst = $con->Execute($sql);
        if (!$rst OR $rst->EOF) return FALSE; // No record, we cannot continue

        // Do we have an address for this contact?
        if ($rst->fields['address_id'] > 0) {
            $rst->fields['work_phone'] = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['work_phone']);
            $rst->fields['cell_phone'] = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['cell_phone']);
            $rst->fields['fax'] = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['fax']);
            // Do we also have a home address for this contact?
            if ($rst->fields['home_address_id'] > 0)
                $rst->fields['home_phone'] = get_formatted_phone($con, $rst->fields['home_address_id'], $rst->fields['home_phone']);
            else
                $rst->fields['home_phone'] = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['home_phone']);
        }
        // No address but do we have at least a home address?
        elseif ($rst->fields['home_address_id'] > 0) {
            $rst->fields['work_phone'] = get_formatted_phone($con, $rst->fields['home_address_id'], $rst->fields['work_phone']);
            $rst->fields['cell_phone'] = get_formatted_phone($con, $rst->fields['home_address_id'], $rst->fields['cell_phone']);
            $rst->fields['home_phone'] = get_formatted_phone($con, $rst->fields['home_address_id'], $rst->fields['home_phone']);
            $rst->fields['fax'] = get_formatted_phone($con, $rst->fields['home_address_id'], $rst->fields['fax']);
        }

        // Fetch the owner of the contact record
        if ($rst->fields['user_id'] > 0)
            $user_id = (int)$rst->fields['user_id'];

        append_merge_array($rst->fields, 'contact', $merge_array);
        $rst->Close();

        // Load all contact company values
        $sql = "SELECT companies.*, contacts.contact_id, contacts.company_id
                FROM companies, contacts
                WHERE contacts.contact_id = $contact_id
                AND companies.company_id = contacts.company_id;";
        $rst = $con->Execute($sql);
        if (!$rst OR $rst->EOF) return FALSE; // No record, we cannot continue
        unset($rst->fields['contact_id']);

        // Do we have an address for this company.  Hopefully we'll get at least one, preferrably the primary
        if ($rst->fields['default_payment_address'] > 0) $company_address_id = $rst->fields['default_payment_address'];
        if ($rst->fields['default_shipping_address'] > 0) $company_address_id = $rst->fields['default_shipping_address'];
        if ($rst->fields['default_billing_address'] > 0) $company_address_id = $rst->fields['default_billing_address'];
        if ($rst->fields['default_primary_address'] > 0) $company_address_id = $rst->fields['default_primary_address'];

        if ($company_address_id > 0) {
            $rst->fields['phone'] = get_formatted_phone($con, $company_address_id, $rst->fields['phone']);
            $rst->fields['phone2'] = get_formatted_phone($con, $company_address_id, $rst->fields['phone2']);
            $rst->fields['fax'] = get_formatted_phone($con, $company_address_id, $rst->fields['fax']);
        }

        append_merge_array($rst->fields, 'contact_company', $merge_array);
        $rst->Close();

        // Load all contact address values
        $sql = "SELECT addresses.*, contacts.contact_id, contacts.address_id
                FROM addresses, contacts
                WHERE contacts.contact_id = $contact_id
                AND addresses.address_id = contacts.address_id;";
        $rst = $con->Execute($sql);
        if ($rst AND !$rst->EOF) {
            unset($rst->fields['contact_id']);

            append_merge_array($rst->fields, 'contact_address', $merge_array);
            $rst->Close();
        }

        if(isset($user_id)) {

            // Load all user values
            $sql = "SELECT contacts.*, users.user_id, users.user_contact_id FROM contacts, users
                    WHERE contacts.contact_id = users.user_contact_id
                    AND users.user_id = $user_id;";
            $rst = $con->Execute($sql);

            if ($rst AND !$rst->EOF) {

                $user_contact_id = $rst->fields['user_contact_id'];
                unset($rst->fields['user_contact_id']);

                // Do we have an address for this user?
                if ($rst->fields['address_id'] > 0) {
                    $rst->fields['work_phone'] = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['work_phone']);
                    $rst->fields['cell_phone'] = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['cell_phone']);
                    $rst->fields['fax'] = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['fax']);
                    // Do we also have a home address for this user?
                    if ($rst->fields['home_address_id'] > 0)
                        $rst->fields['home_phone'] = get_formatted_phone($con, $rst->fields['home_address_id'], $rst->fields['home_phone']);
                    else
                        $rst->fields['home_phone'] = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['home_phone']);
                }
                // No address but do we have at least a home address?
                elseif ($rst->fields['home_address_id'] > 0) {
                    $rst->fields['work_phone'] = get_formatted_phone($con, $rst->fields['home_address_id'], $rst->fields['work_phone']);
                    $rst->fields['cell_phone'] = get_formatted_phone($con, $rst->fields['home_address_id'], $rst->fields['cell_phone']);
                    $rst->fields['home_phone'] = get_formatted_phone($con, $rst->fields['home_address_id'], $rst->fields['home_phone']);
                    $rst->fields['fax'] = get_formatted_phone($con, $rst->fields['home_address_id'], $rst->fields['fax']);
                }

                append_merge_array($rst->fields, 'user', $merge_array);
                $rst->Close();

                // Load all user company values
                $sql = "SELECT companies.*, contacts.contact_id, contacts.company_id
                        FROM companies, contacts
                        WHERE contacts.contact_id = $user_contact_id
                        AND companies.company_id = contacts.company_id;";
                $rst = $con->Execute($sql);
                if (!$rst OR $rst->EOF) return FALSE; // No record, we cannot continue
                unset($rst->fields['contact_id']);

                // Do we have an address for this company?  Hopefully we'll get at least one, preferrably the primary
                if ($rst->fields['default_payment_address'] > 0) $company_address_id = $rst->fields['default_payment_address'];
                if ($rst->fields['default_shipping_address'] > 0) $company_address_id = $rst->fields['default_shipping_address'];
                if ($rst->fields['default_billing_address'] > 0) $company_address_id = $rst->fields['default_billing_address'];
                if ($rst->fields['default_primary_address'] > 0) $company_address_id = $rst->fields['default_primary_address'];

                if ($company_address_id > 0) {
                    $rst->fields['phone'] = get_formatted_phone($con, $company_address_id, $rst->fields['phone']);
                    $rst->fields['phone2'] = get_formatted_phone($con, $company_address_id, $rst->fields['phone2']);
                    $rst->fields['fax'] = get_formatted_phone($con, $company_address_id, $rst->fields['fax']);
                }

                append_merge_array($rst->fields, 'user_company', $merge_array);
                $rst->Close();

                // Load all contact address values
                $sql = "SELECT addresses.*, contacts.contact_id, contacts.address_id
                        FROM addresses, contacts
                        WHERE contacts.contact_id = $user_contact_id
                        AND addresses.address_id = contacts.address_id;";
                $rst = $con->Execute($sql);
                if ($rst AND !$rst->EOF) {
                    unset($rst->fields['contact_id']);

                    append_merge_array($rst->fields, 'user_address', $merge_array);
                    $rst->Close();
                }
            }
        }

        // Merge fields and text
        return htmlquotes(str_replace($merge_array['keys'], $merge_array['values'], $text));

    } else {
        // The contact_id is missing, we can't do anything
        return FALSE;
    }
}

?>