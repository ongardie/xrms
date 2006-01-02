<?php
/**
 * Edit the information for a system parameter
 *
 * $Id: one.php,v 1.6 2006/01/02 22:07:25 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$param_id = $_GET['param_id'];

$con = get_xrms_dbconnection();

$sql ="select string_val, int_val, float_val, datetime_val, description from system_parameters where param_id='$param_id'";
$sysst = $con->execute($sql);
if ($sysst) {

  // is the requested record in the database ???
  if ( $sysst->RecordCount() == 1 ) {
	// yes - it was found

	$string_val   = $sysst->fields['string_val'];
	$int_val      = $sysst->fields['int_val'];
	$float_val    = $sysst->fields['float_val'];
	$datetime_val = $sysst->fields['datetime_val'];
    $description  = $sysst->fields['description'];

	if (!is_null($string_val)) {
		$my_val=$string_val;
		$type = 'string_val';
	} elseif (!is_null($int_val)) {
		$my_val=$int_val;
		$type = 'int_val';
	} elseif (!is_null($float_val)) {
		$my_val=$float_val;
		$type = 'float_val';
	} elseif (!is_null($datetime_val)) {
		$my_val=$datetime_val;
		$type = 'datetime_val';
	} else {
		echo _('Failure to get system parameter ') . $param . _('.  The data entry appears to be corrupted.');
		exit;
	}
  } else {
	// no - it was not found

	echo _('Failure to get system parameter ') . $param . _('.  Make sure you have run the administration update.');
	exit;

  } // if ( $sysst->RecordCount() > 0 ) ...

  // close the recordset
  $sysst->close();
} else {
	//there was a problem, notify the user
	db_error_handler ($con, $sql);
	exit;
}

//get case details
$sql = "select description from system_parameters where param_id = '$param_id'";

$rst = $con->execute($sql);

if ($rst) {
    $description = $rst->fields['description'];
    $rst->close();
} else {
    db_error_handler ($con, $sql);
}

$options = false;
$sql = "select $type from system_parameters_options where param_id = '$param_id' order by sort_order";
$rst = $con->execute($sql);
if ($rst) {

  // Are there option records in the database ???
  if ( $rst->RecordCount() > 0 ) {
	// yes - they were found
    $options = true;
    $options_menu = $rst->getmenu($type, $my_val, false);
    $rst->close();
  }
} else {
    db_error_handler ($con, $sql);
}

$con->close();

if ($options) {
	$field = $options_menu;
	$field = str_replace($type, "param_value", $field);
} else {
	$field = "<input type=text size=40 name=param_value value=\"$my_val\">";
}

$page_title = _("System Parameter")." : "._($param_id);
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=param_id value="<?php  echo $param_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Edit System Parameter"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _($param_id); ?></td>
                <td class=widget_content_form_element><?php echo _($field); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><?php echo _($description); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>
        </table>
        </form>

    </div>
</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.6  2006/01/02 22:07:25  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.5  2005/05/10 13:32:21  braverock
 * - localized string patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.4  2005/02/05 16:39:38  maulani
 * - Fix field name
 *
 * Revision 1.3  2005/02/05 14:25:56  maulani
 * - Use popup menu for entry of parameters that have a discrete list of
 *   options
 *
 * Revision 1.2  2005/01/24 00:17:19  maulani
 * - Add description to system parameters
 *
 * Revision 1.1  2004/07/14 16:23:37  maulani
 * - Add administrator capability to modify system parameters
 *
 *
 */
?>