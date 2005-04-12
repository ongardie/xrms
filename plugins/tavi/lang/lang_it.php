<?php
// $Id: lang_it.php,v 1.14 2004/03/12 14:16:15UTC mesfet
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
       '<br />Per un aiuto, contatta l\'<a href="mailto:' .
       $Admin . '">amministratore</a>.');

/* action-directory */
setConst('ACTION_RateControlIpBlocking', 'Controllo accessi / Blocco IP disabilitato');
setConst('ACTION_LockUnlockPages', 'Blocco / Sblocco pagine');
setConst('ACTION_BLockUnblockHosts', 'Blocco / Sblocco numero IP');
setConst('ACTION_ErrorNameMatch',
       'Hai inserito uno username invalido.' . GEN_ErrorSuffix);
setConst('ACTION_ErrorAdminDisabled',
       'Le funzioni di amministrazioni risultano disabilitate.'.GEN_ErrorSuffix);
setConst('ACTION_ErrorPageLocked',
       'La pagina risulta bloccata, ovvero non editabile.' . GEN_ErrorSuffix);

/* lib-directory */
setConst('LIB_ErrorDatabaseQuery',
       'Errore nell\'esecuzione della query.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseConnect',
       'Errore di connessione al database.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseSelect',
       'Errore selezione database.' . GEN_ErrorSuffix);
setConst('LIB_ErrorCreatingTemp',
       'Errore creazione file temporaneo.' . GEN_ErrorSuffix);
setConst('LIB_ErrorWritingTemp',
       'Errore scrittura file temporaneo.' . GEN_ErrorSuffix);
// The next two entries are parsed through the wiki engine, so leave the \'\'
// to get bolded entries... And the escaping of $!DiffCmd...   ;)
setConst('LIB_NoDiffAvailableSafeMode',
       '\'\'Impossibile calcolare le differenze, a causa di un errata locazione del comando diffcmd. Deve essere '.
       'inserito dentro '. ini_get("safe_mode_exec_dir") .
       'ma risulta locato in '. $DiffCmd .'. Per favore muovere diffcmd oppure '.
       'notifica l\'amministratore affinché crei un link simbolico dentro questa directory. Inoltre '.
       'ricorda di cambiare la configurazione di $!DiffCmd .\'\'');
setConst('LIB_NoDiffAvailable',
       '\'\'Diff non disponibile. $!DiffCmd punta a ' . $DiffCmd .
       ' che non esiste o non risulta leggibile\'\'');
setConst('LIB_ErrorDeniedAccess',
       'L\'accesso a wiki ti è stato negato.' . GEN_ErrorSuffix);
setConst('LIB_ErrorRateExceeded',
       'Hai superato il numero max di visite consentite '.
       'nel periodo di tempo.  A presto.' . GEN_ErrorSuffix);
setConst('LIB_ErrorInvalidPage',
       'Nome pagina invalida.' . GEN_ErrorSuffix);

/* parse-directory */
setConst('PARSE_Added', 'Nuova: ');
setConst('PARSE_Changed', 'Modificata: ');
setConst('PARSE_Deleted', 'Rimossa: ');
setConst('PARSE_Never', 'Mai');
// The next two entries, are joined with a counting variable inbetween
setConst('PARSE_CompleteListStart', 'Visita la lista completa (');
setConst('PARSE_CompleteListEnd',   ' occorrenze)');
setConst('PARSE_RecentChanges', 'UltimeModifiche');
setConst('PARSE_Locked', 'Bloccata');
setConst('PARSE_BlockedRange', 'Range indirizzi IP bloccati');
setConst('PARSE_EnterIpRange',
       'Inserisci range indirizzi IP nella forma <tt>12.*</tt>, <tt>34.56.*</tt>, o ' .
       '<tt>78.90.123.*</tt>');
setConst('PARSE_EmptyToBeDeleted',
       ' ** Vuoto, programmato per la cancellazione ** ');
setConst('PARSE_From', 'da'); //ie. SomeThing *from* WantedPages, MacroSomePage
setConst('PARSE_ButtonBlock', 'Blocca');
setConst('PARSE_ButtonUnblock', 'Sblocca');
setConst('PARSE_ButtonSave', 'Salva');
setConst('PARSE_ButtonPreview', 'Anteprima');
setConst('PARSE_Preferences', 'Preferenze');
setConst('PARSE_History', 'history'); // note lowercase first character

/* Template directory */
// Note the change to use only TMPL_ as prefix instead of full TEMPLATE_
setConst('TMPL_Administration', 'Amministrazione');
setConst('TMPL_EditDocument', 'Modifica documento');
setConst('TMPL_NoEditDocument', 'Questo documento non risulta modificabile');
setConst('TMPL_EditArchiveVersion',
       'Edita questa <em>VERSIONE ARCHIVIATA</em> del documento');
// Next entry is followed by a date as produced by html_time()
setConst('TMPL_ViewDocHistory', 'Visualizza modifiche documento');
setConst('TMPL_DocLastModified', 'Ultima modifica al documento');
setConst('TMPL_TwinPages', 'Pagine doppie:');
setConst('TMPL_Search', 'Cerca:');
setConst('TMPL_Editing', 'Modifica');
setConst('TMPL_WarningOtherEditing',
       'Attenzione: da quando hai iniziato la modifica, questo documento è stato cambiato '.
       'da qualcun altro.  per favore unisci le tue modifiche alla versione corrente '.
       'di questo documento.' );
setConst('TMPL_CurrentVersion', 'Versione Corrente');
setConst('TMPL_ButtonSave', 'Salva');
setConst('TMPL_ButtonPreview', 'Anteprima');
setConst('TMPL_PreCaptcha', 'Nel risparmiare, entri in quanto segue:<br />');
setConst('TMPL_YourUsername', 'Il tuo username è');
setConst('TMPL_VisitPrefs',
       'Visita <a href="'. $PrefsScript. '">Preferenze</a> per impostare '.
       'il tuo username');
setConst('TMPL_SummaryOfChange', 'Lista modifiche:');
setConst('TMPL_AddToCategory', 'Aggiungi documento alla categoria:');
setConst('TMPL_YourChanges', 'Le tue modifiche');
setConst('TMPL_PreviewCurrentVersion', 'Anteprima della Versione Corrente');
setConst('TMPL_DifferencesIn', 'Differenze In');
setConst('TMPL_DifferenceBetweenVersions', 'Differenze fra versioni:');
setConst('TMPL_Find', 'Cerca');
setConst('TMPL_HistoryOf', 'History di');
setConst('TMPL_Older', 'Più vecchio');
setConst('TMPL_Newer', 'Più recente');
setConst('TMPL_ButtonComputeDifference', 'Calcola Differenze');
setConst('TMPL_ChangesLastAuthor', 'Cambiamenti dall\'ultimo autore:');
setConst('TMPL_Prefs', 'Preferenze');
setConst('TMPL_Previewing', 'Visualizza Anteprima');
setConst('TMPL_Preview', 'Anteprima');

?>
