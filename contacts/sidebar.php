<?php
/**
 * Contact Information Sidebar
 *
 * Include this file anywhere you want to show a summary of the company information
 *
 * @param integer $contact_id The contact_id should be set before including this file
 *
 * @author Brad Marshall
 * - moved to seperate include file and extended by Brian Perterson
 *
 * $Id: sidebar.php,v 1.23 2006/01/02 23:00:00 vanmer Exp $
 */

$new_cell_phone         = isset($_GET['cell_phone']) ? $_GET['cell_phone'] : false;
$new_home_phone         = isset($_GET['home_phone']) ? $_GET['home_phone'] : false;
$new_work_phone         = isset($_GET['work_phone']) ? $_GET['work_phone'] : false;
$new_email         = isset($_GET['email']) ? $_GET['email'] : false;

if($new_cell_phone or $new_home_phone or $new_work_phone or $new_email) {
    $contact_id         = isset($_GET['contact_id']) ? $_GET['contact_id'] : false;
    if($contact_id) {
        // handle includes
        require_once('../include-locations.inc');

        require_once($include_directory . 'vars.php');
        require_once($include_directory . 'utils-interface.php');
        require_once($include_directory . 'utils-misc.php');
        require_once($include_directory . 'adodb/adodb.inc.php');
        require_once($include_directory . 'adodb/adodb-pager.inc.php');
        require_once($include_directory . 'adodb-params.php');

        $session_user_id = session_check();

        $con = get_xrms_dbconnection();
        //$con->debug = 1;

        $sql = "SELECT *
                FROM contacts
                WHERE contact_id=" . $contact_id;
        $rst = $con->execute($sql);

        $rec = array();
        if($new_cell_phone) {
            $rec['cell_phone'] = $new_cell_phone;
        }
        elseif($new_home_phone) {
            $rec['home_phone'] = $new_home_phone;
        }
        elseif($new_work_phone) {
            $rec['work_phone'] = $new_work_phone;
        }
        elseif($new_email) {
            $rec['email'] = $new_email;
        }

        $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
        $con->execute($upd);
        $con->close();

        header("Content-type: image/gif");
        @readfile("../img/pixel.gif");

        die();
    }
}

if ( !defined('IN_XRMS') )
{
  die(_("Hacking attempt"));
  exit;
}

$contact_block = '<script language="JavaScript" type="text/javascript">
    var temp = new Image();
    var newsrc;

    function updateVariable(text, variable, extra) {
        var new_variable = prompt(text, "");
        if(new_variable != null && new_variable != "") {
            newsrc = "' . $http_site_root . '/contacts/sidebar.php?" + variable + "=" + new_variable + "&" + extra;
            temp.src = newsrc;
            isChanged();
            document.forms[0].return_url.value = "' . current_page() . '";
            setTimeout("document.forms[0].submit()", 500);
        }
    }

    function isChanged() {
        if(!(temp.complete && (temp.src == newsrc))) {
            setTimeout("isChanged()", 500);
        }
    }

</script>';

//add contact information block on sidebar
$contact_block .= '<table class=widget cellspacing=1 width="100%">
    <tr>
        <td class=widget_header colspan=5>' . _("Contact Information") . '</td>
    </tr>'."\n";

if ( $contact_id ) {
    $sql = "SELECT ct.first_names, ct.last_name, ct.work_phone, ct.work_phone_ext, ct.address_id, ct.email, ct.cell_phone, ct.home_phone, ct.company_id
            FROM contacts ct
            WHERE ct.contact_id=$contact_id";

    $rst = $con->execute($sql);

    // database error ???
    if ( !$rst ) {
      // no result set - database error
      db_error_handler($con, $sql);
    }
    // any data ???
    if ( !$rst->EOF ) {
        // yes
        $sql = "SELECT default_primary_address, company_name
                FROM companies
                WHERE company_id=" . $rst->fields['company_id'];
        $rst2 = $con->execute($sql);
        if ( !$rst2 ) {
            db_error_handler($con, $sql);
        }
        if ( !$rst2->EOF ) {
            $default_primary_address = $rst2->fields['default_primary_address'];
            $company_name            = $rst2->fields['company_name'];
        } else {
            $default_primary_address = '';
            $company_name            = '';
        }
        $rst2->close();

        //
        // build contact_block
        //
        $contact_block .= "\n\t<tr>\n\t\t<td class=widget_content>"
                            . '<a href="../contacts/one.php?contact_id=' . $contact_id . '">'
                            . $rst->fields['first_names'] . " " . $rst->fields['last_name'] . "</a></td>\n\t</tr>";
        if ( $rst->fields['address_id'] != $default_primary_address ) {
            $contact_block .= "\n\t<tr>\n\t\t<td class=widget_content>"
                            . get_formatted_address ($con, $rst->fields['address_id'])
                            . "</td>\n\t</tr>";
        }

        if ($rst->fields['email']) {
            $contact_block .= "<tr>\n\t\t<td class=widget_content>"
                            . "<a href=\"mailto:" . $rst->fields['email'] . "\">"
                            . $rst->fields['email'] . "</a></td>\n\t</tr>";
        }
        else {
            $contact_block .= "<tr>\n\t\t<td class=widget_content>"
                            . "<a href=\"javascript: updateVariable('Enter Email', 'email', 'contact_id=" . $contact_id . "');\">"
                            . _("Enter Email Address") . "</a></td>\n\t</tr>";
        }

    $contact_block .= "<tr><td class=widget_content>";

        if (trim($rst->fields['work_phone'])) {
            $contact_block .= _("Work Phone") . ": <strong>"
                            . get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['work_phone']);
                                $work_phone_ext = $rst->fields['work_phone_ext'];
                                
            if ($work_phone_ext) {
                $contact_block .= '&nbsp;' . _("x") . $work_phone_ext;
            }
                $contact_block .= "</strong><br>";
        }
        else {
            $contact_block .= "<a href=\"javascript: updateVariable('"._("Enter Work Phone")."', 'work_phone', 'contact_id=" . $contact_id . "');\">"
                           . _("Enter Work Phone") . "</a><br>";
        }

        if (trim($rst->fields['cell_phone'])) {
            $contact_block .= _("Cell Phone") . ": <strong>"
                            . get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['cell_phone'])
                            . "</strong><br>";
        }
        else {
            $contact_block .= "<a href=\"javascript: updateVariable('"._("Enter Cell Phone")."', 'cell_phone', 'contact_id=" . $contact_id . "');\">"
                           . _("Enter Cell") . "</a><br>";
        }

        if (trim($rst->fields['home_phone'])) {
            $contact_block .= _("Home Phone") . ": <strong>"
                            . get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['home_phone'])
                            . "</strong><br>";
        }
        else {
            $contact_block .= "<a href=\"javascript: updateVariable('"._("Enter Home Phone")."', 'home_phone', 'contact_id=" . $contact_id . "');\">"
                           . _("Enter Home Phone") . "</a><br>";
        }

        $contact_block .= "</td>\n\t</tr>";


        $rst->close();

    } else {
        // no data
        $contact_block .= "\n\t<tr>\n\t\t<td class=widget_content colspan=5>"
                          . _("No Contact Selected.")
                          . "&nbsp; </td>\n\t</tr>";
    }
} // if ( $contact_id ) ...

