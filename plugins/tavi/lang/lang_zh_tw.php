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
 *   setConst(LANGUAGE_CODE, 'zh_tw');
 * Using 'zh_tw' for the traditional chinese version, if available.
 */

/* General messages */
setConst('GEN_ErrorSuffix', 
       '<br />麻煩請�?�絡 <a href="mailto:' . 
       $Admin . '">管�?�者</a> 來�?�助� .');

/* action-directory */
setConst('ACTION_RateControlIpBlocking', '� �率管制 / IP �?鎖�?�用');
setConst('ACTION_LockUnlockPages', '鎖定 / 解鎖 � ?�?�');
setConst('ACTION_BLockUnblockHosts', '�?鎖 / 解鎖 hosts');
setConst('ACTION_ErrorNameMatch',
       '� 輸入了無效的使用者�??稱.' . GEN_ErrorSuffix);
setConst('ACTION_ErrorAdminDisabled',
       '�wiki的管�?�功能已關閉.'.GEN_ErrorSuffix);
setConst('ACTION_ErrorPageLocked',
       '� 所嘗試編輯的� ?�?�已鎖定.' . GEN_ErrorSuffix);

/* lib-directory */
setConst('LIB_ErrorDatabaseQuery', 
       '執行資料庫查詢錯誤.' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseConnect', 
       '�?��?資料庫錯誤.' . GEN_ErrorSuffix); 
setConst('LIB_ErrorDatabaseSelect', 
       '�?�擇資料庫錯誤.' . GEN_ErrorSuffix); 
setConst('LIB_ErrorCreatingTemp', 
       '製� 暫�檔錯誤.' . GEN_ErrorSuffix);
setConst('LIB_ErrorWritingTemp',
       '寫入暫�檔錯誤.' . GEN_ErrorSuffix);
// The next two entries are parsed through the wiki engine, so leave the \'\'
// to get bolded entries... And the escaping of $!DiffCmd...   ;)
setConst('LIB_NoDiffAvailableSafeMode',
       '\'\'無�?�用的比較軟體, 應該是 diffcmd 放置在錯誤的�?置. '.
       '應放置在 '. ini_get("safe_mode_exec_dir") . ' 內. '.
       '但其�?置在 '. $DiffCmd .'. 請�?新安置 diffcmd 或'.
       '請 sysAdmins 製�  symbolic links 到該目錄. 也請'.
       '記得將�定改到與  $!DiffCmd 一致的�?置.\'\'');       
setConst('LIB_NoDiffAvailable',
       '\'\'無�?�用的比較軟體. �?於 ' . $DiffCmd . ' 的 ' .'$!DiffCmd'.
       ' �?�在或無法讀�?�\'\'');
setConst('LIB_ErrorDeniedAccess',
       '您的��?�已經�?本站拒絕.' . GEN_ErrorSuffix);
setConst('LIB_ErrorRateExceeded',
       '您已經在時段內超出所�?許的��?�次數.  請晚點�?回來嘗試.' . GEN_ErrorSuffix);
setConst('LIB_ErrorInvalidPage',
       '無效的� ?�?��??稱.' . GEN_ErrorSuffix);

/* parse-directory */
setConst('PARSE_Added', '已新增: ');
setConst('PARSE_Changed', '已改變: ');
setConst('PARSE_Deleted', '已刪除: ');
setConst('PARSE_Never', '從未'); 
// The next two entries, are joined with a counting variable inbetween
setConst('PARSE_CompleteListStart', '觀看完整清單 (');
setConst('PARSE_CompleteListEnd',   ' �)');
setConst('PARSE_RecentChanges', '最近的更動');
setConst('PARSE_Locked', '已鎖定');
setConst('PARSE_BlockedRange', '�?�?鎖的 IP 地�?�範�?');
setConst('PARSE_EnterIpRange', 
       '以�� ��? <tt>12.*</tt>, <tt>34.56.*</tt>, 或 ' .
       '<tt>78.90.123.*</tt> 輸入 IP 地�?�');
setConst('PARSE_EmptyToBeDeleted',
       ' ** 空的, � ?定刪除 ** ');
setConst('PARSE_From', '從'); //ie. SomeThing *from* WantedPages, MacroSomePage
setConst('PARSE_ButtonBlock', '�?鎖');
setConst('PARSE_ButtonUnblock', '解鎖');
setConst('PARSE_ButtonSave', '儲�');
setConst('PARSE_ButtonPreview', '� ?覽');

/* Template directory */
// Note the change to use only TMPL_ as prefix instead of full TEMPLATE_
setConst('TMPL_Administration', '管�?�');
setConst('TMPL_EditDocument', '編輯這份文件');
setConst('TMPL_NoEditDocument', '�?能編輯這份文件');
setConst('TMPL_EditArchiveVersion', 
       '編輯文件的 <em>��?�紀錄版本</em> ');
// Next entry is followed by a date as produced by html_time()
setConst('TMPL_ViewDocHistory', '�?覽文件�程');
setConst('TMPL_DocLastModified', '文件最後一次更改於');
setConst('TMPL_TwinPages', '雙胞� ?�?�:');
setConst('TMPL_Search', '�?�尋:');
setConst('TMPL_Editing', '�在編輯');
setConst('TMPL_WarningOtherEditing',
       '�告: 自您開始編輯的期間, 本文件已�?其他人修改. '.
       '請將您的更改�?�併進目�?版本的文件.');
setConst('TMPL_CurrentVersion', '目�?的版本');
setConst('TMPL_ButtonSave', '儲�');
setConst('TMPL_ButtonPreview', '� ?覽');
setConst('TMPL_YourUsername', '您的使用者�??稱是');
setConst('TMPL_VisitPrefs', 
       '請�?覽 <a href="'. $PrefsScript. '">�??好</a> 來�定 '.
       '您的使用者�??稱');
setConst('TMPL_SummaryOfChange', '概括說明改變:');
setConst('TMPL_AddToCategory', '將文件� 入類別:');
setConst('TMPL_YourChanges', '您的改變');
setConst('TMPL_PreviewCurrentVersion', '� ?覽目�?的版本');
setConst('TMPL_DifferencesIn', '差異：');
setConst('TMPL_DifferenceBetweenVersions', '版本間的�?�?�');
setConst('TMPL_Find', '尋找');
setConst('TMPL_HistoryOf', '�程：');
setConst('TMPL_Older', '較舊的');
setConst('TMPL_Newer', '較新的');
setConst('TMPL_ButtonComputeDifference', '比�?�?�?�點');
setConst('TMPL_ChangesLastAuthor', '最近一�?作者的更動:');
setConst('TMPL_Prefs', '�??好�定');
setConst('TMPL_Previewing', '�在� ?覽');
setConst('TMPL_Preview', '� ?覽');

?>