<?php
/**
 * Create a new contact for a company.
 *
 * $Id: new.php,v 1.10 2004/04/17 16:03:45 maulani Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$msg = $_GET['msg'];
$company_id = $_GET['company_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select company_name, phone, fax from companies where company_id = $company_id";

//$con->debug=1;

$rst = $con->execute($sql);

if ($rst) {
    $company_name = $rst->fields['company_name'];
    $phone = $rst->fields['phone'];
    $fax = $rst->fields['fax'];
    $rst->close();
}

//build division menu
$sql = "select division_name, division_id
        from company_division
        where
        company_division.company_id = $company_id and
        division_record_status = 'a'";
$rst = $con->execute($sql);
if ($rst) {
    $division_menu = $rst->getmenu2('division_id', $division_id, true);
    $rst->close();
}

$salutation_menu = build_salutation_menu($salutation);

$sql = "select address_name, address_id from addresses where company_id = $company_id and address_record_status = 'a' order by address_id";
$rst = $con->execute($sql);
if ($rst) {
    $address_menu = $rst->getmenu2('address_id', $address_id, false);
    $rst->close();
}

$con->close();

$page_title = "New Contact for $company_name";
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=new-2.php method=post>
        <input type=hidden name=company_id value="<?php echo $company_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>Contact Information</td>
            </tr>
            <tr>
                <td class=widget_label_right>Division</td>
                <td class=widget_content_form_element><?php echo $division_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Address</td>
                <td class=widget_content_form_element><?php echo $address_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Salutation</td>
                <td class=widget_content_form_element><?php echo $salutation_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>First Names</td>
                <td class=widget_content_form_element><input type=text name=first_names size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Last Name</td>
                <td class=widget_content_form_element><input type=text name=last_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Gender</td>
                <td class=widget_content_form_element>
                <select name="gender">
                    <option value="u" <?php if (($gender == "u") or ($gender == '')) {print " selected ";} ?>>Unknown
                    <option value="m" <?php if ($gender == "m") {print " selected ";} ?>>Male
                    <option value="f" <?php if ($gender == "f") {print " selected ";} ?>>Female
                </select>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right>Date of Birth</td>
                <td class=widget_content_form_element><input type=text name=date_of_birth size=12></td>
            </tr>
            <tr>
                <td class=widget_label_right>Summary</td>
                <td class=widget_content_form_element><input type=text name=summary size=35></td>
            </tr>
            <tr>
                <td class=widget_label_right>Title</td>
                <td class=widget_content_form_element><input type=text name=title size=35></td>
            </tr>
            <tr>
                <td class=widget_label_right>Description</td>
                <td class=widget_content_form_element><input type=text name=description size=35></td>
            </tr>
            <tr>
                <td class=widget_label_right>E-Mail</td>
                <td class=widget_content_form_element><input type=text name=email size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Work Phone</td>
                <td class=widget_content_form_element><input type=text name=work_phone size=30 value="<?php  echo $phone; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Cell Phone</td>
                <td class=widget_content_form_element><input type=text name=cell_phone size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Home Phone</td>
                <td class=widget_content_form_element><input type=text name=home_phone size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Fax</td>
                <td class=widget_content_form_element><input type=text name=fax size=30 value="<?php  echo $fax; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>AOL Name</td>
                <td class=widget_content_form_element><input type=text name=aol_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Yahoo Name</td>
                <td class=widget_content_form_element><input type=text name=yahoo_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>MSN Name</td>
                <td class=widget_content_form_element><input type=text name=msn_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Interests</td>
                <td class=widget_content_form_element><input type=text name=interests size=35></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px>Profile</td>
                <td class=widget_content_form_element><textarea rows=8 cols=80 name=profile></textarea></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php  echo $contact_custom1_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom1 size=35></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php  echo $contact_custom2_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom2 size=35></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php  echo $contact_custom3_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom3 size=35></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php  echo $contact_custom4_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom4 size=35></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Add Contact"></td>
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

function initialize() {
    document.forms[0].first_names.focus();
}

initialize();

</script>

<?php

end_page();

/**
 * $Log: new.php,v $
 * Revision 1.10  2004/04/17 16:03:45  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.9  2004/04/16 22:20:55  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.8  2004/04/08 17:13:44  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 * Revision 1.7  2004/02/11 15:05:01  braverock
 * - place $rst -> close() commands inside the if blocks
 * - fixes SF bug 893683 reported by Roberto Durrer (durrer)
 *
 * Revision 1.6  2004/01/26 19:13:34  braverock
 * - added company division fields
 * - added phpdoc
 *
 */
?>