<?php
/**
 * one_email-template.php - Display HTML form for a single email template
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 *
 * @author Aaron van Meerten
 * $Id: one_activity_resolution_type.php,v 1.2 2006/01/02 22:14:07 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once $include_directory."classes/QuickForm/ADOdb_QuickForm.php";

$session_user_id = session_check();


		// we need this for the companies foreign key lookup
	  $con = get_xrms_dbconnection();


getGlobalVar($return_url, 'return_url');
getGlobalVar($activity_resolution_type_id, 'activity_resolution_type_id');

$page_title = 'Manage Activity Resolution Type';

start_page($page_title);


  $model = new ADOdb_QuickForm_Model();
  $model->ReadSchemaFromDB($con, 'activity_resolution_types');

	$model->SetDisplayNames(array('resolution_short_name' => _("Short Name"), 
														'resolution_pretty_name' => _("Pretty Name"), 
														'sort_order' => _("Sort Order")));
                                                                                
        $model->SetFieldType('resolution_type_record_status', 'db_only');

  $view = new ADOdb_QuickForm_View($con, _("Activity Resolution Type"));
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

   $con->close();

  end_page();

/**
 * $Log: one_activity_resolution_type.php,v $
 * Revision 1.2  2006/01/02 22:14:07  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.1  2005/06/30 04:35:59  vanmer
 * -initial revision of a quickform for adding/editing an activity resolution type
 *
 * Revision 1.2  2005/06/24 22:37:45  vanmer
 * - added files sidebar when editing an email template
 *
 * Revision 1.1  2005/06/23 16:54:38  vanmer
 * - new interface for managing email templates and their types
 *
 *
 */
?>