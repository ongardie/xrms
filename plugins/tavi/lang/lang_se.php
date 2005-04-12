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
 *   setConst(LANGUAGE_CODE, 'se');
 * Using 'se' for the swedish version, if available.
 */

/* General messages */
setConst('GEN_ErrorSuffix', 
       '<br />Vänligen kontakta <a href="mailto:' . 
       $Admin . '">administratören</a> för assistans.');

/* action-directory */
setConst('ACTION_RateControlIpBlocking', 'Frekvenskontroll / IP-blockering avstängd');
setConst('ACTION_LockUnlockPages', 'Lås / Lås upp sidor');
setConst('ACTION_BLockUnblockHosts', 'Blockera / Avblockera hosts');
setConst('ACTION_ErrorNameMatch',
       'Du har angett ett ogiltigt användarnamn.' . GEN_ErrorSuffix);
setConst('ACTION_ErrorAdminDisabled',
       'Administrationsfunktionerna för denna wiki är avstängda.'.GEN_ErrorSuffix);
setConst('ACTION_ErrorPageLocked',
       'Sidan du försökte redigera är låst.' . GEN_ErrorSuffix);

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
       'Du har blivit nekad tillträde till denna plats.' . GEN_ErrorSuffix);
setConst('LIB_ErrorRateExceeded',
       'Du har överskridit det antal sidor du har tillåtelse att besöka '.
       'under en viss period. Vänligen kom tillbaka senare.' . GEN_ErrorSuffix);
setConst('LIB_ErrorInvalidPage',
       'Ogiltigt sidnamn.' . GEN_ErrorSuffix);

/* parse-directory */
setConst('PARSE_Added', 'Lagt till: ');
setConst('PARSE_Changed', 'Ändrad: ');
setConst('PARSE_Deleted', 'Raderad: ');
setConst('PARSE_Never', 'Aldrig'); 
// The next two entries, are joined with a counting variable inbetween
setConst('PARSE_CompleteListStart', 'Se komplett lista (');
setConst('PARSE_CompleteListEnd',   ' införanden)');
setConst('PARSE_RecentChanges', 'SenastÄndrade');
setConst('PARSE_Locked', 'Låst');
setConst('PARSE_BlockedRange', 'Blockerad IP-adressområde');
setConst('PARSE_EnterIpRange', 
       'Ange IP-adressområde i formen <tt>12.*</tt>, <tt>34.56.*</tt>, eller ' .
       '<tt>78.90.123.*</tt>');
setConst('PARSE_EmptyToBeDeleted',
       ' ** Tom, schemalagd för radering ** ');
setConst('PARSE_From', 'från'); //ie. SomeThing *from* WantedPages, MacroSomePage
setConst('PARSE_ButtonBlock', 'Blockera');
setConst('PARSE_ButtonUnblock', 'Avblockera');
setConst('PARSE_ButtonSave', 'Spara');
setConst('PARSE_ButtonPreview', 'Förhandsvisa');
setConst('PARSE_Preferences', 'Inställningar');
setConst('PARSE_History', 'historia'); //Note the lowercase first character

/* Template directory */
// Note the change to use only TMPL_ as prefix instead of full TEMPLATE_
setConst('TMPL_Administration', 'Administration');
setConst('TMPL_EditDocument', 'Redigera detta dokument');
setConst('TMPL_NoEditDocument', 'Detta dokument kan inte redigeras');
setConst('TMPL_EditArchiveVersion', 
       'Redigera denna <em>ARKIVERADE VERSION</em> av detta dokument');
// Next entry is followed by a date as produced by html_time()
setConst('TMPL_ViewDocHistory', 'Se dokumentets historia');
setConst('TMPL_DocLastModified', 'Dokumentet senast ändrat');
setConst('TMPL_TwinPages', 'Tvillingsidor:');
setConst('TMPL_Search', 'Sök:');
setConst('TMPL_Editing', 'Redigerar');
setConst('TMPL_WarningOtherEditing',
       'Varning: sedan du började redigera har detta dokument ändrats '.
       'av någon annan.  Vänligen sammanför dina förändringar med den aktuell version '.
       'av detta dokument.' );
setConst('TMPL_CurrentVersion', 'Aktuell version');
setConst('TMPL_ButtonSave', 'Spara');
setConst('TMPL_ButtonPreview', 'Förhandsvisa');
setConst('TMPL_PreCaptcha', 'När du sparar, skriv in följande:<br />');
setConst('TMPL_YourUsername', 'Ditt användarnamn är');
setConst('TMPL_VisitPrefs', 
       'Gå till <a href="'. $PrefsScript. '">Inställningar</a> för att ange '.
       'ditt användarnamn');
setConst('TMPL_SummaryOfChange', 'sammanfattning av ändring:');
setConst('TMPL_AddToCategory', 'Lägg dokument till kategori:');
setConst('TMPL_YourChanges', 'Dina ändringar');
setConst('TMPL_PreviewCurrentVersion', 'Förhandsvisning av aktuell version');
setConst('TMPL_DifferencesIn', 'Skillnad i');
setConst('TMPL_DifferenceBetweenVersions', 'Skillnad mellan versioner:');
setConst('TMPL_Find', 'Sök');
setConst('TMPL_HistoryOf', 'Historia för');
setConst('TMPL_Older', 'Äldre');
setConst('TMPL_Newer', 'Nyare');
setConst('TMPL_ButtonComputeDifference', 'Räkna ut skillnad');
setConst('TMPL_ChangesLastAuthor', 'Ändringar av senaste författare:');
setConst('TMPL_Prefs', 'Inställningar');
setConst('TMPL_Previewing', 'Förhandsvisning');
setConst('TMPL_Preview', 'Förhandsvisa');
?>