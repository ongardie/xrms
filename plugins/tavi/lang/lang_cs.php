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
 *   setConst(LANGUAGE_CODE, 'cs');
 * Using 'cs' for the czech version, if available.
 */

/* General messages */
setConst('GEN_ErrorSuffix', 
       '<br />V pøípadì pøetrvávajících problémù kontaktuj <a href="mailto:' . 
       $Admin . '">administrátora</a>.');

/* action-directory */
setConst('ACTION_RateControlIpBlocking', 'Rate control / Blokování IP adres je vypnuto');
setConst('ACTION_LockUnlockPages', 'Zamknout / odemknout dokumenty');
setConst('ACTION_BLockUnblockHosts', 'Zablokovat / odblokovat u¾ivatele');
setConst('ACTION_ErrorNameMatch',
       'Zadal jsi neplatné u¾ivatelské jméno.' . GEN_ErrorSuffix);
setConst('ACTION_ErrorAdminDisabled',
       'Administrace není v této wiki k dispozici.'.GEN_ErrorSuffix);
setConst('ACTION_ErrorPageLocked',
       'Stránka, kterou chce¹ upravit, je zamèená.' . GEN_ErrorSuffix);

/* lib-directory */
setConst('LIB_ErrorDatabaseQuery', 
       'Nastala chyba pøi zpracování dotazu na databázi.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseConnect', 
       'Nastala chyba pøi pøipojování k databázi.' . GEN_ErrorSuffix); 
setConst('LIB_ErrorDatabaseSelect', 
       'Nastala chyba pøi výbìru databáze.' . GEN_ErrorSuffix); 
setConst('LIB_ErrorCreatingTemp', 
       'Nastala chyba pøi vytváøení pracovního souboru.' . GEN_ErrorSuffix);
setConst('LIB_ErrorWritingTemp',
       'Nastala chyba pøi zápisu do pracovního souboru.' . GEN_ErrorSuffix);
// The next two entries are parsed through the wiki engine, so leave the \'\'
// to get bolded entries... And the escaping of $!DiffCmd...   ;)
setConst('LIB_NoDiffAvailableSafeMode',
       '\'\'Nelze zobrazit rozdíly kvùli ¹patné cestì v parametru diffcmd. Cesta '.
       'by mìla být v '. ini_get("safe_mode_exec_dir") .
       'a pøitom je nastavena na '. $DiffCmd .'. Uprav si nastavení diffcmd nebo '.
       'si vytvoø symbolický link na tento adresáø. Poté nezapomeò '.
       'upravit konfiguraci $!DiffCmd.\'\'');       
setConst('LIB_NoDiffAvailable',
       '\'\'Nelze zobrazit rozdíly. $!DiffCmd ukazuje na soubor ' . $DiffCmd .
       ', který neexistuje nebo je po¹kozený\'\'');
setConst('LIB_ErrorDeniedAccess',
       'Nemá¹ práva k pøístupu na tyto stránky.' . GEN_ErrorSuffix);
setConst('LIB_ErrorRateExceeded',
       'Pøekonal jsi limit poètu stránek, které mù¾e¹ shlédnout za urèenou dobu.'.
       'Pokraèuj v prohlí¾ení pozdìji.' . GEN_ErrorSuffix);
setConst('LIB_ErrorInvalidPage',
       'Neplatný název stránky (dokumentu).' . GEN_ErrorSuffix);

/* parse-directory */
setConst('PARSE_Added', 'Pøidáno: ');
setConst('PARSE_Changed', 'Zmìnìno: ');
setConst('PARSE_Deleted', 'Smazáno: ');
setConst('PARSE_Never', '®ádné'); 
// The next two entries, are joined with a counting variable inbetween
setConst('PARSE_CompleteListStart', 'Zobrazit kompletní pøehled (');
setConst('PARSE_CompleteListEnd',   ' záznamù)');
setConst('PARSE_RecentChanges', 'Pøehled zmìn');
setConst('PARSE_Locked', 'Zamknout');
setConst('PARSE_BlockedRange', 'Blokované IP adresy');
setConst('PARSE_EnterIpRange', 
       'Zadej rozsah IP adres ve formì <tt>12.*</tt>, <tt>34.56.*</tt> nebo ' .
       '<tt>78.90.123.*</tt>');
setConst('PARSE_EmptyToBeDeleted',
       ' ** Prázdný dokument, pøipravený ke smazání ** ');
setConst('PARSE_From', 'z'); //ie. SomeThing *from* WantedPages, MacroSomePage
setConst('PARSE_ButtonBlock', 'Zablokovat');
setConst('PARSE_ButtonUnblock', 'Odblokovat');
setConst('PARSE_ButtonSave', 'Ulo¾it');
setConst('PARSE_ButtonPreview', 'Náhled');

/* Template directory */
// Note the change to use only TMPL_ as prefix instead of full TEMPLATE_
setConst('TMPL_Administration', 'Administrace');
setConst('TMPL_EditDocument', 'Upravit tento dokument');
setConst('TMPL_NoEditDocument', 'Tento dokument nelze mìnit');
setConst('TMPL_EditArchiveVersion', 
       'Uprav tuto <em>ARCHIVNÍ VERZI</em> dokumentu');
// Next entry is followed by a date as produced by html_time()
setConst('TMPL_ViewDocHistory', 'Zobrazit historii dokumentu');
setConst('TMPL_DocLastModified', 'Poslední úpravy');
setConst('TMPL_TwinPages', 'Stejné stránky:');
setConst('TMPL_Search', 'Hledat:');
setConst('TMPL_Editing', 'Upravit');
setConst('TMPL_WarningOtherEditing',
       'Upozornìní: bìhem úprav dokumentu byl tento dokument zmìnìn nìkým jiným. '.
       'Pøidej svoje zmìny do aktuální verze tohoto dokumentu.');
setConst('TMPL_CurrentVersion', 'Aktuální verze');
setConst('TMPL_ButtonSave', 'Ulo¾it');
setConst('TMPL_ButtonPreview', 'Náhled');
setConst('TMPL_YourUsername', 'Tvoje u¾ivatelské jméno je');
setConst('TMPL_VisitPrefs', 
       'V <a href="'. $PrefsScript. '">Nastavení</a> si uprav svoje '.
       'u¾ivatelské jméno');
setConst('TMPL_SummaryOfChange', 'Popis provedených zmìn:');
setConst('TMPL_AddToCategory', 'Zaøadit dokument do kategorie:');
setConst('TMPL_YourChanges', 'Tvoje zmìny');
setConst('TMPL_PreviewCurrentVersion', 'Náhled aktuální verze');
setConst('TMPL_DifferencesIn', 'Rozdíly mezi');
setConst('TMPL_DifferenceBetweenVersions', 'Rozdíly mezi vybranými verzemi:');
setConst('TMPL_Find', 'Hledat');
setConst('TMPL_HistoryOf', 'Historie');
setConst('TMPL_Older', 'Star¹í');
setConst('TMPL_Newer', 'Novìj¹í');
setConst('TMPL_ButtonComputeDifference', 'Zobrazit rozdíly');
setConst('TMPL_ChangesLastAuthor', 'Úpravy posledního autora:');
setConst('TMPL_Prefs', 'Nastavení');
setConst('TMPL_Previewing', 'Náhled');
setConst('TMPL_Preview', 'Náhled');

?>
