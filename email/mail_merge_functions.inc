<?php
require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$con = get_xrms_dbconnection();

/*
This library is designed to hold functions you can use to turn an email with TABLENAME.FIELDNAME tags into it into a merged email
It should be pretty simple.

I havent included any error handling routines for templates with incorrect contact,company or address ids
It will simply fail. I may include this later.

Why not add it yourself?


*/

/*
This function merges the email into the final product
It returns an array ($subject,$body)
If you dont pass it any of the DB variables, it will just return the subject and body
If you pass it a bad contact id, it will return 0 for 
*/
function mail_merge_email($subject,$body,$contact_id="",$address_id=""){
			$mm[0]=mail_merge_text($subject,$contact_id,$address_id);
			$mm[1]=mail_merge_text($body,$contact_id,$address_id);
			//error test it
			//if($mm[0]==0||$mm[1]==0)return 0;
			
			return $mm;
			//do the body
}
/*
This is the function which does the merge of anything you send it - a subject or a body...
*/

function mail_merge_text($text,$contact_id,$address_id=""){
			$con = get_xrms_dbconnection();
			//if the DB record ids are set, get the data
			if(isset($contact_id))
			{
						//NOTE: This sql will only give you the BUSINESS address
						if(!isset($address_id)||$address_id==''){
									$sql="select c.*, a.*,cn.*
									FROM contacts as c, addresses as a, companies as cn
						
									WHERE c.company_id=cn.company_id
									AND c.contact_id='".$contact_id."'
									AND a.address_id=c.address_id";
						}else{
									//using tis one you can in throy get the home address
									$sql="select c.*, a.*,cn.*
									FROM contacts as c, addresses as a, companies as cn
									WHERE c.company_id=cn.company_id
									AND c.contact_id='".$contact_id."'
									AND a.address_id='".$address_id."'";
						}
						
						$rst_data = $con->execute($sql);
						//if there is no address get contact info without address info 
            if ($rst_data->fields['address_id'] <= 0) { 
            $sql="select c.*, cn.*  
            FROM contacts as c, companies as cn  
            WHERE c.company_id=cn.company_id  
            AND c.contact_id='".$contact_id."'"; 
            $rst_data = $con->execute($sql); 
            } 


						//echo $sql;
						
						$db_values=$rst_data->FetchRow();
						if($rst_data->RecordCount()==0)return 0;


  						for ($i=0;$i<$rst_data->FieldCount();$i++)
  						{
  									$fld=$rst_data->FetchField[$i];
  									$db_tags[]=$fld->name;
  									//echo var_dump($fld->name);
  									//var_dump($rst_data->FetchField[$i]);
  									}
  						
  
  									$rst_data->Close();
  									$db_tags=array_keys($db_values);
  									for($i=0;$i<count($db_tags);$i++)
  									{
  														$db_tags[$i]="{".$db_tags[$i]."}";														
  									}
							}
					return str_replace($db_tags, array_values($db_values), $text);
}
/*
$subject = 'Greetings! {first_names}';
$body = 'Dear {first_names}, How the hell are you? I really like your {company_name}';
for($i=1;$i<29;$i++){
	$m=mail_merge_email($subject,$body,$i,$address_id="");
	echo $m[0]."<BR>";
	echo $m[1]."<BR>";
}
*/
?>
