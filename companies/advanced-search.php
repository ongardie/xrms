<?php
/**
 * Search and view summary information on multiple companies
 *
 * This is the advanced screen that allows many more search fields
 *
 * $Id: advanced-search.php,v 1.2 2004/06/29 13:19:59 maulani Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

//set the language
$_SESSION['language'] = 'english';

$session_user_id = session_check();

require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//uncomment this line if you suspect a problem with the SQL query
// $con->debug = 1;


$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, true);
$rst->close();

$sql2 = "select category_pretty_name, c.category_id
from categories c, category_scopes cs, category_category_scope_map ccsm
where c.category_id = ccsm.category_id
and cs.on_what_table =  'companies'
and ccsm.category_scope_id = cs.category_scope_id
and category_record_status =  'a'
order by category_pretty_name";
$rst = $con->execute($sql2);
$company_category_menu = $rst->getmenu2('company_category_id', $company_category_id, true);
$rst->close();

$sql2 = "select company_type_pretty_name, company_type_id from company_types where company_type_record_status = 'a' order by company_type_id";
$rst = $con->execute($sql2);
$company_type_menu = $rst->getmenu2('company_type_id', $company_type_id, true);
$rst->close();

$sql2 = "select crm_status_pretty_name, crm_status_id from crm_statuses where crm_status_record_status = 'a' order by crm_status_id";
$rst = $con->execute($sql2);
$crm_status_menu = $rst->getmenu2('crm_status_id', $crm_status_id, true);
$rst->close();

$sql2 = "select company_source_pretty_name, company_source_id from company_sources where company_source_record_status = 'a' order by company_source_pretty_name";
$rst = $con->execute($sql2);
$company_source_menu = $rst->getmenu2('company_source_id', $company_source_id, true);
$rst->close();

$sql2 = "select industry_pretty_name, industry_id from industries where industry_record_status = 'a' order by industry_id";
$rst = $con->execute($sql2);
$industry_menu = $rst->getmenu2('industry_id', $industry_id, true);
$rst->close();

$sql = "select country_name, country_id from countries where country_record_status = 'a' order by country_name";
$rst = $con->execute($sql);
$country_menu = $rst->getmenu2('country_id', $country_id, true);
$rst->close();

$page_title = $strCompaniesSomePageTitle;
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="ContentFullWidth">

        <form action=some-advanced.php method=post>
<table border=0 cellpadding=0 cellspacing=0 width="100%">
    <tr>
        <td class=lcol width="55%" valign=top>


        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=2>Company Information</td>
            </tr>
            <tr>
                <td class=widget_label_right>Company&nbsp;Name</td>
                <td class=widget_content_form_element><input type=text size=50 name=company_name value="<?php  echo $company_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Legal&nbsp;Name</td>
                <td class=widget_content_form_element><input type=text size=50 name=legal_name value="<?php echo $company_name; ?>"></td>
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
                <td class=widget_label_right>Company&nbsp;Source</td>
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
                <td class=widget_content_form_element><input type=text name=url size=50></td>
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
                <td class=widget_content_form_element><textarea rows=10 cols=70 name=profile></textarea></td>
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
                <td class=widget_header colspan=2>Address</td>
            </tr>
            <tr>
                <td class=widget_label_right>Address&nbsp;Name</td>
                <td class=widget_content_form_element><input type=text name=address_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Line 1</td>
                <td class=widget_content_form_element><input type=text name=line1 size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Line 2</td>
                <td class=widget_content_form_element><input type=text name=line2 size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>City</td>
                <td class=widget_content_form_element><input type=text name=city size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>State/Province</td>
                <td class=widget_content_form_element><input type=text name=province size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right>Postal Code</td>
                <td class=widget_content_form_element><input type=text name=postal_code size=10></td>
            </tr>
            <tr>
                <td class=widget_label_right>Country</td>
                <td class=widget_content_form_element><?php echo $country_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right_91px>Override&nbsp;Address</td>
                <td class=widget_content_form_element><textarea rows=5 cols=40 name=address_body></textarea></td>
            </tr>
             <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Search"></td>
            </tr>
        </table>

        </td>
    </tr>
</table>
</form>

<?php

$con->close();

?>

    </div>

</div>

<script language="JavaScript" type="text/javascript">
<!--

function initialize() {
    document.forms[0].company_name.focus();
}

initialize();

function bulkEmail() {
    document.forms[0].action = "../email/email.php";
    document.forms[0].submit();
}


//-->
</script>

<?php

end_page();

/**
 * $Log: advanced-search.php,v $
 * Revision 1.2  2004/06/29 13:19:59  maulani
 * - Additional fields for advanced search
 *
 * Revision 1.1  2004/06/28 23:08:39  maulani
 * - Advanced search allows searching with a lot more fields
 *
 */
?>
