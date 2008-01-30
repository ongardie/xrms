<?php
/**
 *
 * Log an email message
 *
 * $Id: logger.php,v 1.4 2008/01/30 21:31:29 gpowers Exp $
 */

// include files
require_once('/var/www/xrms/include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-contacts.php');
require_once($include_directory . 'utils-activities.php');
//require_once 'Console/Getopt.php';
require_once '/usr/share/php/Zend/Mail/Storage/Pop3.php';

// open database connection
$con = get_xrms_dbconnection();
$con->debug = 0;

$session_user_id = 1;

// putenv("TZ=America/Chicago");
    
$email_host = '';
$email_addr = '';
$email_pass = '';

$mail = new Zend_Mail_Storage_Pop3(array('host'     => $email_host,
                                         'user'     => $email_addr,
                                         'password' => $email_pass));

add_audit_item($con, $session_user_id, 'read', 'email_messages', $mail->countMessages(), 1);

foreach ($mail as $messageNum => $message) {
    $from = $message->from;
    $from_email = preg_replace('/[^<]*<([^>]+)>.*/','$1',$from);
    $subject = $message->subject;
    $tos = $message->to;
    $date = date("Y-m-d h:i m");

echo "<h2>Processing Email</h2>";
echo "To: <pre>" . $tos .  "</pre><br />";
echo "From: <pre>" . $from . "</pre><br />";
echo "Date: " . $date .  "<br />";
echo "Subject: " . $subject . "<br />";

    
// output first text/html part
    $foundPart = null;

    foreach ($mail->getMessage($messageNum) as $part) {
        // try {
            if (strtok($part->contentType, ';') == 'text/plain') {
                $foundPart = $part;
                break;
            }
            if (strtok($part->contentType, ';') == 'text/html') {
                $HTMLfoundPart = $part;
                break;
            }
        /*} catch (Zend_Mail_Exception $e) {
           $error=1;
        }*/
    }

    if (strlen($HTMLfoundPart)>0) {
        $body = $HTMLfoundPart;
    } elseif (strlen($foundPart)>0) {
        $body = $foundPart;
    } else {
        $body = "UNABLE TO PROCESS MESSAGE!";
    }

// find the owner
$sql = "select  user_id from users
        where email like " . $con->qstr($from_email) . " limit 1";
$rst = $con->execute($sql);

if (!$rst->EOF) {
    $activity_data['user_id'] = $rst->fields['user_id'];
} else {
    $activity_data['user_id'] = 1;
}

$sql = "select  contact_id, company_id from contacts
        where email like " . $con->qstr($from_email) . " limit 1";
$rst = $con->execute($sql);

if (!$rst->EOF) {
    $contact_id = $rst->fields['contact_id'];
    $company_id = $rst->fields['company_id'];
} else {
    $company_id = 1;
    $contact_info['company_id'] = $company_id;
    $contact_info['address_id'] = 1;
    $contact_info['home_address_id'] = 1;
    $contact_info['last_name'] = $addr_sql;
    $contact_info['first_names'] = '(Email)';
    $contact_info['email'] = $addr_sql;
    $contact_info['email_status'] = 'a';

    $new_contact = add_update_contact($con, $contact_info);
    $contact_id  = $new_contact['contact_id'];
}

$activity_data['activity_type_id'] = 4; # "Email From"
$activity_data['company_id'] = $company_id;
$activity_data['scheduled_at'] = $date;
$activity_data['end_at'] = $date;


if (str_word_count($subject) > 0) {
    $activity_data['activity_title'] = $subject;
} else {
    $activity_data['activity_title'] = _("No Subject");
}

$activity_data['contact_id'] = $contact_id;
$activity_data['activity_status'] = 'o';
//$activity_data['activity_description'] = $body;

preg_match_all("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $tos, $rcpts);

$activity_data['activity_description'] = $body;
$activity_id = add_activity($con, $activity_data);

$i=0;
while($one_to = $rcpts[0][$i]) {
    // if ($one_to == $email_addr) next;

    $i++;
    $to_sql = $con->qstr($one_to);

    $sql = "SELECT  contact_id, company_id
            FROM contacts
            WHERE email like " . $to_sql . " limit 1";
    $rst = $con->execute($sql);

    if ($rst && (!$rst->EOF)) {
        $contact_id = $rst->fields['contact_id'];
    } else {
$company_id = 1;
$contact_info['company_id'] = 1;
$contact_info['address_id'] = 1;
$contact_info['home_address_id'] = 1;
$contact_info['last_name'] = $one_to;
$contact_info['first_names'] = '(Email)';
$contact_info['email'] = $one_to;
$contact_info['email_status'] = 'a';

$new_contact = add_update_contact($con, $contact_info);

$contact_id  = $new_contact['contact_id'];
add_activity_participant($con, $activity_id, $contact_id);
}

$mail->removeMessage($messageNum);
}
}
$con->close();

/**
 * $Log: logger.php,v $
 * Revision 1.4  2008/01/30 21:31:29  gpowers
 * - updated to use Zend Framework
 * - still in development
 *
 * Revision 1.3  2006/01/02 23:02:14  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.2  2005/02/10 14:40:03  maulani
 * - Set last modified info when creating activities
 *
 * Revision 1.1  2004/11/16 00:04:37  gpowers
 * - email logging script (in development)
 *
 *
 */
?>