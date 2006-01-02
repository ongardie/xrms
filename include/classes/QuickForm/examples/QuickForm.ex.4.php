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
QuickForm example 1
</h3>
<p>
Example showing usage of FCKEditor and QuickForm for the 'notes' table.</p>

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

    $model->SetFieldType('note_description', 'fckeditor');




    $view = new ADOdb_QuickForm_View($con, _('Edit Note'), 'POST');
    $view->SetReturnButton('Return to List', $return_url);
    $view->SetButtonText('A B C', 'Easy As', '1 2 3');

    $controller = new ADOdb_QuickForm_Controller(array(&$model), &$view);
    $form_html = $controller->ProcessAndRenderForm();

    $con->close();

echo <<<END

<div id="Main">
    <?php echo $form_html; ?>
</div>
END;


} else {
	echo _("Examples are viewable by Administrators only");
}

end_page();


?>

