<?php
/**
 * Create a new contact for a company.
 *
 * $Id: new.php,v 1.30 2005/06/02 20:51:21 ycreddy Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check('','Create');

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$clone_id = isset($_GET['clone_id']) ? $_GET['clone_id'] : 0;


$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

if ($clone_id > 0) {
	
    $sql = "select * from contacts where contact_id = $clone_id";
    $rst = $con->execute($sql);

    // was there a database error?
    if ( $rst ) {
      // no
      // was data found ???
      if ( !$rst->EOF ) {
      // yes - data found
        $company_id 	= $rst->fields['company_id'];
        $division_id 	= $rst->fields['division_id'];
        $address_id 	= $rst->fields['address_id'];
      } else {
    	// no - data not found
        $company_id 	= '';
        $division_id 	= '';
        $address_id 	= '';
      }
    } else {
      // yes - database error
      db_error_handler ($con, $sql);
    }
    $rst->close();

} else {

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
$salutation_menu = build_salutation_menu($con, $salutation);

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

// TBD - BUG - $gender should be set from database
if ( !isset($gender) ) {
  $gender = '';
}

$sql = "select country_name, country_id from countries where country_record_status = 'a' order by country_name";
$rst = $con->execute($sql);
if (!$country_id) {$country_id = $default_country_id;}

$country_menu = $rst->getmenu2('address_country_id', $country_id, false, false, 0, 'style="font-size: x-small; border: outset; width: 175px;"');
$rst->close();

$address_type_menu = build_address_type_menu($con, $address_type);

$sql = "select address_name, address_id from addresses where company_id = $company_id and address_record_status = 'a' order by address_id";
$rst = $con->execute($sql);
$rst->close();

$home_address_menu = $rst->getmenu2('home_address_id', $home_address_id, true);

$con->close();

$page_title = _("New Contact for") . ' ' . $company_name;
start_page($page_title, true, $msg);

?>

<form action=new-2.php method=post>
        <input type=hidden name=company_id value="<?php echo $company_id; ?>">
<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Contact Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Division"); ?></td>
                <td class=widget_content_form_element><?php echo $division_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Business Address"); ?></td>
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
                <td class=widget_label_right><?php echo _("Tax ID"); ?></td>
                <td class=widget_content_form_element><input type=text name=tax_id size=35></td>
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
                <td class=widget_content_form_element><input type=text name=work_phone size=30 value="<?php  echo $phone; ?>">&nbsp;
                <?php echo _("x"); ?>&nbsp;<input type=text name=work_phone_ext size=5 value=""></td>
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
                <td class=widget_label_right><?php echo _("Profile"); ?></td>
                <td class=widget_content_form_element><textarea rows=8 cols=42 name=profile></textarea></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Edit Address"); ?></td>
                <td class=widget_content_form_element><input type=checkbox name=edit_address></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Add Contact"); ?>"></td>
            </tr>
        </table>

    </div>
    <div id="Sidebar">
        <table class=widget><tr><td colspan=2 class=widget_header>Home Address</td></tr>
            <tr>
                <td class=widget_label_right><?php echo _("Home Address"); ?></td>
                <td class=widget_content_form_element><?php echo $home_address_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=30 name=address_name value="<?php echo $address_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Line 1"); ?></td>
                <td class=widget_content_form_element><input type=text size=30 name=line1 value="<?php echo $line1; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Line 2"); ?></td>
                <td class=widget_content_form_element><input type=text size=30 name=line2 value="<?php echo $line2; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("City"); ?></td>
                <td class=widget_content_form_element><input type=text size=30 name=city value="<?php echo $city; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("State/Province"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=province value="<?php echo $province; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Postal Code"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=postal_code value="<?php echo $postal_code; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Country"); ?></td>
                <td class=widget_content_form_element><?php echo $country_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address Type"); ?></td>
                <td class=widget_content_form_element><?php echo $address_type_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address Body"); ?></td>
                <td class=widget_content_form_element><textarea rows=5 cols=23 name=address_body><?php echo $address_body; ?></textarea><br> <input type="checkbox" name="use_pretty_address"<?php if ($use_pretty_address == 't') {echo " checked";} ?>><?php echo _("Use"); ?></td>
            </tr>
        </table>
    </div>
</div>
 </form>

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
 * Revision 1.30  2005/06/02 20:51:21  ycreddy
 * Fixes to the Alignment problems on IE 6.0
 *
 * Revision 1.29  2005/06/01 21:14:48  vanmer
 * - changed country list for new home address to be smaller width than text insideit
 * - changed width of profile box to be the same as other boxes
 *
 * Revision 1.28  2005/05/16 21:30:22  vanmer
 * - added tax_id handling to contacts pages
 *
 * Revision 1.27  2005/05/16 16:50:27  vanmer
 * - moved sidebar to appear after main div, for IE compatibility
 *
 * Revision 1.26  2005/05/07 00:10:56  vanmer
 * - added sidebar for adding a new address when adding a new contact
 * - move form to include new address fields
 *
 * Revision 1.25  2005/05/06 00:14:24  daturaarutad
 * added ability to clone contacts
 *
 * Revision 1.24  2005/05/04 14:36:13  braverock
 * - removed obsolete CSS widget_label_right_166px, replaced with widget_label_right
 *
 * Revision 1.23  2005/05/02 15:03:19  braverock
 * - change Address to 'Business Address' in the display
 *   @todo: still need to update to handle Home Address
 *
 * Revision 1.22  2005/04/26 17:28:04  gpowers
 * - added Extension ("x") to contact work phone
 * - removed non-digits from phone numbers in edit-2's, new-2's
 * - updated work phone display to include Extension
 *
 * Revision 1.21  2005/04/07 13:57:05  maulani
 * - Add salutation table to allow installation configurable list.  Also add
 *   many more default entries.
 *   RFE 913526 by algon.
 *
 * Revision 1.20  2005/01/13 18:42:30  vanmer
 * - Basic ACL changes to allow create/delete/update functionality to be restricted
 *
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
