<?php
/*
 * setup.php
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 *  init plugin into xrms
 *
 * @example Create a function called
 *      xrms_plugin_init_pluginname
 *      where pluginname is the name of your pluign directory
 *      inside this function, you will register all the hooks
 *      that you wish your plugin to be called by
 *
 * You should also put the called functions in your setup,php file
 * Please take care to keep this file as small as possible, as it
 * is included on every page load.  Place your actualy functionality
 * in another file.  It will improve the performance of the entire
 * system.
 *
 * $Id: setup.php,v 1.1 2005/09/25 05:54:55 vanmer Exp $
 */


/** 
  * @var $im_fields defines an associative array, keyed on field name.  Each entry is an array with element 'name' with the display name of the field, and 'type' indicating the type of link to use when displaying the value 
*/
global $im_fields;
$im_fields=array('aol_name'=> array ('name'=>_("AOL IM"), 'type'=>'aim'), 'yahoo_name'=>array ('name'=>_("Yahoo IM"), 'type'=>'yahoo'), 'msn_name'=>array('name'=>_("MSN IM"), 'type'=>'msn'));

/** 
  * @var $im_fields_format defines an associative array, keyed on field type.  Each entry is a string to be used in an <a href="$string"> format.  %n in the string will be replaced with the value of the IM field
*/
global $im_fields_urlformat;
$im_fields_urlformat['msn']="javascript: openMsnSession('%n');";
$im_fields_urlformat['yahoo']="ymsgr:sendim?%n\"><img border=0 src=\"http://opi.yahoo.com/online?u=%n&m=g&t=3\"";
$im_fields_urlformat['aim']="aim:goim?screenname=%n";


/**
  * Plugin Initialization function.  Intended to register all needed hooks into XRMS
  *
**/
function xrms_plugin_init_imfields() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['contact_accounting_inline_display']['imfields']='imfields_edit_contact_display';
    $xrms_plugin_hooks['contact_edit_2']['imfields']='imfields_edit_contact_process';
    $xrms_plugin_hooks['contact_new_2']['imfields']='imfields_new_contact_process';
    $xrms_plugin_hooks['contact_custom_inline_edit_display']['imfields']='imfields_edit_contact_form';
    $xrms_plugin_hooks['contact_custom_inline_new_display']['imfields']='imfields_new_contact_form';
}


/**
  * Form function to display in the edit page.  Retrieves the values from the database and then runs the new contact form code to render the HTML to return
  *
  * @param string &$form_extra referencing string to add HTML string to for output
  * @return string $form_extra with im field output
  *
**/
function imfields_edit_contact_form(&$form_extra) {
    global $im_fields;
    global $im_field_values;
    $im_field_values=array();
    getGlobalVar($contact_id,'contact_id');
    if (!$contact_id) return $form_extra;
    
    $rst=get_imfields_rst($contact_id);
    if ($rst) $im_field_values=$rst->fields;
    $form_extra=imfields_new_contact_form($form_extra);
    return $form_extra;
}

/**
  * Handles the processing of a contact's data, splits output into changed data (contact_data) and old rst, and passes new data into new contact process function
  *
  * @param array $contact_data with all contact data being changed, include key 'contact_id' with ID of contact to update
  *
**/
function imfields_edit_contact_process($contact_data) {
    $old_rst=$contact_data[0];
    $new_contact=$contact_data[1];
    imfields_new_contact_process($new_contact);
}

/**
  * Form function to display in the edit/new page.  Uses values from the database if available and renders the HTML to edit the field
  *
  * @param string &$form_extra referencing string to add HTML string to for output
  * @return string $form_extra with im field output
  *
**/
function imfields_new_contact_form(&$form_extra) {
    global $im_fields;
    global $im_field_values;
    foreach ($im_fields as $fname=>$finfo) {
        $flabel=$finfo['name'];
        $form_extra.="<tr><td class=widget_label_right>$flabel</td><td class=widget_content_form_element><input type=text name=\"$fname\" value=\"{$im_field_values[$fname]}\"></td></tr>";
    }
    return $form_extra;
}

/**
  * Output function to display in the one.php page.  Uses values from the database and renders links to the different IM types
  *
  * @param string &$form_extra referencing string to add HTML string to for output
  * @return string $form_extra with im field output
  *
**/
function imfields_edit_contact_display(&$form_extra) {
    global $im_fields;
    global $im_field_values;
    global $im_fields_urlformat;
    getGlobalVar($contact_id, 'contact_id');
    $rst=get_imfields_rst($contact_id);
    if ($rst) $im_field_values=$rst->fields;

    foreach ($im_fields as $fname=>$finfo) {
        if ($im_field_values[$fname]) {
            $flabel=$finfo['name'];
            $furl_format=$im_fields_urlformat[$finfo['type']];
            if ($furl_format) {
                $flink=str_replace('%n', $im_field_values[$fname], $furl_format);
                $f_str="<a href=\"$flink\">{$im_field_values[$fname]}</a>";
            } else $f_str=$im_field_values[$fname];
            $form_extra.="<tr><td width=1% class=sublabel>$flabel</td><td class=clear>$f_str</td></tr>";
        }
    }
    return $form_extra;
}

/**
  * Returns a recordset with the values of the IM fields
  *
  * @param integer $contact_id with db identifier of contact
  * @param adodbconnection $con with optional db connection to use
  * @return adodbrecordset or false if query failed
  *
**/
function get_imfields_rst($contact_id, $con=false) {
    if (!$contact_id) return false;
    if (!$con) $con= get_xrms_dbconnection();

    global $im_fields;
    $sql_fields=implode(", ", array_keys($im_fields));
    $sql = "SELECT $sql_fields FROM contacts WHERE contact_id=$contact_id";
    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }
    elseif (!$rst->EOF) return $rst;
    else return false;
}


/**
  * Handles the processing of a contact's data, uses form fields and getUpdateSQL to update/add field values to the contacts table in XRMS
  *
  * @param array $contact_data with all contact data being changed, include key 'contact_id' with ID of contact to update
  *
**/
function imfields_new_contact_process($contact_data) {
    global $im_fields;
    $con = get_xrms_dbconnection();
    if (!$contact_data) return false;
    if ($contact_data['contact_id']) {
	$rst=get_imfields_rst($contact_data['contact_id'], $con);
        foreach ($im_fields as $fname=>$finfo) {
            getGlobalVar($contact_im[$fname],$fname);
        }
        $upd = $con->getUpdateSQL($rst, $contact_im, true, get_magic_quotes_gpc());
        if ($upd) {
	    $rst=$con->execute($upd);
	    if (!$rst) { db_error_handler($con, $upd); return false; }
        }
    }
}

/**
 * $Log: setup.php,v $
 * Revision 1.1  2005/09/25 05:54:55  vanmer
 * -Initial Revision of a plugin to handle IM fields for contacts, both display and update
 * - stores contact fields within contact table in XRMS for the time being
 *
 *
 */
?>