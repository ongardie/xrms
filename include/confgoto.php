<?php
// Allow a button click to be confirmed before a URL is executed
?>
<!-- confGoTo start -->
<script language="JavaScript" type="text/javascript">
function confGoTo(quest,dest) {
  if ( confirm(quest) ) {
    window.location = dest;
  }
}
</script>
<!-- confGoTo end -->
<?php

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

/*
 * $Log: confgoto.php,v $
 * Revision 1.1  2004/07/28 19:23:07  cpsource
 * - Handle all confGoTo processing that allows operator to give
 *   the OK before a button click is executed.
 *
 */
?>

