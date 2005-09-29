<?php
  /*
  *  setup.php
  *
  * Copyright (c) 2004 The XRMS Project Team
  *
  * $Id: setup.php,v 1.1 2005/09/29 19:35:27 niclowe Exp $

  @todo - have option to show notices on frontpage on logon - maybe add hook to private/home.php

  */


  function xrms_plugin_init_teamnotice () {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['private_sidebar_bottom']['teamnotice'] = 'teamnotice_sidebar';
    $xrms_plugin_hooks['private_front_splash']['teamnotice'] = 'teamnotice_front_splash';
    $xrms_plugin_hooks['plugin_admin']['teamnotice'] = 'teamnotice_setup';
  }

  function teamnotice_sidebar () {
    global $con;
		$sidebar_string = '<div id="teamnotice_sidebar">
        <table class=widget cellspacing=1 width="100%">
        <tr>
        <th class=widget_header colspan=4>'
        ._("Team Notices ")
        .'</th>
        </tr>
        <tr>
        <td>';
				
    $sql = "select * from  teamnotices where status='a'";
    //$con->debug=1;
    $rst = $con->execute($sql);

    if ($rst) {
		
      while (!$rst->EOF) {
        $heading = $rst->fields['notice_heading'];
        $text = $rst->fields['notice_text'];
        $sidebar_string.='<b>' .$heading.'</b><BR>'. $text . '<BR><BR>';
        $rst->movenext();
      }
    $rst->close();
    }
    $sidebar_String.='</td></tr></table>';

    return $sidebar_string;
  }

  function teamnotice_front_splash () {
    global $con;
    $splash_string = '
    <table class=widget cellspacing=1 width="100%">
    <tr>
    <th class=widget_header colspan=4>'._("Team Notices ").'</th></tr>
		<tr>
    <td><marquee scrollamount="2" direction="up" loop="true">';

    $sql = "select * from  teamnotices where status='a'";
    //$con->debug=1;
    $rst = $con->execute($sql);

    if ($rst) {
      while (!$rst->EOF) {
        $heading = $rst->fields['notice_heading'];
        $text = $rst->fields['notice_text'];
				$splash_string.='
        <b>' .$heading.'</b>
				<BR>'. $text.'<BR><BR>';
			$rst->movenext();
      }
      $rst->close();
    }
		$splash_string.='</marquee></td>
        </tr></table>';
    return $splash_string;
  }

  function teamnotice_setup() {
    global $http_site_root, $xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname;
    $con = &adonewconnection($xrms_db_dbtype);
    $con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
    // $con->debug = 1;

    echo "<tr><td class=widget_content>\n<a href='$http_site_root/plugins/teamnotice/teamnotice_list.php'>Manage Team Notices</a>\n</td>\n</tr>\n";
  }

?>
