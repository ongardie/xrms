<?php
// Allow a button click to be confirmed before a URL is executed

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

// generate html code to ask a $quest(ion) with a $button about
// going to a $to_url. If the answer is TRUE, goto the URL, else
// don't do anything.
function confGoTo( $quest, $button, $to_url )
{
  $tmp    = ' onclick="javascript: confGoTo(\''.$quest.'\',\''.$to_url.'\')"';
  echo '<input type=button class=button value="'.$button.'"'. $tmp.'>';
}

//
// load the coresponding javascript
//
function confGoTo_includes() {

    global $http_site_root;

    echo <<<EOQ
    <!-- confGoTo SCRIPT INCLUDES -->
    <script type="text/javascript" src="$http_site_root/js/confgoto.js"></script>
    <!-- confGoTo End SCRIPT INCLUDES -->
EOQ;
} //end

/*
 * $Log: confgoto.php,v $
 * Revision 1.3  2004/07/29 12:23:03  neildogg
 * - Removed extra lines from EOF
 *  - that prevent cookies being sent in
 *  - non-output-buffering environment
 *
 * Revision 1.2  2004/07/29 09:35:46  cpsource
 * - Seperate .js and .php for confGoTo for PHP V4 problems.
 *
 * Revision 1.1  2004/07/28 19:23:07  cpsource
 * - Handle all confGoTo processing that allows operator to give
 *   the OK before a button click is executed.
 *
 */
?>