<?php
/**
 * Create a new contact for a company.
 *
 * $Id: new.php,v 1.19 2005/01/06 18:39:00 vanmer Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//
// if $company_id is not passed in, get one for company 'Self' if the feature is set
// else, don't set $company_id
//
if ( isset($_GET['company_id']) ) {
  // was passed in
  $company_id = $_GET['company_id'];
  $division_id = $_GET['division_id'];
} elseif ( $use_self_contacts ) {
  // get from database
  $sql = "select company_id from companies where company_name = 'Self'";
  //$con->debug=1;
  $rst = $con->execute($sql);
  if ($rst) {
    $company_id = $rst->fields['company_id'];
    $rst->close();
  }
}

// get $company_name, $phone, $fax
if ( isset($company_id) ) {
  $sql = "select company_name, phone, fax from companies where company_id = $company_id";
  //$con->debug=1;
  $rst = $con->execute($sql);
  if ($rst) {
    $company_name = $rst->fields['company_name'];
    $phone        = $rst->fields['phone'];
    $fax          = $rst->fields['fax'];
    $rst->close();
  }
}
if ( !isset($company_name) ) {
  $company_name = '';
  $phone        = '';
  $fax          = '';
}

// build division menu
if ( isset($company_id) ) {
  $sql = "select division_name, division_id
        from company_division
        where
        company_division.company_id = $company_id and
        division_record_status = 'a'";
  $rst = $con->execute($sql);
  if ($rst) {
    if ( !$rst->EOF AND !$division_id ) {
      $division_id = $rst->fields['division_id'];
    }
    $division_menu = $rst->getmenu2('division_id', $division_id, true);
    $rst->close();
  }
}
if ( !isset($division_menu) ) {
  $division_menu = '';
}

// build salutation menu
if ( !isset($salutation) ) {
  $salutation = '';
}
$salutation_menu = build_salutation_menu($salutation);

// build address menu
if ( isset($company_id) ) {
  $sql = "select address_name, address_id from addresses where company_id = $company_id and address_record_status = 'a' order by address_id";
  $rst = $con->execute($sql);
  if ($rst) {
    if ( !$rst->EOF ) {
      $address_id = $rst->fields['address_id'];
    } else {
      $address_id = '';
    }
    $address_menu = $rst->getmenu2('address_id', $address_id, false);
    $rst->close();
  }
}
if ( !isset($address_menu) ) {
  $address_menu = '';
}

$con->close();

// TBD - BUG - $gender should be set from database
if ( !isset($gender) ) {
  $gender = '';
}

$page_title = _("New Contact for") . ' ' . $company_name;
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=new-2.php method=post>
        <input type=hidden name=company_id value="<?php echo $company_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Contact Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Division"); ?></td>
                <td class=widget_content_form_element><?php echo $division_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address"); ?></td>
                <td class=widget_content_form_element><?php echo $address_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Salutation"); ?></td>
                <td class=widget_content_form_element><?php echo $salutation_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("First Names"); ?></td>
                <td class=widget_content_form_element><input type=text name=first_names size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Last Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=last_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Gender"); ?></td>
                <td class=widget_content_form_element>
                <select name="gender">
                    <option value="u" <?php if (($gender == "u") or ($gender == '')) {print " selected ";} ?>><?php echo _("Unknown"); ?>
                    <option value="m" <?php if ($gender == "m") {print " selected ";} ?>><?php echo _("Male"); ?>
                    <option value="f" <?php if ($gender == "f") {print " selected ";} ?>><?php echo _("Female"); ?>
                </select>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Date of Birth"); ?></td>
                <td class=widget_content_form_element><input type=text name=date_of_birth size=12></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Summary"); ?></td>
                <td class=widget_content_form_element><input type=text name=summary size=35></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Title"); ?></td>
                <td class=widget_content_form_element><input type=text name=title size=35></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><input type=text name=description size=35></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("E-Mail"); ?></td>
                <td class=widget_content_form_element><input type=text name=email size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Work Phone"); ?></td>
                <td class=widget_content_form_element><input type=text name=work_phone size=30 value="<?php  echo $phone; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Cell Phone"); ?></td>
                <td class=widget_content_form_element><input type=text name=cell_phone size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Home Phone"); ?></td>
                <td class=widget_content_form_element><input type=text name=home_phone size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Fax"); ?></td>
                <td class=widget_content_form_element><input type=text name=fax size=30 value="<?php  echo $fax; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("AOL Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=aol_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Yahoo Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=yahoo_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("MSN Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=msn_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Interests"); ?></td>
                <td class=widget_content_form_element><input type=text name=interests size=35></td>
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
                <td class=widget_label_right_166px><?php echo _("Profile"); ?></td>
                <td class=widget_content_form_element><textarea rows=8 cols=80 name=profile></textarea></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px><?php echo _("Edit Address"); ?></td>
                <td class=widget_content_form_element><input type=checkbox name=edit_address></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Add Contact"); ?>"></td>
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
 * Revision 1.19  2005/01/06 18:39:00  vanmer
 * - allow pages calling new contact page to specify division_id and have it set properly when page displays
 *
 * Revision 1.18  2004/10/18 04:33:46  gpowers
 * - corrected spelling mistake
 *
 * Revision 1.17  2004/10/18 03:31:54  gpowers
 * - added "edit address" option
 *
 * Revision 1.16  2004/07/30 11:32:01  cpsource
 * - Define msg properly
 *   Fix bug with new.php wereby division_id and address_id were
 *     not set properly for getmenu2.
 *
 * Revision 1.15  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.14  2004/07/21 15:20:04  introspectshun
 * - Localized strings for i18n/translation support
 * - Removed include of lang file
 *
 * Revision 1.13  2004/07/13 18:05:59  cpsource
 * - Add feature use_self_contacts
 *   fix misc unitialized variables
 *
 * Revision 1.12  2004/06/15 20:41:58  gpowers
 * - moved Profile textbox below Custom Fields to match contacts/edit.php
 *
 * Revision 1.11  2004/06/15 17:26:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL and Concat functions.
 *
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
