<?php
/**
 *
 * Companies by company source report.
 *
 * $Id: companies-by-company-source.php,v 1.8 2005/03/09 21:06:08 daturaarutad Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-graph.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$user_id = $_GET['user_id'];
$all_users = $_GET['all_users'];

if (!$user_id)
{
   $all_users = true;
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;


// JNH add for change user
$sqljnh = "select username, user_id from users where user_record_status = 'a' order by username";
$rstjnh = $con->execute($sqljnh);
$user_menu = $rstjnh->getmenu2('user_id',$user_id, false);
$rstjnh->close();
$con->close();

$page_title = _("Companies by Source");
start_page($page_title, true, $msg);

?>
<div id="Main">
    <div id="ContentFullWidth">
        <table class=widget cellspacing=1>
        <tr>
              <th class=widget_header><?php echo _("Companies by Source"); ?></th>
        </tr>
        <tr>
            <td class=widget_content_graph>
                  <img src="jpgraph-companies-by-company-source.php<?php
                  if ( $all_users )
                  {
                    echo "?all_users=on"; 
                  }
                  else
                  {
                     echo "?user_id=" . $user_id;
                  }  
                  ?>" border=0 align=center>
            </td>
        </tr>
        </table>
<?php
    echo "
    <table>
    <form method=get>
    <tr>
        <th>" . _("User") . "</th>
        <th></th>
    </tr>
    <tr>
            <td>" . $user_menu . "</td>
            <td>
                <input class=button type=submit value=";
    echo "\"";
    echo _("Change Graph");
    echo "\"";
    echo ">
            </td>
    </tr>
    <tr>
       <td>
                <input name=all_users type=checkbox ";

    if ($all_users) {
        echo "checked";
    }

    echo ">" . _("All Users") . "
       </td>
            <td>
            </td>
    </tr>
    </form>
    ";

?>

        </table>
    </div>
</div>

<?php

end_page();

/**
 * $Log: companies-by-company-source.php,v $
 * Revision 1.8  2005/03/09 21:06:08  daturaarutad
 * updated to use Jean-Noel HAYART changes: user filtering
 * updated to use JPGraph bar chart class
 *
 * Revision 1.7  2005/01/03 06:37:19  ebullient
 * update reports - graphs centered on page, reports surrounded by divs
 *
 * Revision 1.6  2004/07/20 18:36:58  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.5  2004/06/12 05:35:58  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.4  2004/04/17 15:57:03  maulani
 * - Add CSS2 positioning
 * - Add phpdoc
 *
 *
 */
?>
