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
 *   setConst(LANGUAGE_CODE, 'fr');
 * Using 'fr' for the french version, if available.
 */

/* General messages */
setConst('GEN_ErrorSuffix', 
       '<br />Contacter SVP l\'<a href="mailto:' . 
       $Admin . '">Administrateur</a> si vous avez besoin d\'aide.');

/* action-directory */
setConst('ACTION_RateControlIpBlocking', 'Niveau de contrôle / Blocage IP désactivé');
setConst('ACTION_LockUnlockPages', 'Verrouille / Déverrouille les pages');
setConst('ACTION_BLockUnblockHosts', 'Bloque / Libère l\'hôte');
setConst('ACTION_ErrorNameMatch',
       'Vous avez saisi un nom d\'utilisateur invalide.' . GEN_ErrorSuffix);
setConst('ACTION_ErrorAdminDisabled',
       'Les options d\'administration sont désactivées pour ce wiki.'.GEN_ErrorSuffix);
setConst('ACTION_ErrorPageLocked',
       'La page que vous tentez d\'éditer est verrouillée.' . GEN_ErrorSuffix);

/* lib-directory */
setConst('LIB_ErrorDatabaseQuery',
       'Erreur d\'exécution de la requête.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseConnect',
       'Erreur de connexion à la base.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseSelect',
       'Erreur de sélection de la base.' . GEN_ErrorSuffix);
setConst('LIB_ErrorCreatingTemp',
       'Erreur de création de fichier temporaire.' . GEN_ErrorSuffix);
setConst('LIB_ErrorWritingTemp',
       'Erreur d\'écriture de fichier temporaire.' . GEN_ErrorSuffix);
// The next two entries are parsed through the wiki engine, so leave the \'\'
// to get bolded entries... And the escaping of $!DiffCmd...   ;)
setConst('LIB_NoDiffAvailableSafeMode',
       '\'\'Programme diff non trouvé, commande diffcmd inaccessible. Elle devrait être '.
       'située à l\'intérieur de '. ini_get("safe_mode_exec_dir") .
       'mais est située à '. $DiffCmd .'. Déplacez SVP diffcmd ou '.
       'demandez à l\'admin de créer un lien vers ce répertoire. N\'oubliez '.
       'pas de changer la configuration de $!DiffCmd en conséquence.\'\'');
setConst('LIB_NoDiffAvailable',
       '\'\'Programme diff non trouvé. $!DiffCmd pointe vers ' . $DiffCmd .
       ' qui, soit n\'existe pas, soit n\'est pas lisible\'\'');
setConst('LIB_ErrorDeniedAccess',
       'Vous avez été interdit de ce site.' . GEN_ErrorSuffix);
setConst('LIB_ErrorRateExceeded',
       'Vous avez dépassé le nombre maximun de pages autorisé par visite '.
       'sur une péroide donnée.  Revenez plus tard.' . GEN_ErrorSuffix);
setConst('LIB_ErrorInvalidPage',
       'Nom de page invalide.' . GEN_ErrorSuffix);

/* parse-directory */
setConst('PARSE_Added', 'Ajouté: ');
setConst('PARSE_Changed', 'Changé: ');
setConst('PARSE_Deleted', 'Supprimé: ');
setConst('PARSE_Never', 'Jamais');
// The next two entries, are joined with a counting variable inbetween
setConst('PARSE_CompleteListStart', 'Voir la liste complète (');
setConst('PARSE_CompleteListEnd',   ' entrées)');
setConst('PARSE_RecentChanges', 'ChangementsRecents');
setConst('PARSE_Locked', 'Verrouillée');
setConst('PARSE_BlockedRange', 'Plage d\'adresses IP bloquées');
setConst('PARSE_EnterIpRange',
       'Entrer les plages d\'adresses IP sous la forme <tt>12.*</tt>, <tt>34.56.*</tt>, or ' .
       '<tt>78.90.123.*</tt>');
setConst('PARSE_EmptyToBeDeleted',
       ' ** Vide, planifiée pour la suppression ** ');
setConst('PARSE_From', 'de'); //ie. SomeThing *from* WantedPages, MacroSomePage
setConst('PARSE_ButtonBlock', 'Bloquer');
setConst('PARSE_ButtonUnblock', 'Débloquer');
setConst('PARSE_ButtonSave', 'Sauver');
setConst('PARSE_ButtonPreview', 'Aperçu');
setConst('PARSE_Preferences', 'Préférences');
setConst('PARSE_History', 'historique'); // note lowercase first character

/* Template directory */
// Note the change to use only TMPL_ as prefix instead of full TEMPLATE_
setConst('TMPL_Administration', 'Administration');
setConst('TMPL_EditDocument', 'Editer ce document');
setConst('TMPL_NoEditDocument', 'Ce document n\'est pas modifiable');
setConst('TMPL_EditArchiveVersion',
       'Edit this <em>ARCHIVE VERSION</em> of this document');
// Next entry is followed by a date as produced by html_time()
setConst('TMPL_ViewDocHistory', 'Voir l\'historique du document');
setConst('TMPL_DocLastModified', 'Dernière modification du document');
setConst('TMPL_TwinPages', 'pages jumelles:');
setConst('TMPL_Search', 'Recherche:');
setConst('TMPL_Editing', 'Edition');
setConst('TMPL_WarningOtherEditing',
       'Attention: depuis que vous avez commencé à l\'éditer, ce document a été changé '.
       'par quelqu\'un d\'autre. S\'il vous plait ajoutez vos modifications à la version actuelle '.
       'de ce document.' );
setConst('TMPL_CurrentVersion', 'Version courante');
setConst('TMPL_ButtonSave', 'Sauver');
setConst('TMPL_ButtonPreview', 'Aperçu');
setConst('TMPL_PreCaptcha', 'Quand vous sauvegardez, entrer le texte suivant: <br />');
setConst('TMPL_YourUsername', 'Votre nom d\'utilisateur est');
setConst('TMPL_VisitPrefs',
       'Voyez <a href="'. $PrefsScript. '">Préférences</a> pour paramètrer '.
       'votre nom d\'utilisateur');
setConst('TMPL_SummaryOfChange', 'Résumé des changements:');
setConst('TMPL_AddToCategory', 'Ajoute le document à la catégorie:');
setConst('TMPL_YourChanges', 'Vos changements');
setConst('TMPL_PreviewCurrentVersion', 'Aperçu de la version courrante');
setConst('TMPL_DifferencesIn', 'Différences dans');
setConst('TMPL_DifferenceBetweenVersions', 'Différence entre versions:');
setConst('TMPL_Find', 'Trouve');
setConst('TMPL_HistoryOf', 'Historique de');
setConst('TMPL_Older', 'Ancien');
setConst('TMPL_Newer', 'Nouveau');
setConst('TMPL_ButtonComputeDifference', 'Calcule la différence');
setConst('TMPL_ChangesLastAuthor', 'Changé par le dernier auteur:');
setConst('TMPL_Prefs', 'Préférences');
setConst('TMPL_Previewing', 'Visualisation');
setConst('TMPL_Preview', 'Aperçu');

?>