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
 *   setConst(LANGUAGE_CODE, 'fi'); 
 * Using 'fi' for the Finnish version, if available.
 */

/* General messages */
setConst('GEN_ErrorSuffix', 
       '<br />Ole hyvä, ja ota yhteyttä <a href="mailto:' . 
       $Admin . '">ylläpitäjään</a>.');

/* action-directory */
setConst('ACTION_RateControlIpBlocking', 'Yhteysmäärärajoitus / IP rajoitus pois päältä');
setConst('ACTION_LockUnlockPages', 'Lukitse / avaa lukitus sivuilta');
setConst('ACTION_BLockUnblockHosts', 'Estä / poista esto isänniltä');
setConst('ACTION_ErrorNameMatch',
       'Syöttämäsi käyttäjänimi on virheellinen.' . GEN_ErrorSuffix);
setConst('ACTION_ErrorAdminDisabled',
       'Ylläpito-ominaisuudet eivät ole käytössä tässä wikissä.'.GEN_ErrorSuffix);
setConst('ACTION_ErrorPageLocked',
       'Sivu, jota yritit muokata, on lukittu.' . GEN_ErrorSuffix);

/* lib-directory */
setConst('LIB_ErrorDatabaseQuery', 
       'Virhe suoritettaessa tietokantakyselyä.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseConnect', 
       'Virhe yhteyden muodostamisessa tietokantaan.' . GEN_ErrorSuffix); 
setConst('LIB_ErrorDatabaseSelect', 
       'Virhe tietokannan valinnassa.' . GEN_ErrorSuffix); 
setConst('LIB_ErrorCreatingTemp', 
       'Virhe väliaikaistiedoston tekemisessä.' . GEN_ErrorSuffix);
setConst('LIB_ErrorWritingTemp',
       'Virhe väliaikaistiedostoon kirjoitettaessa.' . GEN_ErrorSuffix);
// The next two entries are parsed through the wiki engine, so leave the \'\'
// to get bolded entries... And the escaping of $!DiffCmd...   ;)
setConst('LIB_NoDiffAvailableSafeMode',
       '\'\'diff ei ole saatavilla, koska diffcmd on väärässä paikassa. Sen pitäisi '.
       'olla hakemiston '. ini_get("safe_mode_exec_dir") .
       'sisällä, mutta se on hakemistossa '. $DiffCmd .'. Siirrä diffcmd tai '.
       'pyydä sysAdmins tekemään symbolinen linkki tähän hakemistoon. Muista '.
       'myös muuttaa $!DiffCmd vastaavasti.\'\'');       
setConst('LIB_NoDiffAvailable',
       '\'\'diff ei ole saatavilla. $!DiffCmd osoittaa ' . $DiffCmd .
       ', jota ei ole tai se ei ole luettavissa\'\'');
setConst('LIB_ErrorDeniedAccess',
       'Pääsysi sivuille on estetty.' . GEN_ErrorSuffix);
setConst('LIB_ErrorRateExceeded',
       'Olet ylittänyt yhteyksien enimmäismäärän '.
       'määritellyssä ajassa.  Yritä uudestaan myöhemmin.' . GEN_ErrorSuffix);
setConst('LIB_ErrorInvalidPage',
       'Väärä sivunnimi.' . GEN_ErrorSuffix);

/* parse-directory */
setConst('PARSE_Added', 'Lisätty: ');
setConst('PARSE_Changed', 'Muutettu: ');
setConst('PARSE_Deleted', 'Poistettu: ');
setConst('PARSE_Never', 'Ei koskaan'); 
// The next two entries, are joined with a counting variable inbetween
setConst('PARSE_CompleteListStart', 'Katso koko lista (');
setConst('PARSE_CompleteListEnd',   ' kohdetta)');
setConst('PARSE_RecentChanges', 'Muutokset');
setConst('PARSE_Locked', 'Lukittu');
setConst('PARSE_BlockedRange', 'Estetyt IP-osoiteavaruudet');
setConst('PARSE_EnterIpRange', 
       'Syötä IP-osoiteväli muodossa  <tt>12.*</tt>, <tt>34.56.*</tt>, tai ' .
       '<tt>78.90.123.*</tt>');
setConst('PARSE_EmptyToBeDeleted',
       ' ** Tyhjä, merkitty poistettavaksi ** ');
setConst('PARSE_From', 'sivu(i)lta'); //ie. SomeThing *from* WantedPages, MacroSomePage
setConst('PARSE_ButtonBlock', 'Estä');
setConst('PARSE_ButtonUnblock', 'Poista esto');
setConst('PARSE_ButtonSave', 'Tallenna');
setConst('PARSE_ButtonPreview', 'Esikatselu');

/* Template directory */
// Note the change to use only TMPL_ as prefix instead of full TEMPLATE_
setConst('TMPL_Administration', 'Ylläpito');
setConst('TMPL_EditDocument', 'Muokkaa tätä sivua');
setConst('TMPL_NoEditDocument', 'Tätä sivua ei voi muokata');
setConst('TMPL_EditArchiveVersion', 
       'Muokkaa tätä <em>ARKISTOVERSIOTA</em> tästä sivusta');
// Next entry is followed by a date as produced by html_time()
setConst('TMPL_ViewDocHistory', 'Näytä sivun historia');
setConst('TMPL_DocLastModified', 'Sivua on muokattu viimeksi');
setConst('TMPL_TwinPages', 'Kaksoissivut:');
setConst('TMPL_Search', 'Etsi:');
setConst('TMPL_Editing', 'Muokkaus');
setConst('TMPL_WarningOtherEditing',
       'Varoitus: Muokkauksen aikana toinen käyttäjä on muokannut '.
       'tätä sivua.  Ole hyvä, ja yhdistä muutoksesi tämän sivun nykyiseen '.
       'versioon.' );
setConst('TMPL_CurrentVersion', 'Nykyinen versio');
setConst('TMPL_ButtonSave', 'Tallenna');
setConst('TMPL_ButtonPreview', 'Esikatselu');
setConst('TMPL_YourUsername', 'Nimesi');
setConst('TMPL_VisitPrefs', 
       'Käy määrittelemässä nimesi <a href="'. $PrefsScript. '">asetuksiin</a> '.
       '.');
setConst('TMPL_SummaryOfChange', 'Yhteenveto muutoksesta:');
setConst('TMPL_AddToCategory', 'Lisää sivu kategoriaan:');
setConst('TMPL_YourChanges', 'Muutoksesi');
setConst('TMPL_PreviewCurrentVersion', 'Nykyisen version esikatselu');
setConst('TMPL_DifferencesIn', 'Erot');
setConst('TMPL_DifferenceBetweenVersions', 'Erot versioiden välillä:');
setConst('TMPL_Find', 'Etsi');
setConst('TMPL_HistoryOf', 'Historia sivulle');
setConst('TMPL_Older', 'vanhempi');
setConst('TMPL_Newer', 'uudempi');
setConst('TMPL_ButtonComputeDifference', 'Näytä erot');
setConst('TMPL_ChangesLastAuthor', 'Edellisen muokkaajan tekemät muutokset:');
setConst('TMPL_Prefs', 'Asetukset');
setConst('TMPL_Previewing', 'Esikatselutila');
setConst('TMPL_Preview', 'Esikatselu');

?>