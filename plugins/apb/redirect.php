<?
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
    debug("Not a valid id: $id");
    header ("Location: ".$APB_SETTINGS['apb_url']);
}

elseif ($bm->private() AND ! ($APB_SETTINGS['auth_user_id'] == $bm->user_id())) {
    debug("Not a valid id: $id");
    header ("Location: ".$APB_SETTINGS['apb_url']);
}

else {

    $id = $bm->id();

    if ($APB_SETTINGS['auth_user_id']) {
        $user_id = $APB_SETTINGS['auth_user_id'];
    } else {
        $user_id = 0;
    }

    $query = "
        INSERT INTO apb_hits
        (bookmark_id, user_id, hit_date, hit_ip)
        VALUES
        ('$id', '$user_id', NOW(), '$REMOTE_ADDR')
    ";

    $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);

    debug("Location: ".$bm->url());
    header ("Location: ".$bm->url());
}
?>
