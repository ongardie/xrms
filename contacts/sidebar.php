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
 * $Id: sidebar.php,v 1.6 2004/06/28 13:49:52 gpowers Exp $
 */

//add contact information block on sidebar
$contact_block = '<table class=widget cellspacing=1 width="100%">
    <tr>
        <td class=widget_header colspan=5>Contact Information</td>
    </tr>'."\n";

$sql = "select
        first_names, last_name, work_phone, address_id, email
        from contacts
        where
        contact_id=$contact_id";

$rst = $con->execute($sql);

if (!$rst->EOF) {

    $contact_block .= "\n\t<tr>\n\t\t<td class=widget_content>"
                    . '<a href="../contacts/one.php?contact_id=' . $contact_id . '">'
                    . $rst->fields['first_names'] . " " . $rst->fields['last_name'] . "</a></td>\n\t</tr>"
                    . "\n\t<tr>\n\t\t<td class=widget_content>"
                    . get_formatted_address ($con, $rst->fields['address_id'])
                    . "</td>\n\t</tr>";

    if ($rst->fields['work_phone']) {
        $contact_block .= "<tr><td class=widget_content>Work Phone: <strong>"
                        . $rst->fields['work_phone'] . "</strong></td>\n\t</tr>";
    }

    if ($rst->fields['email']) {
        $contact_block .= "<tr>\n\t\t<td class=widget_content>"
                        . "<a href=\"mailto:" . $rst->fields['email'] . "\">"
                        . $rst->fields['email'] . "</a></td>\n\t</tr>";
    }

    $rst->close();

} else {
    $contact_block .= "\n\t<tr>\n\t\t<td class=widget_content colspan=5>"
                    . "No Contact Selected."
                    . "&nbsp; </td>\n\t</tr>";
}
$contact_block .= "\n</table>";

/**
 * $Log: sidebar.php,v $
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
