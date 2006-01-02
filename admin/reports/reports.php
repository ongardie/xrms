<?php
/**
 * admin/reports/reports.php - Administrator's Sales Performance Data
 *
 * Displays Sales Performance Data.
 *
 * $Id: reports.php,v 1.9 2006/01/02 22:07:25 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check( $this );

$msg = $_GET['msg'];

$con = get_xrms_dbconnection();

// $con->debug = 1;
    $period_from = $_POST['period_from'];
    $period_to = $_POST['period_to'];
    $user_id = $_POST['user_id'];
    $opp_name = $_POST['opp_name'];
    $act_name = $_POST['act_name'];
    $cases_name = $_POST['cases_name'];
    $opt_opp = $_POST['opt_opp'];
    $opt_act = $_POST['opt_act'];
    $opt_case = $_POST['opt_case'];

    $flag1 = 0; $flag2 = 0;
    $str_user_id = ''; $sql_opp .= ''; $sql_act=''; $sql_user = ''; $sql_case = ''; $str_temp = '';
    $rst_opp; $rst_act; $rst_case; $rst_user; $rst_temp;
    $sql_opp_temp = ''; $sql_act_temp = ''; $sql_case_temp = ''; $str_temp = '';
    $tab_opp = ''; $tab_act = ''; $tab_case = ''; $tab_both = '';

    if (strlen($period_from) > 0)
        $flag2 += 1;

    if (strlen($period_to) > 0)
        $flag2 += 1;

    $year = substr($period_from, 0, 4);
    $month = substr($period_from, 5, 2);
    $day = substr($period_from, 8, 2);



    if (!checkdate ($month, $day, $year))
        $flag2 = 0;
    else
        $period_from = $year .'-' . $month .'-'. ($day-1);

    $year = substr($period_to, 0, 4);
    $month = substr($period_to, 5, 2);
    $day = substr($period_to, 8, 2);


    if (!checkdate ($month, $day, $year))
        $flag2 = 0;
    else
        $period_to = $year .'-' . $month .'-'. ($day+1);

    $val_count = count($user_id);

    for($i=0;$i < $val_count; $i++)
    {
        if($i==0)
            $str_user_id = $user_id[$i];
        else
            $str_user_id = $str_user_id.','.$user_id[$i];
    }

    $str_user_id = '('. $str_user_id .')';

//opportunity sql
$sql_opp .= "select opp.opportunity_id,opp.company_id,opp.user_id,  opp.opportunity_title, opp.entered_at, ";
$sql_opp .= "opp.probability,opp.size,opp.close_at, os.opportunity_status_pretty_name, c.company_name,u.username ";
$sql_opp .= "from opportunities opp, users u, companies c, opportunity_statuses os ";
$sql_opp .= "where u.user_id = opp.user_id ";
$sql_opp .= "and opp.company_id = c.company_id ";
$sql_opp .= "and opp.opportunity_status_id = os.opportunity_status_id  ";
$sql_opp .= "and os.status_open_indicator = 'o' ";
$sql_opp .= "and (opp.entered_at between '" . $period_from . "' and '" . $period_to . "' )  ";
$sql_opp .= "and u.user_record_status = 'a' ";

//activity sql
$sql_act .= "select act1.activity_type_id,count(u.user_id) as ACT,act.activity_title, act1.activity_type_pretty_name ";
$sql_act .= "from activities act, users u, activity_types act1 ";
$sql_act .= "where act.user_id = u.user_id ";
$sql_act .= "and act.activity_type_id = act1.activity_type_id ";
$sql_act .= "and (act.entered_at between '" . $period_from . "' and '" . $period_to . "' )   ";
$sql_act .= "and u.user_record_status = 'a' ";

//case sql
$sql_case .= "select c1.case_title,c2.case_status_pretty_name ";
$sql_case .= "from cases c1,case_statuses c2,users u ";
$sql_case .= "where c1.case_status_id = c2.case_status_id ";
$sql_case .= "and c1.user_id = u.user_id ";
$sql_case .= "and (c1.entered_at between '" . $period_from . "' and '" . $period_to . "' )   ";
$sql_case .= "and u.user_record_status = 'a' ";

//user sql
$sql_user = "select username,last_name,first_names,user_id
             from users
             where user_id in ";

if (strlen($user_id) > 0)
    $flag1 = 1;

if (strlen($opp_name) > 0)
    $flag1 += 2;

if (strlen($act_name) > 0)
    $flag1 += 4;

if (strlen($cases_name) > 0)
    $flag1 += 8;

switch($flag1)
{

    case 0:
        if($flag2 != 2)
            break;
        else
        {
            $sql_user_temp = "select username,last_name,first_names,user_id from users"
                           . " where user_record_status = 'a' ";

            $rst_user = $con->execute($sql_user_temp);

            if ( $rst_user->NumRows() > 0 ) {
                $rst_temp = $rst_user;

                while (!$rst_user->EOF) {
                    $str_temp = $rst_user->fields['user_id'] . ',' . $str_temp;
                    $rst_user->movenext();
                }

                $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';

            } elseif (!$rst_user) {
                db_error_handler ($con, $sql_user_temp);
            }
        }
        break;

    case 1:
        $sql_user_temp = $sql_user;
        $str_temp = $str_user_id;
        $sql_user_temp .= $str_temp;
        $rst_user = $con->execute($sql_user_temp);

        if ( $rst_user->NumRows() > 0 ) {
            $rst_temp = $rst_user;
        } elseif (!$rst_user) {
            db_error_handler ($con, $sql_user_temp);
        }
        break;

    case 2:

        $sql_opp_temp = $sql_opp;
        $sql_opp_temp .= " and opp.opportunity_title like '%" . $opp_name . "%'";
        $sql_opp_temp .= " order by opp.company_id, opp.size DESC";

        $rst_opp = $con->execute($sql_opp_temp);

        if ($rst_opp->NumRows() > 0) {

            while (!$rst_opp->EOF) {
                $str_temp = $rst_opp->fields['user_id'] . ',' . $str_temp;
                $rst_opp->movenext();
            }

            $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';

        } else {
            $rst_user = NULL;
            if (!$rst_opp) { db_error_handler ($con, $sql_opp_temp); }
            break;
        }

        $sql_user_temp = $sql_user;
        $sql_user_temp .= $str_temp;

        $rst_user = $con->execute($sql_user_temp);

        if ($rst_user) {
            $rst_temp = $rst_user;
        } else {
            db_error_handler ($con, $sql_user_temp);
        }

        break;

    case 3:

        $sql_opp_temp = $sql_opp;

        $sql_opp_temp .= " and opp.user_id in " . $str_user_id;
        $sql_opp_temp .= " and opp.opportunity_title like '%" . $opp_name . "%'";
        $sql_opp_temp .= " order by opp.company_id, opp.size DESC";
        $rst_opp = $con->execute($sql_opp_temp);

        if ($rst_opp->NumRows() > 0) {
            while (!$rst_opp->EOF) {
                $str_temp = $rst_opp->fields['user_id'] . ',' . $str_temp;
                $rst_opp->movenext();
            }
            $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';
        } else {
            $rst_user = NULL;
            if (!$rst_opp) { db_error_handler ($con, $sql_opp_temp); }
            break;
        }

        $sql_user_temp = $sql_user;
        $sql_user_temp .= $str_temp;
        $rst_user = $con->execute($sql_user_temp);

        if ($rst_user) {
            $rst_temp = $rst_user;
        } else {
            db_error_handler ($con, $sql_user_temp);
        }

        break;

    case 4:

        $sql_act_temp = $sql_act;
        $sql_act_temp .= " and act.activity_title like '%" . $act_name . "%'";
        $sql_act_temp .= " group by act.activity_type_id";

        $rst_act = $con->execute($sql_act_temp);

        if ($rst_act->NumRows() > 0) {
            while (!$rst_act->EOF) {
                $str_temp = $rst_act->fields['user_id'] . ',' . $str_temp;
                $rst_act->movenext();
            }

            $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';
        } else {
            $rst_user = NULL;
            break;
        }

        $sql_user_temp = $sql_user;
        $sql_user_temp .= $str_temp;
        $rst_user = $con->execute($sql_user_temp);

        if ($rst_user) {
            $rst_temp = $rst_user;
        } else {
            db_error_handler ($con, $sql_user_temp);
        }

        break;

    case 5:

        $i=0;

        if($i == count($user_id))
        {

            while($i<count($user_id))
            {
                $sql_act_temp = $sql_act;
                $sql_act_temp .= " and act.user_id = " . $user_id[$i];
                $sql_act_temp .= " and act.activity_title like '%" . $act_name . "%'";
                $sql_act_temp .= " group by act.activity_type_id";
                $rst_act = $con->execute($sql_act_temp);

                if ($rst_act->NumRows() > 0) {
                    while (!$rst_act->EOF) {
                        $str_temp = $rst_act->fields['user_id'] . ',' . $str_temp;
                        $rst_act->movenext();
                    }

                    $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';
                }
            } //end while
        } else {
            $rst_user = NULL;
            break;
        } //end count

        $sql_user_temp = $sql_user;
        $sql_user_temp .= $str_temp;
        $rst_user = $con->execute($sql_user_temp);

        if ($rst_user) {
            $rst_temp = $rst_user;
        } else {
            db_error_handler ($con, $sql_user_temp);
        }

        break;

    case 6:

        $sql_opp_temp = $sql_opp;
        $sql_opp_temp .= " and opp.opportunity_title like '%" . $opp_name . "%'";
        $sql_opp_temp .= " order by opp.company_id, opp.size DESC";
        $rst_opp = $con->execute($sql_opp_temp);
        if ($rst_opp->NumRows() > 0) {

            while (!$rst_opp->EOF) {
                $str_temp = $rst_opp->fields['user_id'] . ',' . $str_temp;
                $rst_opp->movenext();
            }

            $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';

        } else {
            $rst_user = NULL;
            break;
        }

        $sql_user_temp = $sql_user;
        $sql_user_temp .= $str_temp;
        $rst_user = $con->execute($sql_user_temp);
        if ($rst_user->NumRows() > 0) {
            $rst_temp = $rst_user;
        } else {
            $rst_user = NULL;
            break;
        }

        $str_temp = '';
        $sql_act_temp = $sql_act;

        if ($rst_temp) {
            while (!$rst_temp->EOF) {
                $sql_act_temp = $sql_act;
                $sql_act_temp .= " and act.user_id = ".$rst_temp->fields['user_id'];
                $sql_act_temp .= " and act.activity_title like '%" . $act_name . "%'";
                $sql_act_temp .= " group by act.activity_type_id";
                $rst_act = $con->execute($sql_act_temp);

                $rst_temp->movenext();

                if ($rst_act->NumRows() > 0) {
                    if(strlen(str_temp) > 0)
                        $str_temp = $str_temp . ',' . $rst_act->fields['user_id'];
                    else
                        $str_temp = $rst_act->fields['user_id'];
                }
            }
        }

        if(strlen(str_temp) > 0)
        {
            $str_temp = '(' . $str_temp . ')';
            $sql_user_temp = $sql_user;
            $sql_user_temp .= $str_temp;
            $rst_user = $con->execute($sql_user_temp);
        }
        else
            $rst_user = NULL;

        break;

    case 7:

        $sql_opp_temp = $sql_opp;
        $sql_opp_temp .= " and opp.user_id in " . $str_user_id;
        $sql_opp_temp .= " and opp.opportunity_title like '%" . $opp_name . "%'";
        $sql_opp_temp .= " order by opp.company_id, opp.size DESC";
        $rst_opp = $con->execute($sql_opp_temp);

        if ($rst_opp->NumRows() > 0) {
            while (!$rst_opp->EOF) {
                $str_temp = $rst_opp->fields['user_id'] . ',' . $str_temp;
                $rst_opp->movenext();
            }

            $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $sql_user_temp = $sql_user;

        $sql_user_temp .= $str_temp;

        $rst_user = $con->execute($sql_user_temp);

        if ($rst_user->NumRows() > 0) {

            $rst_temp = $rst_user;

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $str_temp = '';

        $sql_act_temp = $sql_act;

        if ($rst_temp) {

            while (!$rst_temp->EOF) {

                $sql_act_temp = $sql_act;

                $sql_act_temp .= " and act.user_id = ".$rst_temp->fields['user_id'];

                $sql_act_temp .= " and act.activity_title like '%" . $act_name . "%'";

                $sql_act_temp .= " group by act.activity_type_id";

                $rst_act = $con->execute($sql_act_temp);

                $rst_temp->movenext();

                if ($rst_act->NumRows() > 0) {

                    if(strlen(str_temp) > 0)

                        $str_temp = $str_temp . ',' . $rst_act->fields['user_id'];

                    else

                        $str_temp = $rst_act->fields['user_id'];

                }

            }

        }

        if(strlen(str_temp) > 0)

        {

            $str_temp = '(' . $str_temp . ')';

            $sql_user_temp = $sql_user;

            $sql_user_temp .= $str_temp;

            $rst_user = $con->execute($sql_user_temp);

        }

        else

            $rst_user = NULL;

        break;

    case 8:

        $sql_case_temp = $sql_case;

        $sql_case_temp .= " and c1.case_title like '%" . $cases_name . "%'";

        $rst_case = $con->execute($sql_case_temp);

        if ($rst_case->NumRows() > 0) {

            while (!$rst_case->EOF) {

                $str_temp = $rst_case->fields['user_id'] . ',' . $str_temp;

                $rst_case->movenext();

            }

            $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $sql_user_temp = $sql_user;

        $sql_user_temp .= $str_temp;

        $rst_user = $con->execute($sql_user_temp);

        if ($rst_user) {

            $rst_temp = $rst_user;

        }

        break;

    case 9:

        $sql_case_temp = $sql_case;

        $sql_case_temp .= " and c1.user_id in " . $str_user_id;

        $sql_case_temp .= " and c1.case_title like '%" . $cases_name . "%'";

        $rst_case = $con->execute($sql_case_temp);

        if ($rst_case->NumRows() > 0) {

            while (!$rst_case->EOF) {

                $str_temp = $rst_case->fields['user_id'] . ',' . $str_temp;

                $rst_case->movenext();

            }

            $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $sql_user_temp = $sql_user;

        $sql_user_temp .= $str_temp;

        $rst_user = $con->execute($sql_user_temp);

        if ($rst_user) {

            $rst_temp = $rst_user;

        }

        break;

    case 10:

        $sql_opp_temp = $sql_opp;

        $sql_opp_temp .= " and opp.opportunity_title like '%" . $opp_name . "%'";

        $sql_opp_temp .= " order by opp.company_id, opp.size DESC";

        $rst_opp = $con->execute($sql_opp_temp);

        if ($rst_opp->NumRows() > 0) {

            while (!$rst_opp->EOF) {

                $str_temp = $rst_opp->fields['user_id'] . ',' . $str_temp;

                $rst_opp->movenext();

            }

            $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $sql_user_temp = $sql_user;

        $sql_user_temp .= $str_temp;

        $rst_user = $con->execute($sql_user_temp);

        if ($rst_user->NumRows() > 0) {

            $rst_temp = $rst_user;

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $str_temp = '';

        $sql_case_temp = $sql_case;

        if ($rst_temp) {

            while (!$rst_temp->EOF) {

                $sql_case_temp = $sql_case;

                $sql_case_temp .= " and c1.user_id = ".$rst_temp->fields['user_id'];

                $sql_case_temp .= " and c1.case_title like '%" . $cases_name . "%'";

                $rst_case = $con->execute($sql_case_temp);

                $rst_temp->movenext();

                if ($rst_case->NumRows() > 0) {

                    if(strlen(str_temp) > 0)

                        $str_temp = $str_temp . ',' . $rst_case->fields['user_id'];

                    else

                        $str_temp = $rst_case->fields['user_id'];

                }

            }

        }

        if(strlen(str_temp) > 0)

        {

            $str_temp = '(' . $str_temp . ')';

            $sql_user_temp = $sql_user;

            $sql_user_temp .= $str_temp;

            $rst_user = $con->execute($sql_user_temp);

        }

        else

            $rst_user = NULL;

        break;

    case 11:

        $sql_opp_temp = $sql_opp;

        $sql_opp_temp .= " and opp.user_id in " . $str_user_id;

        $sql_opp_temp .= " and opp.opportunity_title like '%" . $opp_name . "%'";

        $sql_opp_temp .= " order by opp.company_id, opp.size DESC";

        $rst_opp = $con->execute($sql_opp_temp);

        if ($rst_opp->NumRows() > 0) {

            while (!$rst_opp->EOF) {

                $str_temp = $rst_opp->fields['user_id'] . ',' . $str_temp;

                $rst_opp->movenext();

            }

            $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $sql_user_temp = $sql_user;

        $sql_user_temp .= $str_temp;

        $rst_user = $con->execute($sql_user_temp);

        if ($rst_user->NumRows() > 0) {

            $rst_temp = $rst_user;

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $str_temp = '';

        $sql_case_temp = $sql_case;

        if ($rst_temp) {

            while (!$rst_temp->EOF) {

                $sql_case_temp = $sql_case;

                $sql_case_temp .= " and c1.user_id = ".$rst_temp->fields['user_id'];

                $sql_case_temp .= " and c1.case_title like '%" . $cases_name . "%'";

                $rst_case = $con->execute($sql_case_temp);

                $rst_temp->movenext();

                if ($rst_case->NumRows() > 0) {

                    if(strlen(str_temp) > 0)

                        $str_temp = $str_temp . ',' . $rst_case->fields['user_id'];

                    else

                        $str_temp = $rst_case->fields['user_id'];

                }

            }

        }

        if(strlen(str_temp) > 0)

        {

            $str_temp = '(' . $str_temp . ')';

            $sql_user_temp = $sql_user;

            $sql_user_temp .= $str_temp;

            $rst_user = $con->execute($sql_user_temp);

        }

        else

            $rst_user = NULL;

        break;

    case 12:

        $sql_act_temp = $sql_act;

        $sql_act_temp .= " and act.activity_title like '%" . $act_name . "%'";

        $sql_act_temp .= " group by act.activity_type_id";

        $rst_act = $con->execute($sql_act_temp);

        if ($rst_act->NumRows() > 0) {

            while (!$rst_act->EOF) {

                $str_temp = $rst_act->fields['user_id'] . ',' . $str_temp;

                $rst_act->movenext();

            }

            $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $sql_user_temp = $sql_user;

        $sql_user_temp .= $str_temp;

        $rst_user = $con->execute($sql_user_temp);

        if ($rst_user->NumRows() > 0) {

            $rst_temp = $rst_user;

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $str_temp = '';

        $sql_case_temp = $sql_case;

        if ($rst_temp) {

            while (!$rst_temp->EOF) {

                $sql_case_temp = $sql_case;

                $sql_case_temp .= " and c1.user_id = ".$rst_temp->fields['user_id'];

                $sql_case_temp .= " and c1.case_title like '%" . $cases_name . "%'";

                $rst_case = $con->execute($sql_case_temp);

                $rst_temp->movenext();

                if ($rst_case->NumRows() > 0) {

                    if(strlen(str_temp) > 0)

                        $str_temp = $str_temp . ',' . $rst_case->fields['user_id'];

                    else

                        $str_temp = $rst_case->fields['user_id'];

                }

            }

        }

        if(strlen(str_temp) > 0)

        {

            $str_temp = '(' . $str_temp . ')';

            $sql_user_temp = $sql_user;

            $sql_user_temp .= $str_temp;

            $rst_user = $con->execute($sql_user_temp);

        }

        else

            $rst_user = NULL;

        break;

    case 13:

        $i=0;

        if($i == count($user_id))

        {

            while($i<count($user_id))

            {

                $sql_act_temp = $sql_act;

                $sql_act_temp .= " and act.user_id = " . $user_id[$i];

                $sql_act_temp .= " and act.activity_title like '%" . $act_name . "%'";

                $sql_act_temp .= " group by act.activity_type_id";

                $rst_act = $con->execute($sql_act_temp);

                if ($rst_act->NumRows() > 0) {

                    while (!$rst_act->EOF) {

                        $str_temp = $rst_act->fields['user_id'] . ',' . $str_temp;

                        $rst_act->movenext();

                    }

                    $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';

                }

            }

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $sql_user_temp = $sql_user;

        $sql_user_temp .= $str_temp;

        $rst_user = $con->execute($sql_user_temp);

        if ($rst_user->NumRows() > 0) {

            $rst_temp = $rst_user;

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $str_temp = '';

        $sql_case_temp = $sql_case;

        if ($rst_temp) {

            while (!$rst_temp->EOF) {

                $sql_case_temp = $sql_case;

                $sql_case_temp .= " and c1.user_id = ".$rst_temp->fields['user_id'];

                $sql_case_temp .= " and c1.case_title like '%" . $cases_name . "%'";

                $rst_case = $con->execute($sql_case_temp);

                $rst_temp->movenext();

                if ($rst_case->NumRows() > 0) {

                    if(strlen(str_temp) > 0)

                        $str_temp = $str_temp . ',' . $rst_case->fields['user_id'];

                    else

                        $str_temp = $rst_case->fields['user_id'];

                }

            }

        }

        if(strlen(str_temp) > 0)

        {

            $str_temp = '(' . $str_temp . ')';

            $sql_user_temp = $sql_user;

            $sql_user_temp .= $str_temp;

            $rst_user = $con->execute($sql_user_temp);

        }

        else

            $rst_user = NULL;

        break;

    case 14:

        $sql_opp_temp = $sql_opp;

        $sql_opp_temp .= " and opp.opportunity_title like '%" . $opp_name . "%'";

        $sql_opp_temp .= " order by opp.company_id, opp.size DESC";

        $rst_opp = $con->execute($sql_opp_temp);

        if ($rst_opp->NumRows() > 0) {

            while (!$rst_opp->EOF) {

                $str_temp = $rst_opp->fields['user_id'] . ',' . $str_temp;

                $rst_opp->movenext();

            }

            $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $sql_user_temp = $sql_user;

        $sql_user_temp .= $str_temp;

        $rst_user = $con->execute($sql_user_temp);

        if ($rst_user->NumRows() > 0) {

            $rst_temp = $rst_user;

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $str_temp = '';

        $sql_act_temp = $sql_act;

        if ($rst_temp) {

            while (!$rst_temp->EOF) {

                $sql_act_temp = $sql_act;

                $sql_act_temp .= " and act.user_id = ".$rst_temp->fields['user_id'];

                $sql_act_temp .= " and act.activity_title like '%" . $act_name . "%'";

                $sql_act_temp .= " group by act.activity_type_id";

                $rst_act = $con->execute($sql_act_temp);

                $rst_temp->movenext();

                if ($rst_act->NumRows() > 0) {

                    if(strlen(str_temp) > 0)

                        $str_temp = $str_temp . ',' . $rst_act->fields['user_id'];

                    else

                        $str_temp = $rst_act->fields['user_id'];

                }

            }

        }

        if(strlen(str_temp) > 0)

        {

            $str_temp = '(' . $str_temp . ')';

            $sql_user_temp = $sql_user;

            $sql_user_temp .= $str_temp;

            $rst_user = $con->execute($sql_user_temp);

            if ($rst_user->NumRows() > 0) {

                $rst_temp = $rst_user;

            }

            else

            {

                $rst_user = NULL;

                break;

            }

            $str_temp = '';

            $sql_case_temp = $sql_case;

            if ($rst_temp) {

                while (!$rst_temp->EOF) {

                    $sql_case_temp = $sql_case;

                    $sql_case_temp .= " and c1.user_id = ".$rst_temp->fields['user_id'];

                    $sql_case_temp .= " and c1.case_title like '%" . $cases_name . "%'";

                    $rst_case = $con->execute($sql_case_temp);

                    $rst_temp->movenext();

                    if ($rst_case->NumRows() > 0) {

                        if(strlen(str_temp) > 0)

                            $str_temp = $str_temp . ',' . $rst_case->fields['user_id'];

                        else

                            $str_temp = $rst_case->fields['user_id'];

                    }

                }

            }

        }

        else

            $rst_user = NULL;

        break;

    case 15:

        $sql_opp_temp = $sql_opp;

        $sql_opp_temp .= " and opp.user_id in " . $str_user_id;

        $sql_opp_temp .= " and opp.opportunity_title like '%" . $opp_name . "%'";

        $sql_opp_temp .= " order by opp.company_id, opp.size DESC";

        $rst_opp = $con->execute($sql_opp_temp);

        if ($rst_opp->NumRows() > 0) {

            while (!$rst_opp->EOF) {

                $str_temp = $rst_opp->fields['user_id'] . ',' . $str_temp;

                $rst_opp->movenext();

            }

            $str_temp = '(' . substr($str_temp,0,strlen($str_temp)-2) . ')';

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $sql_user_temp = $sql_user;

        $sql_user_temp .= $str_temp;

        $rst_user = $con->execute($sql_user_temp);

        if ($rst_user->NumRows() > 0) {

            $rst_temp = $rst_user;

        }

        else

        {

            $rst_user = NULL;

            break;

        }

        $str_temp = '';

        $sql_act_temp = $sql_act;

        if ($rst_temp) {

            while (!$rst_temp->EOF) {
                $sql_act_temp = $sql_act;
                $sql_act_temp .= " and act.user_id = ".$rst_temp->fields['user_id'];
                $sql_act_temp .= " and act.activity_title like '%" . $act_name . "%'";
                $sql_act_temp .= " group by act.activity_type_id";

                $rst_act = $con->execute($sql_act_temp);

                $rst_temp->movenext();

                if ($rst_act->NumRows() > 0) {
                    if(strlen(str_temp) > 0)
                        $str_temp = $str_temp . ',' . $rst_act->fields['user_id'];
                    else
                        $str_temp = $rst_act->fields['user_id'];
                }
            }
        }

        if(strlen(str_temp) > 0)
        {
            $str_temp = '(' . $str_temp . ')';
            $sql_user_temp = $sql_user;
            $sql_user_temp .= $str_temp;
            $rst_user = $con->execute($sql_user_temp);

            if ($rst_user->NumRows() > 0) {
                $rst_temp = $rst_user;
            }
            else
                break;

            $str_temp = '';
            $sql_case_temp = $sql_case;

            if ($rst_temp) {
                while (!$rst_temp->EOF) {
                    $sql_case_temp = $sql_case;
                    $sql_case_temp .= " and c1.user_id = ".$rst_temp->fields['user_id'];
                    $sql_case_temp .= " and c1.case_title like '%" . $cases_name . "%'";
                    $rst_case = $con->execute($sql_case_temp);
                    $rst_temp->movenext();

                    if ($rst_case->NumRows() > 0) {
                        if(strlen(str_temp) > 0)
                            $str_temp = $str_temp . ',' . $rst_case->fields['user_id'];
                        else
                            $str_temp = $rst_case->fields['user_id'];
                    }
                }
            }
        }
        else
            $rst_user = NULL;

        break;

    default:

        echo "No Records Searched ";

        break;

}

if($rst_user)
{
    $rst_user->movefirst();

    while(!$rst_user->EOF)
    {
        $tab_opp .= '<tr>'
                 . '<td colspan="7" class="widget_content"> '
                 . $rst_user->fields['username'] . '</td></tr>';

        $sql_opp_temp = $sql_opp;
        $sql_act_temp = $sql_act;
        $sql_case_temp = $sql_case;

        $sql_opp_temp .= " and opp.user_id = ".$rst_user->fields['user_id'];
        $sql_opp_temp .= " order by opp.company_id, opp.size DESC";
        $sql_act_temp .= " and act.user_id = " . $rst_user->fields['user_id'];
        $sql_act_temp .= " group by act.activity_type_id";
        $sql_case_temp .= " and c1.user_id = ".$rst_user->fields['user_id'];

        $rst_opp = $con->execute($sql_opp_temp);
        if (!$rst_opp) { db_error_handler ($con,$sql_opp_temp); };

        $rst_act = $con->execute($sql_act_temp);
        if (!$rst_act) { db_error_handler ($con,$sql_act_temp); };

        $rst_case = $con->execute($sql_case_temp);
        if (!$rst_case) { db_error_handler ($con,$sql_case_temp); };

/*        echo "user name = ". $rst_user->fields['user_id'] . "<br>";
        echo "<br> act = ". $sql_act_temp . "<br>";
        echo "<br>sql_opp_temp = ".$sql_opp_temp."<br><br>";
        echo "sql_act_temp = ".$sql_act_temp."<br><br>";
        echo "sql_case = ".$sql_case_temp."<br>";
        echo "in usr = ".$rst_user->NumRows();*/



        if ($rst_opp->NumRows() > 0) {
            while (!$rst_opp->EOF) {
                $tab_opp .= '<tr><td class="widget_content">&nbsp;</td>'
                          . '<td class="widget_content">' . $rst_opp->fields['opportunity_id'] . ' -- ' . $rst_opp->fields['company_name'] . '</td>'
                          . '<td class="widget_content">' . $rst_opp->fields['opportunity_title'] . '</td>'
                          . '<td class="widget_content">' . $rst_opp->fields['size'] . '</td>'
                          . '<td class="widget_content">' . $rst_opp->fields['entered_at'] . '</td>'
                          . '<td class="widget_content">' . $rst_opp->fields['opportunity_status_pretty_name'] . '</td>'
                          . '<td class="widget_content">' . $rst_opp->fields['probability'] . '</td></tr>';

                $rst_opp->movenext();
            }
            $rst_opp->close();
        } else {
            $tab_opp .= '<tr><td class="widget_content">&nbsp;</td>'
                      . '<td colspan="6" class="widget_content">No Records Found Here</td></tr>';
        }

