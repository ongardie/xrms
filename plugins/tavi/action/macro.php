<?php
// $Id: macro.php,v 1.1 2005/04/12 20:45:10 gpowers Exp $

require('parse/macros.php');
require('parse/html.php');

// Execute a macro directly from the URL.
function action_macro()
{
  global $ViewMacroEngine, $macro, $parms;

  if(!empty($ViewMacroEngine[$macro]))
  {
    print $ViewMacroEngine[$macro]($parms);
  }
}
?>
