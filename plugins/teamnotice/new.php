<?php


/**
*
* Basic QuickForm example 
*
*/

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'classes/QuickForm/ADOdb_QuickForm.php');

$session_user_id = session_check();

start_page();
?>

<h3> 
Team Notice Plugin
</h3>
<p>
Use this form to add new or delete team notices</p>
<?php


if(check_user_role(false, $session_user_id, 'Administrator')) {

    
    $return_url = "teamnotice_list.php";

	$con = &adonewconnection($xrms_db_dbtype);
	$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);


    $model = new ADOdb_QuickForm_Model();
    $model->ReadSchemaFromDB($con, 'teamnotices');
    $model->SetDisplayNames(array('notice_heading' => _('Notice Heading'),
                                  'notice_text' => _('Notice Text'),
                                  'status' => _('Status'),
));

    //$model->SetForeignKeyField('entered_by', 'Entered By', 'users', 'user_id', 'username');




    $view = new ADOdb_QuickForm_View($con, _('Edit Team Notice'), 'POST');
    $view->SetReturnButton('Return to List', $return_url);

    $controller = new ADOdb_QuickForm_Controller(array($model), $view);
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

