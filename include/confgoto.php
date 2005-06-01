<?php
// Allow a button click to be confirmed before a URL is executed

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

/**
 * This function is intended to replace the confGoTo function, and only show the button if the ACL allows it
 * The first 3 parameters are the same as confGoTo, and get passed to it if the ACL tests succeed.  The last three parameters control
 * how the ACL assigns permission.  If they are not set, it checks for Read access on the global $on_what_table and $on_what_id
 * If no global $on_what_id or specific $_id is passed, only the object permissions are checked, not individual permissions
 *
 * @param string $quest that user is prompted with before url is switched
 * @param string $button with text that appears on button
 * @param string $to_url with URL to redirect to if question is answered affirmatively
 * @param string $_table specifying which table the ACL should check for permissions on
 * @param integer $_id with ID of entity for which permissions are being checked
 * @param string $acl_action with string for action to check for, defaults to Read
 */
function acl_confGoTo($quest, $button, $to_url, $_table, $_id, $acl_action='Read') {
    global $on_what_table;
    global $session_user_id;
    global $on_what_id;
    if ($_table) $table=$_table;
    else $table=$on_what_table;
    if ($_id) $cid=$_id;
    else $cid=$on_what_id;

    if (!$cid) {
        if (!check_object_permission_bool($session_user_id, false, $acl_action, $table))
            return false;
    } else {
        if (!check_permission_bool($session_user_id, false, $cid, $acl_action, $table))
            return false;
    }
    return confGoTo($quest, $button, $to_url);    
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
 * Revision 1.4  2005/06/01 15:59:54  vanmer
 * - added function for ACL control of confgoto to allow buttons to appear only when permissions have been
 * granted
 *
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