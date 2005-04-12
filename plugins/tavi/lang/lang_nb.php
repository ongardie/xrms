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
 *   setConst('LANGUAGE_CODE', 'fr');
 * Using 'fr' for the french version, if available.
 * 
 * This file, lang_nb.php, in "Norwegian, Bokmål" was translated by Even Holen
 */

/* General messages */
setConst('GEN_ErrorSuffix', 
       '<br />Vennligst kontakt <a href="mailto:' . 
       $Admin . '">administratoren</a> for assistanse.');

/* action-directory */
setConst('ACTION_RateControlIpBlocking', 'Frekvenskontroll / IP-blokkering er skrudd av');
setConst('ACTION_LockUnlockPages', 'Lås/ Lås opp sider');
setConst('ACTION_BLockUnblockHosts', 'Blokker / Avblokker maskiner');
setConst('ACTION_ErrorNameMatch',
       'Du har skrevet inn et ulovlig brukernavn.' . GEN_ErrorSuffix);
setConst('ACTION_ErrorAdminDisabled',
       'De administrative rutinene er avskrudd for denne wiki\'en. '.GEN_ErrorSuffix);
setConst('ACTION_ErrorPageLocked',
       'Siden du prøver å endre er låst.' . GEN_ErrorSuffix);

/* lib-directory */
setConst('LIB_ErrorDatabaseQuery', 
       'Feil ved utføring av databaseforespørsel.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseConnect', 
       'Feil ved tilkobling til databasen.' . GEN_ErrorSuffix); 
setConst('LIB_ErrorDatabaseSelect', 
       'Feil ved valg av database.' . GEN_ErrorSuffix); 
setConst('LIB_ErrorCreatingTemp', 
       'Feil ved oppretting av temporær-fil.' . GEN_ErrorSuffix);
setConst('LIB_ErrorWritingTemp',
       'Feil ved skriving til temporær-fil.' . GEN_ErrorSuffix);
// The next two entries are parsed through the wiki engine, so leave the \'\'
// to get bolded entries... And the escaping of $!DiffCmd...   ;)
setConst('LIB_NoDiffAvailableSafeMode',
       '\'\'Ingen diff-kommando tilgjengelig, grunnet feil plassering '.
       'av \'diffcmd\'. Den skulle ha vært i katalogen '.
        ini_get("safe_mode_exec_dir") .
       ', men er lokalisert til '. $DiffCmd .'. Vennligst flytt diffcmd eller '.
       'be systemadministrator lage symbolske lenker inn i katalogen. Husk '.
       'også å forandre konfigurering av $!DiffCmd tilsvarende.\'\'');
setConst('LIB_NoDiffAvailable',
       '\'\'Ingen diff-kommando tilgjengelig. $!DiffCmd peker til '.$DiffCmd.
       ' som enten ikke eksisterer eller ikke er lesbar.\'\'');
setConst('LIB_ErrorDeniedAccess',
       'Du er nektet tilgang til dette webstedet.' . GEN_ErrorSuffix);
setConst('LIB_ErrorRateExceeded',
       'Du har gått over grensen for antall sider du kan besøke i løpet av '.
       'en gitt tidsperiode. Vennligst kom tilbake senere.'. GEN_ErrorSuffix);
setConst('LIB_ErrorInvalidPage',
       'Ugyldig sidenavn.' . GEN_ErrorSuffix);

/* parse-directory */
setConst('PARSE_Added', 'Lagt til: ');
setConst('PARSE_Changed', 'Endret: ');
setConst('PARSE_Deleted', 'Slettet: ');
setConst('PARSE_Never', 'Aldir'); 
// The next two entries, are joined with a counting variable inbetween
setConst('PARSE_CompleteListStart', 'Se komplett liste(');
setConst('PARSE_CompleteListEnd',   ' innslag)');
setConst('PARSE_RecentChanges', 'SisteEndringer');
setConst('PARSE_Locked', 'Låst');
setConst('PARSE_BlockedRange', 'Blokkert IP-områder');
setConst('PARSE_EnterIpRange', 
       'Skriv inn IP-område i formen <tt>12.*</tt>, <tt>34.56.*</tt>, '.
       'eller <tt>78.90.123.*</tt>');
setConst('PARSE_EmptyToBeDeleted',
       ' ** Tom side, lagt i køen klar for sletting ** ');
setConst('PARSE_From', 'fra'); //ie. SomeThing *from* WantedPages, MacroSomePage
setConst('PARSE_ButtonBlock', 'Blokker');
setConst('PARSE_ButtonUnblock', 'Avblokker');
setConst('PARSE_ButtonSave', 'Lagre');
setConst('PARSE_ButtonPreview', 'Forhåndsvis');
setConst('PARSE_Preferences', 'Preferanser');
setConst('PARSE_History', 'historie');

/* Template directory */
// Note the change to use only TMPL_ as prefix instead of full TEMPLATE_
setConst('TMPL_Administration', 'Administrering');
setConst('TMPL_EditDocument', 'Endre dokument');
setConst('TMPL_NoEditDocument', 'Dette dokumentet kan ikke endres');
setConst('TMPL_EditArchiveVersion', 
       'Endre <em>ARKIV VERSJON</em> av dokumentet');
// Next entry is followed by a date as produced by html_time()
setConst('TMPL_ViewDocHistory', 'Vis dokumenthistorien');
setConst('TMPL_DocLastModified', 'Dokumentet ble sist endret');
setConst('TMPL_TwinPages', 'Tvilling-sider:');
setConst('TMPL_Search', 'Søk:');
setConst('TMPL_Editing', 'Endrer');
setConst('TMPL_WarningOtherEditing',
       'Advarsel: Siden du startet din endring, er dokumentet endret av '.
       'noen andre. Vennligst gjør om igjen dine endringer til nåværende '.
       'versjon av dokumentet. ');
setConst('TMPL_CurrentVersion', 'Nåværende versjon');
setConst('TMPL_ButtonSave', 'Lagre');
setConst('TMPL_ButtonPreview', 'Forhåndsvis');
setConst('TMPL_PreCaptcha', 'Ved lagring, skriv inn følgende:<br />');
setConst('TMPL_YourUsername', 'Ditt brukernavn er ');
setConst('TMPL_VisitPrefs', 
       'Besøk <a href="'. $PrefsScript. '">Preferanser</a> for å '.
       'sette ditt brukernavn');
setConst('TMPL_SummaryOfChange', 'Kommentar til endring:');
setConst('TMPL_AddToCategory', 'Legg dokumentet til følgende kategori(er)side(r):');
setConst('TMPL_YourChanges', 'Dine endringer');
setConst('TMPL_PreviewCurrentVersion', 'Forhåndsvisning av nåværende versjon');
setConst('TMPL_DifferencesIn', 'Forskjeller på');
setConst('TMPL_DifferenceBetweenVersions', 'Forskjeller mellom versjoner:');
setConst('TMPL_Find', 'Finn');
setConst('TMPL_HistoryOf', 'Historien til');
setConst('TMPL_Older', 'Eldre');
setConst('TMPL_Newer', 'Nyere');
setConst('TMPL_ButtonComputeDifference', 'Beregn forskjell');
setConst('TMPL_ChangesLastAuthor', 'Forandring av siste forfatter:');
setConst('TMPL_Prefs', 'Preferanser');
setConst('TMPL_Previewing', 'Forhåndsviser');
setConst('TMPL_Preview', 'Forhåndsvisning');

?>