<?php
/**
 * import-companies.php - File importer for XRMS
 *
 * The three import-companies files in XRMS allow users or administrators
 * to import new companies and contacts into XRMS
 *
 * The first page, import-companies.php, displays several options that
 * will be common to all imported companies, such as source and initial status,
 * and allows the user to select the file to be imported,
 * and the delimiter to be used.
 *
 * @author Chris Woofter
 * @author Brian Peterson
 *
 * $Id: import-companies.php,v 1.7 2004/07/16 13:51:58 braverock Exp $
 */
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$page_title = _("Import");
start_page($page_title, true, $msg);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, false);
$rst->close();

$sql2 = "select crm_status_pretty_name, crm_status_id from crm_statuses where
         crm_status_record_status = 'a' order by crm_status_id";
$rst = $con->execute($sql2);
$crm_status_menu = $rst->getmenu2('crm_status_id', $crm_status_id, false);
$rst->close();

$sql2 = "select company_source_pretty_name, company_source_id from company_sources where
         company_source_record_status = 'a' order by company_source_pretty_name";
$rst = $con->execute($sql2);
$company_source_menu = $rst->getmenu2('company_source_id', $company_source_id, false);
$rst->close();

$sql2 = "select category_pretty_name, category_id from categories where
         category_record_status = 'a' order by category_pretty_name";
$rst = $con->execute($sql2);
$category_menu = $rst->getmenu2('category_id', $category_id, true);
$rst->close();

$sql2 = "select industry_pretty_name, industry_id from industries where
         industry_record_status = 'a' order by industry_pretty_name";
$rst = $con->execute($sql2);
$industry_menu = $rst->getmenu2('industry_id', $industry_id, false);
$rst->close();

$sql = "select account_status_pretty_name, account_status_id from account_statuses where
        account_status_record_status = 'a'";
$rst = $con->execute($sql);
$account_status_menu = $rst->getmenu2('account_status_id', $account_status_id, false);
$rst->close();

$sql = "select rating_pretty_name, rating_id from ratings where rating_record_status = 'a'";
$rst = $con->execute($sql);
$rating_menu = $rst->getmenu2('rating_id', $rating_id, false);
$rst->close();

$con->close();

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=35% valign=top>

        <form action="import-companies-2.php" method=post enctype="multipart/form-data">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Import Companies"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("File"); ?></td>
                <td class=widget_content_form_element><input type=file name=file1></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Field Delimiter"); ?></td>
                <td class=widget_content_form_element>
                    <input type=radio name=delimiter value=comma checked><?php echo _("comma"); ?>
                    <input type=radio name=delimiter value=tab><?php echo _("tab"); ?>
                    <input type=radio name=delimiter value=pipe><?php echo _("pipe"); ?>
                    <input type=radio name=delimiter value='semi-colon'><?php echo _("semi-colon"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("File Format"); ?></td>
                <td class=widget_content_form_element><select name="file_format">
<?php
if ($handle = opendir('.')) {
   while (false !== ($filename = readdir($handle))) {
      if (preg_match("/import-template/i", $filename)) {
         preg_match("/(import-template-)([^\.]+)(.php)/i",$filename,$format_name);
         echo "<option value=\"" . $format_name[2] . "\">$format_name[2]</option>";
         };
      };
};
?>
                </select>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Acct. Owner"); ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("CRM Status"); ?></td>
                <td class=widget_content_form_element><?php  echo $crm_status_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company Source"); ?></td>
                <td class=widget_content_form_element><?php  echo $company_source_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Category"); ?></td>
                <td class=widget_content_form_element><?php  echo $category_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Industry"); ?></td>
                <td class=widget_content_form_element><?php  echo $industry_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Account Status"); ?></td>
                <td class=widget_content_form_element><?php  echo $account_status_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Rating"); ?></td>
                <td class=widget_content_form_element><?php  echo $rating_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Import"); ?>"></td>
            </tr>
        </table>
        </form>

        </td>
        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=63% valign=top>

        </td>
    </tr>
</table>

<?php end_page();
/**
 * $Log: import-companies.php,v $
 * Revision 1.7  2004/07/16 13:51:58  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.6  2004/04/19 14:21:54  braverock
 * - add additional look-ups and tests on import
 * - improve error reporting
 * - revise process to use templates
 *   - makes use of material from SF patch 926925 by Glenn Powers
 *
 * Revision 1.5  2004/04/16 22:18:25  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.4  2004/04/09 22:08:30  braverock
 * - allow import of all fields in the XRMS database
 * - integrated patches provided by Olivier Colonna of Fontaine Consulting
 *
 * Revision 1.3  2004/03/07 14:37:45  braverock
 * - make Industry a required field on import
 *   credit to tjm-fc for suggesting this in response to SF bug 904296
 *
 * Revision 1.2  2004/02/04 18:39:58  braverock
 * - major update to import functionality
 * - add phpdoc
 *
 */
?>