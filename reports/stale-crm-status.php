<?php
/**
* Create a graph of activity for the requested user.
*
* @author Glenn Powers
*
* $Id: stale-crm-status.php,v 1.5 2011/04/05 18:10:58 gopherit Exp $
*/
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$starting = $_POST['starting'];
$ending = $_POST['ending'];
$user_id = (int)$_POST['user_id'];
$crm_status_id = (int)$_POST['crm_status_id'];
$type = $_POST['type'];
$friendly = $_POST['friendly'];
$send_email = $_POST['send_email'];
$send_email_to = $_POST['send_email_to'];
$all_users = $_POST['all_users'];
$display = $_POST['display'];

$userArray = array();

if (!$starting) {
			$starting = "1970-01-01";
}

if (!$ending) {
			$ending = "30 Days Ago";
			// $ending = date("Y-m-d H:i:s", mktime());
}

if (!$today) {
			$user_today = date("Y-m-d H:i:s", mktime());
}


if ($friendly) {
			$display = "";
}

$con = get_xrms_dbconnection();
//$con->debug = 1;

$sql = "SELECT username, user_id
        FROM users
        WHERE user_record_status = 'a'
        ORDER BY username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $user_id, false);

$user_starting = $rst->usertimestamp( date("Y-m-d H:i:s", strtotime($starting)));
$user_ending = $rst->usertimestamp( date("Y-m-d H:i:s", strtotime($ending)));
$rst->close();

$sql = "SELECT crm_status_short_name, crm_status_id
        FROM crm_statuses
        WHERE crm_status_record_status = 'a'
        ORDER BY crm_status_short_name";
$rst = $con->execute($sql);
$crm_status_menu = $rst->getmenu2('crm_status_id', $crm_status_id, false);
$rst->close();

$page_title = "Stale Companies";
if (($display) || (!$friendly)) {
    start_page($page_title, true, $msg);
    ?>
        <p>&nbsp;</p>
        <form action="stale-crm-status.php" method="POST" name="CompanyForm">
            <input type=hidden name=display value=y>

            <table>

                <tr>
                    <th align=left>Start</th>
                    <th align=left>End</th>
                    <th align=left>Crm Status</th>
                    <th align=left>User</th>
                    <th align=left></th>
                </tr>

                <tr>
                    <td><input type=text name=starting value="<?php echo $starting; ?>"></td>
                    <td><input type=text name=ending value="<?php echo $ending; ?>"></td>
                    <td><?php echo $crm_status_menu; ?></td>
                    <td>
                        <?php echo $user_menu; ?>
                        <input name=all_users type=checkbox <?php if ($all_users) echo '"checked"'; ?>>All Users
                    </td>
                    <td>
                        <input class=button type=submit value=Go>
                    </td>
                </tr>

            </table>

            <p>&nbsp;</p>

    <?php
}
        if (($user_id) && (!$all_users)) {
            $userArray = array($user_id);
        }

        if ($all_users) {
            $sql = "SELECT user_id
                    FROM users";
            $rst = $con->execute($sql);
            while (!$rst->EOF) {
                array_push($userArray, $rst->fields['user_id']);
                $rst->movenext();
            }
            $rst->close();
        }

        if ($userArray) {
            $crm_status_id=(int)$_REQUEST['crm_status_id'];
            $userlist="(";
            $i=0;
            $num_users=count($userArray);
            if($num_users==1){
                $userlist.=$userArray[0].")";
            } else {
                $i=1;
                foreach ($userArray as $key => $user_id) {
                    if($i<>$num_users)$userlist.=$user_id.",";
                    if($i==$num_users)$userlist.=$user_id.")";
                    $i++;
                }
            }
            $sql = 'SELECT  '. $con->Concat("'<a id=\"'" , 'c.company_name', "'\" href=\"one.php?company_id='", 'c.company_id', "'\">'", 'c.company_name', "'</a>'"). " AS company_url,
                            MAX(a.last_modified_at) as last_modified_at
                    FROM companies c
                    LEFT JOIN activities a
                        ON a.company_id=c.company_id
                    WHERE c.crm_status_id=$crm_status_id
                        AND c.user_id IN $userlist
                        AND c.company_record_status='a'
                        AND ( (a.activity_record_status='a')
                            OR ISNULL(a.last_modified_at) )
                    GROUP BY c.company_name
                        HAVING MAX(a.last_modified_at)<". $con->qstr($user_ending, get_magic_quotes_gpc()) ."
                            AND MAX(a.last_modified_at)>". $con->qstr($user_starting, get_magic_quotes_gpc());

            $pager_id='CompanyPager';
            $form_id='CompanyForm';

            $columns = array();
            $columns[] = array('name' => 'Company', 'index_sql' => 'company_url');
            $columns[] = array('name' => 'Last Activity Date', 'index_sql' => 'last_modified_at');

            $default_columns=array('company_url','last_modified_at');

            $pager_columns = new Pager_Columns($pager_id, $columns, $default_columns, $form_id);
            $pager_columns_button = $pager_columns->GetSelectableColumnsButton();
            $pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

            $pager = new GUP_Pager($con, $sql, NULL, _("Stale Companies"), $form_id, $pager_id, $columns);
            $pager->Render($system_rows_per_page);//having difficulty with pager next buttons..did not have time to debug fully.

        }
        $con->close();
        ?>
    </form>


<?php
if (($display) || (!$friendly)) {
    end_page();
}

/**
* $Log: stale-crm-status.php,v $
* Revision 1.5  2011/04/05 18:10:58  gopherit
* FIXED Bug Artifact #1585360 There were multiple issues with the way the CompanyPager was instantiated and sortable URL are generated.
* FIXED Bug Artifact # 3276472 The End time input of the stale companies is now factored in in the search SQL.
*
* Revision 1.4  2007/09/17 14:34:49  myelocyte
* - fixed bug: "[ 1737224 ] Table opportunity_types in Closed Items Report"
* - enabled session_check() to some reports to solve "implode function" error
*
* Revision 1.3  2006/09/22 17:14:00  niclowe
* fixed minor bug that meant not all stale crm status companies were shown.
*
* Revision 1.2  2006/01/30 17:48:01  niclowe
* fixed bug in userlist for all users.
*
*/
?>
