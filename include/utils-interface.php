<?php
/**
 * Common user interface functions file.
 *
 *
 * $Id: utils-interface.php,v 1.7 2004/03/20 20:03:24 braverock Exp $
 */

require_once ($include_locations.'plugin.php');

/**
 * function status_msg
 *
 * This utility function will take a status code and turn it into a status message.
 */
function status_msg($msg) {
    switch ($msg) {
        case 'noauth':
            return "We could not authenticate you.  Please try again.";
            break;
        case 'saved':
            return "Changes saved.";
            break;
        case 'activity_added':
            return "Activity added.";
            break;
        case 'contact_added':
            return "Contact added.";
            break;
        case 'company_added':
            return "Company added.";
            break;
    }
} //end status_msg fn

/**
 * function start_page
 *
 * This function is called to set up the page structure for all XRMS pages
 */
function start_page($page_title = '', $show_navbar = true, $msg = '') {

    global $page_title_height;
    global $http_site_root;
    $session_username = $_SESSION['username'];

    $msg = status_msg($msg);

    $stylesheet = "'$http_site_root/stylesheet.css'";

    echo <<<EOQ
    <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
    <html>
    <head>
    <title>$page_title</title>
    <link rel=stylesheet href=$stylesheet>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    </head>
    <body>
    <table class=page_header cellspacing=1 width='100%'>
        <tr>
            <td class=page_title height=$page_title_height valign=bottom colspan=3>$page_title</td>
        </tr>
EOQ;



    if ($show_navbar) echo <<<EOQ

        <tr>
            <td>
            <table class=navbar cellspacing=0 width='100%'>
            <tr>
            <td class=navbar width='80%'>
                <a href="$http_site_root/private/home.php">Home</a> &bull;
                <a href="$http_site_root/companies/some.php">Companies</a> &bull;
                <a href="$http_site_root/contacts/some.php">Contacts</a> &bull;
                <a href="$http_site_root/campaigns/some.php">Campaigns</a> &bull;
                <a href="$http_site_root/opportunities/some.php">Opportunities</a> &bull;
                <a href="$http_site_root/cases/some.php">Cases</a> &bull;
                <a href="$http_site_root/files/some.php">Files</a> &bull;
EOQ;
            //place the munu_line hook before Reports and Adminstration link
            do_hook ('menuline');

            echo <<<EOQ

                <a href="$http_site_root/reports/">Reports</a> &bull;
                <a href="$http_site_root/admin/routing.php">Administration</a>
            </td>
            <td class=navbar align=center>&nbsp;</td>
            <td class=navbar align=right>
                Logged in as: $session_username &bull; <a href="$http_site_root/logout.php">Logout</a>
            </td>
            </tr>
            </table>
            </td>
        </tr>
EOQ;

    echo <<<EOQ
    </table>

EOQ;

    if (strlen($msg) > 0) echo <<<EOQ
        <center><table class=msg border=0 width='80%'><tr><td class=msg>{$msg}</td></tr></table></center>
EOQ;
} // end start_page fn

/**
 * function end_page
 *
 * This function closes off the page structure.
 *
 * This function also contains the end_page hook to allow
 * for adding stuff to the page footer via a hook.
 *
 * Any common page footer would end this.
 */
function end_page() {

    /**
     * place the end_page hook before we close the body and html
     * I don't think any of the tables should still be open, so a
     * hook writer would need to add thier own structure.
     */
    do_hook ('end_page');

    echo "
    </body>
    </html>\n";

} //end end_page fn

/**
 * build salutation menu
 *
 * @todo localize this using gettext, not included strings.
 *
 * @param integer $salutation
 * @return string $salutation_menu
 */
function build_salutation_menu($salutation) {

    require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

    // global $salutation_array;

    $salutation_menu  = "<select name=salutation>";
    $salutation_menu .= "\n<option value=0 > ";

    for ($i = 0; $i < sizeof($salutation_array); $i++) {
        $salutation_menu .= "\n<option value='" . $salutation_array[$i] . "'";
        if ($salutation == $salutation_array[$i]) {
            $salutation_menu .= " selected";
        }
        $salutation_menu .= ">" . $salutation_array[$i];
    }

    $salutation_menu .= "\n</select>";

    return $salutation_menu;
} //end build_salutation_menu fn

/**
 * $Log: utils-interface.php,v $
 * Revision 1.7  2004/03/20 20:03:24  braverock
 * - add code to enable plugins
 * - add menuline and end_page hooks to start
 *
 * Revision 1.6  2004/03/12 15:46:52  maulani
 * Temporary change for use until full access control is implemented
 * - Block non-admin users from the administration screen
 * - Allow all users to modify their own user record and password
 * - Add phpdoc
 *
 * Revision 1.5  2004/02/16 20:14:11  maulani
 * Close table tag when nav bar not used
 *
 * Revision 1.4  2004/01/26 19:23:39  braverock
 * - moved interface functions from utils-misc.php
 *
 */
?>