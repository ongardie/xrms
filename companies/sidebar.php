<?php

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

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
 * $Id: sidebar.php,v 1.9 2004/07/21 21:00:41 neildogg Exp $
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

  if ( !$rst->EOF ) {

    $url = $rst->fields['url'];

    $company_block .= "\n\t<tr>\n\t\t<td class=widget_content>"
                   . '<a href="../companies/one.php?company_id=' . $company_id . '">'
                   . $rst->fields['company_name'] . "</a></td>\n\t</tr>"
                   . "\n\t<tr>\n\t\t<td class=widget_content>"
                   . get_formatted_address ($con, $rst->fields['default_primary_address'])
                   . "</td>\n\t</tr>";

    if ($rst->fields['phone']) {
        $company_block .= "\n\t<tr>\n\t\t<td class=widget_content>"
                        . _("Phone") . ": " . get_formatted_phone($con, $rst->fields['default_primary_address'], $rst->fields['phone'])
                        . "&nbsp;" . get_formatted_phone($con, $rst->fields['default_primary_address'], $rst->fields['phone2'])
                        . "</td>\n\t</tr>";
    };

    if ($rst->fields['fax']) {
        $company_block .= "\n\t<tr>\n\t\t<td class=widget_content>"
                        . _("Fax") . ": " . get_formatted_phone($con, $rst->fields['default_primary_address'], $rst->fields['fax']) . "</td>\n\t</tr>";
    }

    if ($rst->fields['url']) {
        $company_block .= "\n\t<tr>\n\t\t<td class=widget_content>"
	                   . "<a href=\"" . $url . "\" target=\"_new\">"
                       . $url . "</a></td>\n\t</tr>";
    }

  } // if ( !$rst->EOF ) ...

    $rst->close();

} else {

    // database error, return some useful information.
    ob_start();
    db_error_handler ($con,$sql);
    $company_block .= ob_get_contents();
    ob_end_clean();

} // if ($rst) ...

$company_block .= "\n</table>";

/**
 * $Log: sidebar.php,v $
 * Revision 1.9  2004/07/21 21:00:41  neildogg
 * - Added get_formatted_phone
 *
 * Revision 1.8  2004/07/21 19:17:57  introspectshun
 * - Localized strings for i18n/l10n support
 *
 * Revision 1.7  2004/07/21 11:55:06  cpsource
 * - Define $url
 *   Fix get of database to check for any records found.
 *
 * Revision 1.6  2004/07/14 14:49:26  cpsource
 * - All sidebar.php's now support IN_XRMS security feature.
 *
 * Revision 1.5  2004/06/28 13:48:11  gpowers
 * - made company name a link to company page
 *
 * Revision 1.4  2004/06/04 16:42:04  gpowers
 * - removed reassignment of result fields to new var for consistancy with the
 *     rest of XRMS.
 * - removed phone number formatting code, which was commented out.
 *     formatting code should be global, if at all.
 *
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
