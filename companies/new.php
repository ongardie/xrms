<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$clone_id = $_GET['clone_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

if ($clone_id > 0) {
    $sql = "select * from companies where company_id = $clone_id";
    $rst = $con->execute($sql);
    if ($rst) {
        $company_name = 'Copy of ' . $rst->fields['company_name'];
        $company_source_id = $rst->fields['company_source_id'];
        $crm_status_id = $rst->fields['crm_status_id'];
        $industry_id = $rst->fields['industry_id'];
        $user_id = $rst->fields['user_id'];
    }
    $rst->close();
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

$con->close();

$page_title = "New Company";
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=55% valign=top>

        <form action=new-2.php onsubmit="javascript: return validate();" method=post>
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=2>Company Information</td>
            </tr>
            <tr>
                <td class=widget_label_right>Company&nbsp;Name</td>
                <td class=widget_content_form_element><input type=text size=50 name=company_name
value="<?php  echo $company_name; ?>"> <?php echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Company&nbsp;Code</td>
                <td class=widget_content_form_element><input type=text size=10 name=company_code></td>
            </tr>
            <tr>
                <td class=widget_label_right>CRM&nbsp;Status</td>
                <td class=widget_content_form_element><?php  echo $crm_status_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Company Source</td>
                <td class=widget_content_form_element><?php  echo $company_source_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Industry</td>
                <td class=widget_content_form_element><?php  echo $industry_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Owner</td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Phone</td>
                <td class=widget_content_form_element><input type=text name=phone></td>
            </tr>
            <tr>
                <td class=widget_label_right>Alt.&nbsp;Phone</td>
                <td class=widget_content_form_element><input type=text name=phone2></td>
            </tr>
            <tr>
                <td class=widget_label_right>Fax</td>
                <td class=widget_content_form_element><input type=text name=fax></td>
            </tr>
            <tr>
                <td class=widget_label_right>URL</td>
                <td class=widget_content_form_element><input type=text name=url size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Employees</td>
                <td class=widget_content_form_element><input type=text name=employees size=10></td>
            </tr>
            <tr>
                <td class=widget_label_right>Revenue</td>
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
                <td class=widget_label_right_166px>Profile</td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=profile></textarea></td>
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

function initialize() {
    document.forms[0].company_name.select();
    // document.forms[0].company_name.focus();
}

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].company_name.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\nYou must enter a company name.';
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

<?php end_page();; ?>