//        $tab_both = '<tr><td class=widget_content>&nbsp;</td>';

        $tab_opp .= '<tr><td class=widget_content>&nbsp;</td>';

            if ($rst_act->NumRows() > 0) {
                $tab_opp .= '<td colspan="2">';
//                $tab_both .= '<td colspan="2">';
            } else {

                $tab_opp .= '<td colspan="2" class=widget_content>';
//                $tab_both .= '<td colspan="2" class=widget_content>';
            }

        if(strlen($opt_act) > 0)
        {
            if ($rst_act->NumRows() > 0) {
                $tab_opp .= '<table width="100%"><tr> '
                         . '<td class=widget_label>Activities</td>'
                         . '<td class=widget_label>Status</td></tr>';
//                echo "<br> rows act ".$rst_act->NumRows();

                while (!$rst_act->EOF) {
                        $tab_opp .= '<tr> '
                                 . '<td class=widget_content>' . $rst_act->fields['activity_type_pretty_name'] . '</td>'
                                 . '<td class=widget_content>' . $rst_act->fields['ACT'] . '</td></tr>';

                    $rst_act->movenext();
                }

                $tab_opp .= '</table>';
                $rst_act->close();
            }
        }

        $tab_opp .= '</td>';

        if ($rst_case->NumRows() > 0) {
            $tab_opp .= '<td colspan="4">';
        } else {
            $tab_opp .= '<td colspan="4" class=widget_content>';
        }

        if(strlen($opt_case) > 0)
        {
            if ($rst_case->NumRows() > 0) {
//                $tab_case .= '<table width="100%"><tr> '
                $tab_opp .= '<table width="100%" class=widget><tr> '
                         . '<td colspan="2" class=widget_label>Cases</td>'
                         . '<td colspan="2" class=widget_label>Status</td>';

                while (!$rst_case->EOF) {
//                        $tab_case .= '<tr> '
                        $tab_opp .= '<tr> '
                                 . '<td colspan="2" class=widget_content>' . $rst_case->fields['case_title'] . '</td>'
                                 . '<td colspan="2" class=widget_content>' . $rst_case->fields['case_status_pretty_name'] . '</td></tr>';

                    $rst_case->movenext();
                }
//                $tab_case .= '</table>';
                $tab_opp .= '</table>';
                $rst_case->close();
            }
        }

        $rst_user->movenext();

        $tab_opp .= '</td></tr>';
    }

    $rst_user->close();
} else
    $tab_opp = '<tr><td colspan="7" class="widget_content"> No Records Found Here </td></tr>';

