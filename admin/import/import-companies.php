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
 * $Id: import-companies.php,v 1.21 2006/03/22 20:58:45 ongardie Exp $
 */
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$page_title = _("Import");
if (!isset($msg)) { $msg=''; };
start_page($page_title, true, $msg);

$con = get_xrms_dbconnection();

$user_menu = get_user_menu($con, $session_user_id);

$crm_status_menu = build_crm_status_menu($con);

$sql2 = "select company_source_pretty_name, company_source_id from company_sources where
         company_source_record_status = 'a' order by company_source_pretty_name";
$rst = $con->execute($sql2);
$company_source_menu = translate_menu($rst->getmenu2('company_source_id', '', false));
$rst->close();

$sql2 = "select category_pretty_name, category_id from categories where
         category_record_status = 'a' order by category_pretty_name";
$rst = $con->execute($sql2);
$category_menu = $rst->getmenu2('category_id', '', true);
$rst->close();

$sql2 = "select industry_pretty_name, industry_id from industries where
         industry_record_status = 'a' order by industry_pretty_name";
$rst = $con->execute($sql2);
$industry_menu = translate_menu($rst->getmenu2('industry_id', '', false));
$rst->close();

$sql = "select account_status_pretty_name, account_status_id from account_statuses where
        account_status_record_status = 'a'";
$rst = $con->execute($sql);
$account_status_menu = translate_menu($rst->getmenu2('account_status_id', '', false));
$rst->close();

$sql = "select rating_pretty_name, rating_id from ratings where rating_record_status = 'a'";
$rst = $con->execute($sql);
$rating_menu = translate_menu($rst->getmenu2('rating_id', '', false));
$rst->close();

$con->close();

?>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td class="lcol" width="35%" valign="top">

        <form action="import-companies-2.php" method="post" enctype="multipart/form-data">
        <table class="widget" cellspacing="1">
            <tr>
                <td class="widget_header" colspan="2"><?php echo _("Import Companies"); ?></td>
            </tr>
            <tr>
                <td class="widget_label_right"><?php echo _("File"); ?></td>
                <td class="widget_content_form_element"><input type="file" name="file1"></td>
            </tr>
            <tr>
                <td class="widget_label_right"><?php echo _("Field Delimiter"); ?></td>
                <td class="widget_content_form_element">
                    <input type="radio" name="delimiter" value="comma" checked> <?php echo _("comma"); ?>
                    <input type="radio" name="delimiter" value="tab"> <?php echo _("tab"); ?>
                    <input type="radio" name="delimiter" value="pipe"> <?php echo _("pipe"); ?>
                    <input type="radio" name="delimiter" value="semi-colon"> <?php echo _("semi-colon"); ?></td>
            </tr>
            <tr>
                <td class="widget_label_right"><?php echo _("File Format"); ?></td>
                <td class="widget_content_form_element">
                <select name="file_format">
<?php
if ($handle = opendir('.')) {
   $opts = array();
   $mask = '/^(import-template-)([^\.]+)(.php)$/i';
   while (false !== ($filename = readdir($handle))) {
      if (preg_match($mask, $filename)) {
         preg_match($mask,$filename,$format_name);
         $opts[] = $format_name[2];
      }
   }
   if (!empty($opts)) {
       // DEFAULT needs to be first! [walter]
       echo '<option value="default">default</option>';
       natsort($opts);
       foreach ($opts as $opt) {
          if ( $opt != 'default' )  // skip it (default) when we find it here [walter]
             echo '<option value="' . $opt . '">' . $opt . '</option>';
       }
   }
};
?>
                </select>
                </td>
            </tr>
            <tr>
                <td class="widget_label_right"><?php echo _("Account Owner"); ?></td>
                <td class="widget_content_form_element"><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class="widget_label_right"><?php echo _("CRM Status"); ?></td>
                <td class="widget_content_form_element"><?php  echo $crm_status_menu; ?></td>
            </tr>
            <tr>
                <td class="widget_label_right"><?php echo _("Company Source"); ?></td>
                <td class="widget_content_form_element"><?php  echo $company_source_menu; ?></td>
            </tr>
            <tr>
                <td class="widget_label_right"><?php echo _("Category"); ?></td>
                <td class="widget_content_form_element"><?php  echo $category_menu; ?></td>
            </tr>
            <tr>
                <td class="widget_label_right"><?php echo _("Industry"); ?></td>
                <td class="widget_content_form_element"><?php  echo $industry_menu; ?></td>
            </tr>
            <tr>
                <td class="widget_label_right"><?php echo _("Account Status"); ?></td>
                <td class="widget_content_form_element"><?php  echo $account_status_menu; ?></td>
            </tr>
            <tr>
                <td class="widget_label_right"><?php echo _("Rating"); ?></td>
                <td class="widget_content_form_element"><?php  echo $rating_menu; ?></td>
            </tr>
            <tr>
                <td class="widget_content_form_element" colspan="2"><input class="button" type="submit" value="<?php echo _("Import"); ?>"></td>
            </tr>
        </table>
        </form>

        </td>
        <!-- gutter //-->
        <td class="gutter" width="2%">
        &nbsp;
        </td>
        <!-- right column //-->
        <td class="rcol" width="63%" valign="top">

        </td>
    </tr>
</table>

<?php end_page();
/**
 * $Log: import-companies.php,v $
 * Revision 1.21  2006/03/22 20:58:45  ongardie
 * - Changed (1) incorrect single quote to a double quote.
 *
 * Revision 1.20  2006/03/22 18:12:22  jswalter
 *  - modified the import filter SELECT list generator to have 'default' as the first item
 *  - placed double quotes around HTML tag attributes
 *
 * Revision 1.19  2006/01/02 21:50:29  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.18  2005/10/06 04:30:06  vanmer
 * - updated log entries to reflect addition of code by Diego Ongaro at ETSZONE
 *
 * Revision 1.17  2005/10/04 23:21:43  vanmer
 * Patch to allow sort_order on the company CRM status field, thanks to Diego Ongaro at ETSZONE
 *
 * Revision 1.16  2005/04/15 18:43:09  introspectshun
 * - Updated quoting for better readability
 *
 * Revision 1.14  2005/03/21 13:05:58  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.13  2004/11/26 15:55:45  braverock
 * - add translate_menu call to all drop-down menus for i18n
 * - fix unitialized variable Notices
 *
 * Revision 1.12  2004/10/01 14:11:37  introspectshun
 * - Fine-tuned regex to limit list of import templates to
 *   just those that start with 'import-template' and end with 'php'
 *
 * Revision 1.11  2004/09/22 22:05:07  introspectshun
 * - Added ADODB params include for multi-db compatibility
 *
 * Revision 1.10  2004/07/25 13:50:29  johnfawcett
 * - correct my spelling mistake of previous change
 *
 * Revision 1.9  2004/07/25 13:32:33  johnfawcett
 * - modified string Acct. to Account to unify across application
 *
 * Revision 1.8  2004/07/16 23:51:37  cpsource
 * - require session_check ( 'Admin' )
 *
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
