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
 *   setConst(LANGUAGE_CODE, 'de');
 * Using 'de' for the german version.
 */

/* Allgemeine Meldungen */
setConst('GEN_ErrorSuffix', 
       '<br />Kontaktieren Sie den Administrator <a href="mailto:' . 
       $Admin . '">administrator</a> f�r Hilfe.');

/* action-directory */
setConst('ACTION_RateControlIpBlocking', 'Rate control / IP-Sperre deaktiviert');
setConst('ACTION_LockUnlockPages', 'Seiten sperren / entsperren');
setConst('ACTION_BLockUnblockHosts', 'Rechner sperren / entsperren');
setConst('ACTION_ErrorNameMatch',
       'Sie haben einen ung�ltigen Benutzernamen eingegeben.' . GEN_ErrorSuffix);
setConst('ACTION_ErrorAdminDisabled',
       'Administrator-Funktionen sind f�r dieses Wiki deaktiviert.'.GEN_ErrorSuffix);
setConst('ACTION_ErrorPageLocked',
       'Die Seite, die Sie �ndern wollten ist gesperrt.' . GEN_ErrorSuffix);

/* lib-directory */
setConst('LIB_ErrorDatabaseQuery', 
       'Fehler: Datenbank-Abfrage fehlgeschlagen.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseConnect', 
       'Fehler: Datenbank-Verbindung fehlgeschlagen.' . GEN_ErrorSuffix); 
setConst('LIB_ErrorDatabaseSelect', 
       'Fehler: Datenbank-Auswahl fehlgeschlagen.' . GEN_ErrorSuffix); 
setConst('LIB_ErrorCreatingTemp', 
       'Fehler: Tempor�re Datei konnte nicht erstellt werden.' . GEN_ErrorSuffix);
setConst('LIB_ErrorWritingTemp',
       'Fehler: Tempor�re Datei konnte nicht beschrieben werden.' . GEN_ErrorSuffix);
// The next two entries are parsed through the wiki engine, so leave the \'\'
// to get bolded entries... And the escaping of $!DiffCmd...   ;)
setConst('LIB_NoDiffAvailableSafeMode',
       '\'\'Diff-Befehl ist nicht verf�gbar aufgrund falscher Dateiangabe. Sollte '.
       'sich in '. ini_get("safe_mode_exec_dir") .
       'befinden, aber ist tats�chlich in '. $DiffCmd .'. Bitte verschieben Sie den diff-Befehl oder '.
       'der Administrator soll in diesem Verzeichnis einen symbolischen Link erstellen. '.
       'Vergessen Sie auch nicht die Einstellungen von $!DiffCmd entsprechend anzupassen.\'\'');       
setConst('LIB_NoDiffAvailable',
       '\'\'Diff-Befehl ist nicht verf�gbar. $!DiffCmd zeigt auf ' . $DiffCmd .
       '. Dieser existiert jedoch nicht oder es fehlt die Leseberechtigung.\'\'');
setConst('LIB_ErrorDeniedAccess',
       'Der Zugriff auf dieses System wurde Ihnen verweigert.' . GEN_ErrorSuffix);
setConst('LIB_ErrorRateExceeded',
       'Sie haben die Anzahl der Seiten, die Sie in einem bestimmten Zeitraum besuchen '.
       'd�rfen �berschritten. Bitte versuchen Sie es sp�ter nochmals.' . GEN_ErrorSuffix);
setConst('LIB_ErrorInvalidPage',
       'Ung�ltiger Seitenname.' . GEN_ErrorSuffix);

