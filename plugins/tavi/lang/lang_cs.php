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
       '<br />V p��pad� p�etrv�vaj�c�ch probl�m� kontaktuj <a href="mailto:' . 
       $Admin . '">administr�tora</a>.');

/* action-directory */
setConst('ACTION_RateControlIpBlocking', 'Rate control / Blokov�n� IP adres je vypnuto');
setConst('ACTION_LockUnlockPages', 'Zamknout / odemknout dokumenty');
setConst('ACTION_BLockUnblockHosts', 'Zablokovat / odblokovat u�ivatele');
setConst('ACTION_ErrorNameMatch',
       'Zadal jsi neplatn� u�ivatelsk� jm�no.' . GEN_ErrorSuffix);
setConst('ACTION_ErrorAdminDisabled',
       'Administrace nen� v t�to wiki k dispozici.'.GEN_ErrorSuffix);
setConst('ACTION_ErrorPageLocked',
       'Str�nka, kterou chce� upravit, je zam�en�.' . GEN_ErrorSuffix);

/* lib-directory */
setConst('LIB_ErrorDatabaseQuery', 
       'Nastala chyba p�i zpracov�n� dotazu na datab�zi.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseConnect', 
       'Nastala chyba p�i p�ipojov�n� k datab�zi.' . GEN_ErrorSuffix); 
setConst('LIB_ErrorDatabaseSelect', 
       'Nastala chyba p�i v�b�ru datab�ze.' . GEN_ErrorSuffix); 
setConst('LIB_ErrorCreatingTemp', 
       'Nastala chyba p�i vytv��en� pracovn�ho souboru.' . GEN_ErrorSuffix);
setConst('LIB_ErrorWritingTemp',
       'Nastala chyba p�i z�pisu do pracovn�ho souboru.' . GEN_ErrorSuffix);
// The next two entries are parsed through the wiki engine, so leave the \'\'
// to get bolded entries... And the escaping of $!DiffCmd...   ;)
setConst('LIB_NoDiffAvailableSafeMode',
       '\'\'Nelze zobrazit rozd�ly kv�li �patn� cest� v parametru diffcmd. Cesta '.
       'by m�la b�t v '. ini_get("safe_mode_exec_dir") .
       'a p�itom je nastavena na '. $DiffCmd .'. Uprav si nastaven� diffcmd nebo '.
       'si vytvo� symbolick� link na tento adres��. Pot� nezapome� '.
       'upravit konfiguraci $!DiffCmd.\'\'');       
setConst('LIB_NoDiffAvailable',
       '\'\'Nelze zobrazit rozd�ly. $!DiffCmd ukazuje na soubor ' . $DiffCmd .
       ', kter� neexistuje nebo je po�kozen�\'\'');
setConst('LIB_ErrorDeniedAccess',
       'Nem� pr�va k p��stupu na tyto str�nky.' . GEN_ErrorSuffix);
setConst('LIB_ErrorRateExceeded',
       'P�ekonal jsi limit po�tu str�nek, kter� m��e� shl�dnout za ur�enou dobu.'.
       'Pokra�uj v prohl�en� pozd�ji.' . GEN_ErrorSuffix);
setConst('LIB_ErrorInvalidPage',
       'Neplatn� n�zev str�nky (dokumentu).' . GEN_ErrorSuffix);

/* parse-directory */
setConst('PARSE_Added', 'P�id�no: ');
setConst('PARSE_Changed', 'Zm�n�no: ');
setConst('PARSE_Deleted', 'Smaz�no: ');
setConst('PARSE_Never', '��dn�'); 
// The next two entries, are joined with a counting variable inbetween
setConst('PARSE_CompleteListStart', 'Zobrazit kompletn� p�ehled (');
setConst('PARSE_CompleteListEnd',   ' z�znam�)');
setConst('PARSE_RecentChanges', 'P�ehled zm�n');
setConst('PARSE_Locked', 'Zamknout');
setConst('PARSE_BlockedRange', 'Blokovan� IP adresy');
setConst('PARSE_EnterIpRange', 
       'Zadej rozsah IP adres ve form� <tt>12.*</tt>, <tt>34.56.*</tt> nebo ' .
       '<tt>78.90.123.*</tt>');
setConst('PARSE_EmptyToBeDeleted',
       ' ** Pr�zdn� dokument, p�ipraven� ke smaz�n� ** ');
setConst('PARSE_From', 'z'); //ie. SomeThing *from* WantedPages, MacroSomePage
setConst('PARSE_ButtonBlock', 'Zablokovat');
setConst('PARSE_ButtonUnblock', 'Odblokovat');
setConst('PARSE_ButtonSave', 'Ulo�it');
setConst('PARSE_ButtonPreview', 'N�hled');

/* Template directory */
// Note the change to use only TMPL_ as prefix instead of full TEMPLATE_
setConst('TMPL_Administration', 'Administrace');
setConst('TMPL_EditDocument', 'Upravit tento dokument');
setConst('TMPL_NoEditDocument', 'Tento dokument nelze m�nit');
setConst('TMPL_EditArchiveVersion', 
       'Uprav tuto <em>ARCHIVN� VERZI</em> dokumentu');
// Next entry is followed by a date as produced by html_time()
setConst('TMPL_ViewDocHistory', 'Zobrazit historii dokumentu');
setConst('TMPL_DocLastModified', 'Posledn� �pravy');
setConst('TMPL_TwinPages', 'Stejn� str�nky:');
setConst('TMPL_Search', 'Hledat:');
setConst('TMPL_Editing', 'Upravit');
setConst('TMPL_WarningOtherEditing',
       'Upozorn�n�: b�hem �prav dokumentu byl tento dokument zm�n�n n�k�m jin�m. '.
       'P�idej svoje zm�ny do aktu�ln� verze tohoto dokumentu.');
setConst('TMPL_CurrentVersion', 'Aktu�ln� verze');
setConst('TMPL_ButtonSave', 'Ulo�it');
setConst('TMPL_ButtonPreview', 'N�hled');
setConst('TMPL_YourUsername', 'Tvoje u�ivatelsk� jm�no je');
setConst('TMPL_VisitPrefs', 
       'V <a href="'. $PrefsScript. '">Nastaven�</a> si uprav svoje '.
       'u�ivatelsk� jm�no');
setConst('TMPL_SummaryOfChange', 'Popis proveden�ch zm�n:');
setConst('TMPL_AddToCategory', 'Za�adit dokument do kategorie:');
setConst('TMPL_YourChanges', 'Tvoje zm�ny');
setConst('TMPL_PreviewCurrentVersion', 'N�hled aktu�ln� verze');
setConst('TMPL_DifferencesIn', 'Rozd�ly mezi');
setConst('TMPL_DifferenceBetweenVersions', 'Rozd�ly mezi vybran�mi verzemi:');
setConst('TMPL_Find', 'Hledat');
setConst('TMPL_HistoryOf', 'Historie');
setConst('TMPL_Older', 'Star��');
setConst('TMPL_Newer', 'Nov�j��');
setConst('TMPL_ButtonComputeDifference', 'Zobrazit rozd�ly');
setConst('TMPL_ChangesLastAuthor', '�pravy posledn�ho autora:');
setConst('TMPL_Prefs', 'Nastaven�');
setConst('TMPL_Previewing', 'N�hled');
setConst('TMPL_Preview', 'N�hled');

?>
