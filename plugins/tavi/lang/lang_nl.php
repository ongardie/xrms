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
 *   setConst(LANGUAGE_CODE, 'nl');
 * Using 'nl' for the dutch version, if available.
 */

/* Algemene meldingen */
setConst('GEN_ErrorSuffix',
       '<br />Neem a.u.b. contact op met <a href="mailto:' .
       $Admin . '">administrator</a> voor assistentie.');

/* actie-directory */
setConst('ACTION_RateControlIpBlocking', 'Snelheids Controle / IP\'s uitsluiten uitgeschakeld');
setConst('ACTION_LockUnlockPages', 'Vergrendel / Ontgrendel pagina\'s');
setConst('ACTION_BLockUnblockHosts', 'Vergrendel / Ontgrendel aangesloten computers');
setConst('ACTION_ErrorNameMatch',
       'U heeft een ongeldige naam ingegeven.' . GEN_ErrorSuffix);
setConst('ACTION_ErrorAdminDisabled',
       'Beheer functies zijn uitgeschakeld in deze wiki.'.GEN_ErrorSuffix);
setConst('ACTION_ErrorPageLocked',
       'De pagina die u probeert te bewerken, is vergrendeld.' . GEN_ErrorSuffix);

/* bibliotheek-directory */
setConst('LIB_ErrorDatabaseQuery',
       'Fout bij het raadplegen van de database.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseConnect',
       'Fout bij het verbinden met de database.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseSelect',
       'Fout bij het selecteren van de database.' . GEN_ErrorSuffix);
setConst('LIB_ErrorCreatingTemp',
       'Fout bij het creeren van een tijdelijk bestand.' . GEN_ErrorSuffix);
setConst('LIB_ErrorWritingTemp',
       'Fout bij het schrijven naar een tijdelijk bestand.' . GEN_ErrorSuffix);
// The next two entries are parsed through the wiki engine, so leave the \'\'
// to get bolded entries... And the escaping of $!DiffCmd...   ;)
setConst('LIB_NoDiffAvailableSafeMode',
       '\'\'Geen verschil-vergelijking aanwezig, wegens verkeerde locatie van het bestand diffcmd. Het zo zich '.
       'moeten bevinden in '. ini_get("safe_mode_exec_dir") .
       'maar bevind zich in '. $DiffCmd .'. A.u.b. verplaats het bestand diffcmd of '.
       'laat een systeembeheerder symbolische links aanmaken in deze directory. Vergeet '.
       'ook niet de configuratie van $!DiffCmd overeenkomstig te wijzigen.\'\'');
setConst('LIB_NoDiffAvailable',
       '\'\'Geen verschil-vergelijking aangwezig. $!DiffCmd wijst naar ' . $DiffCmd .
       ' welke niet bestaat of onleesbaar is\'\'');
setConst('LIB_ErrorDeniedAccess',
       'Uw toegang tot deze pagina is geweigerd.' . GEN_ErrorSuffix);
setConst('LIB_ErrorRateExceeded',
       'U heeft het maximaal aantal te bezoeken pagina\'s in een bepaalde tijd, overschreden '.
       '. Komt u later nog eens terug.' . GEN_ErrorSuffix);
setConst('LIB_ErrorInvalidPage',
       'Ongeldige pagina naam.' . GEN_ErrorSuffix);

/* parse-directory */
setConst('PARSE_Added', 'Toegevoegd: ');
setConst('PARSE_Changed', 'Gewijzigd: ');
setConst('PARSE_Deleted', 'Verwijderd: ');
setConst('PARSE_Never', 'Nooit');
// The next two entries, are joined with a counting variable inbetween
setConst('PARSE_CompleteListStart', 'Bekijk complete lijst (');
setConst('PARSE_CompleteListEnd',   ' Ingangen)');
setConst('PARSE_RecentChanges', 'RecenteWijzigingen');
setConst('PARSE_Locked', 'Vergrendeld');
setConst('PARSE_BlockedRange', 'Geblokkeerde IP adressen');
setConst('PARSE_EnterIpRange',
       'Ingave IP adres in formaat <tt>12.*</tt>, <tt>34.56.*</tt>, of ' .
       '<tt>78.90.123.*</tt>');
setConst('PARSE_EmptyToBeDeleted',
       ' ** Leeg, gepland voor verwijderen ** ');
setConst('PARSE_From', 'van'); //ie. SomeThing *from* WantedPages, MacroSomePage
setConst('PARSE_ButtonBlock', 'Blokkeer');
setConst('PARSE_ButtonUnblock', 'DeBlokkeer');
setConst('PARSE_ButtonSave', 'Opslaan');
setConst('PARSE_ButtonPreview', 'Voorbeeld');

/* Template directory */
// Note the change to use only TMPL_ as prefix instead of full TEMPLATE_
setConst('TMPL_Administration', 'Administratie');
setConst('TMPL_EditDocument', 'Bewerk dit document');
setConst('TMPL_NoEditDocument', 'Dit document kan niet bewerk worden');
setConst('TMPL_EditArchiveVersion',
       'Bewerk dit <em>ARCHIVE VERSION</em> van dit document');
// Next entry is followed by a date as produced by html_time()
setConst('TMPL_ViewDocHistory', 'Bekijk document geschiedenis');
setConst('TMPL_DocLastModified', 'Document laatst gewijzigd');
setConst('TMPL_TwinPages', 'Dubbele pages:');
setConst('TMPL_Search', 'Zoeken:');
setConst('TMPL_Editing', 'Bewerken');
setConst('TMPL_WarningOtherEditing',
       'Waarschuwing: Dit document is door iemand anders gewijzigd sinds u het '.
       'begon te bewerken. Voer u wijzigen a.u.b. in, in de huidige versie '.
       'van dit document.' );
setConst('TMPL_CurrentVersion', 'Huidige Versie');
setConst('TMPL_ButtonSave', 'Opslaan');
setConst('TMPL_ButtonPreview', 'Voorbeeld');
setConst('TMPL_YourUsername', 'Uw gebruikersnaam is');
setConst('TMPL_VisitPrefs',
       'Bezoek <a href="'. $PrefsScript. '">Preferences</a> om een '.
       'gebruikersnaam in te stellen');
setConst('TMPL_SummaryOfChange', 'Wijzigingsoverzicht:');
setConst('TMPL_AddToCategory', 'Voeg document toe aan categorie:');
setConst('TMPL_YourChanges', 'Uw wijzigingen');
setConst('TMPL_PreviewCurrentVersion', 'Voorbeeld van de huidige versie');
setConst('TMPL_DifferencesIn', 'Verschillen in');
setConst('TMPL_DifferenceBetweenVersions', 'Verschillen tussen beide versies:');
setConst('TMPL_Find', 'Zoeken');
setConst('TMPL_HistoryOf', 'Geschiedenis van');
setConst('TMPL_Older', 'Ouder');
setConst('TMPL_Newer', 'Nieuwer');
setConst('TMPL_ButtonComputeDifference', 'Bereken Verschil');
setConst('TMPL_ChangesLastAuthor', 'Wijzigingen door laatste auteur:');
setConst('TMPL_Prefs', 'Voorkeuren');
setConst('TMPL_Previewing', 'Voorbeeld Bekijken');
setConst('TMPL_Preview', 'Voorbeeld');

?>