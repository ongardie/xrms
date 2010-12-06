<?php
/**
 * Insert a new opportunity type the database
 *
 * $Id: new-2.php,v 1.3 2010/12/06 22:18:55 gopherit Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$opportunity_type_short_name = $_POST['opportunity_type_short_name'];
$opportunity_type_pretty_name = $_POST['opportunity_type_pretty_name'];
$opportunity_type_pretty_plural = $_POST['opportunity_type_pretty_plural'];
$opportunity_type_display_html = $_POST['opportunity_type_display_html'];

// Only insert the record if we have at least a short or pretty name
// @TODO: Should send a message to the user here giving them a clue if we are
// doing nothing
if ((strlen($opportunity_type_short_name) > 0) OR (strlen($opportunity_type_pretty_name) > 0)) {

    // Set defaults if we didn't get everything we need
    if (strlen($opportunity_type_pretty_name) == 0) {
        $opportunity_type_pretty_name = $opportunity_type_short_name;
    }
    if (strlen($opportunity_type_pretty_plural) == 0) {
        $opportunity_type_pretty_plural = $opportunity_type_pretty_name;
    }
    if (strlen($opportunity_type_display_html) == 0) {
        $opportunity_type_display_html = $opportunity_type_pretty_name;
    }

    $con = get_xrms_dbconnection();

    //save to database
    $rec = array();
    $rec['opportunity_type_short_name'] = $opportunity_type_short_name;
    $rec['opportunity_type_pretty_name'] = $opportunity_type_pretty_name;
    $rec['opportunity_type_pretty_plural'] = $opportunity_type_pretty_plural;
    $rec['opportunity_type_display_html'] = $opportunity_type_display_html;

    $tbl = "opportunity_types";
    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $con->close();

}

header("Location: some.php");

/**
 * $Log: new-2.php,v $
 * Revision 1.3  2010/12/06 22:18:55  gopherit
 * Added input validation to ensure that when a new workflow type is created, it has at least a *_type_short_name or a*_type_pretty_name.  If neither is provided, no record will be inserted in the database.
 *
 * Revision 1.2  2006/01/02 21:59:08  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.1  2005/07/06 21:08:57  braverock
 * - Initial Revision of Admin screens for opportunity types
 *
 * Revision 1.5  2004/07/16 23:51:35  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.4  2004/07/15 21:39:09  introspectshun
 * - Now passes a table name instead of a recordset opportunity GetInsertSQL
 *
 * Revision 1.3  2004/06/14 21:48:25  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.2  2004/03/21 23:55:51  braverock
 * - fix SF bug 906413
 * - add phpdoc
 *
 */
?>