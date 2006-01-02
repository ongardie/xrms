<?php
/**
 * View an opportunity
 *
 * $Id: opportunity-view.php,v 1.10 2006/01/02 23:29:27 vanmer Exp $
 */

require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$opportunity_type_id = $_GET['opportunity_type_id'];

$con = get_xrms_dbconnection();

$sql = "select *
        from opportunity_statuses
        where opportunity_status_record_status = 'a'
          and opportunity_type_id = ".$con->qstr($opportunity_type_id, get_magic_quotes_gpc())."
        order by sort_order";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content>'. htmlspecialchars($rst->fields['opportunity_status_pretty_name']) . '</td><td class=widget_content>'. htmlspecialchars($rst->fields['opportunity_status_long_desc']) . '</td>';
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = _("View Opportunity Statuses");
start_page($page_title, $show_navbar = false);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Opportunity Statuses"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Name"); ?></td>
                <td class=widget_label><?php echo _("Description"); ?></td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        &nbsp;

    </div>

</div>

<?php

end_page();

/**
 * $Log: opportunity-view.php,v $
 * Revision 1.10  2006/01/02 23:29:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.9  2005/07/06 22:50:32  braverock
 * - add opportunity types
 *
 * Revision 1.8  2005/01/11 13:54:32  braverock
 * - fixed to show by sort_order
 *
 * Revision 1.7  2005/01/07 01:55:58  braverock
 * - remove navebar from pop-up window
 *
 * Revision 1.6  2004/07/20 19:38:31  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.5  2004/06/14 17:41:36  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, Concat and Date functions.
 *
 * Revision 1.4  2004/04/17 15:59:59  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.3  2004/04/16 22:22:41  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.2  2004/04/08 17:13:06  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>
