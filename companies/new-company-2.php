<?php
/**
 * Associated Companies
 *
 * Submit from new-companies to return companies from company name/id search.
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

$relationship_name = $_POST['relationship_name'];
$from_what_table = $_POST['on_what_table'];
$to_what_table = $_POST['to_what_table'];
$from_what_id = $_POST['from_what_id'];
$to_what_id = $_POST['to_what_id'];
$return_url = $_POST['return_url'];
$search_on = str_replace("'","\\'",$_POST['search_on']);

$what_table['from'] = "contacts";
$what_table_singular['from'] = "contact";
$what_table['to'] = "companies";
$what_table_singular['to'] = "company";
if($from_what_id) {
    $working_direction = "from";
    $opposite_direction = "to";
    $display_name = "Contacts";
    $display_name_singular = "Contact";
    $opposite_name = "Companies";
    $overall_id = $contact_id;
}
else {
    $working_direction = "to";
    $opposite_direction = "from";
    $display_name = "Companies";
    $display_name_singular = "Company";
    $opposite_name = "Contacts";
    $overall_id = $company_id;
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

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

        <form action=new-company-3.php method=post>
        <input type="hidden" name="relationship_name" value="<?php echo $relationship_name; ?>">
        <input type="hidden" name="from_what_table" value="<?php echo $from_what_table; ?>">
        <input type="hidden" name="to_what_table" value="<?php echo $to_what_table; ?>">
        <input type="hidden" name="<?php echo $working_direction; ?>_what_id" value="<?php eval("echo \$$working_direction" . "_what_id;"); ?>">
        <input type="hidden" name="return_url" value="<?php echo $return_url ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header>Contacts/Companies</td>
            </tr>
                <td class=widget_content_form_element>
<?php

    $sql = "select * from relationship_types where relationship_name='$relationship_name'";
    $rst = $con->execute($sql);
    echo "<select name=relationship_type_id>\n";
    while(!$rst->EOF) {
        print "<option value=" . $rst->fields['relationship_type_id'] . ">" . $rst->fields[$working_direction . '_what_text'] . "</option>\n";
        $rst->movenext();
    }
    echo "</select> &nbsp; \n";

if(eregi("[a-zA-Z]", $search_on)) {
    if($working_direction == "from") {
      $sql = "select c.company_id, c.company_name, a.city, a.province
        from companies as c, addresses as a
        where c.company_name like '%$search_on%'
        and c.default_primary_address=a.address_id
        order by c.company_name";
    }
    else {
      $sql = "select c.contact_id, c.first_names, c.last_name
        from contacts as c
        where c.last_name like '%$search_on%' or c.first_names like '%$search_on%'
        order by c.last_name";
    }
    $rst = $con->execute($sql);
    if($rst->rowcount() > 1) {
        print "                    <select name=on_what_id>\n";
        while(!$rst->EOF) {
            if($working_direction == "from") {
              $name = $rst->fields['company_name'];
            }
            else {
              $name = $rst->fields['first_names'] . " " . $rst->fields['last_name'];
            }
            echo "<option value=" . $rst->fields[$what_table_singular[$opposite_direction] . '_id'] . ">" . $name;
            if($working_direction == "from") {
              echo " - " . $rst->fields['city'] . ", " . $rst->fields['province'];
            }
            echo "</option>\n";
            $rst->movenext();
        }
        print "                     </select>\n";
    }
    elseif($rst->rowcount() == 1) {
        if($working_direction == "from") {
            $name = $rst->fields['company_name'];
        }
        else {
            $name = $rst->fields['first_names'] . " " . $rst->fields['last_name'];
        }
        echo "<input type=hidden name=on_what_id value=" . $rst->fields[$what_table_singular[$opposite_direction] . '_id'] . ">" . $name;
        if($working_direction == "from") {
            echo " - " . $rst->fields['city'] . ", " . $rst->fields['province'] . "\n";
        }
    }
    else {
        echo "There is no company by that name";
    }
}
else {
    $sql = "select c.company_id, c.company_name, a.city, a.province
        from companies as c, addresses as a
        where c.company_id='$company_search'
        and c.default_primary_address=a.address_id";
    $rst = $con->execute($sql);
    if($rst->rowcount() > 0) {
        echo "<input type=hidden name=on_what_id value=$company_search>" . $rst->fields['company_name'] . " - "
            . $rst->fields['city'] . ", " . $rst->fields['province'] . "\n";
    }
    else {
        echo "There is no company by that ID";
    }
}

?>
               </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo $page_title; ?>"></td>
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
 * $Log: new-company-2.php,v $
 * Revision 1.1  2004/07/01 19:48:10  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 */
?>