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
 * $Id: sidebar.php,v 1.1 2004/06/03 16:26:14 braverock Exp $
 */

//add contact information block on sidebar
$contact_block = '<table class=widget cellspacing=1 width="100%">
    <tr>
        <td class=widget_header colspan=5>Contact Information</td>
    </tr>'."\n";

$sql = "select
        first_names, last_name, work_phone, address_id
        from contacts
        where
        contact_id=$contact_id";

$rst = $con->execute($sql);

if ($rst) {

    $contact_block .= "\n\t<tr>\n\t\t<td class=widget_content>"
                    . $rst->fields['first_names'] . " " . $rst->fields['last_name'] . "</td>\n\t</tr>"
                    . "\n\t<tr>\n\t\t<td class=widget_content>"
                    . get_formatted_address ($con, $rst->fields['address_id'])
                    . "</td>\n\t</tr>"
                    . "<tr><td class=widget_content>"
                    . $rst->fields['work_phone'] . "&nbsp; </td>\n\t</tr>";
    $rst->close();

} else {
    // database error, return some useful information.
    ob_start();
    db_error_handler ($con,$sql);
    $contact_block .= ob_get_contents();
    ob_end_clean();
}
$contact_block .= "\n</table>";

/**
 * $Log: sidebar.php,v $
 * Revision 1.1  2004/06/03 16:26:14  braverock
 * - add sidebar functionality to activities
 *   - modified from functionality contributed by Brad Marshall
 *
 */
?>