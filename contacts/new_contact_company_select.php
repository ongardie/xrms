<?php
/**
 * Application for selecting a company before creating a new contact on that company
 *
 * @author Aaron van Meerten
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

GetGlobalVar($return_url,'return_url');
GetGlobalVar($msg, 'msg');
GetGlobalVar($noresults, 'noresults');
getGlobalVar($company_select_action, 'company_select_action');
getGlobalVar($company_name,'company_name');
getGlobalVar($btCancel, 'btCancel');
if ($btCancel AND $return_url) {
    Header("Location: $return_url");
    exit;
}

if (!$company_select_action) {
    if ($company_name) {
        $company_select_action='showCompanies';
    }
    else $company_select_action='newCompanySearch';
}
switch ($company_select_action) {
    case 'newContact':
        getGlobalVar($company_id, 'company_id');
        if ($company_id) {
            Header("Location: new.php?company_id=$company_id");
            exit;
        } else {
            $msg=_("Please select a company to continue");
        }
    case 'showCompanies':
        $con = &adonewconnection($xrms_db_dbtype);
        $con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
        if (!$company_name) {
            $msg="Please include a search phrase";
            Header("Location: new_contact_company_select.php?company_select_action=newCompanySearch&msg=$msg&return_url=$return_url");
        }
        $header_text=_("Select a company or create a").'&nbsp;<a href="../companies/new.php">'._("New Company").'</a>';
        $company_search = $con->qstr("%$company_name%");
        $sql = "SELECT company_name, company_id FROM companies WHERE company_name LIKE $company_search";
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); exit; }
        if ($rst->EOF) {
            $msg=_("No search results, please try a less restrictive search");
            Header("Location: new_contact_company_select.php?company_select_action=newCompanySearch&msg=$msg&noresults=1&return_url=$return_url");
        }
        $company_menu=$rst->getMenu2('company_id', false, true);
        $body_content.=_("Company").':'.$company_menu;
        $body_content.="<input type=hidden name=company_select_action  value='newContact'>";
        $body_content.="<input type=hidden name=return_url  value='$return_url'>";
        $body_content.="<input type=submit class=button name=btNewContact value=\""._("Select") ."\">";
        $body_content.="<input type=submit class=button name=btCancel value=\""._("Cancel") ."\">";
    break;
    default:
    case 'newCompanySearch':
        $header_text=_("Search for a company");
        $body_content.="Company: <input type=text name=company_name>";
        $body_content.="<input type=hidden name=company_select_action  value='showCompanies'>";
        $body_content.="<input type=hidden name=return_url  value='$return_url'>";
        $body_content.="<input type=submit class=button name=btNewContact value=\""._("Search") ."\">";
        $body_content.="<input type=submit class=button name=btCancel value=\""._("Cancel") ."\">";
        if ($noresults) {
            $body_content .= '&nbsp;'._("or create a").'&nbsp;'.
            '<input class=button type=button value="'._("New Company").'" onclick="javascript: location.href=\'../companies/new.php\'">';
        }
    break;
}
/* This is the main output of these pages.  This could eventually be made into a template which is included */
start_page($header_text, true, $msg);
        echo '<div id="Main">';
        echo <<<TILLEND
        <div id="Content">
           <form action=new_contact_company_select.php method=POST>
            <table class=widget cellspacing=1>
                <tr>
                    <td class=widget_header>
                        $header_text
                    </td>
                </tr>
                <tr><td class=widget_content>
                    $body_content
                 </td></tr>
             </table>
           </form>
         </div>
         </div>
TILLEND;
end_page();

/*
 * $Log: new_contact_company_select.php,v $
 * Revision 1.3  2005/05/06 23:37:00  braverock
 * - add New Company Button if there are no search results
 * - localize more strings
 *
 * Revision 1.2  2005/05/06 23:25:42  braverock
 * - add New Company option if found companies aren't correct
 * - localize strings
 *
 * Revision 1.1  2005/05/06 23:01:50  vanmer
 * -Initial revision of a simple application for search/selecting a company before adding a new contact
 *
**/
?>