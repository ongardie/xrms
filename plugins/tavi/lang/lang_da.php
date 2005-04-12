<?php
/* General messages */
setConst('GEN_ErrorSuffix',
       '<br />Kontakt venligst <a href="mailto:' .
       $Admin . '">administratoren</a> for assistance.');

/* action-directory */
setConst('ACTION_RateControlIpBlocking', 'Rate kontrol / IP blokering slæet fra');
setConst('ACTION_LockUnlockPages', 'Lås / lås op for sider');
setConst('ACTION_BLockUnblockHosts', 'Bloker / fjern blokering for IPer');
setConst('ACTION_ErrorNameMatch',
       'Du har angivet et forkert brugernavn.' . GEN_ErrorSuffix);
setConst('ACTION_ErrorAdminDisabled',
       'Administrations egenskaber er slået fra for dette wiki.'.GEN_ErrorSuffix);
setConst('ACTION_ErrorPageLocked',
       'Den side du har prøvet at redigere er låst.' . GEN_ErrorSuffix);

/* lib-directory */
setConst('LIB_ErrorDatabaseQuery',
       'Fejl ved udførsel af database forespørgsel.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseConnect',
       'Fejl ved forbindelse til database.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseSelect',
       'Fejl ved valg af database.' . GEN_ErrorSuffix);
setConst('LIB_ErrorCreatingTemp',
       'Fejl ved oprettelse af midlertidig fil.' . GEN_ErrorSuffix);
setConst('LIB_ErrorWritingTemp',
       'Fejl ved skrivning til midlertidig fil.' . GEN_ErrorSuffix);
// The next two entries are parsed through the wiki engine, so leave the \'\'
// to get bolded entries... And the escaping of $!DiffCmd...   ;)
setConst('LIB_NoDiffAvailableSafeMode',
       '\'\'Ingen diff tilgængelig, pga. forkert angivet sti til diffcmd. Det skulle have '.
       'været indenfor '. ini_get("safe_mode_exec_dir") .
       'men findes i '. $DiffCmd .'. Venligst flyt diffcmd eller få '.
       'system administratoren til at oprette et symbolsk link til dette katalog. '.
       'Husk derefter også at ændre konfigurationen for $!DiffCmd.\'\'');
setConst('LIB_NoDiffAvailable',
       '\'\'Ingen diff tilgængelig. $!DiffCmd peger på ' . $DiffCmd .
       ' som ikke eksisterer eller er læsbar.\'\'');
setConst('LIB_ErrorDeniedAccess',
       'Du er blevet nægtet adgang til dette site.' . GEN_ErrorSuffix);
setConst('LIB_ErrorRateExceeded',
       'Du har overskredet antallet af sider du er tilladt at besøge indenfor et givent tidsrum. '.
       'Kom venligst tilbage senere.' . GEN_ErrorSuffix);
setConst('LIB_ErrorInvalidPage',
       'Forkert side-navn.' . GEN_ErrorSuffix);

/* parse-directory */
setConst('PARSE_Added', 'Tilføjet: ');
setConst('PARSE_Changed', 'Ændret: ');
setConst('PARSE_Deleted', 'Slettet: ');
setConst('PARSE_Never', 'Aldrig');
// The next two entries, are joined with a counting variable inbetween
setConst('PARSE_CompleteListStart', 'Se den komplette liste (');
setConst('PARSE_CompleteListEnd',   ' elementer)');
setConst('PARSE_RecentChanges', 'SenesteAendringer');
setConst('PARSE_Locked', 'Låst');
setConst('PARSE_BlockedRange', 'Blokerede IP-adresse intervaller');
setConst('PARSE_EnterIpRange',
       'Indtast IP-adresse interval i formen <tt>12.*</tt>, <tt>34.56.*</tt>, eller ' .
       '<tt>78.90.123.*</tt>');
setConst('PARSE_EmptyToBeDeleted',
       ' ** Tom, markeret til at slettes ** ');
setConst('PARSE_From', 'fra'); //ie. SomeThing *from* WantedPages, MacroSomePage
setConst('PARSE_ButtonBlock', 'Blokér');
setConst('PARSE_ButtonUnblock', 'Lås op');
setConst('PARSE_ButtonSave', 'Gem');
setConst('PARSE_ButtonPreview', 'Vis prøve');
setConst('PARSE_Preferences', 'Indstillinger');
setConst('PARSE_History', 'Historie');

/* Template directory */
// Note the change to use only TMPL_ as prefix instead of full TEMPLATE_
setConst('TMPL_Administration', 'Administration');
setConst('TMPL_EditDocument', 'Rediger denne side');
setConst('TMPL_NoEditDocument', 'Denne side kan ikke redigeres');
setConst('TMPL_EditArchiveVersion',
       'Rediger denne <em>ARKIV VERSION</em> af siden');
// Next entry is followed by a date as produced by html_time()
setConst('TMPL_ViewDocHistory', 'Vis sidens historie');
setConst('TMPL_DocLastModified', 'Side sidst ændret');
setConst('TMPL_TwinPages', 'Tvilling sider:');
setConst('TMPL_Search', 'Søg:');
setConst('TMPL_Editing', 'Redigerer');
setConst('TMPL_WarningOtherEditing',
       'Advarsel: Siden du begyndte at redigere siden her, er den blevet ændret af en anden. '.
       'Flet venligst dine redigeringer ind i den seneste version af denne side.');
setConst('TMPL_CurrentVersion', 'Nuværende version');
setConst('TMPL_ButtonSave', 'Gem');
setConst('TMPL_ButtonPreview', 'Vis prøve');
setConst('TMPL_PreCaptcha', 'Udfyld følgende når der gemmes:<br/>');
setConst('TMPL_YourUsername', 'Dit brugernavn er');
setConst('TMPL_VisitPrefs',
       'Gå til <a href="'. $PrefsScript. '">Indstillinger</a> for at sætte '.
       'dit brugernavn.');
setConst('TMPL_SummaryOfChange', 'Opsummering af ændringer:');
setConst('TMPL_AddToCategory', 'Tilføj side til kategori:');
setConst('TMPL_YourChanges', 'Dine ændringer');
setConst('TMPL_PreviewCurrentVersion', 'Vis prøve af nuværende version');
setConst('TMPL_DifferencesIn', 'Forskelle i');
setConst('TMPL_DifferenceBetweenVersions', 'Forskel mellem versioner:');
setConst('TMPL_Find', 'Find');
setConst('TMPL_HistoryOf', 'Historie for');
setConst('TMPL_Older', 'Ældre');
setConst('TMPL_Newer', 'Nyere');
setConst('TMPL_ButtonComputeDifference', 'Beregn forskel');
setConst('TMPL_ChangesLastAuthor', 'Ændringer af seneste bruger:');
setConst('TMPL_Prefs', 'Indstillinger');
setConst('TMPL_Previewing', 'Viser prøve');
setConst('TMPL_Preview', 'Vis prøve');
?>