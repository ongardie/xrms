<?php
/**
 * Associated Companies
 *
 * Submit from companies to initiate company name search.
 *
 * @author Neil Roberts
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$from_what_id = $_POST['from_what_id'];
$to_what_id = $_POST['to_what_id'];
$working_direction = $_POST['working_direction'];
$return_url = $_POST['return_url'];
$relationship_name = $_POST['relationship_name'];
$from_what_table = $_POST['from_what_table'];
$to_what_table = $_POST['to_what_table'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$con->close();
if($working_direction == "from") {
  $page_title = "Add Company";
}
elseif($working_direction == "to") {
  $page_title = "Add Contact";
}
else {
  $page_title = "Add Association";
}

start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=new-company-2.php method=post>
        <input type="hidden" name="relationship_name" value="<?php echo $relationship_name; ?>">
        <input type="hidden" name="from_what_table" value="<?php echo $from_what_table; ?>">
        <input type="hidden" name="to_what_table" value="<?php echo $to_what_table; ?>">
        <input type="hidden" name="<?php echo $working_direction; ?>_what_id" value="<?php eval("echo \$$working_direction" . "_what_id;"); ?>">
        <input type="hidden" name="return_url" value="<?php echo $return_url ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>Search for Contact/Company</td>
            </tr>
            <tr>
                <td class=widget_label>Name or ID</td>
                <td class=widget_content_form_element><input type=text size=40 name="search_on"> <?php  echo $required_indicator ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Search"></td>
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
 * $Log: new-company.php,v $
 * Revision 1.1  2004/07/01 19:48:10  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 */
?>

