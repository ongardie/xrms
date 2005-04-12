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
       '<br />V�nligen kontakta <a href="mailto:' . 
       $Admin . '">administrat�ren</a> f�r assistans.');

/* action-directory */
setConst('ACTION_RateControlIpBlocking', 'Frekvenskontroll / IP-blockering avst�ngd');
setConst('ACTION_LockUnlockPages', 'L�s / L�s upp sidor');
setConst('ACTION_BLockUnblockHosts', 'Blockera / Avblockera hosts');
setConst('ACTION_ErrorNameMatch',
       'Du har angett ett ogiltigt anv�ndarnamn.' . GEN_ErrorSuffix);
setConst('ACTION_ErrorAdminDisabled',
       'Administrationsfunktionerna f�r denna wiki �r avst�ngda.'.GEN_ErrorSuffix);
setConst('ACTION_ErrorPageLocked',
       'Sidan du f�rs�kte redigera �r l�st.' . GEN_ErrorSuffix);

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
       'Du har blivit nekad tilltr�de till denna plats.' . GEN_ErrorSuffix);
setConst('LIB_ErrorRateExceeded',
       'Du har �verskridit det antal sidor du har till�telse att bes�ka '.
       'under en viss period. V�nligen kom tillbaka senare.' . GEN_ErrorSuffix);
setConst('LIB_ErrorInvalidPage',
       'Ogiltigt sidnamn.' . GEN_ErrorSuffix);

/* parse-directory */
setConst('PARSE_Added', 'Lagt till: ');
setConst('PARSE_Changed', '�ndrad: ');
setConst('PARSE_Deleted', 'Raderad: ');
setConst('PARSE_Never', 'Aldrig'); 
// The next two entries, are joined with a counting variable inbetween
setConst('PARSE_CompleteListStart', 'Se komplett lista (');
setConst('PARSE_CompleteListEnd',   ' inf�randen)');
setConst('PARSE_RecentChanges', 'Senast�ndrade');
setConst('PARSE_Locked', 'L�st');
setConst('PARSE_BlockedRange', 'Blockerad IP-adressomr�de');
setConst('PARSE_EnterIpRange', 
       'Ange IP-adressomr�de i formen <tt>12.*</tt>, <tt>34.56.*</tt>, eller ' .
       '<tt>78.90.123.*</tt>');
setConst('PARSE_EmptyToBeDeleted',
       ' ** Tom, schemalagd f�r radering ** ');
setConst('PARSE_From', 'fr�n'); //ie. SomeThing *from* WantedPages, MacroSomePage
setConst('PARSE_ButtonBlock', 'Blockera');
setConst('PARSE_ButtonUnblock', 'Avblockera');
setConst('PARSE_ButtonSave', 'Spara');
setConst('PARSE_ButtonPreview', 'F�rhandsvisa');
setConst('PARSE_Preferences', 'Inst�llningar');
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
setConst('TMPL_DocLastModified', 'Dokumentet senast �ndrat');
setConst('TMPL_TwinPages', 'Tvillingsidor:');
setConst('TMPL_Search', 'S�k:');
setConst('TMPL_Editing', 'Redigerar');
setConst('TMPL_WarningOtherEditing',
       'Varning: sedan du b�rjade redigera har detta dokument �ndrats '.
       'av n�gon annan.  V�nligen sammanf�r dina f�r�ndringar med den aktuell version '.
       'av detta dokument.' );
setConst('TMPL_CurrentVersion', 'Aktuell version');
setConst('TMPL_ButtonSave', 'Spara');
setConst('TMPL_ButtonPreview', 'F�rhandsvisa');
setConst('TMPL_PreCaptcha', 'N�r du sparar, skriv in f�ljande:<br />');
setConst('TMPL_YourUsername', 'Ditt anv�ndarnamn �r');
setConst('TMPL_VisitPrefs', 
       'G� till <a href="'. $PrefsScript. '">Inst�llningar</a> f�r att ange '.
       'ditt anv�ndarnamn');
setConst('TMPL_SummaryOfChange', 'sammanfattning av �ndring:');
setConst('TMPL_AddToCategory', 'L�gg dokument till kategori:');
setConst('TMPL_YourChanges', 'Dina �ndringar');
setConst('TMPL_PreviewCurrentVersion', 'F�rhandsvisning av aktuell version');
setConst('TMPL_DifferencesIn', 'Skillnad i');
setConst('TMPL_DifferenceBetweenVersions', 'Skillnad mellan versioner:');
setConst('TMPL_Find', 'S�k');
setConst('TMPL_HistoryOf', 'Historia f�r');
setConst('TMPL_Older', '�ldre');
setConst('TMPL_Newer', 'Nyare');
setConst('TMPL_ButtonComputeDifference', 'R�kna ut skillnad');
setConst('TMPL_ChangesLastAuthor', '�ndringar av senaste f�rfattare:');
setConst('TMPL_Prefs', 'Inst�llningar');
setConst('TMPL_Previewing', 'F�rhandsvisning');
setConst('TMPL_Preview', 'F�rhandsvisa');
?>