<?php
/**
 * Delete Company - Verify Delete
 *
 * Submit from companies-sidebar to verify deletion.
 *
 * @author Neil Roberts
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$working_direction = $_GET['working_direction'];
$relationship_id = $_GET['relationship_id'];
$return_url = $_GET['return_url'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$page_title = "Edit Association";
start_page($page_title, true, $msg);

$rst = $con->execute("select * from relationships where relationship_id='$relationship_id'");

if($working_direction == "from") {
    $rst2 = $con->execute("select company_name from companies where company_id='" . $rst->fields['to_what_id'] . "'");
    $name = $rst2->fields['company_name'];
    $rst2->close();
}
else {
    $rst2 = $con->execute("select first_names, last_name from contacts where contact_id='" . $rst->fields['from_what_id'] . "'");
    $name = $rst2->fields['first_names'] . " " . $rst2->fields['last_name'];
}

?>

<div id="Main">
    <div id="Content">

        <form action=company-edit-2.php method=post>
        <input type="hidden" name="working_direction" value="<?php echo $working_direction; ?>">
        <input type="hidden" name="return_url" value="<?php echo $return_url; ?>">
        <input type="hidden" name="relationship_id" value="<?php echo $relationship_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header>Name</td>
            </tr>
                <td class=widget_content_form_element>
                    <?php echo $name; ?>
               </td>
            </tr>
            <tr>
                <td class=widget_content_form_element>
                    <input class=button type=submit name=make_default value="Make Default"> &nbsp; &nbsp;
                    <input class=button type=submit name=unassociate value="Unassociate">
                </td>
            </tr>
        </table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        &nbsp;

    </div>
</div>

<?php

end_page();

/**
 * $Log: company-edit.php,v $
 * Revision 1.2  2004/07/05 22:13:27  introspectshun
 * - Include adodb-params.php
 *
 * Revision 1.1  2004/07/01 19:48:10  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 */
?>