$contact_block .= "\n</table>";

/**
 * $Log: sidebar.php,v $
 * Revision 1.23  2006/01/02 23:00:00  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.22  2005/04/26 18:11:35  gpowers
 * - don't display "x" if no extension
 *
 * Revision 1.21  2005/04/26 17:28:04  gpowers
 * - added Extension ("x") to contact work phone
 * - removed non-digits from phone numbers in edit-2's, new-2's
 * - updated work phone display to include Extension
 *
 * Revision 1.20  2005/04/22 22:14:36  ycreddy
 * Added trim to work, cell and fax fields
 *
 * Revision 1.19  2004/12/27 14:21:50  braverock
 * - localized untranslated strings
 *
 * Revision 1.18  2004/08/25 15:26:50  neildogg
 * - Fixed misnamed variables
 *
 * Revision 1.17  2004/08/05 15:10:52  neildogg
 * - Localized strings, removed company
 *  - Added update from sidebar to all phones and email
 *
 * Revision 1.16  2004/08/03 22:03:20  neildogg
 * - Malformed script tag, my bad
 *
 * Revision 1.15  2004/08/03 21:42:26  neildogg
 * - Sidebar variable changing
 *
 * Revision 1.14  2004/07/29 11:09:02  cpsource
 * - Fixed multiple errors that showed up because no one
 *   checked for uninitialized variables.
 *     default_primary_address and company_name were tried to retrieve
 *     from the wrong database.
 *   home_phone was retrieved but never used for anything, so it was removed
 *     from the sql statement
 *   awkward if else if else was removed
 *   Proper checks of sql statements were made to see if errors/records
 *     existed.
 *
 * Revision 1.13  2004/07/27 20:39:57  neildogg
 * - Removed unnecessary tr's
 *
 * Revision 1.12  2004/07/27 20:22:48  neildogg
 * - Stopped potentially repeating address
 *
 * Revision 1.11  2004/07/21 21:00:33  neildogg
 * - Added get_formatted_phone
 *
 * Revision 1.10  2004/07/21 15:20:04  introspectshun
 * - Localized strings for i18n/translation support
 * - Removed include of lang file
 *
 * Revision 1.9  2004/07/14 22:04:48  braverock
 * - added code to avoid object not defined error
 *
 * Revision 1.8  2004/07/14 14:49:27  cpsource
 * - All sidebar.php's now support IN_XRMS security feature.
 *
 * Revision 1.7  2004/07/04 11:34:58  metamedia
 * Now also displays cell phone number for contact.
 *
 * Revision 1.6  2004/06/28 13:49:52  gpowers
 * - made contact name a link to contact page
 *
 * Revision 1.5  2004/06/15 19:43:05  gpowers
 * - made Contact Work Phone <strong> so it's easier to see what number
 *   I should be calling next.
 *
 * Revision 1.4  2004/06/03 18:41:26  gpowers
 * Added "Work Phone: " for consistancy with companies sidebar
 *
 * Revision 1.3  2004/06/03 17:17:01  gpowers
 * - added email display, with link
 * - only show email or work_phone if they exist
 *
 * Revision 1.2  2004/06/03 16:57:23  gpowers
 * If no contact is associated with the activity,
 * return "No Contact Selected." instead of long error message.
 *
 * Revision 1.1  2004/06/03 16:26:14  braverock
 * - add sidebar functionality to activities
 *   - modified from functionality contributed by Brad Marshall
 *
 */
?>
