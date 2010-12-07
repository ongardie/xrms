<?php
/**
 * Insert a new opportunity status into the database
 *
 * $Id: new-2.php,v 1.13 2010/12/07 22:34:41 gopherit Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$opportunity_status_short_name = $_POST['opportunity_status_short_name'];
$opportunity_status_pretty_name = $_POST['opportunity_status_pretty_name'];
$opportunity_status_pretty_plural = $_POST['opportunity_status_pretty_plural'];
$opportunity_status_display_html = $_POST['opportunity_status_display_html'];
$opportunity_status_long_desc = $_POST['opportunity_status_long_desc'];
$status_open_indicator = $_POST['status_open_indicator'];
$opportunity_type_id = (int)$_POST['opportunity_type_id'];
$sort_order             = $_POST['sort_order'];

// Only insert the record if we have at least a short name or pretty name
// @TODO: Should send a message to the user here giving them a clue if we are
// doing nothing
if ((strlen($opportunity_status_short_name) > 0) OR (strlen($opportunity_status_pretty_name))) {

    //set defaults if we didn't get everything we need
    if (strlen($opportunity_status_pretty_name) == 0) {
        $opportunity_status_pretty_name = $opportunity_status_short_name;
    }
    if (strlen($opportunity_status_pretty_plural) == 0) {
        $opportunity_status_pretty_plural = $opportunity_status_pretty_name;
    }
    if (strlen($opportunity_status_display_html) == 0) {
        $opportunity_status_display_html = $opportunity_status_pretty_name;
    }

    $con = get_xrms_dbconnection();

    // It is useful to have $sort_order as a string so we can validate it here
    if ($sort_order == '') {
        // Get the last sort_order value so we can put the new record at the bottom of the list
        $sql = "SELECT sort_order
                FROM opportunity_statuses
                WHERE opportunity_status_record_status='a'
                AND opportunity_type_id = $opportunity_type_id
                ORDER BY sort_order DESC";
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler($con, $sql);
        } else {
            $sort_order = $rst->fields['sort_order'] + 1;
            $rst->close();
        }
    } else {
        $sort_order = (int)$sort_order;
    }

    //save to database
    $rec = array();
    $rec['opportunity_status_short_name'] = $opportunity_status_short_name;
    $rec['opportunity_status_pretty_name'] = $opportunity_status_pretty_name;
    $rec['opportunity_status_pretty_plural'] = $opportunity_status_pretty_plural;
    $rec['opportunity_status_display_html'] = $opportunity_status_display_html;
    $rec['opportunity_status_long_desc'] = $opportunity_status_long_desc;
    $rec['status_open_indicator'] = $status_open_indicator;
    $rec['opportunity_type_id'] = $opportunity_type_id;
    $rec['sort_order'] = $sort_order;

    $tbl = 'opportunity_statuses';
    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $con->close();
}
// Go back to the main opportunity status page after updating
header("Location: some.php?opportunity_type_id=$opportunity_type_id");

/**
 * $Log: new-2.php,v $
 * Revision 1.13  2010/12/07 22:34:41  gopherit
 * The user can now specify the sort order of a newly created workflow status.  XRMS will also attempt to assign some reasonable values to pretty_name; if no sufficient input is supplied, no record will be inserted in the database.
 *
 * Revision 1.12  2010/11/30 21:40:40  gopherit
 * Removed trailing whitespace.
 *
 * Revision 1.11  2010/11/30 21:32:57  gopherit
 * Code cleanup
 *
 * Revision 1.10  2006/01/10 08:16:55  gpowers
 * - added limiting of opp. statuses by opp. type
 *
 * Revision 1.9  2006/01/02 21:59:08  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.8  2004/07/16 23:51:37  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.7  2004/07/15 22:13:43  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.6  2004/06/14 22:36:43  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.5  2004/06/03 16:13:22  braverock
 * - add functionality to support workflow and activity templates
 * - add functionality to support changing sort order
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.4  2004/03/15 16:49:56  braverock
 * - add sort_order and open status indicator to opportunity statuses
 *
 * Revision 1.3  2004/01/25 18:39:40  braverock
 * - fixed insert bugs so long_desc will be disoplayed and inserted properly
 * - added phpdoc
 *
 */
?>