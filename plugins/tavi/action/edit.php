<?php
// $Id: edit.php,v 1.1 2005/04/12 20:45:09 gpowers Exp $

require('parse/html.php');
require('lib/captcha.php');
require(TemplateDir . '/edit.php');

// Edit a page (possibly an archive version).
function action_edit()
{
  global $page, $pagestore, $ParseEngine, $version, $UseCaptcha;

  $pg = $pagestore->page($page);
  $pg->read();

  if(!$pg->mutable)
    { die(ACTION_ErrorPageLocked); }

  $archive = 0;
  if($version != '')
  {
    $pg->version = $version;
    $pg->read();
    $archive = 1;
  }
    
  if ($UseCaptcha)
  {
    // Build the Captcha, of given length
    $aphrase_arr = generate_captcha(5);
    if (! is_array($aphrase_arr) )
    {
      echo "<p> Error: could not retrieve array. </p>";
      exit();  //*** This is not especially graceful... ;-/
    }
    else
    {
      $aphrase_ascii = $aphrase_arr[0];
      $aphrase_orig = $aphrase_arr[1];
    }
  
    // Store ASCII Art phrase in session variable 
    session_name("taviCaptcha");
    session_start();
    $_SESSION['captcha_phrase'] = $aphrase_orig;
    $_SESSION['captcha_image'] = $aphrase_ascii;
  } else {
    $aphrase_ascii = '';
  }
  
  template_edit(array('page'      => $page,
                      'text'      => $pg->text,
                      'timestamp' => $pg->time,
                      'nextver'   => $pg->version + 1,
                      'archive'   => $archive,
                      'captcha'   => $aphrase_ascii));
                      
}
?>
