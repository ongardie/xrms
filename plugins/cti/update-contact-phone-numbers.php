<?php
/**
 * XRMS CTI Plugin - Update Contact Phone Numbers
 * Copyright (c) 2004 Glenn Powers <glenn@net127.com>
 * Licensed under the GNU GPL v2
 *
 * $Id: update-contact-phone-numbers.php,v 1.2 2006/01/02 23:52:14 vanmer Exp $
 */

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

//set target and see if we are logged in
$session_user_id = session_check();

//connect to the database
$con = get_xrms_dbconnection();

//uncomment the debug line to see what's going on with the query
//$con->debug = 1;

$sql = "select contact_id, work_phone
         from contacts";
$rst = $con->execute($sql);

if (($rst) && (!$rst->EOF)) {
    while (!$rst->EOF) {

        $sql3 = "SELECT * FROM contacts WHERE contact_id = '"
               . $rst->fields['contact_id'] . "'";
        $rst3 = $con->execute($sql3);
        
        $rec = array();
        $numbers = explode('x', $rst->fields['work_phone'], 2);
        $rec['work_phone'] = preg_replace("/[^\d]/", '', $numbers[0]);
        $rec['work_phone_ext'] = preg_replace("/[^\d]/", '', $numbers[1]);

        echo "Old: " . $rst->fields['work_phone'] . "; New: " . $rec['work_phone'] 
            . " Ext: " . $rec['work_phone_ext'] . "<br />";
            
        $tbl = 'contacts';
        $upd = $con->GetUpdateSQL($rst3, $rec, false, get_magic_quotes_gpc());
        $con->execute($upd);

        // add_audit_item($con, $session_user_id, 'updated', 'contact_work_phone_ext', $contact_id, 1);
        

    $rst->MoveNext();
    }
}

$con->close();

echo "<br /><h2>" . _("Complete") . "</h2>";

/**
 * $Log: update-contact-phone-numbers.php,v $
 * Revision 1.2  2006/01/02 23:52:14  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.1  2005/04/26 17:32:14  gpowers
 * - scripts to separate extensions from phone numbers in contacts and companies table
 *   - in contacts table, extension are placed in NEW work_phone_ext column
 *   - in companies table, extensions are placed in profile
 *     - companies do not normally use extensions in their phone numbers
 *
 *
 */
?>