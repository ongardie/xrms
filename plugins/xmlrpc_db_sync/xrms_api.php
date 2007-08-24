<?php
$xmlrpc_methods = array();

$xmlrpc_methods['HelloWorld']        				= 'HelloWorld';
$xmlrpc_methods['method_not_found']       	= 'XMLRPC_method_not_found';

$xmlrpc_methods['xrms_find_contact']        = 'xrms_find_contact';
$xmlrpc_methods['xrms_find_company']        = 'xrms_find_company';
$xmlrpc_methods['xrms_export']        			= 'xrms_export';
$xmlrpc_methods['xrms_sql']        					= 'xrms_sql';

//these expose the functions in utils-XXX.php
$xmlrpc_methods['xrms_add_update_company']  = 'xrms_add_update_company';
$xmlrpc_methods['xrms_add_update_contact']  = 'xrms_add_update_contact';
$xmlrpc_methods['xrms_find_address']  		= 'xrms_find_address';
$xmlrpc_methods['xrms_add_update_address']  		= 'xrms_add_update_address';


require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-companies.php');
require_once($include_directory . 'utils-contacts.php');
require_once($include_directory . 'utils-addresses.php');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug=1;



//select output_fieldname from tablename where keyname=keyvalue
function xrms_export($params){
			global $con;
			$key_name=$params[0];
			$key_value_input=$params[1];
			$tablename=$params[2];
			$output_fieldname=$params[3];

			$sql="select $output_fieldname from $tablename where $key_name='$key_value_input'";
			$rst=$con->execute($sql);
			if($rst->RecordCount()){
						while (!$rst->EOF){
									$export_array[]=$rst->fields[$output_fieldname];
									$rst->MoveNext();
						}
			}else{
						XMLRPC_response(XMLRPC_prepare(false), WEBLOG_XMLRPC_USERAGENT);
			}
			///checks for a 1 dimensional array - converts it back to a non array
			if(is_array($export_array)){
						if(count($export_array)==1){
									$export_array=$export_array[0];
						}
			}

			XMLRPC_response(XMLRPC_prepare($export_array), WEBLOG_XMLRPC_USERAGENT);

}
//DBUSER,DBPASSWORD then sql - used for passing updates and so forth
function xrms_sql($params){
			global $con,$xrms_db_username,$xrms_db_password;

			$DBUSERNAME=$params[0];
			$DBPASSWORD=$params[1];
			$sql=$params[2];
			if(contains_prohibited_sql_strings($sql)){
						return XMLRPC_response(XMLRPC_prepare("SQL contains prohibited string. SQL cannot be passed for security reasons."), WEBLOG_XMLRPC_USERAGENT);
			}

			if ($xrms_db_username==$DBUSERNAME&&$xrms_db_password==$DBPASSWORD){
						$rst=$con->execute($sql);
						$output=$con->insert_id();

						XMLRPC_response(XMLRPC_prepare($output), WEBLOG_XMLRPC_USERAGENT);
			}else{
						XMLRPC_response(XMLRPC_prepare("Could not authenticate request"), WEBLOG_XMLRPC_USERAGENT);
			}
}
//returns contact ID or false
function xrms_find_contact($params){
			global $con;
			$first_names=$params[0];
			$last_name=$params[1];
			$email=$params[2];
			$find_exact_data=$params[3];
			if(!isset($params[3]))$find_exact_data=true;


			if($find_exact_data=true){
						$sql="Select contact_id from contacts where first_names='$first_names' and last_name='$last_name' and email='$email' and contact_record_status='a'";
			}else{
						$sql="Select contact_id from contacts where first_names LIKE $first_names and last_name LIKE $last_name and email LIKE $email  and contact_record_status='a'";
			}
			$rst=$con->execute($sql);
			//if you have something return the data
			if($rst->RecordCount()){
						XMLRPC_response(XMLRPC_prepare($rst->fields['contact_id']), WEBLOG_XMLRPC_USERAGENT);
			}else{
						XMLRPC_response(XMLRPC_prepare(false), WEBLOG_XMLRPC_USERAGENT);
			}

}
//returns company ID or false
function xrms_find_company($params){
			global $con;
			$company_name=$params[0];
			$find_exact_data=$params[1];
			if(!isset($params[1]))$find_exact_data=true;


			if($find_exact_data=true){
						$sql="Select company_id from companies where company_name='$company_name' and company_record_status='a'";
			}else{
						$sql="Select company_id from companies where company_name LIKE '$company_name' and company_record_status='a'";
			}
			$rst=$con->execute($sql);
			//if you have something return the data
			if($rst->RecordCount()){
						if($rst->RecordCount()>1){
									while (!$rst->EOF){
												$company_id_array[]=$rst->fields['company_id'];
												$rst->MoveNext();
									}
									XMLRPC_response(XMLRPC_prepare($company_id_array), WEBLOG_XMLRPC_USERAGENT);
						}else{
									XMLRPC_response(XMLRPC_prepare($rst->fields['company_id']), WEBLOG_XMLRPC_USERAGENT);
						}

			}else{
						XMLRPC_response(XMLRPC_prepare(false), WEBLOG_XMLRPC_USERAGENT);
			}

}

function HelloWorld($dummy){
			XMLRPC_response(XMLRPC_prepare("Hello World"), WEBLOG_XMLRPC_USERAGENT);

}


function XMLRPC_method_not_found($methodName){

			XMLRPC_error("2", "The method you requested, '$methodName', was not found.", WEBLOG_XMLRPC_USERAGENT);

}
//send this an array of company_data
function xrms_add_update_company($params){
			global $con;
			$company_data=$params[0];
			//returns an arary of values which are insert IDs
			$return_value_array=add_update_company($con, $company_data, $magic_quotes=false);
			XMLRPC_response(XMLRPC_prepare($return_value_array), WEBLOG_XMLRPC_USERAGENT);
}
//this sends the address id
function xrms_find_address($params){
			global $con;
			$address_data=$params[0];
			//returns an address ID
			$return_value_array=find_address($con, $address_data);
			XMLRPC_response(XMLRPC_prepare($return_value_array['address_id']), WEBLOG_XMLRPC_USERAGENT);
}
//this adds or updates the address id - you need to pass it address_id to make it update
function xrms_add_update_address($params){
			global $con;
			$address_data=$params[0];
			//returns an address ID
			$return_value_array=add_update_address($con, $address_data);
			XMLRPC_response(XMLRPC_prepare($return_value_array['address_id']), WEBLOG_XMLRPC_USERAGENT);
}
//send this an array of contact_data
function xrms_add_update_contact($params){
			global $con;
			$contact_data=$params[0];
			//returns an arary of values which are insert IDs
			$return_value_array=add_update_contact($con, $contact_data, $magic_quotes=false);
			XMLRPC_response(XMLRPC_prepare($return_value_array), WEBLOG_XMLRPC_USERAGENT);
}
function contains_prohibited_sql_strings($sql){
			//check sql for disallowed functions to prevent drops, truncates and other things which could completely destroy databases and table structures
			//these are all MYSQL terms.
			$prohibited_sql_array=array("alter","delete","drop","truncate","shutdown","grant","index");
			//convert sql to lowercase for comparison
			$sql=strtolower($sql);
			foreach ($prohibited_sql_array As $value) {
  						if(stristr($sql,$value)){
							return true;
							}
			}
			return false;

}
?>