$page_title = "Reporting";

start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=100% valign=top>
            <table cellspacing=1 width=100%>
                <tr>
                   <td colspan="7" valign=top class=lcol> <table cellspacing=1 width=100%>
                </tr>
                <tr>
                   <td class=widget_header>Sales Force Performance Data</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class=widget_label>User</td>
        <td class=widget_label>Opportunity (Sort 1 - company name, Sort 2 - Value)</td>
        <td class=widget_label>Opportunity Name</td>
        <td class=widget_label>Opportunity Value</td>
        <td class=widget_label>Decision Date</td>
        <td class=widget_label>Opportuntity Status</td>
        <td class=widget_label>Probability</td>
    </tr>

<?php
    if($flag2 == 2) {
        echo $tab_opp;
        //  echo $tab_empty;
        //  echo $tab_both;
    } else {
        echo <<< TILLEND
    <tr>
        <td height="22" colspan="7" class="widget_content">
            Invalid Date Entered, Please Enter in YYYY-MM-DD Format.</td>
    </tr>
TILLEND;
     }  //end else
//end php block
?>

</table>
   </td>
  </tr>
</table>

<?php

end_page();

/**
 * $Log: reports.php,v $
 * Revision 1.9  2006/01/02 22:07:25  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.8  2004/12/26 22:09:27  braverock
 * - removed Mac format line endings
 *
 *
 * Revision 1.5  2004/05/27 12:42:51  braverock
 * - fixed phpdoc Log entry
 *
 * Revision 1.4  2004/05/27 12:41:00  braverock
 * -fixed phpdoc
 *
 * Revision 1.3  2004/05/27  braverock
 * - added additional database error handling
 *
 * Revision 1.2  2004/05/27 12:03:03  braverock
 * - added additional database error handling
 *
 * Revision 1.1  2004/04/20 20:03:18  braverock
 * - add additional activity reporting to the admin interface
 *   - modified from SF patch 927132 submitted by s-t
 */
?>
