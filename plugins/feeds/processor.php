#!/usr/bin/php
<?php

/**
 * process RSS/XML feeds in XRMS
 * 
 * copyright 2007 Glenn Powers <glenn@net127.com>
 *
 * $Id: processor.php,v 1.1 2007/12/10 18:06:44 gpowers Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-contacts.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

// $session_user_id = session_check('','Create');
$session_user_id = 1;

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$clone_id = isset($_GET['clone_id']) ? $_GET['clone_id'] : 0;
getGlobalVar($return_url, 'return_url');

$con = get_xrms_dbconnection();
// $con->debug=1;

$file = "http://192.168.0.10/newusers.xml";

$content = array();

// based on http://www.wirelessdevnet.com/channels/wap/features/xmlcast_php.html
 
function startElement($parser, $name, $attrs) { 
	global $curTag, $content;
	$curTag = "$name";
	if ($name == 'ENTRY') {
		$content = array();
	}
}

function endElement($parser, $name) {
	global $curTag, $content;
	$caret_pos = strrpos($curTag,'^'); 
	$curTag = substr($curTag,0,$caret_pos);
	echo $name . "<br />";
	if ($name == 'ENTRY') {
		do_add($content);
	}
}

function characterData($parser, $data) {
	global $curTag, $content;
	$content[$curTag]=$data;
}

// main loop
$xml_parser = xml_parser_create();
xml_set_element_handler($xml_parser, "startElement", "endElement");
xml_set_character_data_handler($xml_parser, "characterData");

if (!($fp = fopen($file,"r"))) {
	die ("could not open RSS for input");
}

while ($data = fread($fp, 4096)) {
	if (!xml_parse($xml_parser, $data, feof($fp))) {
		die(sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($xml_parser)),
			xml_get_current_line_number($xml_parser)));
	}
}

xml_parser_free($xml_parser);

function do_add($content) {
	global $con,$content;

	// Add a contact record
    // Required Fields
$contact_info=array();
$contact_info['company_id']			= 1;
$contact_info['address_id']			= 1;
$contact_info['home_address_id']	= 1;
$contact_info['last_name']			= $content['LOGIN'];
$contact_info['first_names']		= $content['LOGIN'];
$contact_info['email']				= $content['EMAIL'];
$contact_info['email_status'] 		= 'a';

    // These fields are optional,
    // some may be derived from other fields if not defined.
$contact_info['user_id']			= 1;
$contact_info['division_id']		= "";
$contact_info['salutation']			= "";
$contact_info['gender']				= "";
$contact_info['date_of_birth']		= "";
$contact_info['summary']			= $content['COMPANY'];
$contact_info['title']				= $content['TITLE'];
$contact_info['description']		= "";
$contact_info['work_phone']			= "";
$contact_info['work_phone_ext']		= "";
$contact_info['cell_phone']			= "";
$contact_info['home_phone']			= "";
$contact_info['fax']				= "";
$contact_info['tax_id']				= "";
$contact_info['aol_name']			= "";
$contact_info['yahoo_name']			= "";
$contact_info['msn_name']			= "";
$contact_info['interests']			= $content['URL'];
$contact_info['profile']			= $content['ABOUT'];
$contact_info['custom1']			= $content['LOGIN'];
$contact_info['custom2']			= "";
$contact_info['custom3']			= $content['SIGNUP_CODE'];
$contact_info['custom4']			= "";
$contact_info['extref1']			= "";
$contact_info['extref2']			= "";
$contact_info['extref3']			= "";

/*Do not define these fields, they are auto-defined
 * - entered_at              - when was record created
 * - entered_by              - who created the record
 * - last_modified_at        - when was record modified - this will be the same as 'entered_at'
 * - last_modified_by        - who modified the record  - this will be the same as 'entered_by'
 * - contact_record_status   - the database defaults this to [a] Active
 * - email_status            - the database defaults this to [a] Active
 *
 * @param adodbconnection  $con               with handle to the database
 * @param array            $contact_info      with data about the contact, to add/update
 * @param boolean          $_return_data      F - returns record ID, T - returns record in an array
 * @param boolean          $_magic_quotes     F - inbound data is not "add slashes", T - data is "add slashes"
 *
 * @return mixed $contact_id of newly created or modified contact, record data array or false if failure occured
 */


add_update_contact($con, $contact_info);

foreach(array_keys($contact_info) as $key) {
	echo $key . "=" . $contact_info['key'] . "=<br />";
}

}

?>