<?php
/**
 * Export Pager contents to Google Spreadsheet
 * copyright 2007 Glenn Powers <glenn@net127.com>
 *
 * $Id: export-spreadsheet.php,v 1.1 2007/12/10 17:46:00 gpowers Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'adodb/toexport.inc.php');

if (!$con) {$con = get_xrms_dbconnection();}
// $con->debug = 1;

if (!$session_user_id) {$session_user_id = session_check();}

getGlobalVar($pager_id, 'pager_id');
getGlobalVar($custom_header, 'custom_header');
getGlobalVar($custom_footer, 'custom_footer');
getGlobalVar($hide_field_headers, 'hide_field_headers');

getGlobalVar($session_data, $pager_id . "_data");
getGlobalVar($column_info, $pager_id . "_columns");

$filename =  $pager_id .'-'. date('Y-m-d_H-i') . '.csv';

// Authenitcate to Google
require_once('Zend/Gdata/AuthSub.php');

$my_docs = 'http://docs.google.com/feeds/documents/private/full';

if (!isset($_SESSION['google_token'])) {
    if (isset($_GET['token'])) {
        // You can convert the single-use token to a session token.
        $session_token =  Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
        // Store the session token in our session.
        $_SESSION['google_token'] = $session_token;
    } else {
        // Display link to generate single-use token
        $googleUri = Zend_Gdata_AuthSub::getAuthSubTokenUri(
            'https://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'],
        $my_docs, 0, 1);
        header("Location: $googleUri");
        exit();
    }
}

// get data
if(is_array($session_data) && is_array($column_info)) {
    // first output the column names
    $csvdata = '';

    //include custom headers if provided
    if ($custom_header) $csvdata.=$custom_header."\n";

    //by default, include field headers, unless hide field headers is explicitly set
    if (!$hide_field_headers) {
        foreach($column_info as $column) {
            $csvdata .= $column['name'] . ',';
        }
        $csvdata = substr($csvdata, 0, -1);
        $csvdata .= "\n";
    }
    // now output the data
    foreach($session_data as $row) {

        foreach($column_info as $column) {
            // do some formatting of the data before moving to csvdata
            if('url' == $column['type']) {
                // extract <a...>(good stuff)</a>
                if(preg_match("/<a[^>]*>(.*)<\/a>/", $row[$column['index']], $matches))
                {
                    $row[$column['index']] = $matches[1];
                }
            }
            if('html' == $column['type']) {
                // extract all html
                $row[$column['index']] = preg_replace("/(<\/?)(\w+)([^>]*>)/e", '', $row[$column['index']]);
            }

            if(false !== strpos($row[$column['index']], ',')) {
                $row[$column['index']] = '"' . $row[$column['index']] . '"';
            }
            	
            $csvdata .= $row[$column['index']] . ',';
        }
        $csvdata = substr($csvdata, 0, -1);
        $csvdata .= "\n";
    }

    if ($custom_footer) { $csvdata.=$custom_footer."\n"; }

    $filesize = strlen($csvdata);

    // send to google
    $page = "/feeds/documents/private/full";
    $fp = fsockopen("docs.google.com", 80, $errno, $errstr, 30);

    $send = 	"POST $page HTTP/1.1\r\n";
    $send .=	"Host: docs.google.com\r\n";
    $send .=	"Slug: $filename\r\n";
    $send .=	"Authorization: AuthSub token=\"" . $_SESSION['google_token'] . "\"\r\n";
    $send .=	"Content-length: $filesize\r\n";
    $send .=	"Content-Type: text/csv\r\n";
    $send .=	"Connection: Close\r\n\r\n";
    $send .=	$csvdata;

    fwrite($fp, $send);
    while (!feof($fp)) {
        $line = fgets($fp, 128);
        if (!$spreadsheet_id) {
            preg_match('/%3A([^\/]+)/', $line, $results);
            if ($results[1]) {
                $spreadsheet_id=$results[1];
            }
        }
    }
    fclose($fp);

header("Location: http://spreadsheets.google.com/ccc?key=" . $spreadsheet_id . "&hl=en");
    
    
// error handling
} else {
    echo "<p>" . _("There was a problem with your export") . ":\n";

    if(!is_array($session_data))
    echo "<br>" . _("There is no data to export!") . "\n";

    if(!is_array($column_info))
    echo "<br>" . _("There is no column_info!") . "\n";
}


/**
 * $Log: export-spreadsheet.php,v $
 * Revision 1.1  2007/12/10 17:46:00  gpowers
 * - Export Pager to Google Spreadsheet
 * - Requires Zend Framework (Gdata)
 *
 *
 */
?>