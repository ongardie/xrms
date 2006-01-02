<?php


require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
  require_once $include_directory."classes/QuickForm/ADOdb_QuickForm.php";
require_once($include_directory."utils-activities.php");

$session_user_id = session_check( 'Admin' );


if (getGlobalVar($activity_type_id, 'activity_type_id')) { if (!$activity_type_id) $activity_type_id=(array_key_exists('activity_type_id',$_POST) ? $_POST['activity_type_id'] : $_GET['activity_type_id']); }
getGlobalVar($position_action,'position_action');
getGlobalVar($activity_participant_position_id,'activity_participant_position_id');
getGlobalVar($participant_position_name,'participant_position_name');
getGlobalVar($global_flag, 'global_flag');
getGlobalVar($return_url, 'return_url');
getGlobalVar($form_action,'form_action');
if (!$return_url) $return_url="one.php?activity_type_id=$activity_type_id";
//echo $return_url; exit;
if ($global_flag) {
    $rec['global_flag']=$global_flag;
}

$con = get_xrms_dbconnection();

switch ($position_action) {
    case 'new':
        if (!$participant_position_name) {
            $msg=urlencode(_("Failed to add participant position") . ': ' . _("No role specified"));
            Header("Location: {$http_site_root}{$return_url}&msg=$msg");
        }
        else {
        
            $new_position=add_participant_position($con, $activity_type_id, $participant_position_name);
//            echo "$new_position=add_participant_position($con, $activity_type_id, $activity_participant_position_name);";
            if ($new_position) {
                $msg=_("Successfully added participant position");
            } else $msg=_("Failed to add participant position");
            $msg=urlencode($msg);
            Header("Location: {$http_site_root}{$return_url}&msg=$msg"); exit;
        }        
    break;
    case 'edit':
    default:
        if (!array_key_exists('form_action',$_GET)) {
            $_GET['form_action']='edit';
         }
        if (!$activity_participant_position_id) { $msg=urlencode(_("Failed to edit position, no position id specified")); Header("Location: $http_site_root/private/home.php?msg=$msg"); }
        $activity_participant_position=get_activity_participant_positions($con,false, false, $activity_participant_position_id);        
        $participant_position_name= $activity_participant_position['participant_position_name'];
        $global_flag=$rst->fields['global_flag'];
        $model = new ADOdb_QuickForm_Model();
        $model->ReadSchemaFromDB($con, 'activity_participant_positions');
        
            $model->SetDisplayNames(array('participant_position_name' => 'Participant Position',
                                                                'global_flag' => 'Global'
                   ));
            $model->SetForeignKeyField('activity_type_id', 'Activity Type', 'activity_types', 'activity_type_id', 'activity_type_pretty_name', null, null, 'activity_type_pretty_name');
        
        $view = new ADOdb_QuickForm_View($con, 'Participant Position');
        $view->SetReturnButton('Return', $return_url);

  //don't run start page if updating (redirect instead)
 start_page("Edit Activity Participant Position: $participant_position_name");
  
  $controller = new ADOdb_QuickForm_Controller(array(&$model), &$view);
  $form_html = $controller->ProcessAndRenderForm();
  echo $form_html;
 break;
}



?>