<?php
/**
 * Enter a new company, and create default contact and address.
 *
 * @author Chris Woofter
 *
 * @todo Add ability to ctreate a Sales Opportunity for a new company
 *
 * $Id: new.php,v 1.10 2004/07/22 15:39:05 cpsource Exp $
 */

/* Include required files */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$msg      = isset($_GET['msg'])      ? $_GET['msg']      : '';
$clone_id = isset($_GET['clone_id']) ? $_GET['clone_id'] : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

if ($clone_id > 0) {
    $sql = "select * from companies where company_id = $clone_id";
    $rst = $con->execute($sql);

    // was there a database error?
    if ( $rst ) {
      // no
      // was data found ???
      if ( !$rst->EOF ) {
	// yes - data found
        $company_name      = 'Copy of ' . $rst->fields['company_name'];
        $company_source_id = $rst->fields['company_source_id'];
        $crm_status_id     = $rst->fields['crm_status_id'];
        $industry_id       = $rst->fields['industry_id'];
        $user_id           = $rst->fields['user_id'];
      } else {
	// no - data not found
        $company_name      = '';
        $company_source_id = '';
        $crm_status_id     = '';
        $industry_id       = '';
        $user_id           = '';
      }
    } else {
      // yes - database error
      db_error_handler ($con, $sql);
    }
    $rst->close();
} else {
  $company_name      = '';
  $company_source_id = '';
  $crm_status_id     = '';
  $industry_id       = '';
  $user_id           = '';
}

$user_id = ($user_id > 0) ? $user_id : $session_user_id;

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $user_id, false);
$rst->close();

$sql2 = "select crm_status_pretty_name, crm_status_id from crm_statuses where crm_status_record_status = 'a' order by crm_status_id";
$rst = $con->execute($sql2);
$crm_status_menu = $rst->getmenu2('crm_status_id', $crm_status_id, false);
$rst->close();

$sql2 = "select company_source_pretty_name, company_source_id from company_sources where company_source_record_status = 'a' order by company_source_pretty_name";
$rst = $con->execute($sql2);
$company_source_menu = $rst->getmenu2('company_source_id', $company_source_id, false);
$rst->close();

$sql2 = "select industry_pretty_name, industry_id from industries where industry_record_status = 'a' order by industry_pretty_name";
$rst = $con->execute($sql2);
$industry_menu = $rst->getmenu2('industry_id', $industry_id, false);
$rst->close();

$sql = "select country_name, country_id from countries where country_record_status = 'a' order by country_name";
$rst = $con->execute($sql);
$country_menu = $rst->getmenu2('country_id', $default_country_id, false);
$rst->close();

$con->close();

$page_title = _("New Company");
start_page($page_title, true, $msg);

?>

<form action="new-2.php" onsubmit="javascript: return validate();" method=post>
<table border=0 cellpadding=0 cellspacing=0 width="100%">
    <tr>
        <td class=lcol width="55%" valign=top>


        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=2><?php echo _("Company Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company Name"); ?></td>
		<td class=widget_content_form_element><input type=text size=50 name=company_name value="<?php echo $company_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Legal Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=50 name=legal_name value="<?php echo $company_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company Code"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=company_code></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("CRM Status"); ?></td>
                <td class=widget_content_form_element><?php  echo $crm_status_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company Source"); ?></td>
                <td class=widget_content_form_element><?php  echo $company_source_menu; ?> <?php echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Industry"); ?></td>
                <td class=widget_content_form_element><?php  echo $industry_menu; ?> <?php echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Owner"); ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Phone"); ?></td>
                <td class=widget_content_form_element><input type=text name=phone></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Alt. Phone"); ?></td>
                <td class=widget_content_form_element><input type=text name=phone2></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Fax"); ?></td>
                <td class=widget_content_form_element><input type=text name=fax></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("URL"); ?></td>
                <td class=widget_content_form_element><input type=text name=url size=50></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Employees"); ?></td>
                <td class=widget_content_form_element><input type=text name=employees size=10></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Revenue"); ?></td>
                <td class=widget_content_form_element><input type=text name=revenue size=10></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $company_custom1_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom1 size=30 ></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $company_custom2_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom2 size=30 ></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $company_custom3_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom3 size=30 ></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $company_custom4_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom4 size=30 ></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px><?php echo _("Profile"); ?></td>
                <td class=widget_content_form_element><textarea rows=10 cols=70 name=profile></textarea></td>
            </tr>
             <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>
        </table>

        </td>
        <!-- gutter //-->
        <td class=gutter width="1%">
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width="44%" valign=top>

        <!-- Address Entry //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=2><?php echo _("Address"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=address_name size=30 value="Main"> <?php echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Line 1"); ?></td>
                <td class=widget_content_form_element><input type=text name=line1 size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Line 2"); ?></td>
                <td class=widget_content_form_element><input type=text name=line2 size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("City"); ?></td>
                <td class=widget_content_form_element><input type=text name=city size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("State/Province"); ?></td>
                <td class=widget_content_form_element><input type=text name=province size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Postal Code"); ?></td>
                <td class=widget_content_form_element><input type=text name=postal_code size=10></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Country"); ?></td>
                <td class=widget_content_form_element><?php echo $country_menu ?> <?php echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right_91px><?php echo _("Override Address"); ?></td>
                <td class=widget_content_form_element><textarea rows=5 cols=40 name=address_body></textarea><br><input type="checkbox" name="use_pretty_address"> <?php echo _("Use"); ?></td>
            </tr>
        </table>

        <!-- Default Contact Entry //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=2><?php echo _("Contact Information<"); ?>/td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("First Names"); ?></td>
                <td class=widget_content_form_element><input type=text name=first_names size=30 value="Default"> <?php echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Last Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=last_name size=30 value="Contact"> <?php echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("E-Mail"); ?></td>
                <td class=widget_content_form_element><input type=text name=email size=30></td>
            </tr>
        </table>

        </td>
    </tr>
</table>
</form>

<script language=javascript type="text/javascript" >

function initialize() {
    document.forms[0].company_name.select();
    // document.forms[0].company_name.focus();
}

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].company_name.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\n<?php echo _("You must enter a company name."); ?>';
    }

    if (numberOfErrors > 0) {
        alert(msgToDisplay);
        return false;
    } else {
        return true;
    }

}

initialize();

</script>

<?php

end_page();

/**
 * $Log: new.php,v $
 * Revision 1.10  2004/07/22 15:39:05  cpsource
 * - Fix multiple undefines
 *   Check for records retrieved from db
 *
 * Revision 1.9  2004/07/21 19:17:57  introspectshun
 * - Localized strings for i18n/l10n support
 *
 * Revision 1.8  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.7  2004/02/15 02:18:12  maulani
 * force popup menus to have valid values
 *
 * Revision 1.6  2004/02/02 02:51:16  braverock
 * - fixed small display bug
 * - added phpdoc
 *
 */
?>