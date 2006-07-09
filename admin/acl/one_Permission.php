<?php
/**
 * one_Permission.php - Display HTML form for a single Permission.
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @author Justin Cooper
 * $Id: one_Permission.php,v 1.5 2006/07/09 05:04:03 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check('Admin');

require_once ($include_directory.'classes/acl/xrms_acl_config.php');


global $symbol_precendence;

	$con = get_acl_dbconnection();
	
	// $con->debug=1;
	
	// we need this for the companies foreign key lookup
	$xcon = get_xrms_dbconnection();

	getGlobalVar($return_url, 'return_url');


	$page_title = 'Manage Permissions';
        
	start_page($page_title);

  require_once($include_directory ."classes/QuickForm/ADOdb_QuickForm.php");



  $model = new ADOdb_QuickForm_Model();
  $model->ReadSchemaFromDB($con, 'Permission');
  $model->SetPrimaryKeyName('Permission_id');
  $model->SetDisplayNames(array('Permission_name' => 'Permission Name', 'Permission_abbr' => 'Abbreviation', 'Permission_filter' => 'Filter'));

  $view = new ADOdb_QuickForm_View($con, 'Permission');
  $view->SetReturnButton('Return to List', $return_url);

  $controller = new ADOdb_QuickForm_Controller(array(&$model), &$view);
  $form_html = $controller->ProcessAndRenderForm();

	$con->close();

?>


<div id="Main">
<?php require_once('xrms_acl_nav.php'); ?>
<div id="Content">
<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=30% valign=top>
					<?php echo $form_html ?>
        </td>
    </tr>
</table>
</div>

<?php

end_page();

/**
 * $Log: one_Permission.php,v $
 * Revision 1.5  2006/07/09 05:04:03  vanmer
 * - patched ACL interface to check for admin access
 *
 * Revision 1.4  2006/01/02 22:27:11  vanmer
 * - removed force of css theme for ACL interface
 * - changed to use centralized dbconnection function
 *
 * Revision 1.3  2005/08/11 22:53:53  vanmer
 * - changed to use ACL dbconnection
 *
 * Revision 1.2  2005/03/05 00:52:34  daturaarutad
 * manually setting primary keys until mssql driver supports metacolumns fully
 *
 * Revision 1.1  2005/01/13 17:16:16  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.3  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.2  2004/12/02 09:34:45  ke
 * - added navigation sidebar to all one_ pages
 *
 * Revision 1.1  2004/12/02 04:37:56  justin
 * initial version
 *
 *
 */
?>
