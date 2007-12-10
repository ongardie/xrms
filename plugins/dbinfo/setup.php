<?php
/*
 *  setup.php
 *
 * Copyright (c) 2007 Glenn Powers <glenn@net127.com>
 *
 * $Id: setup.php,v 1.1 2007/12/10 18:03:23 gpowers Exp $
 */

function xrms_plugin_init_dbinfo () {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['system_monitoring']['dbinfo'] = 'record_counts';
}

function record_counts () {
    global $session_user_id;
    
    if ($session_user_id) {
        $con = get_xrms_dbconnection();
    }

    // $con->debug = 1;
    
    $data =  '
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header>
			        Name
                </td>
                <td class=widget_header>
		            Rows	
                </td>
                <td class=widget_header>
			        Active
                </td>
                <td class=widget_header>
			        Percent Active
                </td>
                <td class=widget_header>
			        Mine
                </td>
                <td class=widget_header>
			        Percent Mine
                </td>
            </tr>';

$tables=array('activities','companies','contacts','campaigns','opportunities');

foreach ($tables as $table) {

$sql = "select count(*) as count from " . $table;
$rows_rst = $con->execute($sql);

if ($rows_rst) {
    $rows = $rows_rst->fields['count'];
    $rows_rst->close();
} else {
    db_error_handler ($con, $sql);
}

$sql = "select count(*) as count from " . $table . "
        where " . make_singular($table) . "_record_status = 'a'";

$active_rst = $con->execute($sql);

$former_name_rows = '';
if ($active_rst) {
    $active = $active_rst->fields['count'];
    $active_rst->close();
} else {
    db_error_handler ($con, $sql);
}

$sql = "select count(*) as count from " . $table . "
        where " . make_singular($table) . "_record_status = 'a'
        and user_id = '" . $session_user_id . "'";
        

$my_rst = $con->execute($sql);

$former_name_rows = '';
if ($my_rst) {
    $mine = $my_rst->fields['count'];
    $my_rst->close();
} else {
    db_error_handler ($con, $sql);
}

if (($rows>=1) && ($active>=1)) {
    $percent_active = round($active/$rows*100);
}

if (($mine>=1) && ($active>=1)) {
    $percent_mine = round($mine/$active*100);
}

$data .= '
            <tr>
                <td>
                ' . $table . '
                </td>
                <td>
                ' . $rows . '
                </td>
                <td>
                ' . $active . '
                </td>
                <td>
                ' . $percent_active . '
                </td>
                <td>
                ' . $mine . '
                </td>
                <td>
                ' . $percent_mine . '
                </td>
            </tr>
';

}

$data .= '</table>';

echo $data;


}

?>
