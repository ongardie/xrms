<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$company_id = $_GET['company_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

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
    $custom1 = $rst->fields['custom1'];
    $custom2 = $rst->fields['custom2'];
    $custom3 = $rst->fields['custom3'];
    $custom4 = $rst->fields['custom4'];
    $rst->close();
}

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

$con->close();

$page_title = $company_name . " - Edit Profile";
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=55% valign=top>

        <form action=edit-2.php method=post onsubmit="javascript: return validate();">
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=2>Edit Profile</td>
            </tr>
            <tr>
                <td class=widget_label_right>Company&nbsp;Name</td>
                <td class=widget_content_form_element><input type=text size=40 name=company_name value="<?php echo $company_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Legal&nbsp;Name</td>
                <td class=widget_content_form_element><input type=text size=40 name=legal_name value="<?php echo $legal_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Company&nbsp;Code</td>
                <td class=widget_content_form_element><input type=text size=10 name=company_code value="<?php echo $company_code; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>CRM&nbsp;Status</td>
                <td class=widget_content_form_element><?php echo $crm_status_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Owner</td>
                <td class=widget_content_form_element><?php echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Company Source</td>
                <td class=widget_content_form_element><?php echo $company_source_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Industry</td>
                <td class=widget_content_form_element><?php echo $industry_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Phone</td>
                <td class=widget_content_form_element><input type=text name=phone value="<?php echo $phone; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Alt.&nbsp;Phone</td>
                <td class=widget_content_form_element><input type=text name=phone2 value="<?php echo $phone2; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Fax</td>
                <td class=widget_content_form_element><input type=text name=fax value="<?php echo $fax; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>URL</td>
                <td class=widget_content_form_element><input type=text name=url size=40 value="<?php echo $url; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Employees</td>
                <td class=widget_content_form_element><input type=text name=employees size=10 value="<?php echo $employees; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Revenue</td>
                <td class=widget_content_form_element><input type=text name=revenue size=10 value="<?php echo $revenue; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $company_custom1_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom1 size=30 value="<?php echo $custom1; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $company_custom2_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom2 size=30 value="<?php echo $custom2; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $company_custom3_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom3 size=30 value="<?php echo $custom3; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $company_custom4_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom4 size=30 value="<?php echo $custom4; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px>Profile</td>
                <td class=widget_content_form_element><textarea rows=8 cols=80 name=profile><?php echo $profile; ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"></td>
            </tr>
        </table>
        </form>

        </td>
        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=43% valign=top>

        </td>
    </tr>
</table>

<script language=javascript>
<!--

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].company_name.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\nYou must enter a company name.';
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

<?php end_page();; ?>