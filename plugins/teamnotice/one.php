<?php
  /**
  * View a single Service Case
  *
  * $Id: one.php,v 1.1 2005/09/29 19:35:27 niclowe Exp $
  */

  //include required files
  require_once('../../include-locations.inc');

  require_once($include_directory . 'vars.php');
  require_once($include_directory . 'utils-interface.php');
  require_once($include_directory . 'utils-misc.php');
  require_once($include_directory . 'adodb/adodb.inc.php');
  require_once($include_directory . 'adodb-params.php');
  require_once($include_directory . 'classes/Pager/GUP_Pager.php');
  require_once($include_directory . 'classes/Pager/Pager_Columns.php');
//  require_once('../activities/activities-pager-functions.php');


  $teamnotice_id = $_GET['teamnotice_id'];
  $on_what_id=$teamnotice_id;
  $session_user_id = session_check();
  $msg = isset($_GET['msg']) ? $_GET['msg'] : '';

  $con = &adonewconnection($xrms_db_dbtype);
  $con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
  // $con->debug = 1;

  $form_name = 'One_teamnotice';

  //update_recent_items($con, $session_user_id, "teamnotices", $teamnotice_id);

  //get teamnotice details
  $sql = "SELECT *
  FROM teamnotices
  WHERE
  teamnotice_id='".$teamnotice_id."'";

  $rst = $con->execute($sql);

  if ($rst) {
    $notice_heading = $rst->fields['notice_heading'];
    $notice_text = $rst->fields['notice_text'];
    $status = $rst->fields['status'];
    $rst->close();
  } else {
    db_error_handler ($con, $sql);
  }

/*
  $columns = array();
  $columns[] = array('name' => _('Heading'), 'index_sql' => 'activity_title_link', 'sql_sort_column' => 'notice_heading');
  $columns[] = array('name' => _('Text'), 'index_sql' => 'notice_text');
  $columns[] = array('name' => _('Status'), 'index_sql' => 'status');

  // no reason to set this if you don't want all by default
  $default_columns = null;
  //$default_columns = array('activity_title_link', 'username','activity_type_pretty_name','contact_name','scheduled_at');

  // selects the columns this user is interested in
  $pager_columns = new Pager_Columns('teamnoticesActivitiesPager', $columns, $default_columns, $form_name);
  $pager_columns_button = $pager_columns->GetSelectableColumnsButton();
  $pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

  $columns = $pager_columns->GetUserColumns('default');

  $pager = new GUP_Pager($con, $sql_activities, 'GetActivitiesPagerData', _('Activities'), $form_name, 'teamnoticesActivitiesPager', $columns, false, true);

  $activity_rows = $pager->Render($system_rows_per_page);

*/
 
  add_audit_item($con, $session_user_id, 'viewed', 'teamnotices', $teamnotice_id, 3);
	

  $con->close();

  $page_title = _("teamnotice #") . $teamnotice_id . ": " . $teamnotice_title;
  start_page($page_title, true, $msg);

?>

<?php

  end_page();

?>
