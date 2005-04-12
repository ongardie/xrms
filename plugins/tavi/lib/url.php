<?php
// Users may redefine these functions if they wish to change the
// URL scheme, e.g., to enable links like:
//
//     http://somewiki.org/PageName
//
// The new versions of the relevant functions should be defined in
// config.php.  Those functions that are redefined will not be
// redefined here.
if(!isset($ViewBase))
  { $ViewBase    = $ScriptBase . '?page='; }
if(!isset($EditBase))
  { $EditBase    = $ScriptBase . '?action=edit&amp;page='; }
if(!isset($HistoryBase))
  { $HistoryBase = $ScriptBase . '?action=history&amp;page='; }
if(!isset($FindScript))
  { $FindScript  = $ScriptBase . '?action=find'; }
if(!isset($FindBase))
  { $FindBase    = $FindScript . '&amp;find='; }
if(!isset($SaveBase))
  { $SaveBase    = $ScriptBase . '?action=save&amp;page='; }
if(!isset($DiffScript))
  { $DiffScript  = $ScriptBase . '?action=diff'; }
if(!isset($PrefsScript))
  { $PrefsScript = $ScriptBase . '?action=prefs'; }
if(!isset($StyleScript))
  { $StyleScript = $ScriptBase . '?action=style'; }

if(!function_exists('viewURL'))
{
function viewURL($page, $version = '', $full = '')
{
  global $ViewBase;

  return $ViewBase . urlencode($page) .
         ($version == '' ? '' : "&amp;version=$version") .
         ($full == '' ? '' : '&amp;full=1');
}
}

if(!function_exists('editURL'))
{
function editURL($page, $version = '')
{
  global $EditBase;

  return $EditBase . urlencode($page) .
         ($version == '' ? '' : "&amp;version=$version");
}
}

if(!function_exists('historyURL'))
{
function historyURL($page, $full = '')
{
  global $HistoryBase;

  return $HistoryBase . urlencode($page) .
         ($full == '' ? '' : '&amp;full=1');
}
}

if(!function_exists('findURL'))
{
function findURL($page)
{
  global $FindBase;

  return $FindBase . urlencode($page);
}
}

if(!function_exists('saveURL'))
{
function saveURL($page)
{
  global $SaveBase;

  return $SaveBase . urlencode($page);
}
}

?>
