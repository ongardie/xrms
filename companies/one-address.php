<?php
/**
 * Edit address for a company
 *
 * $Id: one-address.php,v 1.2 2005/07/06 00:24:25 vanmer Exp $
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
getGlobalVar($address_type, 'address_type');
getGlobalVar($use_pretty_address, 'use_pretty_address');

switch ($form_action) {
    case 'new':
        $page_title = _("New Address");
    break;
    case 'edit':
        $page_title=_("Edit Address");
    break;
    case 'view':
        $page_title=_("Edit Address");
    break;
}

start_page($page_title);

  $model = new ADOdb_QuickForm_Model();
  $model->ReadSchemaFromDB($con, 'addresses');

	$model->SetDisplayNames(array('address_name' => _("Address Name"), 
                                                                                                                'line1' => _("Line 1"),
                                                                                                                'line2' => _("Line 2"), 
                                                                                                                'city' => _("City"),
                                                                                                                'province' => _("State/Province"), 
                                                                                                                'postal_code' => _("Postal Code"),
                                                                                                                'country_id' => _("Country"),
                                                                                                                'address_type' => _("Address Type"),
                                                                                                                'address_body' => _("Address Body"),
                                                                                                                'use_pretty_address' => _("Use Pretty Address"),
                                                                                                                'sort_order' => _("Sort Order")));
        $display_order=array('address_name','line1','line2','city','province','postal_code','country_id','address_type', 'use_pretty_address','address_body');
        
        $model->SetDisplayOrders($display_order);
	$model->SetForeignKeyField('country_id', _("Country"), 'countries', 'country_id', 'country_name', $con, null, 'country_name');
	$model->SetForeignKeyField('address_type', _("Address Type"), 'address_types', 'address_type', 'address_type', $con, null, 'address_type_sort_value');
        $model->SetFieldType('address_record_status', 'db_only');
        $model->SetFieldType('offset', 'db_only');
        $model->SetFieldType('daylight_savings_id', 'db_only');
//        $model->SetFieldType('use_pretty_address', 'db_only');
        $model->SetCheckboxField('use_pretty_address', 't','f');
        $model->SetFieldType('company_id','hidden');
        $model->SetFieldValue('company_id',$company_id);
        $model->SetFieldType('address_body', 'textarea','cols=50 rows=10');
//        $model->AddCustomField('use_pretty_address',12, $use_pretty_address_box, null, _("Use Pretty Address"), count($display_order)-1.5  );
//        $model->RemoveField('use_pretty_address');
        $fields=$model->GetFields();
        $sorter = new array_sorter($fields, 'displayOrder', false);
        $model->DBStructure['fields']=$sorter->sortit();
        $fields=$model->GetFields();
        
        
  $view = new ADOdb_QuickForm_View($con, $page_title, 'post');
  $view->SetReturnButton('Return to List', $return_url);

  $controller = new ADOdb_QuickForm_Controller(array(&$model), &$view);
  $template_form_html = $controller->ProcessAndRenderForm();



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
                $rec['offset'] = $time_zone_offset['offset'];
        
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