/* parse-directory */
setConst('PARSE_Added', 'Hinzugef�gt: ');
setConst('PARSE_Changed', 'Ge�ndert: ');
setConst('PARSE_Deleted', 'Gel�scht: ');
setConst('PARSE_Never', 'Nie'); 
// The next two entries, are joined with a counting variable inbetween
setConst('PARSE_CompleteListStart', 'Gesamte Liste anzeigen (');
setConst('PARSE_CompleteListEnd',   ' Eintr�ge)');
setConst('PARSE_RecentChanges', 'Letzte�nderungen');
setConst('PARSE_Locked', 'Gesperrt');
setConst('PARSE_BlockedRange', 'Gesperrter IP-Adressbereich');
setConst('PARSE_EnterIpRange', 
       'Geben Sie einen IP-Adressbereich in folgender Form ein: <tt>12.*</tt>, <tt>34.56.*</tt> oder ' .
       '<tt>78.90.123.*</tt>');
setConst('PARSE_EmptyToBeDeleted',
       ' ** Leer, zur L�schung vorgemerkt ** ');
setConst('PARSE_From', 'von'); //ie. SomeThing *from* WantedPages, MacroSomePage
setConst('PARSE_ButtonBlock', 'Sperren');
setConst('PARSE_ButtonUnblock', 'Entsperren');
setConst('PARSE_ButtonSave', 'Speichern');
setConst('PARSE_ButtonPreview', 'Vorschau');
setConst('PARSE_Preferences', 'Preferences');
setConst('PARSE_History', 'verlauf'); // note lowercase first character

/* Template directory */
// Note the change to use only TMPL_ as prefix instead of full TEMPLATE_
setConst('TMPL_Administration', 'Administration');
setConst('TMPL_EditDocument', 'Dieses Dokument �ndern');
setConst('TMPL_NoEditDocument', 'Dieses Dokument kann nicht ge�ndert werden');
setConst('TMPL_EditArchiveVersion', 
       '�ndern Sie diese <em>ARCHIV-VERSION</em> des Dokuments');
// Next entry is followed by a date as produced by html_time()
setConst('TMPL_ViewDocHistory', 'Dokumentverlauf anzeigen');
setConst('TMPL_DocLastModified', 'Dokument zuletzt ge�ndert am');
setConst('TMPL_TwinPages', 'Zwillingsseiten:');
setConst('TMPL_Search', 'Suche:');
setConst('TMPL_Editing', '�ndern');
setConst('TMPL_WarningOtherEditing',
       'Achtung: W�hrend Sie das Dokument ge�ndert haben, wurde es zwischenzeitlich '.
       'von jemand anderem bearbeitet. Bitte f�gen Sie Ihre �nderungen in die aktuelle '.
       'Version des Dokuments ein.' );
setConst('TMPL_CurrentVersion', 'Aktuelle Version');
setConst('TMPL_ButtonSave', 'Speichern');
setConst('TMPL_ButtonPreview', 'Vorschau');
setConst('TMPL_PreCaptcha', 'Beim Speichern, betreten Sie das folgende: <br />');
setConst('TMPL_YourUsername', 'Ihr Benutzername ist ');
setConst('TMPL_VisitPrefs', 
       'Konfigurieren Sie Ihren Benutzernamen bei den <a href="'.$PrefsScript.'">Einstellungen</a>');
setConst('TMPL_SummaryOfChange', 'Kurzbeschreibung Ihrer �nderungen:');
setConst('TMPL_AddToCategory', 'Dokument folgender Kategorie zuordnen:');
setConst('TMPL_YourChanges', 'Ihre �nderungen');
setConst('TMPL_PreviewCurrentVersion', 'Vorschau der derzeitigen Version');
setConst('TMPL_DifferencesIn', 'Unterschiede in ');
setConst('TMPL_DifferenceBetweenVersions', 'Unterschiede zwischen den Versionen:');
setConst('TMPL_Find', 'Suche');
setConst('TMPL_HistoryOf', 'Verlauf von ');
setConst('TMPL_Older', '�lter');
setConst('TMPL_Newer', 'Neuer');
setConst('TMPL_ButtonComputeDifference', 'Unterschiede ermitteln');
setConst('TMPL_ChangesLastAuthor', '�nderungen des letzten Autors:');
setConst('TMPL_Prefs', 'Einstellungen');
setConst('TMPL_Previewing', 'Vorschau');
setConst('TMPL_Preview', 'Vorschau');

?>