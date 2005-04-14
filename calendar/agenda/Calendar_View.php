<?php

include("$include_directory../calendar/global.inc");
include("$include_directory../calendar/global_pref.inc");

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

require("agenda_query.php");
require("agenda_display.php");


/**
* Note: user of this class must provide a form and a hidden form field for the calendar_start_date f
*	
*/
class CalendarView {

	var $start_date;
	var $calendar_date_field;
	var $form_id;

	function CalendarView($form_id_in, $calendar_date_field) {

		getGlobalVar($calendar_start_date, $calendar_date_field);

		global $form_id;
		$form_id = $form_id_in;

		$this->form_id = $form_id_in;
		$this->calendar_date_field = $calendar_date_field;

		$this->start_date = $calendar_start_date;
	}

	function RenderMonth($rs) {
		return $this->Render('month', $rs);
	}
	function RenderWeek($rs, $start_date) {
		return $this->Render('week', $rs);
	}

	/**
	* @param string one of: 'month','week','day'
	* @param array array of assoc arrays of activity values.  expected fields are:
		activity_id, scheduled_at, ends_at, contact_id, activity_title, activity_description, user_id

	*/
	function Render($type, $activity_data) {


		$display["result"] .= "<!-- Calendar Begins -->\n";
		$display["result"] .= "<input type=hidden name=\"{$this->calendar_date_field}\" value=\"{$this->start_date}\">";

		// week planning details
  		global $set_theme,$ico_left_day,$ico_right_day,$ico_spacer,$l_description ,$ico_event,$auth;
  		global $set_weekstart_default, $set_start_time, $set_stop_time,$set_cal_interval,$ico_new_event;
  		global $l_private,$l_private_description;
/*
		echo "global $set_theme,$ico_left_day,$ico_right_day,$ico_spacer,$l_description ,$ico_event,$auth;<br>";
        echo "global $set_weekstart_default, $set_start_time, $set_stop_time,$set_cal_interval,$ico_new_event;<br>";
        echo "global $l_private,$l_private_description;<br>";
*/
		global $param_date;
		//global $tf_date_begin;
		//$tf_date_begin = $this->start_date;

		$param_date	= date('Ymd', strtotime($this->start_date));

		$set_weekstart_default = 'Sunday';

		if (count($sel_user_id) != 0 ) {
  			$agenda_user_view = $sel_user_id;
  		}
  		//$sess->register("agenda_user_view");
  		//page_close();
  		$sel_user_id = $agenda_user_view;
  		getGlobalVar($action,"action");
  		if ($action == "") $action = "index";
  		$agenda = $this->get_param_agenda();
  		$this->get_agenda_action();
  		//$perm->check_permissions($menu, $action);


	  	$sel_user_id = slice_user($sel_user_id);
	  	$obm_wait = run_query_waiting_events();

    	if(count($sel_user_id) != 0) { 
      		$p_user_array =  $sel_user_id;
    	} else {
      		$p_user_array =  array($auth->auth["uid"]);
    	}
    	require("agenda_js.inc");

//echo "activity calendar rst is $rs<BR>";
  	
/*
    	if (!$activity_calendar_rst)
        	$obm_q = run_query_week_event_list($agenda,$p_user_array);
    	else $obm_q = $activity_calendar_rst;
*/
	
    	$user_q = store_users(run_query_get_user_name($p_user_array));
    	$user_obm = run_query_userobm_readable();

// you could handle this one better
global $calendar_user;


		switch($type) {
			case 'month':
    			$display["result"] .= dis_month_planning($agenda,$activity_data,$user_q,$user_obm);
				break;
			case 'week':
    			$display["result"] .= dis_week_planning($agenda,$activity_data,$user_q,$user_obm);
				break;
		}

		//$display['result'].= '<link rel="STYLESHEET" type="text/css" href="../css/calendar.css" />';
    	$display['features'] = html_planning_bar($agenda,$user_obm, $p_user_array,$user_q);

		$display["result"] .= "<!-- Calendar Ends -->\n";

		return $display;

	}

/**
* Returns the javascript functions like calendar_next_day that are used by the buttons in the calendar.
*/
function GetCalendarJS() {

	$calendar_start_date = $this->start_date;
	$date_field_name = $this->calendar_date_field;

        $year = date('Y', strtotime($calendar_start_date));
        $month = date('m', strtotime($calendar_start_date));
        $day = date('d', strtotime($calendar_start_date));

        $month_n = $month+1;
        if($month_n > 12) {
            $month_n = 1;
            $year++;
        }
        $next_month =  "$year-$month_n-$day";

        $month_p = $month-1;
        if(!$month_p) {
            $month_p=12;
            $year--;
        }
        $prev_month = "$year-$month_p-$day";

	return "
<script language=\"JavaScript\" type=\"text/javascript\">

function calendar_next_day() {
    document.{$this->form_id}.$date_field_name.value = '" .  date('Y-m-d', strtotime($calendar_start_date . ' +1 days')) . "'; 
    document.{$this->form_id}.submit(); 
}
function calendar_next_week() {
    document.{$this->form_id}.$date_field_name.value = '" .  date('Y-m-d', strtotime($calendar_start_date . ' +7 days')) . "'; 
    document.{$this->form_id}.submit(); 
}
function calendar_next_month() {
    document.{$this->form_id}.$date_field_name.value = '$next_month';
    document.{$this->form_id}.submit(); 
}

function calendar_previous_week() {
    document.{$this->form_id}.$date_field_name.value = '" .  date('Y-m-d', strtotime($calendar_start_date . ' -7 days')) . "'; 
    document.{$this->form_id}.submit(); 
}
function calendar_previous_day() {
    document.{$this->form_id}.$date_field_name.value = '" .  date('Y-m-d', strtotime($calendar_start_date . ' -1 days')) . "'; 
    document.{$this->form_id}.submit(); 
}
function calendar_previous_month() {
    document.{$this->form_id}.$date_field_name.value = '$prev_month';
    document.{$this->form_id}.submit(); 
}
</script>
	";
}



// Stores in $agenda hash, Agenda parameters transmited
// returns : $agenda hash with parameters set
///////////////////////////////////////////////////////////////////////////////
function get_param_agenda() {
  global $param_date,$param_event,$tf_title,$sel_category_id,$sel_priority,$ta_event_description;
  global $set_start_time, $set_stop_time,$tf_date_begin,$sel_time_begin,$sel_min_begin,$sel_time_end,$sel_min_end;
  global $tf_date_end,$sel_repeat_kind,$hd_conflict_end,$hd_old_end,$hd_old_begin,$action,$param_user;
  global $cdg_param,$cb_repeatday_0,$cb_repeatday_1,$cb_repeatday_2,$cb_repeatday_3,$cb_repeatday_4,$cb_repeatday_5;
  global $cb_repeatday_6,$cb_repeatday_7,$tf_repeat_end,$cb_force,$cb_privacy,$cb_repeat_update,$rd_conflict_event;
  global $hd_date_begin, $hd_date_end,$rd_decision_event,$param_date_begin,$param_date_end,$cb_mail,$param_duration;
  global $sel_accept_write,$sel_deny_write,$sel_deny_read,$sel_accept_read,$sel_time_duration,$sel_min_duration;
  global $hd_category_label,$tf_category_upd, $sel_category,$tf_category_new,$sel_grp_id;

  // Agenda fields
  if (isset($tf_category_new)) $agenda["category_label"] = $tf_category_new;
  if (isset($hd_category_label)) $agenda["category_label"] = $hd_category_label;
  if (isset($tf_category_upd)) $agenda["category_label"] = $tf_category_upd;
  if (isset($sel_category)) $agenda["category_id"] = $sel_category;
  if (isset ($param_date))
    $agenda["date"] = $param_date;
  else
    $agenda["date"] = date("Ymd",time());

  if (isset($param_event)) $agenda["id"] = $param_event;
  if (isset($tf_title)) $agenda["title"] = $tf_title;
  if (isset($sel_category_id)) $agenda["category"] = $sel_category_id;
  if (isset($sel_priority)) $agenda["priority"] = $sel_priority;
  if (isset($ta_event_description)) $agenda["description"] = $ta_event_description;
  if (isset($cb_force))  $agenda["force"] = $cb_force;
  if (isset($cb_privacy))  $agenda["privacy"] = $cb_privacy;
  if (is_array($rd_conflict_event)) $agenda["conflict_event"] = $rd_conflict_event;
  if (is_array($hd_conflict_end)) $agenda["conflict_end"] = $hd_conflict_end;
  if (isset($hd_old_begin)) $agenda["old_begin"] = $hd_old_begin;
  if (isset($hd_old_end)) $agenda["old_end"] = $hd_old_end;
  if (isset($cb_mail)) $agenda["mail"] = $cb_mail;
  if (is_array($sel_accept_write)) $agenda["accept_w"] = $sel_accept_write;
  if (is_array($sel_deny_write)) $agenda["deny_w"] = $sel_deny_write;
  if (is_array($sel_deny_read)) $agenda["deny_r"] = $sel_deny_read;
  if (is_array($sel_accept_read)) $agenda["accept_r"] = $sel_accept_read;
  if (isset($sel_time_duration)) {
    $agenda["duration"] = $sel_time_duration;
    if(isset($sel_min_duration)) {
      $agenda["duration"] +=  $sel_min_duration/60;
    }
  }
  if(isset($param_user)) $agenda["user_id"] = $param_user;
  if(isset($param_duration)) $agenda["duration"] = $param_duration;
  if (isset($tf_repeat_end)){
    ereg ("([0-9]{4}).([0-9]{2}).([0-9]{2})",$tf_repeat_end , $day_array1);
    $agenda["repeat_end"] =  $day_array1[1].$day_array1[2].$day_array1[3];
   }
  if (isset($cb_repeat_update)) $agenda["repeat_update"] = 1;
  if (isset($tf_date_begin)) {
    ereg ("([0-9]{4}).([0-9]{2}).([0-9]{2})",$tf_date_begin , $day_array2);
    $agenda["date_begin"] .=  $day_array2[1].$day_array2[2].$day_array2[3];
    $agenda["date"] = $agenda["date_begin"];
    if (isset($sel_time_begin) && isset($sel_min_begin)) {
      $agenda["date_begin"] = $agenda["date_begin"].$sel_time_begin.$sel_min_begin;
    }
    else {
      $agenda["date_begin"] = date("YmdHi",strtotime("+$set_start_time hours",strtotime($agenda["date_begin"])));
    }
  }
  else {
    $agenda["date_begin"] = date("YmdHi",strtotime("+$set_start_time hours",strtotime(date("Ymd"))));
  }

  if (isset($tf_date_end)) {
    ereg ("([0-9]{4}).([0-9]{2}).([0-9]{2})",$tf_date_end , $day_array);
    $agenda["date_end"] =  $day_array[1].$day_array[2].$day_array[3];
    if (isset($sel_time_end) && isset($sel_min_end)) {
      $agenda["date_end"] =  $agenda["date_end"].$sel_time_end.$sel_min_end;
    }
    else {
      $agenda["date_end"] = date("YmdHi",strtotime("+$set_stop_time hours",strtotime($agenda["date_end"])));
    }
  }
  else {
    $agenda["date_end"] = date("YmdHi",strtotime("+$set_stop_time hours",strtotime(date("Ymd"))));
  }
  if (isset($param_date_begin)) {
    $agenda["date_begin"] = $param_date_begin;
  }
  if (isset($param_date_end))
    $agenda["date_end"] = $param_date_end;
  if (isset($sel_repeat_kind)) $agenda["kind"] = $sel_repeat_kind;
  for ($i=0; $i<7; $i++) {
    if (isset(${"cb_repeatday_".$i}))  {
      $agenda["repeat_days"] .= '1';
    }
    else {
      $agenda["repeat_days"] .= '0';
    }

  }
  if (isset($hd_date_begin)) $agenda["date_begin"] = $hd_date_begin;
  if (isset($hd_date_end)) $agenda["date_end"] = $hd_date_end;
  if (isset($rd_decision_event)) $agenda["decision_event"] = $rd_decision_event;
  if (is_array($sel_grp_id)) $agenda["group"] = $sel_grp_id;


  if (debug_level_isset($cdg_param)) {
    if ( $agenda ) {
      while ( list( $key, $val ) = each( $agenda ) ) {
        echo "<br />agenda[$key]=";var_dump($val);
      }
    }
    echo "<br />action = $action";
  }

  return $agenda;
}

///////////////////////////////////////////////////////////////////////////////
//  Agenda Action 
///////////////////////////////////////////////////////////////////////////////
function get_agenda_action() {
  global $actions, $path;
  global $l_header_update,$l_header_right,$l_header_meeting;
  global $l_header_day,$l_header_week,$l_header_year,$l_header_delete;
  global $l_header_month,$l_header_new_event,$param_event,$param_date,$l_header_admin, $l_header_export;
  global $cright_read, $cright_write, $cright_read_admin, $cright_write_admin;

  // Index
  $actions["AGENDA"]["index"] = array (
    'Url'      => "$path/agenda/agenda_index.php?action=index",
    'Right'    => $cright_read,
    'Condition'=> array ('None') 
  );
  
  // Decision
  $actions["AGENDA"]["decision"] = array (
    'Url'      => "$path/agenda/agenda_index.php?action=decision",
    'Right'    => $cright_read,
    'Condition'=> array ('None') 
                                         );

  // Decision
  $actions["AGENDA"]["calendar"] = array (
    'Url'      => "$path/agenda/agenda_index.php?action=calendar",
    'Right'    => $cright_read,
    'Condition'=> array ('None') 
                                         );
  // New   
  $actions["AGENDA"]["new"] = array (
    'Name'     => $l_header_new_event,
    'Url'      => "$path/agenda/agenda_index.php?action=new",
    'Right'    => $cright_write,
    'Condition'=> array ('index','detailconsult','insert','insert_conflict',
		  'update_decision','decision','update','delete',
                  'view_month','view_week','view_day','view_year',
		  'rights_admin','rights_update')
		);
		
//Detail Update

  $actions["AGENDA"]["detailconsult"] = array (
    'Name'     => $l_header_update,
    'Url'      => "$path/agenda/agenda_index.php?action=detailconsult&amp;param_event=".$param_event."&amp;param_date=$param_date",
    'Right'    => $cright_read,
    'Condition'=> array ('None') 
  );

		
//Detail Update

  $actions["AGENDA"]["detailupdate"] = array (
    'Name'     => $l_header_update,
    'Url'      => "$path/agenda/agenda_index.php?action=detailupdate&amp;param_event=".$param_event."&amp;param_date=$param_date",
    'Right'    => $cright_write,
    'Condition'=> array ('detailconsult') 
  );

//Check Delete

  $actions["AGENDA"]["check_delete"] = array (
    'Name'     => $l_header_delete,
    'Url'      => "$path/agenda/agenda_index.php?action=check_delete&amp;param_event=".$param_event."&amp;param_date=$param_date",
    'Right'    => $cright_write_admin,
    'Condition'=> array ('detailconsult') 
                                     		 );


//Delete
  $actions["AGENDA"]["delete"] = array (
    'Url'      => "$path/agenda/agenda_index.php?action=delete&amp;param_event=".$param_event."&amp;param_date=$param_date",
    'Right'    => $cright_write_admin,
    'Condition'=> array ('None') 
                                     		 );

						 
//Insert

  $actions["AGENDA"]["insert"] = array (
    'Url'      => "$path/agenda/agenda_index.php?action=insert",
    'Right'    => $cright_write,
    'Condition'=> array ('None') 
                                         );


//View Year

  $actions["AGENDA"]["view_year"] = array (
    'Name'     => $l_header_year,
    'Url'      => "$path/agenda/agenda_index.php?action=view_year",
    'Right'    => $cright_read,  
    'Condition'=> array ('all') 
                                    	    );

//View Month

  $actions["AGENDA"]["view_month"] = array (
    'Name'     => $l_header_month,
    'Url'      => "$path/agenda/agenda_index.php?action=view_month",
    'Right'    => $cright_read,  
    'Condition'=> array ('all') 
                                    	    );

//View Week

  $actions["AGENDA"]["view_week"] = array (
    'Name'     => $l_header_week,
    'Url'      => "$path/agenda/agenda_index.php?action=view_week",
    'Right'    => $cright_read, 
    'Condition'=> array ('all') 
                                    	  );

//View Day

  $actions["AGENDA"]["view_day"] = array (
    'Name'     => $l_header_day,
    'Url'      => "$path/agenda/agenda_index.php?action=view_day",
    'Right'    => $cright_read, 
    'Condition'=> array ('all') 
                                    	 );

//Update

  $actions["AGENDA"]["update"] = array (
    'Url'      => "$path/agenda/agenda_index.php?action=update",
    'Right'    => $cright_write,
    'Condition'=> array ('None') 
                                         );
					 
//Update

  $actions["AGENDA"]["update_decision"] = array (
    'Url'      => "$path/agenda/agenda_index.php?action=update",
    'Right'    => $cright_write,
    'Condition'=> array ('None') 
                                         );
					 
//Meeting managment.					 
  $actions["AGENDA"]["new_meeting"] = array (
    'Name'     => $l_header_meeting,
    'Url'      => "$path/agenda/agenda_index.php?action=new_meeting",
    'Right'    => $cright_write,
    'Condition'=> array ('all') 
                                         );

//Meeting managment.					 
  $actions["AGENDA"]["perform_meeting"] = array (
    'Url'      => "$path/agenda/agenda_index.php?action=perform_meeting",
    'Right'    => $cright_write,
    'Condition'=> array ('None') 
                                         );

  // Right admin.					 
  $actions["AGENDA"]["rights_admin"] = array (
    'Name'     => $l_header_right,
    'Url'      => "$path/agenda/agenda_index.php?action=rights_admin",
    'Right'    => $cright_write,
    'Condition'=> array ('all') 
                                         );

  // Update Right
  $actions["AGENDA"]["rights_update"] = array (
    'Url'      => "$path/agenda/agenda_index.php?action=rights_update",
    'Right'    => $cright_write,
    'Condition'=> array ('None') 
                                         );

// Admin
  $actions["AGENDA"]["admin"] = array (
    'Name'     => $l_header_admin,
    'Url'      => "$path/agenda/agenda_index.php?action=admin",
    'Right'    => $cright_read_admin,
    'Condition'=> array ('all') 
                                       );
				       
// Kind Insert
  $actions["AGENDA"]["category_insert"] = array (
    'Url'      => "$path/agenda/agenda_index.php?action=category_insert",
    'Right'    => $cright_write_admin,
    'Condition'=> array ('None') 
                                     	     );

// Kind Update
  $actions["AGENDA"]["category_update"] = array (
    'Url'      => "$path/agenda/agenda_index.php?action=category_update",
    'Right'    => $cright_write_admin,
    'Condition'=> array ('None') 
                                     	      );

// Kind Check Link
  $actions["AGENDA"]["category_checklink"] = array (
    'Url'      => "$path/agenda/agenda_index.php?action=category_checklink",
    'Right'    => $cright_write_admin,
    'Condition'=> array ('None') 
                                     		);

// Kind Delete
  $actions["AGENDA"]["category_delete"] = array (
    'Url'      => "$path/agenda/agenda_index.php?action=category_delete",
    'Right'    => $cright_write_admin,
    'Condition'=> array ('None') 
                                     	       );
					       
// Export
  $actions["AGENDA"]["export"] = array (
    'Name'     => $l_header_export,
    'Url'      => "$path/agenda/agenda_index.php?action=export&amp;popup=1",
    'Right'    => $cright_read,
    'Condition'=> array ('all') 
                                       );

}
 


}

?>
