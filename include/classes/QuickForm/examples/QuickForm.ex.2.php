<?php


/**
*
* Basic QuickForm example 
*
*/

require_once('../../../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'classes/QuickForm/ADOdb_QuickForm.php');

$session_user_id = session_check();

start_page();
?>

<h3> 
QuickForm example 2
</h3>

<p>This example shows how to replace QuickForm's widget for a particular element, in this case Status.</p>
<p>In this case, we want a &lt;select&gt; element to show in the 'new' and 'edit' states, and we want
the value to show in 'view' state.</p>

<p><i>If the field were a key into another table you would want to follow the example of <a href="QuickForm.ex.3.php">QuickForm.ex.3.php</a> instead.</i></p>
<?php


if(check_user_role(false, $session_user_id, 'Administrator')) {

    global $http_site_root;
    $return_url = $http_site_root . current_page();

	$con = get_xrms_dbconnection();


    $model = new ADOdb_QuickForm_Model();
    $model->ReadSchemaFromDB($con, 'notes');
    $model->SetDisplayNames(array('note_description' => _('Description'),
                                  'on_what_table' => _('On what table'),
                                  'on_what_id' => _('On what id'),
                                  'entered_at' => _('Entered At'),
                                  'entered_by' => _('Entered By'),
                                  'note_record_status' => _('Status'),
));

    $model->SetForeignKeyField('entered_by', 'Entered By', 'users', 'user_id', 'username');

    // This code shows how to use a custom <select>
    $select_sql = "SELECT note_record_status FROM notes GROUP BY note_record_status";
    $rst = $con->execute($select_sql);
    if($rst) {

        $status_menu = $rst->GetMenu('note_record_status', null, true);

    } else {
        db_error_handler($con, $select_sql);
    }

    
    // QF already has a field called note_record_status, so we must remove it before re-adding 
    $model->RemoveField('note_record_status');
    $model->AddCustomField('note_record_status', 2, $status_menu, null, _('Status Selector'));


    $view = new ADOdb_QuickForm_View($con, _('Edit Note'), 'POST');
    $view->SetReturnButton('Return to List', $return_url);

    $controller = new ADOdb_QuickForm_Controller(array(&$model), &$view);
    $form_html = $controller->ProcessAndRenderForm();

    $con->close();

echo "
        $form_html

";


	

	




} else {
	echo _("Examples are viewable by Administrators only");
}

end_page();


?>

