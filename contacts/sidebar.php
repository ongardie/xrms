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
 * $Id: sidebar.php,v 1.13 2004/07/27 20:39:57 neildogg Exp $
 */

if ( !defined('IN_XRMS') )
{
  die(_("Hacking attempt"));
  exit;
}

//add contact information block on sidebar
$contact_block = '<table class=widget cellspacing=1 width="100%">
    <tr>
        <td class=widget_header colspan=5>' . _("Contact Information") . '</td>
    </tr>'."\n";

if ($contact_id) {
    $sql = "SELECT first_names, last_name, work_phone, address_id, email, cell_phone, company_id
            FROM contacts
            WHERE contact_id=$contact_id";

    $rst = $con->execute($sql);
    
    $sql = "SELECT default_primary_address
            FROM companies
            WHERE company_id=" . $rst->fields['company_id'];

    $rst2 = $con->execute($sql);
    
}

if ($rst && $rst->RecordCount()>=1) {

    $contact_block .= "\n\t<tr>\n\t\t<td class=widget_content>"
                    . '<a href="../contacts/one.php?contact_id=' . $contact_id . '">'
                    . $rst->fields['first_names'] . " " . $rst->fields['last_name'] . "</a><br>"
                    . '<a href="../companies/one.php?company_id=' . $rst->fields['company_id'] . '">'
                    . $rst->fields['company_name'] . "</a></td>\n\t</tr>";
    if($rst->fields['address_id'] != $rst2->fields['default_primary_address']) {
        $contact_block .= "\n\t<tr>\n\t\t<td class=widget_content>"
                        . get_formatted_address ($con, $rst->fields['address_id'])
                        . "</td>\n\t</tr>";
    }

    if ($rst->fields['cell_phone'] or $rst->fields['work_phone'] or $rst->fields['home_phone']) {
        $contact_block .= "<tr><td class=widget_content>";
    }
    
    if ($rst->fields['cell_phone']) {
        $contact_block .= _("Cell Phone") . ": <strong>"
                        . get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['cell_phone']) 
                        . "</strong><br>";
    }
    
    if ($rst->fields['work_phone']) {
        $contact_block .= _("Work Phone") . ": <strong>"
                        . get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['work_phone']) 
                        . "</strong><br>";
    }

    if ($rst->fields['cell_phone'] or $rst->fields['work_phone']) {
        $contact_block .= "</td>\n\t</tr>";
    }

    if ($rst->fields['email']) {
        $contact_block .= "<tr>\n\t\t<td class=widget_content>"
                        . "<a href=\"mailto:" . $rst->fields['email'] . "\">"
                        . $rst->fields['email'] . "</a></td>\n\t</tr>";
    }

    $rst->close();

} else {
    $contact_block .= "\n\t<tr>\n\t\t<td class=widget_content colspan=5>"
                    . _("No Contact Selected.")
                    . "&nbsp; </td>\n\t</tr>";
}
$contact_block .= "\n</table>";

/**
 * $Log: sidebar.php,v $
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
