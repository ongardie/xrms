<?php
/** 
 *   This file provides the textual interface of WikkiTikkiTavi
 * For more information see http://tavi.sourceforge.net/TaviTranslation
 *
 *   This file is divided into the sections according to subdirectories of 
 * where the constants are used. So that the constant PARSE_RecentChanges are
 * to be found somewhere within the parse-subdirectory.
 *
 *   See http://tavi.sourceforge.net/TaviTranslation for notes on how to 
 * translate the file into another language, and how to publish your changes.
 * Please do contribute to make 'Tavi available in multiple languages. 
 *   The gist of translation, is to copy this file and translate it. Store the
 * resulting work as lang_XX.php, where XX denotes the two characters used to
 * describe the language. And after that to add a line like the following to
 * your config.php:
 *   setConst(LANGUAGE_CODE, 'fr');
 * Using 'fr' for the french version, if available.
 */

/* General messages */
setConst('GEN_ErrorSuffix', 
       '<br />Please contact the <a href="mailto:' . 
       $Admin . '">administrator</a> for assistance.');

/* action-directory */
setConst('ACTION_RateControlIpBlocking', 'Rate control / IP blocking disabled');
setConst('ACTION_LockUnlockPages', 'Lock / unlock pages');
setConst('ACTION_BLockUnblockHosts', 'Block / unblock hosts');
setConst('ACTION_ErrorNameMatch',
       'You have entered an invalid user name.' . GEN_ErrorSuffix);
setConst('ACTION_ErrorAdminDisabled',
       'Administration features are disabled for this wiki.'.GEN_ErrorSuffix);
setConst('ACTION_ErrorPageLocked',
       'The page you have tried to edit is locked.' . GEN_ErrorSuffix);

/* lib-directory */
setConst('LIB_ErrorDatabaseQuery', 
       'Error executing database query.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseConnect', 
       'Error connecting to database.' . GEN_ErrorSuffix); 
setConst('LIB_ErrorDatabaseSelect', 
       'Error selecting database.' . GEN_ErrorSuffix); 
setConst('LIB_ErrorCreatingTemp', 
       'Error creating temporary file.' . GEN_ErrorSuffix);
setConst('LIB_ErrorWritingTemp',
       'Error writing to temporary file.' . GEN_ErrorSuffix);
// The next two entries are parsed through the wiki engine, so leave the \'\'
// to get bolded entries... And the escaping of $!DiffCmd...   ;)
setConst('LIB_NoDiffAvailableSafeMode',
       '\'\'No diff available, due to wrong location of diffcmd. It should '.
       'have been inside '. ini_get("safe_mode_exec_dir") .
       'but is located at '. $DiffCmd .'. Please relocate diffcmd or '.
       'make sysAdmins create symbolic links into this directory. Also '.
       'remember to change configuration of $!DiffCmd accordingly.\'\'');       
setConst('LIB_NoDiffAvailable',
       '\'\'No diff available. $!DiffCmd points to ' . $DiffCmd .
       ' which doesn\'t exist or isn\'t readable\'\'');
setConst('LIB_ErrorDeniedAccess',
       'You have been denied access to this site.' . GEN_ErrorSuffix);
setConst('LIB_ErrorRateExceeded',
       'You have exeeded the number of pages you are allowed to visit in a '.
       'given period of time.  Please return later.' . GEN_ErrorSuffix);
setConst('LIB_ErrorInvalidPage',
       'Invalid page name.' . GEN_ErrorSuffix);

/* parse-directory */
setConst('PARSE_Added', 'Added: ');
setConst('PARSE_Changed', 'Changed: ');
setConst('PARSE_Deleted', 'Deleted: ');
setConst('PARSE_Never', 'Never'); 
// The next two entries, are joined with a counting variable inbetween
setConst('PARSE_CompleteListStart', 'See complete list (');
setConst('PARSE_CompleteListEnd',   ' entries)');
setConst('PARSE_RecentChanges', 'RecentChanges');
setConst('PARSE_Locked', 'Locked');
setConst('PARSE_BlockedRange', 'Blocked IP address ranges');
setConst('PARSE_EnterIpRange', 
       'Enter IP address range in form <tt>12.*</tt>, <tt>34.56.*</tt>, or ' .
       '<tt>78.90.123.*</tt>');
setConst('PARSE_EmptyToBeDeleted',
       ' ** Empty, scheduled for deletion ** ');
setConst('PARSE_From', 'from'); //ie. SomeThing *from* WantedPages, MacroSomePage
setConst('PARSE_ButtonBlock', 'Block');
setConst('PARSE_ButtonUnblock', 'Unblock');
setConst('PARSE_ButtonSave', 'Save');
setConst('PARSE_ButtonPreview', 'Preview');
setConst('PARSE_Preferences', 'Preferences');
setConst('PARSE_History', 'history'); // Note the lowercase first character

/* Template directory */
// Note the change to use only TMPL_ as prefix instead of full TEMPLATE_
setConst('TMPL_Administration', 'Administration');
setConst('TMPL_EditDocument', 'Edit this document');
setConst('TMPL_NoEditDocument', 'This document can\'t be edited');
setConst('TMPL_EditArchiveVersion', 
       'Edit this <em>ARCHIVE VERSION</em> of this document');
// Next entry is followed by a date as produced by html_time()
setConst('TMPL_ViewDocHistory', 'View document history');
setConst('TMPL_DocLastModified', 'Document last modified');
setConst('TMPL_TwinPages', 'Twin pages:');
setConst('TMPL_Search', 'Search:');
setConst('TMPL_Editing', 'Editing');
setConst('TMPL_WarningOtherEditing',
       'Warning: since you started editing, this document has been changed '.
       'by someone else.  Please merge your edits into the current version '.
       'of this document.' );
setConst('TMPL_CurrentVersion', 'Current Version');
setConst('TMPL_ButtonSave', 'Save');
setConst('TMPL_ButtonPreview', 'Preview');
setConst('TMPL_PreCaptcha', 'When saving, enter the following:<br />');
setConst('TMPL_YourUsername', 'Your user name is');
setConst('TMPL_VisitPrefs', 
       'Visit <a href="'. $PrefsScript. '">Preferences</a> to set '.
       'your user name');
setConst('TMPL_SummaryOfChange', 'Summary of change:');
setConst('TMPL_AddToCategory', 'Add document to category:');
setConst('TMPL_YourChanges', 'Your changes');
setConst('TMPL_PreviewCurrentVersion', 'Preview of Current Version');
setConst('TMPL_DifferencesIn', 'Differences In');
setConst('TMPL_DifferenceBetweenVersions', 'Difference between versions:');
setConst('TMPL_Find', 'Find');
setConst('TMPL_HistoryOf', 'History of');
setConst('TMPL_Older', 'Older');
setConst('TMPL_Newer', 'Newer');
setConst('TMPL_ButtonComputeDifference', 'Compute Difference');
setConst('TMPL_ChangesLastAuthor', 'Changes by last author:');
setConst('TMPL_Prefs', 'Preferences');
setConst('TMPL_Previewing', 'Previewing');
setConst('TMPL_Preview', 'Preview');

?>
