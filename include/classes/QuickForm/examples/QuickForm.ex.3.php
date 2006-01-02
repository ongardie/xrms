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
QuickForm example 3
</h3>

<p>This example shows how to replace QuickForm's widget for a particular element, in this case 'Entered By'.</p>
<p>In this case, we want a &lt;select&gt; element to show in the 'new' and 'edit' states, and we want
the value from a foreign table to show in 'view' state.  In <a href="QuickForm.ex.1.php">QuickForm.ex.1.php</a>, we accomplished
this by using SetForeignKeyField().  But, there may be cases when you don't want to have QuickForm automatically perform the
foreign key lookup, where you want to supply the &lt;select&gt; (or other element) by hand.  </p>

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
                                  'note_record_status' => _('Status')));

    $model->SetDisplayOrders(array('note_description','entered_by'));




    // Of course, this query could be more complex!
    $select_sql = "SELECT user_id, username FROM users";
    $rst = $con->execute($select_sql);
    if($rst) {

        $users = $rst->GetAssoc();

        // add a blank item to the top of the generated <select>
        $users[0] = '';
        ksort($users);

        $model->SetSelectField('entered_by', _('Entered By'), $users); 

    } else {
        db_error_handler($con, $select_sql);
    }


    $view = new ADOdb_QuickForm_View($con, _('Edit Note'), 'POST');
    $view->SetReturnButton('Return to List', $return_url);

    $controller = new ADOdb_QuickForm_Controller(array(&$model), &$view);
    $form_html = $controller->ProcessAndRenderForm();

    $con->close();

    echo $form_html;

} else {
	echo _("Examples are viewable by Administrators only");
}

end_page();


?>

