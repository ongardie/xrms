<?php
//####################################################################
// Active PHP Bookmarks - lbstone.com/apb/
//
// Filename: redirect.php
// Author:   L. Brandon Stone (lbstone.com)
//           Nathanial P. Hendler (retards.org)
//
// 2001-09-05 02:37     Starting on version 1.0
//
// This is where stats get logged, and browsers get redirected.
//
//####################################################################

include_once('apb.php');

$APB_SETTINGS['debug'] = 0;     // We set this since we usually want this to be quiet.

$id = $_GET['id'];

debug("Validate id: $id");
$bm = apb_bookmark($id);

if (! $bm->url()) {
    header ("Location: ".$APB_SETTINGS['apb_url']);
}

elseif ($bm->private() AND ! ($APB_SETTINGS['auth_user_id'] == $bm->user_id())) {
    header ("Location: ".$APB_SETTINGS['apb_url']);
}

else {

    $id = $bm->id();

    if ($APB_SETTINGS['auth_user_id']) {
        $user_id = $APB_SETTINGS['auth_user_id'];
    } else {
        $user_id = 0;
    }

    $con = get_xrms_dbconnection();
    // $con->debug = 1;
    
    //save to database
    $rec = array();
    $rec['bookmark_id'] = $id;
    $rec['user_id'] = $user_id;
    $rec['hit_date'] = time();
    $rec['hit_ip'] = $_SERVER['REMOTE_ADDR'];
    
    $tbl = 'apb_hits';
    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
    $rst = $con->execute($ins);
    if (!$rst) {
        db_error_handler ($con, $ins);
    }

    header ("Location: ".$bm->url());
}
?>
