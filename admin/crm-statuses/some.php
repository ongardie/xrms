<?php
/**
 * Show and edit the details for all crm statuses
 *
 * $Id: some.php,v 1.12 2006/12/04 20:07:57 jnhayart Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$con = get_xrms_dbconnection();

$sql = "select * from crm_statuses where crm_status_record_status = 'a' order by sort_order";
$rst = $con->execute($sql);

$cnt = 1;
$maxcnt = $rst->rowcount();

if ($rst) {
    while (!$rst->EOF) {
	    $sort_order = $rst->fields['sort_order'];
	  
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content>' . $rst->fields['crm_status_short_name'] . '</td>';
        $table_rows .= '<td class=widget_content><a href=one.php?crm_status_id=' . $rst->fields['crm_status_id'] . '>' . $rst->fields['crm_status_pretty_name'] . '</a></td>';
        $table_rows .= '<td class=widget_content>' . $rst->fields['crm_status_pretty_plural'] . '</td>';
        $table_rows .= '<td class=widget_content>' . $rst->fields['crm_status_display_html'] . '</td>';        
	//sets up ordering links in the table
	$table_rows .= '<td class=widget_content>';
	if ($sort_order != $cnt) {
		$table_rows .= '<a href="' . $http_site_root
			. '/admin/sort.php?direction=up&sort_order='
			. $sort_order . '&table_name=crm_status'
			. '&return_url=/admin/crm-statuses/some.php">'._("up").'</a> &nbsp; ';
	}
	if ($sort_order != $maxcnt) {
		$table_rows .= '<a href="' . $http_site_root
			. '/admin/sort.php?direction=down&sort_order='
			. $sort_order . '&table_name=crm_status'
			. '&return_url=/admin/crm-statuses/some.php">'._("down").'</a>';
	}
	$table_rows .= '</td>';
	
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = _("Manage CRM Statuses");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=5><?php echo _("Existing CRM Statuses"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Short Name"); ?></td>
                <td class=widget_label><?php echo _("Full Name"); ?></td>
                <td class=widget_label><?php echo _("Full Plural Name"); ?></td>
                <td class=widget_label><?php echo _("Display HTML"); ?></td>
                
		<td class=widget_label width=15%><?php echo _("Move"); ?></td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <form action=add-2.php method=post>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Add New CRM Status"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=crm_status_short_name size=10></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=crm_status_pretty_name size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=crm_status_pretty_plural size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text name=crm_status_display_html size=30></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Add"); ?>"></td>
            </tr>
        </table>
        </form>

    </div>
</div>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.12  2006/12/04 20:07:57  jnhayart
 * cosmetics modif
 *
 * Revision 1.11  2006/01/02 21:48:01  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.10  2005/10/06 04:30:06  vanmer
 * - updated log entries to reflect addition of code by Diego Ongaro at ETSZONE
 *
 * Revision 1.9  2005/10/04 23:21:43  vanmer
 * Patch to allow sort_order on the company CRM status field, thanks to Diego Ongaro at ETSZONE
 *
 * Revision 1.8  2004/11/26 17:18:51  braverock
 * - localized strings for i18n
 *
 * Revision 1.7  2004/07/16 23:51:36  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.6  2004/07/16 13:51:57  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.5  2004/06/14 22:14:42  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.4  2004/04/16 22:18:25  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.3  2004/03/19 03:46:33  braverock
 * - reversed { on line 24
 *   - patch committed by Jake Starbile ( zathras66 )
 *
 * Revision 1.2  2004/02/22 17:05:09  braverock
 * - changed to show display_html
 *   Resolves SF bug 881277
 * - add phpdoc
 *
 */
?>