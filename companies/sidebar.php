<?php
/**
 * Company Information Sidebar
 *
 * Include this file anywhere you want to show a summary of the company information
 *
 * @param integer $company_id The company_id should be set before including this file
 *
 * @author Brad Marshall
 * - moved to seperate include file and extended by Brian Perterson
 *
 * $Id: sidebar.php,v 1.3 2004/06/03 18:39:40 gpowers Exp $
 */

// add company information block on sidebar

$company_block = '<table class=widget cellspacing=1 width="100%">
    <tr>
        <td class=widget_header colspan=5>Company Information</td>
    </tr>'."\n";


$sql = "select company_name, phone,
        phone2, fax, url, default_primary_address
        from companies
        where company_id=$company_id";

$rst = $con->execute($sql);

if ($rst) {

    $phone = $rst->fields['phone'];
    $phone2 = $rst->fields['phone2'];
    $fax = $rst->fields['fax'];
    $url = $rst->fields['url'];

    // this phone number formatting will not be appropriate for non-US phones...
    // $phone = "(" . substr($phone, 0, 3) . ") " . substr($phone, 3, 3) . "-" . substr($phone, 6, 4);
    // $phone2 = " &nbsp; (" . substr($phone2, 0, 3) . ") " . substr($phone2, 3, 3) . "-" . substr($phone2, 6, 4);
    // $fax = "(" . substr($fax, 0, 3) . ") " . substr($fax, 3, 3) . "-" . substr($fax, 6, 4);

    $company_block .= "\n\t<tr>\n\t\t<td class=widget_content>"
                    . $rst->fields['company_name'] . "</td>\n\t</tr>"
                    . "\n\t<tr>\n\t\t<td class=widget_content>"
                    . get_formatted_address ($con, $rst->fields['default_primary_address'])
                    . "</td>\n\t</tr>";


    if ($phone) {
        $company_block .= "\n\t<tr>\n\t\t<td class=widget_content>"
                        . "Phone: " . $phone . "&nbsp;" . $phone2 . "</td>\n\t</tr>";
    };

    if ($fax) {
        $company_block .= "\n\t<tr>\n\t\t<td class=widget_content>"
                        . "Fax: " . $fax . "</td>\n\t</tr>";
    }

    if ($url) {
        $company_block .= "\n\t<tr>\n\t\t<td class=widget_content>"
                    . "<a href=\"" . $url . "\" target=\"_new\">"
                    . $url . "</a></td>\n\t</tr>";
    }

    $rst->close();

} else {
    // database error, return some useful information.
    ob_start();
    db_error_handler ($con,$sql);
    $company_block .= ob_get_contents();
    ob_end_clean();
}

$company_block .= "\n</table>";

/**
 * $Log: sidebar.php,v $
 * Revision 1.3  2004/06/03 18:39:40  gpowers
 * Added Address, to be consistant with contacts sidebar.
 *
 * Revision 1.2  2004/06/03 17:09:28  gpowers
 * - only display phone/fax/url if they exist
 * - make url a link and open it in a new window (on click)
 *
 * Revision 1.1  2004/06/03 16:26:05  braverock
 * - add sidebar functionality to activities
 *   - modified from functionality contributed by Brad Marshall
 *
 */
?>
