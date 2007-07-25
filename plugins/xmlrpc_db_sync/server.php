<?php
error_reporting(1);
include_once "xmlrpc.inc";
include_once "xrms_api.php";

$xmlrpc_request = XMLRPC_parse($HTTP_RAW_POST_DATA);

$methodName = XMLRPC_getMethodName($xmlrpc_request);

$params = XMLRPC_getParams($xmlrpc_request);

if(!isset($xmlrpc_methods[$methodName])){

    $xmlrpc_methods['method_not_found']($methodName);

}else{

    #call the method

    $xmlrpc_methods[$methodName]($params);

}

?>