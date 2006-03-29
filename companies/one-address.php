<?php
/**
 * Edit address for a company
 *
 * $Id: one-address.php,v 1.13 2006/03/29 18:24:38 maulani Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');
require_once $include_directory."classes/QuickForm/ADOdb_QuickForm.php";
require_once($include_directory."classes/Pager/Array_Sorter.php");

$session_user_id = session_check();
    
$con=get_xrms_dbconnection();

getGlobalVar($return_url, 'return_url');
getGlobalVar($form_action, 'form_action');
getGlobalVar($address_id, 'address_id');
getGlobalVar($company_id, 'company_id');
getGlobalVar($contact_id, 'contact_id');
getGlobalVar($address_type, 'address_type');
getGlobalVar($use_pretty_address, 'use_pretty_address');
getGlobalVar($msg, 'msg');


$company_name = fetch_company_name($con, $company_id);

    if ($company_id) {
        switch ($form_action) {
            case 'new':
                $page_title = _("New Business Address");
                $_POST['address_type']='commercial';
				global $default_country_id;
                $_POST['country_id']=$default_country_id;
            break;
            case 'edit':
                $page_title=_("Edit Business Address");
            break;
            case 'view':
                $page_title=_("View Business Address");
            break;
        }
    } else if ($contact_id) {
        switch ($form_action) {
            case 'new':
                $page_title = _("New Home Address");
                $_POST['address_type']='residential';
            break;
            case 'edit':
                $page_title=_("Edit Home Address");
            break;
            case 'view':
                $page_title=_("View Home Address");
            break;
        }    
    }


    if(false !== render_delete_button("Delete",'button',"", false, false, 'addresses',$address_id)) {
        $delete_enabled = true;
    }


    // Model
    $model = new ADOdb_QuickForm_Model();
    $model->ReadSchemaFromDB($con, 'addresses');
    $model->SetPrimaryKeyName('address_id');

	$model->SetDisplayNames(array('address_name' => _("Address Name"), 
                                  'line1' => _("Line 1"),
                                  'line2' => _("Line 2"), 
                                  'city' => _("City"),
                                  'province' => _("State/Province"), 
                                  'postal_code' => _("Postal Code"),
                                  'country_id' => _("Country"),
                                  'company_id' => _("Company"),
                                  'address_type' => _("Address Type"),
                                  'address_body' => _("Non-Standard Address"),
                                  'use_pretty_address' => _("Use Non-Standard Address"),
                                  'sort_order' => _("Sort Order")));

    $display_order=array('company_id','address_name','line1','line2','city','province','postal_code','country_id','address_type', 'use_pretty_address','address_body');
        
    $model->SetDisplayOrders($display_order);

    $model->SetForeignKeyField('country_id', _("Country"), 'countries', 'country_id', 'country_name', $con, null, 'country_name');
	$model->SetForeignKeyField('address_type', _("Address Type"), 'address_types', 'address_type', 'address_type', $con, null, 'address_type_sort_value');
        
    $model->SetFieldType('address_record_status', 'db_only');
    $model->SetFieldType('gmt_offset', 'db_only');
    $model->SetFieldType('daylight_savings_id', 'db_only');
        
    $model->SetCheckboxField('use_pretty_address', 't','f');
    if ($company_id) {
        $model->SetHiddenLinkField('company_id', "$http_site_root/companies/one.php?company_id=$company_id",$company_name, $company_id);
    } else {
        $model->SetFieldType('company_id','hidden');
        $model->SetFieldValue('company_id',0);
    }
    $model->SetFieldType('address_body', 'textarea','cols=50 rows=10');

	global $default_country_id;
	$model->SetFieldValue('country_id', $default_country_id);

    if ($contact_id) {
        $model->AddField("<input type=hidden name=contact_id value=$contact_id>", 'html',0);
    }

    // delete button
    if($delete_enabled) {
        $model->SetLogicalDeleteParams('address_record_status');
    }

    

    // View    
    $view = new ADOdb_QuickForm_View($con, $page_title, 'post');

    $view->SetReturnButton('Return to List', $return_url);
    $view->SetReturnAfterUpdate($return_url);

    // delete button
    if($delete_enabled) {
        $view->EnableDeleteButton();
    }

    // Controller
    $controller = new ADOdb_QuickForm_Controller(array(&$model), $view);

    $template_form_html = $controller->ProcessAndRenderForm();

    $msg        .= $controller->GetStatusMessage();

    // this may not work always...
    if(!strchr($return_url,'?')) {
        $return_url .= "?msg=$msg";
    } else {
        $return_url .= "&msg=$msg";
    }

    if (!$template_form_html) header("Location: $return_url&msg=$msg");

    start_page($page_title, true, $msg);
?>

<div id="Main">
<div id="Sidebar">
    &nbsp;
</div>
<div id="Content">
<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=30% valign=top>
					<?php echo $template_form_html; ?>
        </td>
    </tr>
</table>
</div>
<?php
switch ($form_action) {
    case 'create':
        $values=$model->GetValues();
        $address_id=$values['address_id'];
        add_audit_item($con, $session_user_id, 'created', 'addresses', $address_id, 1);
        if ($contact_id) {
            $sql = "SELECT * FROM contacts WHERE contact_id = $contact_id";
            $rst = $con->execute($sql);
        
            $rec = array();
            $rec['home_address_id'] = $address_id;
        
            $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
            $con->execute($upd);
        
            add_audit_item($con, $session_user_id, 'changed home address', 'contacts', $contact_id, 1);
        }
        if($time_zone_offset = time_zone_offset($con, $address_id)) {
            $sql = 'SELECT *
                    FROM addresses
                    WHERE address_id=' . $address_id;
            $rst = $con->execute($sql);
            if(!$rst) {
                db_error_handler($con, $sql);
            }
            elseif(!$rst->EOF) {
                $rec = array();
                $rec['daylight_savings_id'] = $time_zone_offset['daylight_savings_id'];
                $rec['gmt_offset'] = $time_zone_offset['offset'];
        
                $upd = $con->getUpdateSQL($rst, $rec, true, get_magic_quotes_gpc());
                $rst = $con->execute($upd);
                if(!$rst) {
                    db_error_handler($con, $sql);
                }
            }
        } 
    break;
    case 'update':
        $rst=$model->GetRecordset();
        $rec=$model->GetValues();
        $param = array( $_POST, $rst, $rec);
        do_hook_function('company_edit_address_2', $param);
    break;
}
    
   $con->close();

end_page();

/**
 * $Log: one-address.php,v $
 * Revision 1.13  2006/03/29 18:24:38  maulani
 * - Set default country instead of a blank option when creating a new address
 *
 * Revision 1.12  2006/03/29 18:23:05  maulani
 * - Remove deprecated pass-by-reference indicator.  Pass-by-reference already
 *   indicated in class definition
 *
 * Revision 1.11  2005/12/18 02:57:20  vanmer
 * - changed to use gmt_offset instead of offset field
 * - Thanks to kennyholden for this patch
 *
 * Revision 1.10  2005/09/07 17:32:40  daturaarutad
 * fixed formatting; enabled delete button in quickform; remove old form sort code
 *
 * Revision 1.9  2005/08/02 22:41:32  ycreddy
 * Defaulted the country to the default_address_id defined in vars.php
 *
 * Revision 1.8  2005/07/14 19:42:24  daturaarutad
 * changed to use QuickForms "html" type
 *
 * Revision 1.7  2005/07/07 23:18:18  vanmer
 * - moved start_page to after all quickform processes complete
 * - added returnAfterUpdateURL to allow immediate return after update of address record
 *
 * Revision 1.6  2005/07/06 21:25:51  vanmer
 * - setting primary key explicitly to attempt to fix issue on sql server
 *
 * Revision 1.5  2005/07/06 16:00:22  vanmer
 * - added sensible defaults for address type (commerical or residential) when initially entering an address
 *
 * Revision 1.4  2005/07/06 03:20:04  vanmer
 * - allow company id to be ignored for an address, if the address is a home address
 * - change page title to act differently on a business vs. home address
 *
 * Revision 1.3  2005/07/06 01:25:32  vanmer
 * - added link to company at the top of the edit address page
 * - changed address body and checkbox to be called Non-Standard Address
 *
 * Revision 1.2  2005/07/06 00:24:25  vanmer
 * - removed debug output
 *
 * Revision 1.1  2005/07/06 00:22:57  vanmer
 * - Initial commit of a QuickForm page to replace edit-address.php, edit-address-2.php, add-address.php and the new
 * form on addresses.php
 * - runs the hook function on edit-2, audit_item on creation, and timezone lookups, fully replacing the functionality
 * of the above files
 *
 */
?>
