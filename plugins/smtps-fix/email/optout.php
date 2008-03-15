<?php
/**
*
* Opt-out link.
*
* $Id: optout.php,v 1.1 2008/03/15 16:54:31 randym56 Exp $
*
*/

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-files.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

//$session_user_id = session_check();
$contact_id = $_GET['optout_id'];
$emailverify = isset($_GET['email']) ? $_GET['email'] : '' ;
$contact_id = $contact_id / 1956 * 2 / 195;

$con = get_xrms_dbconnection();
//$con->debug = 1;

$sql = "select * FROM contacts where contact_id=$contact_id";
$rst = $con->execute($sql);

?>
<html>
<body>
<?
if ((!$rst->EOF) AND ($emailverify==$rst->fields['email'])) {
	$contact_name = $rst->fields['first_names'] . " " . $rst->fields['last_name'];
	$contact_email = $rst->fields['email'];
	$user_id = $rst->fields['user_id'];
    $session_user_id = $user_id; // set equal for this script only	
	$sql="SELECT concat(u.first_names, ' ', u.last_name) as user_name, u.email  
		FROM users u
		WHERE u.user_id=$user_id;";
	$rst2 = $con->execute($sql);
	$user_email = $rst2->fields['email'];
	$user_name = $rst2->fields['user_name'];

	// activity Type for Process
	$activity_type_id = 10;

	//add activity
	// Create "activity" log
	$activity_data['contact_id']           = $contact_id; // the contact this activity related to
	$activity_data['activity_type_id']     = $activity_type_id;  // is pulled from activity_type table
	$activity_data['company_id']           = $rst->fields['company_id']; // which company is this activity related to
	$activity_data['activity_title']       = 'Opt Out: User requested opt-out from e-mail link';
	$activity_data['activity_description'] = 'User selected UNSUBSCRIBE from within e-mail to opt out from receiving marketing newsletters.';
	$activity_data['activity_status']      = 'c';         // Closed status
	$activity_data['completed_bol']        = true;           // activity is completed
	$activity_date['activity_record_status'] = 'a';

 	//this line adds the activity..
    $activity_id = add_activity($con, $activity_data );
//echo "<p> Activity ID: " . $activity_id . "</p>";
	//this line updates the contact record to opt-out	
    $sql = "Update contacts SET email_status='o' WHERE contact_id=$contact_id";
	$rst = $con->execute($sql);

	if ($user_email > '') {
		$output = "<p>User <a href=\"" . $http_site_root . "/contacts/one.php?contact_id=" . $contact_id . "\">" . $contact_id . "</a>&nbsp;has opted out of the email marketing newsletter, and has been updated in the CRM database.</p>";
		$_email_full = '"' . $user_name . '" <' . $user_email . '>';
        require_once ( $include_directory . 'classes/SMTPs/SMTPs.php' );
		
		//send e-mail to address in user_email record to notify of opt-out
		$objSMTP = new SMTPs ();
		$objSMTP->setConfig($con, $include_directory.'classes/SMTPs/SMTPs.ini.php');
		$objSMTP->setFrom ( $rst2->fields['first_names'] . " " . $rst2->fields['last_name'] . '<' . $contact_email . '>' );
		$objSMTP->setSubject ( 'User Opted Out of Email' );
		$objSMTP->setTo ( $_email_full );
		$objSMTP->setSensitivity(0); //1 = personal
		$objSMTP->setBodyContent ($output,'html');
		$objSMTP->setTransEncodeType(0); //0=7bit, 1=8bit
		$objSMTP->setMD5flag(true);
		$objSMTP->setSensitivity(0); //0=none
		$objSMTP->setPriority(3); //3=normal
					
		//this line of code sends the message to the SMTP server
		$mail_result=$objSMTP->sendMsg ();
		$rst2->close();
		}
?>
<p>We are sorry you have decided to unsubscribe to our e-mail newsletters.  We have checked the Opt-Out box on your contact record in our database.<br>
If you change your mind and want us to reactivate your subscription, please contact us or <input type=button class=button onclick="javascript: location.href='optin.php?contact_id=<?php echo $contact_id; ?>'" value="Click Here">.
<p>Thank you,<br>
<? echo $system_company_name; ?>&nbsp;Customer Service</p>
<input type=button class=button onclick="javascript: location.href='<?php echo $main_web_site; ?>'" value="Home Page">
<?
	}
else {
?>

<p>You have selected a contact name to opt out from the e-news that does not exist in our database.  <br>If you believe this to be in error, please contact us by e-mail directly.
<p>Thank you,<br>
<? echo $system_company_name; ?>&nbsp;Customer Service

<?
}	

$rst->close();
$con->close();

?>
</body>
</html>

<?
/**
* $Log: optout.php,v $
* Revision 1.1  2008/03/15 16:54:31  randym56
* Updated SMTPs to allow for individual user SMTP addressing - requires installation and activation of mcrypt in PHP - follow README.txt instructions
*
* 
*
*/
?>