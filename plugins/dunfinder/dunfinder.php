<?php
/**
 * Dial-Up Number Finder (dunfinder) XRMS Plugin
 * Copyright (c) 2004 The XRMS Project Team and
 * Copyright (c) 2004 Glenn Powers <glenn@net127.com>
 *
 * $Id: dunfinder.php,v 1.2 2006/01/02 23:54:07 vanmer Exp $
 */

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

//set target and see if we are logged in
$session_user_id = session_check();

$msg = $_GET['msg'];
$query = $_GET['query'];
$type = $_GET['type'];

$monitor_url = "http://www.megapop.net/cgi-bin/restricted/conn_mon.cgi?";

//connect to the database
$con = get_xrms_dbconnection();

//uncomment the debug line to see what's going on with the query
// $con->debug = 1;

if ($type == 'state') {
   $sql = "SELECT * from dunfinder where state = '" . $query . "' order by city";
} elseif ($type == 'npa') {
   $sql = "SELECT * from dunfinder where npa = '" . $query . "' order by city";
} elseif ($type == 'all') {
   $sql = "SELECT * from dunfinder order by state, city, access_number";
};

$rst = $con->execute($sql);

if ($rst) {
$resultrows = "
        <table>
            <tr>
                <th align=left>State</th>
                <th align=left>City</th>
                <th align=left>Phone Number</th>
                <th align=left>POP</th>
                <th align=left>Monitor</th>
            </tr>
";
while (!$rst->EOF) {
    $resultrows .= "<tr><td>" . $rst->fields['state']
                 . "</td><td>" . $rst->fields['city']
                 . "</td><td><strong>" . $rst->fields['access_number'] ."</strong></td>"
                 . "</td><td>" . $rst->fields['pop_code'] 
                 . "</td><td><a href=\"" . $monitor_url
                 . $rst->fields['pop_code'] . "\">" . _("Monitor") . "</td>";
    $resultrows .= "</tr>";
    $rst->movenext();
}
    $resultrows .= "</table>";
}


//close the database connection, as we are done with it.
$con->close();

$page_title = "Dialup Number Finder";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

    <p>&nbsp;</p>

        <form action="dunfinder.php" method="get">
            Search for:
            <input type="text" name="query" value=<?php echo $query; ?> >
            <select name="type">
                <option value="state" <?php if ($type=='state') {echo "selected"; } ?> >by State</a>
                <option value="npa" <?php if ($type=='npa') {echo "selected"; } ?> >by Area Code</a>
                <option value="all" <?php if ($type=='all') {echo "selected"; } ?> >All Numbers</a>
            </select>
            <input class=button type=submit value="Go">
        </form>

    <p>&nbsp;</p>

    <?php echo $resultrows; ?>

    </div>
</div>

<?php

end_page();

/**
 * $Log: dunfinder.php,v $
 * Revision 1.2  2006/01/02 23:54:07  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.1  2004/08/03 17:28:59  gpowers
 * - This is a semi-standard Dialup Number Finder,
 *   modified to work inside of XRMS.
 *   - The table schema and insert.pl script are designed to work with
 *     POP lists from megapop.net (StarNet).
 *
 */
?>
