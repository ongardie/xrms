<?php
// $Id: loopback.php,v 1.1 2005/04/11 19:50:49 gpowers Exp $
/**
* This is a remote script to call from Javascript
*/

// IE's XMLHttpRequest caching...
header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
header( "Cache-Control: no-cache, must-revalidate" ); 
header( "Pragma: no-cache" );

require_once '../config.php';
require_once JPSPAN . 'Listener.php';

/**
* A basic responder
*/
class LoopBack {
    function execute($payload) {
        echo serialize($payload);
    }
}

$L = & new JPSpan_Listener();
$L->encoding = 'php';
$L->setResponder(new LoopBack());
$L->serve();
?>
