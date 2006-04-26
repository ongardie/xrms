<?php
/**
 * Edit company details
 *
 * $Id: edit.php,v 1.23 2006/04/26 20:06:27 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'confgoto.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

getGlobalVar($company_id,'company_id');

$on_what_id=$company_id;

$session_user_id = session_check('','Update');

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';


$con = get_xrms_dbconnection();

// $con->debug=1;

$sql = "select * from companies where company_id = $company_id";

$rst = $con->execute($sql);

if ($rst) {
    $company_name = $rst->fields['company_name'];
    $legal_name = $rst->fields['legal_name'];
    $company_code = $rst->fields['company_code'];
    $crm_status_id = $rst->fields['crm_status_id'];
    $user_id = $rst->fields['user_id'];
    $company_source_id = $rst->fields['company_source_id'];
    $industry_id = $rst->fields['industry_id'];
    $rating_id = $rst->fields ['rating_id'];
    $profile = $rst->fields['profile'];
    $phone = $rst->fields['phone'];
    $phone2 = $rst->fields['phone2'];
    $fax = $rst->fields['fax'];
    $url = $rst->fields['url'];
    $address = $rst->fields['address'];
    $city = $rst->fields['city'];
    $state = $rst->fields['state'];
    $postal_code = $rst->fields['postal_code'];
    $country = $rst->fields['country'];
    $employees = $rst->fields['employees'];
    $revenue = $rst->fields['revenue'];
    $tax_id = $rst->fields['tax_id'];
    $account_status_id = $rst->fields['account_status_id'];
    $credit_limit = $rst->fields['credit_limit'];
    $rating_id = $rst->fields['rating_id'];
    $terms = $rst->fields['terms'];
    $extref1 = $rst->fields['extref1'];
    $extref2 = $rst->fields['extref2'];
    $custom1 = $rst->fields['custom1'];
    $custom2 = $rst->fields['custom2'];
    $custom3 = $rst->fields['custom3'];
    $custom4 = $rst->fields['custom4'];
    $rst->close();
}

$user_menu = get_user_menu($con, $user_id, false, 'user_id', false);

$crm_status_menu = build_crm_status_menu($con, $crm_status_id);

$sql2 = "select company_source_pretty_name, company_source_id from company_sources where company_source_record_status = 'a' order by company_source_pretty_name";
$rst = $con->execute($sql2);
$company_source_menu = $rst->getmenu2('company_source_id', $company_source_id, false);
$rst->close();

$sql2 = "select industry_pretty_name, industry_id from industries where industry_record_status = 'a' order by industry_pretty_name";
$rst = $con->execute($sql2);
$industry_menu = $rst->getmenu2('industry_id', $industry_id, false);
$rst->close();

$sql2 = "select rating_pretty_name, rating_id from ratings where rating_record_status = 'a' order by rating_pretty_name";
$rst = $con->execute($sql2);
$rating_menu = $rst->getmenu2('rating_id', $rating_id, false);
$rst->close();

$sql = "select account_status_pretty_name, account_status_id from account_statuses where account_status_record_status = 'a'";
$rst = $con->execute($sql);
$account_status_menu = $rst->getmenu2('account_status_id', $account_status_id, false);
$rst->close();

$accounting_rows = do_hook_function('company_accounting_inline_edit', $accounting_rows);

$con->close();

$page_title = $company_name . " - " . _("Edit Profile");
start_page($page_title, true, $msg);

// include confGoTo javascript module
confGoTo_includes();

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post onsubmit="javascript: return validate();">
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Edit Profile"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=40 name=company_name value="<?php echo $company_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Legal Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=40 name=legal_name value="<?php echo $legal_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company Code"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=company_code value="<?php echo $company_code; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("CRM Status"); ?></td>
                <td class=widget_content_form_element><?php echo $crm_status_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Owner"); ?></td>
                <td class=widget_content_form_element><?php echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company Source"); ?></td>
                <td class=widget_content_form_element><?php echo $company_source_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Industry"); ?></td>
                <td class=widget_content_form_element><?php echo $industry_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Phone"); ?></td>
                <td class=widget_content_form_element><input type=text name=phone value="<?php echo $phone; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Alt. Phone"); ?></td>
                <td class=widget_content_form_element><input type=text name=phone2 value="<?php echo $phone2; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Fax"); ?></td>
                <td class=widget_content_form_element><input type=text name=fax value="<?php echo $fax; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("URL"); ?></td>
                <td class=widget_content_form_element><input type=text name=url size=40 value="<?php echo $url; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Employees"); ?></td>
                <td class=widget_content_form_element><input type=text name=employees size=10 value="<?php echo $employees; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Revenue"); ?></td>
                <td class=widget_content_form_element><input type=text name=revenue size=10 value="<?php echo $revenue; ?>"></td>
            </tr>
            <!-- accounting plugin -->
            <?php echo $accounting_rows; ?>
            <tr>
                <td class=widget_label_right><?php echo _("Account Status"); ?></td>
                <td class=widget_content_form_element><?php echo $account_status_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Tax ID"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=tax_id value="<?php echo $tax_id; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Credit Limit"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=credit_limit value="<?php echo $credit_limit; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Rating"); ?></td>
                <td class=widget_content_form_element><?php echo $rating_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Terms"); ?></td>
                <td class=widget_content_form_element>Net &nbsp;<input type=text size=3 name=terms value="<?php echo $terms; ?>"> Days</td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Customer Key"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=extref1 value="<?php echo $extref1; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Vendor Key"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=extref2 value="<?php echo $extref2; ?>"></td>
            </tr>

        <?php if ($company_custom1_label!='(Custom 1)') { ?>
            <tr>
                <td class=widget_label_right><?php echo $company_custom1_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom1 size=30 value="<?php echo $custom1; ?>"></td>
            </tr>
	    <?php } if ($company_custom2_label!='(Custom 2)') { ?>
            <tr>
                <td class=widget_label_right><?php echo $company_custom2_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom2 size=30 value="<?php echo $custom2; ?>"></td>
            </tr>
	    <?php } if ($company_custom3_label!='(Custom 3)') { ?>
            <tr>
                <td class=widget_label_right><?php echo $company_custom3_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom3 size=30 value="<?php echo $custom3; ?>"></td>
            </tr>
	    <?php } if ($company_custom4_label!='(Custom 4)') { ?>
            <tr>
                <td class=widget_label_right><?php echo $company_custom4_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom4 size=30 value="<?php echo $custom4; ?>"></td>
            </tr>
	    <?php } ?>
            <tr>
                <td class=widget_label_right><?php echo _("Profile"); ?></td>
                <td class=widget_content_form_element><textarea rows=8 cols=80 name=profile><?php echo $profile; ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
                <td class=widget_content_form_element>
                    <input class=button type=button value="<?php echo _("Edit Former Names"); ?>" onclick="javascript: location.href='former-names.php?company_id=<?php echo $company_id; ?>';">
                    <?php
                        if ( $company_id > 1 ) {
                            $quest = _("Are you sure you want to remove this company (and all associated contacts, activities, opportunities, cases, etc.) from the system?");
                            $button = _("Delete Company");
                            $to_url = "delete.php?company_id=$company_id";
                            acl_confGoTo( $quest, $button, $to_url, 'companies', $company_id, 'Delete' );
                        }
                    ?>
                </td>
            </tr>
        </table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        &nbsp;

    </div>
</div>

<script language="JavaScript" type="text/javascript">
<!--

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].company_name.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\n<?php echo _("You must enter a company name."); ?>';
    }

    if (numberOfErrors > 0) {
        alert(msgToDisplay);
        document.forms[0].company_name.focus();
        return false;
    } else {
        return true;
    }

}

//-->
</script>

<?php

end_page();

/**
 * $Log: edit.php,v $
 * Revision 1.23  2006/04/26 20:06:27  braverock
 * - move accounting and credit fields from old companies/admin* pages
 * - add Delete button here, for consistency with other XRMS object types
 *
 * Revision 1.22  2006/01/02 22:56:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.21  2005/10/06 04:30:06  vanmer
 * - updated log entries to reflect addition of code by Diego Ongaro at ETSZONE
 *
 * Revision 1.20  2005/10/04 23:21:44  vanmer
 * Patch to allow sort_order on the company CRM status field, thanks to Diego Ongaro at ETSZONE
 *
 * Revision 1.19  2005/08/17 20:02:23  ycreddy
 * Expanded the Owner field
 *
 * Revision 1.18  2005/08/13 22:57:00  vanmer
 * - altered to hide custom company fields unless their labels have been changed in vars.php
 *
 * Revision 1.17  2005/05/04 14:35:51  braverock
 * - removed obsolete CSS widget_label_right_166px, replaced with widget_label_right
 *
 * Revision 1.16  2005/03/21 13:40:55  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.15  2005/03/18 20:53:29  gpowers
 * - added hooks for inline info plugin
 *
 * Revision 1.14  2005/01/13 18:20:28  vanmer
 * - Basic ACL changes to allow create/delete/update functionality to be restricted
 *
 * Revision 1.13  2004/07/30 11:23:38  cpsource
 * - Do standard msg processing
 *   Default use_pretty_address in new-2.php set to null
 *
 * Revision 1.12  2004/07/21 19:17:57  introspectshun
 * - Localized strings for i18n/l10n support
 *
 * Revision 1.11  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.10  2004/05/06 13:32:23  gpowers
 * added support for "Edit Former Name"
 *
 * Revision 1.9  2004/04/16 22:19:38  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.8  2004/04/08 17:00:59  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 * Revision 1.7  2004/02/18 21:30:53  braverock
 * fixed rating so it populates correctly
 *
 * Revision 1.6  2004/02/14 15:27:19  braverock
 * - add ratings to the editing of companies
 *
 * Revision 1.5  2004/01/26 19:18:29  braverock
 * - cleaned up sql format
 * - added phpdoc
 *
 */
?>
